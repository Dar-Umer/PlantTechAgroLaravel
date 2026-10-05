@extends('admin.layout')

@section('page-title', 'Settings')

@section('content')
    <div class="space-y-6" x-data="{ activeTab: '{{ request('tab', 'general') }}' }">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Settings</h2>
                <p class="text-xs text-gray-500 mt-0.5">Manage store preferences, invoices, appearance, and integrations.</p>
            </div>
        </div>

        {{-- Tab Navigation --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-2 py-1">
            <nav class="flex gap-1 overflow-x-auto" aria-label="Settings tabs">
                <button type="button" @click="activeTab = 'general'"
                    :class="activeTab === 'general' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    General
                </button>
                <button type="button" @click="activeTab = 'appearance'"
                    :class="activeTab === 'appearance' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                    Appearance
                </button>
                <button type="button" @click="activeTab = 'invoice'"
                    :class="activeTab === 'invoice' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8h4m-4 4h4"/></svg>
                    Invoices &amp; Quotations
                </button>
                <button type="button" @click="activeTab = 'seo'"
                    :class="activeTab === 'seo' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.1-4.4a5.5 5.5 0 11-11 0 5.5 5.5 0 0111 0z"/></svg>
                    SEO
                </button>
                <button type="button" @click="activeTab = 'weather'"
                    :class="activeTab === 'weather' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 15a4.5 4.5 0 004.5 4.5H18a3.75 3.75 0 001.332-7.257 3 3 0 00-3.758-3.848 5.25 5.25 0 00-10.233 2.33A4.502 4.502 0 002.25 15z"/></svg>
                    Weather
                </button>
                <button type="button" @click="activeTab = 'apis'"
                    :class="activeTab === 'apis' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    APIs
                </button>
                <button type="button" @click="activeTab = 'media'"
                    :class="activeTab === 'media' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Photo Compression
                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800">Space Saver</span>
                </button>
                <button type="button" @click="activeTab = 'smtp'"
                    :class="activeTab === 'smtp' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Email / SMTP
                </button>

                <a href="{{ route('admin.settings.pos') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border border-dashed border-emerald-300 text-emerald-700 bg-emerald-50/60 hover:bg-emerald-100 hover:text-emerald-900 transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    POS &amp; Subdomain Settings
                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-200 text-emerald-900 font-mono">pos.planttechagro.com</span>
                </a>
            </nav>
        </div>

        <form id="main-settings-form" action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="tab" x-model="activeTab">

            {{-- General Tab --}}
            <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="space-y-6">

                    {{-- 1. Store & Official Contact Information --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center gap-3 mb-1">
                            <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Store & Contact Information</h3>
                                <p class="text-sm text-gray-500">Official company identity, contact numbers, email, physical office address, and support hours shown on the website, receipts, and client communications.</p>
                            </div>
                        </div>

                        <div class="mt-6 space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <x-admin.input name="site_name" label="Store / Company Name" :value="$settings['site_name'] ?? ''" placeholder="Plant Tech Agro" required />
                                <x-admin.input name="site_email" label="Official Contact Email" type="email" :value="$settings['site_email'] ?? ''" placeholder="info@plantechagro.com" helptext="Displayed in website footer, header, and official inquiries." />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <x-admin.input name="site_phone" label="Official Contact Phone" :value="$settings['site_phone'] ?? ''" placeholder="0194-796-1490" helptext="Helpline number shown in frontend footer and quotation headers." />
                                <x-admin.input name="support_hours" label="Support / Working Hours" :value="$settings['support_hours'] ?? ''" placeholder="Mon – Sat, 9 AM – 6 PM" helptext="Shown to clients and customers on support pages." />
                            </div>

                            <div>
                                <x-admin.textarea name="site_address" label="Physical Office Address" :value="$settings['site_address'] ?? ''" rows="3" placeholder="56 Murad House, Pine Lane-8, Kurso Rajbagh, Srinagar-190008, Jammu & Kashmir" helptext="Official physical address used across frontend website footer, Google Maps directions, quotations, invoices, and receipts." />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <x-admin.input name="footer_tagline" label="Footer Mission / Slogan" :value="$settings['footer_tagline'] ?? ''" placeholder="Transforming orchards across Kashmir through high-density farming, drip irrigation, and precision agriculture." helptext="Summary text appearing next to the logo in the website footer." />
                                <x-admin.input name="return_policy_text" label="Return Policy Text" :value="$settings['return_policy_text'] ?? ''" helptext="Shown on product detail and warranty pages." />
                            </div>
                        </div>
                    </div>

                    {{-- 2. Bank Account Details --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between flex-wrap gap-4 mb-1">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-lg font-semibold text-gray-900">Bank Account Details</h3>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                            Official Settlement Account
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500">Official bank coordinates used for customer NEFT, RTGS, IMPS bank payments, quotations, invoices, and website footer.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <x-admin.input name="bank_account_name" label="Account Name" :value="$settings['bank_account_name'] ?? 'Plant Tech Agro'" placeholder="Plant Tech Agro" required helptext="Exact legal name as registered in the bank account." />
                                <x-admin.input name="bank_name" label="Bank Name" :value="$settings['bank_name'] ?? 'J&K Bank'" placeholder="J&K Bank" required helptext="e.g. Jammu & Kashmir Bank (J&K Bank)" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                <x-admin.input name="bank_account_no" label="Account Number" :value="$settings['bank_account_no'] ?? '0942 0100 0000 0275'" placeholder="0942 0100 0000 0275" required class="font-mono" helptext="Primary account number for RTGS/NEFT settlements." />
                                <x-admin.input name="bank_branch" label="Branch Name" :value="$settings['bank_branch'] ?? 'Migrant Colony Hall Pulwama'" placeholder="Migrant Colony Hall Pulwama" required helptext="Bank branch location." />
                                <x-admin.input name="bank_ifsc" label="IFSC Code" :value="$settings['bank_ifsc'] ?? 'JAKA0MIGRNT'" placeholder="JAKA0MIGRNT" required class="font-mono uppercase" helptext="11-character Indian Financial System Code." />
                            </div>

                            {{-- Visual Preview Card --}}
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-900">Live Client Document Preview</span>
                                    </div>
                                    <span class="text-[11px] font-mono font-bold text-emerald-700">RTGS / NEFT / IMPS</span>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                                    <div>
                                        <span class="text-gray-500 text-[10.5px] block font-medium">Bank</span>
                                        <span class="font-bold text-gray-900">{{ $settings['bank_name'] ?? 'J&K Bank' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 text-[10.5px] block font-medium">Account Name</span>
                                        <span class="font-bold text-gray-900">{{ $settings['bank_account_name'] ?? 'Plant Tech Agro' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 text-[10.5px] block font-medium">Account No.</span>
                                        <span class="font-mono font-bold text-emerald-800 text-sm tracking-wide">{{ $settings['bank_account_no'] ?? '0942 0100 0000 0275' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 text-[10.5px] block font-medium">IFSC Code</span>
                                        <span class="font-mono font-bold text-gray-900 text-sm tracking-wider">{{ $settings['bank_ifsc'] ?? 'JAKA0MIGRNT' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Social Media Links --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center gap-3 mb-1">
                            <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Social Media Links</h3>
                                <p class="text-sm text-gray-500">Official profile URLs linked in the website footer and customer touchpoints. Leave blank to hide.</p>
                            </div>
                        </div>

                        <div class="mt-6 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-admin.input name="social_facebook" label="Facebook URL" :value="$settings['social_facebook'] ?? ''" placeholder="https://facebook.com/planttechagro" />
                                <x-admin.input name="social_instagram" label="Instagram URL" :value="$settings['social_instagram'] ?? ''" placeholder="https://instagram.com/planttechagro" />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <x-admin.input name="social_youtube" label="YouTube Channel" :value="$settings['social_youtube'] ?? ''" placeholder="https://youtube.com/@planttechagro" />
                                <x-admin.input name="social_whatsapp" label="WhatsApp Number / Link" :value="$settings['social_whatsapp'] ?? ''" placeholder="https://wa.me/917780995003" />
                                <x-admin.input name="social_x" label="X (Twitter) URL" :value="$settings['social_x'] ?? ''" placeholder="https://x.com/planttechagro" />
                            </div>
                        </div>
                    </div>

                    {{-- 4. System Maintenance Mode --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between flex-wrap gap-4 mb-2">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl {{ !empty($settings['maintenance_mode']) ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-700' }} flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">System Maintenance Mode</h3>
                                    <p class="text-sm text-gray-500">Lock the public website, POS, and customer mobile apps during planned upgrades or infrastructure maintenance. Administrators retain full access.</p>
                                </div>
                            </div>

                            @if(!empty($settings['maintenance_mode']))
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                        Maintenance Mode ACTIVE
                                    </span>
                                </div>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    Live / Public
                                </span>
                            @endif
                        </div>

                        <div class="mt-6 space-y-5">
                            <x-admin.checkbox name="maintenance_mode" label="Enable Maintenance Mode (Site-wide & Mobile Apps)" :checked="!empty($settings['maintenance_mode'])" help="When enabled, all visitors and mobile app customers are shown a friendly maintenance screen. Only logged-in administrators can access the system." />

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2">
                                <x-admin.input name="maintenance_title" label="Maintenance Screen Title" :value="$settings['maintenance_title'] ?? ''" placeholder="We're Undergoing Scheduled Maintenance" helptext="Heading displayed on the public maintenance page." />
                                <x-admin.input name="maintenance_message" label="Maintenance Notice / Message" :value="$settings['maintenance_message'] ?? ''" placeholder="We are currently performing scheduled upgrades and essential optimizations. Please check back shortly." helptext="Detailed announcement explaining the scheduled maintenance." />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <x-admin.button type="submit">Save General Settings</x-admin.button>
                    </div>

                </div>
            </div>

            {{-- Appearance Tab --}}
            <div x-show="activeTab === 'appearance'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="space-y-6">

                    {{-- Brand Color --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Brand Color</h3>
                        <p class="text-sm text-gray-500 mb-5">Choose a primary color palette for buttons, links, and highlights.</p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            @foreach($palettes as $key => $palette)
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="theme_palette" value="{{ $key }}" {{ ($settings['theme_palette'] ?? 'emerald') === $key ? 'checked' : '' }} class="peer sr-only">
                                    <div class="peer-checked:ring-2 peer-checked:ring-brand-600 peer-checked:ring-offset-2 rounded-2xl p-4 border border-gray-200 hover:border-gray-300 transition">
                                        <div class="flex gap-1 mb-3 justify-center">
                                            @foreach($palette['colors'] as $shade)
                                                @if(in_array($shade, [$palette['colors'][400], $palette['colors'][600], $palette['colors'][800]]))
                                                    <div class="w-6 h-6 rounded-full shadow-sm" style="background-color: {{ $shade }}"></div>
                                                @endif
                                            @endforeach
                                        </div>
                                        <p class="text-sm font-medium text-gray-700 text-center">{{ $palette['label'] }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Admin Sidebar Style --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Admin Sidebar</h3>
                        <p class="text-sm text-gray-500 mb-5">Choose the sidebar color scheme for the admin panel.</p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            @foreach($sidebarStyles as $value => $label)
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="sidebar_style" value="{{ $value }}" {{ ($settings['sidebar_style'] ?? 'dark') === $value ? 'checked' : '' }} class="peer sr-only">
                                    <div class="peer-checked:ring-2 peer-checked:ring-brand-600 peer-checked:ring-offset-2 rounded-2xl border border-gray-200 hover:border-gray-300 transition overflow-hidden">
                                        @php
                                            $sBg = $value === 'light' ? 'bg-white' : ($value === 'brand' ? 'bg-brand-700' : 'bg-gray-900');
                                            $sBorder = $value === 'light' ? 'border-b border-gray-100' : '';
                                            $sText = $value === 'light' ? 'text-gray-900' : 'text-white';
                                            $sBars = $value === 'light' ? ['bg-brand-100', 'bg-gray-200'] : ($value === 'brand' ? ['bg-white/20', 'bg-white/10'] : ['bg-brand-500/20', 'bg-gray-700']);
                                        @endphp
                                        <div class="{{ $sBg }} px-4 py-3 flex items-center gap-2 {{ $sBorder }}">
                                            <div class="w-3 h-3 rounded-full bg-brand-500"></div>
                                            <span class="text-xs {{ $sText }} font-medium">{{ config('shop.site_name', 'PTA Admin') }}</span>
                                        </div>
                                        <div class="{{ $sBg }} px-4 py-2 space-y-1">
                                            <div class="h-2 {{ $sBars[0] }} rounded w-3/4"></div>
                                            <div class="h-2 {{ $sBars[1] }} rounded w-1/2"></div>
                                        </div>
                                    </div>
                                    <p class="text-sm font-medium text-gray-700 text-center mt-2">{{ $label }}</p>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Font Family --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Font Family</h3>
                        <p class="text-sm text-gray-500 mb-5">Choose a font for the frontend storefront.</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($fonts as $fontName => $googleName)
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="font_family" value="{{ $fontName }}" {{ ($settings['font_family'] ?? 'Inter') === $fontName ? 'checked' : '' }} class="peer sr-only">
                                    <div class="peer-checked:ring-2 peer-checked:ring-brand-600 peer-checked:ring-offset-2 rounded-xl border border-gray-200 hover:border-gray-300 p-4 transition">
                                        <p class="text-xl text-gray-900 mb-1" style="font-family: '{{ $fontName }}', sans-serif">{{ $fontName }}</p>
                                        <p class="text-xs text-gray-500" style="font-family: '{{ $fontName }}', sans-serif">The quick brown fox jumps over the lazy dog</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Logo Upload --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6" x-data="{ preview: '{{ $settings['logo_url'] ?? '' }}', hasLogo: '{{ $settings['logo_url'] ?? '' }}' !== '' }">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Logo</h3>
                        <p class="text-sm text-gray-500 mb-5">Upload a logo for the admin sidebar and frontend header.</p>

                        <div class="flex items-start gap-6">
                            {{-- Preview --}}
                            <div class="shrink-0">
                                <div class="w-32 h-32 rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden">
                                    <template x-if="hasLogo">
                                        <img :src="preview" alt="Logo" class="w-full h-full object-contain p-2">
                                    </template>
                                    <template x-if="!hasLogo">
                                        <div class="text-center">
                                            <svg class="w-8 h-8 text-gray-300 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span class="text-xs text-gray-400">No logo</span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Upload --}}
                            <div class="flex-1 space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Upload Logo</label>
                                    <input type="file" name="logo_file" accept="image/png,image/jpeg,image/svg+xml"
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                                           x-on:change="const f = $event.target.files[0]; if (f) { const r = new FileReader(); r.onload = e => { preview = e.target.result; hasLogo = true }; r.readAsDataURL(f) }">
                                    <p class="mt-1.5 text-xs text-gray-400">PNG, JPG, or SVG. Max 2MB.</p>
                                </div>
                                @if(!empty($settings['logo_url']))
                                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                        <input type="checkbox" name="remove_logo" value="1" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                        Remove current logo
                                    </label>
                                @endif
                            </div>
                        </div>

                        {{-- Favicon Upload --}}
                        <div class="mt-6 pt-6 border-t border-gray-100" x-data="{ faviconPreview: '{{ $settings['favicon_url'] ?? '' }}', hasFavicon: '{{ $settings['favicon_url'] ?? '' }}' !== '' }">
                            <h4 class="text-sm font-semibold text-gray-900 mb-1">Favicon</h4>
                            <p class="text-sm text-gray-500 mb-4">The small icon shown on the browser tab and bookmarks.</p>

                            <div class="flex items-start gap-6">
                                {{-- Preview --}}
                                <div class="shrink-0">
                                    <div class="w-16 h-16 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden">
                                        <template x-if="hasFavicon">
                                            <img :src="faviconPreview" alt="Favicon" class="w-full h-full object-contain p-2">
                                        </template>
                                        <template x-if="!hasFavicon">
                                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118L2.077 10.1c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                        </template>
                                    </div>
                                </div>

                                {{-- Upload --}}
                                <div class="flex-1 space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Upload Favicon</label>
                                        <input type="file" name="favicon_file" accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml,image/x-icon,image/vnd.microsoft.icon,.ico"
                                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                                               x-on:change="const f = $event.target.files[0]; if (f) { const r = new FileReader(); r.onload = e => { faviconPreview = e.target.result; hasFavicon = true }; r.readAsDataURL(f) }">
                                        <p class="mt-1.5 text-xs text-gray-400">PNG, JPG, SVG, or ICO. Square, 32×32 or 64×64. Max 1MB.</p>
                                        @error('favicon_file')
                                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    @if(!empty($settings['favicon_url']))
                                        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                            <input type="checkbox" name="remove_favicon" value="1" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                            Remove current favicon
                                        </label>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <x-admin.button type="submit">Save Appearance Settings</x-admin.button>
                    </div>

                </div>
            </div>

            {{-- Invoices & Quotations Tab --}}
            <div x-show="activeTab === 'invoice'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="space-y-6">

                <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-blue-50 border border-emerald-200 rounded-2xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-gray-900">Services &amp; Work Stages Integration</h4>
                            <p class="text-xs text-gray-600 mt-0.5">Looking to configure pricing units (Kanals, Acres, Meters), package variations (150 vs 170 plants), or service workflow stages? Manage them directly under each service.</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.services.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-sm transition whitespace-nowrap">
                        <span>Go to Services</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>

                {{-- Company Billing Information --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-0.5">Company Details</h3>
                        <p class="text-sm text-gray-500">Official business identity displayed on generated invoices, estimates, and quotations.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.input name="invoice_company_name" label="Legal Business Name" :value="$invoiceSettings['company_name']" required />
                        <x-admin.input name="invoice_gst_no" label="GST Number" :value="$invoiceSettings['gst_no']" placeholder="e.g. 01ABCDE1234F1Z5" />
                        <x-admin.input name="invoice_phone" label="Billing Phone" :value="$invoiceSettings['phone']" />
                        <x-admin.input name="invoice_email" label="Billing Email" type="email" :value="$invoiceSettings['email']" />
                    </div>
                    <div>
                        <x-admin.textarea name="invoice_address" label="Billing / Dispatch Address" :value="$invoiceSettings['address']" rows="2" />
                    </div>
                </div>

                {{-- Numbering Sequences & Document Prefixes --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-0.5">Invoice Branding & Numbering</h3>
                        <p class="text-sm text-gray-500">Configure prefixes used for tax invoices and quotation estimates.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <x-admin.input name="invoice_prefix" label="Invoice Prefix" :value="$invoiceSettings['prefix']" required helptext="e.g. PTA -> formats as PTA/2026-27/0001" />
                        </div>
                        <div>
                            <x-admin.input name="quotation_prefix" label="Quotation / Estimate Prefix" :value="$quotationSettings['prefix'] ?? 'QT'" helptext="e.g. QT -> formats as QT/2026-27/0001" />
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100">
                        @if(!empty($invoiceSettings['logo']))
                            <p class="text-sm font-medium text-gray-700 mb-1.5">Current Document Logo</p>
                            <div class="w-32 h-16 rounded-xl border border-gray-200 bg-gray-50 overflow-hidden mb-3 flex items-center justify-center">
                                <img src="{{ \App\Support\Media::url($invoiceSettings['logo']) }}" alt="Invoice logo" class="max-h-full max-w-full object-contain">
                            </div>
                        @endif
                        <label for="invoice_logo_file" class="block text-sm font-medium text-gray-700 mb-1.5">Upload Document Logo</label>
                        <input type="file" name="invoice_logo_file" id="invoice_logo_file" accept="image/png,image/jpeg,image/svg+xml"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        <p class="mt-1.5 text-xs text-gray-400">PNG, JPG, or SVG shown on document headers.</p>
                        @if(!empty($invoiceSettings['logo']))
                            <label class="mt-2 flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                <input type="checkbox" name="remove_invoice_logo" value="1" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                Remove current logo
                            </label>
                        @endif
                        @error('invoice_logo_file')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Default Terms & Conditions --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-0.5">Global Terms &amp; Conditions</h3>
                        <p class="text-sm text-gray-500">Default legal terms printed at the bottom of invoices and quotations when no service-specific terms apply.</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <x-admin.textarea name="invoice_terms" label="Default Invoice Terms & Conditions" :value="$invoiceSettings['terms']" rows="4" placeholder="Default payment terms, bank transfer timelines, jurisdiction..." />
                        </div>
                        <div>
                            <x-admin.textarea name="quotation_terms" label="Default Quotation Terms & Conditions" :value="$quotationSettings['terms'] ?? ''" rows="4" placeholder="Default validity, advance payment terms, site access terms..." />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <x-admin.button type="submit">Save Invoice &amp; Quotation Settings</x-admin.button>
                </div>

            </div>

            {{-- SEO Tab --}}
            <div x-show="activeTab === 'seo'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="space-y-6">

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Search Engine Optimization</h3>
                    <p class="text-sm text-gray-500 mb-5">Site-wide defaults used in the head of every public page. Individual pages can override the title, but these apply everywhere else.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.input name="seo_meta_title" label="Meta Title" :value="$seoSettings['meta_title'] ?? ''" placeholder="e.g. Plant Tech Agro — Agritech Services in Kashmir" helptext="Shown as the browser tab and search result title (up to 120 chars). Blank uses the store name." />
                        <x-admin.input name="seo_author" label="Author" :value="$seoSettings['author'] ?? ''" placeholder="e.g. Plant Tech Agro" helptext="Adds an author meta tag to every page." />
                        <div class="md:col-span-2">
                            <x-admin.textarea name="seo_meta_description" label="Meta Description" :value="$seoSettings['meta_description'] ?? ''" rows="3" placeholder="Short description of your store (up to 500 chars)" />
                        </div>
                        <div class="md:col-span-2">
                            <x-admin.input name="seo_meta_keywords" label="Meta Keywords" :value="$seoSettings['meta_keywords'] ?? ''" placeholder="farming, irrigation, Kashmir agriculture" helptext="Comma-separated keywords. Blank to omit the tag." />
                        </div>
                        <div class="md:col-span-2">
                            <x-admin.input name="seo_canonical_url" label="Canonical URL" :value="$seoSettings['canonical_url'] ?? ''" placeholder="https://plantechagro.com/" helptext="Preferred base URL for indexing. Blank uses the current page URL automatically." />
                        </div>
                        <div class="md:col-span-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-admin.checkbox name="seo_robots_index" label="Allow indexing" :checked="$seoSettings['robots_index'] ?? true" help="Adds noindex if off" />
                                <x-admin.checkbox name="seo_robots_follow" label="Allow following links" :checked="$seoSettings['robots_follow'] ?? true" help="Adds nofollow if off" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Social Sharing — Open Graph</h3>
                    <p class="text-sm text-gray-500 mb-5">Controls how pages look when shared on Facebook, WhatsApp, LinkedIn, Telegram and other Open Graph clients.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <x-admin.checkbox name="seo_og_enabled" label="Enable Open Graph tags" :checked="$seoSettings['og_enabled'] ?? true" />
                        </div>
                        <x-admin.select name="seo_og_type" label="Content Type"
                            :options="[
                                'website' => 'Website',
                                'article' => 'Article',
                                'product' => 'Product',
                                'profile' => 'Profile',
                                'business.business' => 'Business',
                                'book' => 'Book',
                                'music.song' => 'Music',
                                'video.movie' => 'Movie',
                            ]"
                            :value="$seoSettings['og_type'] ?? 'website'" />
                        <x-admin.input name="seo_og_site_name" label="Site Name" :value="$seoSettings['og_site_name'] ?? ''" placeholder="e.g. Plant Tech Agro" helptext="Blank uses the store name." />
                        <x-admin.input name="seo_og_title" label="Share Title" :value="$seoSettings['og_title'] ?? ''" placeholder="e.g. High-Density Orchard Development" helptext="Blank uses the page title." />
                        <div class="md:col-span-2">
                            <x-admin.textarea name="seo_og_description" label="Share Description" :value="$seoSettings['og_description'] ?? ''" rows="2" placeholder="Blank uses the meta description." />
                        </div>
                        <div class="md:col-span-2">
                            <x-admin.input name="seo_og_image" label="Share Image URL" :value="$seoSettings['og_image'] ?? ''" placeholder="https://plantechagro.com/og-image.jpg or /storage/seo/xxx.jpg" helptext="Optional. Paste an external URL, or upload an image below. Blank falls back to the search image, then the store logo." />
                        </div>
                        <div class="md:col-span-2">
                            <x-admin.image-upload name="seo_og_image_file" label="Upload Share Image" :value="$seoSettings['og_image'] ?? ''" remove-name="remove_seo_og_image_file" helptext="1200×630 recommended (JPG, PNG, WebP). Max 4MB. Uploading replaces the URL above." />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Social Sharing — X (Twitter) Card</h3>
                    <p class="text-sm text-gray-500 mb-5">How pages look when shared on X/Twitter. Open Graph values are used as fallbacks.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <x-admin.checkbox name="seo_twitter_enabled" label="Enable Twitter Card tags" :checked="$seoSettings['twitter_enabled'] ?? true" />
                        </div>
                        <x-admin.select name="seo_twitter_card" label="Card Type"
                            :options="[
                                'summary_large_image' => 'Summary with Large Image (recommended)',
                                'summary' => 'Summary',
                                'app' => 'App',
                                'player' => 'Player',
                            ]"
                            :value="$seoSettings['twitter_card'] ?? 'summary_large_image'" />
                        <x-admin.input name="seo_twitter_site" label="Site Handle" :value="$seoSettings['twitter_site'] ?? ''" placeholder="@plantechagro" helptext="Optional @handle shown on the card." />
                        <x-admin.input name="seo_twitter_title" label="Card Title" :value="$seoSettings['twitter_title'] ?? ''" placeholder="Blank uses the page title." />
                        <div class="md:col-span-2">
                            <x-admin.textarea name="seo_twitter_description" label="Card Description" :value="$seoSettings['twitter_description'] ?? ''" rows="2" placeholder="Blank uses the meta description." />
                        </div>
                        <div class="md:col-span-2">
                            <x-admin.input name="seo_twitter_image" label="Card Image URL" :value="$seoSettings['twitter_image'] ?? ''" placeholder="https://plantechagro.com/tw-image.jpg or /storage/seo/xxx.jpg" helptext="Optional. Paste an external URL, or upload an image below. Blank falls back to the Open Graph image." />
                        </div>
                        <div class="md:col-span-2">
                            <x-admin.image-upload name="seo_twitter_image_file" label="Upload Card Image" :value="$seoSettings['twitter_image'] ?? ''" remove-name="remove_seo_twitter_image_file" helptext="1200×600 recommended (JPG, PNG, WebP). Max 4MB. Uploading replaces the URL above." />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Search Engines & Structured Data</h3>
                    <p class="text-sm text-gray-500 mb-5">Ownership verification codes and schema.org markup for richer search results.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.input name="seo_google_site_verification" label="Google Site Verification" :value="$seoSettings['google_site_verification'] ?? ''" placeholder="Google code from Search Console" helptext="Paste the content between the Google meta tag quotes." />
                        <x-admin.input name="seo_bing_site_verification" label="Bing Site Verification" :value="$seoSettings['bing_site_verification'] ?? ''" placeholder="Bing code from Webmaster Tools" helptext="Paste the content between the msvalidate.01 meta tag quotes." />
                        <x-admin.input name="seo_yandex_verification" label="Yandex Site Verification" :value="$seoSettings['yandex_verification'] ?? ''" placeholder="Yandex code from Webmaster" helptext="Paste the content between the yandex-verification meta tag quotes." />
                        <div class="md:col-span-2">
                            <x-admin.image-upload name="seo_search_image_file" label="Search Engine Image" :value="$seoSettings['search_image'] ?? ''" remove-name="remove_seo_search_image_file" helptext="Used in structured data (schema.org) and as a fallback for link previews in search results. 1200×630 recommended (JPG, PNG, WebP). Max 4MB." />
                        </div>
                        <div class="md:col-span-2">
                            <x-admin.checkbox name="seo_schema_enabled" label="Enable Organization structured data" :checked="$seoSettings['schema_enabled'] ?? true" help="Emits schema.org JSON-LD built from store details" />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <x-admin.button type="submit">Save SEO Settings</x-admin.button>
                </div>

            </div>

            {{-- Weather Tab --}}
            <div x-show="activeTab === 'weather'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="space-y-6">

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Weather Service</h3>
                    <p class="text-sm text-gray-500 mb-5">Free Open-Meteo forecasts for farmers. Switching the service off stops all upstream calls and hides weather everywhere.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-admin.checkbox name="weather_enabled" label="Enable weather service" :checked="$weatherSettings['enabled'] ?? true" help="Master kill switch — off hides all farmer-facing weather immediately" />
                        <x-admin.checkbox name="weather_admin_preview" label="Admin preview when disabled" :checked="$weatherSettings['admin_preview'] ?? true" help="Show a live preview in this tab even while disabled for farmers" />
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Location</h3>
                    <p class="text-sm text-gray-500 mb-5">Farmers are matched to districts by their profile area. Blank or unknown areas use the default district.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.select name="weather_default_district" label="Default District"
                            :options="collect($weatherDistricts)->mapWithKeys(fn ($d, $k) => [$k => $d['label']])->all()"
                            :value="$weatherSettings['default_district'] ?? 'srinagar'" />
                    </div>
                    <div class="mt-5">
                        <p class="text-sm font-medium text-gray-700 mb-3">District Coordinates</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($weatherDistricts as $districtKey => $district)
                                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4">
                                    <p class="text-sm font-semibold text-gray-800 mb-2">{{ $district['label'] }}</p>
                                    <div class="grid grid-cols-2 gap-3">
                                        <x-admin.input name="weather_district_{{ $districtKey }}_lat" label="Latitude" type="number" step="any" :value="$district['lat']" />
                                        <x-admin.input name="weather_district_{{ $districtKey }}_lon" label="Longitude" type="number" step="any" :value="$district['lon']" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Fetching</h3>
                    <p class="text-sm text-gray-500 mb-5">How forecasts are pulled from Open-Meteo. One cached entry per district is shared by all farmers.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.input name="weather_cache_ttl_minutes" label="Cache (minutes)" type="number" :value="$weatherSettings['cache_ttl_minutes'] ?? 60" helptext="15–180. Shared per district." />
                        <x-admin.input name="weather_timeout_seconds" label="Request Timeout (seconds)" type="number" :value="$weatherSettings['timeout_seconds'] ?? 5" helptext="2–15." />
                        <x-admin.input name="weather_retries" label="Retries" type="number" :value="$weatherSettings['retries'] ?? 2" helptext="0–3." />
                        <x-admin.select name="weather_units" label="Units"
                            :options="['metric' => 'Metric (°C, km/h)', 'imperial' => 'Imperial (°F, mph)']"
                            :value="$weatherSettings['units'] ?? 'metric'" />
                        <div class="md:col-span-2">
                            <x-admin.input name="weather_timezone" label="Timezone" :value="$weatherSettings['timezone'] ?? 'auto'" helptext="Upstream timezone parameter. Keep auto." />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Forecast Content</h3>
                    <p class="text-sm text-gray-500 mb-5">Which blocks are included in the payload sent to farmers.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.checkbox name="weather_include_current" label="Include current conditions" :checked="$weatherSettings['include_current'] ?? true" />
                        <x-admin.select name="weather_forecast_days" label="Forecast Days"
                            :options="['0' => 'Current only', '3' => '3 days', '7' => '7 days', '16' => '16 days']"
                            :value="(string) ($weatherSettings['forecast_days'] ?? 7)" />
                        <div class="md:col-span-2">
                            <x-admin.checkbox name="weather_include_hourly" label="Include next-24h hourly strip" :checked="$weatherSettings['include_hourly'] ?? false" help="Temperature + rain probability. Increases payload size." />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Orchard Advisory</h3>
                    <p class="text-sm text-gray-500 mb-5">Rule-based spray, frost and irrigation hints. Shown as indicative guidance, not agronomist advice.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <x-admin.checkbox name="weather_advisory_enabled" label="Enable advisory block" :checked="$weatherSettings['advisory_enabled'] ?? true" />
                        </div>
                        <x-admin.input name="weather_frost_threshold_c" label="Frost Threshold (°C)" type="number" step="any" :value="$weatherSettings['frost_threshold_c'] ?? 2" helptext="-10 to 10." />
                        <x-admin.input name="weather_spray_wind_kmh" label="Spray Wind Limit (km/h)" type="number" step="any" :value="$weatherSettings['spray_wind_kmh'] ?? 20" helptext="Above this, spraying is discouraged." />
                        <x-admin.input name="weather_spray_rain_prob" label="Spray Rain Limit (%)" type="number" :value="$weatherSettings['spray_rain_prob'] ?? 50" helptext="Above this, spraying is discouraged." />
                        <x-admin.input name="weather_heat_threshold_c" label="Heat Threshold (°C)" type="number" step="any" :value="$weatherSettings['heat_threshold_c'] ?? 30" helptext="Above this with no rain, irrigation is suggested." />
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Where Weather Appears</h3>
                    <p class="text-sm text-gray-500 mb-5">Stage the rollout per surface — e.g. test on the admin card before enabling mobile.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-admin.checkbox name="weather_show_admin_card" label="Admin dashboard card" :checked="$weatherSettings['show_admin_card'] ?? true" />
                        <x-admin.checkbox name="weather_show_api_dashboard" label="Customer app dashboard" :checked="$weatherSettings['show_api_dashboard'] ?? true" />
                        <x-admin.checkbox name="weather_show_app_config" label="App config snapshot" :checked="$weatherSettings['show_app_config'] ?? true" />
                        <x-admin.checkbox name="weather_api_endpoint_enabled" label="Standalone /api/weather endpoint" :checked="$weatherSettings['api_endpoint_enabled'] ?? true" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <x-admin.button type="submit">Save Weather Settings</x-admin.button>
                </div>

            </div>

            {{-- APIs Tab --}}
            <div x-show="activeTab === 'apis'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="space-y-6">

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">API Integrations</h3>
                    <p class="text-sm text-gray-500 mb-5">Third-party keys used across the platform. New integrations will appear here as additional cards.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-admin.checkbox name="apis_recaptcha_enabled" label="Enable Google reCAPTCHA v3" :checked="$apisSettings['recaptcha_enabled'] ?? false" help="Invisible bot protection on the lead form and admin login" />
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Google reCAPTCHA v3</h3>
                    <p class="text-sm text-gray-500 mb-5">Invisible score-based protection. Get keys from the Google reCAPTCHA admin console (v3). Submissions scoring below the threshold are rejected.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.input name="apis_recaptcha_site_key" label="Site Key" :value="$apisSettings['recaptcha_site_key'] ?? ''" placeholder="6Lc..." helptext="Public key — embedded in the lead form and login page." />
                        <x-admin.input name="apis_recaptcha_secret_key" label="Secret Key" type="password" :value="''" placeholder="{{ ($apisSettings['has_secret_key'] ?? false) ? 'Saved — leave blank to keep it' : '6Lc...' }}" helptext="{{ ($apisSettings['has_secret_key'] ?? false) ? 'A secret is already saved. Leave blank to keep it.' : 'Private key — never shown again after saving.' }}" />
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900">Firebase Cloud Messaging (FCM Push)</h3>
                                <p class="text-xs text-gray-500">Google HTTP v1 Push Notifications for Customer Mobile App (tickets, order status, broadcasts).</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.mobile-apps.index', ['tab' => 'push']) }}" class="px-4 py-2 rounded-xl bg-brand-50 text-brand-700 hover:bg-brand-100 text-xs font-semibold transition flex items-center gap-1.5">
                            <span>Manage Firebase & Push</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <x-admin.button type="submit">Save API Settings</x-admin.button>
                </div>

            </div>

            {{-- Photo Compression & Media Tab --}}
            <div x-show="activeTab === 'media'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="space-y-6">
                
                {{-- Master Configuration Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
                    <div class="flex items-start justify-between gap-4 pb-4 border-b border-gray-100">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-semibold text-gray-900">Automatic Photo Compression</h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Admin Panel + Mobile App
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">
                                Automatically downscales and compresses all photos uploaded by farmers, field agents, and admins to maximize storage efficiency and accelerate mobile app load speeds.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <x-admin.checkbox name="media_compression_enabled" label="Enable Automatic Photo Compression" :checked="$mediaSettings['compression_enabled'] ?? true" help="When enabled, all photos uploaded through the Admin Panel or Mobile App are optimized before saving." />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Maximum Dimension (px)</label>
                                <select name="media_max_dimension" class="w-full text-sm rounded-xl border-gray-200 focus:border-brand-500 focus:ring-brand-500 py-2.5">
                                    <option value="1280" {{ ($mediaSettings['max_dimension'] ?? 1920) == 1280 ? 'selected' : '' }}>1280 px (Compact & Ultra Fast)</option>
                                    <option value="1600" {{ ($mediaSettings['max_dimension'] ?? 1920) == 1600 ? 'selected' : '' }}>1600 px (Standard Web)</option>
                                    <option value="1920" {{ ($mediaSettings['max_dimension'] ?? 1920) == 1920 ? 'selected' : '' }}>1920 px (Full HD — Recommended)</option>
                                    <option value="2560" {{ ($mediaSettings['max_dimension'] ?? 1920) == 2560 ? 'selected' : '' }}>2560 px (High Res 2K)</option>
                                </select>
                                <p class="text-xs text-gray-400 mt-1">Photos larger than this bounding box are proportionally downscaled to save memory and space.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Compression Quality (1 - 100)</label>
                                <div class="flex items-center gap-3">
                                    <input type="number" name="media_quality" min="30" max="100" value="{{ $mediaSettings['quality'] ?? 82 }}" class="w-28 text-sm rounded-xl border-gray-200 focus:border-brand-500 focus:ring-brand-500 py-2">
                                    <span class="text-xs text-gray-500">Recommended: <strong>80 – 85%</strong> (Imperceptible quality difference, massive size reduction)</span>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">Lower quality uses less disk space; higher quality preserves more uncompressed details.</p>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-admin.checkbox name="media_convert_to_webp" label="Auto-convert Photos to WebP" :checked="$mediaSettings['convert_to_webp'] ?? true" help="Saves an additional ~30-40% storage space compared to standard JPG without visual quality loss." />

                            <x-admin.checkbox name="media_auto_orient" label="Auto-orient from Phone Camera Sensors (EXIF)" :checked="$mediaSettings['auto_orient'] ?? true" help="Fixes rotated/upside-down photos automatically based on the smartphone's camera orientation." />
                        </div>
                    </div>
                </div>

                {{-- Interactive Live Test Tool --}}
                <div class="bg-gradient-to-br from-gray-50 to-white rounded-2xl shadow-sm border border-gray-200 p-6" x-data="{
                    testing: false,
                    result: null,
                    error: null,
                    testCompress() {
                        const fileInput = document.getElementById('test-photo-input');
                        if (!fileInput.files || fileInput.files.length === 0) {
                            alert('Please select an image file to test.');
                            return;
                        }

                        this.testing = true;
                        this.result = null;
                        this.error = null;

                        const formData = new FormData();
                        formData.append('test_image', fileInput.files[0]);
                        formData.append('_token', '{{ csrf_token() }}');

                        fetch('{{ route('admin.settings.media.test') }}', {
                            method: 'POST',
                            headers: { 'Accept': 'application/json' },
                            body: formData
                        })
                        .then(r => r.json())
                        .then(data => {
                            this.testing = false;
                            if (data.success) {
                                this.result = data.data;
                            } else {
                                this.error = data.message || 'Compression test failed.';
                            }
                        })
                        .catch(err => {
                            this.testing = false;
                            this.error = 'Network error during test.';
                        });
                    },
                    formatBytes(bytes) {
                        if (bytes === 0) return '0 Bytes';
                        const k = 1024;
                        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                        const i = Math.floor(Math.log(bytes) / Math.log(k));
                        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                    }
                }">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Live Compression Preview Tool</h3>
                            <p class="text-xs text-gray-500">Pick any photo from your computer to see how much space the system will save.</p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-3">
                        <input type="file" id="test-photo-input" accept="image/jpeg,image/png,image/webp" class="block w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                        <button type="button" @click="testCompress()" :disabled="testing" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs transition shadow-sm flex items-center justify-center gap-2 flex-shrink-0 disabled:opacity-50">
                            <span x-show="!testing">Run Test</span>
                            <span x-show="testing" x-cloak class="flex items-center gap-1.5">
                                <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Compressing...
                            </span>
                        </button>
                    </div>

                    {{-- Error message --}}
                    <div x-show="error" x-cloak class="mt-4 p-3 rounded-xl bg-red-50 text-red-700 text-xs border border-red-200" x-text="error"></div>

                    {{-- Success Results Card --}}
                    <div x-show="result" x-cloak class="mt-4 p-4 rounded-xl bg-emerald-50/80 border border-emerald-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-emerald-950 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Optimization Complete!
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-emerald-600 text-white shadow-xs">
                                <span x-text="result ? result.saved_percent + '% Space Saved' : ''"></span>
                            </span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs pt-1">
                            <div class="bg-white/80 p-2.5 rounded-lg border border-emerald-100">
                                <span class="text-gray-400 block text-[10px] uppercase font-semibold">Original Size</span>
                                <span class="font-bold text-gray-800 text-sm" x-text="result ? formatBytes(result.original_size) : ''"></span>
                                <span class="text-[10px] text-gray-500 block" x-text="result ? result.original_dimensions : ''"></span>
                            </div>
                            <div class="bg-white/80 p-2.5 rounded-lg border border-emerald-100">
                                <span class="text-gray-400 block text-[10px] uppercase font-semibold">Optimized Size</span>
                                <span class="font-bold text-emerald-700 text-sm" x-text="result ? formatBytes(result.compressed_size) : ''"></span>
                                <span class="text-[10px] text-emerald-600 font-semibold block" x-text="result ? result.compressed_dimensions + ' (' + result.output_format + ')' : ''"></span>
                            </div>
                            <div class="bg-white/80 p-2.5 rounded-lg border border-emerald-100">
                                <span class="text-gray-400 block text-[10px] uppercase font-semibold">Total Saved</span>
                                <span class="font-bold text-emerald-800 text-sm" x-text="result ? formatBytes(result.saved_bytes) : ''"></span>
                                <span class="text-[10px] text-gray-500 block">Preserved Quality</span>
                            </div>
                            <div class="bg-white/80 p-2.5 rounded-lg border border-emerald-100">
                                <span class="text-gray-400 block text-[10px] uppercase font-semibold">Reduction Ratio</span>
                                <span class="font-bold text-brand-700 text-sm" x-text="result ? result.saved_percent + '%' : ''"></span>
                                <span class="text-[10px] text-brand-600 block">Lower Storage Bill</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <x-admin.button type="submit">Save Photo Compression Settings</x-admin.button>
                </div>

            </div>
        </form>

        {{-- Email / SMTP Tab (separate form so the test button can post independently) --}}
        <form action="{{ route('admin.settings.mail.update') }}" method="POST" x-show="activeTab === 'smtp'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="space-y-6">
            @csrf

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Mail Driver</h3>
                <p class="text-sm text-gray-500 mb-5">Choose the transport used for all outgoing notifications and follow-up emails.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <x-admin.select name="default" label="Send Method"
                            :options="[
                                'smtp' => 'SMTP (recommended)',
                                'log' => 'Log to file (development, no real delivery)',
                                'array' => 'Array (discard, dev only)',
                            ]"
                            :value="old('default', $mailSettings['default'])" />
                    </div>

                    <x-admin.input name="smtp_host" label="SMTP Host" :value="old('smtp_host', $mailSettings['smtp_host'])" placeholder="e.g. smtp.gmail.com" helptext="Leave blank to keep methods like Gmail/Outlook defaults." />

                    <x-admin.input name="smtp_port" label="SMTP Port" type="number" :value="old('smtp_port', $mailSettings['smtp_port'])" placeholder="e.g. 587" helptext="587 (TLS) or 465 (SSL)." />

                    <x-admin.input name="smtp_username" label="SMTP Username" :value="old('smtp_username', $mailSettings['smtp_username'])" placeholder="you@gmail.com" />

                    <x-admin.input name="smtp_password" label="SMTP Password / App Password"
                        type="password"
                        :value="old('smtp_password', $mailSettings['has_password'] ? '_____' : '')"
                        helptext="{{ $mailSettings['has_password'] ? 'A password is already saved. Leave blank to keep it.' : 'App passwords recommended (e.g. Gmail).' }}" />

                    <x-admin.select name="smtp_encryption" label="Encryption"
                        :options="['none' => 'None', 'tls' => 'TLS', 'ssl' => 'SSL']"
                        :value="old('smtp_encryption', $mailSettings['smtp_encryption'])" />
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Sender Address</h3>
                <p class="text-sm text-gray-500 mb-5">Used as the "from" address on every email your customers receive.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-admin.input name="from_address" label="From Email" type="email" :value="old('from_address', $mailSettings['from_address'])" required />
                    <x-admin.input name="from_name" label="From Name" :value="old('from_name', $mailSettings['from_name'])" required />
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <x-admin.button type="submit" variant="secondary" formaction="{{ route('admin.settings.mail.test') }}">Send Test Email</x-admin.button>
                <x-admin.button type="submit">Save Mail Settings</x-admin.button>
            </div>
        </form>
    </div>
@endsection
