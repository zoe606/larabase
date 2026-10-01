<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create();
});

it('lists notifications', function () {
    // Create test notifications directly in the database
    DatabaseNotification::create([
        'id' => \Illuminate\Support\Str::uuid()->toString(),
        'type' => 'App\\Notifications\\AccountUpdated',
        'notifiable_type' => 'App\\Models\\User',
        'notifiable_id' => $this->admin->id,
        'data' => ['title' => 'Account Alert', 'message' => 'Account is running low'],
    ]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/v1/notifications');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data',
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
});

it('marks a notification as read', function () {
    $notification = DatabaseNotification::create([
        'id' => \Illuminate\Support\Str::uuid()->toString(),
        'type' => 'App\\Notifications\\AccountUpdated',
        'notifiable_type' => 'App\\Models\\User',
        'notifiable_id' => $this->admin->id,
        'data' => ['title' => 'Account Alert', 'message' => 'Test'],
    ]);

    $response = $this->actingAs($this->admin)
        ->postJson("/api/v1/notifications/{$notification->id}/read");

    $response->assertOk()
        ->assertJson(['success' => true]);

    $notification->refresh();
    expect($notification->read_at)->not->toBeNull();
});

it('marks all notifications as read', function () {
    // Create 3 unread notifications
    for ($i = 0; $i < 3; $i++) {
        DatabaseNotification::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'App\\Notifications\\AccountUpdated',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $this->admin->id,
            'data' => ['title' => "Alert {$i}", 'message' => 'Test'],
        ]);
    }

    $response = $this->actingAs($this->admin)
        ->postJson('/api/v1/notifications/mark-all-read');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => ['marked_count' => 3],
        ]);
});

it('returns not found for non-existent notification', function () {
    $fakeId = \Illuminate\Support\Str::uuid()->toString();

    $response = $this->actingAs($this->admin)
        ->postJson("/api/v1/notifications/{$fakeId}/read");

    $response->assertNotFound();
});

it('requires authentication', function () {
    $response = $this->getJson('/api/v1/notifications');

    $response->assertUnauthorized();
});

it('only shows own notifications', function () {
    $otherUser = User::factory()->create();

    DatabaseNotification::create([
        'id' => \Illuminate\Support\Str::uuid()->toString(),
        'type' => 'App\\Notifications\\AccountUpdated',
        'notifiable_type' => 'App\\Models\\User',
        'notifiable_id' => $otherUser->id,
        'data' => ['title' => 'Other Users Alert', 'message' => 'Test'],
    ]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/v1/notifications');

    $response->assertOk();
    expect($response->json('data'))->toBeEmpty();
});
