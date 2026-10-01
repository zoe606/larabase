<?php

declare(strict_types=1);

use App\Models\User;
use App\Queries\Notification\GetUnreadCountQuery;
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
function createTestNotification(
    User $user,
    bool $isRead = false
): DatabaseNotification {
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

describe('GetUnreadCountQuery', function () {
    it('returns 0 for user with no notifications', function () {
        $query = new GetUnreadCountQuery($this->user->id);
        $count = $query->handle();

        expect($count)->toBe(0);
    });

    it('returns 0 for non-existent user', function () {
        $query = new GetUnreadCountQuery(99999);
        $count = $query->handle();

        expect($count)->toBe(0);
    });

    it('returns correct count of unread notifications', function () {
        // Create 5 unread notifications
        for ($i = 0; $i < 5; $i++) {
            createTestNotification($this->user, false);
        }

        $query = new GetUnreadCountQuery($this->user->id);
        $count = $query->handle();

        expect($count)->toBe(5);
    });

    it('does not count read notifications', function () {
        // Create 3 unread notifications
        for ($i = 0; $i < 3; $i++) {
            createTestNotification($this->user, false);
        }

        // Create 2 read notifications
        for ($i = 0; $i < 2; $i++) {
            createTestNotification($this->user, true);
        }

        $query = new GetUnreadCountQuery($this->user->id);
        $count = $query->handle();

        expect($count)->toBe(3);
    });

    it('only counts user own notifications', function () {
        $otherUser = User::factory()->create();

        // Create 4 unread for this user
        for ($i = 0; $i < 4; $i++) {
            createTestNotification($this->user, false);
        }

        // Create 3 unread for other user
        for ($i = 0; $i < 3; $i++) {
            createTestNotification($otherUser, false);
        }

        $query = new GetUnreadCountQuery($this->user->id);
        $count = $query->handle();

        expect($count)->toBe(4);
    });

    it('returns 0 when all notifications are read', function () {
        // Create only read notifications
        for ($i = 0; $i < 5; $i++) {
            createTestNotification($this->user, true);
        }

        $query = new GetUnreadCountQuery($this->user->id);
        $count = $query->handle();

        expect($count)->toBe(0);
    });

    it('ignores filters parameter', function () {
        // Create 3 unread notifications
        for ($i = 0; $i < 3; $i++) {
            createTestNotification($this->user, false);
        }

        $query = new GetUnreadCountQuery($this->user->id);

        // Even with filters, should return the count
        $count = $query->handle(['some_filter' => 'value']);

        expect($count)->toBe(3);
    });

    it('handles large number of notifications', function () {
        // Create 100 unread notifications
        for ($i = 0; $i < 100; $i++) {
            createTestNotification($this->user, false);
        }

        $query = new GetUnreadCountQuery($this->user->id);
        $count = $query->handle();

        expect($count)->toBe(100);
    });

    it('updates count after notification is marked as read', function () {
        // Create 3 unread notifications
        $notifications = [];
        for ($i = 0; $i < 3; $i++) {
            $notifications[] = createTestNotification($this->user, false);
        }

        $query = new GetUnreadCountQuery($this->user->id);

        // Initial count
        expect($query->handle())->toBe(3);

        // Mark one as read
        $notifications[0]->update(['read_at' => now()]);

        // Count should decrease
        expect($query->handle())->toBe(2);
    });

    it('works with different notification types', function () {
        // Create notifications of different types
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

        $query = new GetUnreadCountQuery($this->user->id);
        $count = $query->handle();

        expect($count)->toBe(3);
    });
});
