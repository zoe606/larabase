<?php

declare(strict_types=1);

namespace App\Queries\Permission;

use App\Contracts\QueryInterface;
use App\Queries\Concerns\Paginatable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

final class ListPermissionsQuery implements QueryInterface
{
    use Paginatable;

    /**
     * List permissions with optional filters.
     *
     * @param  array{search?: string|null, group?: string|null, paginate?: bool, per_page?: int}  $filters
     * @return LengthAwarePaginator<Permission>|Collection<int, Permission>
     */
    public function handle(array $filters = []): LengthAwarePaginator|Collection
    {
        $query = Permission::query()
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', '%'.$search.'%'))
            ->when($filters['group'] ?? null, fn ($q, $group) => $q->where('group', $group))
            ->orderBy('group')
            ->orderBy('name');

        return $this->paginateOrGet($query, $filters);
    }
}
