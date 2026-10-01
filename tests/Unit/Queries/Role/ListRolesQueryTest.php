<?php

use App\Queries\Role\ListRolesQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->query = new ListRolesQuery;
});

it('returns paginated roles by default', function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);
    Role::create(['name' => 'editor', 'guard_name' => 'web']);

    $result = $this->query->handle();

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($result->total())->toBe(2);
});

it('returns collection when paginate is false', function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);
    Role::create(['name' => 'editor', 'guard_name' => 'web']);

    $result = $this->query->handle(['paginate' => false]);

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBe(2);
});

it('loads permissions relationship', function () {
    Permission::create(['name' => 'users-view', 'guard_name' => 'web']);
    $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
    $role->givePermissionTo('users-view');

    $result = $this->query->handle(['paginate' => false]);

    expect($result->first()->relationLoaded('permissions'))->toBeTrue();
});

it('filters by search term', function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);
    Role::create(['name' => 'editor', 'guard_name' => 'web']);

    $result = $this->query->handle(['search' => 'admin']);

    expect($result->total())->toBe(1)
        ->and($result->first()->name)->toBe('admin');
});

it('orders by name', function () {
    Role::create(['name' => 'zeta', 'guard_name' => 'web']);
    Role::create(['name' => 'alpha', 'guard_name' => 'web']);

    $result = $this->query->handle(['paginate' => false]);

    expect($result->first()->name)->toBe('alpha');
});

it('respects per_page parameter', function () {
    for ($i = 0; $i < 20; $i++) {
        Role::create(['name' => "role-$i", 'guard_name' => 'web']);
    }

    $result = $this->query->handle(['per_page' => 5]);

    expect($result->perPage())->toBe(5);
});
