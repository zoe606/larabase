<?php

declare(strict_types=1);

describe('CORS Configuration', function () {
    it('returns CORS headers on preflight OPTIONS request', function () {
        $response = $this->options('/api/v1/auth/user', [], [
            'Origin' => 'http://localhost:8081',
            'Access-Control-Request-Method' => 'GET',
            'Access-Control-Request-Headers' => 'Authorization, Content-Type',
        ]);

        $response->assertHeader('Access-Control-Allow-Origin');
        $response->assertHeader('Access-Control-Allow-Methods');
    });

    it('includes Authorization in allowed headers', function () {
        $response = $this->options('/api/v1/auth/user', [], [
            'Origin' => 'http://localhost:8081',
            'Access-Control-Request-Method' => 'GET',
            'Access-Control-Request-Headers' => 'Authorization',
        ]);

        $allowedHeaders = strtolower($response->headers->get('Access-Control-Allow-Headers', ''));
        expect($allowedHeaders)->toContain('authorization');
    });

    it('supports credentials', function () {
        $response = $this->options('/api/v1/auth/user', [], [
            'Origin' => 'http://localhost:8081',
            'Access-Control-Request-Method' => 'GET',
        ]);

        $response->assertHeader('Access-Control-Allow-Credentials', 'true');
    });
});
