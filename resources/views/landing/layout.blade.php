<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
        (function () {
            var root = document.documentElement;
            var stored = null;
            try { stored = localStorage.getItem('pta-theme'); } catch (e) {}
            var media = window.matchMedia('(prefers-color-scheme: dark)');
            var dark = stored ? stored === 'dark' : media.matches;
            root.classList.toggle('dark', dark);

            window.ptaTheme = {
                isDark: function () { return root.classList.contains('dark'); },
                apply: function (isDark) {
                    root.classList.toggle('dark', isDark);
                    try { localStorage.setItem('pta-theme', isDark ? 'dark' : 'light'); } catch (e) {}
                },
                toggle: function () { this.apply(! this.isDark()); }
            };

            media.addEventListener('change', function (e) {
                var choice = null;
                try { choice = localStorage.getItem('pta-theme'); } catch (err) {}
                if (! choice) { root.classList.toggle('dark', e.matches); }
            });
        })();
    </script>
    <title>@yield('title', config('seo.meta_title') ?: config('shop.site_name', 'Plant Tech Agro'))</title>
    @include('landing.partials.seo-head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
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
        html { color-scheme: light; }
        html.dark { color-scheme: dark; }
        body { transition: background-color .3s ease, color .3s ease; }
        @keyframes marquee {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }
        .animate-marquee {
            animation: marquee 40s linear infinite;
        }
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-up {
            opacity: 0;
            animation: fade-up .7s cubic-bezier(.22,1,.36,1) forwards;
        }
        .rain-drop {
            background: linear-gradient(to bottom, transparent, rgba(5, 150, 105, 0.55));
            animation: rain-fall 4s linear infinite;
            will-change: transform;
        }
        @keyframes rain-fall {
            from { transform: translate3d(0, -12vh, 0); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            to { transform: translate3d(0, 112vh, 0); opacity: 0; }
        }
        [x-cloak] {
            display: none !important;
        }
        .hero-cursor, .hero-cursor * {
            cursor: url("data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20width='26'%20height='26'%20viewBox='0%200%2026%2026'%3E%3Crect%20x='11.5'%20y='2'%20width='2.6'%20height='5'%20rx='1'%20fill='%2378350f'/%3E%3Cellipse%20cx='17'%20cy='3.5'%20rx='3.5'%20ry='1.8'%20fill='%23059669'%20transform='rotate(-30%2017%203.5)'/%3E%3Cpath%20d='M13%206c-1-1.5-3-2-4-2-1.5%200-3%20.8-3.6%202.3C3.8%208%203.2%2010.4%203.2%2013c0%204.4%202.6%209%209.8%209s9.8-4.6%209.8-9c0-2.6-.6-5-2.2-6.7-.6-1.5-2.1-2.3-3.6-2.3-1%200-3%20.5-4%202z'%20fill='%23ef4444'%20stroke='%237f1d1d'%20stroke-width='0.8'/%3E%3Cellipse%20cx='9'%20cy='10'%20rx='2'%20ry='3'%20fill='%23fca5a5'%20opacity='0.7'%20transform='rotate(-20%209%2010)'/%3E%3C/svg%3E") 13 6, auto;
        }
        .hero-glow {
            background: radial-gradient(circle 195px at var(--gx, 50%) var(--gy, 30%), rgba(16, 185, 129, 0.45) 0%, rgba(16, 185, 129, 0.15) 55%, rgba(16, 185, 129, 0.03) 80%, transparent 100%);
            transition: opacity .4s ease;
        }
        @media (prefers-reduced-motion: reduce) {
            .animate-fade-up { animation: none; opacity: 1; transform: none; }
            .rain-drop { animation: none; opacity: 0; }
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
<body class="font-sans antialiased text-gray-800 dark:text-gray-300 bg-white dark:bg-gray-950">
    @include('landing.partials.header')

    <main>
        @yield('content')
    </main>

    @include('landing.partials.footer')
    @include('landing.partials.book-modal')
</body>
</html>
