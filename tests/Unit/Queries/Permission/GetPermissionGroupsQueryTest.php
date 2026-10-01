<?php

use App\Queries\Permission\GetPermissionGroupsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->query = new GetPermissionGroupsQuery;
});

it('returns permissions grouped by group', function () {
    Permission::create(['name' => 'users-view', 'group' => 'users', 'guard_name' => 'web']);
    Permission::create(['name' => 'users-create', 'group' => 'users', 'guard_name' => 'web']);
    Permission::create(['name' => 'posts-view', 'group' => 'posts', 'guard_name' => 'web']);

    $result = $this->query->handle();

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->has('users'))->toBeTrue()
        ->and($result->has('posts'))->toBeTrue()
        ->and($result->get('users'))->toHaveCount(2)
        ->and($result->get('posts'))->toHaveCount(1);
});

it('handles permissions without group', function () {
    Permission::create(['name' => 'dashboard-view', 'group' => null, 'guard_name' => 'web']);

    $result = $this->query->handle();

    expect($result->has(''))->toBeTrue();
});

it('orders by group then name', function () {
    Permission::create(['name' => 'zeta', 'group' => 'b', 'guard_name' => 'web']);
    Permission::create(['name' => 'alpha', 'group' => 'a', 'guard_name' => 'web']);

    $result = $this->query->handle();

    expect($result->keys()->first())->toBe('a');
});

it('returns empty collection when no permissions exist', function () {
    $result = $this->query->handle();

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->isEmpty())->toBeTrue();
});
