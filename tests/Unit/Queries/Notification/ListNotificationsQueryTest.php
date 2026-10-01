<?php

declare(strict_types=1);

use App\Models\User;
use App\Queries\Notification\ListNotificationsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

/**
 * Helper to create a notification for testing
 */
function createNotification(
    User $user,
    array $data = [],
    ?string $type = null,
    ?string $readAt = null
): DatabaseNotification {
    return DatabaseNotification::create([
        'id' => Str::uuid()->toString(),
        'type' => $type ?? 'App\\Notifications\\AccountUpdatedNotification',
        'notifiable_type' => 'App\\Models\\User',
        'notifiable_id' => $user->id,
        'data' => array_merge([
            'title' => 'Test Title',
            'message' => 'Test Message',
            'link' => '/test-link',
        ], $data),
        'read_at' => $readAt,
    ]);
}

describe('ListNotificationsQuery', function () {
    it('returns paginated results', function () {
        // Create 15 notifications
        for ($i = 0; $i < 15; $i++) {
            createNotification($this->user, ['title' => "Notification $i"]);
        }

        $query = new ListNotificationsQuery($this->user->id);
        $result = $query->handle();

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($result->total())->toBe(15)
            ->and($result->perPage())->toBe(15);
    });

    it('returns empty for user with no notifications', function () {
        $query = new ListNotificationsQuery($this->user->id);
        $result = $query->handle();

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($result->total())->toBe(0)
            ->and($result->count())->toBe(0);
    });

    it('returns empty for non-existent user', function () {
        $query = new ListNotificationsQuery(99999);
        $result = $query->handle();

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($result->total())->toBe(0);
    });

    it('filters by unread_only when specified', function () {
        // Create 3 unread notifications
        for ($i = 0; $i < 3; $i++) {
            createNotification($this->user, ['title' => "Unread $i"]);
        }

        // Create 2 read notifications
        for ($i = 0; $i < 2; $i++) {
            createNotification($this->user, ['title' => "Read $i"], null, now()->toDateTimeString());
        }

        $query = new ListNotificationsQuery($this->user->id);

        // Without filter - returns all
        $allResult = $query->handle(['unread_only' => false]);
        expect($allResult->total())->toBe(5);

        // With unread_only filter
        $unreadResult = $query->handle(['unread_only' => true]);
        expect($unreadResult->total())->toBe(3);
    });

    it('orders by created_at desc', function () {
        // Create notifications with different timestamps
        $olderNotification = createNotification($this->user, ['title' => 'Older']);
        $olderNotification->update(['created_at' => now()->subHour()]);

        $newerNotification = createNotification($this->user, ['title' => 'Newer']);
        $newerNotification->update(['created_at' => now()]);

        $query = new ListNotificationsQuery($this->user->id);
        $result = $query->handle();

        expect($result->first()['title'])->toBe('Newer');
    });

    it('returns transformed data with correct structure', function () {
        $notification = createNotification($this->user, [
            'title' => 'Account Alert',
            'message' => 'Your account is running low',
            'link' => '/accounts/1',
        ], 'App\\Notifications\\AccountUpdatedNotification');

        $query = new ListNotificationsQuery($this->user->id);
        $result = $query->handle();

        $item = $result->first();

        expect($item)->toHaveKeys(['id', 'type_key', 'title', 'message', 'is_read', 'time_ago', 'link', 'created_at'])
            ->and($item['id'])->toBe($notification->id)
            ->and($item['type_key'])->toContain('account')
            ->and($item['title'])->toBe('Account Alert')
            ->and($item['message'])->toBe('Your account is running low')
            ->and($item['is_read'])->toBeFalse()
            ->and($item['time_ago'])->toBeString()
            ->and($item['link'])->toBe('/accounts/1')
            ->and($item['created_at'])->toBeString();
    });

    it('correctly identifies read notifications', function () {
        // Create unread notification
        $unread = createNotification($this->user, ['title' => 'Unread']);

        // Create read notification
        $read = createNotification($this->user, ['title' => 'Read'], null, now()->toDateTimeString());

        $query = new ListNotificationsQuery($this->user->id);
        $result = $query->handle();

        $notifications = $result->getCollection();
        $readNotification = $notifications->firstWhere('title', 'Read');
        $unreadNotification = $notifications->firstWhere('title', 'Unread');

        expect($readNotification['is_read'])->toBeTrue()
            ->and($unreadNotification['is_read'])->toBeFalse();
    });

    it('only returns user own notifications', function () {
        $otherUser = User::factory()->create();

        // Create notifications for both users
        createNotification($this->user, ['title' => 'My Notification']);
        createNotification($otherUser, ['title' => 'Other User Notification']);

        $query = new ListNotificationsQuery($this->user->id);
        $result = $query->handle();

        expect($result->total())->toBe(1)
            ->and($result->first()['title'])->toBe('My Notification');
    });

    it('handles null data fields gracefully', function () {
        $notification = DatabaseNotification::create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\\Notifications\\TestNotification',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $this->user->id,
            'data' => [], // Empty data
            'read_at' => null,
        ]);

        $query = new ListNotificationsQuery($this->user->id);
        $result = $query->handle();

        $item = $result->first();

        expect($item['title'])->toBeNull()
            ->and($item['message'])->toBeNull()
            ->and($item['link'])->toBeNull();
    });

    it('respects per_page parameter', function () {
        for ($i = 0; $i < 20; $i++) {
            createNotification($this->user, ['title' => "Notification $i"]);
        }

        $query = new ListNotificationsQuery($this->user->id);
        $result = $query->handle(['per_page' => 5]);

        expect($result->perPage())->toBe(5)
            ->and($result->count())->toBe(5)
            ->and($result->total())->toBe(20);
    });

    it('uses default per_page of 15', function () {
        for ($i = 0; $i < 20; $i++) {
            createNotification($this->user, ['title' => "Notification $i"]);
        }

        $query = new ListNotificationsQuery($this->user->id);
        $result = $query->handle();

        expect($result->perPage())->toBe(15);
    });

    it('filters by notification type when specified', function () {
        createNotification($this->user, ['title' => 'Account Alert'], 'App\\Notifications\\AccountUpdatedNotification');
        createNotification($this->user, ['title' => 'Service Alert'], 'App\\Notifications\\PasswordResetNotification');

        $query = new ListNotificationsQuery($this->user->id);
        $result = $query->handle(['type' => 'AccountUpdated']);

        expect($result->total())->toBe(1)
            ->and($result->first()['title'])->toBe('Account Alert');
    });

    it('extracts type_key correctly from notification class name', function () {
        createNotification(
            $this->user,
            ['title' => 'Test'],
            'App\\Notifications\\AccountUpdatedNotification'
        );

        $query = new ListNotificationsQuery($this->user->id);
        $result = $query->handle();

        // The type key should be snake_case of the class name
        expect($result->first()['type_key'])->toContain('account');
    });

    it('handles multiple notifications correctly', function () {
        // Create various notifications
        for ($i = 0; $i < 10; $i++) {
            createNotification(
                $this->user,
                ['title' => "Notification $i"],
                'App\\Notifications\\TestNotification',
                $i % 2 === 0 ? null : now()->toDateTimeString()
            );
        }

        $query = new ListNotificationsQuery($this->user->id);
        $allResult = $query->handle();
        $unreadResult = $query->handle(['unread_only' => true]);

        expect($allResult->total())->toBe(10)
            ->and($unreadResult->total())->toBe(5);
    });

    it('returns valid ISO8601 created_at timestamp', function () {
        createNotification($this->user, ['title' => 'Test']);

        $query = new ListNotificationsQuery($this->user->id);
        $result = $query->handle();

        $createdAt = $result->first()['created_at'];

        // Verify it's a valid ISO8601 timestamp
        expect($createdAt)->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
    });
});
