@extends('landing.layout')

@section('title', 'Apple & Fruit Varieties — ' . config('shop.site_name', 'Plant Tech Agro'))

@section('content')
<section id="varieties" class="pt-28 sm:pt-36 pb-16 sm:pb-20 bg-gray-50/60 dark:bg-gray-950 min-h-screen"
         x-data="{
             selectedCategory: 'all',
             searchQuery: '',
             activeModalVariety: null,
             openModal(variety) {
                 this.activeModalVariety = variety;
             },
             closeModal() {
                 this.activeModalVariety = null;
             }
         }">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Hero Section (Matching Front Page Hero Gradient) --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-900 via-brand-800 to-brand-600 text-white shadow-xl mb-10">
            <div class="absolute inset-0 opacity-15"
                 style="background-image: radial-gradient(circle at 20% 20%, rgba(255,255,255,.35) 0, transparent 40%), radial-gradient(circle at 80% 70%, rgba(255,255,255,.25) 0, transparent 35%);">
            </div>
            <div class="relative px-6 sm:px-12 py-12 sm:py-16 text-left">
                <p class="text-xs sm:text-sm font-semibold tracking-widest uppercase text-brand-200 mb-3">High-Density Orchard Varieties</p>
                <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight mb-4 leading-tight">
                    Premium Apple & Rootstock Varieties
                </h1>
                <p class="text-base sm:text-lg text-brand-100 max-w-2xl leading-relaxed">
                    Explore our curated collection of high-yielding, virus-free apple varieties and clonal rootstocks (M9, MM106) engineered for maximum fruit color, size, and shelf-life.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <a href="#catalogue"
                       class="inline-flex items-center gap-2 rounded-xl bg-white text-brand-800 px-6 py-3 text-sm font-bold shadow-lg hover:bg-brand-50 transition">
                        Explore Varieties
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    </a>
                    <button type="button" onclick="openBookModal()"
                            class="inline-flex items-center gap-2 rounded-xl border border-white/30 bg-white/10 backdrop-blur-xs text-white px-6 py-3 text-sm font-bold hover:bg-white/20 transition">
                        Book Plantation Consultation
                    </button>
                </div>
            </div>
        </div>

        {{-- Filter & Live Search Bar --}}
        <div id="catalogue" class="mb-8 space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-900 p-4 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-2xs">
                {{-- Search Box --}}
                <div class="relative flex-1 max-w-md">
                    <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text"
                           x-model="searchQuery"
                           placeholder="Search by variety name, taste, origin, or season..."
                           class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                </div>

                {{-- Category Filter Pills --}}
                @php
                    $categoriesList = $varieties->pluck('category')->filter()->unique()->values();
                @endphp
                <div class="flex flex-wrap items-center gap-2">
                    <button @click="selectedCategory = 'all'"
                            :class="selectedCategory === 'all' ? 'bg-brand-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition">
                        All Varieties ({{ $varieties->count() }})
                    </button>

                    @foreach($categoriesList as $cat)
                        <button @click="selectedCategory = '{{ addslashes($cat) }}'"
                                :class="selectedCategory === '{{ addslashes($cat) }}' ? 'bg-brand-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition">
                            {{ $cat }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Varieties Catalogue Grid --}}
        @if($varieties->isEmpty())
            <div class="text-center py-20 bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800">
                <div class="w-16 h-16 rounded-full bg-brand-50 dark:bg-gray-800 text-brand-600 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Varieties catalogue updated soon</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-sm mx-auto">Our team is adding new high-density apple varieties. Check back shortly!</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($varieties as $variety)
                    @php
                        $jsonVariety = [
                            'id' => $variety->id,
                            'name' => $variety->name,
                            'category' => $variety->category,
                            'season' => $variety->season,
                            'taste' => $variety->taste,
                            'origin' => $variety->origin,
                            'color' => $variety->color,
                            'storage_life' => $variety->storage_life,
                            'short_description' => $variety->short_description,
                            'description' => $variety->description,
                            'image_url' => ($variety->image && \App\Support\Media::exists($variety->image)) ? \App\Support\Media::url($variety->image) : null,
                        ];
                    @endphp

                    <article
                        x-show="(selectedCategory === 'all' || selectedCategory === '{{ addslashes($variety->category ?? '') }}') &&
                                (searchQuery === '' || '{{ strtolower(addslashes($variety->name . ' ' . $variety->category . ' ' . $variety->season . ' ' . $variety->taste . ' ' . $variety->origin)) }}'.includes(searchQuery.toLowerCase()))"
                        x-transition:enter="transition ease-out duration-300 transform"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        class="group bg-white dark:bg-gray-900 rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-800 shadow-sm dark:shadow-none hover:shadow-xl hover:-translate-y-1 transition duration-300 flex flex-col justify-between">

                        <div>
                            {{-- Image Cover --}}
                            <div class="relative h-52 overflow-hidden bg-gray-100 dark:bg-gray-800">
                                @if($variety->image && \App\Support\Media::exists($variety->image))
                                    <img src="{{ \App\Support\Media::url($variety->image) }}" alt="{{ $variety->name }}"
                                         loading="lazy" decoding="async"
                                         class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-brand-800 to-emerald-700">
                                        <span class="text-3xl font-black text-white/30">{{ strtoupper(substr($variety->name, 0, 2)) }}</span>
                                    </div>
                                @endif

                                @if($variety->category)
                                    <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-xs font-bold bg-white/95 dark:bg-gray-900/90 text-brand-700 dark:text-brand-300 backdrop-blur-xs shadow-xs">
                                        {{ $variety->category }}
                                    </span>
                                @endif

                                @if($variety->is_featured)
                                    <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500 text-white shadow-xs">
                                        Featured
                                    </span>
                                @endif
                            </div>

                            {{-- Card Details --}}
                            <div class="p-5">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 transition">{{ $variety->name }}</h3>

                                @if($variety->short_description)
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 leading-relaxed line-clamp-2">{{ $variety->short_description }}</p>
                                @endif

                                {{-- Badges List --}}
                                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs font-medium">
                                    @if($variety->season)
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-brand-50 dark:bg-brand-900/30 px-2.5 py-1 text-brand-700 dark:text-brand-300 border border-brand-100 dark:border-brand-800/40">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            {{ $variety->season }}
                                        </span>
                                    @endif

                                    @if($variety->taste)
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-gray-100 dark:bg-gray-800 px-2.5 py-1 text-gray-600 dark:text-gray-300">
                                            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                            {{ $variety->taste }}
                                        </span>
                                    @endif

                                    @if($variety->origin)
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 px-2.5 py-1 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-900/40">
                                            Origin: {{ $variety->origin }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Card Action Footer --}}
                        <div class="p-5 pt-0">
                            <button type="button"
                                    @click="openModal({{ json_encode($jsonVariety) }})"
                                    class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:border-brand-500 hover:text-brand-600 dark:hover:text-brand-400 transition">
                                View Characteristics
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        {{-- Book Consultation CTA Section --}}
        <div class="mt-12 bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 p-6 sm:p-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 shadow-sm">
            <div class="space-y-1">
                <span class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400">Orchard Planning</span>
                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Not sure which variety fits your land & climate?</h3>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 max-w-xl">
                    Speak directly to Plant Tech Agro agronomists about rootstock compatibility (M9/MM106), chilling hours, and high-density plantation yield.
                </p>
            </div>
            <button type="button" onclick="openBookModal()"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 text-white px-6 py-3 text-sm font-bold shadow-lg shadow-brand-600/20 hover:bg-brand-700 transition flex-shrink-0">
                Book Expert Consultation
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </button>
        </div>
    </div>

    {{-- Variety Characteristics Modal --}}
    <div x-show="activeModalVariety !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs" @click="closeModal()"></div>

        <div class="relative bg-white dark:bg-gray-900 rounded-3xl shadow-2xl max-w-2xl w-full p-6 sm:p-8 border border-gray-100 dark:border-gray-800 z-10 max-h-[90vh] overflow-y-auto"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <button type="button" @click="closeModal()" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600 dark:hover:text-white p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <template x-if="activeModalVariety">
                <div class="space-y-6 text-left">
                    {{-- Modal Header Image & Title --}}
                    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4">
                        <template x-if="activeModalVariety.image_url">
                            <img :src="activeModalVariety.image_url" :alt="activeModalVariety.name" class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl object-cover border border-gray-200 dark:border-gray-700 flex-shrink-0">
                        </template>
                        <template x-if="!activeModalVariety.image_url">
                            <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-brand-700 to-emerald-600 text-white font-bold text-2xl flex items-center justify-center flex-shrink-0">
                                <span x-text="activeModalVariety.name ? activeModalVariety.name.substring(0, 2).toUpperCase() : ''"></span>
                            </div>
                        </template>
                        <div>
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300" x-text="activeModalVariety.category || 'Variety'"></span>
                            <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1" x-text="activeModalVariety.name"></h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="activeModalVariety.short_description"></p>
                        </div>
                    </div>

                    {{-- Characteristics Grid --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 p-4 rounded-2xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800 text-xs">
                        <div>
                            <span class="text-gray-400 font-medium block">Harvest Season</span>
                            <strong class="text-gray-900 dark:text-white text-sm" x-text="activeModalVariety.season || 'N/A'"></strong>
                        </div>
                        <div>
                            <span class="text-gray-400 font-medium block">Taste & Flavor</span>
                            <strong class="text-gray-900 dark:text-white text-sm" x-text="activeModalVariety.taste || 'N/A'"></strong>
                        </div>
                        <div>
                            <span class="text-gray-400 font-medium block">Origin / Breeder</span>
                            <strong class="text-gray-900 dark:text-white text-sm" x-text="activeModalVariety.origin || 'N/A'"></strong>
                        </div>
                        <div>
                            <span class="text-gray-400 font-medium block">Color & Appearance</span>
                            <strong class="text-gray-900 dark:text-white text-sm" x-text="activeModalVariety.color || 'N/A'"></strong>
                        </div>
                        <div>
                            <span class="text-gray-400 font-medium block">Storage Capacity</span>
                            <strong class="text-gray-900 dark:text-white text-sm" x-text="activeModalVariety.storage_life || 'N/A'"></strong>
                        </div>
                        <div>
                            <span class="text-gray-400 font-medium block">Type</span>
                            <strong class="text-gray-900 dark:text-white text-sm" x-text="activeModalVariety.category || 'Standard'"></strong>
                        </div>
                    </div>

                    {{-- Description --}}
                    <template x-if="activeModalVariety.description">
                        <div class="space-y-2">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">Detailed Specs & Growth Characteristics</h4>
                            <div class="prose prose-sm dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 leading-relaxed" x-html="activeModalVariety.description"></div>
                        </div>
                    </template>

                    {{-- Modal Actions --}}
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-end gap-3">
                        <button type="button" @click="closeModal()" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Close</button>
                        <button type="button" @click="closeModal(); openBookModal()" class="px-5 py-2.5 rounded-xl bg-brand-600 text-white text-xs font-bold hover:bg-brand-700 transition">Inquire About This Variety</button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</section>
@endsection
