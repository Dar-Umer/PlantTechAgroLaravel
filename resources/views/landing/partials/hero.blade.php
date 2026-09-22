@php
    $hero = $sections->get('hero');
    $heroActive = ! $hero || $hero->is_active;

    $title = $hero->title ?? 'Innovation Built to Elevate Agriculture';
    $subtitle = $hero->subtitle ?? 'Driving agricultural excellence with advanced technologies, sustainable farming models, and data-driven solutions for higher productivity and farmer prosperity.';

    $words = preg_split('/\s+/', trim($title)) ?: [];
    $highlight = count($words) > 1 ? end($words) : '';
    $headline = count($words) > 1 ? implode(' ', array_slice($words, 0, -1)) : '';
@endphp
@if($heroActive)
<section id="hero-section" class="hero-cursor relative flex items-center overflow-hidden bg-white dark:bg-gray-950 pt-20 sm:pt-24 lg:pt-28 pb-8 sm:pb-12">
    <div class="absolute inset-0 bg-gradient-to-b from-brand-50 via-white to-gray-50 dark:from-brand-900/40 dark:via-gray-950 dark:to-gray-950"></div>
    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(ellipse_at_top,rgba(16,185,129,0.35),transparent_55%)]"></div>
    <div class="absolute inset-0 opacity-[0.36] bg-[linear-gradient(rgba(15,23,42,0.06)_1px,transparent_1px),linear-gradient(90deg,rgba(15,23,42,0.06)_1px,transparent_1px)] dark:bg-[linear-gradient(rgba(148,163,184,0.10)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.10)_1px,transparent_1px)] bg-[size:64px_64px] [mask-image:radial-gradient(ellipse_at_center,black_20%,transparent_72%)]"></div>
    <div id="hero-glow" class="hero-glow pointer-events-none absolute inset-0 opacity-0"></div>

    {{-- Subtle tilted rain (desktop only) --}}
    @php
        $drops = [
            ['x' => 4,  'h' => 30, 'dur' => 8.2, 'delay' => 0.0, 'op' => 0.28],
            ['x' => 9,  'h' => 42, 'dur' => 9.4, 'delay' => 2.1, 'op' => 0.32],
            ['x' => 14, 'h' => 34, 'dur' => 7.9, 'delay' => 5.0, 'op' => 0.25],
            ['x' => 20, 'h' => 46, 'dur' => 9.1, 'delay' => 1.2, 'op' => 0.33],
            ['x' => 26, 'h' => 30, 'dur' => 8.6, 'delay' => 6.4, 'op' => 0.24],
            ['x' => 33, 'h' => 40, 'dur' => 8.0, 'delay' => 3.3, 'op' => 0.30],
            ['x' => 39, 'h' => 34, 'dur' => 9.5, 'delay' => 0.9, 'op' => 0.26],
            ['x' => 45, 'h' => 48, 'dur' => 8.7, 'delay' => 4.4, 'op' => 0.35],
            ['x' => 51, 'h' => 32, 'dur' => 8.1, 'delay' => 7.0, 'op' => 0.27],
            ['x' => 57, 'h' => 44, 'dur' => 9.2, 'delay' => 1.8, 'op' => 0.33],
            ['x' => 63, 'h' => 36, 'dur' => 8.4, 'delay' => 5.5, 'op' => 0.25],
            ['x' => 69, 'h' => 48, 'dur' => 9.6, 'delay' => 3.0, 'op' => 0.34],
            ['x' => 75, 'h' => 30, 'dur' => 7.8, 'delay' => 0.4, 'op' => 0.24],
            ['x' => 81, 'h' => 40, 'dur' => 8.9, 'delay' => 6.0, 'op' => 0.30],
            ['x' => 87, 'h' => 34, 'dur' => 9.3, 'delay' => 2.6, 'op' => 0.28],
            ['x' => 92, 'h' => 44, 'dur' => 8.3, 'delay' => 4.0, 'op' => 0.33],
            ['x' => 97, 'h' => 32, 'dur' => 9.0, 'delay' => 7.3, 'op' => 0.26],
        ];
    @endphp
    <div class="pointer-events-none absolute inset-0 overflow-hidden hidden lg:block" aria-hidden="true">
        @foreach($drops as $drop)
            <span class="absolute top-0" style="left: {{ $drop['x'] }}%; transform: rotate(10deg)">
                <span class="rain-drop block w-px rounded-full"
                      style="height: {{ $drop['h'] }}px; animation-duration: {{ $drop['dur'] }}s; animation-delay: {{ $drop['delay'] }}s; opacity: {{ $drop['op'] }}">
                </span>
            </span>
        @endforeach
    </div>

    <script>
        window.heroTyping = {
            words: @js($words),
        };
        window.heroTyping.state = {
            words: window.heroTyping.words,
            n: 0,
            html: '',
            init() {
                const total = this.words.join(' ').length;
                if (! total) return;
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    this.n = total;
                    this.render();
                    return;
                }
                const type = () => {
                    this.n = 0;
                    this.render();
                    const iv = setInterval(() => {
                        this.n++;
                        if (this.n >= total) {
                            clearInterval(iv);
                            setTimeout(type, 10000);
                        }
                        this.render();
                    }, 60);
                };
                type();
            },
            escape(s) {
                return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            },
            render() {
                const lastIdx = this.words.length - 1;
                const shown = this.words.join(' ').slice(0, this.n).split(' ');
                let out = '';
                shown.forEach((w, i) => {
                    const esc = this.escape(w);
                    if (i === lastIdx) {
                        out += (i ? ' ' : '') + '<span class="bg-gradient-to-r from-brand-500 via-brand-600 to-emerald-600 bg-clip-text text-transparent">' + esc + '</span>';
                    } else {
                        out += (i ? ' ' : '') + esc;
                    }
                });
                this.html = out;
            }
        };

        (function () {
            const section = document.getElementById('hero-section');
            const glow = document.getElementById('hero-glow');
            if (! section || ! glow) return;
            let raf = null;
            section.addEventListener('mousemove', function (e) {
                if (raf) return;
                raf = requestAnimationFrame(function () {
                    const rect = section.getBoundingClientRect();
                    glow.style.setProperty('--gx', ((e.clientX - rect.left) / rect.width * 100) + '%');
                    glow.style.setProperty('--gy', ((e.clientY - rect.top) / rect.height * 100) + '%');
                    glow.style.opacity = '0.3';
                    raf = null;
                });
            });
            section.addEventListener('mouseleave', function () {
                glow.style.opacity = '0';
            });
        })();
    </script>

    <div class="relative w-full max-w-[88rem] mx-auto px-6 sm:px-8 lg:px-12 text-center">
        <p class="animate-fade-up inline-flex items-center justify-center gap-2 rounded-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-1.5 text-[11px] sm:text-xs font-semibold tracking-widest uppercase text-brand-600 dark:text-brand-400 shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21c5.5 0 8-3.5 8-9V5.25c0-.14-.11-.25-.25-.25H12C6.5 5 4 8.5 4 14s2.5 7 8 7z"/></svg>
            Kashmir's Finest Agritech Company
        </p>

        <h1 class="animate-fade-up [animation-delay:100ms] mt-6 text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 dark:text-white leading-[1.08] tracking-tight text-balance min-h-[1.4em]"
            x-data="window.heroTyping.state">
            <span x-html="html"></span>
        </h1>
        <noscript>
            <h2 class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 dark:text-white leading-[1.08] tracking-tight text-center">
                @if($headline && $highlight)
                    {{ $headline }}
                    <span class="bg-gradient-to-r from-brand-500 via-brand-600 to-emerald-600 bg-clip-text text-transparent">{{ $highlight }}</span>
                @else
                    {{ $title }}
                @endif
            </h2>
        </noscript>

        <p class="animate-fade-up [animation-delay:200ms] mt-6 text-base sm:text-lg text-gray-600 dark:text-gray-400 leading-relaxed max-w-2xl mx-auto">
            {{ $subtitle }}
        </p>

        <div class="animate-fade-up [animation-delay:300ms] mt-10 flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4">
            <button type="button" onclick="openBookModal()"
                    class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-8 py-3.5 rounded-xl bg-brand-600 text-white font-semibold hover:bg-brand-500 transition shadow-lg shadow-brand-600/25">
                Book Now
            </button>
            <a href="#contact"
               class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-8 py-3.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 font-semibold hover:bg-gray-50 dark:hover:bg-gray-800 hover:border-gray-400 dark:hover:border-gray-500 transition">
                Contact Us
            </a>
        </div>

        <div class="animate-fade-up [animation-delay:400ms] mt-8 sm:mt-10 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 sm:gap-5 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
            @foreach($stats->take(3) as $stat)
                <span class="flex items-center gap-2"><strong class="text-gray-900 dark:text-white">{{ $stat->value }}{{ $stat->suffix }}</strong> {{ $stat->label }}</span>
                @if(! $loop->last) <span class="w-px h-4 bg-gray-200 dark:bg-gray-700"></span> @endif
            @endforeach
        </div>

        {{-- Hero Image Slider Banner --}}
        @php
            $defaultSlides = [
                [
                    'image' => asset('images/hero/slide-1.jpg'),
                    'badge' => 'High-Density Orchards',
                    'title' => 'Advanced Trellis & High-Yield Orchard Architecture',
                    'desc' => 'Transforming Kashmir\'s apple orchards with high-density Italian dwarf rootstocks, modern trellis architecture, and higher yields per kanal.',
                    'cta_text' => 'Explore Orchard Booking',
                    'cta_link' => '#services',
                ],
                [
                    'image' => asset('images/hero/slide-2.jpg'),
                    'badge' => 'Certified Apple Varieties',
                    'title' => 'World-Class Clonal Varieties Grafted for Kashmir',
                    'desc' => 'Disease-resistant, high-coloring Gala, Fuji, Red Velox, and King Roat varieties delivering premium market value.',
                    'cta_text' => 'View Varieties',
                    'cta_link' => route('varieties.index'),
                ],
                [
                    'image' => asset('images/hero/slide-3.jpg'),
                    'badge' => 'Precision AgTech & Irrigation',
                    'title' => 'Smart Micro-Drip Irrigation & Automated Fertigation',
                    'desc' => 'Optimize root-zone water efficiency up to 60% with sensor-guided irrigation systems and precision soil nutrition plans.',
                    'cta_text' => 'Soil & Drip Solutions',
                    'cta_link' => '#services',
                ],
                [
                    'image' => asset('images/hero/slide-4.jpg'),
                    'badge' => 'Aerial Drone Spraying',
                    'title' => 'Drone Crop Protection & Orchard Safety Solutions',
                    'desc' => 'Next-gen drone pesticide and nutrient spraying combined with heavy-duty anti-hail net installations to shield your harvest.',
                    'cta_text' => 'Book Consultation',
                    'cta_link' => '#contact',
                ],
            ];

            $customSlides = $hero->content['slides'] ?? [];
            $slides = [];
            if (!empty($customSlides) && is_array($customSlides)) {
                foreach ($customSlides as $idx => $cs) {
                    if (!empty($cs['image'])) {
                        $slides[] = [
                            'image' => \App\Support\Media::url($cs['image']),
                            'badge' => $cs['badge'] ?? ('Featured ' . ($idx + 1)),
                            'title' => $cs['title'] ?? 'Modern Agriculture Excellence',
                            'desc' => $cs['desc'] ?? '',
                            'cta_text' => $cs['cta_text'] ?? 'Learn More',
                            'cta_link' => $cs['cta_link'] ?? '#services',
                        ];
                    }
                }
            }
            if (empty($slides)) {
                $slides = $defaultSlides;
            }
        @endphp

        <div class="animate-fade-up [animation-delay:500ms] mt-10 sm:mt-12 w-full max-w-5xl mx-auto"
             x-data="{
                 current: 0,
                 total: {{ count($slides) }},
                 timer: null,
                 touchStartX: 0,
                 touchEndX: 0,
                 autoplay() {
                     this.stop();
                     this.timer = setInterval(() => { this.next(); }, 5000);
                 },
                 stop() {
                     if (this.timer) {
                         clearInterval(this.timer);
                         this.timer = null;
                     }
                 },
                 next() {
                     this.current = (this.current + 1) % this.total;
                 },
                 prev() {
                     this.current = (this.current - 1 + this.total) % this.total;
                 },
                 goTo(idx) {
                     this.current = idx;
                     this.autoplay();
                 },
                 handleTouchStart(e) {
                     this.touchStartX = e.changedTouches[0].screenX;
                 },
                 handleTouchEnd(e) {
                     this.touchEndX = e.changedTouches[0].screenX;
                     if (this.touchStartX - this.touchEndX > 50) this.next();
                     if (this.touchEndX - this.touchStartX > 50) this.prev();
                 },
                 init() {
                     this.autoplay();
                 }
             }"
             @mouseenter="stop()"
             @mouseleave="autoplay()"
             @touchstart.passive="handleTouchStart($event)"
             @touchend.passive="handleTouchEnd($event)">
            <div class="relative overflow-hidden rounded-3xl bg-gray-900 border border-gray-200/80 dark:border-gray-800 shadow-2xl shadow-brand-900/10 h-[280px] sm:h-[380px] lg:h-[440px] group">
                {{-- Slides --}}
                @foreach($slides as $index => $slide)
                    <div x-show="current === {{ $index }}"
                         x-transition:enter="transition ease-out duration-700"
                         x-transition:enter-start="opacity-0 scale-105"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-500"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute inset-0 w-full h-full"
                         style="{{ $index === 0 ? '' : 'display: none;' }}">
                        <img src="{{ $slide['image'] }}"
                             alt="{{ $slide['title'] }}"
                             loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                             class="w-full h-full object-cover object-center">

                        {{-- Gradient Dark Overlays for Text Legibility --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-950/95 via-gray-950/50 to-black/20"></div>
                        <div class="absolute inset-0 bg-gradient-to-r from-gray-950/85 via-gray-950/40 to-transparent"></div>

                        {{-- Slide Content Overlay --}}
                        <div class="absolute inset-0 p-6 sm:p-8 lg:p-10 flex flex-col justify-end text-left">
                            <div class="max-w-2xl">
                                @if(!empty($slide['badge']))
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-500/25 border border-brand-400/40 text-brand-300 text-[11px] sm:text-xs font-semibold backdrop-blur-md uppercase tracking-wider mb-2.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-brand-400 animate-pulse"></span>
                                        {{ $slide['badge'] }}
                                    </span>
                                @endif

                                <h3 class="text-lg sm:text-2xl lg:text-3xl font-extrabold text-white tracking-tight leading-snug drop-shadow-sm">
                                    {{ $slide['title'] }}
                                </h3>

                                @if(!empty($slide['desc']))
                                    <p class="mt-2 text-xs sm:text-sm text-gray-200/90 leading-relaxed max-w-xl line-clamp-2 sm:line-clamp-none drop-shadow-sm">
                                        {{ $slide['desc'] }}
                                    </p>
                                @endif

                                <div class="mt-4 flex items-center gap-3">
                                    @if(!empty($slide['cta_link']) && !empty($slide['cta_text']))
                                        <a href="{{ $slide['cta_link'] }}"
                                           class="inline-flex items-center px-4 py-2 rounded-xl bg-brand-600 text-white hover:bg-brand-500 text-xs sm:text-sm font-semibold transition-all shadow-md shadow-brand-600/30">
                                            <span>{{ $slide['cta_text'] }}</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Previous & Next Navigation Buttons --}}
                <button type="button"
                        @click="prev(); autoplay()"
                        aria-label="Previous Slide"
                        class="absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 w-9 h-9 sm:w-11 sm:h-11 rounded-full bg-black/40 hover:bg-black/70 text-white backdrop-blur-md border border-white/20 flex items-center justify-center transition-all opacity-80 group-hover:opacity-100 hover:scale-105 z-10 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button type="button"
                        @click="next(); autoplay()"
                        aria-label="Next Slide"
                        class="absolute right-3 sm:right-4 top-1/2 -translate-y-1/2 w-9 h-9 sm:w-11 sm:h-11 rounded-full bg-black/40 hover:bg-black/70 text-white backdrop-blur-md border border-white/20 flex items-center justify-center transition-all opacity-80 group-hover:opacity-100 hover:scale-105 z-10 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </button>

                {{-- Pagination Dot Indicators --}}
                <div class="absolute bottom-4 right-4 sm:bottom-6 sm:right-8 flex items-center gap-2 z-10">
                    <template x-for="(slide, i) in {{ count($slides) }}" :key="i">
                        <button type="button"
                                @click="goTo(i)"
                                :aria-label="'Go to slide ' + (i + 1)"
                                class="h-2 rounded-full transition-all duration-300 focus:outline-none"
                                :class="current === i ? 'w-7 sm:w-9 bg-brand-500' : 'w-2 bg-white/40 hover:bg-white/70'">
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- Curved wave edge that flows into the next section --}}
    <div class="pointer-events-none absolute inset-x-0 -bottom-px text-white dark:text-gray-950 overflow-hidden">
        <svg viewBox="0 0 2880 100" preserveAspectRatio="none" class="animate-wave block w-[200%] h-10 sm:h-12 lg:h-16">
            <path fill="currentColor" d="M0,50 C240,50 200,10 360,26 C520,42 560,74 720,50 C880,26 920,10 1080,26 C1240,42 1200,50 1440,50 C1680,50 1640,10 1800,26 C1960,42 2000,74 2160,50 C2320,26 2360,10 2520,26 C2680,42 2640,50 2880,50 L2880,100 L0,100 Z"/>
        </svg>
    </div>
</section>
@endif