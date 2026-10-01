<?php

use App\Queries\Permission\ListPermissionsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->query = new ListPermissionsQuery;
});

it('returns paginated permissions by default', function () {
    Permission::create(['name' => 'users-view', 'guard_name' => 'web']);
    Permission::create(['name' => 'users-create', 'guard_name' => 'web']);

    $result = $this->query->handle();

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($result->total())->toBe(2);
});

it('returns collection when paginate is false', function () {
    Permission::create(['name' => 'users-view', 'guard_name' => 'web']);
    Permission::create(['name' => 'users-create', 'guard_name' => 'web']);

    $result = $this->query->handle(['paginate' => false]);

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBe(2);
});

it('filters by search term', function () {
    Permission::create(['name' => 'users-view', 'guard_name' => 'web']);
    Permission::create(['name' => 'posts-view', 'guard_name' => 'web']);

    $result = $this->query->handle(['search' => 'users']);

    expect($result->total())->toBe(1)
        ->and($result->first()->name)->toBe('users-view');
});

it('filters by group', function () {
    Permission::create(['name' => 'users-view', 'group' => 'users', 'guard_name' => 'web']);
    Permission::create(['name' => 'posts-view', 'group' => 'posts', 'guard_name' => 'web']);

    $result = $this->query->handle(['group' => 'users']);

    expect($result->total())->toBe(1)
        ->and($result->first()->group)->toBe('users');
});

it('orders by group then name', function () {
    Permission::create(['name' => 'zeta', 'group' => 'b', 'guard_name' => 'web']);
    Permission::create(['name' => 'alpha', 'group' => 'a', 'guard_name' => 'web']);
    Permission::create(['name' => 'beta', 'group' => 'a', 'guard_name' => 'web']);

    $result = $this->query->handle(['paginate' => false]);

    expect($result->get(0)->name)->toBe('alpha')
        ->and($result->get(1)->name)->toBe('beta')
        ->and($result->get(2)->name)->toBe('zeta');
});

it('respects per_page parameter', function () {
    for ($i = 0; $i < 20; $i++) {
        Permission::create(['name' => "permission-$i", 'guard_name' => 'web']);
    }

    $result = $this->query->handle(['per_page' => 5]);

    expect($result->perPage())->toBe(5);
});
