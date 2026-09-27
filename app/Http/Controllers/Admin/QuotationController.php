<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Service;
use App\Models\WorkOrder;
use App\Models\WorkOrderStage;
use App\Models\WorkOrderStageProduct;
use App\Services\QuotationNumberer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $query = Quotation::query()->with(['service', 'lead', 'customer', 'workOrder'])->latest();

        if ($status = $request->query('status')) {
            if (in_array($status, array_keys(Quotation::STATUSES), true)) {
                $query->where('status', $status);
            }
        }

        if ($search = trim((string) $request->query('q'))) {
            $search = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $quotations = $query->paginate(15)->withQueryString();

        $statusCounts = Quotation::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.quotations.index', compact('quotations', 'statusCounts'));
    }

    public function create(Request $request)
    {
        $lead = null;
        if ($leadId = $request->query('lead_id')) {
            $lead = Lead::with(['service', 'convertedCustomer'])->find($leadId);
        }

        $services = Service::where('is_active', true)->orderBy('name')->get();
        $servicesJson = $services->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'slug' => $s->slug,
            'category' => $s->category,
            'description' => $s->description,
            'quotation_type' => $s->getQuotationType(),
            'defaults' => $s->getQuotationDefaults(),
        ]);

        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'unit', 'rate', 'gst_rate']);
        $varieties = \App\Models\Variety::where('is_active', true)->orderBy('sort_order')->orderBy('name')->pluck('name');

        $defaults = config('quotation', []);
        $leadData = $this->extractLeadQuotationData($lead);

        $companySettings = $this->getCompanyDocumentSettings();

        return view('admin.quotations.create', compact('lead', 'services', 'servicesJson', 'products', 'varieties', 'defaults', 'leadData', 'companySettings'));
    }

    /**
     * Resolve company and letterhead identity settings.
     */
    protected function getCompanyDocumentSettings(): array
    {
        return [
            'company_name' => config('shop.site_name', config('invoice.company_name', config('quotation.company_name', 'Plant Tech Agro'))),
            'company_tagline' => config('quotation.company_tagline', 'Complete Orchard Solution'),
            'company_slogan' => config('quotation.company_slogan', 'From Planning to Plantation We Build Better Orchards.'),
            'company_address' => config('shop.site_address', config('invoice.address', config('quotation.address', '56 Murad House, Pine Lane-8, Kurso Rajbagh, Srinagar-190008, Jammu & Kashmir'))),
            'company_phone' => config('shop.site_phone', config('invoice.phone', config('quotation.phone', '0194-796-1490'))),
            'company_email' => config('shop.site_email', config('invoice.email', config('quotation.email', 'info@plantechagro.com'))),
            'company_website' => config('quotation.website', 'www.planttechagro.com'),
            'bank_name' => config('shop.bank_name', config('quotation.bank_name', 'J&K Bank')),
            'bank_account_name' => config('shop.bank_account_name', config('quotation.bank_account_name', 'Plant Tech Agro')),
            'bank_account_no' => config('shop.bank_account_no', config('quotation.bank_account_no', '0942 0100 0000 0275')),
            'bank_branch' => config('shop.bank_branch', config('quotation.bank_branch', 'Migrant Colony Hall Pulwama')),
            'bank_ifsc' => config('shop.bank_ifsc', config('quotation.bank_ifsc', 'JAKA0MIGRNT')),
            'logo_url' => config('shop.logo_url') ?: config('invoice.logo'),
            'prefix' => config('quotation.prefix', 'QT'),
        ];
    }

    /**
     * Extract actual customer selections and contact info from Lead.
     */
    protected function extractLeadQuotationData(?Lead $lead): array
    {
        if (! $lead) {
            return [
                'customer_name' => '',
                'customer_phone' => '',
                'customer_email' => '',
                'customer_area' => '',
                'customer_address' => '',
                'service_id' => '',
                'scope_title' => '',
                'scope_subtitle' => '',
                'variety_name' => '',
                'variety_specification' => '',
                'rootstock' => '',
                'plants_per_kanal' => '',
                'notes' => '',
                'area_kanals_qty' => null,
            ];
        }

        $custom = (array) ($lead->custom_fields ?? []);
        $existingCustomer = $lead->convertedCustomer ?: Customer::findByPhoneDigits($lead->phone);

        // 1. Customer Name & Phone
        $name = $lead->name ?: ($existingCustomer?->name ?? '');
        $phone = $lead->phone ?: ($existingCustomer?->phone ?? '');
        $email = $custom['email'] ?? ($existingCustomer?->email ?? '');

        // 2. Area & Requirement
        $areaQty = $lead->getArea();
        $unit = $lead->getUnit();
        $areaFormatted = $lead->formattedRequirement();

        if ($areaQty === null) {
            $areaRaw = $custom['proposed_area_kanals']
                ?? $custom['area_kanals']
                ?? $custom['orchard_kanals']
                ?? $custom['land_size']
                ?? $custom['kanals']
                ?? $custom['area']
                ?? ($existingCustomer?->area ?? '');

            if ($areaRaw !== '' && $areaRaw !== null) {
                if (is_numeric($areaRaw)) {
                    $areaQty = (float) $areaRaw;
                    $areaFormatted = $areaQty . ' ' . $unit;
                } else {
                    $areaFormatted = (string) $areaRaw;
                    if (preg_match('/([0-9]+(?:\.[0-9]+)?)/', (string) $areaRaw, $m)) {
                        $areaQty = (float) $m[1];
                    }
                }
            }
        }

        // 3. Address & Location
        $address = $lead->address
            ?: ($custom['proposed_location']
                ?? $custom['location']
                ?? $custom['address']
                ?? $custom['orchard_location']
                ?? ($existingCustomer?->address ?? ''));

        // 4. Service & Scope Details
        $serviceId = $lead->service_id ?? '';
        $serviceVariation = $lead->getVariation();
        $scopeTitle = $lead->service?->name ?? ($custom['service_name'] ?? '');
        $scopeSubtitle = $lead->service?->description ?? '';

        // 5. Variety & Rootstock
        $varietyRaw = $custom['preferred_variety']
            ?? $custom['variety']
            ?? $custom['variety_name']
            ?? $custom['tree_variety']
            ?? '';

        $varietyName = $varietyRaw;
        $rootstock = $custom['rootstock'] ?? '';

        if ($varietyRaw && str_contains($varietyRaw, '/')) {
            $parts = array_map('trim', explode('/', $varietyRaw, 2));
            $varietyName = $parts[0];
            if (empty($rootstock) && ! empty($parts[1])) {
                $rootstock = $parts[1];
            }
        }

        $plantsPerKanal = $custom['plants_per_kanal'] ?? $custom['plant_density'] ?? '';

        return [
            'customer_name' => $name,
            'customer_phone' => $phone,
            'customer_email' => $email,
            'customer_area' => $areaFormatted,
            'customer_address' => $address,
            'service_id' => $serviceId,
            'service_variation' => $serviceVariation,
            'scope_title' => $scopeTitle,
            'scope_subtitle' => $scopeSubtitle,
            'variety_name' => $varietyName,
            'variety_specification' => $varietyName,
            'rootstock' => $rootstock,
            'plants_per_kanal' => $plantsPerKanal,
            'notes' => $lead->notes ?? '',
            'area_kanals_qty' => $areaQty,
            'unit' => $unit,
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lead_id' => ['nullable', Rule::exists('leads', 'id')],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')],
            'service_id' => ['nullable', Rule::exists('services', 'id')],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:25', 'regex:/^[0-9+\-\s()]{7,25}$/'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_address' => ['nullable', 'string', 'max:1000'],
            'customer_area' => ['nullable', 'string', 'max:255'],
            'scope_title' => ['nullable', 'string', 'max:255'],
            'scope_subtitle' => ['nullable', 'string', 'max:255'],
            'variety_name' => ['nullable', 'string', 'max:255'],
            'variety_specification' => ['nullable', 'string', 'max:255'],
            'rootstock' => ['nullable', 'string', 'max:255'],
            'plants_per_kanal' => ['nullable', 'string', 'max:255'],
            'package_title' => ['nullable', 'string', 'max:255'],
            'package_poles' => ['nullable', 'integer', 'min:0'],
            'package_anchors' => ['nullable', 'integer', 'min:0'],
            'package_plants' => ['nullable', 'integer', 'min:0'],
            'payment_schedule' => ['nullable', 'array'],
            'payment_schedule.*.percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_schedule.*.stage' => ['nullable', 'string', 'max:255'],
            'payment_schedule.*.amount' => ['nullable', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:date'],
            'status' => ['required', Rule::in(array_keys(Quotation::STATUSES))],
            'notes' => ['nullable', 'string'],
            'additional_notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_no' => ['nullable', 'string', 'max:255'],
            'bank_branch' => ['nullable', 'string', 'max:255'],
            'bank_ifsc' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_website' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', Rule::exists('products', 'id')],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.qty' => ['required', 'numeric', 'min:0.001'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.gst_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $quotation = DB::transaction(function () use ($data, $request) {
            $subtotal = 0;
            $discountTotal = 0;
            $gstTotal = 0;
            $grandTotal = 0;

            $itemsData = [];
            foreach ($data['items'] as $index => $item) {
                $qty = (float) $item['qty'];
                $rate = (float) $item['rate'];
                $discount = (float) ($item['discount'] ?? 0);
                $gstRate = (float) ($item['gst_rate'] ?? 0);

                $taxable = max(0, ($qty * $rate) - $discount);
                $tax = round($taxable * ($gstRate / 100), 2);
                $total = round($taxable + $tax, 2);

                $subtotal += $taxable;
                $discountTotal += $discount;
                $gstTotal += $tax;
                $grandTotal += $total;

                $itemsData[] = [
                    'product_id' => $item['product_id'] ?? null,
                    'name' => $item['name'],
                    'unit' => $item['unit'] ?? null,
                    'qty' => $qty,
                    'rate' => $rate,
                    'discount' => $discount,
                    'gst_rate' => $gstRate,
                    'total' => $total,
                    'sort_order' => $index,
                ];
            }

            $customerId = $data['customer_id'] ?? null;
            if (! $customerId && ! empty($data['lead_id'])) {
                $lead = Lead::find($data['lead_id']);
                if ($lead && $lead->converted_customer_id) {
                    $customerId = $lead->converted_customer_id;
                }
            }
            if (! $customerId && ! empty($data['customer_phone'])) {
                $matchedCustomer = Customer::findByPhoneDigits($data['customer_phone']);
                if ($matchedCustomer) {
                    $customerId = $matchedCustomer->id;
                }
            }

            // Clean up payment schedule
            $paymentSchedule = null;
            if (! empty($data['payment_schedule']) && is_array($data['payment_schedule'])) {
                $cleanSchedule = [];
                foreach ($data['payment_schedule'] as $sched) {
                    if (! empty($sched['stage']) || isset($sched['percent'])) {
                        $pct = (float) ($sched['percent'] ?? 0);
                        $amt = isset($sched['amount']) && is_numeric($sched['amount']) && (float) $sched['amount'] > 0
                            ? (float) $sched['amount']
                            : round(($grandTotal * $pct) / 100, 2);

                        $cleanSchedule[] = [
                            'percent' => $pct,
                            'stage' => $sched['stage'] ?? '',
                            'amount' => $amt,
                        ];
                    }
                }
                if (! empty($cleanSchedule)) {
                    $paymentSchedule = $cleanSchedule;
                }
            }

            $service = ! empty($data['service_id']) ? Service::find($data['service_id']) : null;
            $sDefaults = $service ? $service->getQuotationDefaults() : [];
            $serviceShowPackage = $service ? (! empty($sDefaults['show_package'])) : (! empty($data['package_poles']) || ! empty($data['package_anchors']) || ! empty($data['package_plants']));

            $quotation = Quotation::create([
                'number' => QuotationNumberer::next(),
                'lead_id' => $data['lead_id'] ?? null,
                'customer_id' => $customerId,
                'service_id' => $data['service_id'] ?? null,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_address' => $data['customer_address'] ?? null,
                'customer_area' => $data['customer_area'] ?? null,
                'scope_title' => $data['scope_title'] ?? ($sDefaults['label'] ?? ($service?->name ?? config('quotation.scope_title', 'Book an Orchard'))),
                'scope_subtitle' => $data['scope_subtitle'] ?? ($sDefaults['document_subtitle'] ?? ($service?->description ?? config('quotation.scope_subtitle', 'Complete Orchard Development Solution'))),
                'variety_name' => $data['variety_name'] ?? ($sDefaults['variety_name'] ?? config('quotation.variety_name', 'Devil Gala')),
                'variety_specification' => $data['variety_specification'] ?? ($data['variety_name'] ?? ($sDefaults['variety_name'] ?? config('quotation.variety_specification', 'Devil Gala'))),
                'rootstock' => $data['rootstock'] ?? ($sDefaults['rootstock'] ?? config('quotation.rootstock', 'M9 / T337 (High Density)')),
                'plants_per_kanal' => $data['plants_per_kanal'] ?? ($sDefaults['plants_per_kanal'] ?? config('quotation.plants_per_kanal', '150 (Standard)')),
                'package_title' => $serviceShowPackage ? ($data['package_title'] ?? ($sDefaults['package_title'] ?? 'Per Kanal Standard Package')) : null,
                'package_poles' => $serviceShowPackage ? (isset($data['package_poles']) && $data['package_poles'] !== '' ? (int) $data['package_poles'] : ($sDefaults['package_poles'] ?? 19)) : null,
                'package_anchors' => $serviceShowPackage ? (isset($data['package_anchors']) && $data['package_anchors'] !== '' ? (int) $data['package_anchors'] : ($sDefaults['package_anchors'] ?? 6)) : null,
                'package_plants' => $serviceShowPackage ? (isset($data['package_plants']) && $data['package_plants'] !== '' ? (int) $data['package_plants'] : ($sDefaults['package_plants'] ?? 150)) : null,
                'payment_schedule' => $paymentSchedule ?: ($sDefaults['payment_schedule'] ?? config('quotation.payment_schedule')),
                'date' => $data['date'],
                'valid_until' => $data['valid_until'] ?? null,
                'status' => $data['status'],
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'gst_total' => $gstTotal,
                'grand_total' => $grandTotal,
                'notes' => $data['notes'] ?? null,
                'additional_notes' => $data['additional_notes'] ?? ($sDefaults['additional_notes'] ?? config('quotation.additional_notes')),
                'terms' => $data['terms'] ?? ($sDefaults['terms'] ?? config('quotation.terms')),
                'bank_name' => $data['bank_name'] ?? config('quotation.bank_name', 'J&K Bank'),
                'bank_account_name' => $data['bank_account_name'] ?? config('quotation.bank_account_name', 'Plant Tech Agro'),
                'bank_account_no' => $data['bank_account_no'] ?? config('quotation.bank_account_no', '0942 0100 0000 0275'),
                'bank_branch' => $data['bank_branch'] ?? config('quotation.bank_branch', 'Migrant Colony Hall Pulwama'),
                'bank_ifsc' => $data['bank_ifsc'] ?? config('quotation.bank_ifsc', 'JAKA0MIGRNT'),
                'company_address' => $data['company_address'] ?? (config('invoice.address') ?: (config('shop.site_address') ?: config('quotation.address'))),
                'company_phone' => $data['company_phone'] ?? (config('invoice.phone') ?: (config('shop.site_phone') ?: config('quotation.phone'))),
                'company_email' => $data['company_email'] ?? (config('invoice.email') ?: (config('shop.site_email') ?: config('quotation.email'))),
                'company_website' => $data['company_website'] ?? (config('invoice.website') ?: (config('shop.site_url') ?: config('quotation.website'))),
                'created_by' => $request->user('admin')->id ?? null,
            ]);

            foreach ($itemsData as $row) {
                $quotation->items()->create($row);
            }

            return $quotation;
        });

        return redirect()->route('admin.quotations.show', $quotation)
            ->with('success', "Quotation {$quotation->number} created successfully.");
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['items.product', 'lead', 'customer', 'service', 'workOrder', 'createdBy']);

        return view('admin.quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        if ($quotation->isApproved()) {
            return redirect()->route('admin.quotations.show', $quotation)
                ->with('error', 'Approved quotations are locked and cannot be edited.');
        }

        $quotation->load(['items', 'service']);
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $servicesJson = $services->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'slug' => $s->slug,
            'category' => $s->category,
            'description' => $s->description,
            'quotation_type' => $s->getQuotationType(),
            'defaults' => $s->getQuotationDefaults(),
        ]);

        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'unit', 'rate', 'gst_rate']);
        $varieties = \App\Models\Variety::where('is_active', true)->orderBy('sort_order')->orderBy('name')->pluck('name');
        $defaults = config('quotation', []);
        $companySettings = $this->getCompanyDocumentSettings();

        return view('admin.quotations.edit', compact('quotation', 'services', 'servicesJson', 'products', 'varieties', 'defaults', 'companySettings'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        if ($quotation->isApproved()) {
            return redirect()->route('admin.quotations.show', $quotation)
                ->with('error', 'Approved quotations are locked and cannot be edited.');
        }

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:25', 'regex:/^[0-9+\-\s()]{7,25}$/'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_address' => ['nullable', 'string', 'max:1000'],
            'customer_area' => ['nullable', 'string', 'max:255'],
            'service_id' => ['nullable', Rule::exists('services', 'id')],
            'scope_title' => ['nullable', 'string', 'max:255'],
            'scope_subtitle' => ['nullable', 'string', 'max:255'],
            'variety_name' => ['nullable', 'string', 'max:255'],
            'variety_specification' => ['nullable', 'string', 'max:255'],
            'rootstock' => ['nullable', 'string', 'max:255'],
            'plants_per_kanal' => ['nullable', 'string', 'max:255'],
            'package_title' => ['nullable', 'string', 'max:255'],
            'package_poles' => ['nullable', 'integer', 'min:0'],
            'package_anchors' => ['nullable', 'integer', 'min:0'],
            'package_plants' => ['nullable', 'integer', 'min:0'],
            'payment_schedule' => ['nullable', 'array'],
            'payment_schedule.*.percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_schedule.*.stage' => ['nullable', 'string', 'max:255'],
            'payment_schedule.*.amount' => ['nullable', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:date'],
            'status' => ['required', Rule::in(array_keys(Quotation::STATUSES))],
            'notes' => ['nullable', 'string'],
            'additional_notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_no' => ['nullable', 'string', 'max:255'],
            'bank_branch' => ['nullable', 'string', 'max:255'],
            'bank_ifsc' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_website' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', Rule::exists('products', 'id')],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.qty' => ['required', 'numeric', 'min:0.001'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.gst_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        DB::transaction(function () use ($quotation, $data) {
            $subtotal = 0;
            $discountTotal = 0;
            $gstTotal = 0;
            $grandTotal = 0;

            $itemsData = [];
            foreach ($data['items'] as $index => $item) {
                $qty = (float) $item['qty'];
                $rate = (float) $item['rate'];
                $discount = (float) ($item['discount'] ?? 0);
                $gstRate = (float) ($item['gst_rate'] ?? 0);

                $taxable = max(0, ($qty * $rate) - $discount);
                $tax = round($taxable * ($gstRate / 100), 2);
                $total = round($taxable + $tax, 2);

                $subtotal += $taxable;
                $discountTotal += $discount;
                $gstTotal += $tax;
                $grandTotal += $total;

                $itemsData[] = [
                    'product_id' => $item['product_id'] ?? null,
                    'name' => $item['name'],
                    'unit' => $item['unit'] ?? null,
                    'qty' => $qty,
                    'rate' => $rate,
                    'discount' => $discount,
                    'gst_rate' => $gstRate,
                    'total' => $total,
                    'sort_order' => $index,
                ];
            }

            $customerId = $quotation->customer_id;
            if (! $customerId && ! empty($quotation->lead_id)) {
                $customerId = $quotation->lead?->converted_customer_id;
            }
            if (! $customerId && ! empty($data['customer_phone'])) {
                $customerId = Customer::findByPhoneDigits($data['customer_phone'])?->id;
            }

            // Clean up payment schedule
            $paymentSchedule = null;
            if (! empty($data['payment_schedule']) && is_array($data['payment_schedule'])) {
                $cleanSchedule = [];
                foreach ($data['payment_schedule'] as $sched) {
                    if (! empty($sched['stage']) || isset($sched['percent'])) {
                        $pct = (float) ($sched['percent'] ?? 0);
                        $amt = isset($sched['amount']) && is_numeric($sched['amount']) && (float) $sched['amount'] > 0
                            ? (float) $sched['amount']
                            : round(($grandTotal * $pct) / 100, 2);

                        $cleanSchedule[] = [
                            'percent' => $pct,
                            'stage' => $sched['stage'] ?? '',
                            'amount' => $amt,
                        ];
                    }
                }
                if (! empty($cleanSchedule)) {
                    $paymentSchedule = $cleanSchedule;
                }
            }

            $svcId = $data['service_id'] ?? $quotation->service_id;
            $service = $svcId ? Service::find($svcId) : null;
            $sDefaults = $service ? $service->getQuotationDefaults() : [];
            $serviceShowPackage = $service ? (! empty($sDefaults['show_package'])) : (! empty($data['package_poles']) || ! empty($data['package_anchors']) || ! empty($data['package_plants']));

            $quotation->update([
                'customer_id' => $customerId,
                'service_id' => $data['service_id'] ?? null,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_address' => $data['customer_address'] ?? null,
                'customer_area' => $data['customer_area'] ?? null,
                'scope_title' => $data['scope_title'] ?? $quotation->scope_title,
                'scope_subtitle' => $data['scope_subtitle'] ?? $quotation->scope_subtitle,
                'variety_name' => $data['variety_name'] ?? $quotation->variety_name,
                'variety_specification' => $data['variety_specification'] ?? $quotation->variety_specification,
                'rootstock' => $data['rootstock'] ?? $quotation->rootstock,
                'plants_per_kanal' => $data['plants_per_kanal'] ?? $quotation->plants_per_kanal,
                'package_title' => $serviceShowPackage ? ($data['package_title'] ?? $quotation->package_title) : null,
                'package_poles' => $serviceShowPackage ? (isset($data['package_poles']) && $data['package_poles'] !== '' ? (int) $data['package_poles'] : $quotation->package_poles) : null,
                'package_anchors' => $serviceShowPackage ? (isset($data['package_anchors']) && $data['package_anchors'] !== '' ? (int) $data['package_anchors'] : $quotation->package_anchors) : null,
                'package_plants' => $serviceShowPackage ? (isset($data['package_plants']) && $data['package_plants'] !== '' ? (int) $data['package_plants'] : $quotation->package_plants) : null,
                'payment_schedule' => $paymentSchedule ?: $quotation->payment_schedule,
                'date' => $data['date'],
                'valid_until' => $data['valid_until'] ?? null,
                'status' => $data['status'],
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'gst_total' => $gstTotal,
                'grand_total' => $grandTotal,
                'notes' => $data['notes'] ?? null,
                'additional_notes' => $data['additional_notes'] ?? $quotation->additional_notes,
                'terms' => $data['terms'] ?? null,
                'bank_name' => $data['bank_name'] ?? $quotation->bank_name,
                'bank_account_name' => $data['bank_account_name'] ?? $quotation->bank_account_name,
                'bank_account_no' => $data['bank_account_no'] ?? $quotation->bank_account_no,
                'bank_branch' => $data['bank_branch'] ?? $quotation->bank_branch,
                'bank_ifsc' => $data['bank_ifsc'] ?? $quotation->bank_ifsc,
                'company_address' => $data['company_address'] ?? $quotation->company_address,
                'company_phone' => $data['company_phone'] ?? $quotation->company_phone,
                'company_email' => $data['company_email'] ?? $quotation->company_email,
                'company_website' => $data['company_website'] ?? $quotation->company_website,
            ]);

            $quotation->items()->delete();
            foreach ($itemsData as $row) {
                $quotation->items()->create($row);
            }
        });

        return redirect()->route('admin.quotations.show', $quotation)
            ->with('success', "Quotation {$quotation->number} updated successfully.");
    }

    public function destroy(Quotation $quotation)
    {
        if ($quotation->work_order_id) {
            return back()->with('error', 'Cannot delete a quotation linked to an active work order.');
        }

        $quotation->delete();

        return redirect()->route('admin.quotations.index')
            ->with('success', 'Quotation deleted.');
    }

    public function print(Quotation $quotation)
    {
        $quotation->load(['items.product', 'service', 'lead', 'customer']);

        return view('admin.quotations.print', [
            'quotation' => $quotation,
            'forPdf' => false,
        ]);
    }

    public function pdf(Quotation $quotation)
    {
        $quotation->load(['items.product', 'service', 'lead', 'customer']);

        $pdf = Pdf::loadView('admin.quotations.print', [
            'quotation' => $quotation,
            'forPdf' => true,
        ])->setPaper('a4');

        $filename = str_replace('/', '-', $quotation->number) . '-proforma-quotation.pdf';

        return $pdf->download($filename);
    }

    /**
     * "Approve & Start Work":
     * - Finds or creates Customer record.
     * - Converts Lead (if linked).
     * - Creates active Work Order (in_progress) with quoted items.
     * - Marks Quotation approved.
     */
    public function approveAndStartWork(Request $request, Quotation $quotation)
    {
        if (! $quotation->canApprove()) {
            return back()->with('error', 'This quotation has already been approved or linked to a work order.');
        }

        try {
            $adminId = $request->user('admin')?->id;
            $workOrder = \App\Services\QuotationApprovalService::approveAndStartWork($quotation, $adminId);

            return redirect()->route('admin.work-orders.show', $workOrder)
                ->with('success', "Quotation {$quotation->number} approved! Lead converted and active Work Order {$workOrder->number} has been started.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
