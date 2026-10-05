<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. If maintenance mode is NOT active, proceed normally
        if (! static::isMaintenanceMode()) {
            return $next($request);
        }

        // 2. Health check endpoint (/up) is always allowed for monitoring
        if ($request->is('up')) {
            return $next($request);
        }

        // 3. Admin panel routes are ALWAYS accessible so admins can log in and toggle maintenance
        if ($request->is('admin', 'admin/*')) {
            return $next($request);
        }

        // 4. Main domain /login for administrators (when not on POS subdomain / port)
        $posPort = (int) env('POS_LOCAL_PORT', 8001);
        $posHost = parse_url(config('pos.subdomain_url', 'https://pos.planttechagro.com'), PHP_URL_HOST);
        $posHosts = array_unique(array_filter([$posHost, 'pos.planttechagro.com', 'pos.localhost']));
        $isPosHost = in_array($request->getHost(), $posHosts, true) || (int) $request->getPort() === $posPort;

        if ($request->is('login') && ! $isPosHost) {
            return $next($request);
        }

        // 5. App config endpoint (/api/app-config) must be accessible so mobile apps can detect maintenance mode
        if ($request->is('api/app-config')) {
            return $next($request);
        }

        // 6. Authenticated Admins are granted full bypass (allows admin frontend preview and testing)
        if (Auth::guard('admin')->check()) {
            return $next($request);
        }

        // 7. API / JSON requests receive HTTP 503 JSON response
        if ($request->is('api', 'api/*') || $request->expectsJson()) {
            return response()->json([
                'maintenance' => true,
                'message' => config('shop.maintenance_message')
                    ?: config('mobile.maintenance_message')
                    ?: 'System is currently undergoing scheduled maintenance. Please check back shortly.',
                'retry_after' => 300,
            ], 503, [
                'Retry-After' => '300',
            ]);
        }

        // 8. Public web visitors receive HTTP 503 Maintenance Page
        return response()->view('errors.maintenance', [
            'title' => config('shop.maintenance_title') ?: "We're Undergoing Scheduled Maintenance",
            'message' => config('shop.maintenance_message') ?: 'We are currently performing scheduled upgrades and essential optimizations to improve your experience. Our services and catalog will be back online shortly. Thank you for your patience.',
        ], 503, [
            'Retry-After' => '300',
        ]);
    }

    /**
     * Check if maintenance mode is enabled across the application.
     */
    public static function isMaintenanceMode(): bool
    {
        return (bool) config('mobile.maintenance_mode', false)
            || (bool) config('shop.maintenance_mode', false);
    }
}
