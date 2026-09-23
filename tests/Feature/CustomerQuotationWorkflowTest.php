<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Orchard;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Service;
use App\Models\ServiceStage;
use App\Models\WorkOrder;
use Database\Seeders\AdminSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerQuotationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(AdminSeeder::class);
    }

    public function test_customer_can_submit_service_inquiry_for_new_high_density_orchard(): void
    {
        $customer = Customer::create([
            'name' => 'Farooq Abdullah',
            'phone' => '9419112233',
            'password' => 'Secret123!',
            'status' => 'active',
        ]);

        $service = Service::create([
            'name' => 'Book High-Density Orchard Setup',
            'slug' => 'book-high-density-orchard',
            'creates_orchard_on_completion' => true,
            'is_active' => true,
        ]);

        $token = $customer->createToken('test-app')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/service-requests', [
            'service_id' => $service->id,
            'area_kanals' => 12.5,
            'location' => 'Shopian, Pinjora',
            'variety' => 'Gala Schniga / M9 Rootstock',
            'notes' => 'Looking to establish on vacant terraced land with irrigation source nearby.',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('request.service_type', 'new_orchard_establishment');
        $response->assertJsonPath('request.status', 'under_review');

        $this->assertDatabaseHas('leads', [
            'name' => 'Farooq Abdullah',
            'phone' => '9419112233',
            'service_id' => $service->id,
            'source' => 'customer_app',
            'status' => 'new',
            'converted_customer_id' => $customer->id,
        ]);
    }

    public function test_customer_can_submit_service_inquiry_for_existing_orchard(): void
    {
        $customer = Customer::create([
            'name' => 'Altaf Hussain',
            'phone' => '9419445566',
            'password' => 'Secret123!',
            'status' => 'active',
        ]);

        $orchard = Orchard::create([
            'customer_id' => $customer->id,
            'name' => 'Altaf Royal Orchard',
            'area_kanals' => 8,
            'tree_count' => 800,
            'is_company_established' => true,
            'status' => 'active',
        ]);

        $service = Service::create([
            'name' => 'Seasonal Soil & Leaf Analysis',
            'slug' => 'soil-leaf-analysis',
            'creates_orchard_on_completion' => false,
            'is_active' => true,
        ]);

        $token = $customer->createToken('test-app')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/service-requests', [
            'service_id' => $service->id,
            'orchard_id' => $orchard->id,
            'notes' => 'Soil testing for macro/micronutrients before spring spray.',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('request.service_type', 'existing_orchard_service');
        $response->assertJsonPath('request.target_orchard', 'Altaf Royal Orchard');

        $this->assertDatabaseHas('leads', [
            'name' => 'Altaf Hussain',
            'service_id' => $service->id,
            'source' => 'customer_app',
        ]);
    }

    public function test_customer_can_list_view_and_approve_quotation(): void
    {
        $admin = Admin::where('email', 'admin@pta.com')->first();

        $customer = Customer::create([
            'name' => 'Shabir Ahmad',
            'phone' => '9419778899',
            'password' => 'Secret123!',
            'status' => 'active',
        ]);

        $service = Service::create([
            'name' => 'High-Density Trellis & Drip Irrigation',
            'slug' => 'trellis-drip-setup',
            'creates_orchard_on_completion' => true,
            'is_active' => true,
        ]);

        $stage = ServiceStage::create([
            'service_id' => $service->id,
            'name' => 'Soil Bed Preparation',
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'name' => 'Galvanized Trellis Post 3.5m',
            'sku' => 'POST-35M',
            'unit' => 'Pcs',
            'rate' => 850,
            'gst_rate' => 18,
            'stock_qty' => 500,
            'is_active' => true,
        ]);

        // Admin prepares and sends Quotation
        $quotation = Quotation::create([
            'number' => 'QT/2026-27/0055',
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'service_id' => $service->id,
            'date' => now()->toDateString(),
            'valid_until' => now()->addDays(20)->toDateString(),
            'status' => 'sent',
            'subtotal' => 85000,
            'discount_total' => 5000,
            'gst_total' => 14400,
            'grand_total' => 94400,
            'notes' => 'Comprehensive turnkey quote for 10 Kanals high-density setup.',
            'terms' => '50% advance upon confirmation, 50% upon trellis completion.',
            'created_by' => $admin->id,
        ]);

        $quotation->items()->create([
            'product_id' => $product->id,
            'name' => 'Galvanized Trellis Post 3.5m',
            'unit' => 'Pcs',
            'qty' => 100,
            'rate' => 850,
            'discount' => 5000,
            'gst_rate' => 18,
            'total' => 94400,
        ]);

        $token = $customer->createToken('test-app')->plainTextToken;

        // 1. Customer lists quotations
        $listResponse = $this->withToken($token)->getJson('/api/quotations');
        $listResponse->assertOk();
        $listResponse->assertJsonFragment([
            'number' => 'QT/2026-27/0055',
            'status' => 'sent',
            'status_label' => 'Pending Your Approval',
            'grand_total' => 94400.0,
        ]);

        // 2. Customer views full quotation details
        $detailResponse = $this->withToken($token)->getJson("/api/quotations/{$quotation->id}");
        $detailResponse->assertOk();
        $detailResponse->assertJsonPath('quotation.can_approve', true);
        $detailResponse->assertJsonCount(1, 'quotation.items');

        // 3. Customer Approves Quotation
        $approveResponse = $this->withToken($token)->postJson("/api/quotations/{$quotation->id}/approve");
        $approveResponse->assertOk();
        $approveResponse->assertJsonPath('message', 'Quotation approved successfully! Your project is now scheduled.');

        // 4. Verify Quotation is approved and linked to a live Work Order
        $quotation->refresh();
        $this->assertSame('approved', $quotation->status);
        $this->assertNotNull($quotation->work_order_id);

        $workOrder = WorkOrder::find($quotation->work_order_id);
        $this->assertNotNull($workOrder);
        $this->assertSame('in_progress', $workOrder->status);
        $this->assertSame($customer->id, $workOrder->customer_id);
        $this->assertSame($service->id, $workOrder->service_id);
        $this->assertGreaterThan(0, $workOrder->stages()->count());
    }
}
