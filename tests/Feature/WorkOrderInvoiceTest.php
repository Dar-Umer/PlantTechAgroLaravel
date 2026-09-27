<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceStage;
use App\Models\ServiceStageProduct;
use App\Models\WorkOrder;
use App\Models\WorkOrderStageProduct;
use App\Services\StockService;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderInvoiceTest extends TestCase
{
    use RefreshDatabase;

    private function actingAdmin(): Admin
    {
        $this->seed(AdminSeeder::class);

        return Admin::where('email', 'admin@pta.com')->first();
    }

    private function buildServiceWithStages(): Service
    {
        $service = Service::create(['name' => 'Full Orchard Establishment', 'slug' => 'full-orchard-establishment']);

        $stage1 = ServiceStage::create(['service_id' => $service->id, 'name' => 'Land Layout', 'sort_order' => 1]);
        $stage2 = ServiceStage::create(['service_id' => $service->id, 'name' => 'Install Drip', 'sort_order' => 2]);

        $plants = Product::create(['name' => 'Apple Plant M9', 'unit' => 'pcs', 'rate' => 350, 'gst_rate' => 12, 'stock_qty' => 0, 'is_active' => true]);
        $pipe = Product::create(['name' => 'Drip Pipe', 'unit' => 'mtr', 'rate' => 25, 'gst_rate' => 18, 'stock_qty' => 0, 'is_active' => true]);

        StockService::record($plants, 'in', 1000, 'OPENING');
        StockService::record($pipe, 'in', 5000, 'OPENING');

        ServiceStageProduct::create(['service_stage_id' => $stage1->id, 'product_id' => $plants->id, 'quantity' => 10]);
        ServiceStageProduct::create(['service_stage_id' => $stage2->id, 'product_id' => $pipe->id, 'quantity' => 40]);

        return $service;
    }

    public function test_work_order_creation_instantiates_stages_and_materials(): void
    {
        $admin = $this->actingAdmin();
        $service = $this->buildServiceWithStages();
        $customer = Customer::create(['name' => 'Farooq Ahmad', 'phone' => '9900000001', 'password' => 'secret123']);

        $this->actingAs($admin, 'admin')->post('/admin/work-orders', [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'notes' => 'Two kanal land',
        ])->assertRedirect();

        $workOrder = WorkOrder::first();
        $this->assertNotNull($workOrder);
        $this->assertEquals('WO-0001', $workOrder->number);
        $this->assertEquals('Farooq Ahmad', $workOrder->customer_name);
        $this->assertSame(2, $workOrder->stages()->count());
        $this->assertSame(2, WorkOrderStageProduct::count());
        $this->assertEquals('pending', $workOrder->status);
    }

    public function test_stage_completion_flow_and_status_transitions(): void
    {
        $admin = $this->actingAdmin();
        $service = $this->buildServiceWithStages();
        $customer = Customer::create(['name' => 'Farooq Ahmad', 'phone' => '9900000002', 'password' => 'secret123']);
        $agent = Admin::create(['name' => 'Field Agent', 'email' => 'agent@pta.com', 'password' => 'secret123', 'is_active' => true]);

        $this->actingAs($admin, 'admin')->post('/admin/work-orders', [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'assigned_agent_id' => $agent->id,
        ]);

        $workOrder = WorkOrder::first();
        $this->assertEquals('assigned', $workOrder->status);

        // Agent got a database notification
        $agent->refresh();
        $this->assertSame(1, $agent->unreadNotifications()->count());

        $stages = $workOrder->stages()->orderBy('sort_order')->get();

        $this->actingAs($admin, 'admin')
            ->patch("/admin/work-orders/{$workOrder->id}/stages/{$stages[0]->id}/complete", ['notes' => 'Layout done'])
            ->assertRedirect();

        $workOrder->refresh();
        $this->assertEquals('in_progress', $workOrder->status);
        $this->assertNotNull($workOrder->started_at);

        $this->actingAs($admin, 'admin')
            ->patch("/admin/work-orders/{$workOrder->id}/stages/{$stages[1]->id}/complete")
            ->assertRedirect();

        $workOrder->refresh();
        $this->assertEquals('completed', $workOrder->status);
        $this->assertNotNull($workOrder->completed_at);
    }

    public function test_material_usage_deducts_stock_and_invoice_generation(): void
    {
        $admin = $this->actingAdmin();
        $service = $this->buildServiceWithStages();
        $customer = Customer::create(['name' => 'Farooq Ahmad', 'phone' => '9900000003', 'password' => 'secret123']);

        $this->actingAs($admin, 'admin')->post('/admin/work-orders', [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
        ]);

        $workOrder = WorkOrder::first();
        $plants = Product::where('name', 'Apple Plant M9')->first();
        $pipe = Product::where('name', 'Drip Pipe')->first();

        // Stage 1 uses planned 10 plants â€” add 2 extra via update
        $stage1 = $workOrder->stages()->orderBy('sort_order')->first();
        $row1 = $stage1->products()->first();

        $this->actingAs($admin, 'admin')
            ->patch("/admin/work-orders/{$workOrder->id}/stages/{$stage1->id}/products/{$row1->id}", [
                'quantity' => 12, 'rate' => 350,
            ])->assertRedirect();

        $plants->refresh();
        $this->assertEquals('988.000', $plants->stock_qty); // 1000 - 12

        $stage2 = $workOrder->stages()->orderBy('sort_order')->skip(1)->first();
        $row2 = $stage2->products()->first();
        $this->assertEquals('4960.000', $pipe->refresh()->stock_qty); // 5000 - 40 (planned, on creation)

        // Generate invoice
        $this->actingAs($admin, 'admin')
            ->post("/admin/work-orders/{$workOrder->id}/invoice")
            ->assertRedirect();

        $invoice = Invoice::where('work_order_id', $workOrder->id)->first();
        $this->assertNotNull($invoice);
        $this->assertStringStartsWith('PTA/', $invoice->number);
        $this->assertSame(2, $invoice->items()->count());

        // 12 Ã— 350 = 4200 + GST 12% = 504 | 40 Ã— 25 = 1000 + GST 18% = 180
        $this->assertEquals(5200.0, (float) $invoice->subtotal);
        $this->assertEquals(684.0, (float) $invoice->gst_total);
        $this->assertEquals(5884.0, (float) $invoice->grand_total);

        // Cannot generate twice
        $this->actingAs($admin, 'admin')
            ->post("/admin/work-orders/{$workOrder->id}/invoice")
            ->assertRedirect(route('admin.invoices.show', $invoice));
    }

    public function test_manual_invoice_creation_deducts_stock_and_payment_sets_status(): void
    {
        $admin = $this->actingAdmin();
        $customer = Customer::create(['name' => 'Aisha Bano', 'phone' => '9900000004', 'password' => 'secret123']);
        $product = Product::create(['name' => 'Hail Net', 'unit' => 'mtr', 'rate' => 90, 'gst_rate' => 18, 'stock_qty' => 0, 'is_active' => true]);
        StockService::record($product, 'in', 200, 'OPENING');

        $this->actingAs($admin, 'admin')->post('/admin/invoices', [
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'name' => 'Hail Net', 'unit' => 'mtr', 'qty' => 50, 'rate' => 90, 'discount' => 100, 'gst_rate' => 18],
            ],
        ])->assertRedirect();

        $invoice = Invoice::first();
        $this->assertStringStartsWith('PTA/', $invoice->number);

        // 50×90 = 4500 − 100 = 4400 + 18% GST = 792 → 5192
        $this->assertEquals(5192.0, (float) $invoice->grand_total);
        $this->assertEquals('150.000', $product->refresh()->stock_qty); // 200 - 50

        $this->actingAs($admin, 'admin')
            ->post("/admin/invoices/{$invoice->id}/payments", [
                'amount' => 2000, 'method' => 'upi', 'paid_at' => now()->toDateString(),
            ])->assertRedirect();

        $invoice->refresh();
        $this->assertEquals('partial', $invoice->status);
        $this->assertEquals(2000.0, (float) $invoice->amount_paid);

        // Cancel reverses stock
        $this->actingAs($admin, 'admin')
            ->patch("/admin/invoices/{$invoice->id}/cancel")
            ->assertRedirect();

        $invoice->refresh();
        $this->assertEquals('cancelled', $invoice->status);
        $this->assertEquals('200.000', $product->refresh()->stock_qty); // 150 + 50 reversed
    }

    public function test_invoice_pdf_downloads(): void
    {
        $admin = $this->actingAdmin();
        $customer = Customer::create(['name' => 'PDF Customer', 'phone' => '9900000005', 'password' => 'secret123']);

        $this->actingAs($admin, 'admin')->post('/admin/invoices', [
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'items' => [
                ['product_id' => null, 'name' => 'Consultation Fee', 'qty' => 1, 'rate' => 1500, 'discount' => 0, 'gst_rate' => 0],
            ],
        ])->assertRedirect();

        $invoice = Invoice::first();
        $response = $this->actingAs($admin, 'admin')->get("/admin/invoices/{$invoice->id}/pdf");

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_invoice_creation_with_service_link_and_custom_prefix(): void
    {
        $admin = $this->actingAdmin();
        $customer = Customer::create(['name' => 'Ghulam Nabi', 'phone' => '9900000006', 'password' => 'secret123']);

        $service = Service::create([
            'name' => 'Book an Orchard',
            'slug' => 'book-an-orchard',
            'default_price' => 185000,
            'default_unit' => 'kanal',
            'package_variations' => [
                ['name' => '150 Plants / Kanal (Standard)', 'price' => 185000, 'unit' => 'kanal'],
                ['name' => '170 Plants / Kanal (High Density)', 'price' => 210000, 'unit' => 'kanal'],
            ],
            'invoice_settings' => [
                'invoice_prefix' => 'INV-ORC',
                'doc_title' => 'Orchard Tax Invoice',
                'doc_subtitle' => 'High Density Apple Project',
                'accent_color' => '#059669',
                'terms' => 'Standard payment terms for orchard installation.',
            ],
        ]);

        $createResponse = $this->actingAs($admin, 'admin')->get('/admin/invoices/create');
        $createResponse->assertOk();
        $createResponse->assertSee('Book an Orchard');
        $createResponse->assertSee('Package / Density Variations');

        $response = $this->actingAs($admin, 'admin')->post('/admin/invoices', [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'invoice_date' => now()->toDateString(),
            'notes' => 'Direct booking with 170 plants/kanal package',
            'items' => [
                [
                    'product_id' => null,
                    'name' => 'Book an Orchard - 170 Plants / Kanal (High Density)',
                    'unit' => 'kanal',
                    'qty' => 2,
                    'rate' => 210000,
                    'discount' => 10000,
                    'gst_rate' => 18,
                ],
            ],
        ]);

        $response->assertRedirect();
        $invoice = Invoice::where('service_id', $service->id)->firstOrFail();

        $this->assertEquals($service->id, $invoice->service_id);
        $this->assertStringStartsWith('INV-ORC/', $invoice->number);
        // Subtotal = 2 * 210000 = 420000, discount = 10000, taxable = 410000 + 18% GST (73800) = 483800
        $this->assertEquals(420000.0, (float) $invoice->subtotal);
        $this->assertEquals(10000.0, (float) $invoice->discount_total);
        $this->assertEquals(73800.0, (float) $invoice->gst_total);
        $this->assertEquals(483800.0, (float) $invoice->grand_total);

        // Show page renders service details
        $showResponse = $this->actingAs($admin, 'admin')->get(route('admin.invoices.show', $invoice));
        $showResponse->assertOk();
        $showResponse->assertSee('Book an Orchard');
        $showResponse->assertSee('Direct service-linked invoice');

        // Print page renders custom doc title and subtitle
        $printResponse = $this->actingAs($admin, 'admin')->get(route('admin.invoices.print', $invoice));
        $printResponse->assertOk();
        $printResponse->assertSee('Orchard Tax Invoice');
        $printResponse->assertSee('High Density Apple Project');
    }

    public function test_work_order_invoice_generation_propagates_service_id(): void
    {
        $admin = $this->actingAdmin();
        $service = Service::create([
            'name' => 'Drip Setup Service',
            'slug' => 'drip-setup-service',
            'invoice_settings' => [
                'invoice_prefix' => 'INV-DRP',
                'doc_title' => 'Irrigation Invoice',
            ],
        ]);
        $customer = Customer::create(['name' => 'Bilal Khan', 'phone' => '9900000007', 'password' => 'secret123']);

        $workOrder = WorkOrder::create([
            'number' => 'WO/2026/0099',
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'service_id' => $service->id,
            'service_name' => $service->name,
            'status' => 'completed',
        ]);

        $product = Product::create(['name' => 'Drip Emitter', 'unit' => 'pcs', 'rate' => 15, 'gst_rate' => 18, 'stock_qty' => 100, 'is_active' => true]);
        $stage = ServiceStage::create(['service_id' => $service->id, 'name' => 'Installation', 'sort_order' => 1]);
        $woStage = \App\Models\WorkOrderStage::create(['work_order_id' => $workOrder->id, 'service_stage_id' => $stage->id, 'name' => 'Installation', 'status' => 'completed', 'sort_order' => 1]);
        WorkOrderStageProduct::create([
            'work_order_stage_id' => $woStage->id,
            'product_id' => $product->id,
            'name' => $product->name,
            'unit' => $product->unit,
            'rate' => $product->rate,
            'planned_qty' => 10,
            'used_qty' => 10,
        ]);

        $this->actingAs($admin, 'admin')
            ->post("/admin/work-orders/{$workOrder->id}/invoice")
            ->assertRedirect();

        $invoice = Invoice::where('work_order_id', $workOrder->id)->firstOrFail();
        $this->assertEquals($service->id, $invoice->service_id);
        $this->assertStringStartsWith('INV-DRP/', $invoice->number);
    }
}
