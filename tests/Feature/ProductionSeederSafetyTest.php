<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Spatie\Permission\Models\Role;

it('creates platform data without demo credentials in production', function () {
    app()->detectEnvironment(fn () => 'production');

    app(DatabaseSeeder::class)->run();

    expect(User::query()->count())->toBe(0)
        ->and(Role::query()->where('name', 'admin')->exists())->toBeTrue();
});

it('creates the demo administrator only in local and testing environments', function (string $environment) {
    app()->detectEnvironment(fn () => $environment);

    app(DatabaseSeeder::class)->run();
    app(DatabaseSeeder::class)->run();

    expect(User::query()->where('email', 'admin@admin.com')->count())->toBe(1)
        ->and(User::query()->where('email', 'admin@admin.com')->first()->hasRole('admin'))->toBeTrue();
})->with(['local', 'testing']);
