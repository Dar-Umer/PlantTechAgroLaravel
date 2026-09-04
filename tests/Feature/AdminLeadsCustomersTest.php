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

        $response->assertRedirect(route('admin.customers.show', $customer));
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
