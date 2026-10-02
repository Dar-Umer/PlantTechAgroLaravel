<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>POS Terminal Sign-in - {{ $posSettings['store_name'] ?? config('pos.store_name', 'Plant Tech Agro') }}</title>

    @if(config('shop.favicon_url'))
        <link rel="icon" href="{{ \App\Support\Media::url(config('shop.favicon_url')) }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ $theme['fontGoogle'] ?? 'Inter:wght@400;500;600;700;800' }}&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    {{-- Dynamic Brand Theme Colors Controlled via Admin Panel --}}
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['{{ $theme['font'] ?? 'Inter' }}', 'system-ui', 'sans-serif'] },
                    colors: {
                        brand: {
                            50:  '{{ $theme['palette'][50] ?? '#f0fdf4' }}',
                            100: '{{ $theme['palette'][100] ?? '#dcfce7' }}',
                            200: '{{ $theme['palette'][200] ?? '#bbf7d0' }}',
                            300: '{{ $theme['palette'][300] ?? '#86efac' }}',
                            400: '{{ $theme['palette'][400] ?? '#4ade80' }}',
                            500: '{{ $theme['palette'][500] ?? '#22c55e' }}',
                            600: '{{ $theme['palette'][600] ?? '#16a34a' }}',
                            700: '{{ $theme['palette'][700] ?? '#15803d' }}',
                            800: '{{ $theme['palette'][800] ?? '#166534' }}',
                            900: '{{ $theme['palette'][900] ?? '#14532d' }}',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full bg-slate-950 font-sans text-slate-100 antialiased selection:bg-brand-500 selection:text-white flex flex-col justify-between">

    {{-- Subtle Ambient Background Grid & Glows --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-brand-600/20 rounded-full blur-[100px]"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-emerald-700/20 rounded-full blur-[100px]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(#1e293b_1px,transparent_1px)] [background-size:24px_24px] opacity-30"></div>
    </div>

    {{-- Top POS Header Bar --}}
    <header class="relative z-10 w-full border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-md px-4 sm:px-8 py-3.5 flex items-center justify-between">
        <div class="flex items-center gap-3">
            @php
                $posLogo = !empty($posSettings['logo_url']) ? $posSettings['logo_url'] : ($theme['logo_url'] ?? null);
            @endphp
            @if($posLogo)
                <img src="{{ $posLogo }}" alt="{{ $posSettings['store_name'] ?? 'POS' }}" class="h-9 w-auto max-w-[140px] object-contain rounded-lg bg-white p-1 shadow-2xs">
            @else
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-600 to-brand-500 flex items-center justify-center text-white shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
            @endif
            <div>
                <div class="text-sm font-bold text-white tracking-tight flex items-center gap-2">
                    <span>{{ $posSettings['store_name'] ?? 'Plant Tech Agro' }}</span>
                    <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-full bg-brand-500/20 text-brand-300 border border-brand-500/30">
                        {{ $posSettings['store_code'] ?? 'PTA-SRX-01' }}
                    </span>
                </div>
                <div class="text-[11px] text-slate-400">Retail Counter &amp; Warehouse Dispatch</div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>POS KIOSK ONLINE</span>
            </span>
        </div>
    </header>

    {{-- Main Container --}}
    <main class="relative z-10 flex-1 flex items-center justify-center p-4 sm:p-6 my-auto">
        <div class="w-full max-w-md">

            {{-- Terminal Login Card --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-xl relative overflow-hidden">

                {{-- Branded Accent Bar at Top --}}
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-brand-500 via-emerald-400 to-brand-600"></div>

                {{-- Card Header --}}
                <div class="text-center mb-6">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-brand-500/15 text-brand-400 border border-brand-500/30 flex items-center justify-center mb-3 shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Cashier Sign-in</h2>
                    <p class="text-xs text-slate-400 mt-1">Enter your assigned cashier credentials to launch billing terminal</p>
                </div>

                {{-- Flash Notifications / Alerts --}}
                @if(session('success'))
                    <div class="mb-5 p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-5 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-5 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs space-y-1">
                        <div class="font-bold flex items-center gap-2 text-rose-200">
                            <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Authentication Error
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 text-[11px] text-rose-300">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Sign-in Form --}}
                <form action="{{ url()->current() }}" method="POST" class="space-y-4" x-data="{ showPass: false }">
                    @csrf

                    <div>
                        <label for="login" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Cashier ID / Email or Mobile
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <input type="text"
                                   name="login"
                                   id="login"
                                   value="{{ old('login') }}"
                                   required
                                   autofocus
                                   placeholder="e.g. cashier@pta.com or 9876543210"
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-700 bg-slate-800/80 text-white placeholder-slate-500 text-sm transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                                Access Password
                            </label>
                            <span class="text-[11px] text-slate-500">Case-sensitive</span>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <input :type="showPass ? 'text' : 'password'"
                                   name="password"
                                   id="password"
                                   required
                                   placeholder="••••••••"
                                   class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-slate-700 bg-slate-800/80 text-white placeholder-slate-500 text-sm transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                            <button type="button"
                                    @click="showPass = !showPass"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-200 focus:outline-none">
                                <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPass" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full mt-2 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-brand-600 hover:bg-brand-500 active:scale-[0.99] text-white font-bold text-sm shadow-lg shadow-brand-900/40 transition duration-150">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        <span>Open Terminal &amp; Sign In</span>
                    </button>
                </form>

                <div class="mt-6 pt-5 border-t border-slate-800 text-center space-y-2">
                    <p class="text-[11px] text-slate-400">
                        Cashier shifts and sales are recorded under your operator profile.
                    </p>
                    <div>
                        <a href="{{ route('admin.login') }}" class="text-xs font-semibold text-brand-400 hover:text-brand-300 transition hover:underline">
                            Switch to Main Admin Panel Sign-in &rarr;
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </main>

    {{-- Bottom Store Info Footer --}}
    <footer class="relative z-10 border-t border-slate-800/80 bg-slate-900/40 px-4 sm:px-8 py-3 text-center sm:flex sm:items-center sm:justify-between text-[11px] text-slate-500">
        <div>
            <span>{{ $posSettings['store_address'] ?? '56 Murad House, Pine Lane-8, Kurso Rajbagh, Srinagar-190008' }}</span>
            @if(!empty($posSettings['store_phone']))
                <span class="mx-1.5">•</span>
                <span>Tel: {{ $posSettings['store_phone'] }}</span>
            @endif
        </div>
        <div class="mt-1 sm:mt-0 font-mono text-[10px] text-slate-400">
            Powered by Plant Tech Agro Retail POS
        </div>
    </footer>

</body>
</html>
