<?php

declare(strict_types=1);

namespace App\Actions\Notification;

use App\Contracts\ActionInterface;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;

final class MarkNotificationAsRead implements ActionInterface
{
    public function __construct(
        private readonly string $notificationId,
        private readonly int $userId
    ) {}

    /**
     * Mark a single notification as read.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data = []): bool
    {
        return DB::transaction(function (): bool {
            $notification = DatabaseNotification::query()
                ->where('id', $this->notificationId)
                ->where('notifiable_type', 'App\\Models\\User')
                ->where('notifiable_id', $this->userId)
                ->first();

            if ($notification === null) {
                return false;
            }

            if ($notification->read_at === null) {
                $notification->markAsRead();
            }

            return true;
        });
    }
}
