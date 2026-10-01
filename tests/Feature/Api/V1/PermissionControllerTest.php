<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create permissions for managing permissions
    Permission::create(['name' => 'permissions-view', 'guard_name' => 'web', 'group' => 'permissions']);
    Permission::create(['name' => 'permissions-create', 'guard_name' => 'web', 'group' => 'permissions']);
    Permission::create(['name' => 'permissions-edit', 'guard_name' => 'web', 'group' => 'permissions']);
    Permission::create(['name' => 'permissions-delete', 'guard_name' => 'web', 'group' => 'permissions']);

    // Create some test permissions
    Permission::create(['name' => 'posts-view', 'guard_name' => 'web', 'group' => 'posts']);
    Permission::create(['name' => 'posts-create', 'guard_name' => 'web', 'group' => 'posts']);

    // Create admin role with all permissions
    $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
    $adminRole->givePermissionTo([
        'permissions-view',
        'permissions-create',
        'permissions-edit',
        'permissions-delete',
    ]);

    // Create admin user
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('lists permissions', function () {
    $response = $this->actingAs($this->admin)
        ->getJson('/api/v1/permissions');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data',
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
});

it('lists permissions with search filter', function () {
    $response = $this->actingAs($this->admin)
        ->getJson('/api/v1/permissions?search=posts');

    $response->assertOk()
        ->assertJsonStructure(['success', 'data']);

    $data = $response->json('data');
    foreach ($data as $permission) {
        expect($permission['name'])->toContain('posts');
    }
});

it('lists permissions with group filter', function () {
    $response = $this->actingAs($this->admin)
        ->getJson('/api/v1/permissions?group=posts');

    $response->assertOk();

    $data = $response->json('data');
    foreach ($data as $permission) {
        expect($permission['group'])->toBe('posts');
    }
});

it('creates a permission', function () {
    $data = [
        'name' => 'comments-view',
        'group' => 'comments',
    ];

    $response = $this->actingAs($this->admin)
        ->postJson('/api/v1/permissions', $data);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'comments-view',
                'group' => 'comments',
            ],
        ]);

    $this->assertDatabaseHas('permissions', ['name' => 'comments-view']);
});

it('shows a permission', function () {
    $permission = Permission::where('name', 'posts-view')->first();

    $response = $this->actingAs($this->admin)
        ->getJson("/api/v1/permissions/{$permission->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $permission->id,
                'name' => 'posts-view',
                'group' => 'posts',
            ],
        ]);
});

it('updates a permission', function () {
    $permission = Permission::where('name', 'posts-view')->first();

    $data = [
        'name' => 'articles-view',
        'group' => 'articles',
    ];

    $response = $this->actingAs($this->admin)
        ->putJson("/api/v1/permissions/{$permission->id}", $data);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'articles-view',
                'group' => 'articles',
            ],
        ]);

    $this->assertDatabaseHas('permissions', ['name' => 'articles-view']);
    $this->assertDatabaseMissing('permissions', ['name' => 'posts-view']);
});

it('deletes a permission', function () {
    $permission = Permission::where('name', 'posts-create')->first();

    $response = $this->actingAs($this->admin)
        ->deleteJson("/api/v1/permissions/{$permission->id}");

    $response->assertOk()
        ->assertJson(['success' => true]);

    $this->assertDatabaseMissing('permissions', ['id' => $permission->id]);
});

it('requires authentication', function () {
    $response = $this->getJson('/api/v1/permissions');

    $response->assertUnauthorized();
});

it('requires permission to list permissions', function () {
    $userWithoutPermission = User::factory()->create();

    $response = $this->actingAs($userWithoutPermission)
        ->getJson('/api/v1/permissions');

    $response->assertForbidden();
});

it('validates required fields on create', function () {
    $response = $this->actingAs($this->admin)
        ->postJson('/api/v1/permissions', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

it('validates unique name on create', function () {
    $response = $this->actingAs($this->admin)
        ->postJson('/api/v1/permissions', [
            'name' => 'posts-view', // Already exists
            'group' => 'posts',
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});
