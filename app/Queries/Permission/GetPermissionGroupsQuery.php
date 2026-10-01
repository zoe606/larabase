<?php

declare(strict_types=1);

namespace App\Queries\Permission;

use App\Contracts\QueryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;

final class GetPermissionGroupsQuery implements QueryInterface
{
    /**
     * Cache key for grouped permissions.
     */
    private const CACHE_KEY = 'permissions_grouped';

    /**
     * Cache TTL in seconds (1 hour - permissions rarely change).
     */
    private const CACHE_TTL = 3600;

    /**
     * Get permissions grouped by their group field.
     * Uses caching since permissions rarely change.
     *
     * @param  array{fresh?: bool}  $filters  Set 'fresh' to true to bypass cache
     * @return Collection<string|null, Collection<int, Permission>>
     */
    public function handle(array $filters = []): Collection
    {
        // Allow bypassing cache when needed (e.g., after creating/updating permissions)
        if ($filters['fresh'] ?? false) {
            Cache::forget(self::CACHE_KEY);
        }

        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Permission::query()
                ->orderBy('group')
                ->orderBy('name')
                ->get()
                ->groupBy('group');
        });
    }

    /**
     * Clear the cached permissions.
     * Call this after creating, updating, or deleting permissions.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
