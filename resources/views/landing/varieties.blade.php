@extends('landing.layout')

@section('title', 'Apple Varieties — ' . config('shop.site_name', 'Plant Tech Agro'))

@section('content')
    <section id="varieties" class="pt-24 sm:pt-28 pb-16 sm:pb-20 bg-gray-50 dark:bg-gray-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Hero --}}
            <div
                class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-800 via-brand-700 to-brand-600 text-white shadow-xl mb-10">
                <div class="absolute inset-0 opacity-10"
                     style="background-image: radial-gradient(circle at 20% 20%, rgba(255,255,255,.35) 0, transparent 40%), radial-gradient(circle at 80% 70%, rgba(255,255,255,.25) 0, transparent 35%);">
                </div>
                <div class="relative px-6 sm:px-10 py-12 sm:py-16">
                    <p class="text-sm font-semibold tracking-widest uppercase text-brand-200 mb-3">Orchard Selection</p>
                    <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight mb-4">Apple Varieties</h1>
                    <p class="text-base sm:text-lg text-brand-100 max-w-2xl leading-relaxed">
                        Pakistan's finest apple varieties for high-density orchards — each one
                        hand-picked for yield, taste, and resilience in cold climates.
                    </p>
                    <a href="#catalogue"
                       class="mt-8 inline-flex items-center gap-2 rounded-xl bg-white text-brand-700 px-6 py-3 text-sm font-bold shadow-lg hover:bg-brand-50 transition">
                        Browse the Catalogue
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    </a>
                </div>
            </div>

            {{-- Catalogue --}}
            <div id="catalogue">
                @if($varieties->isEmpty())
                    <div class="text-center py-16">
                        <p class="text-gray-500 text-sm">Varieties are being added. Check back soon.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($varieties as $variety)
                            <article
                                class="group bg-white dark:bg-gray-900 rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300">
                                <div class="relative h-52 overflow-hidden bg-gray-200 dark:bg-gray-800">
                                    @if($variety->image && \App\Support\Media::exists($variety->image))
                                        <img src="{{ \App\Support\Media::url($variety->image) }}" alt="{{ $variety->name }}"
                                             loading="lazy" decoding="async"
                                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-brand-600 to-brand-500">
                                            <svg class="w-12 h-12 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        </div>
                                    @endif
                                    @if($variety->category)
                                        <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-xs font-bold bg-white/90 text-brand-700 backdrop-blur">
                                            {{ $variety->category }}
                                        </span>
                                    @endif
                                </div>
                                <div class="p-5">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $variety->name }}</h3>
                                    @if($variety->short_description)
                                        <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ $variety->short_description }}</p>
                                    @endif
                                    <div class="mt-4 flex flex-wrap items-center gap-2 text-xs font-medium">
                                        @if($variety->season)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 dark:bg-gray-800 px-2.5 py-1 text-brand-700 dark:text-brand-300">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                {{ $variety->season }}
                                            </span>
                                        @endif
                                        @if($variety->taste)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-1 text-gray-600 dark:text-gray-300">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                                {{ $variety->taste }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- CTA --}}
            <div class="mt-12 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-6 sm:p-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 shadow-sm">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Not sure which variety fits your orchard?</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Speak to our experts about rootstock, spacing and climate fit.</p>
                </div>
                <a href="{{ url('/') }}#services"
                   class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 text-white px-6 py-3 text-sm font-bold shadow-lg shadow-brand-600/20 hover:bg-brand-700 transition">
                    Book an Expert Consultation
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            </div>
        </div>
    </section>
@endsection
