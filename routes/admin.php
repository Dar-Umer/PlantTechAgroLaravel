<?php

use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\AutomationController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentDesignController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\ForgotPasswordController;
use App\Http\Controllers\Admin\FrontendController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\LeadFormFieldController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\MobileAppController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\NotificationTemplateController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\PurchaseBillController;
use App\Http\Controllers\Admin\QuotationController;
use App\Http\Controllers\Admin\ResetPasswordController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ServiceItemController;
use App\Http\Controllers\Admin\ServiceStageController;
use App\Http\Controllers\Admin\ServiceStageProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\ProductBatchController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StockMovementController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\Admin\SystemHealthController;
use App\Http\Controllers\Admin\WorkOrderController;
use Illuminate\Support\Facades\Route;

// Public admin auth routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('login', [LoginController::class, 'login'])
        ->middleware('throttle:admin-login')
        ->name('admin.login.submit');

    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('admin.password.request');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('throttle:3,1')->name('admin.password.email');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('admin.password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->middleware('throttle:5,1')->name('admin.password.update');
});

Route::post('logout', [LoginController::class, 'logout'])->name('admin.logout');

// Authenticated admin routes
Route::middleware('admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Sensitive system administration — Super Admin only.
    Route::middleware('role:Super Admin')->group(function () {
        Route::get('settings', [SettingController::class, 'index'])->name('admin.settings.index');

        Route::put('settings', [SettingController::class, 'update'])->name('admin.settings.update');
        Route::post('settings/maintenance/toggle', [SettingController::class, 'toggleMaintenance'])->name('admin.settings.maintenance.toggle');

        // Invoices & Quotations Designer
        Route::get('document-settings', [DocumentDesignController::class, 'index'])->name('admin.document-settings.index');
        Route::put('document-settings/prefixes', [DocumentDesignController::class, 'updatePrefixes'])->name('admin.document-settings.prefixes.update');
        Route::put('document-settings/quotations', [DocumentDesignController::class, 'updateQuotations'])->name('admin.document-settings.quotations.update');
        Route::put('document-settings/invoices', [DocumentDesignController::class, 'updateInvoices'])->name('admin.document-settings.invoices.update');
        Route::put('document-settings/services/{service}', [DocumentDesignController::class, 'updateService'])->name('admin.document-settings.service.update');
        Route::post('document-settings/services/{service}/reset', [DocumentDesignController::class, 'resetService'])->name('admin.document-settings.service.reset');

        Route::get('mobile-apps', [MobileAppController::class, 'index'])->name('admin.mobile-apps.index');
        Route::put('mobile-apps', [MobileAppController::class, 'update'])->name('admin.mobile-apps.update');
        Route::post('mobile-apps/firebase/test', [MobileAppController::class, 'testPush'])->name('admin.mobile-apps.firebase.test');
        Route::post('mobile-apps/firebase/broadcast', [MobileAppController::class, 'broadcastPush'])->name('admin.mobile-apps.firebase.broadcast');

        Route::post('settings/mail', [SettingController::class, 'smtpUpdate'])->name('admin.settings.mail.update');

        Route::post('settings/mail/test', [SettingController::class, 'smtpTest'])->middleware('throttle:6,1')->name('admin.settings.mail.test');

        Route::post('settings/media/test', [SettingController::class, 'testMediaCompression'])->name('admin.settings.media.test');

        Route::get('automation', [AutomationController::class, 'index'])->name('admin.automation.index');

        Route::put('automation', [AutomationController::class, 'update'])->name('admin.automation.update');

        // Notification Templates
        Route::get('notification-templates', [NotificationTemplateController::class, 'index'])->name('admin.notification-templates.index');
        Route::put('notification-templates/system', [NotificationTemplateController::class, 'updateSystem'])->name('admin.notification-templates.system.update');
        Route::put('notification-templates/stage/{stage}', [NotificationTemplateController::class, 'updateStage'])->name('admin.notification-templates.stage.update');

        // Staff
        Route::get('staff', [StaffController::class, 'index'])->name('admin.staff.index');
        Route::get('staff/create', [StaffController::class, 'create'])->name('admin.staff.create');
        Route::post('staff', [StaffController::class, 'store'])->name('admin.staff.store');
        Route::get('staff/{admin}/edit', [StaffController::class, 'edit'])->name('admin.staff.edit');
        Route::put('staff/{admin}', [StaffController::class, 'update'])->name('admin.staff.update');
        Route::delete('staff/{admin}', [StaffController::class, 'destroy'])->name('admin.staff.destroy');

        // Roles & Permissions
        Route::resource('roles', RoleController::class)->except('show')->names('admin.roles');
    });

    // ==========================================
    // CMS & Website Content
    // ==========================================
    Route::middleware('permission:content.manage,admin')->group(function () {
        Route::patch('posts/{post}/toggle-publish', [PostController::class, 'togglePublish'])->name('admin.posts.toggle-publish');
        Route::post('post-categories', [PostController::class, 'storeCategory'])->name('admin.post-categories.store');
        Route::resource('posts', PostController::class)->except('show')->names('admin.posts');
        Route::resource('projects', ProjectController::class)->except('show')->names('admin.projects');
        Route::resource('testimonials', TestimonialController::class)->except('show')->names('admin.testimonials');
        Route::resource('faqs', FaqController::class)->except('show')->names('admin.faqs');
        Route::resource('gallery', GalleryController::class)->except(['create', 'edit', 'update', 'show'])->names('admin.gallery');
        Route::patch('varieties/{variety}/toggle-active', [\App\Http\Controllers\Admin\VarietyController::class, 'toggleActive'])->name('admin.varieties.toggle-active');
        Route::resource('varieties', \App\Http\Controllers\Admin\VarietyController::class)->except('show')->names('admin.varieties');
        Route::patch('partners/{partner}/toggle-active', [\App\Http\Controllers\Admin\PartnerController::class, 'toggleActive'])->name('admin.partners.toggle-active');
        Route::post('partners/speed', [\App\Http\Controllers\Admin\PartnerController::class, 'updateSpeed'])->name('admin.partners.speed');
        Route::resource('partners', \App\Http\Controllers\Admin\PartnerController::class)->except('show')->names('admin.partners');
    });

    // Website / Frontend theme editor
    Route::middleware('permission:frontend.editor,admin')->group(function () {
        Route::get('frontend', [FrontendController::class, 'index'])->name('admin.frontend.index');
        Route::put('frontend/lead-form', [FrontendController::class, 'updateLeadForm'])->name('admin.frontend.lead-form.update');
        Route::put('frontend/notice', [FrontendController::class, 'updateNotice'])->name('admin.frontend.notice.update');
        Route::put('frontend/home-sections', [FrontendController::class, 'updateHomeSections'])->name('admin.frontend.home-sections.update');
        Route::put('frontend/footer', [FrontendController::class, 'updateFooter'])->name('admin.frontend.footer.update');
        Route::post('frontend/lead-form/fields', [LeadFormFieldController::class, 'store'])->name('admin.lead-form-fields.store');
        Route::put('frontend/lead-form/fields/{field}', [LeadFormFieldController::class, 'update'])->name('admin.lead-form-fields.update');
        Route::delete('frontend/lead-form/fields/{field}', [LeadFormFieldController::class, 'destroy'])->name('admin.lead-form-fields.destroy');
        Route::post('frontend/lead-form/fields/reorder', [LeadFormFieldController::class, 'reorder'])->name('admin.lead-form-fields.reorder');
    });

    // ==========================================
    // CRM: Leads & Enquiries
    // ==========================================
    Route::middleware('permission:leads.view,admin')->group(function () {
        Route::get('leads', [LeadController::class, 'index'])->name('admin.leads.index');
        Route::get('leads/{lead}', [LeadController::class, 'show'])->name('admin.leads.show');
    });
    Route::middleware('permission:leads.manage,admin')->group(function () {
        Route::get('leads/{lead}/edit', [LeadController::class, 'edit'])->name('admin.leads.edit');
        Route::put('leads/{lead}', [LeadController::class, 'update'])->name('admin.leads.update');
        Route::patch('leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('admin.leads.status');
        Route::get('leads/{lead}/convert', [LeadController::class, 'showConvert'])->name('admin.leads.convert');
        Route::post('leads/{lead}/convert', [LeadController::class, 'convert'])->name('admin.leads.convert.store');
        Route::post('leads/{lead}/work-order', [LeadController::class, 'createWorkOrder'])->name('admin.leads.work-order');
        Route::delete('leads/{lead}', [LeadController::class, 'destroy'])->name('admin.leads.destroy');
    });

    // ==========================================
    // CRM: Quotations / Estimates
    // ==========================================
    Route::middleware('permission:quotations.manage,admin')->group(function () {
        Route::get('quotations/create', [QuotationController::class, 'create'])->name('admin.quotations.create');
        Route::post('quotations', [QuotationController::class, 'store'])->name('admin.quotations.store');
        Route::get('quotations/{quotation}/edit', [QuotationController::class, 'edit'])->name('admin.quotations.edit');
        Route::put('quotations/{quotation}', [QuotationController::class, 'update'])->name('admin.quotations.update');
        Route::delete('quotations/{quotation}', [QuotationController::class, 'destroy'])->name('admin.quotations.destroy');
        Route::post('quotations/{quotation}/approve', [QuotationController::class, 'approveAndStartWork'])->name('admin.quotations.approve');
    });
    Route::middleware('permission:quotations.view,admin')->group(function () {
        Route::get('quotations', [QuotationController::class, 'index'])->name('admin.quotations.index');
        Route::get('quotations/{quotation}', [QuotationController::class, 'show'])->name('admin.quotations.show');
        Route::get('quotations/{quotation}/print', [QuotationController::class, 'print'])->name('admin.quotations.print');
        Route::get('quotations/{quotation}/pdf', [QuotationController::class, 'pdf'])->name('admin.quotations.pdf');
    });

    // ==========================================
    // CRM: Customers Directory & Ledgers
    // ==========================================
    Route::middleware('permission:customers.manage,admin')->group(function () {
        Route::get('customers/create', [CustomerController::class, 'create'])->name('admin.customers.create');
        Route::post('customers', [CustomerController::class, 'store'])->name('admin.customers.store');
        Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('admin.customers.edit');
        Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('admin.customers.update');
        Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('admin.customers.destroy');
    });
    Route::middleware('permission:customers.view,admin')->group(function () {
        Route::get('customers', [CustomerController::class, 'index'])->name('admin.customers.index');
        Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('admin.customers.show');
    });
    Route::middleware('permission:customers.ledger,admin')->group(function () {
        Route::get('customers/{customer}/ledger', [CustomerController::class, 'ledger'])->name('admin.customers.ledger');
        Route::get('customers/{customer}/ledger/pdf', [CustomerController::class, 'ledgerPdf'])->name('admin.customers.ledger.pdf');
        Route::get('customers/{customer}/ledger/csv', [CustomerController::class, 'ledgerCsv'])->name('admin.customers.ledger.csv');
    });

    // ==========================================
    // CRM: Farmer Orchards
    // ==========================================
    Route::middleware('permission:orchards.manage,admin')->group(function () {
        Route::get('orchards/create', [\App\Http\Controllers\Admin\OrchardController::class, 'create'])->name('admin.orchards.create');
        Route::post('orchards', [\App\Http\Controllers\Admin\OrchardController::class, 'store'])->name('admin.orchards.store');
        Route::get('orchards/{orchard}/edit', [\App\Http\Controllers\Admin\OrchardController::class, 'edit'])->name('admin.orchards.edit');
        Route::put('orchards/{orchard}', [\App\Http\Controllers\Admin\OrchardController::class, 'update'])->name('admin.orchards.update');
        Route::delete('orchards/{orchard}', [\App\Http\Controllers\Admin\OrchardController::class, 'destroy'])->name('admin.orchards.destroy');
    });
    Route::middleware('permission:orchards.view,admin')->group(function () {
        Route::get('orchards', [\App\Http\Controllers\Admin\OrchardController::class, 'index'])->name('admin.orchards.index');
        Route::get('orchards/{orchard}', [\App\Http\Controllers\Admin\OrchardController::class, 'show'])->name('admin.orchards.show');
    });

    // ==========================================
    // Customer Support: Support Tickets
    // ==========================================
    Route::middleware('permission:tickets.manage,admin')->group(function () {
        Route::get('tickets/create', [TicketController::class, 'create'])->name('admin.tickets.create');
        Route::post('tickets', [TicketController::class, 'store'])->name('admin.tickets.store');
        Route::patch('tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('admin.tickets.status');
        Route::patch('tickets/{ticket}/priority', [TicketController::class, 'updatePriority'])->name('admin.tickets.priority');
        Route::post('tickets/{ticket}/messages', [TicketController::class, 'addMessage'])->name('admin.tickets.messages.store');
        Route::post('tickets/{ticket}/notes', [TicketController::class, 'addInternalNote'])->name('admin.tickets.notes.store');
        Route::delete('tickets/{ticket}', [TicketController::class, 'destroy'])->name('admin.tickets.destroy');
    });
    Route::middleware('permission:tickets.view,admin')->group(function () {
        Route::get('tickets', [TicketController::class, 'index'])->name('admin.tickets.index');
        Route::get('tickets/{ticket}', [TicketController::class, 'show'])->name('admin.tickets.show');
    });
    Route::middleware('permission:tickets.assign,admin')->group(function () {
        Route::patch('tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('admin.tickets.assign');
    });

    // ==========================================
    // Field Operations: Work Orders (Jobs)
    // ==========================================
    Route::middleware('permission:work-orders.manage,admin')->group(function () {
        Route::get('work-orders/create', [WorkOrderController::class, 'create'])->name('admin.work-orders.create');
        Route::post('work-orders', [WorkOrderController::class, 'store'])->name('admin.work-orders.store');
        Route::patch('work-orders/{workOrder}/assign', [WorkOrderController::class, 'assign'])->name('admin.work-orders.assign');
        Route::patch('work-orders/{workOrder}/cancel', [WorkOrderController::class, 'cancel'])->name('admin.work-orders.cancel');
        Route::patch('work-orders/{workOrder}/stages/{stage}/complete', [WorkOrderController::class, 'completeStage'])->name('admin.work-orders.stages.complete');
        Route::patch('work-orders/{workOrder}/stages/{stage}/skip', [WorkOrderController::class, 'skipStage'])->name('admin.work-orders.stages.skip');
        Route::post('work-orders/{workOrder}/stages/{stage}/products', [WorkOrderController::class, 'addStageProduct'])->name('admin.work-orders.stages.products.store');
        Route::patch('work-orders/{workOrder}/stages/{stage}/products/{stageProduct}', [WorkOrderController::class, 'updateStageProduct'])->name('admin.work-orders.stages.products.update');
        Route::delete('work-orders/{workOrder}/stages/{stage}/products/{stageProduct}', [WorkOrderController::class, 'destroyStageProduct'])->name('admin.work-orders.stages.products.destroy');
        Route::delete('work-orders/{workOrder}/stages/{stage}/attachments/{attachment}', [WorkOrderController::class, 'destroyAttachment'])->name('admin.work-orders.stages.attachments.destroy');
        Route::post('work-orders/{workOrder}/invoice', [WorkOrderController::class, 'generateInvoice'])->name('admin.work-orders.invoice');
    });
    Route::middleware('permission:work-orders.view,admin')->group(function () {
        Route::get('work-orders', [WorkOrderController::class, 'index'])->name('admin.work-orders.index');
        Route::get('work-orders/{workOrder}', [WorkOrderController::class, 'show'])->name('admin.work-orders.show');
    });

    // ==========================================
    // Field Operations: Invoices & Billing
    // ==========================================
    Route::middleware('permission:invoices.manage,admin')->group(function () {
        Route::get('invoices/create', [InvoiceController::class, 'create'])->name('admin.invoices.create');
        Route::post('invoices', [InvoiceController::class, 'store'])->name('admin.invoices.store');
        Route::post('invoices/{invoice}/payments', [InvoiceController::class, 'addPayment'])->name('admin.invoices.payments.store');
        Route::patch('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('admin.invoices.cancel');
    });
    Route::middleware('permission:invoices.view,admin')->group(function () {
        Route::get('invoices', [InvoiceController::class, 'index'])->name('admin.invoices.index');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('admin.invoices.show');
        Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('admin.invoices.print');
        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('admin.invoices.pdf');
    });

    // ==========================================
    // Services & Stages Workflow
    // ==========================================
    Route::middleware('permission:services.view,admin')->group(function () {
        Route::get('services', [ServiceController::class, 'index'])->name('admin.services.index');
    });
    Route::middleware('permission:services.manage,admin')->group(function () {
        Route::get('services/create', [ServiceController::class, 'create'])->name('admin.services.create');
        Route::post('services', [ServiceController::class, 'store'])->name('admin.services.store');
        Route::get('services/{service}/edit', [ServiceController::class, 'edit'])->name('admin.services.edit');
        Route::put('services/{service}', [ServiceController::class, 'update'])->name('admin.services.update');
        Route::delete('services/{service}', [ServiceController::class, 'destroy'])->name('admin.services.destroy');
        Route::resource('services.items', ServiceItemController::class)->shallow()->except('show')->names('admin.services.items');
    });
    Route::middleware('permission:services.stages.manage,admin')->group(function () {
        Route::resource('services.stages', ServiceStageController::class)->shallow()->except('show')->names('admin.services.stages');
        Route::post('services/{service}/stages/reorder', [ServiceStageController::class, 'reorder'])->name('admin.services.stages.reorder');
        Route::get('stages/{stage}/products', [ServiceStageProductController::class, 'index'])->name('admin.stage-products.index');
        Route::post('stages/{stage}/products', [ServiceStageProductController::class, 'store'])->name('admin.stage-products.store');
        Route::put('stages/{stage}/products/{stageProduct}', [ServiceStageProductController::class, 'update'])->name('admin.stage-products.update');
        Route::delete('stages/{stage}/products/{stageProduct}', [ServiceStageProductController::class, 'destroy'])->name('admin.stage-products.destroy');
    });

    // ==========================================
    // Inventory & Products Catalogue
    // ==========================================
    Route::middleware('permission:inventory.stock-in|inventory.stock-out|inventory.manage,admin')->group(function () {
        Route::get('stock-movements/create', [StockMovementController::class, 'create'])->name('admin.stock-movements.create');
        Route::post('stock-movements', [StockMovementController::class, 'store'])->name('admin.stock-movements.store');
        Route::delete('stock-movements/{movement}', [StockMovementController::class, 'destroy'])->name('admin.stock-movements.destroy')->whereNumber('movement');
    });
    Route::middleware('permission:inventory.view,admin')->group(function () {
        Route::get('products', [ProductController::class, 'index'])->name('admin.products.index');
        Route::get('stock-movements', [StockMovementController::class, 'index'])->name('admin.stock-movements.index');
        Route::get('stock-movements/export', [StockMovementController::class, 'export'])->name('admin.stock-movements.export');
        Route::get('stock-movements/{movement}', [StockMovementController::class, 'show'])->name('admin.stock-movements.show')->whereNumber('movement');
    });
    Route::middleware('permission:inventory.manage|inventory.stock-in,admin')->group(function () {
        Route::get('products/create', [ProductController::class, 'create'])->name('admin.products.create');
        Route::post('products', [ProductController::class, 'store'])->name('admin.products.store');
    });
    Route::middleware('permission:inventory.manage,admin')->group(function () {
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('admin.products.update');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
        Route::post('products/{product}/notify-supplier', [ProductController::class, 'notifySupplier'])->name('admin.products.notify-supplier');
    });
    Route::middleware('permission:inventory.batches,admin')->group(function () {
        Route::get('product-batches', [ProductBatchController::class, 'index'])->name('admin.product-batches.index');
        Route::get('product-batches/export', [ProductBatchController::class, 'export'])->name('admin.product-batches.export');
    });

    // ==========================================
    // Suppliers & Procurement
    // ==========================================
    Route::middleware('permission:suppliers.manage,admin')->group(function () {
        Route::resource('suppliers', SupplierController::class)->names('admin.suppliers');
        Route::post('suppliers/{supplier}/payments', [SupplierController::class, 'recordPayment'])->name('admin.suppliers.payments.store');
        Route::delete('suppliers/{supplier}/payments/{payment}', [SupplierController::class, 'destroyPayment'])->name('admin.suppliers.payments.destroy');
    });

    // ==========================================
    // Purchase Bills (Inward Stock & Billing)
    // ==========================================
    Route::middleware('permission:purchase-bills.manage,admin')->group(function () {
        Route::resource('purchase-bills', PurchaseBillController::class)->parameters(['purchase-bills' => 'purchaseBill'])->except(['edit', 'update'])->names('admin.purchase-bills');
        Route::get('purchase-bills/{purchaseBill}/print', [PurchaseBillController::class, 'print'])->name('admin.purchase-bills.print');
        Route::post('purchase-bills/{purchaseBill}/payments', [PurchaseBillController::class, 'recordPayment'])->name('admin.purchase-bills.payments.store');
        Route::delete('purchase-bills/{purchaseBill}/payments/{payment}', [PurchaseBillController::class, 'destroyPayment'])->name('admin.purchase-bills.payments.destroy');
    });

    // ==========================================
    // Finance & Reports: GST
    // ==========================================
    Route::middleware('permission:reports.gst,admin')->group(function () {
        Route::get('reports/gst', [ReportController::class, 'gstReport'])->name('admin.reports.gst');
        Route::get('reports/gst/export', [ReportController::class, 'exportGst'])->name('admin.reports.gst.export');
    });

    // ==========================================
    // Point of Sale (POS)
    // ==========================================
    Route::middleware('permission:pos.terminal,admin')->group(function () {
        Route::get('pos', [PosController::class, 'terminal'])->name('admin.pos.terminal');
        Route::get('pos/products/search', [PosController::class, 'searchProducts'])->name('admin.pos.products.search');
        Route::get('pos/customers/search', [PosController::class, 'searchCustomers'])->name('admin.pos.customers.search');
        Route::post('pos/checkout', [PosController::class, 'checkout'])->name('admin.pos.checkout');
    });
    Route::middleware('permission:pos.sales.view,admin')->group(function () {
        Route::get('pos/sales', [PosController::class, 'sales'])->name('admin.pos.sales');
        Route::get('pos/sales/{sale}', [PosController::class, 'show'])->name('admin.pos.sales.show');
        Route::get('pos/sales/{sale}/receipt', [PosController::class, 'receipt'])->name('admin.pos.receipt');
        Route::get('pos/sales/{sale}/invoice', [PosController::class, 'invoice'])->name('admin.pos.invoice');
    });
    Route::middleware('permission:pos.sales.cancel,admin')->group(function () {
        Route::post('pos/sales/{sale}/cancel', [PosController::class, 'cancel'])->name('admin.pos.sales.cancel');
    });
    Route::middleware('permission:pos.customers.manage,admin')->group(function () {
        Route::post('pos/customers', [PosController::class, 'storeCustomer'])->name('admin.pos.customers.store');
        Route::post('pos/customers/{customer}/settle-balance', [PosController::class, 'settleBalance'])->name('admin.pos.customers.settle-balance');
    });
    Route::middleware('permission:settings.manage,admin')->group(function () {
        Route::get('pos/settings', [\App\Http\Controllers\Admin\PosSettingController::class, 'index'])->name('admin.settings.pos');
        Route::post('pos/settings', [\App\Http\Controllers\Admin\PosSettingController::class, 'update'])->name('admin.settings.pos.update');
        Route::get('settings/pos', [\App\Http\Controllers\Admin\PosSettingController::class, 'index'])->name('admin.pos.settings');
    });

    // ==========================================
    // Notifications (Self / Authenticated Staff)
    // ==========================================
    Route::get('notifications/latest', [NotificationController::class, 'latest'])->name('admin.notifications.latest');
    Route::get('notifications/{notification}/go', [NotificationController::class, 'go'])->name('admin.notifications.go');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'read'])->name('admin.notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('admin.notifications.read-all');

    // ==========================================
    // System Performance & Diagnostics
    // ==========================================
    Route::middleware('permission:settings.manage,admin')->group(function () {
        Route::get('system/diagnostics', [SystemHealthController::class, 'index'])->name('admin.system.diagnostics');
        Route::get('system/health', [SystemHealthController::class, 'stats'])->name('admin.system.health');
        Route::post('system/clear-views', [SystemHealthController::class, 'clearViews'])->name('admin.system.clear-views');
        Route::post('system/clear-cache', [SystemHealthController::class, 'clearAllCache'])->name('admin.system.clear-cache');
    });
});
