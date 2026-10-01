<?php

declare(strict_types=1);

namespace App\Exceptions\User;

use App\Enums\HttpStatus;
use App\Exceptions\DomainException;

final class UserCannotDeleteSelfException extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            message: 'You cannot delete your own account.',
            status: HttpStatus::FORBIDDEN
        );
    }
}
