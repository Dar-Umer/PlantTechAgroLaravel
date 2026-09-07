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
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(SecurityHeaders::class);

        $middleware->alias([
            'admin' => AdminMiddleware::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('invoices:process-overdue')->dailyAt('00:15');
        $schedule->command('work-orders:send-followups')->dailyAt('08:00');
        $schedule->command('leads:escalate')->dailyAt('08:05');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
