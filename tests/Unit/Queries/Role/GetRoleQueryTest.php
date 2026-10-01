<?php

use App\Queries\Role\GetRoleQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->query = new GetRoleQuery;
});

it('returns role by id', function () {
    $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);

    $result = $this->query->handle(['id' => $role->id]);

    expect($result)->toBeInstanceOf(Role::class)
        ->and($result->name)->toBe('admin');
});

it('loads permissions relationship', function () {
    Permission::create(['name' => 'users-view', 'guard_name' => 'web']);
    $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
    $role->givePermissionTo('users-view');

    $result = $this->query->handle(['id' => $role->id]);

    expect($result->relationLoaded('permissions'))->toBeTrue();
});

it('returns null for non-existent role', function () {
    $result = $this->query->handle(['id' => 99999]);

    expect($result)->toBeNull();
});
