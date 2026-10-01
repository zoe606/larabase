<?php

use App\Actions\Role\DeleteRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = new DeleteRole;
});

it('deletes a role', function () {
    $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);

    $result = $this->action->handle(['id' => $role->id]);

    expect($result)->toBeTrue()
        ->and(Role::find($role->id))->toBeNull();
});

it('removes permissions before deletion', function () {
    Permission::create(['name' => 'posts-create', 'guard_name' => 'web']);

    $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
    $role->givePermissionTo('posts-create');

    $this->action->handle(['id' => $role->id]);

    expect(Role::find($role->id))->toBeNull();
});

it('throws exception for non-existent role', function () {
    $this->action->handle(['id' => 99999]);
})->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
