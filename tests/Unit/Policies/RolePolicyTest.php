<?php

declare(strict_types=1);

use App\Models\User;
use App\Policies\RolePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::create(['name' => 'roles-view', 'guard_name' => 'web']);
    Permission::create(['name' => 'roles-create', 'guard_name' => 'web']);
    Permission::create(['name' => 'roles-edit', 'guard_name' => 'web']);
    Permission::create(['name' => 'roles-delete', 'guard_name' => 'web']);

    $this->user = User::factory()->create();
    $this->role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
    $this->adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
    $this->policy = new RolePolicy;
});

describe('viewAny', function () {
    it('allows users with roles-view permission', function () {
        $this->user->givePermissionTo('roles-view');

        expect($this->policy->viewAny($this->user))->toBeTrue();
    });

    it('denies users without roles-view permission', function () {
        expect($this->policy->viewAny($this->user))->toBeFalse();
    });
});

describe('view', function () {
    it('allows users with roles-view permission', function () {
        $this->user->givePermissionTo('roles-view');

        expect($this->policy->view($this->user, $this->role))->toBeTrue();
    });

    it('denies users without roles-view permission', function () {
        expect($this->policy->view($this->user, $this->role))->toBeFalse();
    });
});

describe('create', function () {
    it('allows users with roles-create permission', function () {
        $this->user->givePermissionTo('roles-create');

        expect($this->policy->create($this->user))->toBeTrue();
    });

    it('denies users without roles-create permission', function () {
        expect($this->policy->create($this->user))->toBeFalse();
    });
});

describe('update', function () {
    it('allows users with roles-edit permission to update non-admin role', function () {
        $this->user->givePermissionTo('roles-edit');

        expect($this->policy->update($this->user, $this->role))->toBeTrue();
    });

    it('denies users without roles-edit permission', function () {
        expect($this->policy->update($this->user, $this->role))->toBeFalse();
    });

    it('denies non-admin users from updating the admin role', function () {
        $this->user->givePermissionTo('roles-edit');

        expect($this->policy->update($this->user, $this->adminRole))->toBeFalse();
    });

    it('allows admin users to update the admin role', function () {
        $this->user->assignRole('admin');
        $this->user->givePermissionTo('roles-edit');

        expect($this->policy->update($this->user, $this->adminRole))->toBeTrue();
    });
});

describe('delete', function () {
    it('allows users with roles-delete permission to delete non-admin role', function () {
        $this->user->givePermissionTo('roles-delete');

        expect($this->policy->delete($this->user, $this->role))->toBeTrue();
    });

    it('denies users without roles-delete permission', function () {
        expect($this->policy->delete($this->user, $this->role))->toBeFalse();
    });

    it('denies deletion of the admin role even with permission', function () {
        $this->user->givePermissionTo('roles-delete');

        expect($this->policy->delete($this->user, $this->adminRole))->toBeFalse();
    });

    it('denies deletion of admin role even for admin users', function () {
        $this->user->assignRole('admin');
        $this->user->givePermissionTo('roles-delete');

        expect($this->policy->delete($this->user, $this->adminRole))->toBeFalse();
    });
});
