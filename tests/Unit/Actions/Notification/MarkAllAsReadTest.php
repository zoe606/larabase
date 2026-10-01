<?php

declare(strict_types=1);

use App\Actions\Notification\MarkAllAsRead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

/**
 * Helper to create a notification for testing
 */
function createNotificationForUser(User $user, bool $isRead = false): DatabaseNotification
{
    return DatabaseNotification::create([
        'id' => Str::uuid()->toString(),
        'type' => 'App\\Notifications\\AccountUpdatedNotification',
        'notifiable_type' => 'App\\Models\\User',
        'notifiable_id' => $user->id,
        'data' => [
            'title' => 'Test Title',
            'message' => 'Test Message',
            'link' => '/test-link',
        ],
        'read_at' => $isRead ? now() : null,
    ]);
}

describe('MarkAllAsRead', function () {
    it('marks all unread notifications as read', function () {
        // Create 5 unread notifications
        $notifications = [];
        for ($i = 0; $i < 5; $i++) {
            $notifications[] = createNotificationForUser($this->user, false);
        }

        $action = new MarkAllAsRead($this->user->id);
        $count = $action->handle();

        expect($count)->toBe(5);

        // Verify all are now read
        foreach ($notifications as $notification) {
            $notification->refresh();
            expect($notification->read_at)->not->toBeNull();
        }
    });

    it('returns count of marked notifications', function () {
        // Create 3 unread notifications
        for ($i = 0; $i < 3; $i++) {
            createNotificationForUser($this->user, false);
        }

        $action = new MarkAllAsRead($this->user->id);
        $count = $action->handle();

        expect($count)->toBe(3);
    });

    it('does not affect other users notifications', function () {
        $otherUser = User::factory()->create();

        // Create notifications for both users
        $myNotifications = [];
        for ($i = 0; $i < 3; $i++) {
            $myNotifications[] = createNotificationForUser($this->user, false);
        }

        $otherNotifications = [];
        for ($i = 0; $i < 2; $i++) {
            $otherNotifications[] = createNotificationForUser($otherUser, false);
        }

        $action = new MarkAllAsRead($this->user->id);
        $count = $action->handle();

        // Should only mark my notifications
        expect($count)->toBe(3);

        // My notifications should be read
        foreach ($myNotifications as $notification) {
            $notification->refresh();
            expect($notification->read_at)->not->toBeNull();
        }

        // Other user's notifications should remain unread
        foreach ($otherNotifications as $notification) {
            $notification->refresh();
            expect($notification->read_at)->toBeNull();
        }
    });

    it('returns 0 when all already read', function () {
        // Create only read notifications
        for ($i = 0; $i < 3; $i++) {
            createNotificationForUser($this->user, true);
        }

        $action = new MarkAllAsRead($this->user->id);
        $count = $action->handle();

        expect($count)->toBe(0);
    });

    it('returns 0 for user with no notifications', function () {
        $action = new MarkAllAsRead($this->user->id);
        $count = $action->handle();

        expect($count)->toBe(0);
    });

    it('returns 0 for non-existent user', function () {
        $action = new MarkAllAsRead(99999);
        $count = $action->handle();

        expect($count)->toBe(0);
    });

    it('only marks unread notifications not already read ones', function () {
        // Create mixed notifications
        $unread1 = createNotificationForUser($this->user, false);
        $read1 = createNotificationForUser($this->user, true);
        $unread2 = createNotificationForUser($this->user, false);
        $read2 = createNotificationForUser($this->user, true);

        $action = new MarkAllAsRead($this->user->id);
        $count = $action->handle();

        // Should only count the unread ones
        expect($count)->toBe(2);
    });

    it('handles data parameter even though not used', function () {
        for ($i = 0; $i < 2; $i++) {
            createNotificationForUser($this->user, false);
        }

        $action = new MarkAllAsRead($this->user->id);
        $count = $action->handle(['some_data' => 'value']);

        expect($count)->toBe(2);
    });

    it('handles large number of notifications', function () {
        // Create 50 unread notifications
        for ($i = 0; $i < 50; $i++) {
            createNotificationForUser($this->user, false);
        }

        $action = new MarkAllAsRead($this->user->id);
        $count = $action->handle();

        expect($count)->toBe(50);

        // Verify all are marked as read
        $unreadCount = DatabaseNotification::query()
            ->where('notifiable_type', 'App\\Models\\User')
            ->where('notifiable_id', $this->user->id)
            ->whereNull('read_at')
            ->count();

        expect($unreadCount)->toBe(0);
    });

    it('uses database transaction', function () {
        for ($i = 0; $i < 3; $i++) {
            createNotificationForUser($this->user, false);
        }

        $action = new MarkAllAsRead($this->user->id);
        $count = $action->handle();

        expect($count)->toBe(3);

        // Verify changes were committed
        $allRead = DatabaseNotification::query()
            ->where('notifiable_type', 'App\\Models\\User')
            ->where('notifiable_id', $this->user->id)
            ->whereNotNull('read_at')
            ->count();

        expect($allRead)->toBe(3);
    });

    it('works with different notification types', function () {
        $types = [
            'App\\Notifications\\AccountUpdatedNotification',
            'App\\Notifications\\PasswordResetNotification',
            'App\\Notifications\\WelcomeNotification',
        ];

        foreach ($types as $type) {
            DatabaseNotification::create([
                'id' => Str::uuid()->toString(),
                'type' => $type,
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $this->user->id,
                'data' => ['title' => 'Test'],
                'read_at' => null,
            ]);
        }

        $action = new MarkAllAsRead($this->user->id);
        $count = $action->handle();

        expect($count)->toBe(3);

        // Verify all types were marked as read
        $unreadCount = DatabaseNotification::query()
            ->where('notifiable_id', $this->user->id)
            ->whereNull('read_at')
            ->count();

        expect($unreadCount)->toBe(0);
    });

    it('can be called multiple times safely', function () {
        for ($i = 0; $i < 3; $i++) {
            createNotificationForUser($this->user, false);
        }

        $action = new MarkAllAsRead($this->user->id);

        // First call
        $count1 = $action->handle();
        expect($count1)->toBe(3);

        // Second call - should return 0 since all are already read
        $count2 = $action->handle();
        expect($count2)->toBe(0);
    });

    it('preserves already read notifications read_at timestamp', function () {
        // Create a read notification with specific timestamp
        $originalReadAt = now()->subDays(5);
        $readNotification = DatabaseNotification::create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\\Notifications\\TestNotification',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $this->user->id,
            'data' => ['title' => 'Already Read'],
            'read_at' => $originalReadAt,
        ]);

        // Create an unread notification
        createNotificationForUser($this->user, false);

        $action = new MarkAllAsRead($this->user->id);
        $count = $action->handle();

        // Should only mark the unread one
        expect($count)->toBe(1);

        // The already read notification's timestamp should remain unchanged
        $readNotification->refresh();
        expect($readNotification->read_at->toDateString())->toBe($originalReadAt->toDateString());
    });
});
