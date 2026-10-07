@php
    $appDownloadUrl = config('mobile.android_update_url') ?: 'https://play.google.com/store/apps/details?id=com.plant.tech';
    $siteName = config('shop.site_name', 'Plant Tech Agro');
    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' . urlencode($appDownloadUrl) . '&margin=6';
@endphp
<section id="app-download" class="py-16 sm:py-20 lg:py-24 bg-gradient-to-b from-gray-50 via-white to-gray-50 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950 relative overflow-hidden border-t border-gray-100 dark:border-gray-800">
    {{-- Background glows --}}
    <div class="absolute top-1/2 left-0 -translate-y-1/2 w-96 h-96 bg-brand-500/10 dark:bg-brand-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/3 right-0 w-96 h-96 bg-emerald-500/10 dark:bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
            {{-- Left Info Column --}}
            <div class="lg:col-span-7 text-left space-y-6 sm:space-y-8">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-brand-50 dark:bg-brand-950/60 border border-brand-200 dark:border-brand-800 text-brand-700 dark:text-brand-300 text-xs font-bold tracking-wide uppercase shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-brand-500 animate-pulse"></span>
                    <span>Official Android App • High-Density Agritech</span>
                </div>

                <div>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 dark:text-white tracking-tight leading-tight">
                        Precision Orchard Care <br class="hidden sm:inline">
                        <span class="bg-gradient-to-r from-brand-600 via-brand-500 to-emerald-600 bg-clip-text text-transparent">In the Palm of Your Hand</span>
                    </h2>
                    <p class="mt-4 text-base sm:text-lg text-gray-600 dark:text-gray-300 leading-relaxed max-w-2xl">
                        Built especially for Kashmir's high-density apple orchardists. Track plant growth stages, monitor soil health, raise instant support tickets with photos, and receive audible alerts—even when your phone is locked.
                    </p>
                </div>

                {{-- App Key Features Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div class="p-4 rounded-2xl bg-white dark:bg-gray-800/80 border border-gray-100 dark:border-gray-700/60 shadow-sm flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-brand-50 dark:bg-brand-900/40 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Orchard Dashboard</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Real-time status of your acreage, rootstock varieties, and stage schedules.</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-white dark:bg-gray-800/80 border border-gray-100 dark:border-gray-700/60 shadow-sm flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Support Tickets & Chat</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Attach crop disease photos and chat directly with our expert agronomists.</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-white dark:bg-gray-800/80 border border-gray-100 dark:border-gray-700/60 shadow-sm flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Audible Push Alerts</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Reliable sound alerts for spray calendars even when the app is closed.</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-white dark:bg-gray-800/80 border border-gray-100 dark:border-gray-700/60 shadow-sm flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Orders & Invoices</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Instant PDF invoices, order receipts, and live nursery tree dispatch statuses.</p>
                        </div>
                    </div>
                </div>

                {{-- Download Actions & QR code --}}
                <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-4 sm:gap-6">
                    {{-- Google Play Button --}}
                    <a href="{{ $appDownloadUrl }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center justify-center gap-3.5 px-6 py-4 rounded-2xl bg-gray-950 hover:bg-black text-white font-bold text-base shadow-xl shadow-gray-950/20 hover:scale-[1.02] transition border border-gray-800 group">
                        <svg class="w-7 h-7 shrink-0" viewBox="0 0 24 24" fill="none">
                            <path d="M3.609 1.814C3.228 2.183 3 2.709 3 3.279v17.442c0 .57.228 1.096.61 1.465l10.183-10.186L3.609 1.814z" fill="#00C1A6"/>
                            <path d="M17.27 8.531L5.26 1.705 13.793 12l3.477-3.469z" fill="#0083D6"/>
                            <path d="M3.609 22.186l13.661-7.755L13.793 12 3.61 22.186z" fill="#EB2A44"/>
                            <path d="M20.682 10.469l-3.412-1.938-3.477 3.469 3.477 3.469 3.412-1.938c.762-.433.762-1.137 0-1.562z" fill="#FFC900"/>
                        </svg>
                        <div class="text-left leading-tight">
                            <span class="block text-[11px] uppercase tracking-wider text-gray-400 font-medium">GET IT ON</span>
                            <span class="block text-base font-extrabold text-white">Google Play</span>
                        </div>
                    </a>

                    {{-- QR Code Scan Box --}}
                    <div class="flex items-center gap-3 p-2.5 sm:p-3 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm">
                        <div class="w-16 h-16 rounded-xl overflow-hidden bg-white p-1 border border-gray-100 shrink-0">
                            <img src="{{ $qrUrl }}" alt="Scan QR to download app" class="w-full h-full object-contain" loading="lazy">
                        </div>
                        <div class="text-left">
                            <p class="text-xs font-bold text-gray-900 dark:text-white">Scan with Camera</p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Install directly on any Android device</p>
                            <span class="inline-block mt-1 text-[10px] font-semibold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-950/60 px-1.5 py-0.5 rounded">Free Download</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Phone Mockup Column --}}
            <div class="lg:col-span-5 flex justify-center lg:justify-end relative">
                {{-- Decorative Circle Backdrop --}}
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="w-72 h-72 sm:w-80 sm:h-80 rounded-full bg-gradient-to-tr from-brand-600/20 to-emerald-400/20 blur-2xl"></div>
                </div>

                {{-- Modern Realistic Smartphone Mockup Frame --}}
                <div class="relative w-[285px] sm:w-[315px] rounded-[42px] p-3 bg-gray-900 dark:bg-gray-950 shadow-2xl shadow-brand-950/30 border-4 border-gray-800 ring-1 ring-white/10 select-none">
                    {{-- Speaker & Camera Notch --}}
                    <div class="absolute top-4 left-1/2 -translate-x-1/2 w-28 h-4 bg-gray-950 rounded-full flex items-center justify-center gap-2 z-30">
                        <span class="w-2.5 h-2.5 rounded-full bg-gray-800"></span>
                        <span class="w-8 h-1 rounded-full bg-gray-800"></span>
                    </div>

                    {{-- Smartphone Screen Container --}}
                    <div class="w-full rounded-[32px] overflow-hidden bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 text-xs border border-gray-800 relative shadow-inner">
                        {{-- In-App Status Bar --}}
                        <div class="pt-3 px-5 pb-2 flex items-center justify-between text-[10px] text-gray-500 dark:text-gray-400 font-semibold bg-white dark:bg-gray-900">
                            <span>09:41</span>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3c-4.97 0-9 4.03-9 9 0 2.12.74 4.07 1.97 5.61L12 22l7.03-4.39C20.26 16.07 21 14.12 21 12c0-4.97-4.03-9-9-9z"/></svg>
                                <span>5G</span>
                                <div class="w-4 h-2 border border-current rounded-sm p-0.5"><div class="h-full w-full bg-brand-500"></div></div>
                            </div>
                        </div>

                        {{-- In-App App Bar --}}
                        <div class="px-4 py-3 bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-brand-600 text-white flex items-center justify-center font-black text-xs shadow-sm">
                                    PTA
                                </div>
                                <div>
                                    <div class="text-[11px] font-extrabold text-gray-900 dark:text-white leading-tight">Plant Tech Agro</div>
                                    <div class="text-[9px] text-brand-600 dark:text-brand-400 font-semibold">Customer & Orchard Portal</div>
                                </div>
                            </div>
                            <div class="relative">
                                <div class="w-7 h-7 rounded-full bg-brand-50 dark:bg-brand-900/40 text-brand-600 dark:text-brand-400 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                </div>
                                <span class="absolute -top-0.5 -right-0.5 w-2 h-2 rounded-full bg-red-500 ring-2 ring-white dark:ring-gray-900"></span>
                            </div>
                        </div>

                        {{-- In-App Body Content --}}
                        <div class="p-3.5 space-y-3 bg-gray-50/50 dark:bg-gray-950/40">
                            {{-- Welcome Banner --}}
                            <div class="p-3 rounded-2xl bg-gradient-to-r from-brand-600 to-emerald-600 text-white shadow-md shadow-brand-900/20">
                                <div class="text-[10px] text-white/80 uppercase font-bold tracking-wider">Welcome Back</div>
                                <div class="text-sm font-extrabold mt-0.5">Orchardist Dashboard</div>
                                <div class="mt-2 flex items-center justify-between text-[10px] text-white/90 pt-1 border-t border-white/20">
                                    <span>High-Density Acreage: <strong>4 Kanals</strong></span>
                                    <span class="bg-white/20 px-2 py-0.5 rounded-full font-bold">M9 Clonal</span>
                                </div>
                            </div>

                            {{-- In-App Orchard Card --}}
                            <div class="p-3 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <span class="font-bold text-[11px] text-gray-900 dark:text-white">Block A: Gala Redlum</span>
                                    </div>
                                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 font-bold">Healthy</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 pt-1 text-[10px]">
                                    <div class="bg-gray-50 dark:bg-gray-900/60 p-1.5 rounded-lg">
                                        <span class="text-gray-400 block text-[9px]">Irrigation:</span>
                                        <span class="font-bold text-gray-700 dark:text-gray-200">Drip Active</span>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-900/60 p-1.5 rounded-lg">
                                        <span class="text-gray-400 block text-[9px]">Stage:</span>
                                        <span class="font-bold text-gray-700 dark:text-gray-200">Fruit Bud Burst</span>
                                    </div>
                                </div>
                            </div>

                            {{-- In-App Ticket Pill --}}
                            <div class="p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-lg bg-brand-50 dark:bg-brand-900/40 text-brand-600 flex items-center justify-center text-[10px]">
                                        🎫
                                    </div>
                                    <div>
                                        <div class="text-[10px] font-bold text-gray-800 dark:text-gray-200">Ticket #104 (Pruning)</div>
                                        <div class="text-[8px] text-gray-400">Agronomist replied 10m ago</div>
                                    </div>
                                </div>
                                <span class="text-[8px] font-bold text-brand-600 bg-brand-50 dark:bg-brand-950/60 px-1.5 py-0.5 rounded-md">Open</span>
                            </div>

                            {{-- In-App Bottom Tab Bar --}}
                            <div class="pt-1 border-t border-gray-200 dark:border-gray-800 flex items-center justify-around text-[9px] text-gray-400">
                                <div class="text-brand-600 dark:text-brand-400 font-bold flex flex-col items-center">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                    <span>Home</span>
                                </div>
                                <div class="flex flex-col items-center">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    <span>Orchard</span>
                                </div>
                                <div class="flex flex-col items-center">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    <span>Orders</span>
                                </div>
                                <div class="flex flex-col items-center">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span>Account</span>
                                </div>
                            </div>
                        </div>

                        {{-- Home indicator bar at bottom --}}
                        <div class="py-1.5 flex justify-center bg-white dark:bg-gray-900">
                            <span class="w-20 h-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
