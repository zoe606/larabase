<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('redirects dashboard guests to login', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

it('shows platform counts to authenticated users', function () {
    $user = User::factory()->create();
    Role::create(['name' => 'operator']);
    Permission::create(['name' => 'users-view']);

    $counts = [
        'users' => User::query()->count(),
        'roles' => Role::query()->count(),
        'permissions' => Permission::query()->count(),
        'audit_events' => Activity::query()->count(),
    ];

    $this->actingAs($user)->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->where('platform', $counts));
});
