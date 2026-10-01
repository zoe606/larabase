<?php

use App\Models\User;
use App\Queries\User\ListUsersQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->query = new ListUsersQuery;
});

it('returns paginated users', function () {
    User::factory()->count(5)->create();

    $result = $this->query->handle();

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($result->total())->toBe(5);
});

it('loads roles relationship', function () {
    $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('admin');

    $result = $this->query->handle();

    expect($result->first()->relationLoaded('roles'))->toBeTrue();
});

it('filters by search term on name', function () {
    User::factory()->create(['name' => 'John Doe']);
    User::factory()->create(['name' => 'Jane Smith']);

    $result = $this->query->handle(['search' => 'John']);

    expect($result->total())->toBe(1)
        ->and($result->first()->name)->toBe('John Doe');
});

it('filters by search term on email', function () {
    User::factory()->create(['email' => 'john@example.com']);
    User::factory()->create(['email' => 'jane@example.com']);

    $result = $this->query->handle(['search' => 'john@']);

    expect($result->total())->toBe(1)
        ->and($result->first()->email)->toBe('john@example.com');
});

it('filters by role', function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);
    Role::create(['name' => 'user', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();
    $user->assignRole('user');

    $result = $this->query->handle(['role' => 'admin']);

    expect($result->total())->toBe(1)
        ->and($result->first()->id)->toBe($admin->id);
});

it('respects per_page parameter', function () {
    User::factory()->count(20)->create();

    $result = $this->query->handle(['per_page' => 5]);

    expect($result->perPage())->toBe(5)
        ->and($result->count())->toBe(5);
});

it('uses default per_page of 15', function () {
    User::factory()->count(20)->create();

    $result = $this->query->handle();

    expect($result->perPage())->toBe(15);
});

it('orders by latest', function () {
    $older = User::factory()->create(['created_at' => now()->subDay()]);
    $newer = User::factory()->create(['created_at' => now()]);

    $result = $this->query->handle();

    expect($result->first()->id)->toBe($newer->id);
});
