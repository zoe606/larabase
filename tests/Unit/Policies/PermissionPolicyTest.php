<?php

declare(strict_types=1);

use App\Models\User;
use App\Policies\PermissionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::create(['name' => 'permissions-view', 'guard_name' => 'web']);
    Permission::create(['name' => 'permissions-create', 'guard_name' => 'web']);
    Permission::create(['name' => 'permissions-edit', 'guard_name' => 'web']);
    Permission::create(['name' => 'permissions-delete', 'guard_name' => 'web']);

    $this->user = User::factory()->create();
    $this->permission = Permission::create(['name' => 'test-permission', 'guard_name' => 'web']);
    $this->policy = new PermissionPolicy;
});

describe('viewAny', function () {
    it('allows users with permissions-view permission', function () {
        $this->user->givePermissionTo('permissions-view');

        expect($this->policy->viewAny($this->user))->toBeTrue();
    });

    it('denies users without permissions-view permission', function () {
        expect($this->policy->viewAny($this->user))->toBeFalse();
    });
});

describe('view', function () {
    it('allows users with permissions-view permission', function () {
        $this->user->givePermissionTo('permissions-view');

        expect($this->policy->view($this->user, $this->permission))->toBeTrue();
    });

    it('denies users without permissions-view permission', function () {
        expect($this->policy->view($this->user, $this->permission))->toBeFalse();
    });
});

describe('create', function () {
    it('allows users with permissions-create permission', function () {
        $this->user->givePermissionTo('permissions-create');

        expect($this->policy->create($this->user))->toBeTrue();
    });

    it('denies users without permissions-create permission', function () {
        expect($this->policy->create($this->user))->toBeFalse();
    });
});

describe('update', function () {
    it('allows users with permissions-edit permission', function () {
        $this->user->givePermissionTo('permissions-edit');

        expect($this->policy->update($this->user, $this->permission))->toBeTrue();
    });

    it('denies users without permissions-edit permission', function () {
        expect($this->policy->update($this->user, $this->permission))->toBeFalse();
    });
});

describe('delete', function () {
    it('allows users with permissions-delete permission', function () {
        $this->user->givePermissionTo('permissions-delete');

        expect($this->policy->delete($this->user, $this->permission))->toBeTrue();
    });

    it('denies users without permissions-delete permission', function () {
        expect($this->policy->delete($this->user, $this->permission))->toBeFalse();
    });
});
