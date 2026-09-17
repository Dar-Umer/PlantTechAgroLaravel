@php
    $about = $sections->get('about_preview');
    $aboutVisible = (! $about || $about->is_active);
    $aboutContent = $about?->content ?? [];
    $aboutSubtitle = $about?->subtitle ?? null;
    $aboutTitle = $about->title ?? 'Leading Innovation in Kashmir\'s Agriculture';
    $aboutDescription = $aboutContent['description'] ?? $aboutSubtitle ?? '';

    $points = array_values(array_filter($aboutContent['points'] ?? []));
    if (empty($points)) {
        $points = [
            "We assess your land's soil health and seasonal conditions.",
            'We provide expert-guided orchard planning and setup.',
            'We deliver premium seeds and produce in a timely manner.',
            'We support your goals, budget, and long-term harvest targets.',
        ];
    }

    $image1 = $aboutContent['image_1'] ?? ($gallery[1]->image ?? null);
    $image2 = $aboutContent['image_2'] ?? ($gallery[2]->image ?? null);
    $stat = $stats->first();
@endphp
@if($aboutVisible)
<section id="about" class="py-24 bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-14 lg:gap-20 items-center">
            {{-- Content --}}
            <div class="relative">
                <p class="inline-flex items-center gap-2 text-sm font-semibold tracking-widest uppercase text-brand-600 mb-4">
                    <span class="w-8 h-px bg-brand-500 inline-block"></span>
                    About {{ config('shop.site_name', 'Plant Tech Agro') }}
                </p>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight leading-tight">
                    {{ $aboutTitle }}
                </h2>
                @if($aboutDescription)
                    <p class="mt-6 text-gray-600 leading-relaxed text-lg">{{ $aboutDescription }}</p>
                @endif

                @if($points)
                    <div class="mt-8 space-y-3.5">
                        @foreach($points as $point)
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 w-6 h-6 rounded-full bg-brand-50 border border-brand-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3.5 h-3.5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <p class="text-gray-600 text-[15px] leading-relaxed">{{ $point }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="mt-10 flex flex-wrap gap-4">
                    <button type="button" onclick="openBookModal()"
                            class="inline-flex items-center gap-2 px-7 py-3.5 rounded-xl bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition shadow-sm shadow-brand-600/20">
                        Get Started
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </button>
                    <a href="#contact"
                       class="inline-flex items-center px-7 py-3.5 rounded-xl border border-gray-200 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition">
                        Get in Touch
                    </a>
                </div>
            </div>

            {{-- Media --}}
            <div class="relative lg:pl-6">
                <div class="grid grid-cols-12 gap-4 sm:gap-5">
                    {{-- Main image --}}
                    <div class="col-span-8 rounded-3xl overflow-hidden bg-gradient-to-br from-brand-800 to-brand-700 aspect-[3/4] shadow-xl shadow-brand-900/10 relative">
                        @if($image1 && \App\Support\Media::exists($image1))
                            <img src="{{ \App\Support\Media::url($image1) }}" alt="{{ $aboutTitle }}" class="w-full h-full object-cover">
                        @else
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg class="w-20 h-20 text-brand-300/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 21a9 9 0 100-18 9 9 0 000 18zm6.5-3.5L15 14m-3-9v6m0 0l-2.5-2.5M12 11l2.5 2.5"/></svg>
                            </div>
                        @endif
                    </div>
                    {{-- Secondary image --}}
                    <div class="col-span-4 self-end rounded-3xl overflow-hidden bg-gradient-to-br from-brand-700 to-brand-500 aspect-[3/4] shadow-xl relative">
                        @if($image2 && \App\Support\Media::exists($image2))
                            <img src="{{ \App\Support\Media::url($image2) }}" alt="{{ config('shop.site_name', 'Plant Tech Agro') }}" class="w-full h-full object-cover">
                        @else
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg class="w-10 h-10 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Floating stat badge --}}
                @if($stat)
                    <div class="absolute -bottom-7 left-1/2 -translate-x-1/2 bg-gray-900 rounded-2xl px-7 py-4 shadow-xl border border-gray-800 text-center whitespace-nowrap">
                        <p class="text-3xl font-extrabold text-brand-400">{{ $stat->value }}{{ $stat->suffix ?? '+' }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $stat->label }}</p>
                    </div>
                @endif

                {{-- Decorative accent --}}
                <div class="absolute -top-6 -right-4 w-24 h-24 rounded-3xl bg-brand-100/70 -z-10 hidden sm:block"></div>
            </div>
        </div>
    </div>
</section>
@endif