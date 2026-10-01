<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('renders audit logs page for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('audit-logs.index'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('auditlogs/Index')
            ->has('logs')
        );
});

it('redirects unauthenticated users to login', function () {
    $response = $this->get(route('audit-logs.index'));

    $response->assertRedirect(route('login'));
});
