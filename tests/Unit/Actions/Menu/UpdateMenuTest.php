<?php

use App\Actions\Menu\UpdateMenu;
use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = new UpdateMenu;
});

it('updates menu title and icon', function () {
    $menu = Menu::factory()->create([
        'title' => 'Old Title',
        'icon' => 'old-icon',
    ]);

    $data = [
        'id' => $menu->id,
        'title' => 'New Title',
        'icon' => 'new-icon',
    ];

    $updatedMenu = $this->action->handle($data);

    expect($updatedMenu->title)->toBe('New Title')
        ->and($updatedMenu->icon)->toBe('new-icon');
});

it('updates menu route', function () {
    $menu = Menu::factory()->create(['route' => 'old.route']);

    $data = [
        'id' => $menu->id,
        'title' => $menu->title,
        'route' => 'new.route',
    ];

    $updatedMenu = $this->action->handle($data);

    expect($updatedMenu->route)->toBe('new.route');
});

it('updates parent id', function () {
    $menu = Menu::factory()->create(['parent_id' => null]);
    $newParent = Menu::factory()->create();

    $data = [
        'id' => $menu->id,
        'title' => $menu->title,
        'parent_id' => $newParent->id,
    ];

    $updatedMenu = $this->action->handle($data);

    expect($updatedMenu->parent_id)->toBe($newParent->id);
});

it('updates permission name', function () {
    $menu = Menu::factory()->create(['permission_name' => null]);

    $data = [
        'id' => $menu->id,
        'title' => $menu->title,
        'permission_name' => 'users-view',
    ];

    $updatedMenu = $this->action->handle($data);

    expect($updatedMenu->permission_name)->toBe('users-view');
});

it('preserves order when not provided', function () {
    $menu = Menu::factory()->create(['order' => 5]);

    $data = [
        'id' => $menu->id,
        'title' => 'Updated Title',
    ];

    $updatedMenu = $this->action->handle($data);

    expect($updatedMenu->order)->toBe(5);
});

it('throws exception for non-existent menu', function () {
    $this->action->handle(['id' => 99999, 'title' => 'Test']);
})->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
