<?php

declare(strict_types=1);

namespace App\Queries\Permission;

use App\Contracts\QueryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;

final class GetDistinctGroupsQuery implements QueryInterface
{
    /**
     * Cache key for distinct permission groups.
     */
    private const CACHE_KEY = 'permission_distinct_groups';

    /**
     * Cache TTL in seconds (1 hour - permission groups rarely change).
     */
    private const CACHE_TTL = 3600;

    /**
     * Get distinct permission group names.
     * Uses caching since permission groups rarely change.
     *
     * @param  array{fresh?: bool}  $filters  Set 'fresh' to true to bypass cache
     * @return Collection<int, string>
     */
    public function handle(array $filters = []): Collection
    {
        // Allow bypassing cache when needed
        if ($filters['fresh'] ?? false) {
            Cache::forget(self::CACHE_KEY);
        }

        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Permission::query()
                ->select('group')
                ->distinct()
                ->whereNotNull('group')
                ->orderBy('group')
                ->pluck('group');
        });
    }

    /**
     * Clear the cached groups.
     * Call this after creating, updating, or deleting permissions.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
