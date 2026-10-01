<?php

use App\Actions\User\UpdateUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = new UpdateUser;
});

it('updates user name and email', function () {
    $role = Role::create(['name' => 'user', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('user');

    $data = [
        'id' => $user->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'roles' => ['user'],
    ];

    $updatedUser = $this->action->handle($data);

    expect($updatedUser->name)->toBe('Updated Name')
        ->and($updatedUser->email)->toBe('updated@example.com');
});

it('updates password when provided', function () {
    $role = Role::create(['name' => 'user', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('user');
    $originalPassword = $user->password;

    $data = [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'password' => 'newpassword123',
        'roles' => ['user'],
    ];

    $updatedUser = $this->action->handle($data);

    expect($updatedUser->password)->not->toBe($originalPassword);
});

it('does not update password when not provided', function () {
    $role = Role::create(['name' => 'user', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('user');
    $originalPassword = $user->password;

    $data = [
        'id' => $user->id,
        'name' => 'Updated Name',
        'email' => $user->email,
        'roles' => ['user'],
    ];

    $updatedUser = $this->action->handle($data);

    expect($updatedUser->password)->toBe($originalPassword);
});

it('syncs roles', function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);
    Role::create(['name' => 'editor', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('admin');

    $data = [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'roles' => ['editor'],
    ];

    $updatedUser = $this->action->handle($data);

    expect($updatedUser->hasRole('admin'))->toBeFalse()
        ->and($updatedUser->hasRole('editor'))->toBeTrue();
});

it('returns fresh user with roles', function () {
    $role = Role::create(['name' => 'user', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('user');

    $data = [
        'id' => $user->id,
        'name' => 'Updated Name',
        'email' => $user->email,
        'roles' => ['user'],
    ];

    $updatedUser = $this->action->handle($data);

    expect($updatedUser->relationLoaded('roles'))->toBeTrue();
});
