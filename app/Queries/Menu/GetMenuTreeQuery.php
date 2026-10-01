<?php

declare(strict_types=1);

namespace App\Queries\Menu;

use App\Contracts\QueryInterface;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Collection;

final class GetMenuTreeQuery implements QueryInterface
{
    /**
     * Get the full menu tree structure.
     *
     * @param  array{user?: User}  $filters
     */
    public function handle(array $filters = []): Collection
    {
        $query = Menu::query()
            ->root()
            ->with(['children' => fn ($q) => $q->orderBy('order')])
            ->orderBy('order');

        // Filter by user permissions if user is provided
        if (isset($filters['user'])) {
            $query->forUser($filters['user']);
        }

        return $query->get();
    }
}
