@php
    $gallerySection = $sections->get('home_gallery');
    $galleryVisible = (! $gallerySection || $gallerySection->is_active);

    // Fallback curated images if gallery table has no records yet
    $fallbackItems = [
        [
            'id' => 'fb-1',
            'image' => '/images/hero/slide-1.jpg',
            'caption' => 'High-Density M9 Apple Plantation & Galvanized Trellis',
            'category' => 'High-Density Orchards',
            'subtitle' => 'Pulwama Orchard Installation',
        ],
        [
            'id' => 'fb-2',
            'image' => '/images/hero/slide-3.jpg',
            'caption' => 'Automated Micro-Drip Irrigation & Fertigation Setup',
            'category' => 'Micro Irrigation',
            'subtitle' => 'Precision Water & Nutrient Control',
        ],
        [
            'id' => 'fb-3',
            'image' => '/images/hero/slide-4.jpg',
            'caption' => 'Anti-Hail Netting Infrastructure for Weather Protection',
            'category' => 'Trellis & Nets',
            'subtitle' => 'Crop Canopy Shielding',
        ],
        [
            'id' => 'fb-4',
            'image' => '/images/hero/slide-2.jpg',
            'caption' => 'Scientific Pruning & Canopy Architecture Management',
            'category' => 'High-Density Orchards',
            'subtitle' => 'Optimum Sunlight Interception',
        ],
        [
            'id' => 'fb-5',
            'image' => 'sections/By91b9nsj7923RDMiDnD0bHtZpIf2sJ1vrycf01Q.jpg',
            'caption' => 'Bumper Apple Harvest Sorted for Cold Storage Logistics',
            'category' => 'Harvest',
            'subtitle' => 'Export-Quality Grade A Apples',
        ],
        [
            'id' => 'fb-6',
            'image' => 'varieties/B1rBOfuA08fZWW0cjxruzXZdVoLWvNyPNUDEaI1t.jpg',
            'caption' => 'Early-Bearing Red Velox & Jeromine Apple Clusters',
            'category' => 'Fruit Varieties',
            'subtitle' => 'Deep Color & Superior Sweetness',
        ],
    ];

    $items = [];
    if ($gallery->isNotEmpty()) {
        foreach ($gallery as $g) {
            $items[] = [
                'id' => $g->id,
                'image' => $g->image,
                'url' => \App\Support\Media::url($g->image),
                'caption' => $g->caption ?? 'Plant Tech Agro Showcase',
                'category' => $g->category ?: 'Field Showcase',
            ];
        }
    } else {
        foreach ($fallbackItems as $fb) {
            $items[] = [
                'id' => $fb['id'],
                'image' => $fb['image'],
                'url' => \App\Support\Media::url($fb['image']),
                'caption' => $fb['caption'],
                'category' => $fb['category'],
            ];
        }
    }

    $categories = array_values(array_unique(array_filter(array_column($items, 'category'))));
@endphp

@if($galleryVisible && count($items) > 0)
<section id="gallery"
         class="py-14 sm:py-20 bg-gray-50 dark:bg-gray-900/60 relative overflow-hidden transition-colors"
         x-data="{
             activeFilter: 'all',
             lightboxOpen: false,
             currentIndex: 0,
             items: {{ json_encode($items) }},
             get filteredItems() {
                 if (this.activeFilter === 'all') return this.items;
                 return this.items.filter(item => item.category === this.activeFilter);
             },
             openLightbox(index) {
                 this.currentIndex = index;
                 this.lightboxOpen = true;
                 document.body.style.overflow = 'hidden';
             },
             closeLightbox() {
                 this.lightboxOpen = false;
                 document.body.style.overflow = '';
             },
             nextImage() {
                 const list = this.filteredItems;
                 this.currentIndex = (this.currentIndex + 1) % list.length;
             },
             prevImage() {
                 const list = this.filteredItems;
                 this.currentIndex = (this.currentIndex - 1 + list.length) % list.length;
             }
         }"
         @keydown.escape.window="closeLightbox()"
         @keydown.arrow-right.window="if (lightboxOpen) nextImage()"
         @keydown.arrow-left.window="if (lightboxOpen) prevImage()">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Section Header --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10 sm:mb-12">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-50 dark:bg-brand-950/60 border border-brand-200 dark:border-brand-800/80 text-brand-700 dark:text-brand-300 text-xs font-bold uppercase tracking-widest mb-3">
                    <svg class="w-3.5 h-3.5 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Field Portfolio & Gallery</span>
                </div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 dark:text-white tracking-tight leading-tight">
                    {{ $gallerySection->title ?? "From Kashmir's Fields" }}
                </h2>
                <p class="text-base sm:text-lg text-gray-600 dark:text-gray-400 mt-2 max-w-xl">
                    Explore real high-density installations, automated drip systems, trellis engineering, and harvests across Kashmir.
                </p>
            </div>

        {{-- Category Filter Tabs (Horizontal scroll on phone, wrap on tablet/desktop) --}}
        @if(count($categories) > 1)
        <div class="flex items-center gap-2 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 sm:flex-wrap" style="scrollbar-width: none; -ms-overflow-style: none;">
            <button type="button"
                    @click="activeFilter = 'all'"
                    :class="activeFilter === 'all'
                        ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30'
                        : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'"
                    class="px-3.5 py-1.5 sm:px-4 sm:py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-200 whitespace-nowrap flex-shrink-0">
                All Photos
            </button>
            @foreach($categories as $cat)
            <button type="button"
                    @click="activeFilter = '{{ $cat }}'"
                    :class="activeFilter === '{{ $cat }}'
                        ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30'
                        : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'"
                    class="px-3.5 py-1.5 sm:px-4 sm:py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-200 whitespace-nowrap flex-shrink-0">
                {{ $cat }}
            </button>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Bento Grid: 2 columns on phone, 3 columns on PC --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-5 lg:gap-6">
        <template x-for="(item, index) in filteredItems" :key="item.id">
            <div @click="openLightbox(index)"
                 :class="{
                     'col-span-2 lg:col-span-2 lg:row-span-2 min-h-[260px] sm:min-h-[380px] lg:min-h-[500px]': (activeFilter === 'all' && index === 0 && filteredItems.length >= 4),
                     'col-span-1 min-h-[175px] sm:min-h-[240px] lg:min-h-[290px]': !(activeFilter === 'all' && index === 0 && filteredItems.length >= 4)
                 }"
                 class="group relative rounded-2xl sm:rounded-3xl overflow-hidden cursor-pointer shadow-sm hover:shadow-2xl transition-all duration-500 bg-gray-900 border border-gray-100/80 dark:border-gray-800/80 flex flex-col justify-end">

                {{-- Image --}}
                <img :src="item.url"
                     :alt="item.caption"
                     loading="lazy"
                     decoding="async"
                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-108 transition-transform duration-700 ease-out">

                {{-- Subtle ambient gradient overlays --}}
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/35 to-black/10 opacity-80 group-hover:opacity-95 transition-opacity duration-300"></div>

                {{-- Top Controls: Category Pill & Expand Button --}}
                <div class="absolute top-2.5 sm:top-4 inset-x-2.5 sm:inset-x-4 flex items-center justify-between pointer-events-none z-10">
                    <span class="inline-flex items-center px-2 py-0.5 sm:px-3 sm:py-1 rounded-full text-[10px] sm:text-xs font-semibold backdrop-blur-md bg-black/45 text-white border border-white/20 shadow-xs"
                          x-text="item.category"></span>

                    <div class="w-7 h-7 sm:w-10 sm:h-10 rounded-full bg-white/25 backdrop-blur-md text-white flex items-center justify-center opacity-85 sm:opacity-0 sm:group-hover:opacity-100 transition-all duration-300 transform sm:translate-y-1 sm:group-hover:translate-y-0 shadow-md">
                        <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/>
                        </svg>
                    </div>
                </div>

                {{-- Bottom Caption Bar --}}
                <div class="relative p-3 sm:p-5 lg:p-6 z-10 transform sm:translate-y-1 sm:group-hover:translate-y-0 transition-transform duration-300">
                    <p :class="(activeFilter === 'all' && index === 0 && filteredItems.length >= 4) ? 'text-sm sm:text-lg lg:text-xl' : 'text-xs sm:text-base lg:text-lg'"
                       class="text-white font-bold tracking-tight drop-shadow-sm line-clamp-2 leading-snug"
                       x-text="item.caption"></p>
                    <p class="hidden sm:flex text-xs sm:text-sm text-gray-300/90 font-medium mt-1 sm:mt-1.5 items-center gap-1.5 opacity-90 group-hover:text-white transition-colors">
                        <span>Click to expand full resolution</span>
                        <svg class="w-3.5 h-3.5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </p>
                </div>

            </div>
        </template>
    </div>

    {{-- Bottom CTA Prompt --}}
    <div class="mt-8 sm:mt-16 text-center">
        <div class="inline-flex flex-col sm:flex-row items-center gap-2.5 sm:gap-6 px-4 py-3 sm:px-6 sm:py-3.5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 shadow-xs">
            <span class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-300">
                Want to see live orchard installations and technical consultations across Kashmir?
            </span>
            <a href="{{ url('/') }}#projects"
               class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-bold text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 transition-colors">
                <span>View Our Field Projects</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>
    </div>

</div>

{{-- Interactive Fullscreen Lightbox Modal (Mobile & PC touch-enabled) --}}
<div x-show="lightboxOpen"
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @touchstart="touchStartX = $event.touches[0].clientX"
     @touchend="if ($event.changedTouches[0].clientX - touchStartX > 40) prevImage(); else if (touchStartX - $event.changedTouches[0].clientX > 40) nextImage();"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/95 backdrop-blur-xl p-3 sm:p-6 lg:p-10 select-none">

    {{-- Top Bar: Category, Counter & Close Button --}}
    <div class="absolute top-3 sm:top-4 inset-x-3 sm:inset-x-8 flex items-center justify-between z-20">
        <div class="flex items-center gap-2 sm:gap-3">
            <span class="px-2.5 py-0.5 sm:px-3.5 sm:py-1 rounded-full text-[10px] sm:text-xs font-bold uppercase tracking-wider bg-brand-500/25 text-brand-300 border border-brand-500/30 backdrop-blur-md"
                  x-text="filteredItems[currentIndex]?.category"></span>
            <span class="text-xs sm:text-sm text-gray-400 font-mono"
                  x-text="`${currentIndex + 1} / ${filteredItems.length}`"></span>
        </div>

        <button type="button"
                @click="closeLightbox()"
                class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors border border-white/15"
                title="Close (Esc)">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Previous Button (Mobile & Desktop) --}}
    <button type="button"
            @click.stop="prevImage()"
            class="flex absolute left-2 sm:left-4 lg:left-8 top-1/2 -translate-y-1/2 w-9 h-9 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-white/15 hover:bg-white/25 text-white items-center justify-center transition-all duration-200 border border-white/15 hover:scale-105 z-20 backdrop-blur-md"
            title="Previous image">
        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>

    {{-- Next Button (Mobile & Desktop) --}}
    <button type="button"
            @click.stop="nextImage()"
            class="flex absolute right-2 sm:right-4 lg:right-8 top-1/2 -translate-y-1/2 w-9 h-9 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-white/15 hover:bg-white/25 text-white items-center justify-center transition-all duration-200 border border-white/15 hover:scale-105 z-20 backdrop-blur-md"
            title="Next image">
        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
        </svg>
    </button>

    {{-- Main Center Image Display --}}
    <div class="relative max-w-5xl max-h-[75vh] sm:max-h-[80vh] w-full flex flex-col items-center justify-center z-10 px-2 sm:px-0"
         @click.outside="closeLightbox()">
        <img :src="filteredItems[currentIndex]?.url"
             :alt="filteredItems[currentIndex]?.caption"
             class="max-w-full max-h-[68vh] sm:max-h-[75vh] w-auto h-auto object-contain rounded-2xl shadow-2xl transition-all duration-300">

        {{-- Caption Banner --}}
        <div class="mt-3 sm:mt-4 text-center px-4 max-w-2xl">
            <p class="text-white text-sm sm:text-base lg:text-lg font-semibold tracking-tight"
               x-text="filteredItems[currentIndex]?.caption"></p>
        </div>
    </div>

</div>

</section>
@endif

