<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Lead;
use App\Models\Service;
use App\Support\Recaptcha;
use Database\Seeders\AdminSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RecaptchaTest extends TestCase
{
    use RefreshDatabase;

    private function enableRecaptcha(): void
    {
        config([
            'apis.recaptcha_enabled' => true,
            'apis.recaptcha_site_key' => 'test-site-key',
            'apis.recaptcha_secret_key' => 'test-secret-key',
            'apis.recaptcha_min_score' => 0.5,
        ]);
    }

    private function leadPayload(Service $service): array
    {
        return [
            'name' => 'Farooq Ahmad',
            'phone' => '9999999999',
            'service_id' => $service->id,
            'loaded_at' => time() - 10,
            'g-recaptcha-response' => 'test-token',
        ];
    }

    public function test_verifier_is_disabled_without_keys(): void
    {
        $this->assertFalse(Recaptcha::enabled());

        config(['apis.recaptcha_enabled' => true]);
        $this->assertFalse(Recaptcha::enabled());
    }

    public function test_verifier_accepts_high_score_and_rejects_low_score(): void
    {
        $this->enableRecaptcha();

        Http::fakeSequence()
            ->push(['success' => true, 'score' => 0.9], 200)
            ->push(['success' => true, 'score' => 0.1], 200)
            ->push(['success' => false], 200);

        $this->assertTrue(Recaptcha::verify('token', '127.0.0.1'));
        $this->assertFalse(Recaptcha::verify('token', '127.0.0.1'));
        $this->assertFalse(Recaptcha::verify('token', '127.0.0.1'));

        $this->assertFalse(Recaptcha::verify(null));
        $this->assertFalse(Recaptcha::verify(''));
    }

    public function test_lead_is_accepted_when_recaptcha_passes(): void
    {
        $this->enableRecaptcha();
        Http::fake(['www.google.com/*' => Http::response(['success' => true, 'score' => 0.9], 200)]);

        $service = Service::factory()->create();

        $this->post('/leads', $this->leadPayload($service))->assertRedirect('/?submitted=1');

        $this->assertSame(1, Lead::count());
    }

    public function test_lead_is_rejected_when_recaptcha_fails(): void
    {
        $this->enableRecaptcha();
        Http::fake(['www.google.com/*' => Http::response(['success' => true, 'score' => 0.1], 200)]);

        $service = Service::factory()->create();

        $this->post('/leads', $this->leadPayload($service))->assertInvalid('g-recaptcha-response');

        $this->assertSame(0, Lead::count());
    }

    public function test_lead_is_rejected_when_google_unreachable(): void
    {
        $this->enableRecaptcha();
        Http::fake(['www.google.com/*' => Http::response(null, 500)]);

        $service = Service::factory()->create();

        $this->post('/leads', $this->leadPayload($service))->assertInvalid('g-recaptcha-response');

        $this->assertSame(0, Lead::count());
    }

    public function test_lead_skips_verification_when_disabled(): void
    {
        Http::preventStrayRequests();

        $service = Service::factory()->create();

        $payload = $this->leadPayload($service);
        unset($payload['g-recaptcha-response']);

        $this->post('/leads', $payload)->assertRedirect('/?submitted=1');

        $this->assertSame(1, Lead::count());
    }

    public function test_admin_login_blocked_when_recaptcha_fails(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, AdminSeeder::class]);
        $this->enableRecaptcha();
        Http::fake(['www.google.com/*' => Http::response(['success' => true, 'score' => 0.1], 200)]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@pta.com',
            'password' => 'password',
            'g-recaptcha-response' => 'test-token',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_admin_login_allowed_when_recaptcha_passes(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, AdminSeeder::class]);
        $this->enableRecaptcha();
        Http::fake(['www.google.com/*' => Http::response(['success' => true, 'score' => 0.9], 200)]);

        $this->post('/admin/login', [
            'email' => 'admin@pta.com',
            'password' => 'password',
            'g-recaptcha-response' => 'test-token',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs(Admin::where('email', 'admin@pta.com')->first(), 'admin');
    }

    public function test_admin_can_save_apis_settings_without_clearing_secret(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, AdminSeeder::class]);
        $admin = Admin::where('email', 'admin@pta.com')->first();
        $admin->assignRole('Super Admin');

        config(['apis.recaptcha_secret_key' => 'original-secret']);

        $this->actingAs($admin, 'admin')->put(route('admin.settings.update'), [
            'tab' => 'apis',
            'invoice_company_name' => 'Plant Tech Agro',
            'invoice_prefix' => 'PTA',
            'apis_recaptcha_enabled' => '1',
            'apis_recaptcha_site_key' => 'new-site-key',
            'apis_recaptcha_secret_key' => '',
            'apis_recaptcha_min_score' => 0.7,
        ])->assertRedirect(route('admin.settings.index', ['tab' => 'apis']));

        // Blank secret preserves the saved one; other keys persist.
        $this->assertSame('new-site-key', config('apis.recaptcha_site_key'));
        $this->assertSame(0.7, config('apis.recaptcha_min_score'));
        $this->assertDatabaseMissing('settings', ['key' => 'apis.recaptcha_secret_key', 'value' => json_encode('')]);
    }
}
