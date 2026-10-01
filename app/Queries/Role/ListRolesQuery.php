<?php

declare(strict_types=1);

namespace App\Queries\Role;

use App\Contracts\QueryInterface;
use App\Queries\Concerns\Paginatable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

final class ListRolesQuery implements QueryInterface
{
    use Paginatable;

    /**
     * List roles with optional filters.
     *
     * @param  array{search?: string|null, with_permissions?: bool, paginate?: bool, per_page?: int}  $filters
     * @return LengthAwarePaginator<Role>|Collection<int, Role>
     */
    public function handle(array $filters = []): LengthAwarePaginator|Collection
    {
        $query = Role::query()
            ->when($filters['with_permissions'] ?? true, fn ($q) => $q->with('permissions'))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name');

        return $this->paginateOrGet($query, $filters);
    }
}
