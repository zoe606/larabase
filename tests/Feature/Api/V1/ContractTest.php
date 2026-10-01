<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

it('exposes only the conventional platform API', function () {
    $allowed = ['ping', 'health', 'auth', 'users', 'roles', 'permissions', 'menus', 'notifications'];

    foreach (Route::getRoutes() as $route) {
        $uri = $route->uri();
        if (! str_starts_with($uri, 'api/')) {
            continue;
        }

        $segments = explode('/', $uri);
        $resource = $segments[1] === 'v1' ? $segments[2] : $segments[1];
        expect($allowed)->toContain($resource);
    }

    $this->getJson('/api/ping')->assertExactJson(['status' => 'ok']);
});

it('keeps validation errors in the standard API envelope', function (string $endpoint) {
    $response = $this->postJson($endpoint, []);

    $response->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonStructure(['success', 'message', 'errors' => ['email', 'password']]);
    expect(array_keys($response->json()))->toBe(['success', 'message', 'errors']);
})->with(['/api/v1/auth/login', '/api/v1/auth/register']);

it('keeps authenticated platform lists in the standard pagination envelope', function (string $resource) {
    $user = User::factory()->create();
    $user->assignRole(Role::firstOrCreate(['name' => 'admin']));
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/'.$resource);

    $response->assertOk()->assertJsonPath('success', true)
        ->assertJsonStructure([
            'success', 'message', 'data',
            'meta' => ['current_page', 'last_page', 'per_page', 'total', 'from', 'to'],
            'links' => ['first', 'last', 'prev', 'next'],
        ]);
    expect(array_keys($response->json()))->toBe(['success', 'message', 'data', 'meta', 'links']);
})->with(['users', 'roles', 'permissions', 'menus', 'notifications']);
