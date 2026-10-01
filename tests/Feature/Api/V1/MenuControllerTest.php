<?php

declare(strict_types=1);

use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create permissions
    Permission::create(['name' => 'menus-view', 'guard_name' => 'web', 'group' => 'menus']);
    Permission::create(['name' => 'menus-create', 'guard_name' => 'web', 'group' => 'menus']);
    Permission::create(['name' => 'menus-edit', 'guard_name' => 'web', 'group' => 'menus']);
    Permission::create(['name' => 'menus-delete', 'guard_name' => 'web', 'group' => 'menus']);

    // Create admin role with all permissions
    $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
    $adminRole->givePermissionTo([
        'menus-view',
        'menus-create',
        'menus-edit',
        'menus-delete',
    ]);

    // Create admin user
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('lists menus', function () {
    Menu::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)
        ->getJson('/api/v1/menus');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data',
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
});

it('lists menus as tree structure', function () {
    $parent = Menu::factory()->create(['title' => 'Parent', 'parent_id' => null]);
    Menu::factory()->create(['title' => 'Child', 'parent_id' => $parent->id]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/v1/menus?tree=true');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data',
        ]);
});

it('lists only root menus', function () {
    $parent = Menu::factory()->create(['title' => 'Parent', 'parent_id' => null]);
    Menu::factory()->create(['title' => 'Child', 'parent_id' => $parent->id]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/v1/menus?root_only=true');

    $response->assertOk();

    $data = $response->json('data');
    foreach ($data as $menu) {
        expect($menu['parent_id'])->toBeNull();
    }
});

it('creates a menu', function () {
    $data = [
        'title' => 'Dashboard',
        'icon' => 'dashboard',
        'route' => 'dashboard',
        'order' => 1,
    ];

    $response = $this->actingAs($this->admin)
        ->postJson('/api/v1/menus', $data);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'title' => 'Dashboard',
                'icon' => 'dashboard',
            ],
        ]);

    $this->assertDatabaseHas('menus', ['title' => 'Dashboard']);
});

it('creates a child menu', function () {
    $parent = Menu::factory()->create(['title' => 'Parent']);

    $data = [
        'title' => 'Child Menu',
        'icon' => 'child',
        'route' => 'parent.child',
        'parent_id' => $parent->id,
        'order' => 1,
    ];

    $response = $this->actingAs($this->admin)
        ->postJson('/api/v1/menus', $data);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'title' => 'Child Menu',
                'parent_id' => $parent->id,
            ],
        ]);
});

it('shows a menu', function () {
    $menu = Menu::factory()->create(['title' => 'Test Menu']);

    $response = $this->actingAs($this->admin)
        ->getJson("/api/v1/menus/{$menu->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $menu->id,
                'title' => 'Test Menu',
            ],
        ]);
});

it('shows a menu with children', function () {
    $parent = Menu::factory()->create(['title' => 'Parent']);
    Menu::factory()->create(['title' => 'Child', 'parent_id' => $parent->id]);

    $response = $this->actingAs($this->admin)
        ->getJson("/api/v1/menus/{$parent->id}");

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'title',
                'children',
            ],
        ]);
});

it('updates a menu', function () {
    $menu = Menu::factory()->create(['title' => 'Old Title']);

    $data = [
        'title' => 'New Title',
        'icon' => 'new-icon',
        'route' => 'new.route',
    ];

    $response = $this->actingAs($this->admin)
        ->putJson("/api/v1/menus/{$menu->id}", $data);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'title' => 'New Title',
                'icon' => 'new-icon',
            ],
        ]);

    $this->assertDatabaseHas('menus', ['title' => 'New Title']);
});

it('deletes a menu', function () {
    $menu = Menu::factory()->create();

    $response = $this->actingAs($this->admin)
        ->deleteJson("/api/v1/menus/{$menu->id}");

    $response->assertOk()
        ->assertJson(['success' => true]);

    $this->assertDatabaseMissing('menus', ['id' => $menu->id]);
});

it('deletes a menu and moves children to parent level', function () {
    $parent = Menu::factory()->create(['title' => 'Parent', 'parent_id' => null]);
    $child = Menu::factory()->create(['title' => 'Child', 'parent_id' => $parent->id]);

    $response = $this->actingAs($this->admin)
        ->deleteJson("/api/v1/menus/{$parent->id}");

    $response->assertOk();

    $this->assertDatabaseMissing('menus', ['id' => $parent->id]);
    // Child should be moved to root level (parent's parent_id)
    $this->assertDatabaseHas('menus', ['id' => $child->id, 'parent_id' => null]);
});

it('reorders menus', function () {
    $menu1 = Menu::factory()->create(['order' => 1]);
    $menu2 = Menu::factory()->create(['order' => 2]);
    $menu3 = Menu::factory()->create(['order' => 3]);

    // Reorder: menu3 first, then menu1, then menu2
    // The action uses array index (0-based) for order
    $data = [
        'menus' => [
            ['id' => $menu3->id],
            ['id' => $menu1->id],
            ['id' => $menu2->id],
        ],
    ];

    $response = $this->actingAs($this->admin)
        ->postJson('/api/v1/menus/reorder', $data);

    $response->assertOk()
        ->assertJson(['success' => true]);

    // Order is 0-indexed based on array position
    $this->assertDatabaseHas('menus', ['id' => $menu3->id, 'order' => 0]);
    $this->assertDatabaseHas('menus', ['id' => $menu1->id, 'order' => 1]);
    $this->assertDatabaseHas('menus', ['id' => $menu2->id, 'order' => 2]);
});

it('requires authentication', function () {
    $response = $this->getJson('/api/v1/menus');

    $response->assertUnauthorized();
});

it('requires permission to list menus', function () {
    $userWithoutPermission = User::factory()->create();

    $response = $this->actingAs($userWithoutPermission)
        ->getJson('/api/v1/menus');

    $response->assertForbidden();
});

it('validates required fields on create', function () {
    $response = $this->actingAs($this->admin)
        ->postJson('/api/v1/menus', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);
});

it('validates parent_id exists', function () {
    $response = $this->actingAs($this->admin)
        ->postJson('/api/v1/menus', [
            'title' => 'Test Menu',
            'parent_id' => 99999, // Non-existent
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['parent_id']);
});
