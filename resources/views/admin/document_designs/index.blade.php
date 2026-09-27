@extends('admin.layout')

@section('page-title', 'Invoices & Quotations Designer')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: '{{ request('tab', $activeTab) }}',
    services: {{ json_encode($servicesData) }},
    selectedServiceId: {{ $selectedService?->id ?? 'null' }},
    get activeService() {
        return this.services.find(s => s.id === this.selectedServiceId) || this.services[0] || null;
    }
}">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-2xl bg-brand-50 text-brand-700 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Invoices & Quotations Designer</h2>
                    <p class="text-xs text-gray-500">Configure numbering prefixes, global document layouts, and unique quotation/invoice styling linked to each service.</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.quotations.index') }}" class="px-3.5 py-2 rounded-xl border border-gray-200 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50 transition inline-flex items-center gap-1.5 shadow-2xs">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Quotations</span>
            </a>
            <a href="{{ route('admin.invoices.index') }}" class="px-3.5 py-2 rounded-xl border border-gray-200 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50 transition inline-flex items-center gap-1.5 shadow-2xs">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <span>Invoices</span>
            </a>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-2 py-1">
        <nav class="flex gap-1 overflow-x-auto" aria-label="Designer tabs">
            <button type="button" @click="activeTab = 'prefixes'"
                :class="activeTab === 'prefixes' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                Prefixes & Numbering
            </button>

            <button type="button" @click="activeTab = 'quotations'"
                :class="activeTab === 'quotations' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Quotation Global Design
            </button>

            <button type="button" @click="activeTab = 'invoices'"
                :class="activeTab === 'invoices' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8h4m-4 4h4"/></svg>
                Invoice Global Design
            </button>

            <button type="button" @click="activeTab = 'services'"
                :class="activeTab === 'services' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>Service-Linked Styling</span>
                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-brand-100 text-brand-800">Per Service</span>
            </button>
        </nav>
    </div>

    {{-- TAB 1: Prefixes & Numbering --}}
    <div x-show="activeTab === 'prefixes'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
        <form action="{{ route('admin.document-settings.prefixes.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- 1. Numbering Sequences & Formats --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Document Numbering & Sequences</h3>
                        <p class="text-sm text-gray-500">Configure invoice prefixes, quotation numbering codes, fiscal years, and payment grace periods.</p>
                    </div>
                </div>

                <div class="mt-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Invoice Numbering Card --}}
                        <div class="rounded-2xl border border-gray-200 bg-gray-50/50 p-5 space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-700">Tax Invoice Numbering</span>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-mono font-bold bg-blue-100 text-blue-800">
                                    {{ $invoiceSettings['prefix'] }}/{{ date('m') >= 4 ? date('Y').'-'.date('y', strtotime('+1 year')) : date('Y', strtotime('-1 year')).'-'.date('y') }}/0001
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <x-admin.input name="invoice_prefix" label="Invoice Prefix" :value="$invoiceSettings['prefix']" placeholder="PTA" required helptext="e.g. PTA or PTA/INV" />
                                <x-admin.input name="invoice_due_days" label="Default Due Days" type="number" :value="$invoiceSettings['due_days']" placeholder="15" required helptext="Default payment grace period." />
                            </div>
                            <p class="text-xs text-gray-500">Generated pattern: <strong class="font-mono text-gray-800">[PREFIX]/[FINANCIAL_YEAR]/[0001]</strong> with automatic sequence increments.</p>
                        </div>

                        {{-- Quotation Numbering Card --}}
                        <div class="rounded-2xl border border-gray-200 bg-gray-50/50 p-5 space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-700">Quotation / Proforma Numbering</span>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-mono font-bold bg-emerald-100 text-emerald-800">
                                    {{ $quotationSettings['prefix'] }}/{{ date('m') >= 4 ? date('Y').'-'.date('y', strtotime('+1 year')) : date('Y', strtotime('-1 year')).'-'.date('y') }}/0001
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <x-admin.input name="quotation_prefix" label="Quotation Prefix" :value="$quotationSettings['prefix']" placeholder="QT" required helptext="e.g. QT or PTA/QT" />
                                <x-admin.input name="quotation_validity_days" label="Default Validity (Days)" type="number" :value="$quotationSettings['validity_days']" placeholder="15" required helptext="Quote expiry window." />
                            </div>
                            <p class="text-xs text-gray-500">Generated pattern: <strong class="font-mono text-gray-800">[PREFIX]/[FINANCIAL_YEAR]/[0001]</strong> automatically tracked per estimate.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Company Letterhead Identity --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Company Letterhead & Billing Identity</h3>
                        <p class="text-sm text-gray-500">Official business information printed at the top of every generated Quotation, Proforma, and Invoice.</p>
                    </div>
                </div>

                <div class="mt-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.input name="company_name" label="Company Legal Name" :value="$companySettings['company_name']" required />
                        <x-admin.input name="company_gst_no" label="GSTIN / Tax ID" :value="$companySettings['company_gst_no']" placeholder="e.g. 01ABCDE1234F1Z5" helptext="Required for official GST compliance." />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.input name="company_tagline" label="Company Brand Tagline" :value="$companySettings['company_tagline']" placeholder="Complete Orchard Solution" />
                        <x-admin.input name="company_slogan" label="Company Mission Slogan" :value="$companySettings['company_slogan']" placeholder="From Planning to Plantation We Build Better Orchards." />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <x-admin.input name="company_phone" label="Helpline / Contact Phone" :value="$companySettings['company_phone']" />
                        <x-admin.input name="company_email" label="Official Contact Email" type="email" :value="$companySettings['company_email']" />
                        <x-admin.input name="company_website" label="Website URL" :value="$companySettings['company_website']" placeholder="www.planttechagro.com" />
                    </div>

                    <div>
                        <x-admin.textarea name="company_address" label="Registered Office Address" :value="$companySettings['company_address']" rows="2" helptext="Printed on all official letters, invoices, and quotation headers." />
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Save Prefixes & Letterhead</x-admin.button>
            </div>
        </form>
    </div>

    {{-- TAB 2: Quotation Global Design --}}
    <div x-show="activeTab === 'quotations'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="space-y-6">
        <form action="{{ route('admin.document-settings.quotations.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- 1. Style Selection --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Global Quotation Layout Style</h3>
                <p class="text-sm text-gray-500 mb-5">Choose the master layout preset used for client price estimates and proformas.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <label class="relative cursor-pointer group">
                        <input type="radio" name="quotation_design_style" value="orchard_proforma" {{ $quotationSettings['design_style'] === 'orchard_proforma' ? 'checked' : '' }} class="peer sr-only">
                        <div class="peer-checked:ring-2 peer-checked:ring-brand-600 peer-checked:ring-offset-2 rounded-2xl p-5 border border-gray-200 hover:border-gray-300 transition h-full flex flex-col justify-between">
                            <div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-brand-100 text-brand-800 uppercase tracking-wider">Recommended</span>
                                <h4 class="font-bold text-gray-900 text-sm mt-2">High-Density Agritech Proforma</h4>
                                <p class="text-xs text-gray-500 mt-1">Full professional layout featuring Variety badge, Package inclusions, 3-stage payment stepper, and bank coordinates.</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-brand-700 font-semibold">
                                <span>Plant Tech Agro Standard</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>
                    </label>

                    <label class="relative cursor-pointer group">
                        <input type="radio" name="quotation_design_style" value="modern_clean" {{ $quotationSettings['design_style'] === 'modern_clean' ? 'checked' : '' }} class="peer sr-only">
                        <div class="peer-checked:ring-2 peer-checked:ring-brand-600 peer-checked:ring-offset-2 rounded-2xl p-5 border border-gray-200 hover:border-gray-300 transition h-full flex flex-col justify-between">
                            <div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700 uppercase tracking-wider">Minimal</span>
                                <h4 class="font-bold text-gray-900 text-sm mt-2">Modern Minimalist Clean</h4>
                                <p class="text-xs text-gray-500 mt-1">Streamlined table structure, clean typography, compact milestones, ideal for fast consultation quotes.</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500 font-medium">
                                <span>Fast Reading</span>
                            </div>
                        </div>
                    </label>

                    <label class="relative cursor-pointer group">
                        <input type="radio" name="quotation_design_style" value="classic_executive" {{ $quotationSettings['design_style'] === 'classic_executive' ? 'checked' : '' }} class="peer sr-only">
                        <div class="peer-checked:ring-2 peer-checked:ring-brand-600 peer-checked:ring-offset-2 rounded-2xl p-5 border border-gray-200 hover:border-gray-300 transition h-full flex flex-col justify-between">
                            <div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 uppercase tracking-wider">Formal</span>
                                <h4 class="font-bold text-gray-900 text-sm mt-2">Classic Executive</h4>
                                <p class="text-xs text-gray-500 mt-1">Structured bordered boxes, formal header styling, traditional government and institutional tender look.</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500 font-medium">
                                <span>Institutional</span>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- 2. Titles & Colors --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Titles & Accent Theme</h3>
                <p class="text-sm text-gray-500 mb-5">Primary badge labels and color accents applied to print headings and totals.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <x-admin.input name="quotation_document_title" label="Header Document Title" :value="$quotationSettings['document_title']" placeholder="PROFORMA INVOICE" required helptext="Large bold badge at the top right." />
                    <x-admin.input name="quotation_document_subtitle" label="Header Subtitle" :value="$quotationSettings['document_subtitle']" placeholder="PRICE ESTIMATE & QUOTATION" helptext="Appears right under document title." />
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Primary Accent Color</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="quotation_accent_color" value="{{ $quotationSettings['accent_color'] }}" class="w-12 h-10 rounded-xl border border-gray-200 p-1 cursor-pointer">
                            <span class="text-xs font-mono font-bold text-gray-700">{{ $quotationSettings['accent_color'] }}</span>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">Used for header banners, grand total bar, and milestone pills.</p>
                    </div>
                </div>
            </div>

            {{-- 3. Component Toggles --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Section Visibility Controls</h3>
                <p class="text-sm text-gray-500 mb-5">Toggle standard quotation elements on or off for print and PDF generation.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <x-admin.checkbox name="quotation_show_logo" label="Show Company Logo" :checked="$quotationSettings['show_logo']" />
                    <x-admin.checkbox name="quotation_show_address" label="Show Company Address" :checked="$quotationSettings['show_address']" />
                    <x-admin.checkbox name="quotation_show_specs" label="Show Technical Specs Block" :checked="$quotationSettings['show_specs']" />
                    <x-admin.checkbox name="quotation_show_package" label="Show Package Inclusions" :checked="$quotationSettings['show_package']" />
                    <x-admin.checkbox name="quotation_show_schedule" label="Show Milestone Schedule" :checked="$quotationSettings['show_schedule']" />
                    <x-admin.checkbox name="quotation_show_bank_details" label="Show Settlement Bank Details" :checked="$quotationSettings['show_bank_details']" />
                    <x-admin.checkbox name="quotation_show_terms" label="Show Terms & Conditions" :checked="$quotationSettings['show_terms']" />
                    <x-admin.checkbox name="quotation_show_signatory" label="Show Authorized Signatory Box" :checked="$quotationSettings['show_signatory']" />
                </div>
            </div>

            {{-- 4. Terms & Notes --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                <x-admin.textarea name="quotation_additional_notes" label="Default Additional Pricing Notes" :value="$quotationSettings['additional_notes']" rows="3" helptext="e.g. Extra anchor and extra plant charges." />
                <x-admin.textarea name="quotation_terms" label="Default Terms & Conditions" :value="$quotationSettings['terms']" rows="4" helptext="Standard quotation legal clauses printed at the bottom." />
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Save Quotation Design</x-admin.button>
            </div>
        </form>
    </div>

    {{-- TAB 3: Invoice Global Design --}}
    <div x-show="activeTab === 'invoices'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="space-y-6">
        <form action="{{ route('admin.document-settings.invoices.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- 1. Style Selection --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Global Invoice Layout Style</h3>
                <p class="text-sm text-gray-500 mb-5">Select the visual format for client tax invoices and receipts.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <label class="relative cursor-pointer group">
                        <input type="radio" name="invoice_design_style" value="tax_invoice" {{ $invoiceSettings['design_style'] === 'tax_invoice' ? 'checked' : '' }} class="peer sr-only">
                        <div class="peer-checked:ring-2 peer-checked:ring-brand-600 peer-checked:ring-offset-2 rounded-2xl p-5 border border-gray-200 hover:border-gray-300 transition h-full flex flex-col justify-between">
                            <div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider">Standard</span>
                                <h4 class="font-bold text-gray-900 text-sm mt-2">GST Tax Invoice (Official Format)</h4>
                                <p class="text-xs text-gray-500 mt-1">Includes GST breakdown, itemized rate and quantity table, balance due banner, and bank settlement box.</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-emerald-700 font-semibold">
                                <span>Compliant</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>
                    </label>

                    <label class="relative cursor-pointer group">
                        <input type="radio" name="invoice_design_style" value="modern_compact" {{ $invoiceSettings['design_style'] === 'modern_compact' ? 'checked' : '' }} class="peer sr-only">
                        <div class="peer-checked:ring-2 peer-checked:ring-brand-600 peer-checked:ring-offset-2 rounded-2xl p-5 border border-gray-200 hover:border-gray-300 transition h-full flex flex-col justify-between">
                            <div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 uppercase tracking-wider">Clean</span>
                                <h4 class="font-bold text-gray-900 text-sm mt-2">Modern Compact</h4>
                                <p class="text-xs text-gray-500 mt-1">Clean line separators, prominent total paid pill, modern typography, fast printing.</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500 font-medium">
                                <span>Retail & POS</span>
                            </div>
                        </div>
                    </label>

                    <label class="relative cursor-pointer group">
                        <input type="radio" name="invoice_design_style" value="detailed_field" {{ $invoiceSettings['design_style'] === 'detailed_field' ? 'checked' : '' }} class="peer sr-only">
                        <div class="peer-checked:ring-2 peer-checked:ring-brand-600 peer-checked:ring-offset-2 rounded-2xl p-5 border border-gray-200 hover:border-gray-300 transition h-full flex flex-col justify-between">
                            <div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 uppercase tracking-wider">Projects</span>
                                <h4 class="font-bold text-gray-900 text-sm mt-2">Detailed Work Order Invoice</h4>
                                <p class="text-xs text-gray-500 mt-1">Highlights field job stages, material usage, and milestone settlement details.</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500 font-medium">
                                <span>Orchard Projects</span>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- 2. Title & Theme --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Invoice Title & Accent Color</h3>
                <p class="text-sm text-gray-500 mb-5">Heading text and highlight colors shown on generated customer invoices.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-admin.input name="invoice_document_title" label="Document Header Title" :value="$invoiceSettings['document_title']" placeholder="TAX INVOICE" required />
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Primary Accent Color</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="invoice_accent_color" value="{{ $invoiceSettings['accent_color'] }}" class="w-12 h-10 rounded-xl border border-gray-200 p-1 cursor-pointer">
                            <span class="text-xs font-mono font-bold text-gray-700">{{ $invoiceSettings['accent_color'] }}</span>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">Colors table header background, total borders, and status accents.</p>
                    </div>
                </div>
            </div>

            {{-- 3. Display Toggles --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Invoice Component Toggles</h3>
                <p class="text-sm text-gray-500 mb-5">Enable or disable elements displayed on invoice printouts.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <x-admin.checkbox name="invoice_show_logo" label="Show Logo" :checked="$invoiceSettings['show_logo']" />
                    <x-admin.checkbox name="invoice_show_gst" label="Show GST Number" :checked="$invoiceSettings['show_gst']" />
                    <x-admin.checkbox name="invoice_show_bank_details" label="Show Bank Settlement Box" :checked="$invoiceSettings['show_bank_details']" />
                    <x-admin.checkbox name="invoice_show_terms" label="Show Terms" :checked="$invoiceSettings['show_terms']" />
                    <x-admin.checkbox name="invoice_show_signatory" label="Show Signatory Line" :checked="$invoiceSettings['show_signatory']" />
                </div>
            </div>

            {{-- 4. Terms --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                <x-admin.textarea name="invoice_terms" label="Default Invoice Terms & Conditions" :value="$invoiceSettings['terms']" rows="3" />
                <x-admin.textarea name="invoice_notes" label="Default Customer Notes" :value="$invoiceSettings['notes']" rows="2" placeholder="Thank you for partnering with Plant Tech Agro." />
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Save Invoice Design</x-admin.button>
            </div>
        </form>
    </div>

    {{-- TAB 4: Service-Linked Styling (Quotations & Invoices per Service) --}}
    <div x-show="activeTab === 'services'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="space-y-6">

        {{-- Top Service Selector Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Select Agriculture Service</h3>
                    <p class="text-sm text-gray-500">Pick a service to customize its unique quotation proforma format, invoice titles, milestone stages, and color theme.</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                    <span x-text="services.length"></span> Services Configured
                </span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                <template x-for="svc in services" :key="svc.id">
                    <button type="button" @click="selectedServiceId = svc.id"
                        :class="selectedServiceId === svc.id ? 'ring-2 ring-brand-600 bg-brand-50/70 border-brand-300' : 'bg-gray-50/60 border-gray-200 hover:border-gray-300 hover:bg-gray-100/50'"
                        class="p-3 rounded-xl border text-left transition flex flex-col justify-between group">
                        <div>
                            <div class="flex items-center justify-between gap-1 mb-1.5">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400" x-text="svc.category || 'Service'"></span>
                                <span x-show="svc.has_custom_settings" class="w-2 h-2 rounded-full bg-emerald-500" title="Customized Styles Active"></span>
                            </div>
                            <p class="text-xs font-bold text-gray-900 line-clamp-2" x-text="svc.name"></p>
                        </div>
                        <div class="mt-3 pt-2 border-t border-gray-200/50 flex items-center justify-between">
                            <span class="text-[10px] font-mono uppercase font-bold text-gray-500" x-text="svc.quotation_type"></span>
                            <span :style="{ backgroundColor: svc.quotation_defaults.accent_color || '#064e3b' }" class="w-3.5 h-3.5 rounded-full shadow-2xs border border-white"></span>
                        </div>
                    </button>
                </template>
            </div>
        </div>

        {{-- Service Customization Form & Live Preview Grid --}}
        @foreach($services as $service)
            <div x-show="selectedServiceId === {{ $service->id }}" class="grid grid-cols-1 lg:grid-cols-12 gap-6"
                 x-data="{
                    milestones: {{ json_encode($service->getQuotationDefaults()['payment_schedule'] ?? []) }},
                    variations: {{ json_encode($service->getQuotationDefaults()['variations'] ?? []) }},
                    addVariation() {
                        this.variations.push({
                            name: '',
                            plants_per_kanal: '150',
                            package_poles: 19,
                            package_anchors: 6,
                            package_plants: 150,
                            rate: 185000,
                            unit: 'Kanal',
                            rootstock: 'M9 / T337 (High Density)',
                            variety_name: 'Devil Gala'
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
                    },
                    accentColor: '{{ $service->getQuotationDefaults()['accent_color'] ?? '#064e3b' }}',
                    quotationType: '{{ $service->getQuotationType() }}',
                    showVariety: {{ ($service->getQuotationDefaults()['show_variety'] ?? false) ? 'true' : 'false' }},
                    showPackage: {{ ($service->getQuotationDefaults()['show_package'] ?? false) ? 'true' : 'false' }},
                    quotationTitle: '{{ addslashes($service->getQuotationDefaults()['document_title'] ?? 'PROFORMA INVOICE') }}',
                    quotationSubtitle: '{{ addslashes($service->getQuotationDefaults()['document_subtitle'] ?? 'PRICE ESTIMATE & QUOTATION') }}',
                    invoiceTitle: '{{ addslashes($service->getInvoiceDefaults()['document_title'] ?? 'TAX INVOICE') }}'
                 }">

                {{-- Left Column: Configuration Controls (7 Cols) --}}
                <div class="lg:col-span-7 space-y-6">
                    <form action="{{ route('admin.document-settings.service.update', $service) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        {{-- Card Header --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-lg font-bold text-gray-900">{{ $service->name }}</h3>
                                        @if(!empty($service->quotation_settings) || !empty($service->invoice_settings))
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                                Custom Styles Active
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-gray-100 text-gray-600">
                                                Factory Presets
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">Category: <strong>{{ ucwords(str_replace('-', ' ', $service->category ?? 'general')) }}</strong> • Slug: <code class="text-xs font-mono">{{ $service->slug }}</code></p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-400 font-medium">Quotation Type:</span>
                                    <select name="quotation_type" x-model="quotationType" class="text-xs rounded-xl border-gray-200 font-bold text-gray-800 py-1.5 focus:border-brand-500 focus:ring-brand-500">
                                        <option value="orchard">High-Density Orchard (orchard)</option>
                                        <option value="plants">Nursery & Plants Booking (plants)</option>
                                        <option value="installation">Netting & Trellis Structure (installation)</option>
                                        <option value="technical">Soil & Field Testing (technical)</option>
                                        <option value="general">General Agritech (general)</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Quotation Titles & Colors --}}
                            <div class="space-y-4">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700">Quotation Document Styling</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Quotation Document Title</label>
                                        <input type="text" name="quotation_title" x-model="quotationTitle" class="w-full text-sm rounded-xl border-gray-200 focus:border-brand-500 focus:ring-brand-500 py-2">
                                        <span class="text-[10px] text-gray-400">e.g. PROFORMA INVOICE, PLANT NURSERY BOOKING</span>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Quotation Subtitle</label>
                                        <input type="text" name="quotation_subtitle" x-model="quotationSubtitle" class="w-full text-sm rounded-xl border-gray-200 focus:border-brand-500 focus:ring-brand-500 py-2">
                                        <span class="text-[10px] text-gray-400">e.g. PRICE ESTIMATE & QUOTATION</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Service Brand Accent Color</label>
                                        <div class="flex items-center gap-3">
                                            <input type="color" name="accent_color" x-model="accentColor" class="w-10 h-9 rounded-xl border border-gray-200 p-1 cursor-pointer">
                                            <span class="text-xs font-mono font-bold text-gray-700" x-text="accentColor"></span>
                                        </div>
                                    </div>
                                    <div class="space-y-2 pt-1">
                                        <label class="flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer">
                                            <input type="checkbox" name="show_variety" value="1" x-model="showVariety" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                            <span>Display Apple Variety & Rootstock block</span>
                                        </label>
                                        <label class="flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer">
                                            <input type="checkbox" name="show_package" value="1" x-model="showPackage" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                            <span>Display Package Inclusions (Poles / Anchors)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Package Specs & Pricing (when active) --}}
                            <div x-show="showPackage || showVariety" class="rounded-2xl border border-gray-200 bg-gray-50/70 p-4 space-y-4">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700">Default Service Package Specifications & Base Pricing</h4>
                                    <span class="text-[10px] text-gray-500">Prefilled into Quotations</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-600 mb-1">Base Price / Rate (₹)</label>
                                        <input type="number" step="0.01" name="base_price" value="{{ $service->getQuotationDefaults()['base_price'] ?? '' }}" placeholder="185000" class="w-full text-xs rounded-xl border-gray-200 py-1.5 font-bold text-gray-900 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-600 mb-1">Pricing Unit</label>
                                        <input type="text" name="unit" value="{{ $service->getQuotationDefaults()['unit'] ?? 'Kanal' }}" placeholder="Kanal" class="w-full text-xs rounded-xl border-gray-200 py-1.5 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-600 mb-1">Package Title</label>
                                        <input type="text" name="package_title" value="{{ $service->getQuotationDefaults()['package_title'] ?? '' }}" placeholder="Per Kanal Standard Package" class="w-full text-xs rounded-xl border-gray-200 py-1.5 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-600 mb-1">Plants / Kanal (Default)</label>
                                        <input type="text" name="plants_per_kanal" value="{{ $service->getQuotationDefaults()['plants_per_kanal'] ?? '' }}" placeholder="150" class="w-full text-xs rounded-xl border-gray-200 py-1.5 bg-white">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-600 mb-1">Poles / Kanal</label>
                                        <input type="number" name="package_poles" value="{{ $service->getQuotationDefaults()['package_poles'] ?? '' }}" placeholder="19" class="w-full text-xs rounded-xl border-gray-200 py-1.5 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-600 mb-1">Anchors / Kanal</label>
                                        <input type="number" name="package_anchors" value="{{ $service->getQuotationDefaults()['package_anchors'] ?? '' }}" placeholder="6" class="w-full text-xs rounded-xl border-gray-200 py-1.5 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-600 mb-1">Plants / Kanal</label>
                                        <input type="number" name="package_plants" value="{{ $service->getQuotationDefaults()['package_plants'] ?? '' }}" placeholder="150" class="w-full text-xs rounded-xl border-gray-200 py-1.5 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-600 mb-1">Default Variety</label>
                                        <input type="text" name="variety_name" value="{{ $service->getQuotationDefaults()['variety_name'] ?? '' }}" placeholder="Devil Gala" class="w-full text-xs rounded-xl border-gray-200 py-1.5 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-600 mb-1">Rootstock</label>
                                        <input type="text" name="rootstock" value="{{ $service->getQuotationDefaults()['rootstock'] ?? '' }}" placeholder="M9 / T337" class="w-full text-xs rounded-xl border-gray-200 py-1.5 bg-white">
                                    </div>
                                </div>
                            </div>

                            {{-- Interactive Package Variations & Pricing Table --}}
                            <div class="space-y-3 pt-2">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700">Package Variations &amp; Pricing</h4>
                                        <p class="text-[11px] text-gray-500">Define density options (e.g. 150 vs 170 plants/kanal) so quotation creators can choose a preset.</p>
                                    </div>
                                    <button type="button" @click="addVariation()" class="px-2.5 py-1 rounded-xl bg-brand-50 hover:bg-brand-100 text-brand-700 text-xs font-bold transition inline-flex items-center gap-1 border border-brand-200 shadow-2xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <span>Add Variation</span>
                                    </button>
                                </div>

                                <div class="space-y-2">
                                    <template x-for="(v, vIdx) in variations" :key="vIdx">
                                        <div class="rounded-xl border border-gray-200 bg-gray-50/80 p-3 space-y-2.5">
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="flex items-center gap-2 flex-1">
                                                    <span class="w-5 h-5 rounded-full bg-brand-100 text-brand-800 text-[10px] font-bold flex items-center justify-center shrink-0" x-text="vIdx + 1"></span>
                                                    <input type="text" :name="'variations[' + vIdx + '][name]'" x-model="v.name" placeholder="Variation Name (e.g. 150 Plants / Kanal (Standard High Density))" required class="flex-1 text-xs font-bold rounded-lg border-gray-200 py-1.5 bg-white">
                                                </div>
                                                <button type="button" @click="removeVariation(vIdx)" class="p-1 text-red-500 hover:bg-red-50 rounded-lg transition" title="Remove Variation">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </div>

                                            <div class="grid grid-cols-2 sm:grid-cols-6 gap-2">
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-gray-500 mb-0.5">Rate (₹)</label>
                                                    <input type="number" step="0.01" :name="'variations[' + vIdx + '][rate]'" x-model.number="v.rate" placeholder="185000" class="w-full text-xs font-bold text-gray-900 rounded-lg border-gray-200 py-1 px-2 bg-white">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-gray-500 mb-0.5">Unit</label>
                                                    <input type="text" :name="'variations[' + vIdx + '][unit]'" x-model="v.unit" placeholder="Kanal" class="w-full text-xs rounded-lg border-gray-200 py-1 px-2 bg-white">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-gray-500 mb-0.5">Plants / Kanal</label>
                                                    <input type="text" :name="'variations[' + vIdx + '][plants_per_kanal]'" x-model="v.plants_per_kanal" placeholder="150" class="w-full text-xs rounded-lg border-gray-200 py-1 px-2 bg-white">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-gray-500 mb-0.5">Poles</label>
                                                    <input type="number" :name="'variations[' + vIdx + '][package_poles]'" x-model.number="v.package_poles" placeholder="19" class="w-full text-xs rounded-lg border-gray-200 py-1 px-2 bg-white">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-gray-500 mb-0.5">Anchors</label>
                                                    <input type="number" :name="'variations[' + vIdx + '][package_anchors]'" x-model.number="v.package_anchors" placeholder="6" class="w-full text-xs rounded-lg border-gray-200 py-1 px-2 bg-white">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-gray-500 mb-0.5">Plants Count</label>
                                                    <input type="number" :name="'variations[' + vIdx + '][package_plants]'" x-model.number="v.package_plants" placeholder="150" class="w-full text-xs rounded-lg border-gray-200 py-1 px-2 bg-white">
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <div x-show="variations.length === 0" class="p-3 rounded-xl border border-dashed border-gray-200 text-center text-xs text-gray-400">
                                        No package variations configured yet. Click "+ Add Variation" to create presets like 150 vs 170 plants/kanal.
                                    </div>
                                </div>
                            </div>

                            {{-- Payment Milestones Builder --}}
                            <div class="space-y-3 pt-2">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700">Milestone Payment Stepper</h4>
                                        <p class="text-[11px] text-gray-500">Configure default payment schedule percentages and stage descriptions for this service.</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-bold"
                                              :class="totalPercent === 100 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'">
                                            Total: <span x-text="totalPercent"></span>%
                                        </span>
                                        <button type="button" @click="addMilestone()" class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition">
                                            + Add Stage
                                        </button>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <template x-for="(step, idx) in milestones" :key="idx">
                                        <div class="flex items-center gap-2 p-2 rounded-xl border border-gray-200 bg-gray-50/60">
                                            <span class="w-6 text-center text-xs font-bold text-gray-400" x-text="idx + 1"></span>
                                            <div class="w-24 shrink-0">
                                                <div class="relative">
                                                    <input type="number" :name="'payment_schedule[' + idx + '][percent]'" x-model.number="step.percent" min="1" max="100" class="w-full text-xs rounded-lg border-gray-200 py-1.5 pr-6 font-bold text-gray-800">
                                                    <span class="absolute right-2 top-1.5 text-xs text-gray-400 font-bold">%</span>
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <input type="text" :name="'payment_schedule[' + idx + '][stage]'" x-model="step.stage" placeholder="e.g. Advance at the time of booking" class="w-full text-xs rounded-lg border-gray-200 py-1.5">
                                            </div>
                                            <button type="button" @click="removeMilestone(idx)" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Remove stage">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Invoice Customization for this Service --}}
                            <div class="space-y-4 pt-4 border-t border-gray-100">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700">Invoice Customization for this Service</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Invoice Document Title</label>
                                        <input type="text" name="invoice_title" x-model="invoiceTitle" class="w-full text-sm rounded-xl border-gray-200 focus:border-brand-500 focus:ring-brand-500 py-2">
                                        <span class="text-[10px] text-gray-400">e.g. TAX INVOICE, SERVICE INVOICE</span>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Invoice Subtitle</label>
                                        <input type="text" name="invoice_subtitle" value="{{ $service->getInvoiceDefaults()['document_subtitle'] ?? '' }}" class="w-full text-sm rounded-xl border-gray-200 focus:border-brand-500 focus:ring-brand-500 py-2">
                                        <span class="text-[10px] text-gray-400">Appears under invoice header for jobs of this service.</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Notes & Terms --}}
                            <div class="space-y-4 pt-4 border-t border-gray-100">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Service-Specific Pricing Notes</label>
                                    <textarea name="additional_notes" rows="2" class="w-full text-xs rounded-xl border-gray-200 focus:border-brand-500 focus:ring-brand-500 py-2">{{ $service->getQuotationDefaults()['additional_notes'] ?? '' }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Service-Specific Quotation / Invoice Terms</label>
                                    <textarea name="quotation_terms" rows="2" class="w-full text-xs rounded-xl border-gray-200 focus:border-brand-500 focus:ring-brand-500 py-2" placeholder="Leave blank to use global terms">{{ $service->getQuotationDefaults()['terms'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center justify-between">
                            <button type="button" @click="if(confirm('Reset this service to default presets?')) { $refs.resetForm_{{ $service->id }}.submit(); }" class="px-4 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-semibold text-red-600 transition shadow-2xs">
                                Reset to Factory Defaults
                            </button>

                            <x-admin.button type="submit">Save Service Document Styles</x-admin.button>
                        </div>
                    </form>

                    {{-- Hidden Reset Form --}}
                    <form x-ref="resetForm_{{ $service->id }}" action="{{ route('admin.document-settings.service.reset', $service) }}" method="POST" class="hidden">
                        @csrf
                    </form>
                </div>

                {{-- Right Column: Interactive Live Document Preview (5 Cols) --}}
                <div class="lg:col-span-5 space-y-4">
                    <div class="sticky top-6">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-700">Live Service Print Preview</span>
                            </div>
                            <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600" x-text="quotationType.toUpperCase()"></span>
                        </div>

                        {{-- Miniature Document Simulation --}}
                        <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-5 space-y-4 text-gray-800">
                            {{-- Header --}}
                            <div class="flex items-start justify-between gap-3 pb-3 border-b border-gray-100">
                                <div>
                                    <span class="font-extrabold text-base tracking-tight" :style="{ color: accentColor }">Plant<span class="text-orange-500">Tech</span> Agro</span>
                                    <p class="text-[9px] font-semibold text-gray-400 uppercase tracking-widest mt-0.5">Complete Orchard Solution</p>
                                    <p class="text-[9px] text-gray-500 mt-1">Srinagar, Jammu & Kashmir</p>
                                </div>
                                <div class="text-right">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold text-white block shadow-xs" :style="{ backgroundColor: accentColor }" x-text="quotationTitle"></span>
                                    <span class="text-[9px] font-bold text-gray-500 block mt-1" x-text="quotationSubtitle"></span>
                                    <span class="text-[9px] font-mono text-gray-400 block mt-0.5">{{ $quotationSettings['prefix'] }}/2026-27/0042</span>
                                </div>
                            </div>

                            {{-- Service & Variety Badge --}}
                            <div class="rounded-xl border p-3" :style="{ borderColor: accentColor + '30', backgroundColor: accentColor + '08' }">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-gray-900">{{ $service->name }}</span>
                                    <span class="text-[10px] font-bold uppercase" :style="{ color: accentColor }">Estimate</span>
                                </div>
                                <template x-if="showVariety">
                                    <div class="mt-2 pt-2 border-t border-gray-200/40 grid grid-cols-3 gap-2 text-[10px]">
                                        <div>
                                            <span class="text-gray-400 block">Variety</span>
                                            <strong class="text-gray-800">{{ $service->getQuotationDefaults()['variety_name'] ?: 'Devil Gala' }}</strong>
                                        </div>
                                        <div>
                                            <span class="text-gray-400 block">Rootstock</span>
                                            <strong class="text-gray-800">{{ $service->getQuotationDefaults()['rootstock'] ?: 'M9 / T337' }}</strong>
                                        </div>
                                        <div>
                                            <span class="text-gray-400 block">Density</span>
                                            <strong class="text-gray-800">{{ $service->getQuotationDefaults()['plants_per_kanal'] ?: '150/Kanal' }}</strong>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- Table Simulation --}}
                            <div class="rounded-lg overflow-hidden border border-gray-200 text-[10px]">
                                <table class="w-full text-left">
                                    <thead class="text-white font-bold" :style="{ backgroundColor: accentColor }">
                                        <tr>
                                            <th class="p-1.5">Deliverable Description</th>
                                            <th class="p-1.5 text-center">Qty</th>
                                            <th class="p-1.5 text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <tr>
                                            <td class="p-1.5 font-medium text-gray-800">{{ $service->name }} (Sample Site Work)</td>
                                            <td class="p-1.5 text-center text-gray-500">10 Kanals</td>
                                            <td class="p-1.5 text-right font-bold text-gray-900">₹ 8,40,000</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- Milestone Payment Schedule Preview --}}
                            <div class="rounded-xl border border-gray-100 bg-gray-50/80 p-3 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-600">Payment Milestones</span>
                                    <span class="text-[9px] font-bold text-gray-400" x-text="milestones.length + ' Stages'"></span>
                                </div>
                                <div class="space-y-1.5">
                                    <template x-for="(st, sidx) in milestones" :key="sidx">
                                        <div class="flex items-center justify-between text-[10px]">
                                            <div class="flex items-center gap-1.5">
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold text-white" :style="{ backgroundColor: accentColor }" x-text="st.percent + '%'"></span>
                                                <span class="text-gray-700" x-text="st.stage || ('Stage ' + (sidx + 1))"></span>
                                            </div>
                                            <span class="font-bold text-gray-900" x-text="'₹ ' + Math.round(840000 * (st.percent / 100)).toLocaleString('en-IN')"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Settlement Bank Preview --}}
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-3 flex items-center justify-between text-[10px]">
                                <div>
                                    <span class="font-bold text-emerald-950 block">Bank Settlement Coordinates</span>
                                    <span class="text-gray-600 font-mono">{{ $companySettings['bank_name'] }} • {{ $companySettings['bank_account_no'] }}</span>
                                </div>
                                <span class="font-mono text-emerald-800 font-bold text-[9px]">IFSC: {{ $companySettings['bank_ifsc'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        @endforeach
    </div>

</div>
@endsection
