<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Profile;
use App\Models\ProfileView;
use App\Models\User;

/**
 * ProfileViewService — Handles tracking of profile/CV views.
 *
 * DESIGN:
 *   - De-duplicated for BOTH authenticated and anonymous users:
 *       • Authenticated: max 1 view per (viewer_id, profile) per day.
 *       • Anonymous: max 1 view per (IP address, profile) per day.
 *   - This prevents view count inflation on page reloads.
 *   - Notifies the profile owner when a logged-in user views their CV.
 *   - Does NOT notify for anonymous views (too noisy).
 *   - Does NOT notify when you view your own profile.
 */
class ProfileViewService
{
    /**
     * Record a profile view and optionally notify the owner.
     *
     * @param  int         $profileId  The profile being viewed
     * @param  int|null    $viewerId   The authenticated viewer (null for guests)
     * @param  string|null $ipAddress  The visitor's IP (for anonymous de-dup)
     * @return bool        True if a new view was recorded, false if duplicate
     */
    public function recordView(int $profileId, ?int $viewerId = null, ?string $ipAddress = null): bool
    {
        $profile = Profile::find($profileId);
        if (! $profile) {
            return false;
        }

        // Don't track self-views
        if ($viewerId && $profile->user_id === $viewerId) {
            return false;
        }

        $today = now()->toDateString();

        // De-duplicate: one view per (viewer OR IP, profile) per day
        if ($viewerId) {
            $exists = ProfileView::where('profile_id', $profileId)
                ->where('viewer_id', $viewerId)
                ->whereDate('viewed_at', $today)
                ->exists();

            if ($exists) {
                return false;
            }
        } else {
            // Anonymous user — de-duplicate by IP address
            if ($ipAddress) {
                $exists = ProfileView::where('profile_id', $profileId)
                    ->whereNull('viewer_id')
                    ->where('ip_address', $ipAddress)
                    ->whereDate('viewed_at', $today)
                    ->exists();

                if ($exists) {
                    return false;
                }
            }
        }

        ProfileView::create([
            'profile_id' => $profileId,
            'viewer_id'  => $viewerId,
            'ip_address' => $viewerId ? null : $ipAddress,
        ]);

        // Notify the profile owner only for authenticated viewers
        if ($viewerId) {
            // De-duplicate notifications too — max 1 view notification per viewer per profile per day
            $existingNotif = Notification::where('user_id', $profile->user_id)
                ->where('type', 'profile_view')
                ->whereDate('created_at', $today)
                ->whereJsonContains('data->viewer_slug', User::find($viewerId)?->slug)
                ->exists();

            if (! $existingNotif) {
                $viewer = User::find($viewerId);
                if ($viewer) {
                    Notification::create([
                        'user_id' => $profile->user_id,
                        'type'    => 'profile_view',
                        'data'    => [
                            'viewer_name' => $viewer->name,
                            'viewer_slug' => $viewer->slug,
                            'profile_id'  => $profile->id,
                        ],
                    ]);
                }
            }
        }

        return true;
    }

    /**
     * Get view count for a profile.
     */
    public function getViewCount(int $profileId): int
    {
        return ProfileView::where('profile_id', $profileId)->count();
    }
}
