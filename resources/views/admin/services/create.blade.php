@extends('admin.layout')

@section('page-title', 'Create Service')

@section('content')
    @php
        $defaultType = old('quotation_type', 'orchard');
        $initialDefs = $presets[$defaultType] ?? $service->getQuotationDefaults();
        $invoiceDefs = $service->getInvoiceDefaults();
    @endphp

    <div class="space-y-6" x-data="{
        activeTab: '{{ request('tab', 'basic') }}',
        presets: {{ json_encode($presets ?? []) }},
        quotationType: '{{ $defaultType }}',
        accentColor: '{{ old('accent_color', $initialDefs['accent_color'] ?? '#064e3b') }}',
        showVariety: {{ old('show_variety', $initialDefs['show_variety'] ?? true) ? 'true' : 'false' }},
        showPackage: {{ old('show_package', $initialDefs['show_package'] ?? true) ? 'true' : 'false' }},
        quotationTitle: '{{ old('quotation_title', $initialDefs['document_title'] ?? 'PROFORMA INVOICE') }}',
        quotationSubtitle: '{{ old('quotation_subtitle', $initialDefs['document_subtitle'] ?? 'PRICE ESTIMATE & QUOTATION') }}',
        hasUnit: {{ old('has_unit', $initialDefs['has_unit'] ?? true) ? 'true' : 'false' }},
        basePrice: '{{ old('base_price', $initialDefs['base_price'] ?? 185000) }}',
        unit: '{{ old('unit', $initialDefs['unit'] ?? 'Kanal') }}',
        packageTitle: '{{ old('package_title', $initialDefs['package_title'] ?? 'Per Kanal Standard Package') }}',
        plantsPerKanal: '{{ old('plants_per_kanal', $initialDefs['plants_per_kanal'] ?? '150 (Standard)') }}',
        packagePoles: '{{ old('package_poles', $initialDefs['package_poles'] ?? 19) }}',
        packageAnchors: '{{ old('package_anchors', $initialDefs['package_anchors'] ?? 6) }}',
        packagePlants: '{{ old('package_plants', $initialDefs['package_plants'] ?? 150) }}',
        varietyName: '{{ old('variety_name', $initialDefs['variety_name'] ?? 'Devil Gala') }}',
        rootstock: '{{ old('rootstock', $initialDefs['rootstock'] ?? 'M9 / T337 (High Density)') }}',
        variations: {{ json_encode(old('variations', $initialDefs['variations'] ?? [])) }},
        milestones: {{ json_encode(old('payment_schedule', $initialDefs['payment_schedule'] ?? [])) }},
        additionalNotes: {{ json_encode(old('additional_notes', $initialDefs['additional_notes'] ?? '')) }},
        quotationTerms: {{ json_encode(old('quotation_terms', $initialDefs['terms'] ?? '')) }},

        applyPreset(type) {
            this.quotationType = type;
            const p = this.presets[type];
            if (!p) return;
            this.accentColor = p.accent_color || '#064e3b';
            this.showVariety = !!p.show_variety;
            this.showPackage = !!p.show_package;
            this.hasUnit = p.has_unit !== false;
            this.quotationTitle = p.document_title || 'PROFORMA INVOICE';
            this.quotationSubtitle = p.document_subtitle || '';
            this.basePrice = p.base_price !== null && p.base_price !== undefined ? p.base_price : '';
            this.unit = p.unit || 'Kanal';
            this.packageTitle = p.package_title || '';
            this.plantsPerKanal = p.plants_per_kanal || '';
            this.packagePoles = p.package_poles || '';
            this.packageAnchors = p.package_anchors || '';
            this.packagePlants = p.package_plants || '';
            this.varietyName = p.variety_name || '';
            this.rootstock = p.rootstock || '';
            this.variations = JSON.parse(JSON.stringify(p.variations || []));
            this.milestones = JSON.parse(JSON.stringify(p.payment_schedule || []));
            this.additionalNotes = p.additional_notes || '';
            this.quotationTerms = p.terms || '';
        },
        addVariation() {
            this.variations.push({
                name: '',
                plants_per_kanal: this.plantsPerKanal || '150',
                package_poles: this.packagePoles || 19,
                package_anchors: this.packageAnchors || 6,
                package_plants: this.packagePlants || 150,
                rate: this.basePrice || 185000,
                unit: this.unit || 'Kanal',
                rootstock: this.rootstock || 'M9 / T337',
                variety_name: this.varietyName || 'Devil Gala'
            });
        },
        removeVariation(idx) {
            this.variations.splice(idx, 1);
        },
        addMilestone() {
            this.milestones.push({ percent: 10, stage: '' });
        },
        removeMilestone(idx) {
            this.milestones.splice(idx, 1);
        },
        get totalPercent() {
            return this.milestones.reduce((sum, m) => sum + (parseFloat(m.percent) || 0), 0);
        }
    }">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Create Service</h2>
                <p class="text-xs text-gray-500 mt-0.5">Define core details, quotation pricing, density variations, and invoice defaults.</p>
            </div>
            <x-admin.button href="{{ route('admin.services.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>
                Back
            </x-admin.button>
        </div>

        {{-- Tab Navigation --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-2 py-1.5">
            <nav class="flex gap-1.5 overflow-x-auto" aria-label="Service create tabs">
                <button type="button" @click="activeTab = 'basic'"
                    :class="activeTab === 'basic' ? 'bg-brand-50 text-brand-700 border-brand-200 shadow-2xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-transparent'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-xl border transition-all whitespace-nowrap cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>1. Basic Details & Media</span>
                </button>

                <button type="button" @click="activeTab = 'quotation'"
                    :class="activeTab === 'quotation' ? 'bg-brand-50 text-brand-700 border-brand-200 shadow-2xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-transparent'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-xl border transition-all whitespace-nowrap cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>2. Quotation Defaults, Package Inclusions & Variations</span>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-brand-100 text-brand-800" x-text="variations.length + ' Variations'"></span>
                </button>

                <button type="button" @click="activeTab = 'invoice'"
                    :class="activeTab === 'invoice' ? 'bg-brand-50 text-brand-700 border-brand-200 shadow-2xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-transparent'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-xl border transition-all whitespace-nowrap cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8h4m-4 4h4"/></svg>
                    <span>3. Invoice Defaults</span>
                </button>
            </nav>
        </div>

        <form action="{{ route('admin.services.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <input type="hidden" name="tab" x-model="activeTab">
            <input type="hidden" name="has_quotation_settings" value="1">

            {{-- ======================================================== --}}
            {{-- TAB 1: BASIC INFORMATION & MEDIA                         --}}
            {{-- ======================================================== --}}
            <div x-show="activeTab === 'basic'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-0.5">Basic Information</h3>
                        <p class="text-sm text-gray-500">Core details about the service.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.input name="name" label="Service Name" :value="old('name')" placeholder="e.g. Book an Orchard, Soil Health Management" required />
                        <x-admin.input name="category" label="Category Slug" :value="old('category')" placeholder="e.g. orchard-development, soil-health-management" required />
                    </div>
                    <div>
                        <x-admin.textarea name="description" label="Short Description" :value="old('description')" rows="3" placeholder="Brief summary of what this service delivers..." />
                    </div>
                    <div>
                        <x-admin.textarea name="content" label="Full Service Content / Overview" :value="old('content')" rows="5" placeholder="Detailed service description, process, deliverables..." />
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-0.5">Media & Links</h3>
                        <p class="text-sm text-gray-500">Service photography, iconography, and booking URL.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.input name="icon" label="Icon Name or Class" :value="old('icon')" placeholder="e.g. apple, seed, drop, or svg icon" />
                        <x-admin.input name="book_url" label="Booking URL (Optional)" :value="old('book_url')" placeholder="https://..." />
                    </div>
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-1.5">Featured Image</label>
                        <input type="file" name="image" id="image" accept="image/*"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        <p class="mt-1.5 text-xs text-gray-400">PNG, JPG, or WebP. Max 5MB.</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <h3 class="text-lg font-bold text-gray-900 mb-0.5">Display Settings</h3>
                    <p class="text-sm text-gray-500">Control the display order and visibility of this service.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-start">
                        <x-admin.input name="sort_order" label="Sort Order" type="number" :value="old('sort_order', 0)" />
                        <div class="space-y-4 pt-1">
                            <x-admin.checkbox name="is_active" label="Active" :checked="old('is_active', true)" help="Visible on the frontend when active." />
                            <x-admin.checkbox name="creates_orchard_on_completion" label="Creates Orchard on Completion" :checked="old('creates_orchard_on_completion', false)" help="Automatically establishes a verified orchard profile when work order is completed." />
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center bg-gray-50 rounded-2xl p-4 border border-gray-200">
                    <span class="text-xs text-gray-500">Tip: Continue to Quotation & Package Pricing next.</span>
                    <button type="button" @click="activeTab = 'quotation'" class="px-4 py-2 rounded-xl bg-brand-600 text-white font-semibold text-xs transition shadow-2xs hover:bg-brand-700">
                        Next: Quotation & Pricing &rarr;
                    </button>
                </div>
            </div>

            {{-- ======================================================== --}}
            {{-- TAB 2: QUOTATION & PACKAGE PRICING                       --}}
            {{-- ======================================================== --}}
            <div x-show="activeTab === 'quotation'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">

                {{-- Preset Selection Bar --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Quick Template Presets</h3>
                        <p class="text-xs text-gray-500">Pick a pre-configured template to auto-fill pricing units, density variations, and package parameters.</p>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-6 gap-3">
                        @foreach(['orchard' => '🍎 High-Density Orchard', 'plants' => '🌱 Plant Booking', 'installation' => '🏗️ Trellis / Netting', 'technical' => '🧪 Soil / Lab Test', 'call' => '📞 Book a Call', 'general' => '⚙️ Custom / Other'] as $pKey => $pLabel)
                            <button type="button" @click="applyPreset('{{ $pKey }}')"
                                     class="p-3 text-left rounded-xl border transition-all shadow-2xs cursor-pointer"
                                     :class="quotationType === '{{ $pKey }}' ? 'bg-brand-50 border-brand-500 ring-2 ring-brand-300' : 'bg-gray-50 border-gray-200 hover:bg-gray-100'">
                                <div class="text-xs font-bold text-gray-900">{{ $pLabel }}</div>
                                <div class="text-[10px] text-gray-500 uppercase mt-0.5 tracking-wider">{{ $pKey }}</div>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Quotation Header & Toggles --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Document Header Title</label>
                            <input type="text" name="quotation_title" x-model="quotationTitle" class="w-full text-sm font-semibold rounded-xl border-gray-200 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Document Subtitle</label>
                            <input type="text" name="quotation_subtitle" x-model="quotationSubtitle" class="w-full text-sm rounded-xl border-gray-200 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Theme Accent Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" name="accent_color" x-model="accentColor" class="w-10 h-10 rounded-xl border border-gray-200 p-1 cursor-pointer">
                                <input type="text" x-model="accentColor" class="flex-1 text-sm font-mono rounded-xl border-gray-200 py-2 uppercase">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-6 pt-2 border-t border-gray-100 flex-wrap">
                        <label class="flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer">
                            <input type="checkbox" name="show_variety" value="1" x-model="showVariety" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span class="font-semibold text-gray-800">Display Apple Variety & Rootstock block on Quotation</span>
                        </label>
                        <label class="flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer">
                            <input type="checkbox" name="show_package" value="1" x-model="showPackage" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span class="font-semibold text-gray-800">Display Package Inclusions box (Poles / Anchors / Plants)</span>
                        </label>
                    </div>
                </div>

                {{-- Default Pricing & Specifications --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Base Pricing & Standard Specifications</h3>
                            <p class="text-xs text-gray-500">Default rate and unit prefilled into quotation line items when no variation is selected.</p>
                        </div>
                    </div>

                    {{-- Measurement Unit / Area Requirement Toggle --}}
                    <div class="p-4 rounded-xl border transition-all"
                         :class="hasUnit ? 'bg-emerald-50/50 border-emerald-200' : 'bg-amber-50/60 border-amber-200'">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="hidden" name="has_unit" value="0">
                            <input type="checkbox" name="has_unit" value="1" x-model="hasUnit"
                                   class="mt-0.5 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <div>
                                <span class="text-xs font-bold text-gray-900">Require Measurement Unit & Area in Booking Form (e.g. Kanals, Plants, Meters)</span>
                                <p class="text-[11px] text-gray-500 mt-0.5">
                                    When enabled, the customer booking form asks for land area or quantity. <strong class="text-amber-800">Uncheck this for services like "Book a Call", "Advisory", or "Consultation"</strong> where the client only needs to submit contact details without specifying an area or unit.
                                </p>
                            </div>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Base Price / Rate (₹)</label>
                            <input type="number" step="0.01" name="base_price" x-model="basePrice" placeholder="185000" class="w-full text-sm font-bold text-gray-900 rounded-xl border-gray-200 py-2 bg-gray-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold mb-1" :class="hasUnit ? 'text-emerald-800' : 'text-gray-500'">
                                Pricing Unit <span class="text-red-500" x-show="hasUnit">*</span>
                            </label>
                            <div x-show="hasUnit">
                                <input type="text" name="unit" x-model="unit" placeholder="Kanal, Acre, Meter, Sample, Job" class="w-full text-sm font-bold rounded-xl border-emerald-300 bg-emerald-50/40 text-emerald-950 py-2 focus:border-emerald-500 focus:ring-emerald-500">
                                <span class="text-[10px] text-gray-400">Used for customer requirement & quotation Qty</span>
                            </div>
                            <div x-show="!hasUnit" x-cloak>
                                <div class="px-3.5 py-2 rounded-xl bg-gray-100 border border-gray-200 text-xs font-semibold text-gray-600 flex items-center justify-between">
                                    <span>No Unit (Fixed Service / Book a Call)</span>
                                    <span class="text-[10px] font-mono uppercase bg-gray-200 px-1.5 py-0.5 rounded text-gray-700">Disabled</span>
                                </div>
                                <input type="hidden" name="unit" value="Fixed" :disabled="hasUnit">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Package Title</label>
                            <input type="text" name="package_title" x-model="packageTitle" placeholder="Per Kanal Standard Package" class="w-full text-sm rounded-xl border-gray-200 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Plants / Kanal (Default)</label>
                            <input type="text" name="plants_per_kanal" x-model="plantsPerKanal" placeholder="150 (Standard)" class="w-full text-sm rounded-xl border-gray-200 py-2">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-5 gap-3 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Poles / Unit</label>
                            <input type="number" name="package_poles" x-model="packagePoles" placeholder="19" class="w-full text-xs rounded-xl border-gray-200 py-1.5">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Anchors / Unit</label>
                            <input type="number" name="package_anchors" x-model="packageAnchors" placeholder="6" class="w-full text-xs rounded-xl border-gray-200 py-1.5">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Plants / Unit</label>
                            <input type="number" name="package_plants" x-model="packagePlants" placeholder="150" class="w-full text-xs rounded-xl border-gray-200 py-1.5">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Default Variety</label>
                            <input type="text" name="variety_name" x-model="varietyName" placeholder="Devil Gala" class="w-full text-xs rounded-xl border-gray-200 py-1.5">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Default Rootstock</label>
                            <input type="text" name="rootstock" x-model="rootstock" placeholder="M9 / T337" class="w-full text-xs rounded-xl border-gray-200 py-1.5">
                        </div>
                    </div>
                </div>

                {{-- Package Variations & Pricing --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Package Variations &amp; Pricing</h3>
                            <p class="text-xs text-gray-500">Define density presets (e.g. 150 vs 170 plants/kanal, or standard vs premium) with individual rates and units.</p>
                        </div>
                        <button type="button" @click="addVariation()" class="px-3.5 py-1.5 rounded-xl bg-brand-50 hover:bg-brand-100 text-brand-700 text-xs font-bold transition inline-flex items-center gap-1.5 border border-brand-200 shadow-2xs cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Add Variation Preset</span>
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(v, vIdx) in variations" :key="vIdx">
                            <div class="rounded-2xl border border-gray-200 bg-gray-50/70 p-4 space-y-3 shadow-2xs">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5 flex-1">
                                        <span class="w-6 h-6 rounded-full bg-brand-600 text-white text-xs font-bold flex items-center justify-center shrink-0" x-text="vIdx + 1"></span>
                                        <input type="text" :name="'variations[' + vIdx + '][name]'" x-model="v.name" placeholder="Variation Name (e.g. 150 Plants / Kanal (Standard High Density))" required class="flex-1 text-xs font-bold rounded-xl border-gray-200 py-2 bg-white">
                                    </div>
                                    <button type="button" @click="removeVariation(vIdx)" class="p-1.5 text-red-500 hover:bg-red-50 rounded-xl transition" title="Remove Variation">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-6 gap-3">
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-700 mb-0.5">Rate (₹)</label>
                                        <input type="number" step="0.01" :name="'variations[' + vIdx + '][rate]'" x-model.number="v.rate" placeholder="185000" class="w-full text-xs font-bold text-gray-900 rounded-xl border-gray-200 py-1.5 px-2.5 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-emerald-800 mb-0.5">Unit</label>
                                        <input type="text" :name="'variations[' + vIdx + '][unit]'" x-model="v.unit" placeholder="Kanal" class="w-full text-xs font-semibold rounded-xl border-emerald-300 bg-white py-1.5 px-2.5">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-gray-500 mb-0.5">Plants / Kanal</label>
                                        <input type="text" :name="'variations[' + vIdx + '][plants_per_kanal]'" x-model="v.plants_per_kanal" placeholder="150" class="w-full text-xs rounded-xl border-gray-200 py-1.5 px-2.5 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-gray-500 mb-0.5">Poles</label>
                                        <input type="number" :name="'variations[' + vIdx + '][package_poles]'" x-model.number="v.package_poles" placeholder="19" class="w-full text-xs rounded-xl border-gray-200 py-1.5 px-2.5 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-gray-500 mb-0.5">Anchors</label>
                                        <input type="number" :name="'variations[' + vIdx + '][package_anchors]'" x-model.number="v.package_anchors" placeholder="6" class="w-full text-xs rounded-xl border-gray-200 py-1.5 px-2.5 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-gray-500 mb-0.5">Plants Count</label>
                                        <input type="number" :name="'variations[' + vIdx + '][package_plants]'" x-model.number="v.package_plants" placeholder="150" class="w-full text-xs rounded-xl border-gray-200 py-1.5 px-2.5 bg-white">
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div x-show="variations.length === 0" class="p-6 rounded-2xl border border-dashed border-gray-200 text-center text-xs text-gray-400 bg-gray-50/50">
                            No package variations configured yet. Click "+ Add Variation Preset" to define package options for this service.
                        </div>
                    </div>
                </div>

                {{-- Milestone Payment Stepper --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Milestone Payment Stepper</h3>
                            <p class="text-xs text-gray-500">Default payment schedule stages and percentages prefilled on proforma quotations.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold"
                                  :class="totalPercent === 100 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'">
                                Total: <span x-text="totalPercent"></span>%
                            </span>
                            <button type="button" @click="addMilestone()" class="px-3 py-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition cursor-pointer">
                                + Add Stage
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2.5">
                        <template x-for="(step, idx) in milestones" :key="idx">
                            <div class="flex items-center gap-2.5 p-2.5 rounded-xl border border-gray-200 bg-gray-50/60">
                                <span class="w-6 text-center text-xs font-bold text-gray-400" x-text="idx + 1"></span>
                                <div class="w-24 shrink-0">
                                    <div class="relative">
                                        <input type="number" :name="'payment_schedule[' + idx + '][percent]'" x-model.number="step.percent" min="1" max="100" class="w-full text-xs rounded-xl border-gray-200 py-1.5 pr-6 font-bold text-gray-800 bg-white">
                                        <span class="absolute right-2 top-1.5 text-xs text-gray-400 font-bold">%</span>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <input type="text" :name="'payment_schedule[' + idx + '][stage]'" x-model="step.stage" placeholder="e.g. Advance at the time of booking" class="w-full text-xs rounded-xl border-gray-200 py-1.5 bg-white">
                                </div>
                                <button type="button" @click="removeMilestone(idx)" class="p-1.5 text-red-500 hover:bg-red-50 rounded-xl transition" title="Remove stage">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Quotation Remarks & Terms --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <h3 class="text-base font-bold text-gray-900">Quotation Remarks & Terms</h3>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Service Scope Notes (Printed on Quotation)</label>
                        <textarea name="additional_notes" x-model="additionalNotes" rows="2" class="w-full text-xs rounded-xl border-gray-200 focus:border-brand-500 focus:ring-brand-500 py-2"></textarea>
                        <span class="text-[10px] text-gray-400">e.g. Extra anchors ₹ 2,500/anchor, extra plants ₹ 1,230/plant.</span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Service Terms & Conditions</label>
                        <textarea name="quotation_terms" x-model="quotationTerms" rows="3" class="w-full text-xs rounded-xl border-gray-200 focus:border-brand-500 focus:ring-brand-500 py-2"></textarea>
                    </div>
                </div>
            </div>

            {{-- ======================================================== --}}
            {{-- TAB 3: INVOICE SETTINGS                                  --}}
            {{-- ======================================================== --}}
            <div x-show="activeTab === 'invoice'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-0.5">Invoice Defaults for this Service</h3>
                        <p class="text-sm text-gray-500">Configure dedicated document headers, numbering prefix, and terms when invoices are issued for this service.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Invoice Prefix (Optional)</label>
                            <input type="text" name="invoice_prefix" value="{{ old('invoice_prefix') }}" placeholder="e.g. INV-ORC" class="w-full text-sm font-mono rounded-xl border-gray-200 py-2">
                            <span class="text-[10px] text-gray-400">Defaults to global prefix ({{ config('invoice.prefix', 'PTA') }}) if empty.</span>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Invoice Document Title</label>
                            <input type="text" name="invoice_title" value="{{ old('invoice_title', $invoiceDefs['document_title'] ?? 'TAX INVOICE') }}" placeholder="TAX INVOICE" class="w-full text-sm rounded-xl border-gray-200 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Invoice Subtitle</label>
                            <input type="text" name="invoice_subtitle" value="{{ old('invoice_subtitle') }}" placeholder="Leave blank to use Service Name" class="w-full text-sm rounded-xl border-gray-200 py-2">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Invoice Terms & Conditions</label>
                        <textarea name="invoice_terms" rows="4" placeholder="Specific terms to print on invoices for this service..." class="w-full text-xs rounded-xl border-gray-200 py-2">{{ old('invoice_terms') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Floating / Fixed Footer Actions --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Workflow stages & material items can be added immediately after saving this service.</span>
                </div>
                <div class="flex items-center gap-3">
                    <x-admin.button href="{{ route('admin.services.index') }}" variant="secondary">Cancel</x-admin.button>
                    <x-admin.button type="submit" variant="primary">
                        Save &amp; Create Service
                    </x-admin.button>
                </div>
            </div>
        </form>
    </div>
@endsection
