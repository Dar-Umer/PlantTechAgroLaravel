@php
    $appDownloadUrl = config('mobile.android_update_url') ?: 'https://play.google.com/store/apps/details?id=com.plant.tech';
    $siteName = config('shop.site_name', 'Plant Tech Agro');
    $logo = config('shop.logo_url');
@endphp

{{-- Android-Only Mobile App Modal Bottom Sheet (Covers >= 50% Screen) --}}
<div x-data="mobileAppBanner()"
     x-init="init()"
     x-cloak
     class="select-none">

    {{-- Dark Backdrop Overlay --}}
    <div x-show="visible"
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="dismiss()"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9990]"
         aria-hidden="true"></div>

    {{-- Half-Screen Bottom Sheet (min 52% screen height) --}}
    <div x-show="visible"
         x-transition:enter="transition ease-out duration-350 transform"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-250 transform"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         class="fixed bottom-0 inset-x-0 z-[9999] rounded-t-[32px] sm:rounded-t-[36px] bg-white dark:bg-gray-900 border-t border-gray-200/80 dark:border-gray-800 shadow-2xl p-5 sm:p-7 min-h-[55vh] max-h-[85vh] flex flex-col justify-between overflow-y-auto"
         role="dialog"
         aria-label="Download Plant Tech Agro App on Google Play">

        {{-- Top Drag Handle & Close Button --}}
        <div class="relative">
            <div class="w-12 h-1.5 rounded-full bg-gray-300 dark:bg-gray-700 mx-auto -mt-1 mb-3"></div>
            <button type="button"
                    @click="dismiss()"
                    aria-label="Close"
                    class="absolute -top-1 right-0 w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 hover:text-gray-900 dark:hover:text-white flex items-center justify-center transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- App Header Card --}}
        <div class="pt-2 text-center flex flex-col items-center">
            {{-- Big App Icon with Google Play badge --}}
            <div class="relative mb-3.5">
                @if($logo)
                    <img src="{{ \App\Support\Media::url($logo) }}"
                         alt="{{ $siteName }}"
                         class="w-20 h-20 rounded-2xl object-contain bg-white dark:bg-gray-800 p-2 border border-gray-200 dark:border-gray-700 shadow-lg shadow-brand-950/10">
                @else
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-brand-600 via-brand-500 to-emerald-600 flex items-center justify-center text-white font-black text-2xl shadow-lg shadow-brand-950/20">
                        PTA
                    </div>
                @endif
                <span class="absolute -bottom-1.5 -right-1.5 w-6 h-6 rounded-full bg-white dark:bg-gray-900 p-1 shadow-md border border-gray-100 dark:border-gray-800">
                    <svg class="w-full h-full" viewBox="0 0 24 24" fill="none">
                        <path d="M3.609 1.814C3.228 2.183 3 2.709 3 3.279v17.442c0 .57.228 1.096.61 1.465l10.183-10.186L3.609 1.814z" fill="#00C1A6"/>
                        <path d="M17.27 8.531L5.26 1.705 13.793 12l3.477-3.469z" fill="#0083D6"/>
                        <path d="M3.609 22.186l13.661-7.755L13.793 12 3.61 22.186z" fill="#EB2A44"/>
                        <path d="M20.682 10.469l-3.412-1.938-3.477 3.469 3.477 3.469 3.412-1.938c.762-.433.762-1.137 0-1.562z" fill="#FFC900"/>
                    </svg>
                </span>
            </div>

            <h3 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white leading-tight">
                {{ $siteName }}
            </h3>

            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-sm">
                Complete High-Density Apple Orchard Management on Mobile
            </p>

            {{-- Ratings & Tag --}}
            <div class="mt-2.5 inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-[11px] font-semibold text-gray-700 dark:text-gray-300">
                <span class="text-amber-500 flex items-center gap-1 font-bold">
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    4.9 ★
                </span>
                <span>•</span>
                <span class="text-gray-600 dark:text-gray-300">Official Android App</span>
                <span>•</span>
                <span class="text-brand-600 dark:text-brand-400 font-bold">Free Download</span>
            </div>
        </div>

        {{-- Feature Highlights --}}
        <div class="my-4 space-y-2.5 max-w-md mx-auto w-full">
            <div class="flex items-center gap-3 p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                <div class="w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-900/40 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0 text-sm">
                    📊
                </div>
                <div class="text-left min-w-0">
                    <div class="text-xs font-bold text-gray-900 dark:text-white">Orchard Telemetry & Growth Stages</div>
                    <div class="text-[10px] text-gray-500 dark:text-gray-400 truncate">Real-time status of rootstocks, irrigation, and weather</div>
                </div>
            </div>

            <div class="flex items-center gap-3 p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 text-sm">
                    🎫
                </div>
                <div class="text-left min-w-0">
                    <div class="text-xs font-bold text-gray-900 dark:text-white">Instant Agronomist Support Tickets</div>
                    <div class="text-[10px] text-gray-500 dark:text-gray-400 truncate">Upload tree disease photos for rapid horticultural diagnosis</div>
                </div>
            </div>

            <div class="flex items-center gap-3 p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 text-sm">
                    🔔
                </div>
                <div class="text-left min-w-0">
                    <div class="text-xs font-bold text-gray-900 dark:text-white">Audible Closed-App Notifications</div>
                    <div class="text-[10px] text-gray-500 dark:text-gray-400 truncate">Loud audio reminders for spray calendars and water runs</div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="pt-2 pb-1 space-y-2 max-w-md mx-auto w-full">
            {{-- Primary Install Button --}}
            <a href="{{ $appDownloadUrl }}"
               target="_blank"
               rel="noopener noreferrer"
               @click="dismiss()"
               class="w-full py-3.5 px-6 rounded-2xl bg-brand-600 hover:bg-brand-500 active:scale-[0.98] text-white font-extrabold text-sm sm:text-base shadow-lg shadow-brand-600/30 flex items-center justify-center gap-3 transition">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none">
                    <path d="M3.609 1.814C3.228 2.183 3 2.709 3 3.279v17.442c0 .57.228 1.096.61 1.465l10.183-10.186L3.609 1.814z" fill="#00C1A6"/>
                    <path d="M17.27 8.531L5.26 1.705 13.793 12l3.477-3.469z" fill="#0083D6"/>
                    <path d="M3.609 22.186l13.661-7.755L13.793 12 3.61 22.186z" fill="#EB2A44"/>
                    <path d="M20.682 10.469l-3.412-1.938-3.477 3.469 3.477 3.469 3.412-1.938c.762-.433.762-1.137 0-1.562z" fill="#FFC900"/>
                </svg>
                <span>Install from Google Play</span>
            </a>

            {{-- Dismiss text button --}}
            <button type="button"
                    @click="dismiss()"
                    class="w-full py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition">
                Continue on mobile website
            </button>
        </div>
    </div>
</div>

<script>
    function mobileAppBanner() {
        return {
            visible: false,
            init() {
                // Clear any old legacy 24h key
                try {
                    localStorage.removeItem('pta_android_banner_dismissed_until');
                } catch (e) {}

                // Comprehensive mobile detection (Android, iPhone, tablet, or mobile screen width)
                const ua = navigator.userAgent || navigator.vendor || window.opera || '';
                const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile/i.test(ua)
                                 || (window.innerWidth && window.innerWidth <= 768)
                                 || window.location.search.includes('test_app_popup=1');

                if (! isMobile) {
                    return;
                }

                // Check 10-minute dismissal cooldown
                try {
                    const dismissedUntil = localStorage.getItem('pta_mobile_banner_dismissed_until');
                    if (dismissedUntil && Number(dismissedUntil) > Date.now()) {
                        return; // Still within the 10-minute cooldown
                    }
                } catch (e) {}

                // Reveal smoothly after a short delay
                setTimeout(() => {
                    this.visible = true;
                }, 600);
            },
            dismiss() {
                this.visible = false;
                try {
                    // Remember dismissal for exactly 10 minutes (10 * 60 * 1000 ms)
                    const tenMinutes = 10 * 60 * 1000;
                    localStorage.setItem('pta_mobile_banner_dismissed_until', Date.now() + tenMinutes);
                } catch (e) {}
            }
        };
    }
</script>
