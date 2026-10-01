<?php

declare(strict_types=1);

namespace App\Queries\Role;

use App\Contracts\QueryInterface;
use Spatie\Permission\Models\Role;

final class GetRoleQuery implements QueryInterface
{
    /**
     * Get a single role by ID.
     *
     * @param  array{id?: int}  $filters
     */
    public function handle(array $filters = []): ?Role
    {
        return Role::query()
            ->with('permissions')
            ->find($filters['id']);
    }
}
