<?php

use App\Actions\Permission\UpdatePermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = new UpdatePermission;
});

it('updates permission name', function () {
    $permission = Permission::create([
        'name' => 'old-name',
        'guard_name' => 'web',
    ]);

    $data = [
        'id' => $permission->id,
        'name' => 'new-name',
    ];

    $updatedPermission = $this->action->handle($data);

    expect($updatedPermission->name)->toBe('new-name');
});

it('updates permission group', function () {
    $permission = Permission::create([
        'name' => 'users-create',
        'group' => 'old-group',
        'guard_name' => 'web',
    ]);

    $data = [
        'id' => $permission->id,
        'name' => 'users-create',
        'group' => 'new-group',
    ];

    $updatedPermission = $this->action->handle($data);

    expect($updatedPermission->group)->toBe('new-group');
});

it('preserves group when not provided', function () {
    $permission = Permission::create([
        'name' => 'users-create',
        'group' => 'users',
        'guard_name' => 'web',
    ]);

    $data = [
        'id' => $permission->id,
        'name' => 'users-create-updated',
    ];

    $updatedPermission = $this->action->handle($data);

    expect($updatedPermission->group)->toBe('users');
});

it('throws exception for non-existent permission', function () {
    $this->action->handle(['id' => 99999, 'name' => 'test']);
})->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
