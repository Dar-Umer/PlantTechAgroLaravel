@php
    $gallerySection = $sections->get('home_gallery');
    $galleryVisible = (! $gallerySection || $gallerySection->is_active);
@endphp
@if($galleryVisible && $gallery->isNotEmpty())
<section id="gallery" class="py-24 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mb-12">
            <p class="text-sm font-semibold tracking-widest uppercase text-brand-600 mb-3">Gallery</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $gallerySection->title ?? "From Kashmir's Fields" }}</h2>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($gallery as $index => $image)
                <div class="relative rounded-2xl overflow-hidden bg-brand-900 {{ $index === 0 ? 'col-span-2 row-span-2' : '' }} aspect-{{ $index === 0 ? '[4/3]' : 'square' }} group">
                    @if(\App\Support\Media::exists($image->image))
                        <img src="{{ \App\Support\Media::url($image->image) }}" alt="{{ $image->caption ?? 'Gallery image' }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-brand-800 to-brand-700">
                            <svg class="w-10 h-10 text-brand-300/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                    @if($image->caption)
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-gray-900/70 to-transparent p-4 opacity-0 group-hover:opacity-100 transition">
                            <p class="text-sm text-white font-medium">{{ $image->caption }}</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
