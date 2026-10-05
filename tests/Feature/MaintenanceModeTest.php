<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckMaintenanceMode;
use App\Models\Admin;
use App\Services\ShopSettingsService;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    private function getSuperAdmin(): Admin
    {
        $this->seed(AdminSeeder::class);
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $admin = Admin::where('email', 'admin@pta.com')->first();
        $admin->assignRole('Super Admin');

        return $admin;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure maintenance mode is off at start of each test
        app(ShopSettingsService::class)->set(['maintenance_mode' => false], 'shop');
        app(ShopSettingsService::class)->set(['maintenance_mode' => false], 'mobile');
    }

    protected function tearDown(): void
    {
        app(ShopSettingsService::class)->set(['maintenance_mode' => false], 'shop');
        app(ShopSettingsService::class)->set(['maintenance_mode' => false], 'mobile');

        parent::tearDown();
    }

    public function test_when_maintenance_mode_is_off_all_endpoints_are_accessible(): void
    {
        $this->assertFalse(CheckMaintenanceMode::isMaintenanceMode());

        $response = $this->get('/');
        $response->assertStatus(200);

        $appConfig = $this->getJson('/api/app-config');
        $appConfig->assertStatus(200);
        $this->assertFalse($appConfig->json('app_config.mobile.maintenance_mode'));
    }

    public function test_when_maintenance_mode_is_on_guests_receive_503_maintenance_page(): void
    {
        app(ShopSettingsService::class)->set(['maintenance_mode' => true], 'shop');
        app(ShopSettingsService::class)->set(['maintenance_mode' => true], 'mobile');

        $this->assertTrue(CheckMaintenanceMode::isMaintenanceMode());

        $response = $this->get('/');
        $response->assertStatus(503);
        $response->assertSee('Scheduled Maintenance');
        $this->assertEquals('300', $response->headers->get('Retry-After'));
    }

    public function test_when_maintenance_mode_is_on_api_requests_receive_503_json(): void
    {
        app(ShopSettingsService::class)->set(['maintenance_mode' => true], 'shop');

        $response = $this->postJson('/api/auth/login', [
            'phone' => '9999999999',
            'password' => 'secret',
        ]);
        $response->assertStatus(503);
        $response->assertJson([
            'maintenance' => true,
        ]);
        $this->assertEquals('300', $response->headers->get('Retry-After'));
    }

    public function test_app_config_endpoint_is_not_blocked_and_reports_maintenance_mode(): void
    {
        app(ShopSettingsService::class)->set(['maintenance_mode' => true], 'mobile');

        $response = $this->getJson('/api/app-config');
        $response->assertStatus(200);
        $this->assertTrue($response->json('app_config.mobile.maintenance_mode'));
    }

    public function test_health_check_up_endpoint_is_not_blocked_during_maintenance(): void
    {
        app(ShopSettingsService::class)->set(['maintenance_mode' => true], 'shop');

        $response = $this->get('/up');
        $response->assertStatus(200);
    }

    public function test_admin_login_is_always_accessible_during_maintenance(): void
    {
        app(ShopSettingsService::class)->set(['maintenance_mode' => true], 'shop');

        $response = $this->get('/admin/login');
        $response->assertStatus(200);
    }

    public function test_authenticated_admin_can_access_admin_panel_during_maintenance(): void
    {
        app(ShopSettingsService::class)->set(['maintenance_mode' => true], 'shop');

        $admin = $this->getSuperAdmin();

        $response = $this->actingAs($admin, 'admin')->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Maintenance Mode is ACTIVE');
    }

    public function test_authenticated_admin_can_preview_frontend_during_maintenance(): void
    {
        app(ShopSettingsService::class)->set(['maintenance_mode' => true], 'shop');

        $admin = $this->getSuperAdmin();

        $response = $this->actingAs($admin, 'admin')->get('/');
        $response->assertStatus(200);
        $response->assertSee('Admin Preview');
    }

    public function test_admin_can_toggle_maintenance_mode_endpoint(): void
    {
        $admin = $this->getSuperAdmin();

        $this->assertFalse(CheckMaintenanceMode::isMaintenanceMode());

        // Toggle ON
        $response = $this->actingAs($admin, 'admin')
            ->post('/admin/settings/maintenance/toggle', ['maintenance_mode' => '1']);

        $response->assertRedirect();
        $this->assertTrue(CheckMaintenanceMode::isMaintenanceMode());

        // Toggle OFF
        $response = $this->actingAs($admin, 'admin')
            ->post('/admin/settings/maintenance/toggle', ['maintenance_mode' => '0']);

        $response->assertRedirect();
        $this->assertFalse(CheckMaintenanceMode::isMaintenanceMode());
    }
}
