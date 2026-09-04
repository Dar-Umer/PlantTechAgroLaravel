@php
    $hero = $sections->get('hero');
    $heroActive = ! $hero || $hero->is_active;
@endphp
@if($heroActive)
<section class="relative min-h-[92vh] flex items-center overflow-hidden bg-gray-950">
    {{-- Background layers --}}
    <div class="absolute inset-0 bg-gradient-to-br from-brand-900 via-gray-950 to-brand-900"></div>
    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_30%_20%,rgba(16,185,129,0.5),transparent_45%),radial-gradient(circle_at_75%_70%,rgba(5,150,105,0.4),transparent_40%)]"></div>
    <div class="absolute -bottom-24 -right-24 w-96 h-96 rounded-full bg-brand-600/10 blur-3xl"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-20 w-full">
        <div class="max-w-3xl">
            <p class="inline-flex items-center gap-2 text-xs font-semibold tracking-widest uppercase text-brand-400 mb-6">
                <span class="w-8 h-px bg-brand-500"></span>
                Kashmir's Finest Agritech Company
            </p>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-[1.1] tracking-tight">
                {{ $hero->title ?? 'Innovation Built to Elevate Agriculture' }}
            </h1>
            <p class="mt-6 text-lg text-gray-300 max-w-2xl leading-relaxed">
                {{ $hero->subtitle ?? 'Driving agricultural excellence with advanced technologies, sustainable farming models, and data-driven solutions for higher productivity and farmer prosperity.' }}
            </p>
            <div class="mt-10 flex flex-wrap items-center gap-4">
                <button type="button" onclick="openBookModal()"
                        class="inline-flex items-center gap-2 px-7 py-3.5 rounded-xl bg-brand-500 text-white font-semibold hover:bg-brand-400 transition shadow-lg shadow-brand-500/25">
                    Book Now
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </button>
                <a href="#contact"
                   class="inline-flex items-center gap-2 px-7 py-3.5 rounded-xl border border-gray-600 text-gray-200 font-semibold hover:bg-white/5 hover:border-gray-400 transition">
                    Contact Us
                </a>
            </div>
        </div>
    </div>

    {{-- Scroll indicator --}}
    <a href="#partners" class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 text-gray-500 hover:text-gray-300 transition">
        <span class="text-[10px] font-semibold tracking-widest uppercase">Scroll</span>
        <svg class="w-4 h-4 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
    </a>
</section>
@endif
