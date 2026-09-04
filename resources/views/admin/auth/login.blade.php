<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login - {{ config('app.name', 'PTA Admin') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ $theme['fontGoogle'] }}&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['{{ $theme['font'] }}', 'system-ui', 'sans-serif'] },
                    colors: {
                        brand: {
                            50:  '{{ $theme['palette'][50] }}',
                            100: '{{ $theme['palette'][100] }}',
                            200: '{{ $theme['palette'][200] }}',
                            300: '{{ $theme['palette'][300] }}',
                            400: '{{ $theme['palette'][400] }}',
                            500: '{{ $theme['palette'][500] }}',
                            600: '{{ $theme['palette'][600] }}',
                            700: '{{ $theme['palette'][700] }}',
                            800: '{{ $theme['palette'][800] }}',
                            900: '{{ $theme['palette'][900] }}',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 font-sans antialiased min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <div class="text-center mb-8">
                @if(!empty($theme['logo_url']))
                    <img src="{{ $theme['logo_url'] }}" alt="{{ $theme['site_name'] }}" class="h-10 mx-auto mb-4">
                @else
                    <div class="w-12 h-12 bg-brand-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                @endif
                <h1 class="text-xl font-bold text-gray-900">Admin Panel</h1>
                <p class="text-sm text-gray-500 mt-1">Sign in to your admin account</p>
            </div>

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                           placeholder="admin@pta.com">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <input type="password" name="password" id="password" required
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                           placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-brand-600 border-gray-300 rounded focus:ring-brand-500">
                        <span class="text-sm text-gray-600">Remember me</span>
                    </label>
                </div>

                <button type="submit" class="w-full bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition shadow-sm">
                    Sign In
                </button>
            </form>
        </div>
    </div>
</body>
</html>
