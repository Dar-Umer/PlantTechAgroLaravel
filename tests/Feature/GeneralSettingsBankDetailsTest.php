<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Quotation;
use App\Models\PosSale;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\ShopSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeneralSettingsBankDetailsTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAdmin(): Admin
    {
        $this->seed([\Database\Seeders\RolesAndPermissionsSeeder::class, \Database\Seeders\AdminSeeder::class]);
        $admin = Admin::where('email', 'admin@pta.com')->first();
        $admin->assignRole('Super Admin');

        return $admin;
    }

    public function test_admin_settings_general_tab_renders_contact_and_bank_account_details(): void
    {
        $admin = $this->actingAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.settings.index', ['tab' => 'general']));

        $response->assertOk();
        $response->assertSee('Store & Contact Information', false);
        $response->assertSee('Bank Account Details');
        $response->assertSee('name="site_name"', false);
        $response->assertSee('name="site_email"', false);
        $response->assertSee('name="site_phone"', false);
        $response->assertSee('name="site_address"', false);
        $response->assertSee('name="support_hours"', false);
        $response->assertSee('name="bank_account_name"', false);
        $response->assertSee('name="bank_name"', false);
        $response->assertSee('name="bank_account_no"', false);
        $response->assertSee('name="bank_branch"', false);
        $response->assertSee('name="bank_ifsc"', false);
        $response->assertSee('Plant Tech Agro');
        $response->assertSee('0942 0100 0000 0275');
        $response->assertSee('JAKA0MIGRNT');
        $response->assertSee('Migrant Colony Hall Pulwama');
    }

    public function test_admin_settings_renders_all_tabs_content_without_nesting_or_blank_screens(): void
    {
        $admin = $this->actingAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.settings.index'));
        $response->assertOk();

        // General
        $response->assertSee('Store & Contact Information', false);
        $response->assertSee('Bank Account Details');

        // Appearance
        $response->assertSee('Brand Color');
        $response->assertSee('Admin Sidebar');
        $response->assertSee('name="theme_palette"', false);

        // Invoice
        $response->assertSee('Company Details');
        $response->assertSee('name="invoice_company_name"', false);
        $response->assertSee('Invoice Branding & Numbering', false);

        // SEO
        $response->assertSee('Search Engine Optimization');
        $response->assertSee('name="seo_meta_title"', false);

        // Weather
        $response->assertSee('Weather Service');
        $response->assertSee('name="weather_default_district"', false);

        // APIs
        $response->assertSee('API Integrations');
        $response->assertSee('name="apis_recaptcha_enabled"', false);

        // Media / Photo Compression
        $response->assertSee('Automatic Photo Compression');
        $response->assertSee('Live Compression Preview Tool');

        // SMTP
        $response->assertSee('Mail Driver');
        $response->assertSee('name="smtp_host"', false);
    }

    public function test_admin_can_update_address_and_bank_details(): void
    {
        $admin = $this->actingAdmin();

        $updateData = [
            'site_name' => 'Plant Tech Agro Pvt Ltd',
            'site_email' => 'contact@planttechagro.com',
            'site_phone' => '0194-222-3344',
            'site_address' => '78 Green View Boulevard, Rajbagh, Srinagar, J&K - 190008',
            'support_hours' => 'Mon – Fri, 9 AM – 5 PM',
            'footer_tagline' => 'Pioneering Modern Horticulture in Kashmir.',
            'bank_account_name' => 'Plant Tech Agro Pvt Ltd',
            'bank_name' => 'Jammu & Kashmir Bank Ltd',
            'bank_account_no' => '0942 0100 0000 9999',
            'bank_branch' => 'Residency Road Srinagar',
            'bank_ifsc' => 'JAKA0RESIRO',
        ];

        $response = $this->actingAs($admin, 'admin')->put(route('admin.settings.update'), $updateData);

        $response->assertRedirect(route('admin.settings.index', ['tab' => 'general']));

        // Verify config updated
        $this->assertEquals('Plant Tech Agro Pvt Ltd', config('shop.site_name'));
        $this->assertEquals('78 Green View Boulevard, Rajbagh, Srinagar, J&K - 190008', config('shop.site_address'));
        $this->assertEquals('0942 0100 0000 9999', config('shop.bank_account_no'));
        $this->assertEquals('JAKA0RESIRO', config('shop.bank_ifsc'));
        $this->assertEquals('Jammu & Kashmir Bank Ltd', config('shop.bank_name'));
        $this->assertEquals('Residency Road Srinagar', config('shop.bank_branch'));
    }

    public function test_landing_page_footer_displays_system_address_and_omits_bank_details(): void
    {
        app(ShopSettingsService::class)->set([
            'site_address' => '56 Murad House, Pine Lane-8, Kurso Rajbagh, Srinagar-190008, Jammu & Kashmir',
            'site_phone' => '0194-796-1490',
            'site_email' => 'info@plantechagro.com',
            'bank_account_name' => 'Plant Tech Agro',
            'bank_name' => 'J&K Bank',
            'bank_account_no' => '0942 0100 0000 0275',
            'bank_branch' => 'Migrant Colony Hall Pulwama',
            'bank_ifsc' => 'JAKA0MIGRNT',
        ], 'shop');

        $response = $this->get(route('landing'));

        $response->assertOk();
        $response->assertSee('56 Murad House, Pine Lane-8, Kurso Rajbagh, Srinagar-190008, Jammu &amp; Kashmir', false);
        $response->assertSee('0194-796-1490');
        $response->assertSee('info@plantechagro.com');
        // Bank details must NOT be exposed on public frontpage footer
        $response->assertDontSee('0942 0100 0000 0275');
        $response->assertDontSee('JAKA0MIGRNT');
        $response->assertDontSee('Migrant Colony Hall Pulwama');
    }

    public function test_admin_frontend_footer_update_preserves_contact_settings(): void
    {
        $admin = $this->actingAdmin();

        app(ShopSettingsService::class)->set([
            'site_address' => '56 Murad House, Pine Lane-8, Kurso Rajbagh, Srinagar-190008, Jammu & Kashmir',
            'site_phone' => '0194-796-1490',
            'site_email' => 'info@plantechagro.com',
        ], 'shop');

        $response = $this->actingAs($admin, 'admin')->put(route('admin.frontend.footer.update'), [
            'social_facebook' => 'https://facebook.com/planttechagro',
            'social_instagram' => 'https://instagram.com/planttechagro',
        ]);

        $response->assertRedirect();

        // Contact info in shop config should remain intact
        $this->assertEquals('56 Murad House, Pine Lane-8, Kurso Rajbagh, Srinagar-190008, Jammu & Kashmir', config('shop.site_address'));
        $this->assertEquals('0194-796-1490', config('shop.site_phone'));
        $this->assertEquals('info@plantechagro.com', config('shop.site_email'));
    }

    public function test_quotation_and_invoice_views_render_updated_bank_details(): void
    {
        $admin = $this->actingAdmin();

        app(ShopSettingsService::class)->set([
            'bank_account_name' => 'Plant Tech Agro',
            'bank_name' => 'J&K Bank',
            'bank_account_no' => '0942 0100 0000 0275',
            'bank_branch' => 'Migrant Colony Hall Pulwama',
            'bank_ifsc' => 'JAKA0MIGRNT',
            'site_address' => '56 Murad House, Pine Lane-8, Kurso Rajbagh, Srinagar-190008, Jammu & Kashmir',
        ], 'shop');

        $quotation = Quotation::create([
            'number' => 'QT/2026-27/0200',
            'customer_name' => 'Ghulam Hassan',
            'customer_phone' => '9419112233',
            'date' => '2026-09-27',
            'status' => 'approved',
            'subtotal' => 100000,
            'grand_total' => 100000,
            'created_by' => $admin->id,
        ]);

        $quotationPrint = $this->actingAs($admin, 'admin')->get(route('admin.quotations.print', $quotation));
        $quotationPrint->assertOk();
        $quotationPrint->assertSee('0942 0100 0000 0275');
        $quotationPrint->assertSee('JAKA0MIGRNT');
        $quotationPrint->assertSee('Migrant Colony Hall Pulwama');

        // Invoice print
        $invoice = Invoice::create([
            'number' => 'INV-2026-0001',
            'customer_name' => 'Ghulam Hassan',
            'invoice_date' => '2026-09-27',
            'status' => 'unpaid',
            'subtotal' => 100000,
            'grand_total' => 100000,
        ]);

        $invoicePrint = $this->actingAs($admin, 'admin')->get(route('admin.invoices.print', $invoice));
        $invoicePrint->assertOk();
        $invoicePrint->assertSee('0942 0100 0000 0275');
        $invoicePrint->assertSee('JAKA0MIGRNT');
        $invoicePrint->assertSee('Bank Account Details');
    }
}
