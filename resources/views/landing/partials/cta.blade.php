@php
    $cta = $sections->get('cta');
    $ctaVisible = (! $cta || $cta->is_active);
    $siteName = config('shop.site_name', 'Plant Tech Agro');
    $rawTitle = $cta->title ?? 'Ready to Transform Your Orchard?';
    $subtitle = $cta->subtitle ?? 'From high-density orchard planning to drip irrigation, let our experts guide every step.';

    // Split title into two lines: Line 1 (white) and Line 2 (emerald green)
    if (stripos($rawTitle, 'Your Orchard') !== false) {
        $parts = preg_split('/(?=Your Orchard)/i', $rawTitle);
        $line1 = trim($parts[0]);
        $line2 = trim($parts[1] ?? 'Your Orchard?');
    } else {
        $words = explode(' ', $rawTitle);
        if (count($words) > 2) {
            $mid = (int) ceil(count($words) / 2);
            $line1 = implode(' ', array_slice($words, 0, $mid));
            $line2 = implode(' ', array_slice($words, $mid));
        } else {
            $line1 = $rawTitle;
            $line2 = '';
        }
    }
    // Rain drops for inside the card
    $cardDrops = [
        ['x' => 6,  'h' => 28, 'dur' => 4.2, 'delay' => 0.0, 'op' => 0.35],
        ['x' => 14, 'h' => 36, 'dur' => 5.2, 'delay' => 1.8, 'op' => 0.40],
        ['x' => 24, 'h' => 30, 'dur' => 4.6, 'delay' => 3.2, 'op' => 0.30],
        ['x' => 34, 'h' => 44, 'dur' => 5.5, 'delay' => 0.8, 'op' => 0.45],
        ['x' => 44, 'h' => 26, 'dur' => 4.0, 'delay' => 2.4, 'op' => 0.28],
        ['x' => 54, 'h' => 38, 'dur' => 4.9, 'delay' => 4.1, 'op' => 0.42],
        ['x' => 64, 'h' => 32, 'dur' => 4.4, 'delay' => 1.2, 'op' => 0.32],
        ['x' => 74, 'h' => 42, 'dur' => 5.6, 'delay' => 2.9, 'op' => 0.44],
        ['x' => 84, 'h' => 28, 'dur' => 4.3, 'delay' => 0.5, 'op' => 0.30],
        ['x' => 93, 'h' => 36, 'dur' => 5.0, 'delay' => 3.6, 'op' => 0.38],
    ];
@endphp
@if($ctaVisible)
<section id="cta" class="py-12 sm:py-16 lg:py-20 bg-white dark:bg-gray-950 relative overflow-hidden border-t border-gray-100 dark:border-gray-900">
    <style>
        @keyframes cta-rain-fall {
            0% { transform: translate3d(0, -60px, 0); opacity: 0; }
            15% { opacity: 1; }
            85% { opacity: 1; }
            100% { transform: translate3d(0, 460px, 0); opacity: 0; }
        }
        .cta-rain-drop {
            background: linear-gradient(to bottom, transparent, rgba(16, 185, 129, 0.65));
            animation: cta-rain-fall 5s linear infinite;
            will-change: transform;
        }
        @media (prefers-reduced-motion: reduce) {
            .cta-rain-drop { animation: none; opacity: 0; }
        }
    </style>

    {{-- Hero Section Exact Background Layers --}}
    <div class="absolute inset-0 bg-gradient-to-b from-brand-50/70 via-white to-gray-50 dark:from-brand-900/40 dark:via-gray-950 dark:to-gray-950 pointer-events-none"></div>
    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(ellipse_at_top,rgba(16,185,129,0.35),transparent_55%)] pointer-events-none"></div>
    <div class="absolute inset-0 opacity-[0.36] bg-[linear-gradient(rgba(15,23,42,0.06)_1px,transparent_1px),linear-gradient(90deg,rgba(15,23,42,0.06)_1px,transparent_1px)] dark:bg-[linear-gradient(rgba(148,163,184,0.10)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.10)_1px,transparent_1px)] bg-[size:64px_64px] [mask-image:radial-gradient(ellipse_at_center,black_20%,transparent_72%)] pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Card Container with Hero-Matched Styling and Rain Drops Effect --}}
        <div class="relative rounded-3xl border border-gray-800 bg-gray-900/90 dark:bg-gray-900/95 backdrop-blur-md p-8 sm:p-12 lg:p-14 shadow-2xl shadow-brand-950/20 transition duration-300 overflow-hidden group">
            {{-- Hero-matching Ellipse Glow inside Card --}}
            <div class="absolute inset-0 opacity-25 bg-[radial-gradient(ellipse_at_top,rgba(16,185,129,0.35),transparent_60%)] pointer-events-none"></div>
            {{-- Hero-matching Grid Texture inside Card --}}
            <div class="absolute inset-0 opacity-[0.20] bg-[linear-gradient(rgba(148,163,184,0.10)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.10)_1px,transparent_1px)] bg-[size:64px_64px] [mask-image:radial-gradient(ellipse_at_center,black_20%,transparent_72%)] pointer-events-none"></div>

            {{-- Subtle Tilted Rain Drop Effect Inside Card (matching hero section) --}}
            <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
                @foreach($cardDrops as $drop)
                    <span class="absolute top-0" style="left: {{ $drop['x'] }}%; transform: rotate(12deg)">
                        <span class="cta-rain-drop block w-px rounded-full"
                              style="height: {{ $drop['h'] }}px; animation-duration: {{ $drop['dur'] }}s; animation-delay: {{ $drop['delay'] }}s; opacity: {{ $drop['op'] }};">
                        </span>
                    </span>
                @endforeach
            </div>

            {{-- Subtle Sparkle Dots at Bottom Left (matching reference design) --}}
            <div class="absolute bottom-6 left-8 sm:left-12 flex items-center gap-1.5 opacity-30 pointer-events-none" aria-hidden="true">
                <span class="w-1 h-1 rounded-full bg-brand-400"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-brand-400/80 -mt-1.5"></span>
                <span class="w-1 h-1 rounded-full bg-brand-400/60 mt-1"></span>
                <span class="w-2 h-2 rounded-full bg-brand-400/70 -mt-1"></span>
                <span class="w-1 h-1 rounded-full bg-brand-400/40"></span>
            </div>

            {{-- Inner Flex Layout --}}
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8 sm:gap-12">
                {{-- Left Text Column --}}
                <div class="max-w-xl text-left">
                    <p class="text-xs sm:text-sm font-semibold tracking-widest uppercase text-brand-500 mb-3">
                        {{ strtoupper($siteName) }}
                    </p>

                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight">
                        <span class="block sm:inline">{{ $line1 }}</span>
                        @if($line2)
                            <span class="block sm:inline text-brand-500"> {{ $line2 }}</span>
                        @endif
                    </h2>

                    <p class="mt-4 text-sm sm:text-base text-gray-400 max-w-lg leading-relaxed">
                        {{ $subtitle }}
                    </p>
                </div>

                {{-- Right Actions Column --}}
                <div class="flex flex-wrap items-center gap-4 lg:shrink-0">
                    {{-- Primary Sprout Action Button --}}
                    <button type="button" onclick="openBookModal()"
                            class="inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-full bg-brand-600 hover:bg-brand-500 text-white font-semibold text-sm sm:text-base transition-all duration-300 shadow-lg shadow-brand-900/40 hover:shadow-brand-600/30 hover:scale-[1.02] focus:outline-none">
                        {{-- Sprout Plant SVG Icon --}}
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M7 20h10"/>
                            <path d="M10 20c5.5-2.5.8-6.4 3-10"/>
                            <path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"/>
                            <path d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2z"/>
                        </svg>
                        <span>Book Orchard</span>
                    </button>

                    {{-- Secondary Dark Glass Contact Button --}}
                    <a href="#contact"
                       class="inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-full bg-gray-900/80 hover:bg-gray-800 border border-gray-700/80 hover:border-gray-600 text-gray-200 hover:text-white font-semibold text-sm sm:text-base backdrop-blur-md transition-all duration-300 hover:scale-[1.02] focus:outline-none shadow-sm">
                        {{-- Phone Receiver SVG Icon --}}
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span>Contact Us</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endif
