@if(isset($partners) && $partners->isNotEmpty())
@php
    // Repeat collection to ensure continuous smooth infinite marquee loop
    $loopPartners = $partners;
    while($loopPartners->count() < 12) {
        $loopPartners = $loopPartners->concat($partners);
    }
@endphp
<section id="partners" class="py-8 sm:py-12 bg-gray-50/70 dark:bg-gray-900/50 border-t border-b border-gray-100 dark:border-gray-800 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section Header --}}
        <div class="flex items-center justify-between gap-6 mb-6">
            <div>
                <p class="text-xs sm:text-sm font-semibold tracking-widest uppercase text-brand-600 dark:text-brand-400">Collaboration & Trust</p>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Our Trusted <span class="text-brand-600 dark:text-brand-400">Partners</span></h2>
            </div>
            <div class="text-right">
                <span class="text-2xl sm:text-3xl font-extrabold text-brand-600 dark:text-brand-400 tabular-nums">{{ $partners->count() }}+</span>
                <span class="block text-[10px] sm:text-xs font-bold text-gray-400 uppercase tracking-wider">Industry Partners</span>
            </div>
        </div>

        {{-- Auto-Scrolling Marquee Container (Contained within max-w-7xl) --}}
        <div class="relative w-full overflow-hidden rounded-2xl bg-white/60 dark:bg-gray-900/60 p-3 border border-gray-100/80 dark:border-gray-800/80 group">
            {{-- Gradient Edge Fades --}}
            <div class="absolute left-0 top-0 bottom-0 w-12 bg-gradient-to-r from-white dark:from-gray-900 z-10 pointer-events-none rounded-l-2xl"></div>
            <div class="absolute right-0 top-0 bottom-0 w-12 bg-gradient-to-l from-white dark:from-gray-900 z-10 pointer-events-none rounded-r-2xl"></div>

            <div class="flex gap-6 sm:gap-8 w-max animate-marquee group-hover:[animation-play-state:paused] py-1">
                @foreach($loopPartners as $partner)
                    @if($partner->website_url)
                        <a href="{{ $partner->website_url }}" target="_blank" rel="noopener noreferrer"
                           class="flex-shrink-0 flex items-center justify-center h-14 sm:h-16 px-6 sm:px-8 bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 shadow-2xs hover:shadow-md hover:border-brand-300 dark:hover:border-brand-700 transition-all duration-300 group/item">
                            @if($partner->logo && \App\Support\Media::exists($partner->logo))
                                <img src="{{ \App\Support\Media::url($partner->logo) }}" alt="{{ $partner->name }}" loading="lazy" decoding="async"
                                     class="h-7 sm:h-9 w-auto max-w-[130px] object-contain grayscale group-hover/item:grayscale-0 opacity-75 dark:opacity-60 dark:group-hover/item:opacity-100 transition-all duration-300">
                            @else
                                <span class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300 group-hover/item:text-brand-600 transition">{{ $partner->name }}</span>
                            @endif
                        </a>
                    @else
                        <div class="flex-shrink-0 flex items-center justify-center h-14 sm:h-16 px-6 sm:px-8 bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 shadow-2xs">
                            @if($partner->logo && \App\Support\Media::exists($partner->logo))
                                <img src="{{ \App\Support\Media::url($partner->logo) }}" alt="{{ $partner->name }}" loading="lazy" decoding="async"
                                     class="h-7 sm:h-9 w-auto max-w-[130px] object-contain grayscale hover:grayscale-0 opacity-75 dark:opacity-60 hover:opacity-100 transition-all duration-300">
                            @else
                                <span class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300">{{ $partner->name }}</span>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
