<?php

declare(strict_types=1);

use App\Models\Menu;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Notification::fake();
    $this->seed(RolePermissionSeeder::class);
    $this->administrator = User::factory()->create();
    $this->administrator->assignRole('admin');
    $this->actingAs($this->administrator);
});

it('supports the web create read update delete flow', function (string $resource, string $model, array $data, array $changed, string $field) {
    $this->get(route($resource.'.index'))->assertOk()->assertInertia(fn ($page) => $page->component($resource.'/Index'));
    $this->get(route($resource.'.create'))->assertOk()->assertInertia(fn ($page) => $page->component($resource.'/Form'));
    $this->post(route($resource.'.store'), $data)->assertRedirect(route($resource.'.index'))->assertSessionHasNoErrors();
    $record = $model::where($field, $data[$field])->firstOrFail();
    $this->get(route($resource.'.edit', $record))->assertOk()->assertInertia(fn ($page) => $page->component($resource.'/Form'));
    $this->put(route($resource.'.update', $record), array_replace($data, $changed))
        ->assertRedirect(route($resource.'.index'))->assertSessionHasNoErrors();
    expect($record->fresh()->{$field})->toBe($changed[$field]);
    $this->delete(route($resource.'.destroy', $record))->assertRedirect(route($resource.'.index'));
    expect($model::find($record->id))->toBeNull();
})->with([
    ['menus', Menu::class, ['title' => 'Example menu'], ['title' => 'Updated menu'], 'title'],
    ['permissions', Permission::class, ['name' => 'example-view', 'group' => 'Example'], ['name' => 'example-edit'], 'name'],
    ['roles', Role::class, ['name' => 'example-role', 'permissions' => ['users-view']], ['name' => 'updated-role'], 'name'],
    ['users', User::class, ['name' => 'Example user', 'email' => 'example@example.com', 'password' => 'password123', 'password_confirmation' => 'password123', 'roles' => ['user']], ['email' => 'updated@example.com'], 'email'],
]);

it('reorders menus through the web endpoint', function () {
    $first = Menu::factory()->create();
    $second = Menu::factory()->create();
    $this->post(route('menus.reorder'), ['menus' => [['id' => $second->id], ['id' => $first->id]]])
        ->assertRedirect()->assertSessionHasNoErrors();
    expect($second->fresh()->order)->toBeLessThan($first->fresh()->order);
});

it('resets a user password through the web endpoint', function () {
    $user = User::factory()->create();
    $previous = $user->password;
    $this->put(route('users.reset-password', $user))->assertRedirect();
    expect($user->fresh()->password)->not->toBe($previous);
    Notification::assertSentTo($user, App\Notifications\PasswordResetNotification::class);
});
