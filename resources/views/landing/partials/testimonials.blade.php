@if($testimonials->isNotEmpty())
<section id="testimonials" class="py-24 bg-white dark:bg-gray-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mb-14">
            <p class="text-sm font-semibold tracking-widest uppercase text-brand-600 dark:text-brand-400 mb-3">Testimonials</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">Voices from <span class="text-brand-600 dark:text-brand-400">the Field</span></h2>
            <p class="mt-4 text-gray-500 dark:text-gray-400 leading-relaxed">Hear directly from the farmers and orchard owners who've experienced the difference.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($testimonials as $testimonial)
                @php
                    $hasVideoFile = $testimonial->video && \App\Support\Media::exists($testimonial->video);
                    $videoEmbed = $hasVideoFile ? null : \App\Support\YouTube::embedUrl($testimonial->video_url);
                @endphp
                <figure class="rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 p-7 flex flex-col hover:border-brand-200 dark:hover:border-brand-800 transition">
                    @if($hasVideoFile)
                        <div class="relative aspect-video rounded-xl overflow-hidden bg-black mb-5">
                            <video controls preload="metadata" playsinline class="absolute inset-0 w-full h-full object-contain">
                                <source src="{{ \App\Support\Media::url($testimonial->video) }}">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    @elseif($videoEmbed)
                        <div class="relative aspect-video rounded-xl overflow-hidden bg-black mb-5">
                            <iframe src="{{ $videoEmbed }}?rel=0&modestbranding=1"
                                    title="Video testimonial from {{ $testimonial->name }}"
                                    loading="lazy"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowfullscreen
                                    class="absolute inset-0 w-full h-full"></iframe>
                        </div>
                    @endif
                    <div class="flex gap-0.5 mb-4">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-4 h-4 {{ $i <= $testimonial->rating ? 'text-amber-400' : 'text-gray-300 dark:text-gray-700' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.446a1 1 0 00-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.539 1.118l-3.367-2.446a1 1 0 00-1.176 0l-3.367 2.446c-.783.57-1.838-.196-1.538-1.118l1.286-3.957a1 1 0 00-.363-1.118L2.063 9.385c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.951-.69l1.286-3.958z"/>
                            </svg>
                        @endfor
                    </div>
                    <blockquote class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed flex-1">"{{ $testimonial->content }}"</blockquote>
                    <figcaption class="mt-6 flex items-center gap-3">
                        @if($testimonial->avatar && \App\Support\Media::exists($testimonial->avatar))
                            <img src="{{ \App\Support\Media::url($testimonial->avatar) }}" alt="{{ $testimonial->name }}"
                                 loading="lazy" decoding="async" class="w-10 h-10 rounded-full object-cover border border-gray-200 dark:border-gray-700 flex-shrink-0">
                        @else
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-brand-600 to-brand-500 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                {{ substr($testimonial->name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $testimonial->name }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $testimonial->role }}</p>
                        </div>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif
