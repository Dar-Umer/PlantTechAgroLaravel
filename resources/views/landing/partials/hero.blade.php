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
<section id="hero-section" class="hero-cursor relative flex items-center overflow-hidden bg-white dark:bg-gray-950 min-h-[68vh] sm:min-h-[80vh] pt-20 pb-10 sm:pt-28 sm:pb-12 lg:pb-16">
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
    </div>

    {{-- Curved wave edge that flows into the next section --}}
    <div class="pointer-events-none absolute inset-x-0 -bottom-px text-white dark:text-gray-950 overflow-hidden">
        <svg viewBox="0 0 2880 100" preserveAspectRatio="none" class="animate-wave block w-[200%] h-10 sm:h-12 lg:h-16">
            <path fill="currentColor" d="M0,50 C240,50 200,10 360,26 C520,42 560,74 720,50 C880,26 920,10 1080,26 C1240,42 1200,50 1440,50 C1680,50 1640,10 1800,26 C1960,42 2000,74 2160,50 C2320,26 2360,10 2520,26 C2680,42 2640,50 2880,50 L2880,100 L0,100 Z"/>
        </svg>
    </div>
</section>
@endif