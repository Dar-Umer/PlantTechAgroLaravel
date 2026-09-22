@if(isset($partners) && $partners->isNotEmpty())
@php
    // Repeat collection to ensure continuous smooth infinite marquee loop
    $loopPartners = $partners;
    while($loopPartners->count() < 12) {
        $loopPartners = $loopPartners->concat($partners);
    }
    $partnerSpeed = max(5, min(120, (int) config('frontend.partner_marquee_speed', 30)));
@endphp
<section id="partners" class="py-10 sm:py-14 bg-gray-50/70 dark:bg-gray-900/50 border-t border-b border-gray-100 dark:border-gray-800 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section Header --}}
        <div class="flex items-center justify-between gap-6 mb-8">
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
        <div class="relative w-full overflow-hidden rounded-2xl bg-white/70 dark:bg-gray-900/70 p-4 sm:p-5 border border-gray-100/90 dark:border-gray-800/90 shadow-2xs group">
            {{-- Gradient Edge Fades --}}
            <div class="absolute left-0 top-0 bottom-0 w-16 sm:w-24 bg-gradient-to-r from-white dark:from-gray-900 z-10 pointer-events-none rounded-l-2xl"></div>
            <div class="absolute right-0 top-0 bottom-0 w-16 sm:w-24 bg-gradient-to-l from-white dark:from-gray-900 z-10 pointer-events-none rounded-r-2xl"></div>

            <div class="flex gap-6 sm:gap-8 w-max animate-marquee group-hover:[animation-play-state:paused] py-2 items-center"
                 style="animation-duration: {{ $partnerSpeed }}s;">
                @foreach($loopPartners as $partner)
                    @if($partner->website_url)
                        <a href="{{ $partner->website_url }}" target="_blank" rel="noopener noreferrer"
                           class="flex-shrink-0 flex items-center justify-center h-20 sm:h-24 px-8 sm:px-10 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/80 dark:border-gray-800 shadow-2xs hover:shadow-lg hover:border-brand-400 dark:hover:border-brand-600 transition-all duration-300 group/item">
                            @if($partner->logo && \App\Support\Media::exists($partner->logo))
                                <img src="{{ \App\Support\Media::url($partner->logo) }}" alt="{{ $partner->name }}" loading="lazy" decoding="async"
                                     class="h-11 sm:h-14 w-auto max-w-[170px] sm:max-w-[210px] object-contain grayscale group-hover/item:grayscale-0 opacity-80 dark:opacity-70 dark:group-hover/item:opacity-100 transition-all duration-300">
                            @else
                                <span class="text-sm sm:text-base font-bold text-gray-800 dark:text-gray-200 group-hover/item:text-brand-600 transition">{{ $partner->name }}</span>
                            @endif
                        </a>
                    @else
                        <div class="flex-shrink-0 flex items-center justify-center h-20 sm:h-24 px-8 sm:px-10 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/80 dark:border-gray-800 shadow-2xs">
                            @if($partner->logo && \App\Support\Media::exists($partner->logo))
                                <img src="{{ \App\Support\Media::url($partner->logo) }}" alt="{{ $partner->name }}" loading="lazy" decoding="async"
                                     class="h-11 sm:h-14 w-auto max-w-[170px] sm:max-w-[210px] object-contain grayscale hover:grayscale-0 opacity-80 dark:opacity-70 hover:opacity-100 transition-all duration-300">
                            @else
                                <span class="text-sm sm:text-base font-bold text-gray-800 dark:text-gray-200">{{ $partner->name }}</span>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
