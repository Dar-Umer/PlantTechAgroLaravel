@if($partners->isNotEmpty())
<section id="partners" class="py-16 bg-gray-50 border-b border-gray-100 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-6 mb-10">
            <h2 class="text-sm font-semibold tracking-widest uppercase text-gray-500">Trusted Partners</h2>
            <p class="text-3xl font-extrabold text-gray-900">{{ $partners->count() }}<span class="text-brand-600">+</span><span class="block text-xs font-medium text-gray-400 tracking-wide mt-1">PARTNERS</span></p>
        </div>
    </div>
    <div class="relative">
        <div class="flex gap-12 w-max animate-marquee px-6">
            @foreach($partners->merge($partners) as $partner)
                <div class="flex-shrink-0 flex items-center justify-center h-14 px-6 bg-white rounded-xl border border-gray-100 shadow-sm">
                    @if($partner->logo && \App\Support\Media::exists($partner->logo))
                        <img src="{{ \App\Support\Media::url($partner->logo) }}" alt="{{ $partner->name }}" class="h-8 w-auto object-contain grayscale opacity-70 hover:grayscale-0 hover:opacity-100 transition">
                    @else
                        <span class="text-sm font-bold text-gray-400">{{ $partner->name }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
