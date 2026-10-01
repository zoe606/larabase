<?php

declare(strict_types=1);

namespace App\Queries\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Provides common pagination logic for List queries.
 * Extracts the repeated pagination pattern found across multiple query classes.
 */
trait Paginatable
{
    /**
     * Apply pagination or return all results based on filters.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  array{paginate?: bool, per_page?: int}  $filters
     * @return LengthAwarePaginator<TModel>|Collection<int, TModel>
     */
    protected function paginateOrGet(Builder $query, array $filters): LengthAwarePaginator|Collection
    {
        // Return all results if pagination is explicitly disabled
        if (isset($filters['paginate']) && $filters['paginate'] === false) {
            return $query->get();
        }

        return $query->paginate($filters['per_page'] ?? $this->getDefaultPerPage());
    }

    /**
     * Get the default number of items per page.
     * Override in implementing class to customize.
     */
    protected function getDefaultPerPage(): int
    {
        return 15;
    }
}
