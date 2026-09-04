@extends('landing.layout')

@section('title', $project->title . ' — ' . config('shop.site_name', 'Plant Tech Agro'))

@section('content')
<section class="pt-28 pb-20 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="{{ url('/') }}#projects"
           class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-600 hover:text-brand-700 transition mb-8">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Projects
        </a>

        {{-- Media --}}
        @php $embedSrc = \App\Support\YouTube::embedSrc($project->video_url); @endphp
        <div class="rounded-2xl overflow-hidden shadow-xl border border-gray-100 bg-gray-950 aspect-video">
            @if($embedSrc)
                <iframe src="{{ $embedSrc }}"
                        title="{{ $project->title }}"
                        loading="lazy"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                        class="w-full h-full"></iframe>
            @elseif($project->featured_image && \App\Support\Media::exists($project->featured_image))
                <img src="{{ \App\Support\Media::url($project->featured_image) }}" alt="{{ $project->title }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-brand-800 to-brand-700">
                    <svg class="w-16 h-16 text-brand-300/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </div>
            @endif
        </div>

        {{-- Header --}}
        <div class="mt-8">
            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                @if($project->location)
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ $project->location }}
                    </span>
                @endif
                @if($project->completed_at)
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ $project->completed_at->format('M Y') }}
                    </span>
                @endif
            </div>
            <h1 class="mt-3 text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $project->title }}</h1>
            @if($project->description)
                <p class="mt-4 text-lg text-gray-600 leading-relaxed">{{ $project->description }}</p>
            @endif
        </div>

        {{-- Content --}}
        @if($project->content)
            <div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 prose prose-green max-w-none">
                {!! $project->content !!}
            </div>
        @endif

        {{-- Related --}}
        @if($related->isNotEmpty())
            <div class="mt-14">
                <h2 class="text-xl font-bold text-gray-900 mb-5">More Projects</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    @foreach($related as $item)
                        @php $itemEmbed = \App\Support\YouTube::embedSrc($item->video_url); @endphp
                        <a href="{{ route('project.show', $item) }}" class="group rounded-2xl border border-gray-100 bg-white overflow-hidden shadow-sm hover:shadow-lg transition">
                            <div class="h-32 bg-gradient-to-br from-brand-800 to-brand-700 overflow-hidden">
                                @if($itemEmbed)
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-10 h-10 text-white/70 group-hover:scale-110 transition" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    </div>
                                @elseif($item->featured_image && \App\Support\Media::exists($item->featured_image))
                                    <img src="{{ \App\Support\Media::url($item->featured_image) }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-900 group-hover:text-brand-700 transition text-sm leading-snug">{{ $item->title }}</h3>
                                @if($item->location)
                                    <p class="mt-1 text-xs text-gray-400">{{ $item->location }}</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
