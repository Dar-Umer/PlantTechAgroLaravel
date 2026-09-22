<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Service;
use App\Models\WorkOrder;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadQuotationWorkOrderTest extends TestCase
{
    use RefreshDatabase;

    private function actingAdmin(): Admin
    {
        $this->seed(AdminSeeder::class);

        return Admin::where('email', 'admin@pta.com')->first();
    }

    public function test_can_create_quotation_from_lead(): void
    {
        $admin = $this->actingAdmin();
        $service = Service::create(['name' => 'Drone Spraying Service', 'slug' => 'drone-spraying', 'is_active' => true]);

        $lead = Lead::create([
            'name' => 'Ghulam Nabi',
            'phone' => '9906112233',
            'service_id' => $service->id,
            'status' => 'interested',
            'source' => 'web',
            'custom_fields' => [
                'area' => 'Shopian',
                'address' => 'Orchard #5, Pinjora',
                'area_kanals' => '15',
            ],
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.quotations.create', ['lead_id' => $lead->id]));
        $response->assertOk();
        $response->assertSee('Ghulam Nabi');
        $response->assertSee('9906112233');

        $storeResponse = $this->actingAs($admin, 'admin')->post(route('admin.quotations.store'), [
            'lead_id' => $lead->id,
            'customer_name' => 'Ghulam Nabi',
            'customer_phone' => '9906112233',
            'customer_area' => 'Shopian',
            'customer_address' => 'Orchard #5, Pinjora',
            'service_id' => $service->id,
            'date' => now()->toDateString(),
            'valid_until' => now()->addDays(15)->toDateString(),
            'status' => 'sent',
            'notes' => 'Drone spraying scheduled for 15 kanals apple orchard.',
            'terms' => '50% advance before flight.',
            'items' => [
                [
                    'name' => 'Precision Drone Spraying - 15 Kanals',
                    'unit' => 'Kanal',
                    'qty' => 15,
                    'rate' => 600,
                    'discount' => 500,
                    'gst_rate' => 18,
                ],
                [
                    'name' => 'Organic Fungicide Solution 5L',
                    'unit' => 'Bottle',
                    'qty' => 3,
                    'rate' => 1200,
                    'discount' => 0,
                    'gst_rate' => 12,
                ],
            ],
        ]);

        $storeResponse->assertRedirect();

        $quotation = Quotation::first();
        $this->assertNotNull($quotation);
        $this->assertEquals('Ghulam Nabi', $quotation->customer_name);
        $this->assertEquals($lead->id, $quotation->lead_id);
        $this->assertEquals(2, $quotation->items()->count());
        $this->assertGreaterThan(0, $quotation->grand_total);
    }

    public function test_can_download_quotation_pdf(): void
    {
        $admin = $this->actingAdmin();
        $service = Service::create(['name' => 'Trellis Installation', 'slug' => 'trellis-installation', 'is_active' => true]);

        $quotation = Quotation::create([
            'number' => 'QT/2026-27/0001',
            'customer_name' => 'Bashir Ahmad',
            'customer_phone' => '9906778899',
            'service_id' => $service->id,
            'date' => now()->toDateString(),
            'status' => 'sent',
            'subtotal' => 50000,
            'grand_total' => 59000,
            'created_by' => $admin->id,
        ]);

        $quotation->items()->create([
            'name' => 'High-Density Trellis Structure per Kanal',
            'unit' => 'Kanal',
            'qty' => 5,
            'rate' => 10000,
            'discount' => 0,
            'gst_rate' => 18,
            'total' => 59000,
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.quotations.pdf', $quotation));
        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }

    public function test_approve_and_start_work_converts_lead_and_creates_active_work_order(): void
    {
        $admin = $this->actingAdmin();
        $service = Service::create(['name' => 'Drone Spraying Service', 'slug' => 'drone-spraying', 'is_active' => true]);

        $lead = Lead::create([
            'name' => 'Tariq Lone',
            'phone' => '9419001122',
            'service_id' => $service->id,
            'status' => 'interested',
            'source' => 'web',
            'custom_fields' => [
                'area' => 'Baramulla',
                'address' => 'Delina apple block 2',
            ],
        ]);

        $quotation = Quotation::create([
            'number' => 'QT/2026-27/0002',
            'lead_id' => $lead->id,
            'customer_name' => 'Tariq Lone',
            'customer_phone' => '9419001122',
            'customer_area' => 'Baramulla',
            'customer_address' => 'Delina apple block 2',
            'service_id' => $service->id,
            'date' => now()->toDateString(),
            'status' => 'sent',
            'subtotal' => 9000,
            'grand_total' => 10620,
            'created_by' => $admin->id,
        ]);

        $quotation->items()->create([
            'name' => 'Drone Spraying (15 Kanals)',
            'unit' => 'Kanal',
            'qty' => 15,
            'rate' => 600,
            'discount' => 0,
            'gst_rate' => 18,
            'total' => 10620,
        ]);

        // Customer does not exist yet
        $this->assertNull(Customer::findByPhoneDigits('9419001122'));

        // Admin clicks "Approve & Start Work"
        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.quotations.approve', $quotation));

        $response->assertRedirect();

        // 1. Customer should now exist
        $customer = Customer::findByPhoneDigits('9419001122');
        $this->assertNotNull($customer);
        $this->assertEquals('Tariq Lone', $customer->name);
        $this->assertEquals('active', $customer->status);

        // 2. Lead should be converted
        $lead->refresh();
        $this->assertTrue($lead->isConverted());
        $this->assertEquals($customer->id, $lead->converted_customer_id);

        // 3. Quotation should be approved and linked
        $quotation->refresh();
        $this->assertTrue($quotation->isApproved());
        $this->assertNotNull($quotation->work_order_id);

        // 4. Work Order should be active (in_progress) with quoted items
        $workOrder = WorkOrder::find($quotation->work_order_id);
        $this->assertNotNull($workOrder);
        $this->assertEquals('in_progress', $workOrder->status);
        $this->assertEquals($customer->id, $workOrder->customer_id);
        $this->assertNotNull($workOrder->started_at);

        // Stage products should include the quoted drone spray
        $firstStage = $workOrder->stages()->first();
        $this->assertNotNull($firstStage);
        $this->assertDatabaseHas('work_order_stage_products', [
            'work_order_stage_id' => $firstStage->id,
            'name' => 'Drone Spraying (15 Kanals)',
        ]);
    }
}
