<?php

use App\Models\User;
use App\Queries\User\GetUserQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->query = new GetUserQuery;
});

it('returns user by id', function () {
    $user = User::factory()->create();

    $result = $this->query->handle(['id' => $user->id]);

    expect($result)->toBeInstanceOf(User::class)
        ->and($result->id)->toBe($user->id);
});

it('loads roles relationship', function () {
    $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('admin');

    $result = $this->query->handle(['id' => $user->id]);

    expect($result->relationLoaded('roles'))->toBeTrue();
});

it('loads permissions relationship', function () {
    Permission::create(['name' => 'users-view', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->givePermissionTo('users-view');

    $result = $this->query->handle(['id' => $user->id]);

    expect($result->relationLoaded('permissions'))->toBeTrue();
});

it('returns null for non-existent user', function () {
    $result = $this->query->handle(['id' => 99999]);

    expect($result)->toBeNull();
});
