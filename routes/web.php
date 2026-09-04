<?php

use App\Http\Controllers\Site\LandingController;
use App\Http\Controllers\Site\LeadController;
use App\Http\Controllers\Site\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::get('projects/{project:slug}', [ProjectController::class, 'show'])->name('project.show');

Route::post('leads', [LeadController::class, 'store'])
    ->middleware('throttle:leads')
    ->name('leads.store');

Route::prefix('admin')->group(function () {
    require __DIR__.'/admin.php';
});
