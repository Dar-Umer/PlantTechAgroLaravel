@php
    $siteName = config('shop.site_name', 'Plant Tech Agro');
    $brandParts = explode(' ', trim($siteName), 2);
    $logo = config('shop.logo_url');
@endphp
<header id="site-header"
        x-data="{ mobileOpen: false, scrolled: false,
            updateTint() { this.scrolled = window.scrollY > 24; } }"
        x-init="updateTint()"
        @scroll.passive.window="updateTint()"
        :class="scrolled ? 'bg-white dark:bg-gray-900 shadow-md shadow-gray-900/[0.04] border-gray-100 dark:border-gray-800' : 'bg-transparent border-transparent'"
        class="fixed top-0 inset-x-0 z-40 border-b transition-all duration-300 ease-out">
    @include('landing.partials.notice-bar')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative flex items-center justify-between h-16">
            <a href="{{ url('/') }}" class="flex items-center gap-2 min-w-0">
                @if($logo)
                    <img src="{{ \App\Support\Media::url($logo) }}" alt="{{ $siteName }}" class="h-11 w-auto max-w-[150px] object-contain sm:h-14 sm:max-w-none">
                @else
                    <div class="w-11 h-11 sm:w-14 sm:h-14 rounded-xl bg-gradient-to-br from-brand-600 to-brand-500 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9-2-9-2-9 2 9 2zm0 0V5m0 0L3 7m9-2l9 2M3 7v6l9 2 9-2V7"/></svg>
                    </div>
                    <span class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-gray-900 dark:text-white truncate">{{ $brandParts[0] }}<span class="font-light text-gray-600 dark:text-gray-400">{{ $brandParts[1] ?? '' }}</span></span>
                @endif
            </a>

            <nav class="hidden lg:flex items-center gap-8 text-sm font-medium text-gray-600 dark:text-gray-300">
                <a href="{{ route('varieties.index') }}" class="hover:text-brand-700 dark:hover:text-brand-400 transition">Varieties</a>
                <a href="{{ url('/') }}#services" class="hover:text-brand-700 dark:hover:text-brand-400 transition">Services</a>
                <a href="{{ url('/') }}#about" class="hover:text-brand-700 dark:hover:text-brand-400 transition">About</a>
                <a href="{{ url('/') }}#gallery" class="hover:text-brand-700 dark:hover:text-brand-400 transition">Gallery</a>
                <a href="{{ url('/') }}#projects" class="hover:text-brand-700 dark:hover:text-brand-400 transition">Projects</a>
                <a href="{{ url('/') }}#blog" class="hover:text-brand-700 dark:hover:text-brand-400 transition">Knowledge</a>
                <a href="{{ url('/') }}#contact" class="hover:text-brand-700 dark:hover:text-brand-400 transition">Contact</a>
            </nav>

            <div class="flex items-center gap-1.5 sm:gap-3 ml-auto lg:ml-0">
                <button type="button" onclick="window.ptaTheme.toggle()" aria-label="Toggle dark mode"
                        class="w-10 h-10 inline-flex items-center justify-center rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:text-brand-600 dark:hover:text-brand-400 hover:border-brand-200 dark:hover:border-brand-700 transition">
                    <svg class="w-5 h-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
                <button type="button" onclick="openBookModal()"
                        class="hidden sm:inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition shadow-sm shadow-brand-600/20">
                    Book Now
                </button>
                <button type="button" @click="mobileOpen = !mobileOpen" class="lg:hidden text-gray-600 dark:text-gray-300 p-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Nav --}}
    <div x-show="mobileOpen" x-cloak class="lg:hidden border-t border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 px-4 py-3 space-y-1">
        <a href="{{ route('varieties.index') }}" @click="mobileOpen = false" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">Varieties</a>
        <a href="{{ url('/') }}#services" @click="mobileOpen = false" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">Services</a>
        <a href="{{ url('/') }}#about" @click="mobileOpen = false" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">About</a>
        <a href="{{ url('/') }}#gallery" @click="mobileOpen = false" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">Gallery</a>
        <a href="{{ url('/') }}#projects" @click="mobileOpen = false" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">Projects</a>
        <a href="{{ url('/') }}#blog" @click="mobileOpen = false" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">Knowledge</a>
        <a href="{{ url('/') }}#contact" @click="mobileOpen = false" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">Contact</a>
        <button type="button" onclick="mobileOpen = false; openBookModal()"
                class="mt-2 w-full inline-flex items-center justify-center px-5 py-3 rounded-xl bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition">Book Now</button>
    </div>
</header>

<script>
    (function () {
        function syncHeaderHeight() {
            var header = document.getElementById('site-header');
            if (header) {
                var h = header.offsetHeight;
                if (h > 0) {
                    document.documentElement.style.setProperty('--site-header-height', h + 'px');
                }
            }
        }
        syncHeaderHeight();
        window.addEventListener('resize', syncHeaderHeight, { passive: true });
        window.addEventListener('load', syncHeaderHeight);
    })();
</script>
