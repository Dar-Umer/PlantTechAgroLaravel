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

            {{-- Save Button --}}
            <div class="flex justify-end">
                <x-admin.button type="submit">Save Mobile App Settings</x-admin.button>
            </div>
        </form>
    </div>
@endsection