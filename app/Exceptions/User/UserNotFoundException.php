<?php

declare(strict_types=1);

namespace App\Exceptions\User;

use App\Enums\HttpStatus;
use App\Exceptions\DomainException;

final class UserNotFoundException extends DomainException
{
    public function __construct(int|string $identifier)
    {
        parent::__construct(
            message: "User with identifier [{$identifier}] not found.",
            status: HttpStatus::NOT_FOUND
        );
    }
}
