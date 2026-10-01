<?php

use App\Actions\Role\CreateRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = new CreateRole;
});

it('creates a role with valid data', function () {
    $data = [
        'name' => 'editor',
    ];

    $role = $this->action->handle($data);

    expect($role)->toBeInstanceOf(Role::class)
        ->and($role->name)->toBe('editor')
        ->and($role->guard_name)->toBe('web');
});

it('creates a role with permissions', function () {
    Permission::create(['name' => 'posts-create', 'guard_name' => 'web']);
    Permission::create(['name' => 'posts-edit', 'guard_name' => 'web']);

    $data = [
        'name' => 'editor',
        'permissions' => ['posts-create', 'posts-edit'],
    ];

    $role = $this->action->handle($data);

    expect($role->hasPermissionTo('posts-create'))->toBeTrue()
        ->and($role->hasPermissionTo('posts-edit'))->toBeTrue();
});

it('creates a role without permissions', function () {
    $data = [
        'name' => 'viewer',
    ];

    $role = $this->action->handle($data);

    expect($role->permissions)->toHaveCount(0);
});

it('loads permissions relationship', function () {
    $data = [
        'name' => 'editor',
    ];

    $role = $this->action->handle($data);

    expect($role->relationLoaded('permissions'))->toBeTrue();
});
