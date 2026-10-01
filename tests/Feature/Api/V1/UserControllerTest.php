<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create permissions
    Permission::create(['name' => 'users-view', 'guard_name' => 'web']);
    Permission::create(['name' => 'users-create', 'guard_name' => 'web']);
    Permission::create(['name' => 'users-edit', 'guard_name' => 'web']);
    Permission::create(['name' => 'users-delete', 'guard_name' => 'web']);

    // Create admin role with all permissions
    $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
    $adminRole->givePermissionTo(['users-view', 'users-create', 'users-edit', 'users-delete']);

    // Create user role
    Role::create(['name' => 'user', 'guard_name' => 'web']);

    // Create admin user
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('lists users', function () {
    User::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)
        ->getJson('/api/v1/users');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data',
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
});

it('creates a user', function () {
    $data = [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'roles' => ['user'],
    ];

    $response = $this->actingAs($this->admin)
        ->postJson('/api/v1/users', $data);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'New User',
                'email' => 'newuser@example.com',
            ],
        ]);

    $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
});

it('shows a user', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $response = $this->actingAs($this->admin)
        ->getJson("/api/v1/users/{$user->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
});

it('updates a user', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $data = [
        'name' => 'Updated Name',
        'email' => $user->email,
        'roles' => ['user'],
    ];

    $response = $this->actingAs($this->admin)
        ->putJson("/api/v1/users/{$user->id}", $data);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'Updated Name',
            ],
        ]);
});

it('deletes a user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($this->admin)
        ->deleteJson("/api/v1/users/{$user->id}");

    $response->assertOk()
        ->assertJson(['success' => true]);

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

it('resets user password', function () {
    $user = User::factory()->create();
    $originalPassword = $user->password;

    $response = $this->actingAs($this->admin)
        ->postJson("/api/v1/users/{$user->id}/reset-password");

    $response->assertOk()
        ->assertJson(['success' => true]);

    $user->refresh();
    expect($user->password)->not->toBe($originalPassword);
});

it('requires authentication', function () {
    $response = $this->getJson('/api/v1/users');

    $response->assertUnauthorized();
});

it('requires permission to list users', function () {
    $userWithoutPermission = User::factory()->create();

    $response = $this->actingAs($userWithoutPermission)
        ->getJson('/api/v1/users');

    $response->assertForbidden();
});
