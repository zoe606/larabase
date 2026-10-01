<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use App\Enums\HttpStatus;
use App\Exceptions\DomainException;

final class InvalidCredentialsException extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            message: 'The provided credentials are incorrect.',
            status: HttpStatus::UNAUTHORIZED
        );
    }
}
