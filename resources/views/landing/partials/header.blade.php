@php
    $siteName = config('shop.site_name', 'Plant Tech Agro');
    $brandParts = explode(' ', trim($siteName), 2);
    $logo = config('shop.logo_url');
@endphp
<header x-data="{ mobileOpen: false, scrolled: false,
            updateTint() { this.scrolled = window.scrollY > 24; } }"
        x-init="updateTint()"
        @scroll.passive.window="updateTint()"
        :class="scrolled ? 'bg-white shadow-md shadow-gray-900/[0.04] border-gray-100' : 'bg-transparent border-transparent'"
        class="fixed top-0 inset-x-0 z-40 border-b transition-all duration-300 ease-out">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative flex items-center justify-between h-16">
            <a href="{{ url('/') }}" class="absolute left-1/2 -translate-x-1/2 lg:static lg:left-auto lg:translate-x-0 flex items-center gap-2">
                @if($logo)
                    <img src="{{ \App\Support\Media::url($logo) }}" alt="{{ $siteName }}" class="h-14 w-auto">
                @else
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-brand-600 to-brand-500 flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9-2-9-2-9 2 9 2zm0 0V5m0 0L3 7m9-2l9 2M3 7v6l9 2 9-2V7"/></svg>
                    </div>
                    <span class="text-3xl font-extrabold text-gray-900">{{ $brandParts[0] }}<span class="font-light text-gray-600">{{ $brandParts[1] ?? '' }}</span></span>
                @endif
            </a>

            <nav class="hidden lg:flex items-center gap-8 text-sm font-medium text-gray-600">
                <a href="#services" class="hover:text-brand-700 transition">Services</a>
                <a href="#about" class="hover:text-brand-700 transition">About</a>
                <a href="#gallery" class="hover:text-brand-700 transition">Gallery</a>
                <a href="#projects" class="hover:text-brand-700 transition">Projects</a>
                <a href="#blog" class="hover:text-brand-700 transition">Knowledge</a>
                <a href="#contact" class="hover:text-brand-700 transition">Contact</a>
            </nav>

            <div class="flex items-center gap-3 ml-auto lg:ml-0">
                <button type="button" onclick="openBookModal()"
                        class="hidden sm:inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition shadow-sm shadow-brand-600/20">
                    Book Now
                </button>
                <button type="button" @click="mobileOpen = !mobileOpen" class="lg:hidden text-gray-600 p-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Nav --}}
    <div x-show="mobileOpen" x-cloak class="lg:hidden border-t border-gray-100 bg-white px-4 py-3 space-y-1">
        <a href="#services" @click="mobileOpen = false" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Services</a>
        <a href="#about" @click="mobileOpen = false" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">About</a>
        <a href="#gallery" @click="mobileOpen = false" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Gallery</a>
        <a href="#projects" @click="mobileOpen = false" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Projects</a>
        <a href="#blog" @click="mobileOpen = false" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Knowledge</a>
        <a href="#contact" @click="mobileOpen = false" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Contact</a>
    </div>
</header>
