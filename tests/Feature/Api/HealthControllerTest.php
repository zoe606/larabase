<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns healthy status when all checks pass', function () {
    $response = $this->getJson('/api/health');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'status',
                'timestamp',
                'version',
                'environment',
                'checks' => [
                    'database',
                    'cache',
                    'queue',
                ],
            ],
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'status' => 'healthy',
                'checks' => [
                    'database' => true,
                ],
            ],
        ]);
});

it('does not require authentication', function () {
    // Health endpoint should be public for load balancers
    $response = $this->getJson('/api/health');

    $response->assertOk();
});

it('returns version from config', function () {
    config(['app.version' => '2.0.0']);

    $response = $this->getJson('/api/health');

    $response->assertOk()
        ->assertJsonPath('data.version', '2.0.0');
});

it('returns current environment', function () {
    $response = $this->getJson('/api/health');

    $response->assertOk()
        ->assertJsonPath('data.environment', 'testing');
});

it('includes timestamp in ISO format', function () {
    $response = $this->getJson('/api/health');

    $response->assertOk();

    $timestamp = $response->json('data.timestamp');
    expect($timestamp)->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
});

it('returns all check results', function () {
    $response = $this->getJson('/api/health');

    $response->assertOk();

    $checks = $response->json('data.checks');
    expect($checks)->toHaveKeys(['database', 'cache', 'queue']);
});
