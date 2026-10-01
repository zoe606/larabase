<?php

declare(strict_types=1);

use App\Models\Menu;
use App\Models\User;
use App\Policies\MenuPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::create(['name' => 'menus-view', 'guard_name' => 'web']);
    Permission::create(['name' => 'menus-create', 'guard_name' => 'web']);
    Permission::create(['name' => 'menus-edit', 'guard_name' => 'web']);
    Permission::create(['name' => 'menus-delete', 'guard_name' => 'web']);

    $this->user = User::factory()->create();
    $this->menu = Menu::factory()->create();
    $this->policy = new MenuPolicy;
});

describe('viewAny', function () {
    it('allows users with menus-view permission', function () {
        $this->user->givePermissionTo('menus-view');

        expect($this->policy->viewAny($this->user))->toBeTrue();
    });

    it('denies users without menus-view permission', function () {
        expect($this->policy->viewAny($this->user))->toBeFalse();
    });
});

describe('view', function () {
    it('allows users with menus-view permission', function () {
        $this->user->givePermissionTo('menus-view');

        expect($this->policy->view($this->user, $this->menu))->toBeTrue();
    });

    it('denies users without menus-view permission', function () {
        expect($this->policy->view($this->user, $this->menu))->toBeFalse();
    });
});

describe('create', function () {
    it('allows users with menus-create permission', function () {
        $this->user->givePermissionTo('menus-create');

        expect($this->policy->create($this->user))->toBeTrue();
    });

    it('denies users without menus-create permission', function () {
        expect($this->policy->create($this->user))->toBeFalse();
    });

    it('denies users with only menus-view permission', function () {
        $this->user->givePermissionTo('menus-view');

        expect($this->policy->create($this->user))->toBeFalse();
    });
});

describe('update', function () {
    it('allows users with menus-edit permission', function () {
        $this->user->givePermissionTo('menus-edit');

        expect($this->policy->update($this->user, $this->menu))->toBeTrue();
    });

    it('denies users without menus-edit permission', function () {
        expect($this->policy->update($this->user, $this->menu))->toBeFalse();
    });

    it('denies users with only menus-view permission', function () {
        $this->user->givePermissionTo('menus-view');

        expect($this->policy->update($this->user, $this->menu))->toBeFalse();
    });
});

describe('delete', function () {
    it('allows users with menus-delete permission', function () {
        $this->user->givePermissionTo('menus-delete');

        expect($this->policy->delete($this->user, $this->menu))->toBeTrue();
    });

    it('denies users without menus-delete permission', function () {
        expect($this->policy->delete($this->user, $this->menu))->toBeFalse();
    });

    it('denies users with only menus-edit permission', function () {
        $this->user->givePermissionTo('menus-edit');

        expect($this->policy->delete($this->user, $this->menu))->toBeFalse();
    });
});

describe('reorder', function () {
    it('allows users with menus-edit permission', function () {
        $this->user->givePermissionTo('menus-edit');

        expect($this->policy->reorder($this->user))->toBeTrue();
    });

    it('denies users without menus-edit permission', function () {
        expect($this->policy->reorder($this->user))->toBeFalse();
    });

    it('denies users with only menus-view permission', function () {
        $this->user->givePermissionTo('menus-view');

        expect($this->policy->reorder($this->user))->toBeFalse();
    });
});
