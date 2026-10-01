<?php

use App\Actions\Menu\CreateMenu;
use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = new CreateMenu;
});

it('creates a menu with valid data', function () {
    $data = [
        'title' => 'Dashboard',
        'icon' => 'icon-dashboard',
        'route' => 'dashboard',
    ];

    $menu = $this->action->handle($data);

    expect($menu)->toBeInstanceOf(Menu::class)
        ->and($menu->title)->toBe('Dashboard')
        ->and($menu->icon)->toBe('icon-dashboard')
        ->and($menu->route)->toBe('dashboard')
        ->and($menu->parent_id)->toBeNull();
});

it('creates a menu with parent', function () {
    $parent = Menu::factory()->create();

    $data = [
        'title' => 'Submenu',
        'parent_id' => $parent->id,
    ];

    $menu = $this->action->handle($data);

    expect($menu->parent_id)->toBe($parent->id);
});

it('creates a menu with permission', function () {
    $data = [
        'title' => 'Users',
        'permission_name' => 'users-view',
    ];

    $menu = $this->action->handle($data);

    expect($menu->permission_name)->toBe('users-view');
});

it('auto-calculates order when not provided', function () {
    Menu::factory()->create(['order' => 5, 'parent_id' => null]);
    Menu::factory()->create(['order' => 10, 'parent_id' => null]);

    $data = [
        'title' => 'New Menu',
    ];

    $menu = $this->action->handle($data);

    expect($menu->order)->toBe(11);
});

it('uses provided order', function () {
    $data = [
        'title' => 'New Menu',
        'order' => 99,
    ];

    $menu = $this->action->handle($data);

    expect($menu->order)->toBe(99);
});

it('calculates order within parent context', function () {
    $parent = Menu::factory()->create();
    Menu::factory()->create(['order' => 5, 'parent_id' => $parent->id]);

    $data = [
        'title' => 'Child Menu',
        'parent_id' => $parent->id,
    ];

    $menu = $this->action->handle($data);

    expect($menu->order)->toBe(6);
});
