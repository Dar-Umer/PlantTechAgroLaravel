<?php

use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FrontendController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\LeadFormFieldController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ServiceItemController;
use App\Http\Controllers\Admin\ServiceStageController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TestimonialController;
use Illuminate\Support\Facades\Route;

// Public admin auth routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('login', [LoginController::class, 'login'])->name('admin.login.submit');
});

Route::post('logout', [LoginController::class, 'logout'])->name('admin.logout');

// Authenticated admin routes
Route::middleware('admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('settings', [SettingController::class, 'index'])->name('admin.settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('admin.settings.update');

    // Content management
    Route::resource('posts', PostController::class)->except('show')->names('admin.posts');
    Route::resource('services', ServiceController::class)->except('show')->names('admin.services');
    Route::resource('services.items', ServiceItemController::class)->shallow()->except('show')->names('admin.services.items');
    Route::resource('services.stages', ServiceStageController::class)->shallow()->except('show')->names('admin.services.stages');
    Route::resource('projects', ProjectController::class)->except('show')->names('admin.projects');
    Route::resource('testimonials', TestimonialController::class)->except('show')->names('admin.testimonials');
    Route::resource('faqs', FaqController::class)->except('show')->names('admin.faqs');
    Route::resource('gallery', GalleryController::class)->except(['create', 'edit', 'update', 'show'])->names('admin.gallery');

    // Website / Frontend management
    Route::get('frontend', [FrontendController::class, 'index'])->name('admin.frontend.index');
    Route::put('frontend/lead-form', [FrontendController::class, 'updateLeadForm'])->name('admin.frontend.lead-form.update');
    Route::put('frontend/home-sections', [FrontendController::class, 'updateHomeSections'])->name('admin.frontend.home-sections.update');
    Route::post('frontend/lead-form/fields', [LeadFormFieldController::class, 'store'])->name('admin.lead-form-fields.store');
    Route::put('frontend/lead-form/fields/{field}', [LeadFormFieldController::class, 'update'])->name('admin.lead-form-fields.update');
    Route::delete('frontend/lead-form/fields/{field}', [LeadFormFieldController::class, 'destroy'])->name('admin.lead-form-fields.destroy');
    Route::post('frontend/lead-form/fields/reorder', [LeadFormFieldController::class, 'reorder'])->name('admin.lead-form-fields.reorder');

    // Leads
    Route::get('leads', [LeadController::class, 'index'])->name('admin.leads.index');
    Route::get('leads/{lead}', [LeadController::class, 'show'])->name('admin.leads.show');
    Route::get('leads/{lead}/edit', [LeadController::class, 'edit'])->name('admin.leads.edit');
    Route::put('leads/{lead}', [LeadController::class, 'update'])->name('admin.leads.update');
    Route::patch('leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('admin.leads.status');
    Route::get('leads/{lead}/convert', [LeadController::class, 'showConvert'])->name('admin.leads.convert');
    Route::post('leads/{lead}/convert', [LeadController::class, 'convert'])->name('admin.leads.convert.store');
    Route::delete('leads/{lead}', [LeadController::class, 'destroy'])->name('admin.leads.destroy');

    // Customers
    Route::get('customers', [CustomerController::class, 'index'])->name('admin.customers.index');
    Route::get('customers/create', [CustomerController::class, 'create'])->name('admin.customers.create');
    Route::post('customers', [CustomerController::class, 'store'])->name('admin.customers.store');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('admin.customers.show');
    Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('admin.customers.edit');
    Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('admin.customers.update');
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('admin.customers.destroy');
});
