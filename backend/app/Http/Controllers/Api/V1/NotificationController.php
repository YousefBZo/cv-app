<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\NotificationResource;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * NotificationController — In-app notification management.
 *
 * Endpoints:
 *   GET  /api/v1/notifications            — List notifications (paginated)
 *   GET  /api/v1/notifications/unread-count — Get unread count
 *   PUT  /api/v1/notifications/{id}/read  — Mark single notification as read
 *   PUT  /api/v1/notifications/read-all   — Mark all as read
 */
class NotificationController extends BaseApiController
{
    public function __construct(
        protected NotificationService $service,
    ) {}

    /**
     * GET /api/v1/notifications
     *
     * Paginated list of notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->query('per_page', 20)), 50);
        $notifications = $this->service->getForUser($request->user()->id, $perPage);

        return response()->json([
            'success'    => true,
            'message'    => 'Success',
            'data'       => NotificationResource::collection($notifications->items()),
            'pagination' => [
                'total'        => $notifications->total(),
                'per_page'     => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/v1/notifications/unread-count
     *
     * Returns the number of unread notifications (for the bell badge).
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = $this->service->unreadCount($request->user()->id);

        return $this->successResponse(['count' => $count]);
    }

    /**
     * PUT /api/v1/notifications/{id}/read
     *
     * Mark a single notification as read.
     */
    public function markAsRead(int $id, Request $request): JsonResponse
    {
        $success = $this->service->markAsRead($id, $request->user()->id);

        if (! $success) {
            return $this->notFoundResponse('Notification not found.');
        }

        return $this->successResponse(null, 'Notification marked as read.');
    }

    /**
     * PUT /api/v1/notifications/read-all
     *
     * Mark all unread notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $count = $this->service->markAllAsRead($request->user()->id);

        return $this->successResponse(
            ['marked' => $count],
            "$count notification(s) marked as read."
        );
    }
}
