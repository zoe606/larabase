<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\HttpStatus;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

final class HealthController
{
    /**
     * Health check endpoint for monitoring and load balancers.
     */
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
        ];

        $healthy = $this->allHealthy($checks);

        return ApiResponse::success(
            data: [
                'status' => $healthy ? 'healthy' : 'degraded',
                'timestamp' => now()->toISOString(),
                'version' => config('app.version', '1.0.0'),
                'environment' => app()->environment(),
                'checks' => $checks,
            ],
            message: $healthy ? 'All systems operational' : 'Some systems degraded',
            status: $healthy ? HttpStatus::OK : HttpStatus::SERVICE_UNAVAILABLE
        );
    }

    /**
     * Check database connectivity.
     *
     * @return array{status: string, latency_ms?: float, message?: string}
     */
    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $latency = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => 'ok',
                'latency_ms' => $latency,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'message' => app()->isProduction()
                    ? 'Database connection failed'
                    : $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache connectivity.
     *
     * @return array{status: string, driver?: string, message?: string}
     */
    private function checkCache(): array
    {
        try {
            $key = 'health_check_'.uniqid();
            Cache::put($key, true, 10);
            $result = Cache::get($key);
            Cache::forget($key);

            return [
                'status' => $result ? 'ok' : 'error',
                'driver' => config('cache.default'),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'message' => app()->isProduction()
                    ? 'Cache connection failed'
                    : $e->getMessage(),
            ];
        }
    }

    /**
     * Check queue connectivity.
     *
     * @return array{status: string, driver?: string, pending_jobs?: int, message?: string}
     */
    private function checkQueue(): array
    {
        try {
            $driver = config('queue.default');

            // Skip queue check for sync driver
            if ($driver === 'sync') {
                return [
                    'status' => 'ok',
                    'driver' => $driver,
                ];
            }

            $size = Queue::size();

            return [
                'status' => 'ok',
                'driver' => $driver,
                'pending_jobs' => $size,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'message' => app()->isProduction()
                    ? 'Queue connection failed'
                    : $e->getMessage(),
            ];
        }
    }

    /**
     * Check if all health checks passed.
     *
     * @param  array<string, array{status: string}>  $checks
     */
    private function allHealthy(array $checks): bool
    {
        foreach ($checks as $check) {
            if ($check['status'] !== 'ok') {
                return false;
            }
        }

        return true;
    }
}
