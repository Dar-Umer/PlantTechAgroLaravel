<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use App\Services\WeatherService;
use App\Support\AgriAdvisory;
use Database\Seeders\AdminSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherTest extends TestCase
{
    use RefreshDatabase;

    private function actingAdmin(): Admin
    {
        $this->seed([RolesAndPermissionsSeeder::class, AdminSeeder::class]);

        $admin = Admin::where('email', 'admin@pta.com')->first();
        $admin->assignRole('Super Admin');

        return $admin;
    }

    private function fakeForecast(): array
    {
        return [
            'current' => [
                'time' => '2026-09-20T10:00',
                'temperature_2m' => 18.5,
                'relative_humidity_2m' => 60,
                'apparent_temperature' => 17.0,
                'precipitation' => 0.0,
                'weather_code' => 2,
                'wind_speed_10m' => 8.0,
            ],
            'daily' => [
                'time' => ['2026-09-20', '2026-09-21'],
                'temperature_2m_max' => [22.0, 12.0],
                'temperature_2m_min' => [10.0, 1.0],
                'precipitation_sum' => [0.0, 5.0],
                'precipitation_probability_max' => [10, 80],
                'wind_speed_10m_max' => [12.0, 25.0],
                'uv_index_max' => [5.0, 3.0],
            ],
        ];
    }

    public function test_district_resolution_matches_area_and_falls_back(): void
    {
        $pulwama = WeatherService::resolveDistrict('  Pulwama ');
        $this->assertSame('pulwama', $pulwama['district']);
        $this->assertEquals(33.8792, $pulwama['lat']);

        $fallback = WeatherService::resolveDistrict('Somewhere Unknown');
        $this->assertSame(config('weather.default_district', 'srinagar'), $fallback['district']);

        $blank = WeatherService::resolveDistrict(null);
        $this->assertSame($fallback['district'], $blank['district']);
    }

    public function test_disabled_service_returns_null_without_http(): void
    {
        config(['weather.enabled' => false]);
        Http::preventStrayRequests();

        $this->assertNull(WeatherService::forArea('Pulwama'));
    }

    public function test_advisory_flags_frost_and_spray_conditions(): void
    {
        $forecast = WeatherService::shape($this->fakeForecast());

        $this->assertNotNull($forecast);
        $this->assertSame('Partly cloudy', $forecast['current']['label']);
        $this->assertCount(2, $forecast['daily']);

        $advisory = AgriAdvisory::fromForecast($forecast);

        // Day 2 min 1.0°C < 2°C frost threshold → alert level.
        $this->assertSame('alert', $advisory['level']);
        $this->assertNotEmpty($advisory['messages']);
    }

    public function test_api_weather_endpoint_returns_farmer_forecast(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::response($this->fakeForecast(), 200)]);

        $customer = Customer::create([
            'name' => 'Farooq Ahmad',
            'phone' => '9999999999',
            'password' => 'Passw0rd!',
            'status' => 'active',
            'area' => 'Pulwama',
        ]);

        $response = $this->actingAs($customer, 'sanctum')->getJson('/api/weather');

        $response->assertOk()
            ->assertJsonPath('weather.location.district', 'pulwama')
            ->assertJsonPath('weather.current.temp', 18.5)
            ->assertJsonStructure(['weather' => ['location', 'current', 'daily', 'advisory']]);
    }

    public function test_api_weather_endpoint_404_when_disabled(): void
    {
        config(['weather.enabled' => false]);

        $customer = Customer::create([
            'name' => 'Farooq Ahmad',
            'phone' => '9999999999',
            'password' => 'Passw0rd!',
            'status' => 'active',
        ]);

        $this->actingAs($customer, 'sanctum')->getJson('/api/weather')->assertNotFound();
    }

    public function test_dashboard_includes_weather_for_customer_area(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::response($this->fakeForecast(), 200)]);

        $customer = Customer::create([
            'name' => 'Aisha Bano',
            'phone' => '8888888888',
            'password' => 'Passw0rd!',
            'status' => 'active',
            'area' => 'Anantnag',
        ]);

        $this->actingAs($customer, 'sanctum')->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('weather.location.district', 'anantnag');
    }

    public function test_admin_can_update_weather_settings(): void
    {
        $admin = $this->actingAdmin();

        $response = $this->actingAs($admin, 'admin')->put(route('admin.settings.update'), [
            'tab' => 'weather',
            'invoice_company_name' => 'Plant Tech Agro',
            'invoice_prefix' => 'PTA',
            'weather_enabled' => '1',
            'weather_default_district' => 'pulwama',
            'weather_cache_ttl_minutes' => 90,
            'weather_forecast_days' => 3,
            'weather_frost_threshold_c' => 1.5,
            'weather_show_admin_card' => '1',
        ]);

        $response->assertRedirect(route('admin.settings.index', ['tab' => 'weather']));

        $this->assertDatabaseHas('settings', ['key' => 'weather.default_district']);
        $this->assertSame('pulwama', config('weather.default_district'));
        $this->assertSame(90, config('weather.cache_ttl_minutes'));
    }

    public function test_admin_settings_page_renders_weather_tab(): void
    {
        $admin = $this->actingAdmin();

        $this->actingAs($admin, 'admin')->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('Weather', false)
            ->assertSee('weather_enabled', false);
    }

    protected function tearDown(): void
    {
        Cache::flush();

        parent::tearDown();
    }
}
