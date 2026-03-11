<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Profile;
use App\Models\Reaction;

/**
 * ReactionService — Business logic for the reaction system.
 *
 * DESIGN:
 *   - 5 reaction types: like, love, celebrate, insightful, curious
 *     (follows LinkedIn's professional palette — each serves a distinct purpose)
 *   - Toggle pattern: calling toggle() twice with same type removes the reaction.
 *   - Switching type: if user liked and now sends love, it swaps to love.
 *   - Notifications are created when a new reaction is added (not on removal).
 *   - Users cannot react to their own profile (enforced here).
 */
class ReactionService
{
    /**
     * All valid reaction types.
     */
    public const TYPES = ['like', 'love', 'celebrate', 'insightful', 'curious'];
    /**
     * Toggle a reaction on a profile.
     *
     * @param  int    $userId    The authenticated user reacting
     * @param  int    $profileId The target profile
     * @param  string $type      'like' or 'love'
     * @return array  { action: 'added'|'removed'|'switched', reaction?: Reaction }
     */
    public function toggle(int $userId, int $profileId, string $type): array
    {
        // Prevent self-reaction
        $profile = Profile::findOrFail($profileId);
        if ($profile->user_id === $userId) {
            return ['action' => 'self', 'reaction' => null];
        }

        $existing = Reaction::where('user_id', $userId)
            ->where('profile_id', $profileId)
            ->first();

        // Case 1: No existing reaction → create it
        if (! $existing) {
            $reaction = Reaction::create([
                'user_id'    => $userId,
                'profile_id' => $profileId,
                'type'       => $type,
            ]);

            // Notify the profile owner
            $this->notifyProfileOwner($reaction);

            return ['action' => 'added', 'reaction' => $reaction];
        }

        // Case 2: Same type → remove (toggle off)
        if ($existing->type === $type) {
            $existing->delete();
            return ['action' => 'removed', 'reaction' => null];
        }

        // Case 3: Different type → switch (like↔love)
        $existing->update(['type' => $type]);

        // Notify about the upgrade/change
        $this->notifyProfileOwner($existing->fresh());

        return ['action' => 'switched', 'reaction' => $existing->fresh()];
    }

    /**
     * Get reaction summary for a profile.
     * Returns counts per type + the current user's reaction (if any).
     */
    public function getProfileReactions(int $profileId, ?int $userId = null): array
    {
        $counts = Reaction::where('profile_id', $profileId)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        $userReaction = null;
        if ($userId) {
            $userReaction = Reaction::where('profile_id', $profileId)
                ->where('user_id', $userId)
                ->value('type');
        }

        $total = array_sum($counts);

        return [
            'likes'       => $counts['like'] ?? 0,
            'loves'       => $counts['love'] ?? 0,
            'celebrates'  => $counts['celebrate'] ?? 0,
            'insightfuls' => $counts['insightful'] ?? 0,
            'curious'     => $counts['curious'] ?? 0,
            'total'         => $total,
            'user_reaction' => $userReaction,
        ];
    }

    /**
     * Create a notification for the profile owner about a new reaction.
     */
    private function notifyProfileOwner(Reaction $reaction): void
    {
        $reactor = $reaction->user;
        $profile = $reaction->profile;

        // Don't notify if somehow the reactor is the profile owner
        if ($profile->user_id === $reaction->user_id) {
            return;
        }

        Notification::create([
            'user_id' => $profile->user_id,
            'type'    => 'reaction',
            'data'    => [
                'reactor_name'  => $reactor->name,
                'reactor_slug'  => $reactor->slug,
                'reaction_type' => $reaction->type,
                'profile_id'    => $profile->id,
            ],
        ]);
    }
}
