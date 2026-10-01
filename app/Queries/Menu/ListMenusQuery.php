<?php

declare(strict_types=1);

namespace App\Queries\Menu;

use App\Contracts\QueryInterface;
use App\Models\Menu;
use App\Queries\Concerns\Paginatable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class ListMenusQuery implements QueryInterface
{
    use Paginatable;

    /**
     * List menus with optional filters.
     *
     * @param  array{search?: string|null, root_only?: bool, with_children?: bool, paginate?: bool, per_page?: int}  $filters
     * @return LengthAwarePaginator<Menu>|Collection<int, Menu>
     */
    public function handle(array $filters = []): LengthAwarePaginator|Collection
    {
        $query = Menu::query()
            ->when($filters['with_children'] ?? false, fn ($q) => $q->with('children'))
            ->withSearch($filters['search'] ?? null)
            ->when($filters['root_only'] ?? false, fn ($q) => $q->root())
            ->ordered();

        return $this->paginateOrGet($query, $filters);
    }
}
