<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\Service;
use App\Models\Setting;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentDesignSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAdmin(): Admin
    {
        $this->seed([\Database\Seeders\RolesAndPermissionsSeeder::class, \Database\Seeders\AdminSeeder::class]);
        $admin = Admin::where('email', 'admin@pta.com')->first();
        $admin->assignRole('Super Admin');

        return $admin;
    }

    public function test_document_design_page_renders_with_tabs_and_service_selector(): void
    {
        $admin = $this->actingAdmin();

        $service = Service::create([
            'name' => 'High-Density Apple Orchard Setup',
            'slug' => 'high-density-apple-orchard-setup',
            'category' => 'orchard-development',
            'is_active' => true,
            'is_featured' => true,
            'creates_orchard_on_completion' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.document-settings.index'));

        $response->assertOk();
        $response->assertSee('Invoices & Quotations Designer', false);
        $response->assertSee('Prefixes & Numbering', false);
        $response->assertSee('Quotation Global Design');
        $response->assertSee('Invoice Global Design');
        $response->assertSee('Service-Linked Styling');
        $response->assertSee($service->name);
    }

    public function test_document_design_updates_prefixes_and_company_identity(): void
    {
        $admin = $this->actingAdmin();

        $response = $this->actingAs($admin, 'admin')->put(route('admin.document-settings.prefixes.update'), [
            'invoice_prefix' => 'INV-PTA',
            'invoice_due_days' => 20,
            'quotation_prefix' => 'EST-PTA',
            'quotation_validity_days' => 30,
            'company_name' => 'Plant Tech Agro Pvt Ltd',
            'company_tagline' => 'Orchard Specialists & Nursery',
            'company_slogan' => 'Building resilient farms together.',
            'company_address' => 'Hall Pulwama Kashmir',
            'company_phone' => '01933-299999',
            'company_email' => 'orchard@pta.com',
            'company_website' => 'www.planttechagro.com',
            'company_gst_no' => '01TESTGST9999Z',
        ]);

        $response->assertRedirect(route('admin.document-settings.index', ['tab' => 'prefixes']));
        $response->assertSessionHas('success');

        $this->assertEquals('INV-PTA', Setting::get('invoice.prefix'));
        $this->assertEquals(20, (int) Setting::get('invoice.due_days'));
        $this->assertEquals('EST-PTA', Setting::get('quotation.prefix'));
        $this->assertEquals(30, (int) Setting::get('quotation.validity_days'));
        $this->assertEquals('Plant Tech Agro Pvt Ltd', Setting::get('invoice.company_name'));
        $this->assertEquals('01TESTGST9999Z', Setting::get('invoice.gst_no'));
    }

    public function test_document_design_updates_global_quotation_settings(): void
    {
        $admin = $this->actingAdmin();

        $response = $this->actingAs($admin, 'admin')->put(route('admin.document-settings.quotations.update'), [
            'quotation_design_style' => 'modern_clean',
            'quotation_accent_color' => '#1e3a8a',
            'quotation_document_title' => 'PROJECT COST ESTIMATE',
            'quotation_document_subtitle' => 'ENGINEERING & MATERIAL QUOTATION',
            'quotation_show_logo' => '1',
            'quotation_show_address' => '1',
            'quotation_show_specs' => '1',
            'quotation_show_package' => '1',
            'quotation_show_schedule' => '1',
            'quotation_show_bank_details' => '1',
            'quotation_show_terms' => '1',
            'quotation_show_signatory' => '1',
            'quotation_terms' => 'Custom global quotation terms and conditions.',
            'quotation_additional_notes' => 'Custom global additional notes.',
        ]);

        $response->assertRedirect(route('admin.document-settings.index', ['tab' => 'quotations']));
        $response->assertSessionHas('success');

        $this->assertEquals('modern_clean', Setting::get('quotation.design_style'));
        $this->assertEquals('#1e3a8a', Setting::get('quotation.accent_color'));
        $this->assertEquals('PROJECT COST ESTIMATE', Setting::get('quotation.document_title'));
    }

    public function test_document_design_updates_global_invoice_settings(): void
    {
        $admin = $this->actingAdmin();

        $response = $this->actingAs($admin, 'admin')->put(route('admin.document-settings.invoices.update'), [
            'invoice_design_style' => 'detailed_field',
            'invoice_accent_color' => '#0f766e',
            'invoice_document_title' => 'OFFICIAL BILL OF SUPPLY',
            'invoice_show_logo' => '1',
            'invoice_show_gst' => '1',
            'invoice_show_bank_details' => '1',
            'invoice_show_terms' => '1',
            'invoice_show_signatory' => '1',
            'invoice_terms' => 'Standard payment terms apply.',
            'invoice_notes' => 'Thank you for your business.',
        ]);

        $response->assertRedirect(route('admin.document-settings.index', ['tab' => 'invoices']));
        $response->assertSessionHas('success');

        $this->assertEquals('detailed_field', Setting::get('invoice.design_style'));
        $this->assertEquals('#0f766e', Setting::get('invoice.accent_color'));
        $this->assertEquals('OFFICIAL BILL OF SUPPLY', Setting::get('invoice.document_title'));
    }

    public function test_document_design_updates_service_specific_styling_and_milestones(): void
    {
        $admin = $this->actingAdmin();

        $service = Service::create([
            'name' => 'Hail Protection Trellis Installation',
            'slug' => 'hail-protection-trellis-installation',
            'category' => 'hail-protection',
            'is_active' => true,
        ]);

        $milestones = [
            ['percent' => 35, 'stage' => 'Advance on signing agreement'],
            ['percent' => 45, 'stage' => 'Upon material delivery at orchard site'],
            ['percent' => 20, 'stage' => 'Post installation audit'],
        ];

        $response = $this->actingAs($admin, 'admin')->put(route('admin.document-settings.service.update', $service), [
            'quotation_type' => 'installation',
            'quotation_title' => 'TRELLIS & HAIL NET ESTIMATE',
            'quotation_subtitle' => 'COMPLETE CANOPY INFRASTRUCTURE',
            'accent_color' => '#0369a1',
            'package_title' => 'Anti-Hail Netting Complete Kit',
            'package_poles' => 24,
            'package_anchors' => 8,
            'package_plants' => 0,
            'variety_name' => 'High Tensile Wire Grid',
            'rootstock' => 'Galvanized Poles',
            'plants_per_kanal' => 'N/A',
            'payment_schedule' => $milestones,
            'additional_notes' => 'All cable clamps and tensioners included.',
            'quotation_terms' => 'Site clearance by owner prior to erection.',
            'invoice_title' => 'TAX INVOICE - TRELLIS WORK',
            'invoice_terms' => 'Net 10 business days.',
        ]);

        $response->assertRedirect(route('admin.document-settings.index', ['tab' => 'services', 'service_id' => $service->id]));
        $response->assertSessionHas('success');

        $service->refresh();
        $this->assertNotEmpty($service->quotation_settings);
        $this->assertEquals('installation', $service->quotation_settings['quotation_type']);
        $this->assertEquals('TRELLIS & HAIL NET ESTIMATE', $service->quotation_settings['document_title']);
        $this->assertEquals('#0369a1', $service->quotation_settings['accent_color']);
        $this->assertEquals(24, $service->quotation_settings['package_poles']);

        // Verify getQuotationDefaults merges correctly
        $defaults = $service->getQuotationDefaults();
        $this->assertEquals('TRELLIS & HAIL NET ESTIMATE', $defaults['document_title']);
        $this->assertEquals(35, (int) $defaults['payment_schedule'][0]['percent']);

        // Verify getInvoiceDefaults merges correctly
        $invoiceDefaults = $service->getInvoiceDefaults();
        $this->assertEquals('TAX INVOICE - TRELLIS WORK', $invoiceDefaults['document_title']);
        $this->assertEquals('#0369a1', $invoiceDefaults['accent_color']);
    }

    public function test_document_design_resets_service_specific_styling(): void
    {
        $admin = $this->actingAdmin();

        $service = Service::create([
            'name' => 'Soil Nutrition Testing',
            'slug' => 'soil-nutrition-testing',
            'category' => 'soil-health-management',
            'is_active' => true,
            'quotation_settings' => [
                'document_title' => 'CUSTOM SOIL LAB INVOICE',
                'accent_color' => '#b45309',
            ],
            'invoice_settings' => [
                'document_title' => 'SOIL LAB BILL',
            ],
        ]);

        $this->assertNotNull($service->quotation_settings);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.document-settings.service.reset', $service));

        $response->assertRedirect(route('admin.document-settings.index', ['tab' => 'services', 'service_id' => $service->id]));
        $response->assertSessionHas('success');

        $service->refresh();
        $this->assertNull($service->quotation_settings);
        $this->assertNull($service->invoice_settings);
    }

    public function test_quotation_print_uses_service_accent_color_and_custom_title(): void
    {
        $admin = $this->actingAdmin();

        $service = Service::create([
            'name' => 'Super Dense Apple Block',
            'slug' => 'super-dense-apple-block',
            'category' => 'orchard-development',
            'is_active' => true,
            'quotation_settings' => [
                'document_title' => 'EXCLUSIVE ORCHARD PROFORMA',
                'document_subtitle' => 'PREMIER HORTICULTURE CONTRACT',
                'accent_color' => '#7c3aed',
            ],
        ]);

        $customer = Customer::create([
            'name' => 'Peerzada Farooq',
            'phone' => '9876543210',
            'password' => 'secret123',
            'status' => 'active',
        ]);

        $quotation = Quotation::create([
            'number' => 'QT-2026-TEST',
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'service_name' => $service->name,
            'land_area' => 5,
            'status' => 'draft',
            'date' => now(),
            'valid_until' => now()->addDays(15),
            'subtotal' => 100000,
            'grand_total' => 100000,
        ]);

        $this->assertEquals('#7c3aed', $quotation->getAccentColor());
        $this->assertEquals('EXCLUSIVE ORCHARD PROFORMA', $quotation->getDocumentTitle());

        $response = $this->actingAs($admin, 'admin')->get(route('admin.quotations.print', $quotation));
        $response->assertOk();
        $response->assertSee('--brand-accent: #7c3aed', false);
        $response->assertSee('EXCLUSIVE ORCHARD PROFORMA');
        $response->assertSee('PREMIER HORTICULTURE CONTRACT');
    }

    public function test_invoice_print_uses_service_accent_color_and_custom_title(): void
    {
        $admin = $this->actingAdmin();

        $service = Service::create([
            'name' => 'Ground Water Geo Survey',
            'slug' => 'ground-water-geo-survey',
            'category' => 'ground-water-detection',
            'is_active' => true,
            'invoice_settings' => [
                'document_title' => 'HYDRO-GEOLOGICAL TAX INVOICE',
                'accent_color' => '#0284c7',
            ],
        ]);

        $customer = Customer::create([
            'name' => 'Tariq Ahmad',
            'phone' => '9876543211',
            'password' => 'secret123',
            'status' => 'active',
        ]);

        $workOrder = WorkOrder::create([
            'number' => 'WO-GEO-001',
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'service_id' => $service->id,
            'service_name' => $service->name,
            'status' => 'completed',
        ]);

        $invoice = Invoice::create([
            'number' => 'INV-2026-TEST',
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'work_order_id' => $workOrder->id,
            'status' => 'paid',
            'invoice_date' => now(),
            'subtotal' => 50000,
            'discount_total' => 0,
            'tax_total' => 0,
            'gst_total' => 0,
            'grand_total' => 50000,
            'amount_paid' => 50000,
        ]);

        $this->assertEquals('#0284c7', $invoice->getAccentColor());
        $this->assertEquals('HYDRO-GEOLOGICAL TAX INVOICE', $invoice->getDocumentTitle());

        $response = $this->actingAs($admin, 'admin')->get(route('admin.invoices.print', $invoice));
        $response->assertOk();
        $response->assertSee('--brand-accent: #0284c7', false);
        $response->assertSee('HYDRO-GEOLOGICAL TAX INVOICE');
    }
}
