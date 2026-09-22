<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('admin.auth.partials.head', ['title' => 'Admin Login'])
</head>
<body class="bg-gray-100 font-sans antialiased min-h-screen">
    <div class="min-h-screen flex">

        {{-- Brand panel --}}
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-gradient-to-br from-brand-700 via-brand-600 to-brand-800">
            <div class="absolute inset-0 opacity-[0.08]" aria-hidden="true">
                <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="grid" width="56" height="56" patternUnits="userSpaceOnUse">
                            <path d="M 56 0 L 0 0 0 56" fill="none" stroke="white" stroke-width="1"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid)"/>
                </svg>
            </div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-white/10 rounded-full blur-3xl" aria-hidden="true"></div>
            <div class="absolute -top-24 -left-24 w-80 h-80 bg-white/5 rounded-full blur-3xl" aria-hidden="true"></div>

            <div class="relative flex flex-col justify-between w-full p-12 xl:p-16">
                <div class="flex items-center gap-3">
                    @if(!empty($theme['logo_url']))
                        <img src="{{ $theme['logo_url'] }}" alt="{{ $theme['site_name'] }}" class="h-14 w-14 object-contain rounded-xl bg-white p-1.5 shadow-sm">
                    @else
                        <div class="w-14 h-14 bg-white/15 backdrop-blur rounded-xl flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                    @endif
                    <span class="text-white text-lg font-bold tracking-tight">{{ $theme['site_name'] }}</span>
                </div>

                <div class="space-y-6">
                    <h1 class="text-4xl xl:text-5xl font-extrabold text-white leading-tight">
                        {{ $theme['brand_first'] }}@if($theme['brand_rest']) <span class="text-white/70">{{ $theme['brand_rest'] }}</span>@endif
                    </h1>
                    <p class="text-brand-100 text-lg max-w-md leading-relaxed">
                        Leads, customers, work orders, inventory, invoices and farm weather — everything for your agritech business in one secure place.
                    </p>
                    <ul class="space-y-3 text-sm text-white/90">
                        <li class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </span>
                            Lead capture to customer conversion with auto-created work orders
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                            Orchard services, farm weather advisories and stage tracking
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.213 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            </span>
                            Invoices, payments, stock control and overdue automation
                        </li>
                    </ul>
                </div>

                <p class="text-brand-200/70 text-sm">
                    &copy; {{ date('Y') }} {{ $theme['site_name'] }}. All rights reserved.
                </p>
            </div>
        </div>

        {{-- Form panel --}}
        <div class="flex-1 flex items-center justify-center bg-gray-50 p-4 sm:p-8">
            <div class="w-full max-w-md">
                @if(session('status'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
                        {{ session('status') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 sm:p-10">
                    <div class="text-center mb-8">
                        @if(!empty($theme['logo_url']))
                            <img src="{{ $theme['logo_url'] }}" alt="{{ $theme['site_name'] }}" class="h-20 w-auto max-w-[200px] mx-auto mb-5 object-contain">
                        @else
                            <div class="w-16 h-16 bg-brand-600 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-sm">
                                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                        @endif
                        <h1 class="text-xl font-bold text-gray-900">Admin Panel</h1>
                        <p class="text-sm text-gray-500 mt-1">Sign in to your admin account</p>
                    </div>

                    @if($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form id="admin-login-form" method="POST" action="{{ route('admin.login.submit') }}" class="space-y-5" autocomplete="on">
                        @csrf

                        @if(\App\Support\Recaptcha::enabled())
                            <input type="hidden" name="g-recaptcha-response" id="admin-recaptcha-token">
                        @endif

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </span>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                                       class="w-full rounded-xl border @error('email') border-red-300 @else border-gray-200 @enderror bg-gray-50 pl-10 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                                        placeholder="example@gmail.com">
                            </div>
                            @error('email')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div x-data="{ show: false, caps: false }">
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                </span>
                                <input :type="show ? 'text' : 'password'" name="password" id="password" required
                                       @keydown="caps = event.getModifierState && event.getModifierState('CapsLock') ? true : false"
                                       @keyup="caps = event.getModifierState && event.getModifierState('CapsLock') ? true : false"
                                       class="w-full rounded-xl border @error('password') border-red-300 @else border-gray-200 @enderror bg-gray-50 pl-10 pr-12 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                                       placeholder="••••••••">
                                <button type="button" @click="show = !show"
                                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 transition"
                                        :aria-label="show ? 'Hide password' : 'Show password'">
                                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                            <p x-show="caps" x-cloak class="mt-1.5 flex items-center gap-1.5 text-xs text-amber-600">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                Caps Lock is on
                            </p>
                            @error('password')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end">
                            <a href="{{ route('admin.password.request') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700 transition">
                                Forgot password?
                            </a>
                        </div>

                        <button type="submit"
                                class="w-full bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed"
                                x-data="{ loading: false }"
                                @click="setTimeout(() => loading = true, 50)">
                            <span x-show="!loading" x-cloak class="inline-flex items-center gap-2">
                                Sign In
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </span>
                            <span x-show="loading" x-cloak class="inline-flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Signing in...
                            </span>
                        </button>
                    </form>
                </div>

                <p class="text-center text-sm text-gray-500 mt-6">
                    <a href="{{ route('landing') }}" class="inline-flex items-center gap-1.5 hover:text-gray-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Back to website
                    </a>
                </p>
            </div>
        </div>
    </div>
    @if(\App\Support\Recaptcha::enabled())
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('apis.recaptcha_site_key') }}"></script>
        <script>
            document.getElementById('admin-login-form').addEventListener('submit', function (e) {
                var tokenInput = document.getElementById('admin-recaptcha-token');
                if (tokenInput.value) return;
                e.preventDefault();
                grecaptcha.ready(function () {
                    grecaptcha.execute('{{ config('apis.recaptcha_site_key') }}', {action: 'admin_login'}).then(function (token) {
                        tokenInput.value = token;
                        e.target.submit();
                    });
                });
            });
        </script>
    @endif
</body>
</html>