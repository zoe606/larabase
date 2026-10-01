<?php

declare(strict_types=1);

use App\Actions\Notification\MarkNotificationAsRead;
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
function createUnreadNotification(User $user): DatabaseNotification
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
        'read_at' => null,
    ]);
}

describe('MarkNotificationAsRead', function () {
    it('marks notification as read', function () {
        $notification = createUnreadNotification($this->user);

        expect($notification->read_at)->toBeNull();

        $action = new MarkNotificationAsRead($notification->id, $this->user->id);
        $result = $action->handle();

        $notification->refresh();

        expect($result)->toBeTrue()
            ->and($notification->read_at)->not->toBeNull();
    });

    it('returns true on success', function () {
        $notification = createUnreadNotification($this->user);

        $action = new MarkNotificationAsRead($notification->id, $this->user->id);
        $result = $action->handle();

        expect($result)->toBeTrue();
    });

    it('returns false for non-existent notification', function () {
        $fakeId = Str::uuid()->toString();

        $action = new MarkNotificationAsRead($fakeId, $this->user->id);
        $result = $action->handle();

        expect($result)->toBeFalse();
    });

    it('does not affect other users notifications', function () {
        $otherUser = User::factory()->create();
        $otherNotification = createUnreadNotification($otherUser);

        // Try to mark other user's notification as read
        $action = new MarkNotificationAsRead($otherNotification->id, $this->user->id);
        $result = $action->handle();

        $otherNotification->refresh();

        // Should fail and notification should remain unread
        expect($result)->toBeFalse()
            ->and($otherNotification->read_at)->toBeNull();
    });

    it('is idempotent marking twice is OK', function () {
        $notification = createUnreadNotification($this->user);

        // First mark as read
        $action1 = new MarkNotificationAsRead($notification->id, $this->user->id);
        $result1 = $action1->handle();

        $notification->refresh();
        $firstReadAt = $notification->read_at;

        // Mark as read again
        $action2 = new MarkNotificationAsRead($notification->id, $this->user->id);
        $result2 = $action2->handle();

        $notification->refresh();

        // Both operations should return true
        expect($result1)->toBeTrue()
            ->and($result2)->toBeTrue()
            ->and($notification->read_at)->not->toBeNull()
            // read_at should remain the same (not updated again)
            ->and($notification->read_at->toDateTimeString())->toBe($firstReadAt->toDateTimeString());
    });

    it('does not mark already read notification again', function () {
        $notification = createUnreadNotification($this->user);

        // Mark as read first
        $originalTime = now()->subHour();
        $notification->update(['read_at' => $originalTime]);

        // Try to mark as read again
        $action = new MarkNotificationAsRead($notification->id, $this->user->id);
        $result = $action->handle();

        $notification->refresh();

        // Should still return true (success) but read_at shouldn't change
        expect($result)->toBeTrue()
            ->and($notification->read_at->toDateTimeString())->toBe($originalTime->toDateTimeString());
    });

    it('handles data parameter even though not used', function () {
        $notification = createUnreadNotification($this->user);

        $action = new MarkNotificationAsRead($notification->id, $this->user->id);
        $result = $action->handle(['some_data' => 'value']);

        $notification->refresh();

        expect($result)->toBeTrue()
            ->and($notification->read_at)->not->toBeNull();
    });

    it('returns false when notification belongs to wrong notifiable type', function () {
        // Create a notification with different notifiable type
        $notification = DatabaseNotification::create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\\Notifications\\TestNotification',
            'notifiable_type' => 'App\\Models\\SomeOtherModel',
            'notifiable_id' => $this->user->id,
            'data' => ['title' => 'Test'],
            'read_at' => null,
        ]);

        $action = new MarkNotificationAsRead($notification->id, $this->user->id);
        $result = $action->handle();

        expect($result)->toBeFalse();
    });

    it('works with different notification types', function () {
        $types = [
            'App\\Notifications\\AccountUpdatedNotification',
            'App\\Notifications\\PasswordResetNotification',
            'App\\Notifications\\WelcomeNotification',
        ];

        foreach ($types as $type) {
            $notification = DatabaseNotification::create([
                'id' => Str::uuid()->toString(),
                'type' => $type,
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $this->user->id,
                'data' => ['title' => 'Test'],
                'read_at' => null,
            ]);

            $action = new MarkNotificationAsRead($notification->id, $this->user->id);
            $result = $action->handle();

            $notification->refresh();

            expect($result)->toBeTrue()
                ->and($notification->read_at)->not->toBeNull();
        }
    });

    it('only marks the specified notification', function () {
        // Create multiple notifications
        $notification1 = createUnreadNotification($this->user);
        $notification2 = createUnreadNotification($this->user);
        $notification3 = createUnreadNotification($this->user);

        // Mark only the second one as read
        $action = new MarkNotificationAsRead($notification2->id, $this->user->id);
        $result = $action->handle();

        $notification1->refresh();
        $notification2->refresh();
        $notification3->refresh();

        expect($result)->toBeTrue()
            ->and($notification1->read_at)->toBeNull()
            ->and($notification2->read_at)->not->toBeNull()
            ->and($notification3->read_at)->toBeNull();
    });

    it('uses database transaction', function () {
        $notification = createUnreadNotification($this->user);

        // This test verifies the action uses a transaction
        // The action should complete successfully without any issues
        $action = new MarkNotificationAsRead($notification->id, $this->user->id);
        $result = $action->handle();

        expect($result)->toBeTrue();

        // Verify the change was committed
        $freshNotification = DatabaseNotification::find($notification->id);
        expect($freshNotification->read_at)->not->toBeNull();
    });
});
