<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $posPort = (int) env('POS_LOCAL_PORT', 8001);
        $posHost = parse_url(config('pos.subdomain_url', 'https://pos.planttechagro.com'), PHP_URL_HOST);
        $posHosts = array_unique(array_filter([$posHost, 'pos.planttechagro.com', 'pos.localhost']));
        $isPos = in_array($request->getHost(), $posHosts, true) || (int) $request->getPort() === $posPort;

        if (! Auth::guard('admin')->check()) {
            return $isPos
                ? redirect()->route('pos.login')
                : redirect()->route('admin.login');
        }

        $admin = Auth::guard('admin')->user();

        if (! $admin->is_active) {
            Auth::guard('admin')->logout();

            return ($isPos ? redirect()->route('pos.login') : redirect()->route('admin.login'))
                ->with('error', 'Your account has been deactivated.');
        }

        // Strict lockdown for POS Operator: prevent accessing non-POS components like work orders, CRM, settings
        if ($admin && $admin->isPosOnly()) {
            $allowedPatterns = [
                'pos*',
                'sales*',
                'terminal*',
                'checkout*',
                'products*',
                'customers*',
                'stock-movements*',
                'product-batches*',
                'admin/pos*',
                'admin/products*',
                'admin/stock-movements*',
                'admin/product-batches*',
                'logout',
                'admin/logout',
            ];

            $isAllowed = false;
            foreach ($allowedPatterns as $pattern) {
                if ($request->is($pattern)) {
                    $isAllowed = true;
                    break;
                }
            }

            if (! $isAllowed) {
                return redirect()->route('pos.terminal')
                    ->with('error', 'Access restricted: Your account only has access to the POS terminal and inventory modules.');
            }
        }

        // On POS domain or port, redirect legacy /admin/* URLs to clean POS URLs
        if ($isPos) {
            if ($request->is('admin/pos/sales*')) {
                return redirect()->route('pos.sales');
            }
            if ($request->is('admin/pos*') || $request->is('admin/dashboard*') || $request->is('admin')) {
                return redirect()->route('pos.terminal');
            }
            if ($request->is('admin/*') && ! $request->is('admin/logout') && ! $request->is('admin/products*') && ! $request->is('admin/stock-movements*') && ! $request->is('admin/product-batches*')) {
                return redirect()->route('pos.terminal');
            }
        }

        return $next($request);
    }
}
