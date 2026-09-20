<?php

use App\Http\Controllers\Api\Customer\AppConfigController;
use App\Http\Controllers\Api\Customer\AuthController;
use App\Http\Controllers\Api\Customer\DashboardController;
use App\Http\Controllers\Api\Customer\ForgotPasswordController;
use App\Http\Controllers\Api\Customer\InvoiceController;
use App\Http\Controllers\Api\Customer\NotificationController;
use App\Http\Controllers\Api\Customer\ProfileController;
use App\Http\Controllers\Api\Customer\ServiceController;
use App\Http\Controllers\Api\Customer\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::get('app-config', [AppConfigController::class, 'show']);

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:customer-login');
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    Route::post('forgot', [ForgotPasswordController::class, 'requestOtp'])->middleware('throttle:customer-otp');
    Route::post('verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->middleware('throttle:customer-otp');
    Route::post('reset-password', [ForgotPasswordController::class, 'resetPassword'])->middleware('throttle:customer-otp');
});

Route::middleware(['auth:sanctum', \App\Http\Middleware\EnsureCustomerIsActive::class])->group(function () {
    Route::get('me', [ProfileController::class, 'show']);
    Route::put('me', [ProfileController::class, 'update']);
    Route::put('me/password', [ProfileController::class, 'changePassword']);
    Route::get('me/ledger', [ProfileController::class, 'ledger']);

    Route::get('dashboard', [DashboardController::class, 'index']);

    Route::get('weather', [\App\Http\Controllers\Api\Customer\WeatherController::class, 'show']);

    Route::get('services', [ServiceController::class, 'index']);

    Route::get('products', [\App\Http\Controllers\Api\Customer\ProductController::class, 'index']);

    Route::get('work-orders', [WorkOrderController::class, 'index']);
    Route::post('work-orders', [WorkOrderController::class, 'store']);
    Route::get('work-orders/{id}', [WorkOrderController::class, 'show']);

    Route::get('invoices', [InvoiceController::class, 'index']);
    Route::get('invoices/{id}', [InvoiceController::class, 'show']);

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/read-all', [NotificationController::class, 'readAll']);
    Route::post('notifications/{notification}/read', [NotificationController::class, 'read']);
});