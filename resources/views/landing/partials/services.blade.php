<section id="services" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mb-16">
            <p class="text-sm font-semibold tracking-widest uppercase text-brand-600 mb-3">What We Offer</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Our <span class="text-brand-600">Services</span></h2>
            <p class="mt-4 text-gray-500 leading-relaxed">Modern orchard development, precision farming, and sustainable agricultural solutions powered by innovative technologies, scientific farm management, and expert advisory services.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($services as $service)
                <article class="group relative rounded-2xl border border-gray-100 bg-white shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300 overflow-hidden flex flex-col">
                    <div class="relative h-48 overflow-hidden bg-gradient-to-br from-brand-800 to-brand-700">
                        @if($service->image && \App\Support\Media::exists($service->image))
                            <img src="{{ \App\Support\Media::url($service->image) }}" alt="{{ $service->name }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-14 h-14 text-brand-300/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            </div>
                        @endif
                    </div>
                    <div class="p-6 flex-1 flex flex-col">
                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-brand-700 transition">{{ $service->name }}</h3>
                        <p class="mt-2 text-sm text-gray-500 leading-relaxed flex-1">{{ $service->description }}</p>
                        <button type="button" onclick="openBookModal('{{ $service->id }}')"
                                class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-600 hover:text-brand-700 transition">
                            Book Now
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </button>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
