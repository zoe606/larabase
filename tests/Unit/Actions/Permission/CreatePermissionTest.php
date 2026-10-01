<?php

use App\Actions\Permission\CreatePermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = new CreatePermission;
});

it('creates a permission with valid data', function () {
    $data = [
        'name' => 'users-create',
    ];

    $permission = $this->action->handle($data);

    expect($permission)->toBeInstanceOf(Permission::class)
        ->and($permission->name)->toBe('users-create')
        ->and($permission->guard_name)->toBe('web');
});

it('creates a permission with group', function () {
    $data = [
        'name' => 'users-create',
        'group' => 'users',
    ];

    $permission = $this->action->handle($data);

    expect($permission->group)->toBe('users');
});

it('creates a permission without group', function () {
    $data = [
        'name' => 'dashboard-view',
    ];

    $permission = $this->action->handle($data);

    expect($permission->group)->toBeNull();
});
