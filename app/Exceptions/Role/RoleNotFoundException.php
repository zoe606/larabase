<?php

declare(strict_types=1);

namespace App\Exceptions\Role;

use App\Enums\HttpStatus;
use App\Exceptions\DomainException;

final class RoleNotFoundException extends DomainException
{
    public function __construct(int|string $identifier)
    {
        parent::__construct(
            message: "Role with identifier [{$identifier}] not found.",
            status: HttpStatus::NOT_FOUND
        );
    }
}
