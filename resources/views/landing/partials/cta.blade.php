@php
    $cta = $sections->get('cta');
    $ctaVisible = (! $cta || $cta->is_active);
@endphp
@if($ctaVisible)
<section id="cta" class="py-24 bg-gradient-to-br from-brand-700 via-brand-800 to-brand-900 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_70%_30%,rgba(255,255,255,0.6),transparent_40%)]"></div>
    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight leading-tight">
            {{ $cta->title ?? 'Ready to Transform Your Orchard?' }}
        </h2>
        <p class="mt-5 text-lg text-brand-100 max-w-2xl mx-auto">
            {{ $cta->subtitle ?? 'From high-density orchard planning to drip irrigation — let our experts guide every step.' }}
        </p>
        <div class="mt-10 flex flex-wrap justify-center gap-4">
            <button type="button" onclick="openBookModal()"
                    class="inline-flex items-center px-8 py-4 rounded-xl bg-white text-brand-800 font-bold hover:bg-brand-50 transition shadow-xl">
                Book Now
            </button>
            <a href="tel:{{ preg_replace('/\s+/', '', config('shop.site_phone')) }}"
               class="inline-flex items-center gap-2 px-8 py-4 rounded-xl border-2 border-white/40 text-white font-semibold hover:bg-white/10 transition">
                {{ config('shop.site_phone') }}
            </a>
        </div>
    </div>
</section>
@endif
