@if($posts->isNotEmpty())
<section id="blog" class="py-24 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mb-14">
            <p class="text-sm font-semibold tracking-widest uppercase text-brand-600 mb-3">From the Blog</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Latest <span class="text-brand-600">Articles</span></h2>
            <p class="mt-4 text-gray-500 leading-relaxed">Expert insights on apple cultivation, orchard management, and modern farming techniques.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($posts as $post)
                <article class="group rounded-2xl border border-gray-100 bg-white overflow-hidden shadow-sm hover:shadow-lg transition flex flex-col">
                    <div class="h-44 bg-gradient-to-br from-brand-800 to-brand-700 overflow-hidden">
                        @if($post->featured_image && \App\Support\Media::exists($post->featured_image))
                            <img src="{{ \App\Support\Media::url($post->featured_image) }}" alt="{{ $post->title }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        @endif
                    </div>
                    <div class="p-5 flex-1 flex flex-col">
                        @if($post->category)
                            <span class="inline-flex self-start items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-50 text-brand-700">{{ $post->category->name }}</span>
                        @endif
                        <h3 class="mt-3 font-bold text-gray-900 group-hover:text-brand-700 transition leading-snug">{{ $post->title }}</h3>
                        <p class="mt-2 text-sm text-gray-500 leading-relaxed flex-1">{{ $post->excerpt }}</p>
                        <p class="mt-4 text-xs text-gray-400">{{ $post->published_at?->format('d M Y') }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
