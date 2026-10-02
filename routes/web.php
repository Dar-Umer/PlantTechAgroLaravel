<?php

use App\Http\Controllers\Admin\PosAuthController;
use App\Http\Controllers\Site\LandingController;
use App\Http\Controllers\Site\LeadController;
use App\Http\Controllers\Site\PostController;
use App\Http\Controllers\Site\ProjectController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Dedicated POS Subdomain Routing (e.g. pos.planttechagro.com / pos.localhost)
|--------------------------------------------------------------------------
| Directly route cashiers and retail terminals on the POS subdomain to the
| dedicated POS Terminal or POS Cashier Login page.
*/
$posHost = parse_url(config('pos.subdomain_url', 'https://pos.planttechagro.com'), PHP_URL_HOST) ?: 'pos.planttechagro.com';
$posHosts = array_unique(array_filter([$posHost, 'pos.planttechagro.com', 'pos.localhost']));

foreach ($posHosts as $host) {
    Route::domain($host)->group(function () {
        Route::get('/', function () {
            if (Auth::guard('admin')->check()) {
                return redirect()->route('admin.pos.terminal');
            }

            return redirect()->route('pos.login');
        })->name('pos.subdomain.root');

        Route::get('terminal', function () {
            return redirect()->route('admin.pos.terminal');
        })->name('pos.subdomain.terminal');

        // Dedicated POS Login on Subdomain (https://pos.planttechagro.com/login)
        Route::get('login', [PosAuthController::class, 'showLoginForm'])->name('pos.login');
        Route::post('login', [PosAuthController::class, 'login'])
            ->middleware('throttle:10,1')
            ->name('pos.login.submit');
        Route::post('logout', [PosAuthController::class, 'logout'])->name('pos.logout');
    });
}

// Fallback direct access on local/main domain (e.g. http://localhost:8000/pos/login)
Route::prefix('pos')->group(function () {
    Route::get('login', [PosAuthController::class, 'showLoginForm'])->name('pos.login.local');
    Route::post('login', [PosAuthController::class, 'login'])->middleware('throttle:10,1')->name('pos.login.local.submit');
    Route::post('logout', [PosAuthController::class, 'logout'])->name('pos.logout.local');
});

/*
|--------------------------------------------------------------------------
| Public Site Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::get('projects/{project:slug}', [ProjectController::class, 'show'])->name('project.show');

Route::get('blog/{post:slug}', [PostController::class, 'show'])->name('post.show');

Route::get('varieties', [\App\Http\Controllers\Site\VarietyController::class, 'index'])->name('varieties.index');

Route::post('leads', [LeadController::class, 'store'])
    ->middleware('throttle:leads')
    ->name('leads.store');

Route::prefix('admin')->group(function () {
    require __DIR__.'/admin.php';
});
