<?php

declare(strict_types=1);

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::create(['name' => 'users-view', 'guard_name' => 'web']);
    Permission::create(['name' => 'users-create', 'guard_name' => 'web']);
    Permission::create(['name' => 'users-edit', 'guard_name' => 'web']);
    Permission::create(['name' => 'users-delete', 'guard_name' => 'web']);

    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
    $this->policy = new UserPolicy;
});

describe('viewAny', function () {
    it('allows users with users-view permission', function () {
        $this->user->givePermissionTo('users-view');

        expect($this->policy->viewAny($this->user))->toBeTrue();
    });

    it('denies users without users-view permission', function () {
        expect($this->policy->viewAny($this->user))->toBeFalse();
    });
});

describe('view', function () {
    it('allows users with users-view permission', function () {
        $this->user->givePermissionTo('users-view');

        expect($this->policy->view($this->user, $this->otherUser))->toBeTrue();
    });

    it('denies users without users-view permission', function () {
        expect($this->policy->view($this->user, $this->otherUser))->toBeFalse();
    });
});

describe('create', function () {
    it('allows users with users-create permission', function () {
        $this->user->givePermissionTo('users-create');

        expect($this->policy->create($this->user))->toBeTrue();
    });

    it('denies users without users-create permission', function () {
        expect($this->policy->create($this->user))->toBeFalse();
    });
});

describe('update', function () {
    it('allows users with users-edit permission', function () {
        $this->user->givePermissionTo('users-edit');

        expect($this->policy->update($this->user, $this->otherUser))->toBeTrue();
    });

    it('denies users without users-edit permission', function () {
        expect($this->policy->update($this->user, $this->otherUser))->toBeFalse();
    });
});

describe('delete', function () {
    it('allows users with users-delete permission to delete another user', function () {
        $this->user->givePermissionTo('users-delete');

        expect($this->policy->delete($this->user, $this->otherUser))->toBeTrue();
    });

    it('denies users without users-delete permission', function () {
        expect($this->policy->delete($this->user, $this->otherUser))->toBeFalse();
    });

    it('denies self-deletion even with users-delete permission', function () {
        $this->user->givePermissionTo('users-delete');

        expect($this->policy->delete($this->user, $this->user))->toBeFalse();
    });

    it('denies self-deletion without permission', function () {
        expect($this->policy->delete($this->user, $this->user))->toBeFalse();
    });
});

describe('resetPassword', function () {
    it('allows users with users-edit permission', function () {
        $this->user->givePermissionTo('users-edit');

        expect($this->policy->resetPassword($this->user, $this->otherUser))->toBeTrue();
    });

    it('denies users without users-edit permission', function () {
        expect($this->policy->resetPassword($this->user, $this->otherUser))->toBeFalse();
    });
});
