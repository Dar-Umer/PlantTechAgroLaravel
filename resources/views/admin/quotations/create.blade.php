@extends('admin.layout')

@section('page-title', 'Create Quotation / Proforma')

@section('content')
@php
    $leadData = $leadData ?? [];
    $selectedSvcId = old('service_id', $leadData['service_id'] ?? '');
    $selectedSvc = $services->firstWhere('id', $selectedSvcId) ?? ($lead?->service);
    $initialServiceType = $selectedSvc ? $selectedSvc->getQuotationType() : 'general';
    $initialDefaults = $selectedSvc ? $selectedSvc->getQuotationDefaults() : [];

    $initialShowVariety = $initialDefaults['show_variety'] ?? (!empty($leadData['variety_name']) || $initialServiceType === 'orchard');
    $initialShowPackage = $initialDefaults['show_package'] ?? ($initialServiceType === 'orchard' || $initialServiceType === 'installation');

    $matchedVariationIndex = null;
    $serviceVariations = $initialDefaults['variations'] ?? [];
    if (!empty($leadData['service_variation']) && is_array($serviceVariations)) {
        foreach ($serviceVariations as $vIdx => $v) {
            if (strcasecmp(trim($v['name'] ?? ''), trim($leadData['service_variation'])) === 0) {
                $matchedVariationIndex = $vIdx;
                break;
            }
        }
    }

    $initialItems = [];
    if (old('items')) {
        $initialItems = old('items');
    } elseif ($lead) {
        $svcName = $leadData['scope_title'] ?: ($lead->service?->name ?? 'Service Deliverable');
        $qty = ($leadData['area_kanals_qty'] && $leadData['area_kanals_qty'] > 0) ? $leadData['area_kanals_qty'] : 1;
        $unit = $leadData['unit'] ?? ($leadData['area_kanals_qty'] ? 'Kanal' : 'Job');
        $rate = 0;

        if ($matchedVariationIndex !== null && isset($serviceVariations[$matchedVariationIndex])) {
            $v = $serviceVariations[$matchedVariationIndex];
            $svcName = ($lead->service?->name ?? 'Service Deliverable') . ' (' . ($v['name'] ?? $leadData['service_variation']) . ')';
            if (isset($v['rate']) && $v['rate'] !== '') {
                $rate = (float) $v['rate'];
            }
            if (!empty($v['unit'])) {
                $unit = $v['unit'];
            }
        } elseif (!empty($initialDefaults['base_price'])) {
            $rate = (float) $initialDefaults['base_price'];
            if (!empty($initialDefaults['unit'])) {
                $unit = $initialDefaults['unit'];
            }
        }

        $initialItems[] = [
            'product_id' => '',
            'name' => $svcName,
            'unit' => $unit,
            'qty' => $qty,
            'rate' => $rate,
            'discount' => 0,
            'gst_rate' => 0,
        ];
    } else {
        $initialItems[] = [
            'product_id' => '',
            'name' => '',
            'unit' => 'Kanal',
            'qty' => 1,
            'rate' => 0,
            'discount' => 0,
            'gst_rate' => 0,
        ];
    }

    $initialMilestones = old('payment_schedule', $initialDefaults['payment_schedule'] ?? ($defaults['payment_schedule'] ?? [
        ['percent' => 30, 'stage' => 'Advance at the time of booking'],
        ['percent' => 50, 'stage' => 'Before trellis installation'],
        ['percent' => 20, 'stage' => 'Before plantation'],
    ]));

    $productsJson = $products->map(fn($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'unit' => $p->unit,
        'rate' => (float) $p->rate,
        'gst_rate' => (float) $p->gst_rate,
    ]);

    $company = array_merge([
        'company_name' => config('shop.site_name', 'Plant Tech Agro'),
        'company_tagline' => config('quotation.company_tagline', 'Complete Orchard Solution'),
        'company_slogan' => config('quotation.company_slogan', 'From Planning to Plantation We Build Better Orchards.'),
        'company_address' => config('shop.site_address', '56 Murad House, Pine Lane-8, Kurso Rajbagh, Srinagar-190008, Jammu & Kashmir'),
        'company_phone' => config('shop.site_phone', '0194-796-1490'),
        'company_email' => config('shop.site_email', 'info@plantechagro.com'),
        'company_website' => config('quotation.website', 'www.planttechagro.com'),
        'bank_name' => config('shop.bank_name', 'J&K Bank'),
        'bank_account_name' => config('shop.bank_account_name', 'Plant Tech Agro'),
        'bank_account_no' => config('shop.bank_account_no', '0942 0100 0000 0275'),
        'bank_branch' => config('shop.bank_branch', 'Migrant Colony Hall Pulwama'),
        'bank_ifsc' => config('shop.bank_ifsc', 'JAKA0MIGRNT'),
        'prefix' => config('quotation.prefix', 'QT'),
    ], $companySettings ?? []);
@endphp

<div class="space-y-6" x-data="quotationForm()">

    {{-- Page Header & Active Service Design Status Banner --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-3">
            <x-admin.button href="{{ $lead ? route('admin.leads.show', $lead) : route('admin.quotations.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>
                Back
            </x-admin.button>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h2 class="text-2xl font-bold text-gray-900">Create Quotation / Proforma</h2>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border shadow-2xs transition-all"
                          :style="{ backgroundColor: accentColor + '15', color: accentColor, borderColor: accentColor + '35' }">
                        <span class="w-2.5 h-2.5 rounded-full shadow-2xs" :style="{ backgroundColor: accentColor }"></span>
                        <span x-text="documentTitle + ' • ' + formatStyleName(serviceType)"></span>
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-0.5">Quotation styling, headers, and fields adapt automatically from the selected Service Reference.</p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a :href="selectedServiceId ? ('{{ url('/admin/services') }}/' + selectedServiceId + '/edit?tab=quotation') : '{{ route('admin.services.index') }}'"
               target="_blank"
               class="px-3.5 py-1.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-semibold text-gray-700 transition inline-flex items-center gap-1.5 shadow-2xs">
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Service in Designer ↗</span>
            </a>

            <button type="button" @click="showLivePreview = !showLivePreview"
                    class="px-3.5 py-1.5 rounded-xl border text-xs font-semibold transition inline-flex items-center gap-1.5 shadow-2xs"
                    :class="showLivePreview ? 'bg-brand-50 border-brand-200 text-brand-700' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span x-text="showLivePreview ? 'Live Preview Active' : 'Show Live Preview'"></span>
            </button>

            @if($lead)
                <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-brand-50 text-brand-700 text-xs font-semibold border border-brand-100">
                    Lead: {{ $lead->name }}
                </span>
            @endif
        </div>
    </div>

    @if($errors->any())
        <div class="rounded-2xl bg-red-50 border border-red-200 p-4">
            <p class="text-sm font-semibold text-red-800">Please correct the following errors:</p>
            <ul class="mt-2 text-xs text-red-700 list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form & Live Document Design Preview Layout --}}
    <form action="{{ route('admin.quotations.store') }}" method="POST">
        @csrf
        @if($lead)
            <input type="hidden" name="lead_id" value="{{ $lead->id }}">
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- Left Column: Configuration & Form Inputs --}}
            <div class="lg:col-span-7 space-y-6 transition-all duration-300"
                 :class="{ 'lg:col-span-12': !showLivePreview, 'lg:col-span-7': showLivePreview }">

                {{-- 1. Client & Document Information --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Quotation Prepared For</h3>
                        <p class="text-sm text-gray-500">Client contact details and proforma validity timeline.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <x-admin.input name="customer_name" label="Client / Farmer Name"
                                       x-model="customerName"
                                       :value="old('customer_name', $leadData['customer_name'] ?? '')"
                                       placeholder="Enter farmer name" required />

                        <x-admin.input name="customer_phone" label="Phone Number"
                                       x-model="customerPhone"
                                       :value="old('customer_phone', $leadData['customer_phone'] ?? '')"
                                       placeholder="10-digit mobile number" required />

                        <x-admin.input name="customer_email" label="Email (Optional)" type="email"
                                       x-model="customerEmail"
                                       :value="old('customer_email', $leadData['customer_email'] ?? '')"
                                       placeholder="farmer@example.com" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <x-admin.input name="customer_area" label="Area"
                                       x-model="customerArea"
                                       :value="old('customer_area', $leadData['customer_area'] ?? '')"
                                       placeholder="e.g. 5 Kanal" />

                        <div class="md:col-span-2">
                            <x-admin.input name="customer_address" label="Orchard Location"
                                           x-model="customerAddress"
                                           :value="old('customer_address', $leadData['customer_address'] ?? '')"
                                           placeholder="e.g. Pattan, Baramulla" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pt-3 border-t border-gray-100">
                        <x-admin.input name="date" label="Quotation Date" type="date"
                                       x-model="quotationDate"
                                       :value="old('date', date('Y-m-d'))" required />

                        <x-admin.input name="valid_until" label="Valid Until" type="date"
                                       x-model="validUntil"
                                       :value="old('valid_until', date('Y-m-d', strtotime('+12 days')))" />

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                            <select name="status" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                @foreach(\App\Models\Quotation::STATUSES as $k => $label)
                                    <option value="{{ $k }}" {{ old('status', 'sent') === $k ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- 2. Project & Service Scope (Decides Style and Input Fields) --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">Project / Service Scope</h3>
                            <p class="text-sm text-gray-500">Select the service to load the quotation styling, document titles, milestones, and relevant fields.</p>
                        </div>

                        {{-- Optional Field Toggles for Admin Customization --}}
                        <div class="flex items-center gap-2">
                            <button type="button" @click="showVarietyDetails = !showVarietyDetails"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-semibold transition"
                                    :class="showVarietyDetails ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'">
                                <span x-text="showVarietyDetails ? '✓ Variety Fields Active' : '+ Add Variety Fields'"></span>
                            </button>
                            <button type="button" @click="showPackageInclusions = !showPackageInclusions"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-semibold transition"
                                    :class="showPackageInclusions ? 'bg-teal-50 border-teal-200 text-teal-700' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'">
                                <span x-text="showPackageInclusions ? '✓ Package Box Active' : '+ Add Package Box'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Service Reference <span class="text-brand-600 font-bold">*</span>
                            </label>
                            <select name="service_id"
                                    x-model="selectedServiceId"
                                    @change="onServiceChange($event)"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                <option value="">-- General Service (Custom) --</option>
                                @foreach($services as $svc)
                                    <option value="{{ $svc->id }}" {{ (string)$selectedSvcId === (string)$svc->id ? 'selected' : '' }}>
                                        {{ $svc->name }} ({{ ucfirst($svc->getQuotationType()) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Project Title</label>
                            <input type="text" name="scope_title" x-model="scopeTitle"
                                   placeholder="e.g. Book High-Density Orchard Setup" required
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Scope Subtitle</label>
                            <input type="text" name="scope_subtitle" x-model="scopeSubtitle"
                                   placeholder="e.g. Complete Orchard Development Solution"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>
                    </div>

                    {{-- Package / Density Variations Selector --}}
                    <div x-show="variations && variations.length > 0" x-transition class="rounded-2xl border border-brand-200 bg-brand-50/50 p-4 space-y-3">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-brand-600 text-white flex items-center justify-center text-xs font-bold shadow-2xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2H5a2 2 0 00-2 2v2m14 0h.01M5 11h.01"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Package / Density Variations</h4>
                                    <p class="text-[11px] text-gray-500">Select a variation to prefill density (e.g. 150 vs 170 plants/kanal), inclusions (poles, anchors), and deliverable rate.</p>
                                </div>
                            </div>

                            <div class="w-full sm:w-auto">
                                <select @change="applyVariation($event.target.value)"
                                        class="w-full sm:w-72 text-xs font-bold rounded-xl border-brand-300 bg-white py-2 px-3 text-brand-900 shadow-2xs focus:border-brand-500 focus:ring-brand-500">
                                    <option value="">-- Choose Variation Preset --</option>
                                    <template x-for="(v, vIdx) in variations" :key="vIdx">
                                        <option :value="vIdx" :selected="selectedVariationIndex == vIdx"
                                                x-text="v.name + ' — ₹' + Number(v.rate || 0).toLocaleString('en-IN') + ' / ' + (v.unit || 'Kanal')"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        {{-- Quick Click Pills --}}
                        <div class="flex items-center gap-2 flex-wrap pt-1 border-t border-brand-100">
                            <span class="text-[10px] uppercase font-bold text-brand-700 tracking-wider">Quick Presets:</span>
                            <template x-for="(v, vIdx) in variations" :key="vIdx">
                                <button type="button" @click="applyVariation(vIdx)"
                                        class="px-2.5 py-1 text-xs font-semibold rounded-lg border transition-all inline-flex items-center gap-1.5 shadow-2xs cursor-pointer"
                                        :class="selectedVariationIndex == vIdx ? 'bg-brand-700 text-white border-brand-700 ring-2 ring-brand-300' : 'bg-white text-gray-800 border-gray-200 hover:border-brand-300 hover:bg-brand-50/50'">
                                    <span class="w-2 h-2 rounded-full" :class="selectedVariationIndex == vIdx ? 'bg-white' : 'bg-brand-500'"></span>
                                    <span x-text="v.name"></span>
                                    <span class="text-[10px] font-bold" :class="selectedVariationIndex == vIdx ? 'text-brand-100' : 'text-brand-700'" x-text="'₹' + Number(v.rate || 0).toLocaleString('en-IN')"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Inner Variety Specification Card (Dynamically Shown for Orchard / Plants) --}}
                    <div x-show="showVarietyDetails" x-transition class="rounded-xl border border-emerald-100 bg-emerald-50/40 p-5 space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-bold text-emerald-900 flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                Booking / Variety Details
                            </h4>
                            <span class="text-xs text-emerald-700 font-medium">Included on Printed Proforma</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Booked Variety</label>
                                <input type="text" name="variety_name" x-model="selectedVariety"
                                       list="variety_catalog"
                                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                                       placeholder="Select or enter variety">
                                <datalist id="variety_catalog">
                                    <option value="Devil Gala"></option>
                                    <option value="Gala Schnico Red"></option>
                                    <option value="Red Velox"></option>
                                    <option value="King Roat"></option>
                                    <option value="Jeromine"></option>
                                    <option value="Fuji"></option>
                                    @foreach($varieties as $vName)
                                        <option value="{{ $vName }}"></option>
                                    @endforeach
                                </datalist>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Variety Specification</label>
                                <input type="text" name="variety_specification"
                                       x-model="varietySpecification"
                                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                                       placeholder="Select or enter variety specification">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Rootstock</label>
                                <input type="text" name="rootstock" x-model="rootstock"
                                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                                       placeholder="e.g. M9 / T337 (High Density)">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Plants per Kanal</label>
                                <input type="text" name="plants_per_kanal" x-model="plantsPerKanal"
                                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                                       placeholder="e.g. 150 (Standard)">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. Deliverables Table --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">Deliverables &amp; Cost Breakdown</h3>
                            <p class="text-sm text-gray-500">Itemized deliverables, units, quantities and rates.</p>
                        </div>

                        <button type="button" @click="addItem()"
                                class="inline-flex items-center justify-center font-semibold transition text-sm rounded-xl shadow-sm px-3.5 py-1.5 text-xs bg-brand-600 text-white hover:bg-brand-700">
                            + Add Row
                        </button>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 text-xs font-semibold uppercase tracking-wider">
                                <tr>
                                    <th class="py-3 px-3.5 w-1/3">Deliverable / Service Description</th>
                                    <th class="py-3 px-3 text-center w-24">Unit</th>
                                    <th class="py-3 px-3 text-center w-20">Qty</th>
                                    <th class="py-3 px-3 text-right w-28">Rate (₹)</th>
                                    <th class="py-3 px-3 text-right w-24">Discount (₹)</th>
                                    <th class="py-3 px-3 text-center w-20">GST %</th>
                                    <th class="py-3 px-3 text-right w-28">Total (₹)</th>
                                    <th class="py-3 px-2 text-center w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="hover:bg-gray-50/60 transition">
                                        <td class="py-2.5 px-3.5">
                                            <input type="text" :name="'items[' + index + '][name]'"
                                                   x-model="item.name"
                                                   placeholder="Enter deliverable description..."
                                                   required
                                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-semibold text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <input type="text" :name="'items[' + index + '][unit]'"
                                                   x-model="item.unit"
                                                   placeholder="Kanal, Pcs, Job"
                                                   class="w-full text-center rounded-xl border border-gray-200 bg-gray-50 px-2 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <input type="number" step="0.001" :name="'items[' + index + '][qty]'"
                                                   x-model.number="item.qty"
                                                   min="0.001"
                                                   required
                                                   class="w-full text-center rounded-xl border border-gray-200 bg-gray-50 px-2 py-2 text-sm font-medium focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <input type="number" step="0.01" :name="'items[' + index + '][rate]'"
                                                   x-model.number="item.rate"
                                                   min="0"
                                                   required
                                                   class="w-full text-right rounded-xl border border-gray-200 bg-gray-50 px-2.5 py-2 text-sm font-medium focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <input type="number" step="0.01" :name="'items[' + index + '][discount]'"
                                                   x-model.number="item.discount"
                                                   min="0"
                                                   class="w-full text-right rounded-xl border border-gray-200 bg-gray-50 px-2 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <input type="number" step="0.01" :name="'items[' + index + '][gst_rate]'"
                                                   x-model.number="item.gst_rate"
                                                   min="0" max="100"
                                                   class="w-full text-center rounded-xl border border-gray-200 bg-gray-50 px-2 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                        </td>
                                        <td class="py-2.5 px-3 text-right font-bold text-gray-900 tabular-nums">
                                            ₹<span x-text="formatMoney(lineTotal(item))"></span>
                                        </td>
                                        <td class="py-2.5 px-2 text-center">
                                            <button type="button" @click="removeItem(index)"
                                                    class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition"
                                                    title="Remove Item">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Totals Summary --}}
                    <div class="flex justify-end pt-2">
                        <div class="w-80 space-y-2.5 text-sm bg-gray-50 rounded-2xl p-5 border border-gray-200">
                            <div class="flex justify-between text-gray-600">
                                <span>Taxable Subtotal:</span>
                                <span class="font-semibold text-gray-900 tabular-nums">₹<span x-text="formatMoney(subtotal)"></span></span>
                            </div>
                            <div class="flex justify-between text-gray-600" x-show="discountTotal > 0">
                                <span>Discount:</span>
                                <span class="font-semibold text-red-600 tabular-nums">-₹<span x-text="formatMoney(discountTotal)"></span></span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>GST / Applicable Taxes:</span>
                                <span class="font-semibold text-gray-900 tabular-nums">+₹<span x-text="formatMoney(gstTotal)"></span></span>
                            </div>
                            <div class="flex justify-between items-center pt-3 border-t border-gray-200">
                                <span class="text-sm font-bold uppercase tracking-wider text-gray-700">Grand Total (INR):</span>
                                <span class="text-xl font-bold text-gray-900 tabular-nums">₹<span x-text="formatMoney(grandTotal)"></span></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. Payment Schedule & Project Package Inclusions (Adaptive Grid) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Payment Schedule Milestones --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4"
                         :class="showPackageInclusions ? '' : 'md:col-span-2'">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Payment Schedule</h3>
                                <p class="text-sm text-gray-500">Milestone percentages &amp; calculated amounts based on Grand Total.</p>
                            </div>

                            <button type="button" @click="addMilestone()"
                                    class="inline-flex items-center justify-center font-semibold transition text-sm rounded-xl shadow-sm px-3 py-1 text-xs bg-brand-600 text-white hover:bg-brand-700">
                                + Add Stage
                            </button>
                        </div>

                        <div class="space-y-3">
                            <template x-for="(m, mIdx) in milestones" :key="mIdx">
                                <div class="flex items-center gap-2.5 p-3 bg-gray-50 rounded-xl border border-gray-200">
                                    <div class="w-16 flex-shrink-0">
                                        <div class="flex items-center bg-white rounded-lg border border-gray-200 px-2 py-1">
                                            <input type="number" :name="'payment_schedule[' + mIdx + '][percent]'"
                                                   x-model.number="m.percent"
                                                   class="w-full text-xs font-bold text-brand-700 text-center focus:outline-none"
                                                   min="0" max="100">
                                            <span class="text-xs text-gray-400 font-bold">%</span>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <input type="text" :name="'payment_schedule[' + mIdx + '][stage]'"
                                               x-model="m.stage"
                                               placeholder="e.g. Advance at the time of booking"
                                               required
                                               class="w-full text-xs font-medium bg-white rounded-lg border border-gray-200 py-1.5 px-3 focus:outline-none focus:border-brand-500">
                                    </div>
                                    <div class="w-28 text-right flex-shrink-0 font-bold text-xs text-gray-900 tabular-nums">
                                        ₹<span x-text="milestoneAmount(m.percent)"></span>
                                    </div>
                                    <button type="button" @click="removeMilestone(mIdx)"
                                            class="text-gray-400 hover:text-red-600 transition p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Project Package Inclusions (Dynamically Shown for Orchard / Installation) --}}
                    <div x-show="showPackageInclusions" x-transition class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                        <div class="border-b border-gray-100 pb-3">
                            <h3 class="text-lg font-semibold text-gray-900">Project Package / Inclusions</h3>
                            <p class="text-sm text-gray-500">Physical equipment &amp; plant inventory specifications.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Package Title</label>
                            <input type="text" name="package_title" x-model="packageTitle"
                                   :disabled="!showPackageInclusions"
                                   placeholder="e.g. Per Kanal Standard Package"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 disabled:opacity-50">
                        </div>

                        <div class="grid grid-cols-3 gap-3 pt-1">
                            <div class="bg-gray-50 rounded-xl p-3.5 border border-gray-200 text-center">
                                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Poles</div>
                                <input type="number" name="package_poles" x-model.number="packagePoles"
                                       :disabled="!showPackageInclusions"
                                       class="w-full text-center text-xl font-bold text-gray-900 bg-white rounded-lg border border-gray-200 mt-2 py-1.5 focus:outline-none focus:border-brand-500 disabled:opacity-50">
                            </div>

                            <div class="bg-gray-50 rounded-xl p-3.5 border border-gray-200 text-center">
                                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Anchors</div>
                                <input type="number" name="package_anchors" x-model.number="packageAnchors"
                                       :disabled="!showPackageInclusions"
                                       class="w-full text-center text-xl font-bold text-gray-900 bg-white rounded-lg border border-gray-200 mt-2 py-1.5 focus:outline-none focus:border-brand-500 disabled:opacity-50">
                            </div>

                            <div class="bg-gray-50 rounded-xl p-3.5 border border-gray-200 text-center">
                                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Plants</div>
                                <input type="number" name="package_plants" x-model.number="packagePlants"
                                       :disabled="!showPackageInclusions"
                                       class="w-full text-center text-xl font-bold text-gray-900 bg-white rounded-lg border border-gray-200 mt-2 py-1.5 focus:outline-none focus:border-brand-500 disabled:opacity-50">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 5. Additional Notes & Internal Notes --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <div class="border-b border-gray-100 pb-3">
                        <h3 class="text-lg font-semibold text-gray-900">Additional Notes &amp; Scope Remarks</h3>
                        <p class="text-sm text-gray-500">Service-specific bullet points printed on proforma vs internal remarks.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Proforma Additional Notes (Bullet Points)</label>
                            <textarea name="additional_notes" x-model="additionalNotes" rows="4"
                                      class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 resize-y"
                                      placeholder="Service-specific notes and conditions..."></textarea>
                            <p class="mt-1.5 text-xs text-gray-400">Each line will appear as an itemized bullet point on the proforma.</p>
                        </div>

                        <x-admin.textarea name="notes"
                                          label="Internal Scope Remarks (Optional)"
                                          rows="4"
                                          :value="old('notes', $leadData['notes'] ?? '')"
                                          placeholder="e.g. Customer requirements or field notes..."
                                          helptext="Customer's inquiry notes and internal notes for agronomy staff." />
                    </div>
                </div>

                {{-- 6. Bank Details & Terms & Conditions --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Bank Account Details --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                        <div class="border-b border-gray-100 pb-3">
                            <h3 class="text-lg font-semibold text-gray-900">Bank Account Details</h3>
                            <p class="text-sm text-gray-500">Account information printed on proforma for farmer payment.</p>
                        </div>

                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <x-admin.input name="bank_account_name" label="Account Name"
                                               :value="old('bank_account_name', $company['bank_account_name'] ?? 'Plant Tech Agro')" />

                                <x-admin.input name="bank_name" label="Bank Name"
                                               :value="old('bank_name', $company['bank_name'] ?? 'J&K Bank')" />
                            </div>

                            <x-admin.input name="bank_account_no" label="Account Number"
                                           :value="old('bank_account_no', $company['bank_account_no'] ?? '0942 0100 0000 0275')"
                                           class="font-mono font-bold" />

                            <div class="grid grid-cols-2 gap-4">
                                <x-admin.input name="bank_branch" label="Branch"
                                               :value="old('bank_branch', $company['bank_branch'] ?? 'Migrant Colony Hall Pulwama')" />

                                <x-admin.input name="bank_ifsc" label="IFSC Code"
                                               :value="old('bank_ifsc', $company['bank_ifsc'] ?? 'JAKA0MIGRNT')"
                                               class="font-mono font-bold" />
                            </div>
                        </div>
                    </div>

                    {{-- Terms & Conditions --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                        <div class="border-b border-gray-100 pb-3">
                            <h3 class="text-lg font-semibold text-gray-900">Terms &amp; Conditions</h3>
                            <p class="text-sm text-gray-500">Numbered terms printed on proforma.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Terms Text</label>
                            <textarea name="terms" x-model="terms" rows="7"
                                      placeholder="Numbered terms..."
                                      class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 resize-y"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Form Actions Bar --}}
                <div class="flex items-center justify-end gap-3 pt-3">
                    <x-admin.button href="{{ $lead ? route('admin.leads.show', $lead) : route('admin.quotations.index') }}" variant="secondary">
                        Cancel
                    </x-admin.button>
                    <x-admin.button type="submit" variant="primary">
                        Save &amp; Generate Quotation
                    </x-admin.button>
                </div>
            </div>

            {{-- Right Column: Live Interactive Quotation Design Preview (from Designer) --}}
            <div x-show="showLivePreview" x-cloak class="lg:col-span-5 sticky top-6 space-y-4">
                <div class="flex items-center justify-between px-1">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full animate-pulse shadow-xs" :style="{ backgroundColor: accentColor }"></span>
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-700">Live Quotation Design Preview</span>
                    </div>
                    <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-full border shadow-2xs"
                          :style="{ backgroundColor: accentColor + '12', color: accentColor, borderColor: accentColor + '30' }"
                          x-text="serviceType.toUpperCase()"></span>
                </div>

                {{-- Miniature Document Simulation Card --}}
                <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-5 space-y-4 text-gray-800 text-[11px] leading-snug">

                    {{-- 1. Document Header & Branding --}}
                    <div class="flex items-start justify-between gap-3 pb-3 border-b border-gray-100">
                        <div>
                            <span class="font-extrabold text-base tracking-tight" :style="{ color: accentColor }">
                                Plant<span class="text-orange-500">Tech</span> Agro
                            </span>
                            <p class="text-[9px] font-semibold text-gray-400 uppercase tracking-widest mt-0.5">{{ $company['company_tagline'] ?? '' }}</p>
                            <p class="text-[9px] text-gray-500 mt-1 italic">{{ $company['company_slogan'] ?? '' }}</p>
                            <p class="text-[9px] text-gray-400 mt-0.5">{{ $company['company_address'] ?? '' }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold text-white block shadow-xs transition-colors"
                                  :style="{ backgroundColor: accentColor }"
                                  x-text="documentTitle"></span>
                            <span class="text-[9px] font-bold text-gray-500 block mt-1" x-text="documentSubtitle"></span>
                            <span class="text-[9px] font-mono text-gray-400 block mt-0.5">{{ $company['prefix'] ?? 'QT' }}/{{ date('Y') }}-{{ date('y', strtotime('+1 year')) }}/0042</span>
                            <span class="text-[9px] text-gray-500 block" x-text="'Date: ' + (quotationDate || 'Today')"></span>
                        </div>
                    </div>

                    {{-- 2. Client & Service Scope Box --}}
                    <div class="grid grid-cols-2 gap-2 text-[10px]">
                        {{-- Client box --}}
                        <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-2.5 space-y-1">
                            <div class="text-[9px] font-bold uppercase tracking-wider text-gray-400 flex items-center gap-1">
                                <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Prepared For
                            </div>
                            <div class="font-bold text-gray-900" x-text="customerName || 'Farmer / Client Name'"></div>
                            <div class="text-gray-500" x-text="customerPhone || 'Phone Number'"></div>
                            <div class="text-gray-500" x-text="(customerArea ? customerArea + ' • ' : '') + (customerAddress || 'Orchard Location')"></div>
                        </div>

                        {{-- Service Scope box --}}
                        <div class="rounded-xl border p-2.5 space-y-1"
                             :style="{ borderColor: accentColor + '30', backgroundColor: accentColor + '08' }">
                            <div class="text-[9px] font-bold uppercase tracking-wider flex items-center justify-between" :style="{ color: accentColor }">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Project Scope
                                </span>
                                <span class="text-[8px] font-bold uppercase px-1 rounded" :style="{ backgroundColor: accentColor + '20' }">Estimate</span>
                            </div>
                            <div class="font-bold text-gray-900 line-clamp-1" x-text="scopeTitle || 'Service Name'"></div>
                            <div class="text-gray-600 line-clamp-1 text-[9px]" x-text="scopeSubtitle || 'Scope Description'"></div>

                            {{-- Variety Specs Sub-badge (when active) --}}
                            <template x-if="showVarietyDetails">
                                <div class="mt-1 pt-1 border-t border-gray-200/50 flex items-center justify-between text-[8.5px]">
                                    <span class="font-semibold text-gray-700" x-text="selectedVariety || 'Devil Gala'"></span>
                                    <span class="text-gray-500" x-text="rootstock || 'M9/T337'"></span>
                                    <span class="font-mono text-gray-600" x-text="plantsPerKanal || '150/K'"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- 3. Itemized Deliverables Table Simulation --}}
                    <div class="rounded-lg overflow-hidden border border-gray-200 text-[10px]">
                        <table class="w-full text-left">
                            <thead class="text-white font-bold" :style="{ backgroundColor: accentColor }">
                                <tr>
                                    <th class="p-1.5">Deliverable Description</th>
                                    <th class="p-1.5 text-center">Unit</th>
                                    <th class="p-1.5 text-center">Qty</th>
                                    <th class="p-1.5 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <template x-for="(it, i) in items" :key="i">
                                    <tr>
                                        <td class="p-1.5 font-medium text-gray-800" x-text="it.name || ('Item #' + (i + 1))"></td>
                                        <td class="p-1.5 text-center text-gray-500" x-text="it.unit || '—'"></td>
                                        <td class="p-1.5 text-center text-gray-600 font-mono" x-text="it.qty || 1"></td>
                                        <td class="p-1.5 text-right font-bold text-gray-900 tabular-nums">₹<span x-text="formatMoney(lineTotal(it))"></span></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- 4. Totals Summary Bar --}}
                    <div class="flex justify-end text-[10px]">
                        <div class="w-56 space-y-1 bg-gray-50 rounded-xl p-2.5 border border-gray-200">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal:</span>
                                <span class="font-semibold text-gray-900">₹<span x-text="formatMoney(subtotal)"></span></span>
                            </div>
                            <div class="flex justify-between text-gray-600" x-show="discountTotal > 0">
                                <span>Discount:</span>
                                <span class="font-semibold text-red-600">-₹<span x-text="formatMoney(discountTotal)"></span></span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>GST:</span>
                                <span class="font-semibold text-gray-900">+₹<span x-text="formatMoney(gstTotal)"></span></span>
                            </div>
                            <div class="flex justify-between items-center pt-1 border-t border-gray-200 font-bold" :style="{ color: accentColor }">
                                <span class="text-[9px] uppercase tracking-wider">Grand Total:</span>
                                <span class="text-xs">₹<span x-text="formatMoney(grandTotal)"></span></span>
                            </div>
                        </div>
                    </div>

                    {{-- 5. Payment Schedule Stepper (Milestones) --}}
                    <div class="rounded-xl border border-gray-100 bg-gray-50/80 p-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-700 flex items-center gap-1">
                                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Payment Milestones
                            </span>
                            <span class="text-[9px] font-bold text-gray-400" x-text="milestones.length + ' Stages'"></span>
                        </div>
                        <div class="space-y-1">
                            <template x-for="(st, sidx) in milestones" :key="sidx">
                                <div class="flex items-center justify-between text-[9.5px]">
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.5 rounded text-[8px] font-bold text-white shadow-2xs"
                                              :style="{ backgroundColor: accentColor }"
                                              x-text="st.percent + '%'"></span>
                                        <span class="text-gray-700 line-clamp-1" x-text="st.stage || ('Stage ' + (sidx + 1))"></span>
                                    </div>
                                    <span class="font-bold text-gray-900" x-text="'₹ ' + milestoneAmount(st.percent)"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- 6. Project Package Inclusions Box (when active) --}}
                    <template x-if="showPackageInclusions">
                        <div class="rounded-xl border border-teal-200 bg-teal-50/40 p-2.5 space-y-1.5">
                            <div class="flex items-center justify-between text-[9px] font-bold text-teal-900 uppercase">
                                <span x-text="packageTitle || 'Per Kanal Standard Package'"></span>
                                <span class="text-teal-600">Package Inclusions</span>
                            </div>
                            <div class="grid grid-cols-3 gap-1.5 text-center text-[9px]">
                                <div class="bg-white rounded-lg p-1 border border-teal-100">
                                    <span class="text-gray-400 block text-[8px]">Poles</span>
                                    <strong class="text-gray-900" x-text="packagePoles"></strong>
                                </div>
                                <div class="bg-white rounded-lg p-1 border border-teal-100">
                                    <span class="text-gray-400 block text-[8px]">Anchors</span>
                                    <strong class="text-gray-900" x-text="packageAnchors"></strong>
                                </div>
                                <div class="bg-white rounded-lg p-1 border border-teal-100">
                                    <span class="text-gray-400 block text-[8px]">Plants</span>
                                    <strong class="text-gray-900" x-text="packagePlants"></strong>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- 7. Settlement Bank Preview --}}
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-2.5 flex items-center justify-between text-[9.5px]">
                        <div>
                            <span class="font-bold text-emerald-950 block">Bank Settlement Coordinates</span>
                            <span class="text-gray-600 font-mono text-[8.5px]">{{ $company['bank_name'] ?? '' }} • {{ $company['bank_account_no'] ?? '' }}</span>
                        </div>
                        <span class="font-mono text-emerald-800 font-bold text-[8.5px]">IFSC: {{ $company['bank_ifsc'] ?? '' }}</span>
                    </div>

                    {{-- 8. Additional Notes & Terms Previews (Collapsible / Compact) --}}
                    <div x-show="getNotesList().length > 0" class="rounded-xl border border-amber-200 bg-amber-50/40 p-2.5 text-[9px] text-amber-950 space-y-1">
                        <span class="font-bold block uppercase tracking-wider text-[8px] text-amber-800">Scope Notes</span>
                        <ul class="list-disc list-inside space-y-0.5">
                            <template x-for="(note, nIdx) in getNotesList()" :key="nIdx">
                                <li class="line-clamp-1" x-text="note"></li>
                            </template>
                        </ul>
                    </div>

                    <div x-show="getTermsList().length > 0" class="rounded-xl border border-gray-100 bg-gray-50/60 p-2.5 text-[9px] text-gray-600 space-y-1">
                        <span class="font-bold block uppercase tracking-wider text-[8px] text-gray-500">Terms &amp; Conditions</span>
                        <ol class="list-decimal list-inside space-y-0.5">
                            <template x-for="(term, tIdx) in getTermsList().slice(0, 3)" :key="tIdx">
                                <li class="line-clamp-1" x-text="term"></li>
                            </template>
                        </ol>
                        <span x-show="getTermsList().length > 3" class="text-[8px] text-gray-400 block italic">+ <span x-text="getTermsList().length - 3"></span> more terms on final document</span>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

@push('scripts')
<script>
function quotationForm() {
    return {
        services: @json($servicesJson),
        products: @json($productsJson),
        items: @json($initialItems),
        milestones: @json($initialMilestones),
        variations: @json($initialDefaults['variations'] ?? []),
        selectedVariationIndex: @json(old('variation_index', $matchedVariationIndex !== null ? (string)$matchedVariationIndex : '')),
        selectedServiceId: @json((string)$selectedSvcId),
        serviceType: @json($initialServiceType),
        accentColor: @json($initialDefaults['accent_color'] ?? '#064e3b'),
        documentTitle: @json($initialDefaults['document_title'] ?? 'PROFORMA INVOICE'),
        documentSubtitle: @json($initialDefaults['document_subtitle'] ?? ($selectedSvc?->description ?? 'PRICE ESTIMATE & QUOTATION')),
        showVarietyDetails: {{ $initialShowVariety ? 'true' : 'false' }},
        showPackageInclusions: {{ $initialShowPackage ? 'true' : 'false' }},
        showLivePreview: true,

        // Client info (bound for live document simulation)
        customerName: @json(old('customer_name', $leadData['customer_name'] ?? '')),
        customerPhone: @json(old('customer_phone', $leadData['customer_phone'] ?? '')),
        customerEmail: @json(old('customer_email', $leadData['customer_email'] ?? '')),
        customerArea: @json(old('customer_area', $leadData['customer_area'] ?? '')),
        customerAddress: @json(old('customer_address', $leadData['customer_address'] ?? '')),
        quotationDate: @json(old('date', date('Y-m-d'))),
        validUntil: @json(old('valid_until', date('Y-m-d', strtotime('+12 days')))),

        // Scope, Variety and Package specs
        selectedVariety: @json(old('variety_name', $leadData['variety_name'] ?? ($initialDefaults['variety_name'] ?? ''))),
        varietySpecification: @json(old('variety_specification', $leadData['variety_specification'] ?? ($leadData['variety_name'] ?? ($initialDefaults['variety_name'] ?? '')))),
        rootstock: @json(old('rootstock', $leadData['rootstock'] ?: ($initialDefaults['rootstock'] ?? ''))),
        plantsPerKanal: @json(old('plants_per_kanal', $leadData['plants_per_kanal'] ?: ($initialDefaults['plants_per_kanal'] ?? ''))),
        scopeTitle: @json(old('scope_title', $leadData['scope_title'] ?: ($initialDefaults['label'] ?? ($selectedSvc?->name ?? '')))),
        scopeSubtitle: @json(old('scope_subtitle', $leadData['scope_subtitle'] ?: ($initialDefaults['document_subtitle'] ?? ($selectedSvc?->description ?? '')))),
        packageTitle: @json(old('package_title', $initialDefaults['package_title'] ?? ($defaults['package_title'] ?? 'Per Kanal Standard Package'))),
        packagePoles: {{ (int) old('package_poles', $initialDefaults['package_poles'] ?? ($defaults['package_poles'] ?? 19)) }},
        packageAnchors: {{ (int) old('package_anchors', $initialDefaults['package_anchors'] ?? ($defaults['package_anchors'] ?? 6)) }},
        packagePlants: {{ (int) old('package_plants', $initialDefaults['package_plants'] ?? ($defaults['package_plants'] ?? 150)) }},
        additionalNotes: @json(old('additional_notes', $initialDefaults['additional_notes'] ?? ($defaults['additional_notes'] ?? ''))),
        terms: @json(old('terms', $initialDefaults['terms'] ?? ($defaults['terms'] ?? ''))),

        init() {
            if (this.selectedServiceId) {
                const svc = this.services.find(s => String(s.id) === String(this.selectedServiceId));
                if (svc) {
                    const defs = svc.defaults || {};
                    this.variations = (defs.variations && Array.isArray(defs.variations)) ? defs.variations : [];
                    if (!this.accentColor) this.accentColor = defs.accent_color || '#064e3b';
                    if (!this.documentTitle) this.documentTitle = defs.document_title || 'PROFORMA INVOICE';
                    if (!this.documentSubtitle) this.documentSubtitle = defs.document_subtitle || (svc.description || 'PRICE ESTIMATE & QUOTATION');
                    if (!this.terms) this.terms = defs.terms || '';
                    if (!this.additionalNotes) this.additionalNotes = defs.additional_notes || '';

                    if (this.selectedVariationIndex !== '' && this.variations[this.selectedVariationIndex]) {
                        const v = this.variations[this.selectedVariationIndex];
                        if (v.plants_per_kanal && !this.plantsPerKanal) this.plantsPerKanal = v.plants_per_kanal;
                        if (v.package_poles !== undefined && v.package_poles !== null && !this.packagePoles) this.packagePoles = v.package_poles;
                        if (v.package_anchors !== undefined && v.package_anchors !== null && !this.packageAnchors) this.packageAnchors = v.package_anchors;
                        if (v.package_plants !== undefined && v.package_plants !== null && !this.packagePlants) this.packagePlants = v.package_plants;
                        if (v.variety_name && !this.selectedVariety) {
                            this.selectedVariety = v.variety_name;
                            this.varietySpecification = v.variety_name;
                        }
                        if (v.rootstock && !this.rootstock) this.rootstock = v.rootstock;
                    }
                }
            }
        },

        onServiceChange(event) {
            const sId = event.target.value;
            this.selectedServiceId = sId;
            const svc = this.services.find(s => String(s.id) === String(sId));
            if (svc) {
                const defs = svc.defaults || {};
                this.serviceType = svc.quotation_type || 'general';
                this.accentColor = defs.accent_color || '#064e3b';
                this.documentTitle = defs.document_title || 'PROFORMA INVOICE';
                this.documentSubtitle = defs.document_subtitle || (svc.description || 'PRICE ESTIMATE & QUOTATION');
                this.showVarietyDetails = !!defs.show_variety;
                this.showPackageInclusions = !!defs.show_package;

                this.scopeTitle = svc.name;
                this.scopeSubtitle = defs.document_subtitle || svc.description || '';

                this.packageTitle = defs.package_title || (defs.show_package ? 'Per Kanal Standard Package' : '');
                this.packagePoles = (defs.package_poles !== undefined && defs.package_poles !== null) ? defs.package_poles : (defs.show_package ? 19 : 0);
                this.packageAnchors = (defs.package_anchors !== undefined && defs.package_anchors !== null) ? defs.package_anchors : (defs.show_package ? 6 : 0);
                this.packagePlants = (defs.package_plants !== undefined && defs.package_plants !== null) ? defs.package_plants : (defs.show_package ? 150 : 0);

                this.selectedVariety = defs.variety_name || '';
                this.varietySpecification = defs.variety_name || '';
                this.rootstock = defs.rootstock || '';
                this.plantsPerKanal = defs.plants_per_kanal || '';

                this.variations = (defs.variations && Array.isArray(defs.variations)) ? defs.variations : [];
                this.selectedVariationIndex = '';

                if (defs.payment_schedule && Array.isArray(defs.payment_schedule) && defs.payment_schedule.length > 0) {
                    this.milestones = JSON.parse(JSON.stringify(defs.payment_schedule));
                }
                this.additionalNotes = defs.additional_notes || '';
                this.terms = defs.terms || '';

                if (this.items.length === 1 && (!this.items[0].name || this.items[0].name === '' || this.isDefaultServiceName(this.items[0].name))) {
                    this.items[0].name = svc.name;
                    if (defs.base_price) {
                        this.items[0].rate = parseFloat(defs.base_price) || 0;
                    }
                    if (defs.unit) {
                        this.items[0].unit = defs.unit;
                    }
                }
            } else {
                this.serviceType = 'general';
                this.accentColor = '#064e3b';
                this.documentTitle = 'PROFORMA INVOICE';
                this.documentSubtitle = 'PRICE ESTIMATE & QUOTATION';
                this.showVarietyDetails = false;
                this.showPackageInclusions = false;
                this.variations = [];
                this.selectedVariationIndex = '';
            }
        },

        applyVariation(vIdx) {
            this.selectedVariationIndex = vIdx;
            if (vIdx === '' || vIdx === null || vIdx === undefined) return;
            const v = this.variations[vIdx];
            if (!v) return;

            if (v.plants_per_kanal !== undefined && v.plants_per_kanal !== null && v.plants_per_kanal !== '') {
                this.plantsPerKanal = v.plants_per_kanal;
            }
            if (v.package_poles !== undefined && v.package_poles !== null) {
                this.packagePoles = v.package_poles;
            }
            if (v.package_anchors !== undefined && v.package_anchors !== null) {
                this.packageAnchors = v.package_anchors;
            }
            if (v.package_plants !== undefined && v.package_plants !== null) {
                this.packagePlants = v.package_plants;
            }
            if (v.variety_name) {
                this.selectedVariety = v.variety_name;
                this.varietySpecification = v.variety_name;
            }
            if (v.rootstock) {
                this.rootstock = v.rootstock;
            }

            if (this.items.length > 0) {
                if (v.rate !== undefined && v.rate !== null && v.rate !== '') {
                    this.items[0].rate = parseFloat(v.rate) || 0;
                }
                if (v.unit) {
                    this.items[0].unit = v.unit;
                }
                const svc = this.services.find(s => String(s.id) === String(this.selectedServiceId));
                const svcName = svc ? svc.name : 'Service Deliverable';
                this.items[0].name = svcName + ' (' + v.name + ')';
            }
        },

        isDefaultServiceName(name) {
            return this.services.some(s => s.name === name) || name === 'Service Deliverable';
        },

        formatStyleName(t) {
            return {
                'orchard': 'High-Density Orchard Establishment Style',
                'plants': 'Plant Nursery & Booking Style',
                'installation': 'Netting & Trellis Infrastructure Style',
                'technical': 'Diagnostics & Laboratory Testing Style',
                'general': 'Standard Quotation Style'
            }[t] || 'Standard Quotation Style';
        },

        addItem() {
            this.items.push({
                product_id: '',
                name: '',
                unit: 'Kanal',
                qty: 1,
                rate: 0,
                discount: 0,
                gst_rate: 0
            });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        addMilestone() {
            this.milestones.push({
                percent: 0,
                stage: ''
            });
        },
        removeMilestone(index) {
            if (this.milestones.length > 1) {
                this.milestones.splice(index, 1);
            }
        },
        lineTaxable(item) {
            const base = (parseFloat(item.qty || 0) * parseFloat(item.rate || 0)) - parseFloat(item.discount || 0);
            return Math.max(0, base);
        },
        lineTax(item) {
            return this.lineTaxable(item) * (parseFloat(item.gst_rate || 0) / 100);
        },
        lineTotal(item) {
            return this.lineTaxable(item) + this.lineTax(item);
        },
        get subtotal() {
            return this.items.reduce((acc, it) => acc + this.lineTaxable(it), 0);
        },
        get discountTotal() {
            return this.items.reduce((acc, it) => acc + parseFloat(it.discount || 0), 0);
        },
        get gstTotal() {
            return this.items.reduce((acc, it) => acc + this.lineTax(it), 0);
        },
        get grandTotal() {
            return this.items.reduce((acc, it) => acc + this.lineTotal(it), 0);
        },
        milestoneAmount(pct) {
            const val = (this.grandTotal * (parseFloat(pct || 0) / 100));
            return this.formatMoney(val);
        },
        formatMoney(val) {
            return parseFloat(val || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        getNotesList() {
            if (!this.additionalNotes) return [];
            return this.additionalNotes.split('\n')
                .map(l => l.trim().replace(/^[•\-\*]\s*/, ''))
                .filter(l => l.length > 0);
        },
        getTermsList() {
            if (!this.terms) return [];
            return this.terms.split('\n')
                .map(l => l.trim().replace(/^\d+[\.\)]\s*/, ''))
                .filter(l => l.length > 0);
        }
    };
}
</script>
@endpush
@endsection
