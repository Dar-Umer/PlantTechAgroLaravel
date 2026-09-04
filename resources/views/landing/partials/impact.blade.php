@if($stats->isNotEmpty())
<section id="impact" class="py-24 bg-gray-950 relative overflow-hidden">
    <div class="absolute inset-0 opacity-15 bg-[radial-gradient(circle_at_20%_50%,rgba(16,185,129,0.5),transparent_50%),radial-gradient(circle_at_80%_50%,rgba(5,150,105,0.4),transparent_45%)]"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mb-14">
            <p class="text-sm font-semibold tracking-widest uppercase text-brand-500 mb-3">Our Impact</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">Grown by <span class="text-brand-500">Numbers</span></h2>
            <p class="mt-4 text-gray-400 leading-relaxed">Every season we grow stronger — in reach, in trust, and in the quality we deliver to farmers across Kashmir.</p>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($stats as $stat)
                <div class="rounded-2xl border border-gray-800 bg-gray-900/60 p-8 text-center hover:border-brand-700 transition">
                    <p class="text-4xl sm:text-5xl font-extrabold text-white">{{ $stat->value }}<span class="text-brand-500">{{ $stat->suffix }}</span></p>
                    <p class="mt-3 text-sm font-medium text-gray-400">{{ $stat->label }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-16 overflow-hidden" aria-hidden="true">
            <div class="flex gap-8 w-max animate-marquee text-sm font-semibold text-gray-600 uppercase tracking-wider">
                @foreach(array_merge($marqueeTags, $marqueeTags) as $tag)
                    <span class="flex items-center gap-8 flex-shrink-0">{{ $tag }} <span class="text-brand-600">·</span></span>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
