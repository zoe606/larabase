<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

/**
 * Helper to create a notification for testing
 */
function createDbNotification(
    User $user,
    array $data = [],
    ?string $type = null,
    bool $isRead = false
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
        'read_at' => $isRead ? now() : null,
    ]);
}

/*
|--------------------------------------------------------------------------
| Index Tests
|--------------------------------------------------------------------------
*/

describe('index', function () {
    it('redirects to login for unauthenticated users', function () {
        $response = $this->get(route('notifications.index'));

        $response->assertRedirect(route('login'));
    });

    it('returns Inertia page with notifications', function () {
        // Create notifications for the user
        for ($i = 0; $i < 3; $i++) {
            createDbNotification($this->user, ['title' => "Notification $i"]);
        }

        $response = $this->actingAs($this->user)
            ->get(route('notifications.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('notifications/Index')
                ->has('notifications.data', 3)
                ->has('filters')
            );
    });

    it('filters by unread_only when true', function () {
        // Create 2 unread notifications
        createDbNotification($this->user, ['title' => 'Unread 1']);
        createDbNotification($this->user, ['title' => 'Unread 2']);

        // Create 1 read notification
        createDbNotification($this->user, ['title' => 'Read 1'], null, true);

        // Test with unread_only filter
        $response = $this->actingAs($this->user)
            ->get(route('notifications.index', ['unread_only' => 1]));

        $response->assertOk();

        // Extract and verify Inertia page data from the response
        $content = $response->getContent();

        // Note: forward slashes are escaped in JSON, so we look for the escaped version
        expect($content)->toContain('notifications\\/Index');

        // Extract and verify page data
        if (preg_match('/data-page="([^"]+)"/m', $content, $matches)) {
            $pageData = json_decode(html_entity_decode($matches[1]), true);
            expect($pageData['component'])->toBe('notifications/Index');
            expect($pageData['props']['notifications']['data'])->toHaveCount(2);
            expect($pageData['props']['filters']['unread_only'])->toBeTrue();
        }
    });

    it('filters by notification type', function () {
        createDbNotification($this->user, ['title' => 'Account Alert'], 'App\\Notifications\\AccountUpdatedNotification');
        createDbNotification($this->user, ['title' => 'Service Alert'], 'App\\Notifications\\PasswordResetNotification');

        $response = $this->actingAs($this->user)
            ->get(route('notifications.index', ['type' => 'AccountUpdated']));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('notifications.data', 1)
                ->where('filters.type', 'AccountUpdated')
            );
    });

    it('returns correct notification structure', function () {
        createDbNotification($this->user, [
            'title' => 'Test Alert',
            'message' => 'Test message content',
            'link' => '/test/link',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('notifications.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('notifications/Index')
                ->has('notifications.data.0', fn (Assert $notification) => $notification
                    ->has('id')
                    ->has('type_key')
                    ->where('title', 'Test Alert')
                    ->where('message', 'Test message content')
                    ->has('is_read')
                    ->has('time_ago')
                    ->where('link', '/test/link')
                    ->has('created_at')
                )
            );
    });

    it('paginates notifications', function () {
        for ($i = 0; $i < 25; $i++) {
            createDbNotification($this->user, ['title' => "Notification $i"]);
        }

        $response = $this->actingAs($this->user)
            ->get(route('notifications.index', ['per_page' => 10]));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('notifications.data', 10)
            );
    });

    it('orders notifications by created_at desc', function () {
        // Create older notification
        $older = createDbNotification($this->user, ['title' => 'Older']);
        $older->update(['created_at' => now()->subHour()]);

        // Create newer notification
        $newer = createDbNotification($this->user, ['title' => 'Newer']);
        $newer->update(['created_at' => now()]);

        $response = $this->actingAs($this->user)
            ->get(route('notifications.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('notifications.data.0.title', 'Newer')
            );
    });

    it('only shows user own notifications', function () {
        $otherUser = User::factory()->create();

        createDbNotification($this->user, ['title' => 'My Notification']);
        createDbNotification($otherUser, ['title' => 'Other User Notification']);

        $response = $this->actingAs($this->user)
            ->get(route('notifications.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('notifications.data', 1)
                ->where('notifications.data.0.title', 'My Notification')
            );
    });

    it('handles empty notifications list', function () {
        $response = $this->actingAs($this->user)
            ->get(route('notifications.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('notifications/Index')
                ->has('notifications.data', 0)
            );
    });
});

/*
|--------------------------------------------------------------------------
| markAsRead Tests
|--------------------------------------------------------------------------
*/

describe('markAsRead', function () {
    it('redirects to login for unauthenticated users', function () {
        $notification = createDbNotification($this->user);

        $response = $this->post(route('notifications.read', $notification->id));

        $response->assertRedirect(route('login'));
    });

    it('marks a notification as read', function () {
        $notification = createDbNotification($this->user);

        expect($notification->read_at)->toBeNull();

        $response = $this->actingAs($this->user)
            ->post(route('notifications.read', $notification->id));

        $response->assertRedirect()
            ->assertSessionHas('success');

        $notification->refresh();
        expect($notification->read_at)->not->toBeNull();
    });

    it('returns redirect with success message', function () {
        $notification = createDbNotification($this->user);

        $response = $this->actingAs($this->user)
            ->post(route('notifications.read', $notification->id));

        $response->assertRedirect()
            ->assertSessionHas('success', 'Notification marked as read.');
    });

    it('handles non-existent notification gracefully', function () {
        $fakeId = Str::uuid()->toString();

        // The controller doesn't explicitly check for 404, so it just redirects back
        $response = $this->actingAs($this->user)
            ->post(route('notifications.read', $fakeId));

        $response->assertRedirect()
            ->assertSessionHas('success');
    });

    it('cannot mark other user notification', function () {
        $otherUser = User::factory()->create();
        $otherNotification = createDbNotification($otherUser);

        // Attempt to mark other user's notification
        $response = $this->actingAs($this->user)
            ->post(route('notifications.read', $otherNotification->id));

        // The action should fail silently (returns false) but still redirects
        $response->assertRedirect();

        // Notification should remain unread
        $otherNotification->refresh();
        expect($otherNotification->read_at)->toBeNull();
    });

    it('handles already read notification', function () {
        $notification = createDbNotification($this->user, [], null, true);

        $response = $this->actingAs($this->user)
            ->post(route('notifications.read', $notification->id));

        $response->assertRedirect()
            ->assertSessionHas('success');
    });
});

/*
|--------------------------------------------------------------------------
| markAllAsRead Tests
|--------------------------------------------------------------------------
*/

describe('markAllAsRead', function () {
    it('redirects to login for unauthenticated users', function () {
        $response = $this->post(route('notifications.mark-all-read'));

        $response->assertRedirect(route('login'));
    });

    it('marks all notifications as read', function () {
        // Create unread notifications
        $notifications = [];
        for ($i = 0; $i < 5; $i++) {
            $notifications[] = createDbNotification($this->user);
        }

        $response = $this->actingAs($this->user)
            ->post(route('notifications.mark-all-read'));

        $response->assertRedirect()
            ->assertSessionHas('success');

        // Verify all are read
        foreach ($notifications as $notification) {
            $notification->refresh();
            expect($notification->read_at)->not->toBeNull();
        }
    });

    it('returns success message with count', function () {
        for ($i = 0; $i < 3; $i++) {
            createDbNotification($this->user);
        }

        $response = $this->actingAs($this->user)
            ->post(route('notifications.mark-all-read'));

        $response->assertRedirect()
            ->assertSessionHas('success', '3 notifications marked as read.');
    });

    it('returns 0 count when no unread notifications', function () {
        // Create only read notifications
        for ($i = 0; $i < 2; $i++) {
            createDbNotification($this->user, [], null, true);
        }

        $response = $this->actingAs($this->user)
            ->post(route('notifications.mark-all-read'));

        $response->assertRedirect()
            ->assertSessionHas('success', '0 notifications marked as read.');
    });

    it('does not affect other users notifications', function () {
        $otherUser = User::factory()->create();

        // Create notifications for both users
        $myNotification = createDbNotification($this->user);
        $otherNotification = createDbNotification($otherUser);

        $response = $this->actingAs($this->user)
            ->post(route('notifications.mark-all-read'));

        $response->assertRedirect();

        // My notification should be read
        $myNotification->refresh();
        expect($myNotification->read_at)->not->toBeNull();

        // Other user's notification should remain unread
        $otherNotification->refresh();
        expect($otherNotification->read_at)->toBeNull();
    });

    it('handles user with no notifications', function () {
        $response = $this->actingAs($this->user)
            ->post(route('notifications.mark-all-read'));

        $response->assertRedirect()
            ->assertSessionHas('success', '0 notifications marked as read.');
    });
});

/*
|--------------------------------------------------------------------------
| User Isolation Tests
|--------------------------------------------------------------------------
*/

describe('user isolation', function () {
    it('prevents viewing other users notifications in list', function () {
        $otherUser = User::factory()->create();

        // Create notification for other user
        createDbNotification($otherUser, ['title' => 'Other User Secret']);

        $response = $this->actingAs($this->user)
            ->get(route('notifications.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('notifications.data', 0)
            );
    });

    it('prevents marking other user notification as read', function () {
        $otherUser = User::factory()->create();
        $otherNotification = createDbNotification($otherUser);

        $this->actingAs($this->user)
            ->post(route('notifications.read', $otherNotification->id));

        $otherNotification->refresh();
        expect($otherNotification->read_at)->toBeNull();
    });
});

/*
|--------------------------------------------------------------------------
| Edge Cases Tests
|--------------------------------------------------------------------------
*/

describe('edge cases', function () {
    it('handles special characters in notification data', function () {
        createDbNotification($this->user, [
            'title' => 'Alert: "Account" & <Profiles>',
            'message' => "Multi\nline\nmessage with 'quotes'",
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('notifications.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('notifications.data', 1)
            );
    });

    it('handles notification with empty data', function () {
        DatabaseNotification::create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\\Notifications\\TestNotification',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $this->user->id,
            'data' => [],
            'read_at' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('notifications.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('notifications.data', 1)
            );
    });

    it('handles different notification types correctly', function () {
        $types = [
            'App\\Notifications\\AccountUpdatedNotification',
            'App\\Notifications\\PasswordResetNotification',
            'App\\Notifications\\WelcomeNotification',
        ];

        foreach ($types as $type) {
            createDbNotification($this->user, ['title' => class_basename($type)], $type);
        }

        $response = $this->actingAs($this->user)
            ->get(route('notifications.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('notifications.data', 3)
            );
    });

    it('handles large number of notifications', function () {
        for ($i = 0; $i < 100; $i++) {
            createDbNotification($this->user, ['title' => "Notification $i"]);
        }

        $response = $this->actingAs($this->user)
            ->get(route('notifications.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('notifications/Index')
                ->has('notifications.data')
            );
    });

    it('handles notification with null link', function () {
        createDbNotification($this->user, [
            'title' => 'No Link Notification',
            'message' => 'This has no link',
            'link' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('notifications.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('notifications.data', 1)
                ->where('notifications.data.0.link', null)
            );
    });

    it('handles concurrent read operations', function () {
        $notification = createDbNotification($this->user);

        // Simulate concurrent marking as read
        $this->actingAs($this->user)
            ->post(route('notifications.read', $notification->id));

        $this->actingAs($this->user)
            ->post(route('notifications.read', $notification->id));

        $notification->refresh();
        expect($notification->read_at)->not->toBeNull();
    });
});
