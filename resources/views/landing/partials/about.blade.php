@php
    $about = $sections->get('about_preview');
    $aboutVisible = (! $about || $about->is_active);
    $aboutDescription = $about?->content['description'] ?? null;
    $aboutSubtitle = $about?->subtitle ?? null;
@endphp
@if($aboutVisible)
<section id="about" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-14 items-center">
            <div>
                <p class="text-sm font-semibold tracking-widest uppercase text-brand-600 mb-3">About {{ config('shop.site_name', 'Plant Tech Agro') }}</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight leading-tight">
                    {{ $about->title ?? 'Leading Innovation in Kashmir\'s Agriculture' }}
                </h2>
                <p class="mt-6 text-gray-500 leading-relaxed text-lg">
                    {{ $aboutDescription ?? $aboutSubtitle ?? '' }}
                </p>
                <div class="mt-8 space-y-4">
                    @foreach([
                        "We assess your land's soil health and seasonal conditions.",
                        'We provide expert-guided orchard planning and setup.',
                        'We deliver premium seeds and produce in a timely manner.',
                        'We support your goals, budget, and long-term harvest targets.',
                    ] as $point)
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 w-5 h-5 rounded-full bg-brand-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3 h-3 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <p class="text-sm text-gray-600">{{ $point }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-10 flex flex-wrap gap-4">
                    <button type="button" onclick="openBookModal()"
                            class="inline-flex items-center px-6 py-3 rounded-xl bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition">
                        Get Started
                    </button>
                    <a href="#contact"
                       class="inline-flex items-center px-6 py-3 rounded-xl border border-gray-200 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition">
                        Get in Touch
                    </a>
                </div>
            </div>

            <div class="relative">
                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-2xl overflow-hidden bg-gradient-to-br from-brand-800 to-brand-700 aspect-[3/4] mt-10">
                        @if(isset($gallery[1]) && \App\Support\Media::exists($gallery[1]->image))
                            <img src="{{ \App\Support\Media::url($gallery[1]->image) }}" alt="Field work" class="w-full h-full object-cover">
                        @endif
                    </div>
                    <div class="rounded-2xl overflow-hidden bg-gradient-to-br from-brand-700 to-brand-800 aspect-[3/4]">
                        @if(isset($gallery[2]) && \App\Support\Media::exists($gallery[2]->image))
                            <img src="{{ \App\Support\Media::url($gallery[2]->image) }}" alt="Orchard" class="w-full h-full object-cover">
                        @endif
                    </div>
                </div>
                <div class="absolute -bottom-6 left-1/2 -translate-x-1/2 bg-gray-900 rounded-2xl px-6 py-4 shadow-xl">
                    <p class="text-2xl font-extrabold text-brand-400">{{ $stats->first()->value ?? '15' }}{{ $stats->first()->suffix ?? '+' }}</p>
                    <p class="text-xs text-gray-400">{{ $stats->first()->label ?? 'Years of Experience' }}</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endif
