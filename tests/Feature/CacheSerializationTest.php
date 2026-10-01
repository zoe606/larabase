<?php

declare(strict_types=1);

use App\Models\Menu;
use App\Models\SettingApp;
use App\Models\User;
use App\Queries\Permission\GetDistinctGroupsQuery;
use App\Queries\Permission\GetPermissionGroupsQuery;
use App\Queries\Setting\GetSettingsQuery;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    config(['cache.default' => 'database']);
    Cache::purge('database');
});

it('restores cached settings through database serialization', function () {
    $setting = SettingApp::create(['nama_app' => 'Cached application']);
    $query = new GetSettingsQuery;
    $query->handle();
    SettingApp::query()->whereKey($setting->id)->update(['nama_app' => 'Changed application']);

    expect($query->handle())->toBeInstanceOf(SettingApp::class)
        ->nama_app->toBe('Cached application');
});

it('restores permission groups and models through database serialization', function () {
    $permission = Permission::create(['name' => 'example-view', 'guard_name' => 'web', 'group' => 'Platform']);
    $distinct = new GetDistinctGroupsQuery;
    $groups = new GetPermissionGroupsQuery;
    $distinct->handle();
    $groups->handle();
    Permission::query()->whereKey($permission->id)->update(['group' => 'Changed']);

    expect($distinct->handle()->all())->toBe(['Platform'])
        ->and($groups->handle()->get('Platform')->first())->toBeInstanceOf(Permission::class)
        ->group->toBe('Platform');
});

it('restores nested menu models on a database cache hit', function () {
    $parent = Menu::factory()->create(['title' => 'Root', 'route' => null]);
    Menu::factory()->child($parent)->create(['title' => 'Child', 'route' => 'dashboard']);
    $this->actingAs(User::factory()->create());

    for ($attempt = 0; $attempt < 2; $attempt++) {
        $this->get('/')->assertOk()->assertInertia(fn (AssertableInertia $page) => $page
            ->where('menus.0.title', 'Root')
            ->where('menus.0.children.0.title', 'Child'));
    }
});

it('does not restore objects outside the cache class allowlist', function () {
    Cache::put('unlisted-object', (object) ['name' => 'Example'], 60);

    expect(Cache::get('unlisted-object'))->toBeInstanceOf(__PHP_Incomplete_Class::class);
});
