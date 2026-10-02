<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\PosAuthController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\PosSettingController;
use App\Http\Controllers\Site\LandingController;
use App\Http\Controllers\Site\LeadController;
use App\Http\Controllers\Site\PostController;
use App\Http\Controllers\Site\ProjectController;
use App\Http\Controllers\Site\VarietyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| POS Context Resolver
|--------------------------------------------------------------------------
| Identify if the current incoming request is on the POS subdomain or
| the dedicated local POS simulator port (e.g. port 8001).
*/
$posHost = parse_url(config('pos.subdomain_url', 'https://pos.planttechagro.com'), PHP_URL_HOST) ?: 'pos.planttechagro.com';
$posHosts = array_unique(array_filter([$posHost, 'pos.planttechagro.com', 'pos.localhost']));

$isPosRequest = function (Request $request) use ($posHosts): bool {
    $posPort = (int) env('POS_LOCAL_PORT', 8001);
    return in_array($request->getHost(), $posHosts, true) || (int) $request->getPort() === $posPort;
};

/*
|--------------------------------------------------------------------------
| Root Route ('/')
|--------------------------------------------------------------------------
| On the dedicated POS subdomain or port 8001:
|   - If authenticated, redirect directly to the POS terminal.
|   - If unauthenticated, redirect directly to the POS cashier login.
| On the main domain / port 8000:
|   - Render the public website landing page.
*/
Route::get('/', function (Request $request) use ($isPosRequest) {
    if ($isPosRequest($request)) {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('pos.terminal');
        }

        return redirect()->route('pos.login');
    }

    return app(LandingController::class)->index();
})->name('landing');

/*
|--------------------------------------------------------------------------
| Clean POS Authentication Routes ('/login', '/logout')
|--------------------------------------------------------------------------
*/
Route::match(['GET', 'POST'], 'login', function (Request $request) use ($isPosRequest) {
    if ($isPosRequest($request)) {
        $controller = app(PosAuthController::class);
        return $request->isMethod('POST')
            ? $controller->login($request)
            : $controller->showLoginForm();
    }

    if ($request->isMethod('POST')) {
        return app(LoginController::class)->login($request);
    }

    return redirect()->route('admin.login');
})->name('pos.login');

Route::post('logout', function (Request $request) use ($isPosRequest) {
    if ($isPosRequest($request)) {
        return app(PosAuthController::class)->logout($request);
    }

    return app(LoginController::class)->logout($request);
})->name('pos.logout');

// Fallback direct access on local prefix /pos/login
Route::prefix('pos')->group(function () {
    Route::get('login', [PosAuthController::class, 'showLoginForm'])->name('pos.login.local');
    Route::post('login', [PosAuthController::class, 'login'])->middleware('throttle:10,1')->name('pos.login.local.submit');
    Route::post('logout', [PosAuthController::class, 'logout'])->name('pos.logout.local');
});

/*
|--------------------------------------------------------------------------
| Dedicated Clean POS Routes (Without '/admin/')
|--------------------------------------------------------------------------
| Available directly as /terminal, /sales, /checkout, etc.
*/
Route::middleware(['auth:admin'])->group(function () {
    Route::get('terminal', [PosController::class, 'terminal'])->name('pos.terminal');
    Route::get('sales', [PosController::class, 'sales'])->name('pos.sales');
    Route::get('sales/{sale}', [PosController::class, 'show'])->name('pos.sales.show');
    Route::get('sales/{sale}/receipt', [PosController::class, 'receipt'])->name('pos.receipt');
    Route::get('sales/{sale}/invoice', [PosController::class, 'invoice'])->name('pos.invoice');
    Route::post('sales/{sale}/cancel', [PosController::class, 'cancel'])->name('pos.sales.cancel');
    Route::post('checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::get('products/search', [PosController::class, 'searchProducts'])->name('pos.products.search');
    Route::get('customers/search', [PosController::class, 'searchCustomers'])->name('pos.customers.search');
    Route::post('customers', [PosController::class, 'storeCustomer'])->name('pos.customers.store');
    Route::post('customers/{customer}/settle-balance', [PosController::class, 'settleBalance'])->name('pos.customers.settle-balance');
    Route::get('pos-settings', [PosSettingController::class, 'index'])->name('pos.settings');
    Route::post('pos-settings', [PosSettingController::class, 'update'])->name('pos.settings.update');
});

/*
|--------------------------------------------------------------------------
| Subdomain & Port URL Interceptors
|--------------------------------------------------------------------------
| If someone visits legacy admin routes on the POS host or port 8001,
| smoothly redirect them to the clean POS URLs without /admin/.
*/
Route::prefix('admin')->group(function () use ($isPosRequest) {
    Route::any('pos/sales', function (Request $request) use ($isPosRequest) {
        if ($isPosRequest($request)) {
            return redirect()->route('pos.sales');
        }
        return app(PosController::class)->sales($request);
    });

    Route::any('pos', function (Request $request) use ($isPosRequest) {
        if ($isPosRequest($request)) {
            return redirect()->route('pos.terminal');
        }
        return app(PosController::class)->terminal();
    });

    Route::any('login', function (Request $request) use ($isPosRequest) {
        if ($isPosRequest($request)) {
            return redirect()->route('pos.login');
        }
        $controller = app(LoginController::class);
        return $request->isMethod('POST') ? $controller->login($request) : $controller->showLoginForm();
    });

    Route::any('dashboard', function (Request $request) use ($isPosRequest) {
        if ($isPosRequest($request)) {
            return redirect()->route('pos.terminal');
        }
        return app(DashboardController::class)->index($request);
    });

    Route::any('/', function (Request $request) use ($isPosRequest) {
        if ($isPosRequest($request)) {
            return redirect()->route('pos.terminal');
        }
        return redirect()->route('admin.dashboard');
    });

    require __DIR__.'/admin.php';
});

/*
|--------------------------------------------------------------------------
| Public Site Routes
|--------------------------------------------------------------------------
*/
Route::get('projects/{project:slug}', [ProjectController::class, 'show'])->name('project.show');
Route::get('blog/{post:slug}', [PostController::class, 'show'])->name('post.show');
Route::get('varieties', [VarietyController::class, 'index'])->name('varieties.index');
Route::post('leads', [LeadController::class, 'store'])
    ->middleware('throttle:leads')
    ->name('leads.store');
