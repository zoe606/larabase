<?php

declare(strict_types=1);

namespace App\Queries\User;

use App\Contracts\QueryInterface;
use App\Models\User;

final class GetUserQuery implements QueryInterface
{
    /**
     * Get a single user by ID.
     *
     * @param  array{id?: int}  $filters
     */
    public function handle(array $filters = []): ?User
    {
        return User::query()
            ->with(['roles', 'permissions', 'profile'])
            ->find($filters['id']);
    }
}
