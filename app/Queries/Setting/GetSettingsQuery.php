<?php

declare(strict_types=1);

namespace App\Queries\Setting;

use App\Contracts\QueryInterface;
use App\Models\SettingApp;
use Illuminate\Support\Facades\Cache;

final class GetSettingsQuery implements QueryInterface
{
    /**
     * Cache key for application settings.
     */
    private const CACHE_KEY = 'app_settings';

    /**
     * Cache TTL in seconds (24 hours - settings change infrequently).
     */
    private const CACHE_TTL = 86400;

    /**
     * Get the application settings (singleton record).
     * Uses caching since settings change infrequently.
     *
     * @param  array{fresh?: bool}  $filters  Set 'fresh' to true to bypass cache
     */
    public function handle(array $filters = []): ?SettingApp
    {
        // Allow bypassing cache when needed (e.g., after updating settings)
        if ($filters['fresh'] ?? false) {
            Cache::forget(self::CACHE_KEY);
        }

        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return SettingApp::query()->first();
        });
    }

    /**
     * Clear the cached settings.
     * Call this after updating settings.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
