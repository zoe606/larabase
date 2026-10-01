<?php

use App\Models\Menu;
use App\Models\User;
use App\Queries\Menu\GetMenuTreeQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->query = new GetMenuTreeQuery;
});

it('returns menu tree as collection', function () {
    Menu::factory()->count(3)->create(['parent_id' => null]);

    $result = $this->query->handle();

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBe(3);
});

it('returns only root menus', function () {
    $parent = Menu::factory()->create(['parent_id' => null]);
    Menu::factory()->create(['parent_id' => $parent->id]);

    $result = $this->query->handle();

    expect($result->count())->toBe(1)
        ->and($result->first()->parent_id)->toBeNull();
});

it('loads children relationship', function () {
    $parent = Menu::factory()->create(['parent_id' => null]);
    Menu::factory()->create(['parent_id' => $parent->id]);

    $result = $this->query->handle();

    expect($result->first()->relationLoaded('children'))->toBeTrue()
        ->and($result->first()->children)->toHaveCount(1);
});

it('orders by order column', function () {
    Menu::factory()->create(['order' => 2, 'parent_id' => null]);
    Menu::factory()->create(['order' => 1, 'parent_id' => null]);

    $result = $this->query->handle();

    expect($result->first()->order)->toBe(1);
});

it('orders children by order column', function () {
    $parent = Menu::factory()->create(['parent_id' => null]);
    Menu::factory()->create(['order' => 2, 'parent_id' => $parent->id]);
    Menu::factory()->create(['order' => 1, 'parent_id' => $parent->id]);

    $result = $this->query->handle();

    expect($result->first()->children->first()->order)->toBe(1);
});

it('filters by user permissions when user provided', function () {
    Permission::create(['name' => 'users-view', 'guard_name' => 'web']);
    Permission::create(['name' => 'admin-view', 'guard_name' => 'web']);

    $user = User::factory()->create();
    $user->givePermissionTo('users-view');

    Menu::factory()->create(['parent_id' => null, 'permission_name' => null]);
    Menu::factory()->create(['parent_id' => null, 'permission_name' => 'users-view']);
    Menu::factory()->create(['parent_id' => null, 'permission_name' => 'admin-view']);

    $result = $this->query->handle(['user' => $user]);

    expect($result->count())->toBe(2);
});

it('includes menus without permission requirement', function () {
    $user = User::factory()->create();

    Menu::factory()->create(['parent_id' => null, 'permission_name' => null]);

    $result = $this->query->handle(['user' => $user]);

    expect($result->count())->toBe(1);
});
