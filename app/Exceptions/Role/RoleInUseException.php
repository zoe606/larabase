<?php

declare(strict_types=1);

namespace App\Exceptions\Role;

use App\Enums\HttpStatus;
use App\Exceptions\DomainException;

final class RoleInUseException extends DomainException
{
    public function __construct(string $roleName, int $userCount)
    {
        parent::__construct(
            message: "Cannot delete role [{$roleName}]. It is assigned to {$userCount} user(s).",
            status: HttpStatus::CONFLICT
        );
    }
}
