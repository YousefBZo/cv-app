<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CVController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\PublicProfileController;
use App\Http\Controllers\Api\V1\CertificationController;
use App\Http\Controllers\Api\V1\EducationController;
use App\Http\Controllers\Api\V1\ExperienceController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\VolunteerExperienceController;
use App\Http\Controllers\Api\V1\LanguageController;
use App\Http\Controllers\Api\V1\SkillController;
use App\Http\Controllers\Api\V1\ReactionController;
use App\Http\Controllers\Api\V1\NotificationController;

use App\Http\Resources\UserResource;
use App\Http\Requests\UpdateUserNameRequest;
use App\Http\Requests\UpdateUserPasswordRequest;

/**
 * OPTIMIZATION #12 — Rate Limiting API Endpoints
 *
 * WHY: Without rate limiting, a single client (or bot) can flood the API
 *      with thousands of requests per second. This:
 *        - Exhausts PHP-FPM workers (max_children = 20)
 *        - Overloads the database connection pool
 *        - Causes legitimate users to get timeouts
 *
 * HOW: Laravel's built-in 'throttle:api' middleware uses the rate limiter
 *      defined in AppServiceProvider. We set it to 60 requests/minute per
 *      user (or per IP for unauthenticated requests).
 *
 * WHY 60/minute? A normal user browsing the CV app makes ~5-10 requests
 *      per page load. Even rapid navigation shouldn't exceed 30/minute.
 *      60 gives comfortable headroom while blocking abuse.
 *
 * RESPONSE: When limit is exceeded, Laravel returns 429 Too Many Requests.
 *           The frontend http.js interceptor already handles 429 with a toast.
 */

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
|
| These endpoints allow anyone to browse profiles and view CVs.
| Recruiters, visitors, and shared-link recipients can access these
| without creating an account. Rate-limited to prevent scraping.
|
*/
Route::prefix('v1')->middleware('throttle:api')->group(function () {
    Route::get('/public/profiles', [PublicProfileController::class, 'index']);
    Route::get('/public/profiles/locations', [PublicProfileController::class, 'locations']);
    Route::get('/public/profiles/{slug}', [PublicProfileController::class, 'show']);

    // Reactions — public read (anyone can see counts)
    Route::get('/reactions/{profileId}', [ReactionController::class, 'show']);

    // Profile views — can be recorded by anyone (guests get viewer_id=null)
    Route::post('/profile-views/{profileId}', [ReactionController::class, 'recordView']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // Authenticated user
    Route::get('/user', fn (\Illuminate\Http\Request $request) => UserResource::make($request->user()));

    // Update user name
    Route::put('/user/name', function (UpdateUserNameRequest $request) {
        $validated = $request->validated();
        $user = $request->user();
        $user->update([
            'name' => $validated['name'],
            'slug' => \App\Models\User::generateUniqueSlug($validated['name'], $user->id),
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Name updated successfully.',
            'data'    => UserResource::make($user->fresh()),
        ]);
    });

    // Update user password
    Route::put('/user/password', function (UpdateUserPasswordRequest $request) {
        $request->user()->update([
            'password' => $request->validated()['password'],
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
        ]);
    });

    // Logout — revoke the current token
    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    });

    // CV
    Route::get('/cv', [CVController::class, 'index']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'index']);
    Route::post('/profile', [ProfileController::class, 'store']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);

    // Certification
    Route::get('/certification', [CertificationController::class, 'index']);
    Route::post('/certification', [CertificationController::class, 'store']);
    Route::match(['put', 'post'], '/certification/{certification}', [CertificationController::class, 'update']);
    Route::delete('/certification/{certification}', [CertificationController::class, 'destroy']);

    // Education
    Route::get('/education/fields', [EducationController::class, 'availableFields']);
    Route::get('/education', [EducationController::class, 'index']);
    Route::post('/education', [EducationController::class, 'store']);
    Route::put('/education/{education}', [EducationController::class, 'update']);
    Route::delete('/education/{education}', [EducationController::class, 'destroy']);

    // Experience
    Route::get('/experience', [ExperienceController::class, 'index']);
    Route::post('/experience', [ExperienceController::class, 'store']);
    Route::put('/experience/{experience}', [ExperienceController::class, 'update']);
    Route::delete('/experience/{experience}', [ExperienceController::class, 'destroy']);

    // Project
    Route::get('/project', [ProjectController::class, 'index']);
    Route::post('/project', [ProjectController::class, 'store']);
    Route::match(['put', 'post'], '/project/{project}', [ProjectController::class, 'update']);
    Route::delete('/project/{project}', [ProjectController::class, 'destroy']);

    // Volunteer Experience
    Route::get('/volunteer', [VolunteerExperienceController::class, 'index']);
    Route::post('/volunteer', [VolunteerExperienceController::class, 'store']);
    Route::put('/volunteer/{volunteer}', [VolunteerExperienceController::class, 'update']);
    Route::delete('/volunteer/{volunteer}', [VolunteerExperienceController::class, 'destroy']);

    // Language
    Route::get('/language/available', [LanguageController::class, 'available']);
    Route::get('/language', [LanguageController::class, 'index']);
    Route::post('/language', [LanguageController::class, 'store']);
    Route::put('/language/{languageId}', [LanguageController::class, 'update']);
    Route::delete('/language/{languageId}', [LanguageController::class, 'destroy']);

    // Skill
    Route::get('/skill/available', [SkillController::class, 'available']);
    Route::get('/skill', [SkillController::class, 'index']);
    Route::post('/skill', [SkillController::class, 'store']);
    Route::put('/skill/{skillId}', [SkillController::class, 'update']);
    Route::delete('/skill/{skillId}', [SkillController::class, 'destroy']);

    // Reactions — toggle (like/love)
    Route::post('/reactions/toggle', [ReactionController::class, 'toggle']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});

