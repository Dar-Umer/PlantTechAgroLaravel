@if($projects->isNotEmpty())
<section id="projects" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mb-14">
            <p class="text-sm font-semibold tracking-widest uppercase text-brand-600 mb-3">Our Work</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Featured <span class="text-brand-600">Projects</span></h2>
            <p class="mt-4 text-gray-500 leading-relaxed">From high-density orchards to drip irrigation systems — explore how we've transformed farms across Kashmir.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($projects as $project)
                @php $embedSrc = \App\Support\YouTube::embedSrc($project->video_url); @endphp
                <a href="{{ route('project.show', $project) }}"
                   class="group rounded-2xl border border-gray-100 overflow-hidden shadow-sm hover:shadow-lg hover:-translate-y-1 transition duration-300 flex flex-col">
                    <div class="h-48 bg-gradient-to-br from-brand-800 to-brand-700 overflow-hidden">
                        @if($embedSrc)
                            <iframe src="{{ $embedSrc }}"
                                    title="{{ $project->title }}"
                                    loading="lazy"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowfullscreen
                                    class="w-full h-full"></iframe>
                        @elseif($project->featured_image && \App\Support\Media::exists($project->featured_image))
                            <img src="{{ \App\Support\Media::url($project->featured_image) }}" alt="{{ $project->title }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        @endif
                    </div>
                    <div class="p-5 flex-1 flex flex-col">
                        <div class="flex items-center gap-3 text-xs text-gray-400">
                            @if($project->location)
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ $project->location }}
                                </span>
                            @endif
                            @if($project->completed_at)
                                <span>{{ $project->completed_at->format('M Y') }}</span>
                            @endif
                        </div>
                        <h3 class="mt-2 font-bold text-gray-900 group-hover:text-brand-700 transition">{{ $project->title }}</h3>
                        <p class="mt-1.5 text-sm text-gray-500 line-clamp-2 flex-1">{{ $project->description }}</p>
                        <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-600 group-hover:text-brand-700 transition">
                            View Project
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
