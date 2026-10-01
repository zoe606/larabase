<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Menu;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class ShareMenus
{
    /**
     * Cache key prefix for menus.
     */
    private const CACHE_KEY_PREFIX = 'menus_tree_user_';

    /**
     * Cache TTL in seconds (30 minutes - menus rarely change).
     */
    private const CACHE_TTL = 1800;

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        Inertia::share('menus', function () use ($user) {
            if (! $user) {
                return [];
            }

            $cacheKey = self::CACHE_KEY_PREFIX.$user->id;

            return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
                return $this->buildMenuTree($user);
            });
        });

        return $next($request);
    }

    /**
     * Build the menu tree for a user.
     *
     * @return Collection<int, Menu>
     */
    private function buildMenuTree(mixed $user): Collection
    {
        // Get all menus in one query
        $allMenus = Menu::query()->orderBy('order')->get();

        // Index by ID for efficient lookups
        $indexed = $allMenus->keyBy('id');

        // Recursive builder (filtered by permission)
        $buildTree = function (?int $parentId = null) use (&$buildTree, $indexed, $user): Collection {
            return $indexed
                ->filter(
                    fn ($menu) => $menu->parent_id === $parentId &&
                        (! $menu->permission_name || $user->can($menu->permission_name))
                )
                ->map(function ($menu) use (&$buildTree) {
                    $menu->setAttribute('children', $buildTree($menu->id)->values());

                    return $menu;
                })
                ->filter(
                    fn ($menu) => $menu->route || $menu->children->isNotEmpty()
                )
                ->values();
        };

        return $buildTree(null);
    }

    /**
     * Clear menu cache for a specific user.
     */
    public static function clearCacheForUser(int $userId): void
    {
        Cache::forget(self::CACHE_KEY_PREFIX.$userId);
    }

    /**
     * Clear all menu caches.
     * Call this after creating, updating, or deleting menus.
     */
    public static function clearAllCache(): void
    {
        // Since we use user-specific cache keys, we need to clear by pattern
        // For simple cases, just flush the cache tag or use a different strategy
        // Here we'll clear the entire cache for menus (in production, use cache tags)
        Cache::flush();
    }
}
