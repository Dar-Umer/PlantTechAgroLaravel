@php
    $siteName = config('shop.site_name', 'Plant Tech Agro');
    $brandParts = explode(' ', trim($siteName), 2);
    $logo = config('shop.logo_url');
    $socials = array_filter([
        'facebook' => config('shop.social_facebook'),
        'instagram' => config('shop.social_instagram'),
        'youtube' => config('shop.social_youtube'),
        'whatsapp' => config('shop.social_whatsapp'),
        'x' => config('shop.social_x'),
    ]);
    $icons = [
        'facebook' => ['#1877F2', 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z'],
        'instagram' => ['#E4405F', 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zm0 10.162a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z'],
        'youtube' => ['#FF0000', 'M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z'],
        'whatsapp' => ['#25D366', 'M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413'],
        'x' => ['#ffffff', 'M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z'],
    ];

    $siteAddress = config('shop.site_address', '56 Murad House, Pine Lane-8, Kurso Rajbagh, Srinagar-190008, Jammu & Kashmir');
    $mapEmbed = config('shop.site_map_embed', '');
    $mapEnabled = (bool) config('shop.site_map_enabled', true);

    $mapSrc = '';
    if (!empty($mapEmbed)) {
        if (preg_match('/src="([^"]+)"/', $mapEmbed, $m)) {
            $mapSrc = $m[1];
        } else {
            $mapSrc = $mapEmbed;
        }
    } else {
        $encodedAddress = urlencode($siteAddress);
        $mapSrc = "https://maps.google.com/maps?q={$encodedAddress}&t=&z=15&ie=UTF8&iwloc=&output=embed";
    }
    $directionsUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($siteAddress);
@endphp
<footer id="contact" class="bg-gray-950 dark:bg-black text-gray-400">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 sm:gap-10">
            {{-- Brand --}}
            <div class="{{ $mapEnabled ? 'lg:col-span-4' : 'lg:col-span-6' }}">
                <div class="flex items-center gap-2 mb-4">
                    @if($logo)
                        <img src="{{ \App\Support\Media::url($logo) }}" alt="{{ $siteName }}" loading="lazy" decoding="async" class="h-10 w-auto object-contain">
                    @else
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-600 to-brand-500 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9-2-9-2-9 2 9 2zm0 0V5m0 0L3 7m9-2l9 2M3 7v6l9 2 9-2V7"/></svg>
                        </div>
                    @endif
                </div>
                <p class="text-sm leading-relaxed max-w-md">{{ config('shop.footer_tagline') }}</p>
                @if($socials)
                    <div class="mt-6 flex gap-3">
                        @foreach($socials as $platform => $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($platform) }}"
                                class="w-9 h-9 rounded-lg flex items-center justify-center hover:scale-105 transition"
                                style="{{ isset($icons[$platform]) ? "background-color: {$icons[$platform][0]};" : 'background-color:#27272a;' }}">
                                <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="{{ $icons[$platform][1] ?? '' }}"/>
                                </svg>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Quick Links --}}
            <div class="lg:col-span-2">
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Company</h3>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="#about" class="hover:text-brand-400 transition">About Us</a></li>
                    <li><a href="#services" class="hover:text-brand-400 transition">Services</a></li>
                    <li><a href="{{ route('varieties.index') }}" class="hover:text-brand-400 transition">Varieties</a></li>
                    <li><a href="#projects" class="hover:text-brand-400 transition">Projects</a></li>
                    <li><a href="#gallery" class="hover:text-brand-400 transition">Gallery</a></li>
                    <li><a href="#blog" class="hover:text-brand-400 transition">Knowledge</a></li>
                </ul>
            </div>

            {{-- Contact --}}
            <div class="{{ $mapEnabled ? 'lg:col-span-3' : 'lg:col-span-4' }}">
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Contact</h3>
                <ul class="space-y-3 text-sm">
                    @if(config('shop.site_email'))
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <a href="mailto:{{ config('shop.site_email') }}" class="hover:text-brand-400 transition break-all">{{ config('shop.site_email') }}</a>
                        </li>
                    @endif
                    @if(config('shop.site_phone'))
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <a href="tel:{{ preg_replace('/\s+/', '', config('shop.site_phone')) }}" class="hover:text-brand-400 transition">{{ config('shop.site_phone') }}</a>
                        </li>
                    @endif
                    @if($siteAddress)
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-brand-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="leading-relaxed">{{ $siteAddress }}</span>
                        </li>
                    @endif
                </ul>
            </div>

            {{-- Interactive Google Map in Footer --}}
            @if($mapEnabled)
                <div class="lg:col-span-3">
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        Our Location
                    </h3>
                    <div class="rounded-2xl overflow-hidden border border-gray-800 bg-gray-900 shadow-md transition-all hover:border-gray-700">
                        <iframe src="{{ $mapSrc }}"
                                width="100%"
                                height="160"
                                style="border:0;"
                                allowfullscreen=""
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                title="Plant Tech Agro Location"
                                class="w-full h-36 sm:h-40 rounded-t-2xl filter contrast-105 opacity-90 hover:opacity-100 transition duration-300">
                        </iframe>
                        <div class="p-2.5 bg-gray-900 border-t border-gray-800 flex items-center justify-between text-xs">
                            <span class="text-gray-400 truncate text-[11px] pr-2">Rajbagh, Srinagar</span>
                            <a href="{{ $directionsUrl }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1 text-brand-400 hover:text-brand-300 font-semibold transition shrink-0">
                                <span>Directions</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="border-t border-gray-900 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <p>© {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>
            <div class="flex gap-6">
                <a href="#" class="hover:text-brand-400 transition">Privacy Policy</a>
                <a href="#" class="hover:text-brand-400 transition">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>
