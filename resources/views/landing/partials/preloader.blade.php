{{-- Plant Tech Agro Frontpage Animated Preloader --}}
<div id="pta-preloader"
     class="fixed inset-0 z-[99999] flex flex-col items-center justify-center bg-white dark:bg-gray-950 transition-all duration-700 ease-out select-none"
     aria-label="Loading Plant Tech Agro"
     role="status">

    {{-- Ambient Agritech Background Aura --}}
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-brand-400/15 dark:bg-brand-500/10 blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-emerald-500/15 dark:bg-emerald-500/15 blur-3xl animate-pulse" style="animation-delay: 1.2s;"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(16,185,129,0.14)_0%,transparent_65%)]"></div>
        <div class="absolute inset-0 opacity-[0.35] dark:opacity-[0.18] bg-[linear-gradient(rgba(16,185,129,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(16,185,129,0.08)_1px,transparent_1px)] bg-[size:36px_36px]"></div>
    </div>

    {{-- Center Content --}}
    <div class="relative z-10 flex flex-col items-center text-center px-4 max-w-sm">

        {{-- Animated Tech-Nature Orbital Emblem --}}
        <div class="relative w-28 h-28 flex items-center justify-center">
            {{-- Pulsing Concentric Radar Rings --}}
            <div class="absolute inset-0 rounded-full border border-brand-500/30 dark:border-brand-400/30 animate-ping opacity-60" style="animation-duration: 2.2s;"></div>
            <div class="absolute -inset-3 rounded-full border border-dashed border-brand-400/40 dark:border-brand-500/40 animate-spin" style="animation-duration: 16s;"></div>

            {{-- Orbiting Satellite Particle (Agritech Drone/Sprout node) --}}
            <div class="absolute -inset-3.5 animate-spin" style="animation-duration: 3s; animation-timing-function: cubic-bezier(0.4, 0, 0.2, 1);">
                <span class="absolute top-0 left-1/2 -translate-x-1/2 w-3 h-3 rounded-full bg-brand-500 dark:bg-brand-400 shadow-[0_0_12px_rgba(16,185,129,0.9)] flex items-center justify-center">
                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                </span>
            </div>

            {{-- Emblem Box / Logo Container --}}
            <div class="relative w-20 h-20 rounded-2xl bg-white/95 dark:bg-gray-900/90 backdrop-blur-md p-3 shadow-2xl border border-brand-100 dark:border-brand-900/60 flex items-center justify-center overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-tr from-brand-500/10 via-transparent to-emerald-400/10 dark:from-brand-500/20"></div>

                @if(!empty($theme['logo_url']))
                    <img src="{{ $theme['logo_url'] }}"
                         alt="{{ $theme['site_name'] }}"
                         class="w-full h-full object-contain relative z-10 animate-pulse"
                         style="animation-duration: 2s;">
                @else
                    {{-- Bespoke Sprout & Modern Tech Circuit Icon --}}
                    <svg class="w-10 h-10 text-brand-600 dark:text-brand-400 relative z-10 animate-pulse"
                         style="animation-duration: 2s;"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21V11m0 0a5 5 0 015-5c2.761 0 5 2.239 5 5 0 2.21-1.432 4.084-3.418 4.73M12 11a5 5 0 00-5-5C4.239 6 2 8.239 2 11c0 2.21 1.432 4.084 3.418 4.73M12 3v3" />
                    </svg>
                @endif
            </div>
        </div>

        {{-- Brand Title & Agricultural Vibe Tagline --}}
        <div class="mt-6 space-y-1">
            <h3 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">
                <span>{{ $theme['brand_first'] ?? 'Plant' }}</span>
                <span class="text-brand-600 dark:text-brand-400">{{ $theme['brand_rest'] ?? 'Tech Agro' }}</span>
            </h3>
            <p class="text-[11px] font-semibold uppercase tracking-widest text-brand-600/90 dark:text-brand-400/90">
                Precision Agriculture & Orchards
            </p>
        </div>

        {{-- Elegant Shimmer Progress Bar --}}
        <div class="w-48 sm:w-56 h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full mt-6 overflow-hidden relative shadow-inner">
            <div id="preloader-progress-bar"
                 class="h-full bg-gradient-to-r from-brand-600 via-emerald-400 to-brand-500 rounded-full transition-all duration-300 ease-out"
                 style="width: 20%;"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/60 dark:via-white/20 to-transparent -translate-x-full animate-[preloader-shimmer_1.6s_infinite]"></div>
        </div>

        {{-- Dynamic Status Ticker --}}
        <p id="preloader-status-text"
           class="text-[11px] font-medium text-gray-400 dark:text-gray-500 mt-2.5 tracking-wide transition-opacity duration-200">
            Nurturing digital orchards...
        </p>
    </div>
</div>

<style>
    @keyframes preloader-shimmer {
        100% {
            transform: translateX(200%);
        }
    }
    #pta-preloader.preloader-hidden {
        opacity: 0;
        transform: scale(1.04);
        pointer-events: none;
    }
    @media (prefers-reduced-motion: reduce) {
        #pta-preloader {
            display: none !important;
        }
    }
</style>

<script>
    (function () {
        const preloader = document.getElementById('pta-preloader');
        if (!preloader) return;

        const progressBar = document.getElementById('preloader-progress-bar');
        const statusText = document.getElementById('preloader-status-text');

        const phrases = [
            'Nurturing digital orchards...',
            'Calibrating precision models...',
            'Connecting farm intelligence...',
            'Ready for harvest.'
        ];

        const startTime = Date.now();
        const minDisplayMs = 2000; // Guaranteed minimum 2 seconds display time
        let progress = 15;
        let phraseIndex = 0;
        let isDismissed = false;

        // Smoothly pace progress bar across the 2 seconds
        const progressInterval = setInterval(() => {
            const elapsed = Date.now() - startTime;
            const targetProgress = Math.min(95, Math.floor((elapsed / minDisplayMs) * 100));

            if (progress < targetProgress) {
                progress = targetProgress;
                if (progressBar) progressBar.style.width = progress + '%';
            }

            // Cycle phrases periodically every ~500ms
            const expectedPhraseIndex = Math.min(phrases.length - 2, Math.floor(elapsed / 500));
            if (expectedPhraseIndex !== phraseIndex) {
                phraseIndex = expectedPhraseIndex;
                if (statusText) {
                    statusText.style.opacity = '0';
                    setTimeout(() => {
                        statusText.textContent = phrases[phraseIndex];
                        statusText.style.opacity = '1';
                    }, 120);
                }
            }
        }, 60);

        function dismissPreloader() {
            if (isDismissed) return;
            isDismissed = true;

            clearInterval(progressInterval);
            if (progressBar) progressBar.style.width = '100%';
            if (statusText) {
                statusText.style.opacity = '0';
                setTimeout(() => {
                    statusText.textContent = phrases[phrases.length - 1];
                    statusText.style.opacity = '1';
                }, 80);
            }

            // Allow short pause at 100% before smoothly fading out
            setTimeout(() => {
                preloader.classList.add('preloader-hidden');
                setTimeout(() => {
                    preloader.style.display = 'none';
                    if (preloader.parentNode) {
                        preloader.parentNode.removeChild(preloader);
                    }
                }, 700);
            }, 250);
        }

        // Wait until both window load event fires AND at least 2 seconds have passed
        let windowLoaded = false;
        function tryDismiss() {
            const elapsed = Date.now() - startTime;
            if (windowLoaded && elapsed >= minDisplayMs) {
                dismissPreloader();
            } else {
                const remaining = Math.max(0, minDisplayMs - elapsed);
                setTimeout(dismissPreloader, remaining);
            }
        }

        if (document.readyState === 'complete') {
            windowLoaded = true;
            tryDismiss();
        } else {
            window.addEventListener('load', function () {
                windowLoaded = true;
                tryDismiss();
            });
        }

        // Safety fallback: if anything stalls, dismiss after 3.2 seconds
        setTimeout(dismissPreloader, 3200);
    })();
</script>
