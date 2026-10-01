<?php

declare(strict_types=1);

namespace App\Actions\Notification;

use App\Contracts\ActionInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class MarkAllAsRead implements ActionInterface
{
    public function __construct(
        private readonly int $userId
    ) {}

    /**
     * Mark all user notifications as read.
     *
     * @param  array<string, mixed>  $data
     * @return int Number of notifications marked as read
     */
    public function handle(array $data = []): int
    {
        return DB::transaction(function (): int {
            $user = User::find($this->userId);

            if ($user === null) {
                return 0;
            }

            $count = $user->unreadNotifications()->count();
            $user->unreadNotifications->markAsRead();

            return $count;
        });
    }
}
