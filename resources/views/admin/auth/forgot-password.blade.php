<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('admin.auth.partials.head', ['title' => 'Forgot Password'])
</head>
<body class="bg-gray-100 font-sans antialiased min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            @if(!empty($theme['logo_url']))
                <img src="{{ $theme['logo_url'] }}" alt="{{ $theme['site_name'] }}" class="h-12 w-auto mx-auto object-contain">
            @else
                <div class="w-12 h-12 bg-brand-600 rounded-2xl flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 sm:p-10">
            <div class="text-center mb-8">
                <h1 class="text-xl font-bold text-gray-900">Forgot your password?</h1>
                <p class="text-sm text-gray-500 mt-1.5">Enter your email and we'll send you a reset link.</p>
            </div>

            @if(session('status'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.password.email') }}" class="space-y-5" x-data="{ loading: false }">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </span>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                               class="w-full rounded-xl border @error('email') border-red-300 @else border-gray-200 @enderror bg-gray-50 pl-10 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                               placeholder="admin@pta.com">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" @click="setTimeout(() => loading = true, 50)"
                        class="w-full bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition shadow-sm">
                    <span x-show="!loading" x-cloak>Send Reset Link</span>
                    <span x-show="loading" x-cloak class="inline-flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Sending...
                    </span>
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-gray-500 mt-6">
            <a href="{{ route('admin.login') }}" class="inline-flex items-center gap-1.5 hover:text-gray-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to login
            </a>
        </p>
    </div>
</body>
</html>