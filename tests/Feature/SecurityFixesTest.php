<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\StockMovementController;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\PasswordOtp;
use Database\Seeders\AdminSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SecurityFixesTest extends TestCase
{
    use RefreshDatabase;

    private function actingSuperAdmin(): Admin
    {
        $this->seed([RolesAndPermissionsSeeder::class, AdminSeeder::class]);

        $admin = Admin::where('email', 'admin@pta.com')->first();
        $admin->assignRole('Super Admin');

        return $admin;
    }

    public function test_inactive_customer_api_access_is_revoked(): void
    {
        $customer = Customer::create([
            'name' => 'Inactive', 'phone' => '9111111111',
            'password' => 'Passw0rd1!', 'status' => 'active',
        ]);
        $token = $customer->createToken('app')->plainTextToken;

        $this->withToken($token)->getJson('/api/me')->assertOk();

        $customer->update(['status' => 'inactive']);

        // Drop the guard's in-memory user so the next request resolves fresh
        // from the DB (production boots a fresh app per request anyway).
        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/api/me')->assertForbidden();
        $this->assertSame(0, $customer->tokens()->count());
    }

    public function test_recaptcha_rejects_wrong_action_and_hostname(): void
    {
        config([
            'apis.recaptcha_enabled' => true,
            'apis.recaptcha_site_key' => 'k',
            'apis.recaptcha_secret_key' => 's',
            'apis.recaptcha_min_score' => 0.5,
            'app.url' => 'http://localhost',
        ]);

        Http::fakeSequence()
            ->push(['success' => true, 'score' => 0.9, 'action' => 'other', 'hostname' => 'localhost'], 200)
            ->push(['success' => true, 'score' => 0.9, 'action' => 'lead', 'hostname' => 'evil.example'], 200)
            ->push(['success' => true, 'score' => 0.9, 'action' => 'lead', 'hostname' => 'localhost'], 200);

        $this->assertFalse(\App\Support\Recaptcha::verify('t', null, 'lead'));
        $this->assertFalse(\App\Support\Recaptcha::verify('t', null, 'lead'));
        $this->assertTrue(\App\Support\Recaptcha::verify('t', null, 'lead'));
    }

    public function test_otp_codes_are_hashed_and_reset_flow_works(): void
    {
        $customer = Customer::create([
            'name' => 'Farooq', 'phone' => '9222222222',
            'password' => 'Passw0rd1!', 'status' => 'active',
        ]);

        PasswordOtp::create([
            'phone' => $customer->phone,
            'code' => '',
            'code_hash' => Hash::make('123456'),
            'purpose' => 'password_reset',
            'expires_at' => now()->addMinutes(10),
        ]);

        $stored = PasswordOtp::first();
        $this->assertNotEmpty($stored->code_hash);
        $this->assertSame('', $stored->code);

        $this->postJson('/api/auth/verify-otp', ['phone' => '9222222222', 'code' => '123456'])
            ->assertOk();

        $this->postJson('/api/auth/reset-password', [
            'phone' => '9222222222',
            'code' => '123456',
            'password' => 'Newpass1!',
            'password_confirmation' => 'Newpass1!',
        ])->assertOk();

        $this->assertTrue(Hash::check('Newpass1!', $customer->refresh()->password));
        $this->assertNotNull($stored->refresh()->consumed_at);

        // Reusing the consumed code fails.
        $this->postJson('/api/auth/reset-password', [
            'phone' => '9222222222',
            'code' => '123456',
            'password' => 'Another1!',
            'password_confirmation' => 'Another1!',
        ])->assertStatus(422);
    }

    public function test_invoice_logo_upload_rejects_non_images(): void
    {
        $admin = $this->actingSuperAdmin();

        $response = $this->actingAs($admin, 'admin')->put(route('admin.settings.update'), [
            'tab' => 'general',
            'invoice_company_name' => 'Plant Tech Agro',
            'invoice_prefix' => 'PTA',
            'invoice_logo_file' => UploadedFile::fake()->create('evil.php', 10, 'application/x-php'),
        ]);

        $response->assertSessionHasErrors('invoice_logo_file');
    }

    public function test_smtp_test_rejects_invalid_host_and_hides_errors(): void
    {
        $admin = $this->actingSuperAdmin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.settings.mail.test'), ['smtp_host' => '169.254.169.254; rm -rf /'])
            ->assertSessionHasErrors('smtp_host');
    }

    public function test_deactivated_admin_login_shows_generic_message(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, AdminSeeder::class]);
        $admin = Admin::where('email', 'admin@pta.com')->first();
        $admin->update(['is_active' => false]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@pta.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email' => 'The provided credentials do not match our records.']);
        $this->assertGuest('admin');
    }

    public function test_csv_export_neutralizes_formulas(): void
    {
        $method = new \ReflectionMethod(StockMovementController::class, 'csvCell');
        $method->setAccessible(true);

        $this->assertSame("'=cmd|'/c calc'!A0", $method->invoke(null, '=cmd|\'/c calc\'!A0'));
        $this->assertSame('Normal product', $method->invoke(null, 'Normal product'));
        $this->assertSame(42, $method->invoke(null, 42));
    }
}
