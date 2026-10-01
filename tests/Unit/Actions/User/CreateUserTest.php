<?php

use App\Actions\User\CreateUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = new CreateUser;
});

it('creates a user with valid data', function () {
    $role = Role::create(['name' => 'user', 'guard_name' => 'web']);

    $data = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'roles' => ['user'],
    ];

    $user = $this->action->handle($data);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('John Doe')
        ->and($user->email)->toBe('john@example.com')
        ->and($user->hasRole('user'))->toBeTrue();
});

it('hashes the password', function () {
    $role = Role::create(['name' => 'user', 'guard_name' => 'web']);

    $data = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'roles' => ['user'],
    ];

    $user = $this->action->handle($data);

    expect($user->password)->not->toBe('password123');
});

it('assigns multiple roles', function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);
    Role::create(['name' => 'editor', 'guard_name' => 'web']);

    $data = [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'roles' => ['admin', 'editor'],
    ];

    $user = $this->action->handle($data);

    expect($user->hasRole('admin'))->toBeTrue()
        ->and($user->hasRole('editor'))->toBeTrue();
});

it('dispatches UserCreated event', function () {
    Event::fake();

    $role = Role::create(['name' => 'user', 'guard_name' => 'web']);

    $data = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'roles' => ['user'],
    ];

    $this->action->handle($data);

    Event::assertDispatched(\App\Events\UserCreated::class);
});

it('loads roles relationship', function () {
    $role = Role::create(['name' => 'user', 'guard_name' => 'web']);

    $data = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'roles' => ['user'],
    ];

    $user = $this->action->handle($data);

    expect($user->relationLoaded('roles'))->toBeTrue();
});
