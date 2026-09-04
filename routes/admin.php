<?php

use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FrontendController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\LeadFormFieldController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ServiceItemController;
use App\Http\Controllers\Admin\ServiceStageController;
use App\Http\Controllers\Admin\ServiceStageProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StockMovementController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\WorkOrderController;
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

    // Inventory
    Route::resource('products', ProductController::class)->except('show')->names('admin.products');
    Route::post('products/{product}/notify-supplier', [ProductController::class, 'notifySupplier'])->name('admin.products.notify-supplier');
    Route::resource('suppliers', SupplierController::class)->except('show')->names('admin.suppliers');
    Route::get('stock-movements', [StockMovementController::class, 'index'])->name('admin.stock-movements.index');
    Route::get('stock-movements/create', [StockMovementController::class, 'create'])->name('admin.stock-movements.create');
    Route::post('stock-movements', [StockMovementController::class, 'store'])->name('admin.stock-movements.store');

    // Work orders
    Route::get('work-orders', [WorkOrderController::class, 'index'])->name('admin.work-orders.index');
    Route::get('work-orders/create', [WorkOrderController::class, 'create'])->name('admin.work-orders.create');
    Route::post('work-orders', [WorkOrderController::class, 'store'])->name('admin.work-orders.store');
    Route::get('work-orders/{workOrder}', [WorkOrderController::class, 'show'])->name('admin.work-orders.show');
    Route::patch('work-orders/{workOrder}/assign', [WorkOrderController::class, 'assign'])->name('admin.work-orders.assign');
    Route::patch('work-orders/{workOrder}/cancel', [WorkOrderController::class, 'cancel'])->name('admin.work-orders.cancel');
    Route::patch('work-orders/{workOrder}/stages/{stage}/complete', [WorkOrderController::class, 'completeStage'])->name('admin.work-orders.stages.complete');
    Route::patch('work-orders/{workOrder}/stages/{stage}/skip', [WorkOrderController::class, 'skipStage'])->name('admin.work-orders.stages.skip');
    Route::post('work-orders/{workOrder}/stages/{stage}/products', [WorkOrderController::class, 'addStageProduct'])->name('admin.work-orders.stages.products.store');
    Route::patch('work-orders/{workOrder}/stages/{stage}/products/{stageProduct}', [WorkOrderController::class, 'updateStageProduct'])->name('admin.work-orders.stages.products.update');
    Route::delete('work-orders/{workOrder}/stages/{stage}/products/{stageProduct}', [WorkOrderController::class, 'destroyStageProduct'])->name('admin.work-orders.stages.products.destroy');
    Route::post('work-orders/{workOrder}/invoice', [WorkOrderController::class, 'generateInvoice'])->name('admin.work-orders.invoice');

    // Invoices
    Route::get('invoices', [InvoiceController::class, 'index'])->name('admin.invoices.index');
    Route::get('invoices/create', [InvoiceController::class, 'create'])->name('admin.invoices.create');
    Route::post('invoices', [InvoiceController::class, 'store'])->name('admin.invoices.store');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('admin.invoices.show');
    Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('admin.invoices.print');
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('admin.invoices.pdf');
    Route::post('invoices/{invoice}/payments', [InvoiceController::class, 'addPayment'])->name('admin.invoices.payments.store');
    Route::patch('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('admin.invoices.cancel');

    // Service stage material templates
    Route::get('stages/{stage}/products', [ServiceStageProductController::class, 'index'])->name('admin.stage-products.index');
    Route::post('stages/{stage}/products', [ServiceStageProductController::class, 'store'])->name('admin.stage-products.store');
    Route::put('stages/{stage}/products/{stageProduct}', [ServiceStageProductController::class, 'update'])->name('admin.stage-products.update');
    Route::delete('stages/{stage}/products/{stageProduct}', [ServiceStageProductController::class, 'destroy'])->name('admin.stage-products.destroy');

    // Staff
    Route::get('staff', [StaffController::class, 'index'])->name('admin.staff.index');
    Route::get('staff/create', [StaffController::class, 'create'])->name('admin.staff.create');
    Route::post('staff', [StaffController::class, 'store'])->name('admin.staff.store');
    Route::get('staff/{admin}/edit', [StaffController::class, 'edit'])->name('admin.staff.edit');
    Route::put('staff/{admin}', [StaffController::class, 'update'])->name('admin.staff.update');
    Route::delete('staff/{admin}', [StaffController::class, 'destroy'])->name('admin.staff.destroy');

    // Notifications
    Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('admin.notifications.read-all');
});
