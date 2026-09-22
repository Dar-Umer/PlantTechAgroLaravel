@if($stats->isNotEmpty())
<section id="impact" class="py-8 sm:py-12 bg-gray-950 relative overflow-hidden">
    <div class="absolute inset-0 opacity-15 bg-[radial-gradient(circle_at_20%_50%,rgba(16,185,129,0.5),transparent_50%),radial-gradient(circle_at_80%_50%,rgba(5,150,105,0.4),transparent_45%)]"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mb-8 sm:mb-10">
            <p class="text-sm font-semibold tracking-widest uppercase text-brand-500 mb-3">Our Impact</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">Grown by <span class="text-brand-500">Numbers</span></h2>
            <p class="mt-4 text-gray-400 leading-relaxed">Every season we grow stronger in reach, trust, and the quality delivered to farmers across Kashmir.</p>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach($stats as $stat)
                <div x-data="window.impactCounter({{ json_encode($stat->value) }}, {{ json_encode($stat->value) }})"
                     class="rounded-2xl border border-gray-800 bg-gray-900/60 p-4 sm:p-8 text-center hover:border-brand-700 transition duration-300">
                    <p class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight">
                        <span x-text="display">{{ $stat->value }}</span><span class="text-brand-500">{{ $stat->suffix }}</span>
                    </p>
                    <p class="mt-2 sm:mt-3 text-xs sm:text-sm font-medium text-gray-400">{{ $stat->label }}</p>
                </div>
            @endforeach
        </div>

        <script>
            window.impactCounter = function (rawTarget, finalFallback) {
                return {
                    display: '0',
                    hasStarted: false,
                    init() {
                        if (!('IntersectionObserver' in window)) {
                            this.display = finalFallback;
                            return;
                        }
                        const num = parseFloat(String(rawTarget).replace(/[^0-9.]/g, ''));
                        if (isNaN(num)) {
                            this.display = finalFallback;
                            return;
                        }
                        const observer = new IntersectionObserver((entries) => {
                            entries.forEach((entry) => {
                                if (entry.isIntersecting && !this.hasStarted) {
                                    this.hasStarted = true;
                                    this.startCounting(num, finalFallback);
                                    observer.disconnect();
                                }
                            });
                        }, { threshold: 0.15 });
                        observer.observe(this.$el);
                    },
                    startCounting(target, fallback) {
                        const duration = 1600;
                        const startTime = performance.now();
                        const isFloat = String(rawTarget).includes('.');
                        const decimals = isFloat ? String(rawTarget).split('.')[1].length : 0;

                        const step = (now) => {
                            const elapsed = now - startTime;
                            const progress = Math.min(elapsed / duration, 1);
                            const ease = 1 - Math.pow(1 - progress, 3);
                            const currentVal = target * ease;

                            if (isFloat) {
                                this.display = currentVal.toFixed(decimals);
                            } else if (target >= 1000) {
                                this.display = Math.floor(currentVal).toLocaleString();
                            } else {
                                this.display = Math.floor(currentVal).toString();
                            }

                            if (progress < 1) {
                                requestAnimationFrame(step);
                            } else {
                                this.display = fallback;
                            }
                        };
                        requestAnimationFrame(step);
                    }
                };
            };
        </script>

        <div class="mt-8 sm:mt-10 overflow-hidden" aria-hidden="true">
            <div class="flex gap-8 w-max animate-marquee text-sm font-semibold text-gray-600 uppercase tracking-wider">
                @foreach(array_merge($marqueeTags, $marqueeTags) as $tag)
                    <span class="flex items-center gap-8 flex-shrink-0">{{ $tag }} <span class="text-brand-600">·</span></span>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
