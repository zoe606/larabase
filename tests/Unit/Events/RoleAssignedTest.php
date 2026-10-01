<?php

declare(strict_types=1);

use App\Events\RoleAssigned;
use App\Models\User;

it('stores user, roles, and previous roles', function () {
    $user = User::factory()->create();
    $roles = ['admin', 'editor'];
    $previousRoles = ['viewer'];

    $event = new RoleAssigned($user, $roles, $previousRoles);

    expect($event->user)->toBe($user)
        ->and($event->roles)->toBe(['admin', 'editor'])
        ->and($event->previousRoles)->toBe(['viewer']);
});

it('defaults previousRoles to empty array', function () {
    $user = User::factory()->create();

    $event = new RoleAssigned($user, ['admin']);

    expect($event->previousRoles)->toBe([]);
});
