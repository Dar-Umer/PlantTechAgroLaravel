<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Orchard;
use App\Models\Service;
use App\Models\ServiceStage;
use App\Models\WorkOrder;
use App\Models\WorkOrderStage;
use Database\Seeders\AdminSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrchardManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function actingSuperAdmin(): Admin
    {
        $this->seed(AdminSeeder::class);
        $admin = Admin::where('email', 'admin@pta.com')->first();
        $admin->assignRole('Super Admin');

        return $admin;
    }

    public function test_customer_gets_auto_assigned_unique_orchardist_id(): void
    {
        $c1 = Customer::create([
            'name' => 'Ghulam Mohammad',
            'phone' => '9419000001',
            'password' => 'Secret123!',
            'status' => 'active',
        ]);

        $c2 = Customer::create([
            'name' => 'Bashir Ahmad',
            'phone' => '9419000002',
            'password' => 'Secret123!',
            'status' => 'active',
        ]);

        $this->assertNotEmpty($c1->orchardist_id);
        $this->assertNotEmpty($c2->orchardist_id);
        $this->assertStringStartsWith('OID-', $c1->orchardist_id);
        $this->assertStringStartsWith('OID-', $c2->orchardist_id);
        $this->assertNotSame($c1->orchardist_id, $c2->orchardist_id);
    }

    public function test_customer_can_list_and_create_their_own_orchard_via_api(): void
    {
        $customer = Customer::create([
            'name' => 'Tariq Mir',
            'phone' => '9419111111',
            'password' => 'Secret123!',
            'status' => 'active',
        ]);

        $token = $customer->createToken('test-app')->plainTextToken;

        // Customer creates their own orchard
        $response = $this->withToken($token)->postJson('/api/orchards', [
            'name' => 'Mir Apple Valley',
            'address' => 'Zainapora, Shopian',
            'latitude' => 33.7255,
            'longitude' => 74.8411,
            'area_kanals' => 8.5,
            'tree_count' => 1200,
            'date_of_establishment' => '2022-03-15',
            'variety_notes' => 'Gala, Red Velox',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('orchard.name', 'Mir Apple Valley')
            ->assertJsonPath('orchard.is_company_established', false)
            ->assertJsonPath('orchard.company_tag', 'Self Registered');

        $this->assertDatabaseHas('orchards', [
            'customer_id' => $customer->id,
            'name' => 'Mir Apple Valley',
            'is_company_established' => false,
        ]);

        // Customer lists their orchards
        $listResponse = $this->withToken($token)->getJson('/api/orchards');
        $listResponse->assertOk()
            ->assertJsonCount(1, 'orchards')
            ->assertJsonPath('counts.total', 1)
            ->assertJsonPath('counts.self_registered', 1)
            ->assertJsonPath('counts.company_established', 0);
    }

    public function test_completing_establishment_service_automatically_creates_company_orchard(): void
    {
        $admin = $this->actingSuperAdmin();

        $customer = Customer::create([
            'name' => 'Showkat Rather',
            'phone' => '9419222222',
            'password' => 'Secret123!',
            'status' => 'active',
            'address' => 'Keller, Shopian',
        ]);

        $service = Service::create([
            'name' => 'High Density Orchard Establishment',
            'slug' => 'high-density-orchard-establishment',
            'creates_orchard_on_completion' => true,
            'is_active' => true,
        ]);

        $workOrder = WorkOrder::create([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'service_id' => $service->id,
            'service_name' => $service->name,
            'status' => 'in_progress',
        ]);

        $stage = WorkOrderStage::create([
            'work_order_id' => $workOrder->id,
            'name' => 'Final Trellis & Planting',
            'sort_order' => 1,
            'status' => 'pending',
        ]);

        // Admin completes the stage
        $this->actingAs($admin, 'admin')
            ->patch(route('admin.work-orders.stages.complete', [$workOrder, $stage]));

        $workOrder->refresh();
        $this->assertSame('completed', $workOrder->status);
        $this->assertNotNull($workOrder->orchard_id);

        $orchard = Orchard::find($workOrder->orchard_id);
        $this->assertNotNull($orchard);
        $this->assertSame($customer->id, $orchard->customer_id);
        $this->assertTrue($orchard->is_company_established);
        $this->assertSame('Established by Plant Tech Agro', $orchard->company_tag);
        $this->assertSame($workOrder->id, $orchard->work_order_id);
    }

    public function test_customer_can_book_service_for_existing_orchard(): void
    {
        $customer = Customer::create([
            'name' => 'Manzoor Ahmad',
            'phone' => '9419333333',
            'password' => 'Secret123!',
            'status' => 'active',
        ]);

        $orchard = Orchard::create([
            'customer_id' => $customer->id,
            'name' => 'Manzoor Orchard Block 1',
            'address' => 'Pulwama',
            'area_kanals' => 10,
            'tree_count' => 1500,
            'date_of_establishment' => '2020-04-01',
            'is_company_established' => false,
        ]);

        $service = Service::create([
            'name' => 'Seasonal Orchard Pruning',
            'slug' => 'seasonal-orchard-pruning',
            'creates_orchard_on_completion' => false,
            'is_active' => true,
        ]);

        $token = $customer->createToken('test-app')->plainTextToken;

        // Customer books pruning for their orchard
        $response = $this->withToken($token)->postJson('/api/work-orders', [
            'service_id' => $service->id,
            'orchard_id' => $orchard->id,
            'notes' => 'Please prune upper canopy',
        ]);

        $response->assertStatus(201);
        $createdWoId = $response->json('work_order.id');

        $this->assertDatabaseHas('work_orders', [
            'id' => $createdWoId,
            'orchard_id' => $orchard->id,
            'customer_id' => $customer->id,
        ]);
    }

    public function test_admin_can_manage_orchards_via_admin_panel(): void
    {
        $admin = $this->actingSuperAdmin();

        $customer = Customer::create([
            'name' => 'Javaid Khan',
            'phone' => '9419444444',
            'password' => 'Secret123!',
            'status' => 'active',
        ]);

        // Admin registers an orchard
        $response = $this->actingAs($admin, 'admin')->post(route('admin.orchards.store'), [
            'customer_id' => $customer->id,
            'name' => 'Khan High Density Estate',
            'address' => 'Baramulla, Kashmir',
            'latitude' => 34.2001,
            'longitude' => 74.3456,
            'area_kanals' => 12.0,
            'tree_count' => 2000,
            'date_of_establishment' => '2021-05-10',
            'is_company_established' => 1,
            'status' => 'active',
            'variety_notes' => 'Red Chief, Gala',
        ]);

        $orchard = Orchard::where('customer_id', $customer->id)->first();
        $this->assertNotNull($orchard);
        $response->assertRedirect(route('admin.orchards.show', $orchard));

        $this->assertTrue($orchard->is_company_established);
        $this->assertStringStartsWith('ORC-', $orchard->orchard_id);

        // Admin index list shows it
        $indexResponse = $this->actingAs($admin, 'admin')->get(route('admin.orchards.index', ['type' => 'company']));
        $indexResponse->assertOk()
            ->assertSee('Khan High Density Estate')
            ->assertSee($orchard->orchard_id)
            ->assertSee('Established by Plant Tech Agro');
    }

    public function test_profile_and_dashboard_return_orchardist_id_and_orchard_stats(): void
    {
        $customer = Customer::create([
            'name' => 'Altaf Hussain',
            'phone' => '9419555555',
            'password' => 'Secret123!',
            'status' => 'active',
        ]);

        Orchard::create([
            'customer_id' => $customer->id,
            'name' => 'Altaf Farm 1',
            'address' => 'Kulgam',
            'area_kanals' => 6,
            'tree_count' => 800,
            'date_of_establishment' => '2023-01-01',
            'is_company_established' => true,
        ]);

        $token = $customer->createToken('test-app')->plainTextToken;

        // Profile check
        $profileResponse = $this->withToken($token)->getJson('/api/me');
        $profileResponse->assertOk()
            ->assertJsonPath('user.orchardist_id', $customer->orchardist_id)
            ->assertJsonPath('user.orchards_count', 1);

        // Dashboard check
        $dashboardResponse = $this->withToken($token)->getJson('/api/dashboard');
        $dashboardResponse->assertOk()
            ->assertJsonPath('orchardist_id', $customer->orchardist_id)
            ->assertJsonPath('stats.orchards_total', 1)
            ->assertJsonPath('stats.company_orchards_total', 1);
    }
}
