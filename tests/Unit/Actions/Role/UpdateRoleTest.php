<?php

use App\Actions\Role\UpdateRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = new UpdateRole;
});

it('updates role name', function () {
    $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);

    $data = [
        'id' => $role->id,
        'name' => 'senior-editor',
    ];

    $updatedRole = $this->action->handle($data);

    expect($updatedRole->name)->toBe('senior-editor');
});

it('syncs permissions', function () {
    Permission::create(['name' => 'posts-create', 'guard_name' => 'web']);
    Permission::create(['name' => 'posts-edit', 'guard_name' => 'web']);
    Permission::create(['name' => 'posts-delete', 'guard_name' => 'web']);

    $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
    $role->givePermissionTo('posts-create');

    $data = [
        'id' => $role->id,
        'name' => 'editor',
        'permissions' => ['posts-edit', 'posts-delete'],
    ];

    $updatedRole = $this->action->handle($data);

    expect($updatedRole->hasPermissionTo('posts-create'))->toBeFalse()
        ->and($updatedRole->hasPermissionTo('posts-edit'))->toBeTrue()
        ->and($updatedRole->hasPermissionTo('posts-delete'))->toBeTrue();
});

it('clears permissions when empty array provided', function () {
    Permission::create(['name' => 'posts-create', 'guard_name' => 'web']);

    $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
    $role->givePermissionTo('posts-create');

    $data = [
        'id' => $role->id,
        'name' => 'editor',
        'permissions' => [],
    ];

    $updatedRole = $this->action->handle($data);

    expect($updatedRole->permissions)->toHaveCount(0);
});

it('returns fresh role with permissions', function () {
    $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);

    $data = [
        'id' => $role->id,
        'name' => 'senior-editor',
    ];

    $updatedRole = $this->action->handle($data);

    expect($updatedRole->relationLoaded('permissions'))->toBeTrue();
});

it('throws exception for non-existent role', function () {
    $this->action->handle(['id' => 99999, 'name' => 'test']);
})->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
