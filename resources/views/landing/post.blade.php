@extends('landing.layout')

@section('title', ($post->meta_title ?: $post->title) . ' — ' . config('shop.site_name', 'Plant Tech Agro'))

@section('content')
<article class="pt-24 sm:pt-28 pb-16 bg-gray-50/50 dark:bg-gray-950/80 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumb & Back Navigation --}}
        <div class="flex items-center justify-between gap-3 mb-6 sm:mb-8">
            <nav class="flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400 min-w-0">
                <a href="{{ url('/') }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition flex-shrink-0">Home</a>
                <svg class="w-3.5 h-3.5 text-gray-300 dark:text-gray-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <a href="{{ url('/') }}#blog" class="hover:text-brand-600 dark:hover:text-brand-400 transition flex-shrink-0">Knowledge</a>
                @if($post->category)
                    <svg class="w-3.5 h-3.5 text-gray-300 dark:text-gray-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="text-gray-700 dark:text-gray-300 font-medium truncate">{{ $post->category->name }}</span>
                @endif
            </nav>

            <a href="{{ url('/') }}#blog"
               class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 hover:text-brand-600 dark:hover:text-brand-400 shadow-2xs transition flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                <span class="hidden sm:inline">Back to Blog</span>
                <span class="sm:hidden">Back</span>
            </a>
        </div>

        {{-- Article Header (Matching Front Page Header Design) --}}
        <header class="space-y-4 sm:space-y-6">
            <div class="flex flex-wrap items-center gap-2.5 sm:gap-3 text-xs sm:text-sm">
                @if($post->category)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-brand-50 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 border border-brand-200/40 dark:border-brand-800/40">
                        {{ $post->category->name }}
                    </span>
                @endif

                @if(!empty($readingTime))
                    <span class="inline-flex items-center gap-1 text-gray-500 dark:text-gray-400">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $readingTime }} min read
                    </span>
                @endif

                @if($post->published_at)
                    <span class="inline-flex items-center gap-1 text-gray-500 dark:text-gray-400">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ $post->published_at->format('d M Y') }}
                    </span>
                @endif
            </div>

            <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 dark:text-white tracking-tight leading-tight">
                {{ $post->title }}
            </h1>

            @if($post->excerpt)
                <p class="text-base sm:text-lg text-gray-600 dark:text-gray-400 leading-relaxed font-normal">
                    {{ $post->excerpt }}
                </p>
            @endif

            {{-- Author Meta & Share Actions Bar --}}
            <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-b border-gray-100 dark:border-gray-800 py-3.5">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-br from-brand-600 to-brand-500 flex items-center justify-center text-white font-bold text-sm shadow-xs flex-shrink-0">
                        {{ strtoupper(substr($post->author->name ?? 'A', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-xs sm:text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $post->author->name ?? 'Plant Tech Agro Specialist' }}
                        </p>
                        <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400">Agricultural Specialist</p>
                    </div>
                </div>

                {{-- Social Share Buttons --}}
                <div x-data="{ copied: false, shareUrl: window.location.href, shareTitle: '{{ addslashes($post->title) }}' }" class="flex items-center gap-2">
                    <span class="text-xs text-gray-400 hidden md:inline">Share article:</span>
                    <a :href="'https://twitter.com/intent/tweet?text=' + encodeURIComponent(shareTitle) + '&url=' + encodeURIComponent(shareUrl)"
                       target="_blank" rel="noopener noreferrer"
                       class="w-9 h-9 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-300 hover:text-sky-500 hover:border-sky-300 transition flex items-center justify-center"
                       title="Share on Twitter / X">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <a :href="'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(shareUrl)"
                       target="_blank" rel="noopener noreferrer"
                       class="w-9 h-9 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-300 hover:text-blue-600 hover:border-blue-300 transition flex items-center justify-center"
                       title="Share on Facebook">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a :href="'https://api.whatsapp.com/send?text=' + encodeURIComponent(shareTitle + ' ' + shareUrl)"
                       target="_blank" rel="noopener noreferrer"
                       class="w-9 h-9 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-300 hover:text-emerald-500 hover:border-emerald-300 transition flex items-center justify-center"
                       title="Share on WhatsApp">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-1.147 4.195 4.19-1.096z"/></svg>
                    </a>
                    <button @click="navigator.clipboard.writeText(shareUrl); copied = true; setTimeout(() => copied = false, 2500)"
                            class="w-9 h-9 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-300 hover:text-brand-600 hover:border-brand-300 transition flex items-center justify-center relative"
                            title="Copy link">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span x-show="copied" x-cloak class="absolute -top-8 left-1/2 -translate-x-1/2 px-2 py-0.5 bg-gray-900 text-white text-[10px] rounded shadow whitespace-nowrap">Copied!</span>
                    </button>
                </div>
            </div>
        </header>

        {{-- Featured Image Container --}}
        @if($post->featured_image && \App\Support\Media::exists($post->featured_image))
            <div class="mt-6 sm:mt-8 rounded-2xl sm:rounded-3xl overflow-hidden shadow-lg dark:shadow-none border border-gray-100 dark:border-gray-800 bg-gray-100 dark:bg-gray-900">
                <img src="{{ \App\Support\Media::url($post->featured_image) }}"
                     alt="{{ $post->title }}"
                     class="w-full h-auto max-h-[280px] sm:max-h-[460px] object-cover">
            </div>
        @endif

        {{-- Post Main Body Content (Front Page Styling Alignment) --}}
        @if($post->content)
            <div class="mt-6 sm:mt-8 rounded-2xl sm:rounded-3xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 sm:p-8 md:p-10 shadow-sm dark:shadow-none">
                <div class="prose prose-base sm:prose-lg prose-emerald dark:prose-invert max-w-none leading-relaxed text-gray-800 dark:text-gray-200 overflow-hidden">
                    {!! \App\Support\HtmlSanitizer::clean($post->content) !!}
                </div>
            </div>
        @endif

        {{-- Book Services CTA Box (Matching Front Page CTA) --}}
        <div class="mt-8 sm:mt-10 rounded-2xl sm:rounded-3xl border border-brand-200 dark:border-brand-900/50 bg-gradient-to-br from-brand-600 via-brand-700 to-emerald-800 p-6 sm:p-8 text-white shadow-lg overflow-hidden relative">
            <div class="relative z-10 flex flex-col sm:flex-row items-center justify-between gap-6 text-center sm:text-left">
                <div class="space-y-1">
                    <p class="text-xs font-bold uppercase tracking-wider text-brand-200">High-Tech Farming Solutions</p>
                    <h3 class="text-xl sm:text-2xl font-extrabold text-white">Need Expert Agricultural Support?</h3>
                    <p class="text-xs sm:text-sm text-brand-100 max-w-xl">
                        Book professional drone spraying, orchard health analysis, or soil testing directly from Plant Tech Agro specialists.
                    </p>
                </div>
                <button type="button" onclick="openBookModal()"
                        class="px-6 py-3 rounded-xl bg-white text-brand-700 hover:bg-brand-50 text-sm font-bold shadow-md hover:scale-105 transition transform flex-shrink-0">
                    Book Service Now
                </button>
            </div>
        </div>

        {{-- Author Bio Card --}}
        <div class="mt-8 sm:mt-10 rounded-2xl sm:rounded-3xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 sm:p-6 shadow-sm dark:shadow-none flex flex-col sm:flex-row items-center sm:items-start gap-4 text-center sm:text-left">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-brand-600 to-brand-500 text-white flex items-center justify-center text-xl font-bold flex-shrink-0 shadow-xs">
                {{ strtoupper(substr($post->author->name ?? 'A', 0, 1)) }}
            </div>
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Written by {{ $post->author->name ?? 'Plant Tech Agro Team' }}</h3>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                    Providing insights on modern apple farming, orchard management, drone spraying technology, and sustainable agriculture in Kashmir.
                </p>
            </div>
        </div>

        {{-- Next / Previous Navigation Cards --}}
        @if($previous || $next)
            <div class="mt-8 sm:mt-10 grid grid-cols-1 sm:grid-cols-2 gap-4">
                @if($previous)
                    <a href="{{ route('post.show', $previous) }}" class="group p-4 sm:p-5 rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm dark:shadow-none hover:shadow-md transition">
                        <span class="text-xs font-semibold text-brand-600 dark:text-brand-400 flex items-center gap-1 mb-1">
                            <svg class="w-3.5 h-3.5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            Previous Article
                        </span>
                        <p class="text-sm font-bold text-gray-900 dark:text-white line-clamp-2 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition">
                            {{ $previous->title }}
                        </p>
                    </a>
                @else
                    <div></div>
                @endif

                @if($next)
                    <a href="{{ route('post.show', $next) }}" class="group p-4 sm:p-5 rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm dark:shadow-none hover:shadow-md transition text-right">
                        <span class="text-xs font-semibold text-brand-600 dark:text-brand-400 flex items-center justify-end gap-1 mb-1">
                            Next Article
                            <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                        <p class="text-sm font-bold text-gray-900 dark:text-white line-clamp-2 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition">
                            {{ $next->title }}
                        </p>
                    </a>
                @endif
            </div>
        @endif

        {{-- Related Articles Section (Matching Front Page Partial Exactly) --}}
        @if($related->isNotEmpty())
            <div class="mt-12 sm:mt-16">
                <div class="flex items-center justify-between mb-6 sm:mb-8">
                    <div>
                        <p class="text-xs sm:text-sm font-semibold tracking-widest uppercase text-brand-600 dark:text-brand-400 mb-1">From the Blog</p>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Related <span class="text-brand-600 dark:text-brand-400">Articles</span></h2>
                    </div>
                    <a href="{{ url('/') }}#blog" class="inline-flex items-center gap-1 text-xs font-bold text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 transition">
                        View All
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($related as $item)
                        <a href="{{ route('post.show', $item) }}" class="group rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden shadow-sm dark:shadow-none hover:shadow-lg transition flex flex-col">
                            <div class="h-44 bg-gradient-to-br from-brand-800 to-brand-700 overflow-hidden relative">
                                @if($item->featured_image && \App\Support\Media::exists($item->featured_image))
                                    <img src="{{ \App\Support\Media::url($item->featured_image) }}" alt="{{ $item->title }}"
                                         loading="lazy" decoding="async"
                                         class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                @endif
                            </div>
                            <div class="p-5 flex-1 flex flex-col">
                                @if($item->category)
                                    <span class="inline-flex self-start items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-50 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300">{{ $item->category->name }}</span>
                                @endif
                                <h3 class="mt-3 font-bold text-gray-900 dark:text-white group-hover:text-brand-700 dark:group-hover:text-brand-400 transition leading-snug line-clamp-2">{{ $item->title }}</h3>
                                @if($item->excerpt)
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 leading-relaxed flex-1 line-clamp-2">{{ $item->excerpt }}</p>
                                @endif
                                <p class="mt-4 text-xs text-gray-400 dark:text-gray-500">{{ $item->published_at?->format('d M Y') }}</p>
                                <span class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-brand-600 dark:text-brand-400 group-hover:text-brand-700 dark:group-hover:text-brand-300 transition">
                                    Read More
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</article>

<style>
    /* Content Typography styles matching Tailwind prose and theme colors */
    .prose h1, .prose h2, .prose h3, .prose h4 {
        color: #111827;
        font-weight: 800;
        margin-top: 1.5em;
        margin-bottom: 0.5em;
        line-height: 1.25;
    }
    .dark .prose h1, .dark .prose h2, .dark .prose h3, .dark .prose h4 {
        color: #ffffff;
    }
    .prose p {
        margin-top: 1em;
        margin-bottom: 1em;
        line-height: 1.75;
    }
    .prose blockquote {
        border-left: 4px solid #059669;
        padding-left: 1.25rem;
        font-style: italic;
        margin: 1.5em 0;
        color: #374151;
        background: rgba(5, 150, 105, 0.05);
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
        border-radius: 0 0.75rem 0.75rem 0;
    }
    .dark .prose blockquote {
        color: #d1d5db;
        background: rgba(5, 150, 105, 0.1);
    }
    .prose ul {
        list-style-type: disc;
        padding-left: 1.25rem;
        margin: 1em 0;
    }
    .prose ol {
        list-style-type: decimal;
        padding-left: 1.25rem;
        margin: 1em 0;
    }
    .prose li {
        margin: 0.35em 0;
    }
    .prose img {
        border-radius: 1rem;
        margin: 1.5em auto;
        max-width: 100%;
        height: auto;
    }
    .prose a {
        color: #059669;
        text-decoration: underline;
        font-weight: 600;
    }
    .dark .prose a {
        color: #34d399;
    }
    .prose table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5em 0;
        display: block;
        overflow-x: auto;
    }
    .prose th, .prose td {
        border: 1px solid #e5e7eb;
        padding: 0.625rem 0.875rem;
        text-align: left;
    }
    .dark .prose th, .dark .prose td {
        border-color: #374151;
    }
    .prose th {
        background-color: #f9fafb;
        font-weight: 700;
    }
    .dark .prose th {
        background-color: #1f2937;
    }
</style>
@endsection