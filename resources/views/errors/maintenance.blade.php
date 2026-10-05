<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
        (function () {
            var root = document.documentElement;
            var stored = null;
            try { stored = localStorage.getItem('pta-theme'); } catch (e) {}
            root.classList.toggle('dark', stored === 'dark');

            window.ptaTheme = {
                isDark: function () { return root.classList.contains('dark'); },
                apply: function (isDark) {
                    root.classList.toggle('dark', isDark);
                    try { localStorage.setItem('pta-theme', isDark ? 'dark' : 'light'); } catch (e) {}
                },
                toggle: function () { this.apply(! this.isDark()); }
            };
        })();
    </script>
    <title>{{ $title ?? "Scheduled Maintenance" }} — {{ config('shop.site_name', 'Plant Tech Agro') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=typography,forms,aspect-ratio"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
                    colors: {
                        brand: {
                            50:  '{{ $theme['palette'][50] ?? '#f0fdf4' }}',
                            100: '{{ $theme['palette'][100] ?? '#dcfce7' }}',
                            200: '{{ $theme['palette'][200] ?? '#bbf7d0' }}',
                            300: '{{ $theme['palette'][300] ?? '#86efac' }}',
                            400: '{{ $theme['palette'][400] ?? '#4ade80' }}',
                            500: '{{ $theme['palette'][500] ?? '#22c55e' }}',
                            600: '{{ $theme['palette'][600] ?? '#16a34a' }}',
                            700: '{{ $theme['palette'][700] ?? '#15803d' }}',
                            800: '{{ $theme['palette'][800] ?? '#166534' }}',
                            900: '{{ $theme['palette'][900] ?? '#14532d' }}',
                        }
                    }
                }
            }
        }
    </script>
    @if(!empty(config('shop.favicon_url')))
        <link rel="icon" href="{{ config('shop.favicon_url') }}">
    @endif
    <style>
        html { color-scheme: light; }
        html.dark { color-scheme: dark; }
        body { transition: background-color .3s ease, color .3s ease; }
    </style>
</head>
<body class="font-sans antialiased text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-950 min-h-screen flex flex-col justify-between selection:bg-brand-500 selection:text-white">

    <!-- Top Announcement Strip (matches notice bar) -->
    <div class="bg-amber-50 dark:bg-amber-950/40 text-amber-900 dark:text-amber-200 border-b border-amber-200/80 dark:border-amber-800/50 py-2.5 px-4 text-xs sm:text-sm font-medium">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            <div class="flex items-center gap-2 truncate">
                <span class="inline-flex h-2 w-2 rounded-full bg-amber-500 animate-pulse shrink-0"></span>
                <span class="truncate">Scheduled maintenance in progress &bull; Systems are currently being upgraded.</span>
            </div>
            @if(!empty(config('shop.site_phone')))
                <a href="tel:{{ config('shop.site_phone') }}" class="shrink-0 font-semibold text-brand-700 dark:text-brand-400 hover:underline flex items-center gap-1.5">
                    <span>Helpline: {{ config('shop.site_phone') }}</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Official Header (matches live site header exactly) -->
    <header class="border-b border-gray-100 dark:border-gray-800 bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @php
                    $logo = $theme['logo_url'] ?? config('shop.logo_url');
                    $siteName = config('shop.site_name', 'Plant Tech Agro');
                    $brandParts = explode(' ', trim($siteName), 2);
                @endphp
                @if(!empty($logo))
                    <img src="{{ \App\Support\Media::url($logo) }}" alt="{{ $siteName }}" class="h-10 sm:h-12 w-auto max-w-[160px] object-contain">
                @else
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-600 to-brand-500 flex items-center justify-center text-white font-bold shrink-0">
                        🌱
                    </div>
                    <span class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white truncate">
                        {{ $brandParts[0] }}<span class="font-light text-gray-600 dark:text-gray-400">{{ $brandParts[1] ?? '' }}</span>
                    </span>
                @endif
            </div>

            <div class="flex items-center gap-2 sm:gap-4">
                <!-- Dark Mode Toggle (matches live header) -->
                <button type="button" onclick="window.ptaTheme.toggle()" aria-label="Toggle dark mode"
                        class="w-10 h-10 inline-flex items-center justify-center rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:text-brand-600 dark:hover:text-brand-400 hover:border-brand-200 dark:hover:border-brand-700 transition cursor-pointer">
                    <svg class="w-5 h-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>

                @if(!empty(config('shop.site_phone')))
                    <a href="tel:{{ config('shop.site_phone') }}"
                       class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs sm:text-sm font-semibold transition shadow-sm shadow-brand-600/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>Call Support</span>
                    </a>
                @endif
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 bg-gradient-to-b from-brand-50/40 via-white to-gray-50/50 dark:from-gray-900/40 dark:via-gray-950 dark:to-gray-950 py-12 sm:py-16 lg:py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Hero Text -->
            <div class="text-center max-w-2xl mx-auto space-y-4">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 dark:bg-brand-950/70 dark:text-brand-300 border border-brand-200 dark:border-brand-800">
                    <span class="w-2 h-2 rounded-full bg-brand-600 dark:bg-brand-400 animate-pulse"></span>
                    <span>System Maintenance in Progress</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 dark:text-white tracking-tight leading-tight">
                    {{ $title ?? "We're Undergoing Scheduled Maintenance" }}
                </h1>

                <p class="text-base sm:text-lg text-gray-600 dark:text-gray-400 leading-relaxed pt-1">
                    {{ $message ?? 'We are currently performing scheduled upgrades and essential optimizations to improve your experience. Our services and catalog will be back online shortly.' }}
                </p>

                <!-- Actions -->
                <div class="flex flex-wrap items-center justify-center gap-3 pt-4">
                    <button type="button" onclick="window.location.reload()"
                            class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold transition shadow-sm shadow-brand-600/20 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Refresh Page</span>
                    </button>

                    @if(!empty(config('shop.site_phone')))
                        <a href="tel:{{ config('shop.site_phone') }}"
                           class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-200 text-sm font-semibold transition border border-gray-200 dark:border-gray-700">
                            <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>Call {{ config('shop.site_phone') }}</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Authentic Information Cards Grid (matches live site card style) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-12 sm:mt-16">
                
                {{-- Card 1: Direct Helpline --}}
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="w-11 h-11 rounded-xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Direct Phone Support</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">For immediate inquiries regarding orchard establishment, irrigation, or nursery saplings.</p>
                    </div>
                    @if(!empty(config('shop.site_phone')))
                        <a href="tel:{{ config('shop.site_phone') }}" class="text-sm font-bold text-brand-600 dark:text-brand-400 hover:text-brand-700 transition inline-flex items-center gap-1.5">
                            <span>{{ config('shop.site_phone') }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @endif
                </div>

                {{-- Card 2: WhatsApp Advisory --}}
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">WhatsApp Advisory</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Send photos of orchard diseases, leaves, or soil queries directly to our experts.</p>
                    </div>
                    @php
                        $wa = config('shop.social_whatsapp') ?: (config('shop.site_phone') ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', config('shop.site_phone')) : null);
                    @endphp
                    @if($wa)
                        <a href="{{ $wa }}" target="_blank" rel="noopener" class="text-sm font-bold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 transition inline-flex items-center gap-1.5">
                            <span>Chat on WhatsApp</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @else
                        <span class="text-xs text-gray-400">Available during working hours</span>
                    @endif
                </div>

                {{-- Card 3: Physical Office --}}
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Office &amp; Walk-ins</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Our corporate office and agro consultation center remain open as normal.</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400 font-medium line-clamp-2">{{ config('shop.site_address') }}</p>
                    </div>
                    <div class="pt-4 text-xs font-semibold text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-brand-600 dark:text-brand-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ config('shop.support_hours', 'Mon – Sat, 9 AM – 6 PM') }}</span>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <!-- Official Footer (matches live site dark footer style) -->
    <footer class="bg-gray-950 text-gray-400 border-t border-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-3">
                    @if(!empty($logo))
                        <img src="{{ \App\Support\Media::url($logo) }}" alt="{{ $siteName }}" class="h-8 w-auto object-contain">
                    @endif
                    <div>
                        <p class="text-sm font-bold text-white">{{ $siteName }}</p>
                        <p class="text-xs text-gray-500">{{ config('shop.footer_tagline') }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-6 text-xs text-gray-400">
                    @if(config('shop.site_email'))
                        <a href="mailto:{{ config('shop.site_email') }}" class="hover:text-brand-400 transition">{{ config('shop.site_email') }}</a>
                    @endif
                    @if(config('shop.site_phone'))
                        <a href="tel:{{ config('shop.site_phone') }}" class="hover:text-brand-400 transition">{{ config('shop.site_phone') }}</a>
                    @endif
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-900 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-500">
                <p>&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>
                <div>
                    <a href="{{ route('admin.login') }}" class="inline-flex items-center gap-1.5 text-gray-500 hover:text-brand-400 transition font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Staff &amp; Admin Sign In</span>
                    </a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
