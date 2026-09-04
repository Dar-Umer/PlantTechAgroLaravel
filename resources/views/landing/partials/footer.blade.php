@php
    $siteName = config('shop.site_name', 'Plant Tech Agro');
    $brandParts = explode(' ', trim($siteName), 2);
    $socials = array_filter([
        'facebook' => config('shop.social_facebook'),
        'instagram' => config('shop.social_instagram'),
        'youtube' => config('shop.social_youtube'),
        'whatsapp' => config('shop.social_whatsapp'),
    ]);
@endphp
<footer id="contact" class="bg-gray-950 text-gray-400">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
            {{-- Brand --}}
            <div class="lg:col-span-2">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-600 to-brand-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9-2-9-2-9 2 9 2zm0 0V5m0 0L3 7m9-2l9 2M3 7v6l9 2 9-2V7"/></svg>
                    </div>
                    <span class="text-lg font-extrabold text-white">{{ $brandParts[0] }}<span class="font-light text-gray-400">{{ $brandParts[1] ?? '' }}</span></span>
                </div>
                <p class="text-sm leading-relaxed max-w-md">{{ config('shop.footer_tagline') }}</p>
                @if($socials)
                    <div class="mt-6 flex gap-3">
                        @foreach($socials as $platform => $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($platform) }}"
                               class="w-9 h-9 rounded-lg bg-gray-900 border border-gray-800 flex items-center justify-center hover:border-brand-600 hover:text-brand-400 transition capitalize">
                                {{ strtoupper(substr($platform, 0, 1)) }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Quick Links --}}
            <div>
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Company</h3>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="#about" class="hover:text-brand-400 transition">About Us</a></li>
                    <li><a href="#services" class="hover:text-brand-400 transition">Services</a></li>
                    <li><a href="#projects" class="hover:text-brand-400 transition">Projects</a></li>
                    <li><a href="#gallery" class="hover:text-brand-400 transition">Gallery</a></li>
                    <li><a href="#blog" class="hover:text-brand-400 transition">Knowledge</a></li>
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Contact</h3>
                <ul class="space-y-3 text-sm">
                    @if(config('shop.site_email'))
                        <li>
                            <a href="mailto:{{ config('shop.site_email') }}" class="hover:text-brand-400 transition">{{ config('shop.site_email') }}</a>
                        </li>
                    @endif
                    @if(config('shop.site_phone'))
                        <li>
                            <a href="tel:{{ preg_replace('/\s+/', '', config('shop.site_phone')) }}" class="hover:text-brand-400 transition">{{ config('shop.site_phone') }}</a>
                        </li>
                    @endif
                    @if(config('shop.site_address'))
                        <li class="leading-relaxed">{{ config('shop.site_address') }}</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <div class="border-t border-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <p>© {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>
            <div class="flex gap-6">
                <a href="#" class="hover:text-brand-400 transition">Privacy Policy</a>
                <a href="#" class="hover:text-brand-400 transition">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>
