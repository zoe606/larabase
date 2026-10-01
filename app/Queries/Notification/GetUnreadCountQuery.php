<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Contracts\QueryInterface;
use App\Models\User;

final class GetUnreadCountQuery implements QueryInterface
{
    public function __construct(
        private readonly int $userId
    ) {}

    /**
     * Get the count of unread notifications for a user.
     *
     * @param  array<string, mixed>  $filters
     */
    public function handle(array $filters = []): int
    {
        $user = User::find($this->userId);

        if (! $user) {
            return 0;
        }

        return $user->unreadNotifications()->count();
    }
}
