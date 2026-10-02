<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(SecurityHeaders::class);

        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            $posPort = (int) env('POS_LOCAL_PORT', 8001);
            $posHost = parse_url(config('pos.subdomain_url', 'https://pos.planttechagro.com'), PHP_URL_HOST);
            $posHosts = array_unique(array_filter([$posHost, 'pos.planttechagro.com', 'pos.localhost']));

            if (in_array($request->getHost(), $posHosts, true) || (int) $request->getPort() === $posPort) {
                return route('pos.login');
            }

            return route('admin.login');
        });

        $middleware->redirectUsersTo(function (\Illuminate\Http\Request $request) {
            $posPort = (int) env('POS_LOCAL_PORT', 8001);
            $posHost = parse_url(config('pos.subdomain_url', 'https://pos.planttechagro.com'), PHP_URL_HOST);
            $posHosts = array_unique(array_filter([$posHost, 'pos.planttechagro.com', 'pos.localhost']));

            $admin = $request->user('admin');
            $isPos = in_array($request->getHost(), $posHosts, true)
                || (int) $request->getPort() === $posPort
                || ($admin && $admin->isPosOnly());

            return $isPos ? route('pos.terminal') : route('admin.dashboard');
        });
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('invoices:process-overdue')->dailyAt('00:15');
        $schedule->command('work-orders:send-followups')->dailyAt('08:00');
        $schedule->command('leads:escalate')->dailyAt('08:05');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
