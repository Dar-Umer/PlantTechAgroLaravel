@extends('admin.layout')

@section('page-title', 'Mobile Apps')

@section('content')
    <div class="space-y-6" x-data="{ activeTab: '{{ request('tab', 'appearance') }}' }">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Mobile Apps</h2>
                <p class="text-sm text-gray-500 mt-1">Manage how your customer app looks, its version, and rollout behaviour.</p>
            </div>
        </div>

        {{-- Tab Navigation --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-2 py-1">
            <nav class="flex gap-1 overflow-x-auto" aria-label="Mobile apps tabs">
                <button @click="activeTab = 'appearance'"
                    :class="activeTab === 'appearance' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                    Colour & Look
                </button>
                <button @click="activeTab = 'version'"
                    :class="activeTab === 'version' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Version & Update
                </button>
                <button @click="activeTab = 'general'"
                    :class="activeTab === 'general' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-1.066 2.573c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    General
                </button>
                <button @click="activeTab = 'push'"
                    :class="activeTab === 'push' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Push Notifications (FCM)
                    @if($totalDevices > 0)
                        <span class="px-1.5 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">{{ $totalDevices }}</span>
                    @endif
                </button>
                <a href="{{ route('admin.notification-templates.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Templates & Tags &rarr;
                </a>
            </nav>
        </div>

        <form action="{{ route('admin.mobile-apps.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="tab" x-model="activeTab">

            {{-- Colour & Look Tab --}}
            <div x-show="activeTab === 'appearance'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">

                {{-- App Colour --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">App Colour</h3>
                    <p class="text-sm text-gray-500 mb-5">Choose the colour palette used in the customer app. "Follow website" keeps the app in sync with your website Appearance settings.</p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="app_palette" value="" {{ ($settings['app_palette'] ?? '') === '' ? 'checked' : '' }} class="peer sr-only">
                            <div class="peer-checked:ring-2 peer-checked:ring-brand-600 peer-checked:ring-offset-2 rounded-2xl p-4 border border-gray-200 hover:border-gray-300 transition">
                                <div class="flex gap-1 mb-3 justify-center">
                                    <div class="w-6 h-6 rounded-full bg-gradient-to-br from-brand-400 to-brand-700 shadow-sm"></div>
                                </div>
                                <p class="text-sm font-medium text-gray-700 text-center">Follow website</p>
                            </div>
                        </label>
                        @foreach($palettes as $key => $palette)
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="app_palette" value="{{ $key }}" {{ ($settings['app_palette'] ?? '') === $key ? 'checked' : '' }} class="peer sr-only">
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

                {{-- App Font --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">App Font</h3>
                    <p class="text-sm text-gray-500 mb-5">Typography used across the app screens.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="app_font_family" value="" {{ ($settings['app_font_family'] ?? '') === '' ? 'checked' : '' }} class="peer sr-only">
                            <div class="peer-checked:ring-2 peer-checked:ring-brand-600 peer-checked:ring-offset-2 rounded-xl border border-gray-200 hover:border-gray-300 p-4 transition">
                                <p class="text-xl text-gray-900 mb-1">Follow website</p>
                                <p class="text-xs text-gray-500">Uses the font selected under Settings → Appearance.</p>
                            </div>
                        </label>
                        @foreach($fonts as $fontName => $googleName)
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="app_font_family" value="{{ $fontName }}" {{ ($settings['app_font_family'] ?? '') === $fontName ? 'checked' : '' }} class="peer sr-only">
                                <div class="peer-checked:ring-2 peer-checked:ring-brand-600 peer-checked:ring-offset-2 rounded-xl border border-gray-200 hover:border-gray-300 p-4 transition">
                                    <p class="text-xl text-gray-900 mb-1" style="font-family: '{{ $fontName }}', sans-serif">{{ $fontName }}</p>
                                    <p class="text-xs text-gray-500" style="font-family: '{{ $fontName }}', sans-serif">The quick brown fox jumps over the lazy dog</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Splash --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Splash Screen</h3>
                    <p class="text-sm text-gray-500 mb-5">The tagline shown for a few seconds when the app opens.</p>
                    <x-admin.input name="splash_tagline" label="Splash Tagline" :value="$settings['splash_tagline']" placeholder="Growing trust, one harvest at a time" helptext="Short line under the app name on the splash screen." />
                </div>

                {{-- App Icon --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6" x-data="{ preview: '{{ \App\Support\Media::url($settings['app_logo_url']) }}', hasLogo: '{{ $settings['app_logo_url'] }}' !== '' }">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">App Icon</h3>
                    <p class="text-sm text-gray-500 mb-5">Shown on the splash screen and inside the app. Leave blank to use the website logo.</p>
                    <div class="flex items-start gap-6">
                        <div class="shrink-0">
                            <div class="w-32 h-32 rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden">
                                <template x-if="hasLogo">
                                    <img :src="preview" alt="App icon" class="w-full h-full object-contain p-2">
                                </template>
                                <template x-if="!hasLogo">
                                    <div class="text-center">
                                        <svg class="w-8 h-8 text-gray-300 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="text-xs text-gray-400">No icon</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="flex-1 space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Upload App Icon</label>
                                <input type="file" name="app_logo_file" accept="image/png,image/jpeg,image/svg+xml"
                                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                                       onchange="if(this.files[0]){const r=new FileReader();r.onload=e=>{preview=e.target.result;hasLogo=true};r.readAsDataURL(this.files[0])}">
                                <p class="mt-1.5 text-xs text-gray-400">PNG, JPG, or SVG. Square recommended. Max 2MB.</p>
                            </div>
                            @if(!empty($settings['app_logo_url']))
                                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                    <input type="checkbox" name="remove_app_logo" value="1" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                    Remove current app icon
                                </label>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Version & Update Tab --}}
            <div x-show="activeTab === 'version'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="space-y-6">

                <div class="bg-brand-50 border border-brand-100 rounded-2xl p-5 flex gap-3">
                    <svg class="w-5 h-5 text-brand-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm text-brand-800">The app reads these values from the API on every launch. When you ship a new build, update the <strong>Version</strong> and optionally turn on <strong>Force update</strong> so customers are asked to download the latest release.</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Release</h3>
                    <p class="text-sm text-gray-500 mb-5">Which version is the current release of the customer app.</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <x-admin.input name="version" label="App Version" :value="$settings['version']" placeholder="e.g. 1.0.0" helptext="Shown in the app (e.g. v1.0.0)." />
                        <x-admin.input name="build_number" label="Build Number" :value="$settings['build_number']" placeholder="e.g. 12" helptext="Internal build identifier." />
                        <x-admin.input name="minimum_supported" label="Minimum Supported" :value="$settings['minimum_supported']" placeholder="e.g. 1.0.0" helptext="Oldest app version allowed to run." />
                    </div>
                    <div class="mt-5">
                        <x-admin.checkbox name="force_update" label="Force update" :checked="$settings['force_update']" help="Blocks customers on older versions until they update." />
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Store Links</h3>
                    <p class="text-sm text-gray-500 mb-5">Where customers download the update.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.input name="android_update_url" label="Google Play URL" :value="$settings['android_update_url']" placeholder="https://play.google.com/store/apps/details?id=..." helptext="Blank to hide the Play Store button." />
                        <x-admin.input name="ios_update_url" label="App Store URL" :value="$settings['ios_update_url']" placeholder="https://apps.apple.com/app/id..." helptext="Blank to hide the App Store button." />
                    </div>
                    <div class="mt-5">
                        <x-admin.textarea name="release_notes" label="What's New" :value="$settings['release_notes']" rows="4" placeholder="What changed in this release, shown to customers when an update is available." />
                    </div>
                </div>
            </div>

            {{-- General Tab --}}
            <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="space-y-6">

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">App Identity</h3>
                    <p class="text-sm text-gray-500 mb-5">The name shown to customers inside the app.</p>
                    <x-admin.input name="app_name" label="Display Name" :value="$settings['app_name']" placeholder="{{ config('shop.site_name', 'Plant Tech Agro') }}" helptext="Override for the app only. Leave blank to use the store name ({{ config('shop.site_name', 'Plant Tech Agro') }})." />
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">App Availability</h3>
                    <p class="text-sm text-gray-500 mb-3">App-wide controls.</p>
                    <div class="space-y-3">
                        <x-admin.checkbox name="maintenance_mode" label="Maintenance mode" :checked="$settings['maintenance_mode']" help="Shows a maintenance screen instead of the app while enabled." />
                        <x-admin.checkbox name="echo_otp" label="Echo OTP in API responses" :checked="$settings['echo_otp']" help="Development helper — returns the generated OTP in the forgot-password API response. Disable in production." />
                    </div>
                </div>
            </div>

            {{-- Push Notifications (FCM) Tab --}}
            <div x-show="activeTab === 'push'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="space-y-6">

                {{-- Status & KPI Cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Devices</span>
                            <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 mt-2">{{ $totalDevices }}</p>
                        <p class="text-xs text-gray-500 mt-1">Installed farmer apps</p>
                    </div>

                    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Android Devices</span>
                            <span class="p-2 rounded-xl bg-teal-50 text-teal-600">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.523 15.3414c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.551 0 .9996.4482.9996.9993.0001.5511-.4486.9997-.9996.9997m-11.046 0c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.5511 0 .9993.4482.9993.9993 0 .5511-.4482.9997-.9993.9997m11.4045-6.02l1.996-3.4572c.1147-.1989.0466-.4535-.1523-.5682-.1988-.1147-.4534-.0465-.5682.1523l-2.0223 3.503C15.5896 8.423 13.8566 8.125 12 8.125c-1.8567 0-3.5897.298-5.1352.8263L4.8425 5.4483c-.1148-.1988-.3694-.267-.5682-.1523-.1989.1147-.267.3693-.1523.5682l1.996 3.4572C2.6889 11.1867.3432 14.6589 0 18.75h24c-.3432-4.0911-2.6889-7.5633-6.1185-9.4286"/></svg>
                            </span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 mt-2">{{ $androidDevices }}</p>
                        <p class="text-xs text-gray-500 mt-1">Google Play / APK users</p>
                    </div>

                    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">iOS Devices</span>
                            <span class="p-2 rounded-xl bg-sky-50 text-sky-600">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 4.37c.62-.75 1.04-1.8 0.93-2.85-.9.04-1.99.6-2.63 1.35-.57.65-1.06 1.71-.93 2.73.99.08 2.01-.5 2.63-1.23z"/></svg>
                            </span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 mt-2">{{ $iosDevices }}</p>
                        <p class="text-xs text-gray-500 mt-1">Apple App Store users</p>
                    </div>

                    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">FCM Status</span>
                            @if($hasServiceAccount)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Ready</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Setup Needed</span>
                            @endif
                        </div>
                        <p class="text-lg font-bold text-gray-900 mt-2 truncate">
                            {{ $hasServiceAccount ? 'HTTP v1 Active' : 'Unconfigured' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1 truncate">
                            {{ $firebaseProjectId ? $firebaseProjectId : 'Add credentials below' }}
                        </p>
                    </div>
                </div>

                {{-- Firebase Credentials & Core Settings Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                    <div>
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Firebase Cloud Messaging (HTTP v1)</h3>
                                <p class="text-sm text-gray-500 mt-0.5">Push alerts to farmers when tickets are answered, work orders progress, or emergency advisories are broadcast.</p>
                            </div>
                            @if($hasServiceAccount)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-100">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    Service Account Loaded
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-4 pt-2">
                        <x-admin.checkbox name="firebase_enabled" label="Enable Firebase Cloud Messaging push notifications"
                            :checked="old('firebase_enabled', $settings['firebase_enabled'] ?? true)"
                            help="When enabled, notifications to customers with active app sessions will be delivered directly to their phone status bar." />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Firebase Project ID</label>
                                <input type="text" name="firebase_project_id" value="{{ old('firebase_project_id', $firebaseProjectId) }}"
                                    placeholder="e.g. plant-tech-agro-mobile"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                <p class="text-xs text-gray-500 mt-1">Found in your Firebase Console Project Settings.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Service Account Key File (.json)</label>
                                <input type="file" name="firebase_service_account_file" accept=".json,application/json"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-1.5 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                                <p class="text-xs text-gray-500 mt-1">
                                    @if($hasServiceAccount)
                                        <span class="text-emerald-700 font-medium">Currently using: {{ $serviceAccountClientEmail }}</span> (Upload a new file to replace).
                                    @else
                                        Generate in Firebase Console &rarr; Project Settings &rarr; Service Accounts &rarr; "Generate new private key".
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="pt-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Or Paste Service Account JSON Directly</label>
                            <textarea name="firebase_service_account_json" rows="3" placeholder='{"type": "service_account", "project_id": "...", "private_key": "..."}'
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-xs font-mono focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"></textarea>
                            <p class="text-xs text-gray-400 mt-1">Useful if running in cloud environments where file uploading is restricted. Leave empty to keep existing key.</p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Save Button --}}
            <div class="flex justify-end">
                <x-admin.button type="submit">Save Mobile App Settings</x-admin.button>
            </div>
        </form>

        {{-- Standalone Push Tools: Test & Broadcast (Shown on Push Tab) --}}
        <div x-show="activeTab === 'push'" x-cloak class="space-y-6">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Test Push Notification Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Send Test Push Alert</h3>
                            <p class="text-xs text-gray-500">Verify your Firebase credentials and verify real device reception.</p>
                        </div>
                    </div>

                    <form action="{{ route('admin.mobile-apps.firebase.test') }}" method="POST" x-data="{ testTarget: 'customer' }" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Target Type</label>
                            <div class="flex gap-4">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                    <input type="radio" name="test_target_type" value="customer" x-model="testTarget" class="text-emerald-600 focus:ring-emerald-500">
                                    <span>Registered Customer</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                    <input type="radio" name="test_target_type" value="token" x-model="testTarget" class="text-emerald-600 focus:ring-emerald-500">
                                    <span>Raw Device Token</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                    <input type="radio" name="test_target_type" value="connection_only" x-model="testTarget" class="text-emerald-600 focus:ring-emerald-500">
                                    <span>OAuth2 Ping Only</span>
                                </label>
                            </div>
                        </div>

                        <div x-show="testTarget === 'customer'">
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Select Customer</label>
                            <select name="test_customer_id" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm focus:outline-none focus:border-brand-500">
                                <option value="">-- Choose a customer with an active device --</option>
                                @foreach($customersWithTokens as $cust)
                                    <option value="{{ $cust->id }}">{{ $cust->name }} ({{ $cust->phone }} &bull; {{ $cust->orchardist_id }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="testTarget === 'token'">
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Device Token (FCM Registration Token)</label>
                            <input type="text" name="test_device_token" placeholder="e.g. fH9_s8k2..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-mono focus:outline-none focus:border-brand-500">
                        </div>

                        <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-amber-600 text-white text-sm font-semibold hover:bg-amber-700 transition shadow-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            Send Test Notification
                        </button>
                    </form>
                </div>

                {{-- Broadcast Notification Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Broadcast Push Notification</h3>
                            <p class="text-xs text-gray-500">Send an instant alert or news announcement to farmers' mobile screens.</p>
                        </div>
                    </div>

                    <form action="{{ route('admin.mobile-apps.firebase.broadcast') }}" method="POST" x-data="{ audience: 'all_customers' }" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Audience</label>
                            <div class="flex gap-4">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                    <input type="radio" name="audience" value="all_customers" x-model="audience" class="text-emerald-600 focus:ring-emerald-500">
                                    <span>All Farmers ({{ $totalDevices }} Devices)</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                    <input type="radio" name="audience" value="specific_customer" x-model="audience" class="text-emerald-600 focus:ring-emerald-500">
                                    <span>Specific Farmer</span>
                                </label>
                            </div>
                        </div>

                        <div x-show="audience === 'specific_customer'">
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Select Farmer</label>
                            <select name="customer_id" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm focus:outline-none focus:border-brand-500">
                                <option value="">-- Choose recipient --</option>
                                @foreach($customersWithTokens as $cust)
                                    <option value="{{ $cust->id }}">{{ $cust->name }} ({{ $cust->phone }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Notification Title *</label>
                            <input type="text" name="title" placeholder="e.g. Frost Advisory: Spray Warning" required
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm font-semibold focus:outline-none focus:border-brand-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Message Body *</label>
                            <textarea name="body" rows="2" placeholder="e.g. Sub-zero temperatures expected tonight. Apply prophylactic copper spray on high-density orchards." required
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 p-2.5 text-sm focus:outline-none focus:border-brand-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Optional Banner Image URL</label>
                            <input type="url" name="image_url" placeholder="https://..."
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-1.5 text-xs focus:outline-none focus:border-brand-500">
                        </div>

                        <button type="submit" onclick="return confirm('Send this push notification to selected audience?')"
                            class="w-full px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition shadow-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                            Broadcast Push Notification
                        </button>
                    </form>
                </div>

            </div>

            {{-- Registered Devices Table --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h4 class="text-base font-bold text-gray-900">Recently Active Mobile Devices</h4>
                        <p class="text-xs text-gray-500">Farmers connected via the PTA Customer App with active FCM push tokens.</p>
                    </div>
                    <span class="text-xs font-medium text-gray-500">Showing last {{ $recentTokens->count() }} of {{ $totalDevices }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-xs uppercase font-semibold text-gray-500 tracking-wider">
                            <tr>
                                <th class="px-6 py-3">Farmer / User</th>
                                <th class="px-6 py-3">Platform</th>
                                <th class="px-6 py-3">Device Model</th>
                                <th class="px-6 py-3">Device Token Preview</th>
                                <th class="px-6 py-3">Last Active</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($recentTokens as $device)
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="px-6 py-3.5">
                                        @if($device->tokenable)
                                            <p class="font-bold text-gray-900">{{ $device->tokenable->name ?? 'User' }}</p>
                                            <p class="text-xs text-gray-400">{{ $device->tokenable->phone ?? '' }} &bull; {{ $device->tokenable->orchardist_id ?? '' }}</p>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Guest Session</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5">
                                        @if($device->platform === 'android')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                Android
                                            </span>
                                        @elseif($device->platform === 'ios')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-100">
                                                iOS
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-50 text-gray-700 border border-gray-200">
                                                {{ ucfirst($device->platform) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5 font-medium text-gray-800">
                                        {{ $device->device_name ?: 'Mobile Device' }}
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <code class="text-xs bg-gray-100 px-2 py-0.5 rounded text-gray-600 font-mono">{{ Str::limit($device->token, 24) }}</code>
                                    </td>
                                    <td class="px-6 py-3.5 text-xs text-gray-500 whitespace-nowrap">
                                        {{ $device->last_active_at ? $device->last_active_at->diffForHumans() : $device->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-400 text-sm">
                                        No mobile devices have registered push notification tokens yet.<br>
                                        <span class="text-xs text-gray-400 mt-1 block">When a farmer logs into the PTA Customer App, their device will automatically register here.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection