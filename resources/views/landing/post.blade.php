@extends('landing.layout')

@section('title', ($post->meta_title ?: $post->title) . ' — ' . config('shop.site_name', 'Plant Tech Agro'))

@section('content')
<section class="pt-28 pb-20 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="{{ url('/') }}#blog"
           class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-600 hover:text-brand-700 transition mb-8">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Blog
        </a>

        {{-- Featured image --}}
        @if($post->featured_image && \App\Support\Media::exists($post->featured_image))
            <div class="rounded-2xl overflow-hidden shadow-xl border border-gray-100">
                <img src="{{ \App\Support\Media::url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full max-h-96 object-cover">
            </div>
        @endif

        {{-- Header --}}
        <div class="mt-8">
            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                @if($post->category)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-50 text-brand-700">{{ $post->category->name }}</span>
                @endif
                @if($post->published_at)
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ $post->published_at->format('d M Y') }}
                    </span>
                @endif
                @if($post->author)
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        {{ $post->author->name }}
                    </span>
                @endif
            </div>
            <h1 class="mt-3 text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $post->title }}</h1>
            @if($post->excerpt)
                <p class="mt-4 text-lg text-gray-600 leading-relaxed">{{ $post->excerpt }}</p>
            @endif
        </div>

        {{-- Content --}}
        @if($post->content)
            <div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 prose prose-green max-w-none">
                {!! $post->content !!}
            </div>
        @endif

        {{-- Related --}}
        @if($related->isNotEmpty())
            <div class="mt-14">
                <h2 class="text-xl font-bold text-gray-900 mb-5">More Articles</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    @foreach($related as $item)
                        <a href="{{ route('post.show', $item) }}" class="group rounded-2xl border border-gray-100 bg-white overflow-hidden shadow-sm hover:shadow-lg transition">
                            <div class="h-32 bg-gradient-to-br from-brand-800 to-brand-700 overflow-hidden">
                                @if($item->featured_image && \App\Support\Media::exists($item->featured_image))
                                    <img src="{{ \App\Support\Media::url($item->featured_image) }}" alt="{{ $item->title }}" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-900 group-hover:text-brand-700 transition text-sm leading-snug">{{ $item->title }}</h3>
                                @if($item->published_at)
                                    <p class="mt-1 text-xs text-gray-400">{{ $item->published_at->format('d M Y') }}</p>
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