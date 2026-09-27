<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\Service;
use Database\Seeders\AdminSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProformaInvoiceQuotationTest extends TestCase
{
    use RefreshDatabase;

    private function actingAdmin(): Admin
    {
        $this->seed([RolesAndPermissionsSeeder::class, AdminSeeder::class]);
        $admin = Admin::where('email', 'admin@pta.com')->first();
        $admin->assignRole('Super Admin');
        return $admin;
    }

    public function test_can_create_proforma_quotation_with_all_reference_fields(): void
    {
        $admin = $this->actingAdmin();
        $service = Service::create(['name' => 'Book an Orchard', 'slug' => 'book-an-orchard', 'is_active' => true]);

        $payload = [
            'customer_name' => 'Tanveer Bhai',
            'customer_phone' => '9796205123',
            'customer_email' => 'tanveerulhak@gmail.com',
            'customer_area' => '5 Kanal',
            'customer_address' => 'Pattan, Kashmir',
            'service_id' => $service->id,
            'scope_title' => 'Book an Orchard',
            'scope_subtitle' => 'Complete Orchard Development Solution',
            'variety_name' => 'Devil Gala',
            'variety_specification' => 'Devil Gala',
            'rootstock' => 'M9 / T337 (High Density)',
            'plants_per_kanal' => '150 (Standard)',
            'package_title' => 'Per Kanal Standard Package',
            'package_poles' => 19,
            'package_anchors' => 6,
            'package_plants' => 150,
            'payment_schedule' => [
                ['percent' => 30, 'stage' => 'Advance at the time of booking', 'amount' => 277500],
                ['percent' => 50, 'stage' => 'Before trellis installation', 'amount' => 462500],
                ['percent' => 20, 'stage' => 'Before plantation', 'amount' => 185000],
            ],
            'additional_notes' => "Extra anchors, if required, will be charged separately at ₹ 2,500 per anchor, including all accessories.\nExtra plants, if required, will be charged separately at ₹ 1,230 per plant, including all accessories.",
            'bank_name' => 'J&K Bank',
            'bank_account_name' => 'Plant Tech Agro',
            'bank_account_no' => '0942 0100 0000 0275',
            'bank_branch' => 'Migrant Colony Hall Pulwama',
            'bank_ifsc' => 'JAKA0MIGRNT',
            'date' => '2026-09-24',
            'valid_until' => '2026-10-05',
            'status' => 'sent',
            'items' => [
                [
                    'name' => 'Booking orchard',
                    'unit' => 'Kanal',
                    'qty' => 5,
                    'rate' => 185000,
                    'discount' => 0,
                    'gst_rate' => 0,
                ],
            ],
        ];

        $response = $this->actingAs($admin, 'admin')->post('/admin/quotations', $payload);
        $response->assertRedirect();

        $quotation = Quotation::where('customer_name', 'Tanveer Bhai')->first();
        $this->assertNotNull($quotation);
        $this->assertEquals(925000.00, (float) $quotation->grand_total);
        $this->assertEquals('Devil Gala', $quotation->variety_name);
        $this->assertEquals('M9 / T337 (High Density)', $quotation->rootstock);
        $this->assertEquals(19, $quotation->package_poles);
        $this->assertEquals(6, $quotation->package_anchors);
        $this->assertEquals(150, $quotation->package_plants);
        $this->assertEquals('0942 0100 0000 0275', $quotation->bank_account_no);
        $this->assertEquals('JAKA0MIGRNT', $quotation->bank_ifsc);

        // Verify PDF generation matches the document
        $pdfResponse = $this->actingAs($admin, 'admin')->get(route('admin.quotations.pdf', $quotation));
        $pdfResponse->assertOk();
        $this->assertStringContainsString('application/pdf', $pdfResponse->headers->get('content-type'));

        // Verify HTML print view contains all expected PDF text
        $printResponse = $this->actingAs($admin, 'admin')->get(route('admin.quotations.print', $quotation));
        $printResponse->assertOk();
        $printResponse->assertSee('PROFORMA INVOICE', false);
        $printResponse->assertSee('PRICE ESTIMATE &amp; QUOTATION', false);
        $printResponse->assertSee('Tanveer Bhai');
        $printResponse->assertSee('Devil Gala');
        $printResponse->assertSee('M9 / T337 (High Density)');
        $printResponse->assertSee('Per Kanal Standard Package');
        $printResponse->assertSee('Advance at the time of booking');
        $printResponse->assertSee('Before trellis installation');
        $printResponse->assertSee('Before plantation');
        $printResponse->assertSee('Migrant Colony Hall Pulwama');
        $printResponse->assertSee('0942 0100 0000 0275');
        $printResponse->assertSee('JAKA0MIGRNT');
        $printResponse->assertSee('From Planning to Plantation We Build Better Orchards');
        $printResponse->assertSee('Your Orchard.');
        $printResponse->assertSee('Our Commitment.');
    }

    public function test_can_update_proforma_quotation_options(): void
    {
        $admin = $this->actingAdmin();

        $quotation = Quotation::create([
            'number' => 'QT/2026-27/0009',
            'customer_name' => 'Initial Farmer',
            'customer_phone' => '9419111222',
            'date' => '2026-09-20',
            'status' => 'draft',
            'subtotal' => 100000,
            'grand_total' => 100000,
            'created_by' => $admin->id,
        ]);

        $quotation->items()->create([
            'name' => 'Soil Digging',
            'unit' => 'Kanal',
            'qty' => 2,
            'rate' => 50000,
            'total' => 100000,
        ]);

        $updatePayload = [
            'customer_name' => 'Updated Farmer Name',
            'customer_phone' => '9419111222',
            'variety_name' => 'Gala Schnico Red',
            'variety_specification' => 'Gala Schnico Red',
            'rootstock' => 'M9 / T337',
            'plants_per_kanal' => '160',
            'package_title' => 'High Density Package',
            'package_poles' => 22,
            'package_anchors' => 8,
            'package_plants' => 160,
            'date' => '2026-09-25',
            'status' => 'sent',
            'items' => [
                [
                    'name' => 'Complete Orchard Installation',
                    'unit' => 'Kanal',
                    'qty' => 2,
                    'rate' => 150000,
                    'discount' => 0,
                    'gst_rate' => 0,
                ],
            ],
        ];

        $response = $this->actingAs($admin, 'admin')->put("/admin/quotations/{$quotation->id}", $updatePayload);
        $response->assertRedirect();

        $quotation->refresh();
        $this->assertEquals('Updated Farmer Name', $quotation->customer_name);
        $this->assertEquals('Gala Schnico Red', $quotation->variety_name);
        $this->assertEquals(22, $quotation->package_poles);
        $this->assertEquals(300000.00, (float) $quotation->grand_total);
    }

    public function test_admin_quotation_create_screen_renders_with_pta_ui_elements(): void
    {
        $admin = $this->actingAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.quotations.create'));
        $response->assertOk();
        $response->assertSee('Create Quotation / Proforma');
        $response->assertSee('Quotation Prepared For');
        $response->assertSee('Project / Service Scope');
        $response->assertSee('Booking / Variety Details');
        $response->assertSee('Deliverables &amp; Cost Breakdown', false);
        $response->assertSee('Payment Schedule');
        $response->assertSee('Project Package / Inclusions');
        $response->assertSee('Additional Notes &amp; Scope Remarks', false);
        $response->assertSee('Bank Account Details');
        $response->assertSee('Terms &amp; Conditions', false);
        $response->assertSee('Save &amp; Generate Quotation', false);
    }

    public function test_admin_quotation_edit_screen_renders_with_pta_ui_elements(): void
    {
        $admin = $this->actingAdmin();

        $quotation = Quotation::create([
            'number' => 'QT/2026-27/0010',
            'customer_name' => 'Tanveer Bhai',
            'customer_phone' => '9796205123',
            'date' => '2026-09-24',
            'status' => 'sent',
            'subtotal' => 925000,
            'grand_total' => 925000,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.quotations.edit', $quotation));
        $response->assertOk();
        $response->assertSee('Edit Quotation — ' . $quotation->number);
        $response->assertSee('Preview Document');
        $response->assertSee('Update Quotation');
    }

    public function test_quotation_create_screen_populates_actual_customer_lead_selections(): void
    {
        $admin = $this->actingAdmin();

        $service = Service::create([
            'name' => 'Book High-Density Orchard Setup',
            'slug' => 'book-high-density-orchard-setup',
            'description' => 'Complete orchard setup and plant delivery',
            'is_active' => true,
        ]);

        $lead = \App\Models\Lead::create([
            'name' => 'Farooq Abdullah',
            'phone' => '9419112233',
            'service_id' => $service->id,
            'source' => 'customer_app',
            'status' => 'new',
            'notes' => 'Looking to establish on vacant terraced land with irrigation source nearby.',
            'custom_fields' => [
                'email' => 'farooq@example.com',
                'proposed_area_kanals' => 12.5,
                'proposed_location' => 'Shopian, Pinjora',
                'preferred_variety' => 'Gala Schniga / M9 Rootstock',
            ],
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.quotations.create', ['lead_id' => $lead->id]));
        $response->assertOk();

        // Must see customer's actual input
        $response->assertSee('Farooq Abdullah');
        $response->assertSee('9419112233');
        $response->assertSee('farooq@example.com');
        $response->assertSee('12.5 Kanal');
        $response->assertSee('Shopian, Pinjora');
        $response->assertSee('Book High-Density Orchard Setup');
        $response->assertSee('Gala Schniga');
        $response->assertSee('M9 Rootstock');
        $response->assertSee('Looking to establish on vacant terraced land with irrigation source nearby.');

        // Must NOT see hardcoded dummy test data from reference PDF
        $response->assertDontSee('Tanveer Bhai');
        $response->assertDontSee('9796205123');
        $response->assertDontSee('tanveerulhak@gmail.com');
        $response->assertDontSee('Pattan, Kashmir');
    }

    public function test_quotation_create_without_lead_has_clean_empty_customer_fields(): void
    {
        $admin = $this->actingAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.quotations.create'));
        $response->assertOk();

        // Must NOT contain dummy values
        $response->assertDontSee('Tanveer Bhai');
        $response->assertDontSee('9796205123');
        $response->assertDontSee('tanveerulhak@gmail.com');
        $response->assertDontSee('Pattan, Kashmir');
    }

    public function test_service_reference_decides_quotation_style_and_fields(): void
    {
        $admin = $this->actingAdmin();

        $soilTestService = Service::create([
            'name' => 'Book a Soil Test',
            'slug' => 'book-soil-test',
            'category' => 'soil-health-management',
            'description' => 'Comprehensive soil testing and nutrient profiling',
            'creates_orchard_on_completion' => false,
            'is_active' => true,
        ]);

        $this->assertEquals('technical', $soilTestService->getQuotationType());
        $this->assertFalse($soilTestService->getQuotationDefaults()['show_variety']);
        $this->assertFalse($soilTestService->getQuotationDefaults()['show_package']);

        // Create a quotation for soil test
        $quotation = Quotation::create([
            'number' => 'QT/2026-27/0077',
            'service_id' => $soilTestService->id,
            'customer_name' => 'Bashir Ahmad',
            'customer_phone' => '9419001122',
            'scope_title' => 'Book a Soil Test',
            'scope_subtitle' => 'Comprehensive soil testing and nutrient profiling',
            'date' => '2026-09-27',
            'status' => 'sent',
            'subtotal' => 15000,
            'grand_total' => 15000,
            'created_by' => $admin->id,
        ]);

        $this->assertEquals('technical', $quotation->getQuotationType());
        $this->assertFalse($quotation->hasVarietyDetails());
        $this->assertFalse($quotation->hasPackageInclusions());
        $this->assertEquals('SERVICE ESTIMATE', $quotation->getDocumentTitle());
        $this->assertEquals('TESTING & WORK QUOTATION', $quotation->getDocumentSubtitle());

        // Verify print view for technical service
        $printResponse = $this->actingAs($admin, 'admin')->get(route('admin.quotations.print', $quotation));
        $printResponse->assertOk();
        $printResponse->assertSee('SERVICE ESTIMATE');
        $printResponse->assertSee('TESTING &amp; WORK QUOTATION', false);
        $printResponse->assertDontSee('Booking / Variety Details');
        $printResponse->assertDontSee('Project Package / Inclusions');

        // Now test plant booking service
        $plantService = Service::create([
            'name' => 'Book Plants',
            'slug' => 'book-plants',
            'category' => 'orchard-development',
            'is_active' => true,
        ]);

        $this->assertEquals('plants', $plantService->getQuotationType());
        $this->assertTrue($plantService->getQuotationDefaults()['show_variety']);
        $this->assertFalse($plantService->getQuotationDefaults()['show_package']);

        $plantQuotation = Quotation::create([
            'number' => 'QT/2026-27/0078',
            'service_id' => $plantService->id,
            'customer_name' => 'Tariq Mir',
            'customer_phone' => '9419334455',
            'variety_name' => 'Gala Schnico Red',
            'rootstock' => 'M9 / T337',
            'date' => '2026-09-27',
            'status' => 'sent',
            'subtotal' => 50000,
            'grand_total' => 50000,
            'created_by' => $admin->id,
        ]);

        $this->assertTrue($plantQuotation->hasVarietyDetails());
        $this->assertFalse($plantQuotation->hasPackageInclusions());
        $this->assertEquals('PLANT NURSERY BOOKING', $plantQuotation->getDocumentTitle());
        $this->assertEquals('PRICE ESTIMATE & QUOTATION', $plantQuotation->getDocumentSubtitle());

        $plantPrintResponse = $this->actingAs($admin, 'admin')->get(route('admin.quotations.print', $plantQuotation));
        $plantPrintResponse->assertOk();
        $plantPrintResponse->assertSee('PLANT NURSERY BOOKING');
        $plantPrintResponse->assertSee('PRICE ESTIMATE &amp; QUOTATION', false);
        $plantPrintResponse->assertSee('Gala Schnico Red');
        $plantPrintResponse->assertDontSee('Project Package / Inclusions');
    }

    public function test_quotation_header_renders_system_logo_and_system_address(): void
    {
        $admin = $this->actingAdmin();

        config([
            'shop.logo_url' => '/storage/logos/4D1yGvc6bq1BcGyGbuJ4HZchnZGkJixLl2FNMCMg.jpg',
            'invoice.address' => '56 Murad House, Pine Lane-8, Kurso Rajbagh, Srinagar-190008, Jammu & Kashmir',
            'invoice.phone' => '0194-796-1490',
            'invoice.email' => 'info@plantechagro.com',
            'quotation.website' => 'www.planttechagro.com',
        ]);

        $quotation = Quotation::create([
            'number' => 'QT/2026-27/0099',
            'customer_name' => 'Bashir Ahmad',
            'customer_phone' => '9419001122',
            'date' => '2026-09-27',
            'status' => 'approved',
            'subtotal' => 200000,
            'grand_total' => 200000,
            'created_by' => $admin->id,
        ]);

        // 1. Verify model helper resolutions
        $this->assertStringContainsString('4D1yGvc6bq1BcGyGbuJ4HZchnZGkJixLl2FNMCMg.jpg', (string) $quotation->getCompanyLogoUrl());
        $this->assertEquals('56 Murad House, Pine Lane-8, Kurso Rajbagh, Srinagar-190008, Jammu & Kashmir', $quotation->getCompanyAddress());
        $this->assertEquals('0194-796-1490', $quotation->getCompanyPhone());
        $this->assertEquals('info@plantechagro.com', $quotation->getCompanyEmail());

        // 2. Verify HTML print view contains logo and system address
        $printResponse = $this->actingAs($admin, 'admin')->get(route('admin.quotations.print', $quotation));
        $printResponse->assertOk();
        $printResponse->assertSee('4D1yGvc6bq1BcGyGbuJ4HZchnZGkJixLl2FNMCMg.jpg');
        $printResponse->assertSee('56 Murad House, Pine Lane-8, Kurso Rajbagh, Srinagar-190008, Jammu &amp; Kashmir', false);
        $printResponse->assertSee('0194-796-1490');
        $printResponse->assertSee('info@plantechagro.com');

        // 3. Verify PDF generation works without errors with logo and address
        $pdfResponse = $this->actingAs($admin, 'admin')->get(route('admin.quotations.pdf', $quotation));
        $pdfResponse->assertOk();
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('content-type'));
    }

    public function test_quotation_create_and_edit_loads_custom_designer_settings_for_selected_service(): void
    {
        $admin = $this->actingAdmin();

        // 1. Create a service and customize its quotation design as done in "Invoice & Quotation Designer"
        $customService = Service::create([
            'name' => 'Custom Trellis High Protection',
            'slug' => 'custom-trellis-high-protection',
            'category' => 'hail-protection',
            'is_active' => true,
            'quotation_settings' => [
                'quotation_type' => 'installation',
                'document_title' => 'CUSTOM NETTING ESTIMATE',
                'document_subtitle' => 'PREMIUM ORCHARD PROTECTION SPECIFICATION',
                'accent_color' => '#0d9488',
                'show_variety' => false,
                'show_package' => true,
                'package_title' => 'Ultra Reinforced Hail Net Package',
                'package_poles' => 24,
                'package_anchors' => 10,
                'package_plants' => 0,
                'payment_schedule' => [
                    ['percent' => 50, 'stage' => 'Advance structure booking'],
                    ['percent' => 50, 'stage' => 'Final handover and tension check'],
                ],
                'additional_notes' => "Reinforced anti-hail net with 10-year UV stabilization warranty.\nPerimeter anchor foundations tested to withstand 120 km/h winds.",
                'terms' => "1. Custom netting installation warranty covers 5 years against manufacturing defects.\n2. Civil ground leveling to be facilitated by client before pole erection.",
            ],
        ]);

        $this->assertEquals('CUSTOM NETTING ESTIMATE', $customService->getQuotationDefaults()['document_title']);
        $this->assertEquals('#0d9488', $customService->getQuotationDefaults()['accent_color']);
        $this->assertCount(2, $customService->getQuotationDefaults()['payment_schedule']);

        // 2. Create lead linked to this service
        $lead = Lead::create([
            'name' => 'Ghulam Nabi',
            'phone' => '9419123456',
            'service_id' => $customService->id,
            'status' => 'new',
        ]);

        // 3. Opening create quotation screen for this lead loads the custom designer settings & preview elements
        $createResponse = $this->actingAs($admin, 'admin')->get(route('admin.quotations.create', ['lead_id' => $lead->id]));
        $createResponse->assertOk();
        $createResponse->assertSee('Live Quotation Design Preview');
        $createResponse->assertSee('CUSTOM NETTING ESTIMATE');
        $createResponse->assertSee('Ultra Reinforced Hail Net Package');
        $createResponse->assertSee('#0d9488');
        $createResponse->assertSee('Edit Service in Designer ↗');

        // 4. Save quotation using this service
        $storeResponse = $this->actingAs($admin, 'admin')->post(route('admin.quotations.store'), [
            'lead_id' => $lead->id,
            'service_id' => $customService->id,
            'customer_name' => 'Ghulam Nabi',
            'customer_phone' => '9419123456',
            'date' => '2026-09-27',
            'status' => 'draft',
            'items' => [
                [
                    'name' => 'Ultra Netting Installation',
                    'unit' => 'Kanal',
                    'qty' => 5,
                    'rate' => 80000,
                    'discount' => 0,
                    'gst_rate' => 0,
                ],
            ],
        ]);
        $storeResponse->assertRedirect();

        $quotation = Quotation::where('service_id', $customService->id)->firstOrFail();
        $this->assertEquals('CUSTOM NETTING ESTIMATE', $quotation->getDocumentTitle());
        $this->assertEquals('#0d9488', $quotation->getAccentColor());
        $this->assertEquals(24, $quotation->package_poles);
        $this->assertEquals(10, $quotation->package_anchors);
        $this->assertStringContainsString('Reinforced anti-hail net', $quotation->additional_notes);
        $this->assertStringContainsString('Custom netting installation warranty', $quotation->terms);

        // 5. Opening edit screen loads the custom designer settings & live preview as well
        $editResponse = $this->actingAs($admin, 'admin')->get(route('admin.quotations.edit', $quotation));
        $editResponse->assertOk();
        $editResponse->assertSee('Live Quotation Design Preview');
        $editResponse->assertSee('CUSTOM NETTING ESTIMATE');
        $editResponse->assertSee('#0d9488');
    }

    public function test_package_inclusions_only_printed_for_services_with_package_enabled(): void
    {
        $admin = $this->actingAdmin();

        // 1. Create a technical / testing service (show_package = false)
        $techService = Service::create([
            'name' => 'Complete Soil & Water Testing',
            'slug' => 'complete-soil-water-testing',
            'category' => 'soil-health-management',
            'is_active' => true,
        ]);
        $this->assertFalse($techService->getQuotationDefaults()['show_package']);

        // 2. Create quotation for this service
        $techQuotation = Quotation::create([
            'number' => 'QT/2026/TECH-01',
            'customer_name' => 'Mohammad Shafi',
            'customer_phone' => '9419001122',
            'service_id' => $techService->id,
            'status' => 'draft',
            'date' => now(),
            'subtotal' => 5000,
            'grand_total' => 5000,
        ]);

        $this->assertFalse($techQuotation->hasPackageInclusions());

        // Verify print view DOES NOT render "Project Package / Inclusions"
        $printResponse = $this->actingAs($admin, 'admin')->get(route('admin.quotations.print', $techQuotation));
        $printResponse->assertOk();
        $printResponse->assertDontSee('Project Package / Inclusions');

        // Verify show view DOES NOT render "Project Package / Inclusions"
        $showResponse = $this->actingAs($admin, 'admin')->get(route('admin.quotations.show', $techQuotation));
        $showResponse->assertOk();
        $showResponse->assertDontSee('Project Package / Inclusions');

        // 3. Create an orchard service (show_package = true)
        $orchardService = Service::create([
            'name' => 'High Density Apple Orchard Booking',
            'slug' => 'high-density-apple-orchard-booking',
            'category' => 'orchard-development',
            'is_active' => true,
        ]);
        $this->assertTrue($orchardService->getQuotationDefaults()['show_package']);

        $orchardQuotation = Quotation::create([
            'number' => 'QT/2026/ORCH-01',
            'customer_name' => 'Bashir Ahmad',
            'customer_phone' => '9419334455',
            'service_id' => $orchardService->id,
            'package_title' => 'Standard Kanal Orchard Package',
            'package_poles' => 19,
            'package_anchors' => 6,
            'package_plants' => 150,
            'status' => 'draft',
            'date' => now(),
            'subtotal' => 450000,
            'grand_total' => 450000,
        ]);

        $this->assertTrue($orchardQuotation->hasPackageInclusions());

        $orchardPrint = $this->actingAs($admin, 'admin')->get(route('admin.quotations.print', $orchardQuotation));
        $orchardPrint->assertOk();
        $orchardPrint->assertSee('Project Package / Inclusions');
        $orchardPrint->assertSee('Poles');
        $orchardPrint->assertSee('19');
    }

    public function test_service_has_default_quotation_variations_and_pricing(): void
    {
        $service = Service::create([
            'name' => 'Book an Orchard',
            'slug' => 'book-an-orchard',
            'category' => 'orchard-development',
            'is_active' => true,
        ]);

        $defaults = $service->getQuotationDefaults();

        $this->assertEquals('orchard', $service->getQuotationType());
        $this->assertTrue($defaults['show_variety']);
        $this->assertTrue($defaults['show_package']);
        $this->assertEquals(185000, $defaults['base_price']);
        $this->assertEquals('Kanal', $defaults['unit']);
        $this->assertIsArray($defaults['variations']);
        $this->assertCount(2, $defaults['variations']);

        // Check variation 1: 150 plants
        $this->assertEquals('150 Plants / Kanal (Standard High Density)', $defaults['variations'][0]['name']);
        $this->assertEquals(150, $defaults['variations'][0]['package_plants']);
        $this->assertEquals(19, $defaults['variations'][0]['package_poles']);
        $this->assertEquals(6, $defaults['variations'][0]['package_anchors']);
        $this->assertEquals(185000, $defaults['variations'][0]['rate']);

        // Check variation 2: 170 plants
        $this->assertEquals('170 Plants / Kanal (Ultra High Density)', $defaults['variations'][1]['name']);
        $this->assertEquals(170, $defaults['variations'][1]['package_plants']);
        $this->assertEquals(22, $defaults['variations'][1]['package_poles']);
        $this->assertEquals(8, $defaults['variations'][1]['package_anchors']);
        $this->assertEquals(210000, $defaults['variations'][1]['rate']);
    }

    public function test_can_update_service_quotation_defaults_and_variations_from_service_controller(): void
    {
        $admin = $this->actingAdmin();
        $service = Service::create([
            'name' => 'Custom Orchard Service',
            'slug' => 'custom-orchard-service',
            'category' => 'orchard-development',
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'Custom Orchard Service',
            'category' => 'orchard-development',
            'sort_order' => 1,
            'is_active' => 1,
            'creates_orchard_on_completion' => 1,
            'has_quotation_settings' => 1,
            'quotation_type' => 'orchard',
            'quotation_title' => 'PREMIUM ORCHARD ESTIMATE',
            'quotation_subtitle' => 'ULTRA DENSITY SPEC',
            'accent_color' => '#064e3b',
            'show_variety' => 1,
            'show_package' => 1,
            'package_title' => 'Ultra High Density Package',
            'package_poles' => 25,
            'package_anchors' => 10,
            'package_plants' => 200,
            'variety_name' => 'King Roat',
            'rootstock' => 'M9 / Nic 29',
            'plants_per_kanal' => '200 (Extreme Density)',
            'base_price' => 240000,
            'unit' => 'Kanal',
            'variations' => [
                [
                    'name' => '200 Plants / Kanal (Super Ultra)',
                    'plants_per_kanal' => '200',
                    'package_poles' => 25,
                    'package_anchors' => 10,
                    'package_plants' => 200,
                    'rate' => 240000,
                    'unit' => 'Kanal',
                    'variety_name' => 'King Roat',
                    'rootstock' => 'M9 / Nic 29',
                ],
            ],
            'payment_schedule' => [
                ['percent' => 50, 'stage' => 'Booking confirmation'],
                ['percent' => 50, 'stage' => 'On delivery'],
            ],
            'additional_notes' => 'Custom field notes',
            'quotation_terms' => 'Custom terms',
        ];

        $response = $this->actingAs($admin, 'admin')->put(route('admin.services.update', $service), $payload);
        $response->assertRedirect(route('admin.services.index'));

        $service->refresh();
        $this->assertNotNull($service->quotation_settings);
        $this->assertEquals('PREMIUM ORCHARD ESTIMATE', $service->quotation_settings['document_title']);
        $this->assertEquals(240000, $service->quotation_settings['base_price']);
        $this->assertCount(1, $service->quotation_settings['variations']);
        $this->assertEquals('200 Plants / Kanal (Super Ultra)', $service->quotation_settings['variations'][0]['name']);
        $this->assertEquals(25, $service->quotation_settings['variations'][0]['package_poles']);
    }

    public function test_can_update_service_quotation_defaults_and_variations_from_document_designer(): void
    {
        $admin = $this->actingAdmin();
        $service = Service::create([
            'name' => 'Trellis Installation Service',
            'slug' => 'trellis-installation-service',
            'category' => 'hail-protection',
            'is_active' => true,
        ]);

        $payload = [
            'quotation_type' => 'installation',
            'quotation_title' => 'TRELLIS PROFORMA',
            'quotation_subtitle' => 'GRID SPEC',
            'accent_color' => '#0f766e',
            'show_variety' => 0,
            'show_package' => 1,
            'package_title' => 'Trellis Grid Package',
            'package_poles' => 22,
            'package_anchors' => 8,
            'package_plants' => 0,
            'base_price' => 125000,
            'unit' => 'Kanal',
            'variations' => [
                [
                    'name' => 'Heavy Trellis 24 Poles',
                    'plants_per_kanal' => '',
                    'package_poles' => 24,
                    'package_anchors' => 10,
                    'package_plants' => 0,
                    'rate' => 135000,
                    'unit' => 'Kanal',
                ],
            ],
            'payment_schedule' => [
                ['percent' => 50, 'stage' => 'Advance'],
                ['percent' => 50, 'stage' => 'Completion'],
            ],
        ];

        $response = $this->actingAs($admin, 'admin')->put(route('admin.document-settings.service.update', $service), $payload);
        $response->assertRedirect();

        $service->refresh();
        $this->assertNotNull($service->quotation_settings);
        $this->assertEquals('TRELLIS PROFORMA', $service->quotation_settings['document_title']);
        $this->assertEquals(125000, $service->quotation_settings['base_price']);
        $this->assertEquals('Heavy Trellis 24 Poles', $service->quotation_settings['variations'][0]['name']);
    }

    public function test_quotation_create_and_edit_screens_include_service_variations_and_quick_presets(): void
    {
        $admin = $this->actingAdmin();
        $service = Service::create([
            'name' => 'Book an Orchard',
            'slug' => 'book-an-orchard',
            'category' => 'orchard-development',
            'is_active' => true,
        ]);

        // Check create view
        $createResponse = $this->actingAs($admin, 'admin')->get(route('admin.quotations.create', ['service_id' => $service->id]));
        $createResponse->assertOk();
        $createResponse->assertSee('Package / Density Variations');
        $createResponse->assertSee('150 Plants');
        $createResponse->assertSee('Standard High Density');
        $createResponse->assertSee('170 Plants');
        $createResponse->assertSee('Ultra High Density');

        // Create quotation and check edit view
        $quotation = Quotation::create([
            'number' => 'QT/2026/TST-01',
            'customer_name' => 'Zahoor Ahmad',
            'customer_phone' => '9796000000',
            'service_id' => $service->id,
            'status' => 'draft',
            'date' => now(),
            'subtotal' => 185000,
            'grand_total' => 185000,
        ]);

        $editResponse = $this->actingAs($admin, 'admin')->get(route('admin.quotations.edit', $quotation));
        $editResponse->assertOk();
        $editResponse->assertSee('Package / Density Variations');
        $editResponse->assertSee('150 Plants');
        $editResponse->assertSee('Standard High Density');
    }

    public function test_can_create_new_service_with_variations_and_settings(): void
    {
        $admin = $this->actingAdmin();

        $createScreenResponse = $this->actingAs($admin, 'admin')->get(route('admin.services.create'));
        $createScreenResponse->assertOk();
        $createScreenResponse->assertSee('Quotation Defaults, Package Inclusions & Variations', false);
        $createScreenResponse->assertSee('Quick Template Presets');
        $createScreenResponse->assertSee('High-Density Orchard');

        $response = $this->actingAs($admin, 'admin')->post(route('admin.services.store'), [
            'name' => 'Custom Kiwi Orchard',
            'category' => 'orchard-development',
            'description' => 'Kiwi plantation and trellis development',
            'is_active' => 1,
            'quotation_type' => 'orchard',
            'quotation_title' => 'KIWI ORCHARD PROFORMA',
            'quotation_subtitle' => 'ESTIMATE & SPECIFICATIONS',
            'accent_color' => '#15803d',
            'show_variety' => 1,
            'show_package' => 1,
            'package_title' => 'Per Kanal Kiwi Package',
            'base_price' => 165000,
            'unit' => 'Kanal',
            'plants_per_kanal' => '100 Plants',
            'package_poles' => 20,
            'package_anchors' => 8,
            'package_plants' => 100,
            'variety_name' => 'Hayward Kiwi',
            'rootstock' => 'Bruno Seedling',
            'variations' => [
                [
                    'name' => '100 Vines / Kanal (Standard Kiwi)',
                    'rate' => 165000,
                    'unit' => 'Kanal',
                    'plants_per_kanal' => '100',
                    'package_poles' => 20,
                    'package_anchors' => 8,
                    'package_plants' => 100,
                    'variety_name' => 'Hayward Kiwi',
                    'rootstock' => 'Bruno Seedling',
                ],
                [
                    'name' => '125 Vines / Kanal (High Density Kiwi)',
                    'rate' => 195000,
                    'unit' => 'Kanal',
                    'plants_per_kanal' => '125',
                    'package_poles' => 24,
                    'package_anchors' => 10,
                    'package_plants' => 125,
                    'variety_name' => 'Hayward Kiwi',
                    'rootstock' => 'Bruno Seedling',
                ],
            ],
            'payment_schedule' => [
                ['percent' => 40, 'stage' => 'Advance booking'],
                ['percent' => 60, 'stage' => 'Upon trellis completion'],
            ],
            'invoice_prefix' => 'INV-KIWI',
            'invoice_title' => 'Kiwi Orchard Invoice',
            'invoice_terms' => '50% balance before sapling supply.',
        ]);

        $response->assertRedirect(route('admin.services.index'));

        $service = Service::where('slug', 'custom-kiwi-orchard')->firstOrFail();
        $this->assertEquals('Custom Kiwi Orchard', $service->name);
        $this->assertNotNull($service->quotation_settings);
        $this->assertEquals('KIWI ORCHARD PROFORMA', $service->quotation_settings['document_title']);
        $this->assertEquals(165000, $service->quotation_settings['base_price']);
        $this->assertCount(2, $service->quotation_settings['variations']);
        $this->assertEquals('100 Vines / Kanal (Standard Kiwi)', $service->quotation_settings['variations'][0]['name']);
        $this->assertEquals(165000, $service->quotation_settings['variations'][0]['rate']);
        $this->assertEquals('125 Vines / Kanal (High Density Kiwi)', $service->quotation_settings['variations'][1]['name']);
        $this->assertEquals(195000, $service->quotation_settings['variations'][1]['rate']);

        // Check invoice settings
        $this->assertNotNull($service->invoice_settings);
        $this->assertEquals('INV-KIWI', $service->invoice_settings['invoice_prefix']);
        $this->assertEquals('Kiwi Orchard Invoice', $service->invoice_settings['document_title']);
    }

    public function test_lead_with_address_variation_and_area_populates_quotation_and_can_be_saved(): void
    {
        $admin = $this->actingAdmin();

        $service = Service::create([
            'name' => 'High Density Apple Orchard',
            'slug' => 'high-density-apple-orchard',
            'description' => 'Turnkey orchard with trellis and drip',
            'is_active' => true,
            'quotation_settings' => [
                'base_price' => 125000,
                'unit' => 'Kanal',
                'show_package' => true,
                'variations' => [
                    [
                        'name' => '150 Plants / Kanal (Standard)',
                        'rate' => 125000,
                        'unit' => 'Kanal',
                        'package_plants' => 150,
                        'package_poles' => 19,
                        'package_anchors' => 6,
                    ],
                    [
                        'name' => '170 Plants / Kanal (High Density)',
                        'rate' => 140000,
                        'unit' => 'Kanal',
                        'package_plants' => 170,
                        'package_poles' => 22,
                        'package_anchors' => 8,
                    ],
                ],
            ],
        ]);

        $lead = \App\Models\Lead::create([
            'name' => 'Bashir Ahmad Lone',
            'phone' => '9906112233',
            'address' => 'Village Keller, Shopian',
            'service_id' => $service->id,
            'service_variation' => '170 Plants / Kanal (High Density)',
            'area' => 4.5,
            'unit' => 'Kanal',
            'source' => 'website_modal',
            'status' => 'new',
        ]);

        // 1. Verify admin lead show view renders address, variation, area/unit
        $showRes = $this->actingAs($admin, 'admin')->get(route('admin.leads.show', $lead));
        $showRes->assertOk();
        $showRes->assertSee('Bashir Ahmad Lone');
        $showRes->assertSee('Village Keller, Shopian');
        $showRes->assertSee('170 Plants / Kanal (High Density)');
        $showRes->assertSee('4.5 Kanal');

        // 2. Open quotation creation screen with lead_id
        $createRes = $this->actingAs($admin, 'admin')->get(route('admin.quotations.create', ['lead_id' => $lead->id]));
        $createRes->assertOk();
        $createRes->assertSee('Bashir Ahmad Lone');
        $createRes->assertSee('Village Keller, Shopian');
        $createRes->assertSee('4.5 Kanal');
        // Check that initial item was prefilled with lead qty (4.5) and variation rate (140000)
        $createRes->assertSee('"qty":4.5', false);
        $createRes->assertSee('"rate":140000', false);

        // 3. Admin submits the quotation with an override on rate (e.g. negotiated to 138000)
        $postPayload = [
            'lead_id' => $lead->id,
            'service_id' => $service->id,
            'customer_name' => 'Bashir Ahmad Lone',
            'customer_phone' => '9906112233',
            'customer_address' => 'Village Keller, Shopian',
            'customer_area' => '4.5 Kanal',
            'scope_title' => 'High Density Apple Orchard (170 Plants / Kanal)',
            'date' => '2026-09-28',
            'status' => 'sent',
            'items' => [
                [
                    'name' => 'High Density Apple Orchard (170 Plants / Kanal (High Density))',
                    'unit' => 'Kanal',
                    'qty' => 4.5,
                    'rate' => 138000, // overridden by admin
                    'discount' => 0,
                    'gst_rate' => 0,
                ],
            ],
            'payment_schedule' => [
                ['percent' => 30, 'stage' => 'Advance at booking'],
                ['percent' => 50, 'stage' => 'Trellis installation'],
                ['percent' => 20, 'stage' => 'Plantation'],
            ],
        ];

        $storeRes = $this->actingAs($admin, 'admin')->post(route('admin.quotations.store'), $postPayload);
        $storeRes->assertRedirect();

        $quotation = Quotation::where('lead_id', $lead->id)->latest()->firstOrFail();
        $this->assertEquals('Bashir Ahmad Lone', $quotation->customer_name);
        $this->assertEquals('Village Keller, Shopian', $quotation->customer_address);
        $this->assertEquals('4.5 Kanal', $quotation->customer_area);
        $this->assertEquals(621000.00, (float) $quotation->grand_total); // 4.5 * 138000 = 621000
        $this->assertCount(1, $quotation->items);
        $this->assertEquals(4.5, (float) $quotation->items[0]->qty);
        $this->assertEquals(138000.00, (float) $quotation->items[0]->rate);
    }

    public function test_service_can_disable_unit_for_call_and_consultation_bookings(): void
    {
        $admin = $this->actingAdmin();

        // 1. Check auto-detection of call service
        $callService = Service::create([
            'name' => 'Book an Expert Call',
            'slug' => 'book-expert-call',
            'category' => 'orchard-consultation',
            'is_active' => true,
        ]);

        $this->assertEquals('call', $callService->getQuotationType());
        $this->assertFalse($callService->requiresUnit());

        // 2. Admin creates a new service with unit disabled explicitly
        $createPayload = [
            'name' => 'Field Agronomist Advisory',
            'category' => 'general',
            'is_active' => 1,
            'quotation_type' => 'call',
            'quotation_title' => 'CONSULTATION SUMMARY',
            'quotation_subtitle' => 'EXPERT ADVISORY',
            'has_unit' => 0,
            'base_price' => 0,
            'unit' => 'Fixed',
        ];

        $storeResponse = $this->actingAs($admin, 'admin')->post(route('admin.services.store'), $createPayload);
        $storeResponse->assertRedirect(route('admin.services.index'));

        $advisoryService = Service::where('slug', 'field-agronomist-advisory')->firstOrFail();
        $this->assertFalse($advisoryService->requiresUnit());
        $this->assertFalse($advisoryService->quotation_settings['has_unit']);

        // 3. Admin can update an existing service to toggle has_unit on or off
        $updatePayload = [
            'name' => 'Book an Expert Call',
            'category' => 'orchard-consultation',
            'is_active' => 1,
            'has_quotation_settings' => 1,
            'quotation_type' => 'call',
            'quotation_title' => 'EXPERT CALL BRIEF',
            'has_unit' => 0,
            'base_price' => 0,
            'unit' => 'Fixed',
        ];

        $updateResponse = $this->actingAs($admin, 'admin')->put(route('admin.services.update', $callService), $updatePayload);
        $updateResponse->assertRedirect();

        $callService->refresh();
        $this->assertFalse($callService->requiresUnit());
        $this->assertFalse($callService->quotation_settings['has_unit']);

        // 4. Client submits booking request without any area or unit
        $leadResponse = $this->post(route('leads.store'), [
            'name' => 'Tariq Ahmad Rather',
            'phone' => '9419001122',
            'address' => 'Bijbehara, Anantnag',
            'service_id' => $callService->id,
            'loaded_at' => time() - 10,
        ]);
        $leadResponse->assertRedirect('/?submitted=1');

        $lead = \App\Models\Lead::where('phone', '9419001122')->firstOrFail();
        $this->assertEquals('Tariq Ahmad Rather', $lead->name);
        $this->assertEquals($callService->id, $lead->service_id);
        $this->assertNull($lead->area);
        $this->assertNull($lead->unit);
        $this->assertEquals('', $lead->formattedRequirement());

        // 5. Admin view of the lead displays Not Applicable instead of empty/dashed area
        $showResponse = $this->actingAs($admin, 'admin')->get(route('admin.leads.show', $lead));
        $showResponse->assertOk();
        $showResponse->assertSee('Tariq Ahmad Rather');
        $showResponse->assertSee('Not Applicable (Call / Advisory)');
    }
}



