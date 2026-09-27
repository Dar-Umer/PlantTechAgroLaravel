<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\ShopSettingsService;
use Illuminate\Http\Request;

class DocumentDesignController extends Controller
{
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'prefixes');

        $services = Service::orderBy('sort_order')->orderBy('name')->get();

        $selectedServiceId = (int) $request->query('service_id', $services->first()?->id);
        $selectedService = $services->firstWhere('id', $selectedServiceId) ?? $services->first();

        $invoiceSettings = [
            'prefix' => config('invoice.prefix', 'PTA'),
            'due_days' => (int) config('invoice.due_days', 15),
            'document_title' => config('invoice.document_title', 'TAX INVOICE'),
            'design_style' => config('invoice.design_style', 'tax_invoice'),
            'accent_color' => config('invoice.accent_color', '#16a34a'),
            'show_logo' => (bool) config('invoice.show_logo', true),
            'show_gst' => (bool) config('invoice.show_gst', true),
            'show_bank_details' => (bool) config('invoice.show_bank_details', true),
            'show_terms' => (bool) config('invoice.show_terms', true),
            'show_signatory' => (bool) config('invoice.show_signatory', true),
            'terms' => config('invoice.terms', "Payment due within 15 days of invoice date.\nGoods once sold will not be taken back."),
            'notes' => config('invoice.notes', ''),
        ];

        $quotationSettings = [
            'prefix' => config('quotation.prefix', 'QT'),
            'validity_days' => (int) config('quotation.validity_days', 15),
            'document_title' => config('quotation.document_title', 'PROFORMA INVOICE'),
            'document_subtitle' => config('quotation.document_subtitle', 'PRICE ESTIMATE & QUOTATION'),
            'design_style' => config('quotation.design_style', 'orchard_proforma'),
            'accent_color' => config('quotation.accent_color', '#064e3b'),
            'show_logo' => (bool) config('quotation.show_logo', true),
            'show_address' => (bool) config('quotation.show_address', true),
            'show_specs' => (bool) config('quotation.show_specs', true),
            'show_package' => (bool) config('quotation.show_package', true),
            'show_schedule' => (bool) config('quotation.show_schedule', true),
            'show_bank_details' => (bool) config('quotation.show_bank_details', true),
            'show_terms' => (bool) config('quotation.show_terms', true),
            'show_signatory' => (bool) config('quotation.show_signatory', true),
            'terms' => config('quotation.terms', "1. This quotation is valid until the specified expiry date.\n2. Advance payment at the time of booking confirms the order.\n3. Final measurements and quantities are subject to site verification.\n4. Any extra work or materials, not included in this quotation, will be billed separately.\n5. Booking is subject to availability of plants and materials.\n6. Cancellation and refund policy will be as per company terms and conditions.\n7. Prices are subject to change without prior notice due to market variations."),
            'additional_notes' => config('quotation.additional_notes', "Extra anchors, if required, will be charged separately at ₹ 2,500 per anchor, including all accessories.\nExtra plants, if required, will be charged separately at ₹ 1,230 per plant, including all accessories."),
        ];

        $companySettings = [
            'company_name' => config('shop.site_name', config('invoice.company_name', config('quotation.company_name', 'Plant Tech Agro'))),
            'company_tagline' => config('quotation.company_tagline', 'Complete Orchard Solution'),
            'company_slogan' => config('quotation.company_slogan', 'From Planning to Plantation We Build Better Orchards.'),
            'company_address' => config('shop.site_address', config('invoice.address', config('quotation.address', '56 Murad House, Pine Lane-8, Kurso Rajbagh, Srinagar-190008, Jammu & Kashmir'))),
            'company_phone' => config('shop.site_phone', config('invoice.phone', config('quotation.phone', '0194-796-1490'))),
            'company_email' => config('shop.site_email', config('invoice.email', config('quotation.email', 'info@plantechagro.com'))),
            'company_website' => config('quotation.website', 'www.planttechagro.com'),
            'company_gst_no' => config('invoice.gst_no', ''),
            'bank_name' => config('shop.bank_name', config('quotation.bank_name', 'J&K Bank')),
            'bank_account_name' => config('shop.bank_account_name', config('quotation.bank_account_name', 'Plant Tech Agro')),
            'bank_account_no' => config('shop.bank_account_no', config('quotation.bank_account_no', '0942 0100 0000 0275')),
            'bank_branch' => config('shop.bank_branch', config('quotation.bank_branch', 'Migrant Colony Hall Pulwama')),
            'bank_ifsc' => config('shop.bank_ifsc', config('quotation.bank_ifsc', 'JAKA0MIGRNT')),
        ];

        $servicesData = $services->map(fn(Service $s) => [
            'id' => $s->id,
            'name' => $s->name,
            'slug' => $s->slug,
            'category' => $s->category,
            'quotation_type' => $s->getQuotationType(),
            'quotation_defaults' => $s->getQuotationDefaults(),
            'invoice_defaults' => $s->getInvoiceDefaults(),
            'has_custom_settings' => !empty($s->quotation_settings) || !empty($s->invoice_settings),
        ]);

        return view('admin.document_designs.index', compact(
            'activeTab',
            'services',
            'servicesData',
            'selectedService',
            'invoiceSettings',
            'quotationSettings',
            'companySettings'
        ));
    }

    public function updatePrefixes(Request $request, ShopSettingsService $shopSettings)
    {
        $validated = $request->validate([
            'invoice_prefix' => 'required|string|max:20',
            'invoice_due_days' => 'required|integer|min:1|max:365',
            'quotation_prefix' => 'required|string|max:20',
            'quotation_validity_days' => 'required|integer|min:1|max:365',
            'company_name' => 'required|string|max:255',
            'company_tagline' => 'nullable|string|max:255',
            'company_slogan' => 'nullable|string|max:500',
            'company_address' => 'nullable|string|max:1000',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'company_website' => 'nullable|string|max:255',
            'company_gst_no' => 'nullable|string|max:50',
        ]);

        // Sync to shop, invoice and quotation namespaces
        $shopSettings->set([
            'prefix' => $validated['invoice_prefix'],
            'due_days' => $validated['invoice_due_days'],
            'company_name' => $validated['company_name'],
            'address' => $validated['company_address'] ?? '',
            'phone' => $validated['company_phone'] ?? '',
            'email' => $validated['company_email'] ?? '',
            'gst_no' => $validated['company_gst_no'] ?? '',
        ], 'invoice');

        $shopSettings->set([
            'prefix' => $validated['quotation_prefix'],
            'validity_days' => $validated['quotation_validity_days'],
            'company_name' => $validated['company_name'],
            'company_tagline' => $validated['company_tagline'] ?? '',
            'company_slogan' => $validated['company_slogan'] ?? '',
            'address' => $validated['company_address'] ?? '',
            'phone' => $validated['company_phone'] ?? '',
            'email' => $validated['company_email'] ?? '',
            'website' => $validated['company_website'] ?? '',
        ], 'quotation');

        $shopSettings->set([
            'site_name' => $validated['company_name'],
            'site_address' => $validated['company_address'] ?? '',
            'site_phone' => $validated['company_phone'] ?? '',
            'site_email' => $validated['company_email'] ?? '',
        ], 'shop');

        return redirect()->route('admin.document-settings.index', ['tab' => 'prefixes'])
            ->with('success', 'Document prefixes and company letterhead identity updated successfully.');
    }

    public function updateQuotations(Request $request, ShopSettingsService $shopSettings)
    {
        $validated = $request->validate([
            'quotation_design_style' => 'required|string|in:orchard_proforma,modern_clean,classic_executive',
            'quotation_accent_color' => 'required|string|max:25',
            'quotation_document_title' => 'required|string|max:100',
            'quotation_document_subtitle' => 'nullable|string|max:150',
            'quotation_show_logo' => 'nullable|boolean',
            'quotation_show_address' => 'nullable|boolean',
            'quotation_show_specs' => 'nullable|boolean',
            'quotation_show_package' => 'nullable|boolean',
            'quotation_show_schedule' => 'nullable|boolean',
            'quotation_show_bank_details' => 'nullable|boolean',
            'quotation_show_terms' => 'nullable|boolean',
            'quotation_show_signatory' => 'nullable|boolean',
            'quotation_terms' => 'nullable|string|max:3000',
            'quotation_additional_notes' => 'nullable|string|max:2000',
        ]);

        $shopSettings->set([
            'design_style' => $validated['quotation_design_style'],
            'accent_color' => $validated['quotation_accent_color'],
            'document_title' => $validated['quotation_document_title'],
            'document_subtitle' => $validated['quotation_document_subtitle'] ?? '',
            'show_logo' => (bool) ($validated['quotation_show_logo'] ?? false),
            'show_address' => (bool) ($validated['quotation_show_address'] ?? false),
            'show_specs' => (bool) ($validated['quotation_show_specs'] ?? false),
            'show_package' => (bool) ($validated['quotation_show_package'] ?? false),
            'show_schedule' => (bool) ($validated['quotation_show_schedule'] ?? false),
            'show_bank_details' => (bool) ($validated['quotation_show_bank_details'] ?? false),
            'show_terms' => (bool) ($validated['quotation_show_terms'] ?? false),
            'show_signatory' => (bool) ($validated['quotation_show_signatory'] ?? false),
            'terms' => $validated['quotation_terms'] ?? '',
            'additional_notes' => $validated['quotation_additional_notes'] ?? '',
        ], 'quotation');

        return redirect()->route('admin.document-settings.index', ['tab' => 'quotations'])
            ->with('success', 'Global quotation styling and design options saved successfully.');
    }

    public function updateInvoices(Request $request, ShopSettingsService $shopSettings)
    {
        $validated = $request->validate([
            'invoice_design_style' => 'required|string|in:tax_invoice,modern_compact,detailed_field',
            'invoice_accent_color' => 'required|string|max:25',
            'invoice_document_title' => 'required|string|max:100',
            'invoice_show_logo' => 'nullable|boolean',
            'invoice_show_gst' => 'nullable|boolean',
            'invoice_show_bank_details' => 'nullable|boolean',
            'invoice_show_terms' => 'nullable|boolean',
            'invoice_show_signatory' => 'nullable|boolean',
            'invoice_terms' => 'nullable|string|max:3000',
            'invoice_notes' => 'nullable|string|max:2000',
        ]);

        $shopSettings->set([
            'design_style' => $validated['invoice_design_style'],
            'accent_color' => $validated['invoice_accent_color'],
            'document_title' => $validated['invoice_document_title'],
            'show_logo' => (bool) ($validated['invoice_show_logo'] ?? false),
            'show_gst' => (bool) ($validated['invoice_show_gst'] ?? false),
            'show_bank_details' => (bool) ($validated['invoice_show_bank_details'] ?? false),
            'show_terms' => (bool) ($validated['invoice_show_terms'] ?? false),
            'show_signatory' => (bool) ($validated['invoice_show_signatory'] ?? false),
            'terms' => $validated['invoice_terms'] ?? '',
            'notes' => $validated['invoice_notes'] ?? '',
        ], 'invoice');

        return redirect()->route('admin.document-settings.index', ['tab' => 'invoices'])
            ->with('success', 'Global invoice design and layout options saved successfully.');
    }

    public function updateService(Request $request, Service $service)
    {
        $validated = $request->validate([
            'quotation_type' => 'required|string|in:orchard,plants,installation,technical,general',
            'quotation_title' => 'required|string|max:120',
            'quotation_subtitle' => 'nullable|string|max:150',
            'accent_color' => 'nullable|string|max:25',
            'show_variety' => 'nullable|boolean',
            'show_package' => 'nullable|boolean',
            'package_title' => 'nullable|string|max:150',
            'package_poles' => 'nullable|integer|min:0',
            'package_anchors' => 'nullable|integer|min:0',
            'package_plants' => 'nullable|integer|min:0',
            'variety_name' => 'nullable|string|max:150',
            'rootstock' => 'nullable|string|max:150',
            'plants_per_kanal' => 'nullable|string|max:150',
            'base_price' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'variations' => 'nullable|array',
            'variations.*.name' => 'nullable|string|max:255',
            'variations.*.plants_per_kanal' => 'nullable',
            'variations.*.package_poles' => 'nullable',
            'variations.*.package_anchors' => 'nullable',
            'variations.*.package_plants' => 'nullable',
            'variations.*.rate' => 'nullable',
            'variations.*.unit' => 'nullable|string|max:50',
            'variations.*.rootstock' => 'nullable|string|max:150',
            'variations.*.variety_name' => 'nullable|string|max:150',
            'payment_schedule' => 'nullable|array',
            'payment_schedule.*.percent' => 'nullable|numeric|min:0|max:100',
            'payment_schedule.*.stage' => 'nullable|string|max:255',
            'additional_notes' => 'nullable|string|max:3000',
            'quotation_terms' => 'nullable|string|max:3000',

            // Invoice overrides for this service
            'invoice_title' => 'nullable|string|max:120',
            'invoice_subtitle' => 'nullable|string|max:150',
            'invoice_prefix' => 'nullable|string|max:30',
            'invoice_terms' => 'nullable|string|max:3000',
        ]);

        // Filter valid payment milestones
        $cleanSchedule = [];
        if (!empty($validated['payment_schedule']) && is_array($validated['payment_schedule'])) {
            foreach ($validated['payment_schedule'] as $step) {
                if (!empty($step['stage']) || isset($step['percent'])) {
                    $cleanSchedule[] = [
                        'percent' => (float) ($step['percent'] ?? 0),
                        'stage' => (string) ($step['stage'] ?? ''),
                    ];
                }
            }
        }

        // Filter valid package variations
        $cleanVariations = [];
        if (!empty($validated['variations']) && is_array($validated['variations'])) {
            foreach ($validated['variations'] as $v) {
                if (!empty($v['name'])) {
                    $cleanVariations[] = [
                        'name' => (string) $v['name'],
                        'plants_per_kanal' => (string) ($v['plants_per_kanal'] ?? ''),
                        'package_poles' => isset($v['package_poles']) && $v['package_poles'] !== '' ? (int) $v['package_poles'] : null,
                        'package_anchors' => isset($v['package_anchors']) && $v['package_anchors'] !== '' ? (int) $v['package_anchors'] : null,
                        'package_plants' => isset($v['package_plants']) && $v['package_plants'] !== '' ? (int) $v['package_plants'] : null,
                        'rate' => isset($v['rate']) && $v['rate'] !== '' ? (float) $v['rate'] : 0,
                        'unit' => (string) ($v['unit'] ?? 'Kanal'),
                        'rootstock' => (string) ($v['rootstock'] ?? ''),
                        'variety_name' => (string) ($v['variety_name'] ?? ''),
                    ];
                }
            }
        }

        $service->quotation_settings = [
            'quotation_type' => $validated['quotation_type'],
            'document_title' => $validated['quotation_title'],
            'document_subtitle' => $validated['quotation_subtitle'] ?? '',
            'accent_color' => $validated['accent_color'] ?: '#064e3b',
            'show_variety' => (bool) ($validated['show_variety'] ?? false),
            'show_package' => (bool) ($validated['show_package'] ?? false),
            'package_title' => $validated['package_title'] ?? '',
            'package_poles' => $validated['package_poles'] !== null ? (int) $validated['package_poles'] : null,
            'package_anchors' => $validated['package_anchors'] !== null ? (int) $validated['package_anchors'] : null,
            'package_plants' => $validated['package_plants'] !== null ? (int) $validated['package_plants'] : null,
            'variety_name' => $validated['variety_name'] ?? '',
            'rootstock' => $validated['rootstock'] ?? '',
            'plants_per_kanal' => $validated['plants_per_kanal'] ?? '',
            'base_price' => isset($validated['base_price']) && $validated['base_price'] !== '' ? (float) $validated['base_price'] : null,
            'unit' => $validated['unit'] ?? 'Kanal',
            'variations' => $cleanVariations,
            'payment_schedule' => $cleanSchedule,
            'additional_notes' => $validated['additional_notes'] ?? '',
            'terms' => $validated['quotation_terms'] ?? '',
        ];

        $service->invoice_settings = [
            'document_title' => !empty($validated['invoice_title']) ? $validated['invoice_title'] : 'TAX INVOICE',
            'document_subtitle' => !empty($validated['invoice_subtitle']) ? $validated['invoice_subtitle'] : $service->name,
            'invoice_prefix' => $validated['invoice_prefix'] ?? '',
            'accent_color' => !empty($validated['accent_color']) ? $validated['accent_color'] : '#16a34a',
            'terms' => $validated['invoice_terms'] ?? '',
        ];

        $service->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Quotation & invoice styling for '{$service->name}' updated successfully.",
                'service' => $service,
            ]);
        }

        return redirect()->route('admin.document-settings.index', ['tab' => 'services', 'service_id' => $service->id])
            ->with('success', "Quotation & invoice styling for '{$service->name}' saved successfully.");
    }

    public function resetService(Service $service)
    {
        $service->quotation_settings = null;
        $service->invoice_settings = null;
        $service->save();

        return redirect()->route('admin.document-settings.index', ['tab' => 'services', 'service_id' => $service->id])
            ->with('success', "Styles for '{$service->name}' reset to factory presets.");
    }
}
