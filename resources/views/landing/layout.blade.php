<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('shop.site_name', 'Plant Tech Agro'))</title>
    <meta name="description" content="{{ config('shop.seo_meta_description', config('shop.footer_tagline')) }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
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
    <style>
        @keyframes marquee {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }
        .animate-marquee {
            animation: marquee 40s linear infinite;
        }
        [x-cloak] {
            display: none !important;
        }
    </style>
    <script>
        function openBookModal(serviceId) {
            window.dispatchEvent(new CustomEvent('open-book-modal', {
                detail: { service: serviceId || null }
            }));
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased text-gray-800 bg-white">
    @include('landing.partials.header')

    <main>
        @yield('content')
    </main>

    @include('landing.partials.footer')
    @include('landing.partials.book-modal')
</body>
</html>
