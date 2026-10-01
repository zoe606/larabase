<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Contracts\QueryInterface;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListNotificationsQuery implements QueryInterface
{
    public function __construct(
        private readonly int $userId
    ) {}

    /**
     * List notifications for a user with optional filters.
     *
     * @param  array{unread_only?: bool, type?: string|null, per_page?: int}  $filters
     * @return LengthAwarePaginator<array{
     *     id: string,
     *     type_key: string,
     *     title: string|null,
     *     message: string|null,
     *     is_read: bool,
     *     time_ago: string,
     *     link: string|null,
     *     created_at: string
     * }>
     */
    public function handle(array $filters = []): LengthAwarePaginator
    {
        $user = User::find($this->userId);

        if (! $user) {
            return new LengthAwarePaginator([], 0, $filters['per_page'] ?? 15);
        }

        $query = ($filters['unread_only'] ?? false)
            ? $user->unreadNotifications()
            : $user->notifications();

        // Filter by notification type if provided
        if (! empty($filters['type'])) {
            $query->where('type', 'like', '%'.$filters['type'].'%');
        }

        $query->orderBy('created_at', 'desc');

        $perPage = $filters['per_page'] ?? 15;
        $paginated = $query->paginate($perPage);

        // Transform the notifications
        $transformed = $paginated->getCollection()->map(function ($notification) {
            /** @var \Illuminate\Notifications\DatabaseNotification $notification */
            $data = $notification->data;

            return [
                'id' => $notification->id,
                'type_key' => $this->extractTypeKey($notification->type),
                'title' => $data['title'] ?? null,
                'message' => $data['message'] ?? null,
                'is_read' => $notification->read_at !== null,
                'time_ago' => $notification->created_at->diffForHumans(),
                'link' => $data['link'] ?? null,
                'created_at' => $notification->created_at->toIso8601String(),
            ];
        });

        return new LengthAwarePaginator(
            $transformed,
            $paginated->total(),
            $paginated->perPage(),
            $paginated->currentPage(),
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Extract a short type key from the full notification class name.
     *
     * Example: 'App\Notifications\AccountUpdated' => 'account_depletion'
     */
    private function extractTypeKey(string $type): string
    {
        // Get the class name without namespace
        $className = class_basename($type);

        // Convert PascalCase to snake_case
        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
    }
}
