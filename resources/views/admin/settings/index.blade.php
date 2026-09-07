@extends('admin.layout')

@section('page-title', 'Settings')

@section('content')
    <div class="space-y-6" x-data="{ activeTab: '{{ request('tab', 'general') }}' }">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Settings</h2>
        </div>

        {{-- Tab Navigation --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-2 py-1">
            <nav class="flex gap-1 overflow-x-auto" aria-label="Settings tabs">
                <button @click="activeTab = 'general'"
                    :class="activeTab === 'general' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    General
                </button>
                <button @click="activeTab = 'appearance'"
                    :class="activeTab === 'appearance' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                    Appearance
                </button>
                <button @click="activeTab = 'invoice'"
                    :class="activeTab === 'invoice' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8h4m-4 4h4"/></svg>
                    Invoice
                </button>
                <button @click="activeTab = 'seo'"
                    :class="activeTab === 'seo' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.1-4.4a5.5 5.5 0 11-11 0 5.5 5.5 0 0111 0z"/></svg>
                    SEO
                </button>
                <button @click="activeTab = 'smtp'"
                    :class="activeTab === 'smtp' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Email / SMTP
                </button>
            </nav>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="tab" x-model="activeTab">

            {{-- General Tab --}}
            <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="space-y-6">

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Store Information</h3>
                        <p class="text-sm text-gray-500 mb-5">Basic details about your platform.</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <x-admin.input name="site_name" label="Store Name" :value="$settings['site_name'] ?? ''" />
                            <x-admin.input name="site_email" label="Store Email" type="email" :value="$settings['site_email'] ?? ''" />
                            <x-admin.input name="site_phone" label="Store Phone" :value="$settings['site_phone'] ?? ''" />
                            <x-admin.input name="support_hours" label="Support Hours" :value="$settings['support_hours'] ?? ''" helptext="Shown in the footer contact section." />
                            <x-admin.input name="site_address" label="Store Address" :value="$settings['site_address'] ?? ''" helptext="Shown in the footer contact section." />
                            <x-admin.input name="footer_tagline" label="Footer Tagline" :value="$settings['footer_tagline'] ?? ''" helptext="Short description shown in the footer." />
                            <x-admin.input name="return_policy_text" label="Return Policy Text" :value="$settings['return_policy_text'] ?? ''" helptext="Shown on the product detail page." />
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Social Media</h3>
                        <p class="text-sm text-gray-500 mb-5">Links shown in the store footer and available to the mobile app. Leave blank to hide a platform.</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <x-admin.input name="social_facebook" label="Facebook URL" :value="$settings['social_facebook'] ?? ''" placeholder="https://facebook.com/yourpage" helptext="Blank to hide" />
                            <x-admin.input name="social_instagram" label="Instagram URL" :value="$settings['social_instagram'] ?? ''" placeholder="https://instagram.com/yourhandle" helptext="Blank to hide" />
                            <x-admin.input name="social_youtube" label="YouTube URL" :value="$settings['social_youtube'] ?? ''" placeholder="https://youtube.com/@yourchannel" helptext="Blank to hide" />
                            <x-admin.input name="social_whatsapp" label="WhatsApp" :value="$settings['social_whatsapp'] ?? ''" placeholder="https://wa.me/919999999999 or +91 99999 99999" helptext="Full link or phone number. Blank to hide" />
                            <x-admin.input name="social_x" label="X (Twitter) URL" :value="$settings['social_x'] ?? ''" placeholder="https://x.com/yourhandle" helptext="Blank to hide" />
                        </div>
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
                                           onchange="if(this.files[0]){const r=new FileReader();r.onload=e=>{preview=e.target.result;hasLogo=true};r.readAsDataURL(this.files[0])}">
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
                    </div>

                </div>
            </div>

            {{-- Invoice Tab --}}
            <div x-show="activeTab === 'invoice'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="space-y-6">

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Company Details</h3>
                    <p class="text-sm text-gray-500 mb-5">Shown on every generated invoice.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.input name="invoice_company_name" label="Company Name" :value="$invoiceSettings['company_name']" required />
                        <x-admin.input name="invoice_gst_no" label="GST Number" :value="$invoiceSettings['gst_no']" placeholder="e.g. 01ABCDE1234F1Z5" />
                        <x-admin.input name="invoice_phone" label="Phone Number" :value="$invoiceSettings['phone']" />
                        <x-admin.input name="invoice_email" label="Email" type="email" :value="$invoiceSettings['email']" />
                    </div>
                    <div class="mt-5">
                        <x-admin.textarea name="invoice_address" label="Address" :value="$invoiceSettings['address']" rows="2" />
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Invoice Branding & Numbering</h3>
                    <p class="text-sm text-gray-500 mb-5">Logo and invoice number format. Invoice numbers are generated as PREFIX/YEAR/0001.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.input name="invoice_prefix" label="Invoice Prefix" :value="$invoiceSettings['prefix']" required helptext="Letters, numbers and dashes only. e.g. PTA" />
                        <div>
                            @if(!empty($invoiceSettings['logo']))
                                <p class="text-sm font-medium text-gray-700 mb-1.5">Current Logo</p>
                                <div class="w-32 h-16 rounded-xl border border-gray-200 bg-gray-50 overflow-hidden mb-3 flex items-center justify-center">
                                    <img src="{{ \App\Support\Media::url($invoiceSettings['logo']) }}" alt="Invoice logo" class="max-h-full max-w-full object-contain">
                                </div>
                            @endif
                            <label for="invoice_logo_file" class="block text-sm font-medium text-gray-700 mb-1.5">Invoice Logo</label>
                            <input type="file" name="invoice_logo_file" id="invoice_logo_file" accept="image/png,image/jpeg,image/svg+xml"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <p class="mt-1.5 text-xs text-gray-400">PNG, JPG, or SVG. Shown on the invoice header.</p>
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
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Terms & Conditions</h3>
                    <p class="text-sm text-gray-500 mb-5">Default terms printed at the bottom of every invoice.</p>
                    <x-admin.textarea name="invoice_terms" label="Invoice Terms" :value="$invoiceSettings['terms']" rows="4" />
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
                            <x-admin.input name="seo_og_image" label="Share Image" :value="$seoSettings['og_image'] ?? ''" placeholder="https://plantechagro.com/og-image.jpg or /storage/logos/xxx.png" helptext="1200×630 recommended. Full URL or a /storage/... path. Blank falls back to the store logo." />
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
                            <x-admin.input name="seo_twitter_image" label="Card Image" :value="$seoSettings['twitter_image'] ?? ''" placeholder="https://plantechagro.com/tw-image.jpg or /storage/logos/xxx.png" helptext="Full URL or /storage/... path. Blank falls back to the Open Graph image." />
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
                            <x-admin.checkbox name="seo_schema_enabled" label="Enable Organization structured data" :checked="$seoSettings['schema_enabled'] ?? true" help="Emits schema.org JSON-LD built from store details" />
                        </div>
                    </div>
                </div>

            </div>

            {{-- Save Button --}}
            <div class="flex justify-end">
                <x-admin.button type="submit">Save All Settings</x-admin.button>
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
