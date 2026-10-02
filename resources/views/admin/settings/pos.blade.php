@extends('admin.layout')

@section('page-title', 'POS & Store Settings')

@section('content')
<div x-data="posSettingsForm()" class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-brand-600 text-white flex items-center justify-center shadow-sm shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Point of Sale (POS) &amp; Store Settings</h2>
                <p class="text-xs text-gray-500 mt-0.5">Control retail terminal behavior, stock deduction rules, thermal receipt printing, and subdomain deployment.</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ $settings['subdomain_url'] ?? 'https://pos.planttechagro.com' }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl border border-brand-200 bg-brand-50 text-brand-700 hover:bg-brand-100 transition shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>Visit POS Subdomain</span>
            </a>
            <a href="{{ route('admin.pos.terminal') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 transition shadow-2xs">
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>Open Local Terminal</span>
            </a>
        </div>
    </div>

    <form action="{{ route('admin.settings.pos.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- Left Column: Settings Tabs & Inputs --}}
            <div class="lg:col-span-8 space-y-6">

                {{-- Tab Navigation Bar (Matches global Settings bar) --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-2 py-1">
                    <nav class="flex gap-1 overflow-x-auto" aria-label="POS settings tabs">
                        <button type="button" @click="activeTab = 'subdomain'"
                            :class="activeTab === 'subdomain' ? 'bg-brand-50 text-brand-700 border-brand-200 font-semibold' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent font-medium'"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm rounded-xl border transition-all whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                            Subdomain &amp; Deploy
                        </button>

                        <button type="button" @click="activeTab = 'store'"
                            :class="activeTab === 'store' ? 'bg-brand-50 text-brand-700 border-brand-200 font-semibold' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent font-medium'"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm rounded-xl border transition-all whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            Store Profile
                        </button>

                        <button type="button" @click="activeTab = 'receipt'"
                            :class="activeTab === 'receipt' ? 'bg-brand-50 text-brand-700 border-brand-200 font-semibold' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent font-medium'"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm rounded-xl border transition-all whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Receipt &amp; Printer
                        </button>

                        <button type="button" @click="activeTab = 'billing'"
                            :class="activeTab === 'billing' ? 'bg-brand-50 text-brand-700 border-brand-200 font-semibold' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent font-medium'"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm rounded-xl border transition-all whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Billing &amp; Tender
                        </button>

                        <button type="button" @click="activeTab = 'stock'"
                            :class="activeTab === 'stock' ? 'bg-brand-50 text-brand-700 border-brand-200 font-semibold' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent font-medium'"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm rounded-xl border transition-all whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            Stock &amp; Batches
                        </button>

                        <button type="button" @click="activeTab = 'cashier'"
                            :class="activeTab === 'cashier' ? 'bg-brand-50 text-brand-700 border-brand-200 font-semibold' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent font-medium'"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm rounded-xl border transition-all whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Shift &amp; Security
                        </button>
                    </nav>
                </div>

                {{-- TAB 1: Subdomain & Deployment --}}
                <div x-show="activeTab === 'subdomain'"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">

                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Subdomain &amp; Deployment Configuration</h3>
                            <p class="text-sm text-gray-500">Configure host routing, allowed CORS origins, SSL enforcement, and standalone kiosk behavior.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Dedicated POS Subdomain URL <span class="text-red-500">*</span>
                            </label>
                            <input type="url" name="subdomain_url" x-model="form.subdomain_url"
                                   required placeholder="https://pos.planttechagro.com"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <p class="mt-1.5 text-xs text-gray-500">The public production address dedicated to the cashier POS terminal.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Allowed CORS Origins</label>
                            <input type="text" name="cors_allowed_origins" value="{{ old('cors_allowed_origins', $settings['cors_allowed_origins'] ?? 'https://pos.planttechagro.com,http://localhost:8000') }}"
                                   placeholder="https://pos.planttechagro.com,http://localhost:8000"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-mono text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <p class="mt-1.5 text-xs text-gray-500">Comma-separated domains authorized for POS AJAX/API calls.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Deployment Environment Status</label>
                            <div class="px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 flex items-center justify-between text-sm">
                                <span class="text-gray-600 font-medium">Server Mode:</span>
                                <span class="font-bold font-mono px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-xs">{{ app()->environment() }}</span>
                            </div>
                            <p class="mt-1.5 text-xs text-gray-500">Running on {{ php_uname('s') }} PHP {{ PHP_VERSION }}</p>
                        </div>
                    </div>

                    {{-- Feature Toggles --}}
                    <div class="pt-5 border-t border-gray-100 space-y-4">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="subdomain_enabled" value="1" {{ !empty($settings['subdomain_enabled']) ? 'checked' : '' }}
                                   class="mt-1 w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <div>
                                <span class="text-sm font-semibold text-gray-900">Enable Subdomain Routing</span>
                                <p class="text-xs text-gray-500">When enabled, requests arriving on pos.* will automatically serve the dedicated POS Kiosk terminal.</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="force_https" value="1" {{ !empty($settings['force_https']) ? 'checked' : '' }}
                                   class="mt-1 w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <div>
                                <span class="text-sm font-semibold text-gray-900">Enforce Secure HTTPS on Subdomain</span>
                                <p class="text-xs text-gray-500">Redirects HTTP traffic to HTTPS to ensure safe cashier logins and encrypted payment data.</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="kiosk_mode" value="1" {{ !empty($settings['kiosk_mode']) ? 'checked' : '' }}
                                   class="mt-1 w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <div>
                                <span class="text-sm font-semibold text-gray-900">Enable Fullscreen Kiosk Mode for Cashiers</span>
                                <p class="text-xs text-gray-500">Hides browser window clutter and restricts cashier access strictly to billing and stock inward screens.</p>
                            </div>
                        </label>
                    </div>

                    {{-- Technical DNS & Hosts Guide Box --}}
                    <div class="rounded-xl border border-blue-100 bg-blue-50/60 p-4 space-y-2">
                        <div class="flex items-center gap-2 text-blue-900 font-bold text-xs">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Server DNS &amp; Local Test Configuration:
                        </div>
                        <p class="text-xs text-blue-800 leading-relaxed">
                            <strong>Live Deployment:</strong> Create a DNS <code class="bg-blue-100 px-1 py-0.5 rounded text-blue-900">CNAME</code> or <code class="bg-blue-100 px-1 py-0.5 rounded text-blue-900">A</code> record pointing <code class="bg-blue-100 px-1 py-0.5 rounded text-blue-900">pos.planttechagro.com</code> to your webserver IP.<br>
                            <strong>Local Testing:</strong> In your <code class="bg-blue-100 px-1 py-0.5 rounded text-blue-900">hosts</code> file, you can map <code class="bg-blue-100 px-1 py-0.5 rounded text-blue-900">127.0.0.1 pos.localhost</code> to test subdomain isolation on local development.
                        </p>
                    </div>
                </div>

                {{-- TAB 2: Store Profile --}}
                <div x-show="activeTab === 'store'"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">

                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Physical Storefront &amp; Outlet Profile</h3>
                            <p class="text-sm text-gray-500">Store credentials, contact numbers, address, and GSTIN printed on customer receipts.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Outlet / Store Name <span class="text-red-500">*</span></label>
                            <input type="text" name="store_name" x-model="form.store_name"
                                   required placeholder="Plant Tech Agro"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Outlet Branch Code <span class="text-red-500">*</span></label>
                            <input type="text" name="store_code" x-model="form.store_code"
                                   required placeholder="PTA-SRX-01"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-mono font-bold text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Tagline / Slogan</label>
                            <input type="text" name="store_tagline" x-model="form.store_tagline"
                                   placeholder="Retail Outlet & Farmers Agro Center"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Store Phone / WhatsApp</label>
                            <input type="text" name="store_phone" x-model="form.store_phone"
                                   placeholder="0194-796-1490"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Store Billing Email</label>
                            <input type="email" name="store_email" value="{{ old('store_email', $settings['store_email'] ?? 'pos@planttechagro.com') }}"
                                   placeholder="pos@planttechagro.com"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Store Address (Printed on Receipts)</label>
                            <textarea name="store_address" x-model="form.store_address" rows="2"
                                      class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 resize-y"
                                      placeholder="Full physical address..."></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">GSTIN / Tax ID</label>
                            <input type="text" name="gstin" x-model="form.gstin"
                                   placeholder="01AAACP9281G1Z7"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-mono text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Currency Symbol</label>
                            <input type="text" name="currency_symbol" x-model="form.currency_symbol"
                                   placeholder="₹"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-bold text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Operating Hours</label>
                            <input type="text" name="operating_hours" value="{{ old('operating_hours', $settings['operating_hours'] ?? 'Mon – Sat: 9:00 AM – 7:30 PM') }}"
                                   placeholder="Mon – Sat: 9:00 AM – 7:30 PM"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>
                    </div>
                </div>

                {{-- TAB 3: Receipt & Thermal Printer --}}
                <div x-show="activeTab === 'receipt'"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">

                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Thermal Receipt &amp; Printer Setup</h3>
                            <p class="text-sm text-gray-500">Customize paper roll width (80mm/58mm/A4), header/footer notes, return policy, and UPI payment QR codes.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Paper Roll Width</label>
                            <select name="paper_width" x-model="form.paper_width"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                <option value="80mm">80mm Thermal Roll (Standard POS Receipt)</option>
                                <option value="58mm">58mm Thermal Roll (Compact Mobile / Bluetooth)</option>
                                <option value="a4">A4 Full Sheet (Office Laser / Inkjet Invoice)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Receipt Logo Upload</label>
                            <input type="file" name="logo_file" accept="image/*"
                                   class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 border border-gray-200 rounded-xl bg-gray-50 p-1">
                            @if(!empty($settings['logo_url']))
                                <div class="mt-2 flex items-center gap-2">
                                    <img src="{{ $settings['logo_url'] }}" alt="POS Logo" class="h-8 w-auto rounded border bg-white p-1">
                                    <label class="text-xs text-red-600 flex items-center gap-1 cursor-pointer font-medium">
                                        <input type="checkbox" name="remove_logo" value="1" class="rounded text-red-600"> Remove current logo
                                    </label>
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">UPI VPA / ID (For Receipt QR Code)</label>
                            <input type="text" name="upi_vpa" x-model="form.upi_vpa"
                                   placeholder="planttechagro@jkb"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-mono text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <p class="mt-1.5 text-xs text-gray-500">Farmers can scan the QR code printed on the receipt to pay via GPay, PhonePe, or Paytm.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">UPI Payee Display Name</label>
                            <input type="text" name="upi_payee_name" value="{{ old('upi_payee_name', $settings['upi_payee_name'] ?? 'Plant Tech Agro') }}"
                                   placeholder="Plant Tech Agro"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <p class="mt-1.5 text-xs text-gray-500">Name shown on the customer's UPI app screen.</p>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Receipt Header Notes (Sub-heading)</label>
                            <input type="text" name="header_notes" x-model="form.header_notes"
                                   placeholder="Modern Orchard & Precision Agriculture Inputs"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Receipt Footer Notes (Thank You Note)</label>
                            <input type="text" name="footer_notes" x-model="form.footer_notes"
                                   placeholder="Thank you for shopping with Plant Tech Agro! Grow better with certified agri-inputs."
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Return &amp; Exchange Terms (Printed at Bottom)</label>
                            <textarea name="return_policy" x-model="form.return_policy" rows="2"
                                      class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 resize-y"
                                      placeholder="Goods can be exchanged within 7 days with original invoice..."></textarea>
                        </div>
                    </div>

                    {{-- Print Toggles --}}
                    <div class="pt-5 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="auto_print" value="1" {{ !empty($settings['auto_print']) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span class="text-xs font-semibold text-gray-800">Auto-open Print Dialog upon Sale Checkout</span>
                        </label>

                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="show_cashier" value="1" x-model="form.show_cashier"
                                   class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span class="text-xs font-semibold text-gray-800">Print Cashier Operator Name on Receipt</span>
                        </label>

                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="show_upi_qr" value="1" x-model="form.show_upi_qr"
                                   class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span class="text-xs font-semibold text-gray-800">Print UPI Payment QR Code on Slip</span>
                        </label>

                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="show_tax_summary" value="1" x-model="form.show_tax_summary"
                                   class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span class="text-xs font-semibold text-gray-800">Print Tax / GST Breakdown Table</span>
                        </label>
                    </div>
                </div>

                {{-- TAB 4: Billing & Tender Modes --}}
                <div x-show="activeTab === 'billing'"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">

                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Billing, Invoicing &amp; Tender Modes</h3>
                            <p class="text-sm text-gray-500">Define invoice numbering sequence, cashier discount authority, supervisor PIN, and enabled payment methods.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Invoice Prefix <span class="text-red-500">*</span></label>
                            <input type="text" name="invoice_prefix" value="{{ old('invoice_prefix', $settings['invoice_prefix'] ?? 'POS') }}"
                                   required placeholder="POS"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-mono font-bold text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <p class="mt-1.5 text-xs text-gray-500">e.g. POS/2026-27/0001</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Default Customer</label>
                            <select name="default_customer_type" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                <option value="walk_in" {{ ($settings['default_customer_type'] ?? 'walk_in') === 'walk_in' ? 'selected' : '' }}>Walk-in Customer (General)</option>
                                <option value="farmer" {{ ($settings['default_customer_type'] ?? '') === 'farmer' ? 'selected' : '' }}>Registered Farmer / Orchardist</option>
                            </select>
                            <p class="mt-1.5 text-xs text-gray-500">Default contact mapped at checkout</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Supervisor Override PIN</label>
                            <input type="password" name="supervisor_pin" value="{{ old('supervisor_pin', $settings['supervisor_pin'] ?? '1234') }}"
                                   placeholder="••••" maxlength="10"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-mono text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <p class="mt-1.5 text-xs text-gray-500">Required to void bills or exceed discount caps.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Max Cashier Discount %</label>
                            <input type="number" name="max_discount_percent" value="{{ old('max_discount_percent', $settings['max_discount_percent'] ?? 15) }}"
                                   min="0" max="100" step="1"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <p class="mt-1.5 text-xs text-gray-500">Maximum percentage without supervisor PIN.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Paise Round-Off Mode</label>
                            <select name="round_off_mode" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                <option value="nearest_rupee" {{ ($settings['round_off_mode'] ?? 'nearest_rupee') === 'nearest_rupee' ? 'selected' : '' }}>Round to Nearest Rupee (₹1)</option>
                                <option value="exact" {{ ($settings['round_off_mode'] ?? '') === 'exact' ? 'selected' : '' }}>Exact Decimal (Paise)</option>
                            </select>
                            <p class="mt-1.5 text-xs text-gray-500">Grand total rounding for cash change.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Default Tax Presentation</label>
                            <select name="default_tax_mode" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                <option value="inclusive" {{ ($settings['default_tax_mode'] ?? 'inclusive') === 'inclusive' ? 'selected' : '' }}>Tax Inclusive (MRP contains GST)</option>
                                <option value="exclusive" {{ ($settings['default_tax_mode'] ?? '') === 'exclusive' ? 'selected' : '' }}>Tax Exclusive (GST calculated on top)</option>
                            </select>
                            <p class="mt-1.5 text-xs text-gray-500">How retail rates appear on screen.</p>
                        </div>
                    </div>

                    {{-- Enabled Payment Methods --}}
                    <div class="pt-5 border-t border-gray-100 space-y-3">
                        <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Authorized Tender Methods</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <label class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-200 bg-gray-50/60 cursor-pointer hover:bg-gray-100 transition">
                                <input type="checkbox" name="enable_cash" value="1" {{ !empty($settings['enable_cash']) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <span class="text-xs font-semibold text-gray-800">💵 Cash</span>
                            </label>

                            <label class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-200 bg-gray-50/60 cursor-pointer hover:bg-gray-100 transition">
                                <input type="checkbox" name="enable_upi" value="1" {{ !empty($settings['enable_upi']) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <span class="text-xs font-semibold text-gray-800">📱 UPI / QR</span>
                            </label>

                            <label class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-200 bg-gray-50/60 cursor-pointer hover:bg-gray-100 transition">
                                <input type="checkbox" name="enable_card" value="1" {{ !empty($settings['enable_card']) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <span class="text-xs font-semibold text-gray-800">💳 Card POS</span>
                            </label>

                            <label class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-200 bg-gray-50/60 cursor-pointer hover:bg-gray-100 transition">
                                <input type="checkbox" name="enable_bank_transfer" value="1" {{ !empty($settings['enable_bank_transfer']) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <span class="text-xs font-semibold text-gray-800">🏦 Bank Transfer</span>
                            </label>

                            <label class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-200 bg-gray-50/60 cursor-pointer hover:bg-gray-100 transition">
                                <input type="checkbox" name="enable_credit_ledger" value="1" {{ !empty($settings['enable_credit_ledger']) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <span class="text-xs font-semibold text-gray-800">📒 Credit (Khaata)</span>
                            </label>

                            <label class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-200 bg-gray-50/60 cursor-pointer hover:bg-gray-100 transition">
                                <input type="checkbox" name="enable_split" value="1" {{ !empty($settings['enable_split']) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <span class="text-xs font-semibold text-gray-800">⚖️ Split Tender</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Max Credit Limit per Farmer (Khaata Ceiling)</label>
                        <div class="relative w-full sm:w-72">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-500 font-bold">₹</span>
                            <input type="number" name="max_farmer_credit" value="{{ old('max_farmer_credit', $settings['max_farmer_credit'] ?? 50000) }}"
                                   min="0" step="500"
                                   class="w-full pl-8 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-bold text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500">Cashier cannot grant credit above this ceiling without supervisor authorization.</p>
                    </div>
                </div>

                {{-- TAB 5: Stock, Barcodes & Batches --}}
                <div x-show="activeTab === 'stock'"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">

                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Stock Policies, Batch Expiry &amp; Hardware Scanner</h3>
                            <p class="text-sm text-gray-500">Configure FIFO/LIFO batch deduction, negative stock billing rules, low stock warning thresholds, and barcode audio feedback.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Batch Deduction Logic <span class="text-red-500">*</span></label>
                            <select name="deduction_logic" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                <option value="fifo" {{ ($settings['deduction_logic'] ?? 'fifo') === 'fifo' ? 'selected' : '' }}>FIFO (First-In, First-Out by Expiry Date)</option>
                                <option value="lifo" {{ ($settings['deduction_logic'] ?? '') === 'lifo' ? 'selected' : '' }}>LIFO (Last-In, First-Out)</option>
                                <option value="manual" {{ ($settings['deduction_logic'] ?? '') === 'manual' ? 'selected' : '' }}>Manual (Cashier selects specific batch at counter)</option>
                            </select>
                            <p class="mt-1.5 text-xs text-gray-500">FIFO automatically sells batches nearing expiry first, preventing warehouse waste.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Negative Stock Billing Policy <span class="text-red-500">*</span></label>
                            <select name="negative_stock_billing" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                <option value="warn" {{ ($settings['negative_stock_billing'] ?? 'warn') === 'warn' ? 'selected' : '' }}>Warn Cashier (Allow checkout with badge)</option>
                                <option value="block" {{ ($settings['negative_stock_billing'] ?? '') === 'block' ? 'selected' : '' }}>Strict Block (Prevent billing if stock is 0)</option>
                                <option value="allow" {{ ($settings['negative_stock_billing'] ?? '') === 'allow' ? 'selected' : '' }}>Silent Allow (Permit backorders)</option>
                            </select>
                            <p class="mt-1.5 text-xs text-gray-500">Determines counter behavior when physical stock precedes computer entry.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Low Stock Alert Threshold</label>
                            <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $settings['low_stock_threshold'] ?? 10) }}"
                                   min="0" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <p class="mt-1.5 text-xs text-gray-500">Shows yellow warning badge on POS catalog.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Critical Stock Alert Threshold</label>
                            <input type="number" name="critical_stock_threshold" value="{{ old('critical_stock_threshold', $settings['critical_stock_threshold'] ?? 3) }}"
                                   min="0" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <p class="mt-1.5 text-xs text-gray-500">Shows flashing red badge and prompts storekeeper to reorder.</p>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Near-Expiry Warning Window (Days)</label>
                            <div class="relative w-full sm:w-72">
                                <input type="number" name="expiry_warning_days" value="{{ old('expiry_warning_days', $settings['expiry_warning_days'] ?? 60) }}"
                                       min="0" max="365"
                                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 text-xs">Days</span>
                            </div>
                            <p class="mt-1.5 text-xs text-gray-500">Batches expiring within this window display an alert badge on the terminal.</p>
                        </div>
                    </div>

                    {{-- Hardware Scanner Settings --}}
                    <div class="pt-5 border-t border-gray-100 space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="auto_increment_on_scan" value="1" {{ !empty($settings['auto_increment_on_scan']) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <div>
                                <span class="text-sm font-semibold text-gray-900">Auto-increment Qty on Repeated Barcode Scan</span>
                                <p class="text-xs text-gray-500">Scanning an item already in the cart adds +1 immediately without prompting.</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="scanner_sound" value="1" {{ !empty($settings['scanner_sound']) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <div>
                                <span class="text-sm font-semibold text-gray-900">Play Audio Beep on Successful Barcode Scan</span>
                                <p class="text-xs text-gray-500">Synthesizes audio feedback on the cashier browser whenever a product barcode is matched.</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- TAB 6: Shift & Security --}}
                <div x-show="activeTab === 'cashier'"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">

                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Cashier Shifts &amp; Register Security</h3>
                            <p class="text-sm text-gray-500">Manage cashier shift controls, opening cash drawer floats, inactivity timeouts, and day-end cash reconciliation (Z-Report).</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <label class="flex items-start gap-3 p-4 rounded-xl border border-gray-200 bg-gray-50/60 cursor-pointer hover:bg-gray-100/60 transition">
                            <input type="checkbox" name="require_opening_float" value="1" {{ !empty($settings['require_opening_float']) ? 'checked' : '' }}
                                   class="mt-1 w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <div>
                                <span class="text-sm font-semibold text-gray-900">Prompt for Opening Cash Float at Shift Start</span>
                                <p class="text-xs text-gray-500 mt-0.5">When cashiers log in at morning opening, requires entering starting drawer cash (e.g. ₹2,000 petty change).</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-4 rounded-xl border border-gray-200 bg-gray-50/60 cursor-pointer hover:bg-gray-100/60 transition">
                            <input type="checkbox" name="require_closing_reconciliation" value="1" {{ !empty($settings['require_closing_reconciliation']) ? 'checked' : '' }}
                                   class="mt-1 w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <div>
                                <span class="text-sm font-semibold text-gray-900">Enforce Day-End Cash Reconciliation (Z-Report)</span>
                                <p class="text-xs text-gray-500 mt-0.5">Cashiers must count physical drawer cash, card receipts, and UPI settlements before closing shift.</p>
                            </div>
                        </label>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Terminal Inactivity Auto-Lock (Minutes)</label>
                            <div class="relative w-full sm:w-72">
                                <input type="number" name="auto_logout_minutes" value="{{ old('auto_logout_minutes', $settings['auto_logout_minutes'] ?? 30) }}"
                                       min="0" max="480"
                                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 text-xs">Mins (0 to disable)</span>
                            </div>
                            <p class="mt-1.5 text-xs text-gray-500">Locks terminal screen if left unattended to prevent unauthorized billing.</p>
                        </div>
                    </div>
                </div>

                {{-- Action Bar (Matches Global Standards) --}}
                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.settings.index') }}"
                       class="px-5 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 font-medium text-sm transition text-center shadow-2xs">
                        Back to All Settings
                    </a>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-500 hidden sm:inline">Settings update POS terminals instantly</span>
                        <button type="submit"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-sm transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Save POS Settings
                        </button>
                    </div>
                </div>

            </div>

            {{-- Right Column: Live Interactive Thermal Slip Simulation --}}
            <div class="lg:col-span-4 lg:sticky lg:top-6 space-y-4">

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-700">Live Thermal Slip Preview</span>
                        </div>
                        <span class="text-[11px] font-mono font-bold px-2 py-0.5 rounded-md bg-gray-100 text-gray-700 border border-gray-200"
                              x-text="form.paper_width"></span>
                    </div>

                    <p class="text-xs text-gray-500 leading-relaxed">
                        This preview reacts dynamically as you edit store details, tax summaries, UPI VPA, and return policies.
                    </p>

                    {{-- Miniature Thermal Slip Card with realistic tear effect --}}
                    <div class="relative bg-white rounded-xl shadow-md border border-gray-200 p-4 font-mono text-[11px] leading-tight text-gray-900 space-y-2.5 select-none transition-all duration-300"
                         :style="{ maxWidth: form.paper_width === '58mm' ? '240px' : '320px', margin: '0 auto' }">

                        {{-- Header --}}
                        <div class="text-center space-y-1">
                            <div class="font-extrabold text-sm tracking-tight text-gray-950 font-sans" x-text="form.store_name.toUpperCase()"></div>
                            <div class="text-[10px] text-gray-600 italic font-sans" x-text="form.header_notes"></div>
                            <div class="text-[10px] text-gray-600 font-sans" x-text="form.store_address"></div>
                            <div class="text-[10px] text-gray-700 font-bold" x-text="'Tel: ' + form.store_phone"></div>
                            <div class="text-[10px] text-gray-500 font-mono" x-show="form.gstin" x-text="'GSTIN: ' + form.gstin"></div>
                        </div>

                        <div class="border-t border-dashed border-gray-400 my-2"></div>

                        {{-- Invoice details --}}
                        <div class="text-[10px] space-y-0.5 text-gray-700">
                            <div class="flex justify-between">
                                <span>Invoice:</span>
                                <span class="font-bold text-gray-950 font-mono">POS/2026-27/0042</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Date:</span>
                                <span>{{ date('d/m/Y h:i A') }}</span>
                            </div>
                            <div class="flex justify-between" x-show="form.show_cashier">
                                <span>Cashier:</span>
                                <span class="font-semibold text-gray-900 font-sans">{{ Auth::guard('admin')->user()?->name ?? 'Admin Staff' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Customer:</span>
                                <span class="font-sans">Walk-in Customer</span>
                            </div>
                        </div>

                        <div class="border-t border-dashed border-gray-400 my-2"></div>

                        {{-- Sample Items Table --}}
                        <table class="w-full text-left text-[10px]">
                            <thead>
                                <tr class="border-b border-gray-300 text-gray-600 font-bold">
                                    <th class="pb-1 font-sans">Item</th>
                                    <th class="pb-1 text-center font-sans">Qty</th>
                                    <th class="pb-1 text-right font-sans">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 font-sans">
                                <tr>
                                    <td class="py-1">M9 Apple Rootstock Bare Root</td>
                                    <td class="py-1 text-center font-bold">10</td>
                                    <td class="py-1 text-right font-bold">₹1,500</td>
                                </tr>
                                <tr>
                                    <td class="py-1">NPK 19-19-19 Soluble (1kg)</td>
                                    <td class="py-1 text-center font-bold">2</td>
                                    <td class="py-1 text-right font-bold">₹360</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="border-t border-dashed border-gray-400 my-2"></div>

                        {{-- Totals --}}
                        <div class="text-[10px] space-y-1">
                            <div class="flex justify-between text-gray-600">
                                <span>Taxable Subtotal:</span>
                                <span class="font-bold">₹1,860.00</span>
                            </div>
                            <div class="flex justify-between text-gray-600" x-show="form.show_tax_summary">
                                <span>GST Summary (Tax Incl):</span>
                                <span>₹168.00</span>
                            </div>
                            <div class="flex justify-between font-bold text-xs pt-1.5 border-t border-gray-300 text-gray-950">
                                <span>GRAND TOTAL:</span>
                                <span>₹1,860.00</span>
                            </div>
                            <div class="flex justify-between text-[9px] text-gray-600 pt-0.5">
                                <span>Payment Mode:</span>
                                <span class="font-bold text-gray-800">CASH (Paid: ₹2,000 / Change: ₹140)</span>
                            </div>
                        </div>

                        {{-- QR Code Simulation --}}
                        <div x-show="form.show_upi_qr && form.upi_vpa" class="text-center pt-2.5 border-t border-dashed border-gray-300 space-y-1">
                            <div class="text-[9px] font-bold uppercase tracking-wider text-gray-700 font-sans">Scan &amp; Pay via UPI</div>
                            <div class="inline-block p-1 bg-white border border-gray-300 rounded shadow-2xs">
                                <svg class="w-16 h-16 mx-auto text-gray-900" fill="currentColor" viewBox="0 0 24 24"><path d="M3 3h8v8H3V3zm2 2v4h4V5H5zm8-2h8v8h-8V3zm2 2v4h4V5h-4zM3 13h8v8H3v-8zm2 2v4h4v-4H5zm13-2h3v2h-3v-2zm-5 0h2v3h-2v-3zm2 3h3v2h-3v-2zm-2 2h2v3h-2v-3zm5 0h3v3h-3v-3zm0-5h3v2h-3v-2z"/></svg>
                            </div>
                            <div class="text-[9px] text-gray-600 font-mono font-medium" x-text="form.upi_vpa"></div>
                        </div>

                        {{-- Footer & Return Policy --}}
                        <div class="text-center pt-2.5 border-t border-dashed border-gray-400 space-y-1">
                            <div class="text-[9px] text-gray-700 font-sans font-medium" x-text="form.footer_notes"></div>
                            <div class="text-[8px] text-gray-500 font-sans leading-tight" x-text="form.return_policy"></div>
                            <div class="text-[8px] text-gray-400 font-mono mt-1 pt-1 border-t border-dotted border-gray-300">*** POWERED BY PLANT TECH AGRO POS ***</div>
                        </div>
                    </div>

                    <div class="text-center">
                        <a href="{{ route('admin.pos.terminal') }}"
                           class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 hover:text-brand-900 hover:underline">
                            <span>Open POS Billing Terminal</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </form>

</div>

@push('scripts')
<script>
function posSettingsForm() {
    return {
        activeTab: {!! json_encode(request('tab', 'subdomain')) !!},
        form: {
            subdomain_url: {!! json_encode(old('subdomain_url', $settings['subdomain_url'] ?? 'https://pos.planttechagro.com')) !!},
            store_name: {!! json_encode(old('store_name', $settings['store_name'] ?? 'Plant Tech Agro')) !!},
            store_code: {!! json_encode(old('store_code', $settings['store_code'] ?? 'PTA-SRX-01')) !!},
            store_tagline: {!! json_encode(old('store_tagline', $settings['store_tagline'] ?? 'Retail Outlet & Farmers Agro Center')) !!},
            store_phone: {!! json_encode(old('store_phone', $settings['store_phone'] ?? '0194-796-1490')) !!},
            store_address: {!! json_encode(old('store_address', $settings['store_address'] ?? '56 Murad House, Pine Lane-8, Kurso Rajbagh, Srinagar-190008, Jammu & Kashmir')) !!},
            gstin: {!! json_encode(old('gstin', $settings['gstin'] ?? '01AAACP9281G1Z7')) !!},
            currency_symbol: {!! json_encode(old('currency_symbol', $settings['currency_symbol'] ?? '₹')) !!},
            paper_width: {!! json_encode(old('paper_width', $settings['paper_width'] ?? '80mm')) !!},
            header_notes: {!! json_encode(old('header_notes', $settings['header_notes'] ?? 'Modern Orchard & Precision Agriculture Inputs')) !!},
            footer_notes: {!! json_encode(old('footer_notes', $settings['footer_notes'] ?? 'Thank you for shopping with Plant Tech Agro! Grow better with certified agri-inputs.')) !!},
            return_policy: {!! json_encode(old('return_policy', $settings['return_policy'] ?? 'Goods can be exchanged within 7 days with original invoice. No refund on open chemical/fertilizer packs.')) !!},
            upi_vpa: {!! json_encode(old('upi_vpa', $settings['upi_vpa'] ?? 'planttechagro@jkb')) !!},
            show_cashier: {{ !empty($settings['show_cashier']) ? 'true' : 'false' }},
            show_upi_qr: {{ !empty($settings['show_upi_qr']) ? 'true' : 'false' }},
            show_tax_summary: {{ !empty($settings['show_tax_summary']) ? 'true' : 'false' }}
        }
    };
}
</script>
@endpush
@endsection
