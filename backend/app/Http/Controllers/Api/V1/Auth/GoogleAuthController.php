<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirect()
    {
        return response()->json([
            'url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl(),
        ]);
    }

    /**
     * Obtain the user information from Google.
     */
    public function callback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            // Check if user already exists
            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if (!$user) {
                // Generate a unique slug based on google name
                $slug = User::generateUniqueSlug($googleUser->getName());

                // Create a new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'slug' => $slug,
                    'password' => null, // No password for OAuth users
                ]);

                // Create an empty profile for the new user automatically
                Profile::create([
                    'user_id' => $user->id,
                ]);
            } else {
                // Update existing user with google_id/avatar if empty
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar()
                ]);
            }

            // Create token using Sanctum
            $token = $user->createToken('google_auth_token')->plainTextToken;

            // Redirect back to frontend with the token
            // Ensure FRONTEND_URL is loaded from .env
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            return redirect()->away($frontendUrl . '/oauth/callback?token=' . $token);

        } catch (\Exception $e) {
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            return redirect()->away($frontendUrl . '/login?error=GoogleAuthFailed');
        }
    }
}
