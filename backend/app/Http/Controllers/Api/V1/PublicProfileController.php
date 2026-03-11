<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\PublicProfileCardResource;
use App\Services\PublicProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * PublicProfileController — Unauthenticated endpoints for browsing profiles.
 *
 * These routes are public (no auth:sanctum middleware) so that:
 *   - Visitors can browse profiles on the homepage
 *   - Recruiters can view CVs without creating an account
 *   - CV links can be shared externally (e.g., on LinkedIn, email)
 *
 * Rate-limited via 'throttle:api' to prevent scraping.
 */
class PublicProfileController extends BaseApiController
{
    protected PublicProfileService $service;

    public function __construct(PublicProfileService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/v1/public/profiles
     *
     * List all public profiles with pagination.
     * Supports query params:
     *   ?search=    — Case-insensitive search across name, headline, location, summary
     *   ?per_page=  — Results per page (1–24, default 12)
     *   ?sort_by=   — Sort key: newest|oldest|name_asc|name_desc|most_skills|most_experience
     *   ?location=  — Filter by exact location (case-insensitive)
     */
    public function index(Request $request): JsonResponse
    {
        $search        = $request->query('search');
        $perPage       = min((int) ($request->query('per_page', 12)), 24);
        $sortBy        = $request->query('sort_by', 'newest');
        $location      = $request->query('location');
        $excludeUserId = $request->query('exclude_user_id') ? (int) $request->query('exclude_user_id') : null;

        $profiles = $this->service->getProfiles($search, $perPage, $sortBy, $location, $excludeUserId);

        return response()->json([
            'success'    => true,
            'message'    => 'Success',
            'data'       => PublicProfileCardResource::collection($profiles->items()),
            'pagination' => [
                'total'        => $profiles->total(),
                'per_page'     => $profiles->perPage(),
                'current_page' => $profiles->currentPage(),
                'last_page'    => $profiles->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/v1/public/profiles/locations
     *
     * Return a list of all unique locations for the filter dropdown.
     * Lightweight endpoint — just an array of strings.
     */
    public function locations(): JsonResponse
    {
        $locations = $this->service->getAvailableLocations();

        return response()->json([
            'success' => true,
            'data'    => $locations,
        ]);
    }

    /**
     * GET /api/v1/public/profiles/{slug}
     *
     * Get a single user's full CV for public viewing.
     * Uses the user's slug (e.g., "yousef-bzo") for SEO-friendly URLs.
     * Returns the same ProfileResource used by the authenticated CV endpoint,
     * plus the user's name and slug.
     */
    public function show(string $slug): JsonResponse
    {
        $profile = $this->service->getPublicCV($slug);

        if (! $profile) {
            return $this->notFoundResponse('Profile not found or not yet completed.');
        }

        $data = ProfileResource::make($profile)->toArray(request());
        $data['user_name'] = $profile->user?->name;
        $data['user_slug'] = $profile->user?->slug;

        return $this->successResponse($data);
    }
}
