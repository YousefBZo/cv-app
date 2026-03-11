<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\ReactionService;
use App\Services\ProfileViewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ReactionController — Handles Like / Love reactions on profiles.
 *
 * Endpoints:
 *   POST /api/v1/reactions/toggle     — Toggle a reaction (like/love) on a profile
 *   GET  /api/v1/reactions/{profileId} — Get reaction summary for a profile
 *   POST /api/v1/profile-views/{profileId} — Record a profile view
 */
class ReactionController extends BaseApiController
{
    public function __construct(
        protected ReactionService $reactionService,
        protected ProfileViewService $viewService,
    ) {}

    /**
     * POST /api/v1/reactions/toggle
     *
     * Toggle a reaction on a profile. Body: { profile_id, type: 'like'|'love' }
     */
    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'profile_id' => 'required|integer|exists:profiles,id',
            'type'       => 'required|string|in:like,love,celebrate,insightful,curious',
        ]);

        $result = $this->reactionService->toggle(
            $request->user()->id,
            $request->input('profile_id'),
            $request->input('type'),
        );

        if ($result['action'] === 'self') {
            return $this->errorResponse('You cannot react to your own profile.', 422);
        }

        // Return fresh reaction counts alongside the action
        $reactions = $this->reactionService->getProfileReactions(
            $request->input('profile_id'),
            $request->user()->id,
        );

        return $this->successResponse([
            'action'    => $result['action'],
            'reactions' => $reactions,
        ], match ($result['action']) {
            'added'    => 'Reaction added.',
            'removed'  => 'Reaction removed.',
            'switched' => 'Reaction updated.',
        });
    }

    /**
     * GET /api/v1/reactions/{profileId}
     *
     * Get reaction summary for a profile. Works for both
     * authenticated and unauthenticated users (user_reaction will be null
     * for guests).
     */
    public function show(int $profileId, Request $request): JsonResponse
    {
        $userId = $request->user()?->id;
        $reactions = $this->reactionService->getProfileReactions($profileId, $userId);

        return $this->successResponse($reactions);
    }

    /**
     * POST /api/v1/profile-views/{profileId}
     *
     * Record a view on a profile. Both authenticated and anonymous users
     * are de-duplicated (1 view/day per viewer or IP).
     * Creates a notification for the profile owner (auth viewers only).
     */
    public function recordView(int $profileId, Request $request): JsonResponse
    {
        $viewerId = $request->user()?->id;
        $recorded = $this->viewService->recordView($profileId, $viewerId, $request->ip());

        $viewCount = $this->viewService->getViewCount($profileId);

        return $this->successResponse([
            'recorded'   => $recorded,
            'view_count' => $viewCount,
        ]);
    }
}
