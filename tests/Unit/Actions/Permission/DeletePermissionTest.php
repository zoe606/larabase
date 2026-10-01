<?php

use App\Actions\Permission\DeletePermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = new DeletePermission;
});

it('deletes a permission', function () {
    $permission = Permission::create([
        'name' => 'users-create',
        'guard_name' => 'web',
    ]);

    $result = $this->action->handle(['id' => $permission->id]);

    expect($result)->toBeTrue()
        ->and(Permission::find($permission->id))->toBeNull();
});

it('throws exception for non-existent permission', function () {
    $this->action->handle(['id' => 99999]);
})->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
