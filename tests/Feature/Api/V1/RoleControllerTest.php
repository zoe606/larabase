<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create permissions
    Permission::create(['name' => 'roles-view', 'guard_name' => 'web']);
    Permission::create(['name' => 'roles-create', 'guard_name' => 'web']);
    Permission::create(['name' => 'roles-edit', 'guard_name' => 'web']);
    Permission::create(['name' => 'roles-delete', 'guard_name' => 'web']);
    Permission::create(['name' => 'posts-view', 'guard_name' => 'web']);

    // Create admin role with all permissions
    $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
    $adminRole->givePermissionTo(['roles-view', 'roles-create', 'roles-edit', 'roles-delete']);

    // Create admin user
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('lists roles', function () {
    Role::create(['name' => 'editor', 'guard_name' => 'web']);
    Role::create(['name' => 'viewer', 'guard_name' => 'web']);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/v1/roles');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data',
            'meta',
        ]);
});

it('creates a role', function () {
    $data = [
        'name' => 'editor',
        'permissions' => ['posts-view'],
    ];

    $response = $this->actingAs($this->admin)
        ->postJson('/api/v1/roles', $data);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'editor',
            ],
        ]);

    $this->assertDatabaseHas('roles', ['name' => 'editor']);
});

it('shows a role', function () {
    $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);

    $response = $this->actingAs($this->admin)
        ->getJson("/api/v1/roles/{$role->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'editor',
            ],
        ]);
});

it('updates a role', function () {
    $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);

    $data = [
        'name' => 'senior-editor',
        'permissions' => ['posts-view'],
    ];

    $response = $this->actingAs($this->admin)
        ->putJson("/api/v1/roles/{$role->id}", $data);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'senior-editor',
            ],
        ]);
});

it('deletes a role', function () {
    $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);

    $response = $this->actingAs($this->admin)
        ->deleteJson("/api/v1/roles/{$role->id}");

    $response->assertOk()
        ->assertJson(['success' => true]);

    $this->assertDatabaseMissing('roles', ['id' => $role->id]);
});

it('requires authentication', function () {
    $response = $this->getJson('/api/v1/roles');

    $response->assertUnauthorized();
});
