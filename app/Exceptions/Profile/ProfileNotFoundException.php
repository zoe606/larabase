<?php

declare(strict_types=1);

namespace App\Exceptions\Profile;

use App\Enums\HttpStatus;
use App\Exceptions\DomainException;

final class ProfileNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            message: 'Profile not found.',
            status: HttpStatus::NOT_FOUND
        );
    }
}
