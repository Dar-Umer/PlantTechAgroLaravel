<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeadFormField;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\ServiceStage;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLeadsCustomersTest extends TestCase
{
    use RefreshDatabase;

    private function actingAdmin(): Admin
    {
        $this->seed(AdminSeeder::class);

        return Admin::where('email', 'admin@pta.com')->first();
    }

    public function test_guest_is_redirected_from_admin_leads(): void
    {
        $this->get('/admin/leads')->assertRedirect(route('admin.login'));
    }

    public function test_leads_index_renders(): void
    {
        $admin = $this->actingAdmin();
        $lead = Lead::create(['name' => 'Farooq', 'phone' => '9999999999']);

        $this->actingAs($admin, 'admin')
            ->get('/admin/leads')
            ->assertOk()
            ->assertSee('Farooq');
    }

    public function test_lead_can_be_converted_to_customer(): void
    {
        $admin = $this->actingAdmin();
        $service = Service::factory()->create();
        $lead = Lead::create([
            'name' => 'Farooq Ahmad',
            'phone' => '9999999999',
            'service_id' => $service->id,
            'custom_fields' => ['address' => 'Pulwama'],
        ]);

        $this->actingAs($admin, 'admin')
            ->get("/admin/leads/{$lead->id}/convert")
            ->assertOk()
            ->assertSee('Convert Lead to Customer');

        $response = $this->actingAs($admin, 'admin')->post("/admin/leads/{$lead->id}/convert", [
            'name' => 'Farooq Ahmad',
            'phone' => '9999999999',
            'address' => 'Pulwama',
            'area' => 'Tahab',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $customer = Customer::where('phone', '9999999999')->first();
        $this->assertNotNull($customer);
        $this->assertEquals('Pulwama', $customer->address);
        $this->assertTrue(password_verify('secret123', $customer->password));

        $lead->refresh();
        $this->assertEquals('converted', $lead->status);
        $this->assertEquals($customer->id, $lead->converted_customer_id);

        $workOrder = \App\Models\WorkOrder::where('customer_id', $customer->id)->first();
        $this->assertNotNull($workOrder);
        $this->assertEquals('pending', $workOrder->status);
        $this->assertNull($workOrder->assigned_agent_id);

        $response->assertRedirect(route('admin.work-orders.show', $workOrder));
    }

    public function test_convert_creates_work_order_with_stages_and_no_stock_movement(): void
    {
        $admin = $this->actingAdmin();
        $service = Service::factory()->create();
        ServiceStage::create(['service_id' => $service->id, 'name' => 'Land Layout', 'sort_order' => 1]);
        ServiceStage::create(['service_id' => $service->id, 'name' => 'Install Drip', 'sort_order' => 2]);
        $lead = Lead::create([
            'name' => 'Farooq Ahmad',
            'phone' => '9999999999',
            'service_id' => $service->id,
            'notes' => 'Wants completion before harvest.',
        ]);

        $this->actingAs($admin, 'admin')->post("/admin/leads/{$lead->id}/convert", [
            'name' => 'Farooq Ahmad',
            'phone' => '9999999999',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertRedirect();

        $customer = Customer::where('phone', '9999999999')->firstOrFail();
        $workOrder = \App\Models\WorkOrder::where('customer_id', $customer->id)->firstOrFail();

        $this->assertEquals($service->id, $workOrder->service_id);
        $this->assertEquals('pending', $workOrder->status);
        $this->assertCount(2, $workOrder->stages);
        $this->assertStringContainsString('harvest', $workOrder->notes);
        $this->assertSame(0, \App\Models\StockMovement::count());
    }

    public function test_convert_is_blocked_when_service_missing_or_inactive(): void
    {
        $admin = $this->actingAdmin();
        $inactive = Service::factory()->create(['is_active' => false]);
        $lead = Lead::create([
            'name' => 'Farooq',
            'phone' => '9777777777',
            'service_id' => $inactive->id,
        ]);

        $this->actingAs($admin, 'admin')->post("/admin/leads/{$lead->id}/convert", [
            'name' => 'Farooq',
            'phone' => '9777777777',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertSessionHas('error');

        $this->assertSame(0, Customer::count());
        $this->assertSame(0, \App\Models\WorkOrder::count());
        $this->assertSame('new', $lead->refresh()->status ?? 'new');
    }

    public function test_lead_cannot_be_converted_twice(): void
    {
        $admin = $this->actingAdmin();
        $customer = Customer::create([
            'name' => 'Existing',
            'phone' => '9888888888',
            'password' => 'secret123',
        ]);
        $lead = Lead::create([
            'name' => 'Farooq',
            'phone' => '9777777777',
            'status' => 'converted',
            'converted_customer_id' => $customer->id,
        ]);

        $this->actingAs($admin, 'admin')
            ->post("/admin/leads/{$lead->id}/convert", [
                'name' => 'Farooq',
                'phone' => '9777777777',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
            ])
            ->assertRedirect(route('admin.leads.show', $lead));
    }

    public function test_converted_lead_cannot_be_deleted(): void
    {
        $admin = $this->actingAdmin();
        $customer = Customer::create([
            'name' => 'Existing',
            'phone' => '9888888888',
            'password' => 'secret123',
        ]);
        $lead = Lead::create([
            'name' => 'Farooq',
            'phone' => '9777777777',
            'status' => 'converted',
            'converted_customer_id' => $customer->id,
        ]);

        $this->actingAs($admin, 'admin')
            ->delete("/admin/leads/{$lead->id}")
            ->assertRedirect(route('admin.leads.show', $lead));

        $this->assertDatabaseHas('leads', ['id' => $lead->id]);
    }

    public function test_converted_lead_status_cannot_be_changed(): void
    {
        $admin = $this->actingAdmin();
        $lead = Lead::create([
            'name' => 'Farooq',
            'phone' => '9777777777',
            'status' => 'converted',
        ]);

        $this->actingAs($admin, 'admin')
            ->patch("/admin/leads/{$lead->id}/status", ['status' => 'interested'])
            ->assertSessionHas('error');

        $this->assertSame('converted', $lead->refresh()->status);
    }

    public function test_converted_lead_cannot_be_edited(): void
    {
        $admin = $this->actingAdmin();
        $lead = Lead::create([
            'name' => 'Farooq',
            'phone' => '9777777777',
            'status' => 'converted',
        ]);

        $this->actingAs($admin, 'admin')
            ->get("/admin/leads/{$lead->id}/edit")
            ->assertRedirect(route('admin.leads.show', $lead));

        $this->actingAs($admin, 'admin')
            ->put("/admin/leads/{$lead->id}", ['name' => 'Changed', 'phone' => '9777777777'])
            ->assertRedirect(route('admin.leads.show', $lead));

        $this->assertSame('Farooq', $lead->refresh()->name);
    }

    public function test_existing_customer_lead_handoff_creates_no_customer_and_prefills_work_order(): void
    {
        $admin = $this->actingAdmin();
        $service = Service::factory()->create();
        $customer = Customer::create([
            'name' => 'Existing Customer',
            'phone' => '9888888888',
            'password' => 'secret123',
        ]);
        $lead = Lead::create([
            'name' => 'Existing Customer',
            'phone' => '9888888888',
            'service_id' => $service->id,
        ]);

        // Show page offers Work Order instead of Convert.
        $this->actingAs($admin, 'admin')->get("/admin/leads/{$lead->id}")
            ->assertOk()
            ->assertSee('New Work Order', false)
            ->assertDontSee('Convert to Customer', false);

        $response = $this->actingAs($admin, 'admin')
            ->post("/admin/leads/{$lead->id}/work-order");

        // Lead locked to the existing customer, redirected to prefilled form.
        $lead->refresh();
        $this->assertSame('converted', $lead->status);
        $this->assertEquals($customer->id, $lead->converted_customer_id);
        $this->assertSame(1, Customer::count());

        $response->assertRedirect(route('admin.work-orders.create', [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
        ]));

        // Prefilled create form renders with both selected.
        $this->actingAs($admin, 'admin')
            ->get(route('admin.work-orders.create', ['customer_id' => $customer->id, 'service_id' => $service->id]))
            ->assertOk()
            ->assertSee('value="' . $customer->id . '" selected', false);

        // Repeat handoff bounces.
        $this->actingAs($admin, 'admin')
            ->post("/admin/leads/{$lead->id}/work-order")
            ->assertRedirect(route('admin.leads.show', $lead));
    }

    public function test_handoff_blocked_without_matching_customer_or_service(): void
    {
        $admin = $this->actingAdmin();
        $lead = Lead::create(['name' => 'Nobody', 'phone' => '9111111111']);

        $this->actingAs($admin, 'admin')
            ->post("/admin/leads/{$lead->id}/work-order")
            ->assertSessionHas('error');

        $this->assertSame('new', $lead->refresh()->status);
    }

    public function test_conversion_requires_unique_phone_and_password_confirmation(): void
    {
        $admin = $this->actingAdmin();
        $lead = Lead::create(['name' => 'Farooq', 'phone' => '9777777777']);

        Customer::create(['name' => 'Taken', 'phone' => '9888888888', 'password' => 'secret123']);

        $this->actingAs($admin, 'admin')
            ->post("/admin/leads/{$lead->id}/convert", [
                'name' => 'Farooq',
                'phone' => '9888888888',
                'password' => 'secret123',
                'password_confirmation' => 'mismatch',
            ])
            ->assertInvalid(['phone', 'password']);

        $this->assertSame(1, Customer::count());
    }

    public function test_customers_crud_renders(): void
    {
        $admin = $this->actingAdmin();

        $this->actingAs($admin, 'admin')->get('/admin/customers')->assertOk();
        $this->actingAs($admin, 'admin')->get('/admin/customers/create')->assertOk();

        $this->actingAs($admin, 'admin')->post('/admin/customers', [
            'name' => 'Aisha Bano',
            'phone' => '9666666666',
            'password' => 'secret123',
            'status' => 'active',
        ])->assertRedirect(route('admin.customers.index'));

        $customer = Customer::where('phone', '9666666666')->first();
        $this->assertNotNull($customer);

        $this->actingAs($admin, 'admin')->get("/admin/customers/{$customer->id}")->assertOk()->assertSee('Aisha Bano');
        $this->actingAs($admin, 'admin')->get("/admin/customers/{$customer->id}/edit")->assertOk();

        $this->actingAs($admin, 'admin')->put("/admin/customers/{$customer->id}", [
            'name' => 'Aisha Bano Updated',
            'phone' => '9666666666',
            'password' => '',
            'status' => 'inactive',
        ])->assertRedirect(route('admin.customers.show', $customer));

        $customer->refresh();
        $this->assertEquals('Aisha Bano Updated', $customer->name);
        $this->assertEquals('inactive', $customer->status);
    }

    public function test_frontend_page_renders_and_lead_form_field_crud(): void
    {
        $admin = $this->actingAdmin();

        $this->actingAs($admin, 'admin')
            ->get('/admin/frontend')
            ->assertOk()
            ->assertSee('Lead Form')
            ->assertSee('Mandatory Fields');

        $this->actingAs($admin, 'admin')->post('/admin/frontend/lead-form/fields', [
            'label' => 'Village',
            'type' => 'text',
            'is_required' => '1',
            'is_active' => '1',
            'sort_order' => 1,
        ])->assertRedirect(route('admin.frontend.index', ['tab' => 'lead_form']));

        $field = LeadFormField::where('name', 'village')->first();
        $this->assertNotNull($field);
        $this->assertTrue($field->is_required);

        $this->actingAs($admin, 'admin')->post('/admin/frontend/lead-form/fields', [
            'label' => 'Category',
            'type' => 'select',
            'options' => 'Plants, Soil Test, Drip',
            'is_active' => '1',
            'sort_order' => 2,
        ])->assertRedirect();

        $selectField = LeadFormField::where('name', 'category')->first();
        $this->assertSame(['Plants', 'Soil Test', 'Drip'], $selectField->options);

        $this->actingAs($admin, 'admin')->post('/admin/frontend/lead-form/fields', [
            'label' => 'Broken',
            'type' => 'select',
            'is_active' => '1',
        ])->assertInvalid(['options']);

        $this->actingAs($admin, 'admin')->delete("/admin/frontend/lead-form/fields/{$field->id}")
            ->assertRedirect();
        $this->assertSame(1, LeadFormField::count());
    }

    public function test_service_stages_and_items_crud(): void
    {
        $admin = $this->actingAdmin();
        $service = Service::factory()->create();

        $this->actingAs($admin, 'admin')->post("/admin/services/{$service->id}/stages", [
            'name' => 'Land Layout',
            'description' => 'Layout marking of the orchard.',
            'sort_order' => 1,
            'requires_photo' => '1',
            'min_photos' => 3,
            'requires_pdf' => '0',
        ])->assertRedirect();

        $stage = ServiceStage::where('service_id', $service->id)->first();
        $this->assertNotNull($stage);
        $this->assertTrue($stage->requires_photo);
        $this->assertEquals(3, $stage->min_photos);

        $this->actingAs($admin, 'admin')->put("/admin/stages/{$stage->id}", [
            'name' => 'Land Layout Updated',
            'sort_order' => 1,
            'min_photos' => 2,
        ])->assertRedirect();

        $this->actingAs($admin, 'admin')->post("/admin/services/{$service->id}/items", [
            'name' => 'M9-T337 Apple Plants',
            'description' => '500 plants',
            'sort_order' => 1,
        ])->assertRedirect();

        $item = ServiceItem::where('service_id', $service->id)->first();
        $this->assertNotNull($item);
        $this->assertEquals('M9-T337 Apple Plants', $item->name);

        $this->actingAs($admin, 'admin')->get("/admin/services/{$service->id}/stages")->assertOk();
        $this->actingAs($admin, 'admin')->get("/admin/services/{$service->id}/items")->assertOk();
        $this->actingAs($admin, 'admin')->get("/admin/services/{$service->id}/edit")->assertOk();

        $this->actingAs($admin, 'admin')->delete("/admin/stages/{$stage->id}")->assertRedirect();
        $this->actingAs($admin, 'admin')->delete("/admin/items/{$item->id}")->assertRedirect();
        $this->assertSame(0, ServiceStage::count());
        $this->assertSame(0, ServiceItem::count());
    }
}
