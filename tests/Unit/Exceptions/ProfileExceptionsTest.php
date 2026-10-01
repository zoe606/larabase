<?php

declare(strict_types=1);

use App\Enums\HttpStatus;
use App\Exceptions\Profile\ProfileNotFoundException;

it('ProfileNotFoundException renders with 404 Not Found', function () {
    $exception = new ProfileNotFoundException;
    $response = $exception->render();

    expect($response->getStatusCode())->toBe(HttpStatus::NOT_FOUND->value)
        ->and($response->getData(true)['success'])->toBeFalse()
        ->and($response->getData(true)['message'])->toContain('Profile not found');
});
