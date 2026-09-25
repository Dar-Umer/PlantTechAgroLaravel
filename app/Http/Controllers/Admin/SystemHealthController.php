<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class SystemHealthController extends Controller
{
    /**
     * Dedicated System Health & Diagnostics Dashboard Page.
     */
    public function index(Request $request): View
    {
        $stats = $this->gatherStats();

        $tables = [];
        try {
            $dbName = DB::connection()->getDatabaseName();
            $tables = DB::select("SELECT table_name, table_rows, round(((data_length + index_length) / 1024), 2) AS size_kb FROM information_schema.TABLES WHERE table_schema = ? ORDER BY (data_length + index_length) DESC LIMIT 15", [$dbName]);
        } catch (\Throwable $e) {}

        return view('admin.system.diagnostics', compact('stats', 'tables'));
    }

    /**
     * Fetch comprehensive live performance and health diagnostics as JSON.
     */
    public function stats(Request $request): JsonResponse
    {
        return response()->json($this->gatherStats());
    }

    /**
     * Gather system health metrics.
     */
    protected function gatherStats(): array
    {
        // Database metrics
        $dbConnected = false;
        $dbLatency = 0.0;
        $dbVersion = 'Unknown';
        $dbName = '';
        $tablesCount = 0;
        $dbSizeMb = 0.0;

        try {
            $dbStart = microtime(true);
            DB::select('SELECT 1');
            $dbLatency = round((microtime(true) - $dbStart) * 1000, 2);
            $dbConnected = true;

            $pdo = DB::connection()->getPdo();
            $dbVersion = $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
            $dbName = DB::connection()->getDatabaseName();

            $tables = DB::select('SHOW TABLES');
            $tablesCount = count($tables);

            $sizeQuery = DB::select("SELECT SUM(data_length + index_length) AS size_bytes FROM information_schema.TABLES WHERE table_schema = ?", [$dbName]);
            $dbSizeMb = round(($sizeQuery[0]->size_bytes ?? 0) / 1024 / 1024, 2);
        } catch (\Throwable $e) {
            $dbConnected = false;
        }

        // Disk metrics
        $diskFree = @disk_free_space(base_path()) ?: 0;
        $diskTotal = @disk_total_space(base_path()) ?: 0;
        $diskUsedPercent = $diskTotal > 0 ? round((($diskTotal - $diskFree) / $diskTotal) * 100, 1) : 0;

        // Cache speed test
        $cacheSpeed = 0.0;
        try {
            $cacheStart = microtime(true);
            Cache::put('_pta_health_test', 1, 10);
            Cache::get('_pta_health_test');
            $cacheSpeed = round((microtime(true) - $cacheStart) * 1000, 2);
            Cache::forget('_pta_health_test');
        } catch (\Throwable $e) {
            $cacheSpeed = -1;
        }

        // OPcache metrics
        $opcacheEnabled = function_exists('opcache_get_status') && !empty(opcache_get_status(false)['opcache_enabled']);
        $opcacheMemory = null;
        if ($opcacheEnabled) {
            $status = opcache_get_status(false);
            if (!empty($status['memory_usage'])) {
                $opcacheMemory = [
                    'used_mb' => round($status['memory_usage']['used_memory'] / 1024 / 1024, 1),
                    'free_mb' => round($status['memory_usage']['free_memory'] / 1024 / 1024, 1),
                    'wasted_mb' => round($status['memory_usage']['wasted_memory'] / 1024 / 1024, 1),
                ];
            }
        }

        // Storage symlink check
        $storageSymlinkOk = File::exists(public_path('storage'));

        // Background jobs / queue metrics
        $failedJobsCount = 0;
        try {
            if (DB::getSchemaBuilder()->hasTable('failed_jobs')) {
                $failedJobsCount = DB::table('failed_jobs')->count();
            }
        } catch (\Throwable $e) {}

        return [
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'database' => [
                'connected' => $dbConnected,
                'latency_ms' => $dbLatency,
                'version' => $dbVersion,
                'name' => $dbName,
                'tables_count' => $tablesCount,
                'size_mb' => $dbSizeMb,
            ],
            'server' => [
                'php_version' => PHP_VERSION,
                'php_sapi' => PHP_SAPI,
                'memory_limit' => ini_get('memory_limit'),
                'current_memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
                'max_execution_time' => ini_get('max_execution_time') . 's',
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'opcache_enabled' => $opcacheEnabled,
                'opcache_memory' => $opcacheMemory,
            ],
            'storage' => [
                'free_gb' => round($diskFree / 1024 / 1024 / 1024, 1),
                'total_gb' => round($diskTotal / 1024 / 1024 / 1024, 1),
                'used_percent' => $diskUsedPercent,
                'symlink_ok' => $storageSymlinkOk,
            ],
            'application' => [
                'laravel_version' => app()->version(),
                'environment' => app()->environment(),
                'debug' => config('app.debug'),
                'cache_driver' => config('cache.default'),
                'cache_latency_ms' => $cacheSpeed,
                'session_driver' => config('session.driver'),
                'queue_driver' => config('queue.default'),
                'failed_jobs' => $failedJobsCount,
                'timezone' => config('app.timezone'),
            ],
        ];
    }

    /**
     * Clear compiled Blade views.
     */
    public function clearViews(Request $request): JsonResponse
    {
        try {
            Artisan::call('view:clear');
            return response()->json([
                'success' => true,
                'message' => 'Compiled Blade views cleared successfully!',
                'output' => trim(Artisan::output()),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear view cache: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear all application caches (config, route, cache).
     */
    public function clearAllCache(Request $request): JsonResponse
    {
        try {
            Artisan::call('optimize:clear');
            return response()->json([
                'success' => true,
                'message' => 'Application, routes, and config caches cleared successfully!',
                'output' => trim(Artisan::output()),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage(),
            ], 500);
        }
    }
}
