<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Notification\MarkAllAsRead;
use App\Actions\Notification\MarkNotificationAsRead;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = $user->notifications();

        // Filter by unread only
        if ($request->boolean('unread_only')) {
            $query = $user->unreadNotifications();
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', 'like', '%'.$request->input('type').'%');
        }

        $notifications = $query->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return Inertia::render('notifications/Index', [
            'notifications' => NotificationResource::collection($notifications),
            'filters' => [
                'unread_only' => $request->boolean('unread_only'),
                'type' => $request->input('type'),
            ],
        ]);
    }

    public function markAsRead(Request $request, string $id): RedirectResponse
    {
        $action = new MarkNotificationAsRead($id, $request->user()->id);
        $action->handle();

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $action = new MarkAllAsRead($request->user()->id);
        $count = $action->handle();

        return back()->with('success', "{$count} notifications marked as read.");
    }
}
