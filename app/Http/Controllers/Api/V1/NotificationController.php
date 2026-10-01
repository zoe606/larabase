<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Notification\MarkAllAsRead;
use App\Actions\Notification\MarkNotificationAsRead;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Queries\Notification\ListNotificationsQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index(Request $request): JsonResponse
    {
        $query = new ListNotificationsQuery($request->user()->id);

        $paginated = $query->handle([
            'unread_only' => $request->boolean('unread_only'),
            'type' => $request->type,
            'per_page' => $request->per_page ?? 15,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Success',
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'from' => $paginated->firstItem(),
                'to' => $paginated->lastItem(),
            ],
            'links' => [
                'first' => $paginated->url(1),
                'last' => $paginated->url($paginated->lastPage()),
                'prev' => $paginated->previousPageUrl(),
                'next' => $paginated->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markRead(string $id, Request $request): JsonResponse
    {
        $action = new MarkNotificationAsRead($id, $request->user()->id);
        $result = $action->handle();

        if (! $result) {
            return ApiResponse::notFound('Notification not found.');
        }

        return ApiResponse::success(null, 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $action = new MarkAllAsRead($request->user()->id);
        $count = $action->handle();

        return ApiResponse::success(
            ['marked_count' => $count],
            "{$count} notifications marked as read."
        );
    }
}
