<?php

use App\Actions\User\DeleteUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = new DeleteUser;
});

it('deletes a user', function () {
    $user = User::factory()->create();

    $result = $this->action->handle(['id' => $user->id]);

    expect($result)->toBeTrue()
        ->and(User::find($user->id))->toBeNull();
});

it('removes user roles before deletion', function () {
    $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->action->handle(['id' => $user->id]);

    // User should be deleted
    expect(User::find($user->id))->toBeNull();
});

it('throws exception for non-existent user', function () {
    $this->action->handle(['id' => 99999]);
})->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
