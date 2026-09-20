<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $theme['site_name'] }} - Admin</title>
    @if(config('shop.favicon_url'))
        <link rel="icon" href="{{ \App\Support\Media::url(config('shop.favicon_url')) }}">
    @endif
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
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @php
        $sidebarStyle = $theme['sidebar'] ?? 'dark';
        $sidebarBg = match($sidebarStyle) {
            'light' => 'bg-white border-r border-gray-200',
            'brand' => 'bg-brand-700',
            default => 'bg-gray-900',
        };
        $sidebarText = match($sidebarStyle) {
            'light' => 'text-gray-700',
            'brand' => 'text-brand-100',
            default => 'text-gray-300',
        };
        $sidebarTextHover = match($sidebarStyle) {
            'light' => 'hover:bg-gray-100 hover:text-gray-900',
            'brand' => 'hover:bg-brand-600 hover:text-white',
            default => 'hover:bg-gray-800 hover:text-white',
        };
        $sidebarActiveBg = match($sidebarStyle) {
            'light' => 'bg-brand-50 text-brand-700',
            'brand' => 'bg-brand-600/50 text-white',
            default => 'bg-brand-600 text-white',
        };
        $sidebarActiveChildBg = match($sidebarStyle) {
            'light' => 'bg-brand-50/50 text-brand-700',
            'brand' => 'bg-brand-600/30 text-white',
            default => 'bg-brand-600/20 text-brand-400',
        };
        $sidebarGroupActive = match($sidebarStyle) {
            'light' => 'text-brand-600',
            'brand' => 'text-white',
            default => 'text-brand-400',
        };
        $sidebarBorder = match($sidebarStyle) {
            'light' => 'border-gray-200',
            'brand' => 'border-brand-600',
            default => 'border-gray-800',
        };
        $sidebarChildBorder = match($sidebarStyle) {
            'light' => 'border-gray-200',
            'brand' => 'border-brand-500',
            default => 'border-gray-700',
        };
        $sidebarChildText = match($sidebarStyle) {
            'light' => 'text-gray-500',
            'brand' => 'text-brand-200',
            default => 'text-gray-400',
        };
        $topbarBg = $sidebarStyle === 'light' ? 'bg-gray-50' : 'bg-white';
    @endphp
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div x-data="{
            sidebarOpen: false,
            commandPaletteOpen: false,
            init() {
                window.addEventListener('keydown', (e) => {
                    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
                        e.preventDefault();
                        this.commandPaletteOpen = !this.commandPaletteOpen;
                    } else if (e.key === 'Escape' && this.commandPaletteOpen) {
                        this.commandPaletteOpen = false;
                    }
                });
            }
         }"
         class="flex h-screen overflow-hidden">

        <!-- Mobile Overlay -->
        <div x-show="sidebarOpen" x-cloak
             @click="sidebarOpen = false"
             class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
        </div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-40 w-64 {{ $sidebarBg }} transition-transform duration-300 lg:translate-x-0 lg:static lg:z-auto flex flex-col">

            <!-- Branding -->
            <div class="flex items-center justify-between h-16 px-6 {{ $sidebarBg }} border-b {{ $sidebarBorder }}">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2">
                    @if(!empty($theme['logo_url']))
                        <img src="{{ $theme['logo_url'] }}" alt="{{ $theme['site_name'] }}" class="h-8 w-auto">
                    @else
                        <span class="text-xl font-bold text-brand-400">{{ $theme['brand_first'] }}</span>
                        @if($theme['brand_rest'])
                            <span class="text-xl font-light {{ $sidebarStyle === 'light' ? 'text-gray-900' : 'text-white' }}">{{ $theme['brand_rest'] }}</span>
                        @endif
                    @endif
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden {{ $sidebarChildText }} hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5">
                @php
                    $groups = [
                        [
                            'label' => null,
                            'items' => [
                                ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>'],
                            ],
                        ],
                        [
                            'label' => 'Content',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>',
                            'items' => [
                                ['route' => 'admin.posts.index', 'label' => 'Posts'],
                                ['route' => 'admin.testimonials.index', 'label' => 'Testimonials'],
                                ['route' => 'admin.faqs.index', 'label' => 'FAQs'],
                                ['route' => 'admin.gallery.index', 'label' => 'Gallery'],
                            ],
                        ],
                        [
                            'label' => 'Services & Projects',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0"/>',
                            'items' => [
                                ['route' => 'admin.services.index', 'label' => 'Services'],
                                ['route' => 'admin.projects.index', 'label' => 'Projects'],
                            ],
                        ],
                        [
                            'label' => 'Website',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/>',
                            'items' => [
                                ['route' => 'admin.frontend.index', 'label' => 'Frontend'],
                            ],
                        ],
                        [
                            'label' => 'Sales',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>',
                            'items' => [
                                ['route' => 'admin.leads.index', 'label' => 'Leads'],
                                ['route' => 'admin.customers.index', 'label' => 'Customers'],
                                ['route' => 'admin.invoices.index', 'label' => 'Invoices'],
                            ],
                        ],
                        [
                            'label' => 'Apps',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/>',
                            'items' => [
                                ['route' => 'admin.mobile-apps.index', 'label' => 'Mobile Apps'],
                            ],
                        ],
                        [
                            'label' => 'Operations',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 9.75h4.5m-4.5 3h4.5m-4.5 3h4.5m-5.625 3.75h6.75a4.5 4.5 0 004.5-4.5v-3a4.5 4.5 0 00-4.5-4.5H16.5a3 3 0 00-3-3h-3a3 3 0 00-3 3H7.125a4.5 4.5 0 00-4.5 4.5v3a4.5 4.5 0 004.5 4.5h6.75M12 3h.008v.008H12V3z"/>',
                            'items' => [
                                ['route' => 'admin.work-orders.index', 'label' => 'Work Orders'],
                            ],
                        ],
                        [
                            'label' => 'Inventory',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>',
                            'items' => [
                                ['route' => 'admin.products.index', 'label' => 'Products'],
                                ['route' => 'admin.suppliers.index', 'label' => 'Suppliers'],
                                ['route' => 'admin.stock-movements.index', 'label' => 'Stock Movements'],
                                ['route' => 'admin.product-batches.index', 'label' => 'Batches & Expiry'],
                            ],
                        ],
                        [
                            'label' => 'Reports',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
                            'items' => [
                                ['route' => 'admin.reports.gst', 'label' => 'GST & Taxes'],
                            ],
                        ],
                        [
                            'label' => 'Administration',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
                            'items' => [
                                ['route' => 'admin.staff.index', 'label' => 'Staff'],
                                ['route' => 'admin.automation.index', 'label' => 'Automation'],
                                ['route' => 'admin.settings.index', 'label' => 'Settings'],
                            ],
                        ],
                    ];
                @endphp

                @php
                    $currentAdmin = Auth::guard('admin')->user();
                    $superOnlyRoutes = ['admin.staff.index', 'admin.automation.index', 'admin.settings.index', 'admin.mobile-apps.index'];
                    $canView = function ($route) use ($currentAdmin, $superOnlyRoutes) {
                        if (!$currentAdmin) return false;
                        if (!Route::has($route)) return false;
                        if (in_array($route, $superOnlyRoutes, true)) {
                            return $currentAdmin->hasRole('Super Admin');
                        }
                        return true;
                    };
                @endphp

                @foreach ($groups as $group)
                    @if($group['label'] === null)
                        @foreach($group['items'] as $item)
                            @if(Route::has($item['route']) && $canView($item['route']))
                                <a href="{{ route($item['route']) }}"
                                   class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200 {{ Route::currentRouteNamed($item['route']) ? $sidebarActiveBg : $sidebarText . ' ' . $sidebarTextHover }}">
                                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        {!! $item['icon'] !!}
                                    </svg>
                                    {{ $item['label'] }}
                                </a>
                            @endif
                        @endforeach
                    @else
                        @php
                            $hasActive = collect($group['items'])->contains(fn($i) => Route::has($i['route']) && $canView($i['route']) && Route::currentRouteNamed($i['route']));
                            $visibleRoutes = collect($group['items'])->filter(fn($i) => Route::has($i['route']) && $canView($i['route']));
                        @endphp

                        @if($visibleRoutes->isNotEmpty())
                            <div x-data="{ open: {{ $hasActive ? 'true' : 'false' }} }" class="mt-1">
                                <button @click="open = !open"
                                        class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200 {{ $hasActive ? $sidebarGroupActive : $sidebarText . ' ' . $sidebarTextHover }}">
                                    <span class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            {!! $group['icon'] !!}
                                        </svg>
                                        {{ $group['label'] }}
                                    </span>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>

                                <div x-show="open" x-collapse x-cloak class="ml-4 mt-0.5 space-y-0.5 border-l {{ $sidebarChildBorder }} pl-3">
                                    @foreach($group['items'] as $item)
                                        @if(Route::has($item['route']) && $canView($item['route']))
                                            <a href="{{ route($item['route']) }}"
                                               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ Route::currentRouteNamed($item['route']) ? $sidebarActiveChildBg : $sidebarChildText . ' ' . $sidebarTextHover }}">
                                                {{ $item['label'] }}
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                @endforeach
            </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">

            <!-- Top Navbar -->
            <header class="{{ $topbarBg }} shadow-sm border-b border-gray-200 h-16 flex items-center justify-between px-4 lg:px-6 flex-shrink-0">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <h1 class="text-lg font-bold text-gray-800 hidden xl:block">
                        @yield('page-title', 'Admin Panel')
                    </h1>

                    {{-- Quick Search (Ctrl+K) Trigger --}}
                    <button @click="commandPaletteOpen = true"
                            type="button"
                            class="hidden sm:flex items-center gap-2 px-3 py-1.5 text-xs text-gray-400 bg-gray-50 hover:bg-gray-100 hover:text-gray-700 border border-gray-200 rounded-xl transition shadow-xs">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                        <span>Search actions or pages...</span>
                        <kbd class="hidden md:inline-block px-1.5 py-0.5 text-[10px] font-semibold text-gray-500 bg-white border border-gray-200 rounded shadow-2xs font-mono">⌘K</kbd>
                    </button>
                </div>

                <div class="flex items-center space-x-3">
                    {{-- Quick Create Dropdown --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition active:scale-95 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span class="hidden sm:inline">Create</span>
                            <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open" @click.away="open = false" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl z-50 border border-gray-100 py-1.5 divide-y divide-gray-50">
                            <div class="py-1">
                                <a href="{{ route('admin.work-orders.create') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition">
                                    <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold">WO</span>
                                    New Work Order
                                </a>
                                <a href="{{ route('admin.invoices.create') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition">
                                    <span class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs font-bold">INV</span>
                                    New Invoice
                                </a>
                                <a href="{{ route('admin.customers.create') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition">
                                    <span class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">CUST</span>
                                    Add Customer
                                </a>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('admin.stock-movements.create') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition">
                                    <span class="w-6 h-6 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs font-bold">STK</span>
                                    Stock In / Movement
                                </a>
                                <a href="{{ route('admin.products.create') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition">
                                    <span class="w-6 h-6 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center text-xs font-bold">PRD</span>
                                    New Product
                                </a>
                            </div>
                        </div>
                    </div>
                    {{-- Farm weather --}}
                    @if(!empty($headerWeather ?? null))
                        @php
                            $hwCurrent = $headerWeather['current'] ?? [];
                            $hwDaily = array_slice($headerWeather['daily'] ?? [], 0, 3);
                            $hwAdvisory = $headerWeather['advisory'] ?? null;
                            $hwDot = ['alert' => 'bg-red-500', 'caution' => 'bg-amber-500', 'good' => 'bg-emerald-500'][$hwAdvisory['level'] ?? 'good'] ?? 'bg-emerald-500';
                            $hwWind = isset($hwCurrent['wind']) ? ' · Wind ' . $hwCurrent['wind'] . ' km/h' : '';
                        @endphp
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center gap-1.5 text-sm text-gray-600 hover:text-gray-900 p-1.5 rounded-lg hover:bg-gray-100 focus:outline-none" title="Farm weather — {{ $headerWeather['location']['label'] ?? '' }}">
                                <svg class="w-5 h-5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 15a4.5 4.5 0 004.5 4.5H18a3.75 3.75 0 001.332-7.257 3 3 0 00-3.758-3.848 5.25 5.25 0 00-10.233 2.33A4.502 4.502 0 002.25 15z"/></svg>
                                <span class="font-semibold text-gray-800">{{ $hwCurrent['temp'] ?? '–' }}°</span>
                                <span class="hidden xl:inline text-xs text-gray-400">{{ $headerWeather['location']['label'] ?? '' }}</span>
                                <span class="w-2 h-2 rounded-full {{ $hwDot }}"></span>
                            </button>

                            <div x-show="open" @click.away="open = false" x-cloak
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-lg z-50 border border-gray-200 overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <p class="text-sm font-semibold text-gray-900">{{ $headerWeather['location']['label'] ?? 'Farm Weather' }} · {{ $hwCurrent['temp'] ?? '–' }}°C</p>
                                    <p class="text-xs text-gray-500">{{ $hwCurrent['label'] ?? '' }}{{ $hwWind }}</p>
                                </div>
                                @if($hwAdvisory && !empty($hwAdvisory['messages']))
                                    <div class="px-4 py-3 border-b border-gray-100 space-y-1">
                                        @foreach(array_slice($hwAdvisory['messages'], 0, 2) as $message)
                                            <p class="text-xs text-gray-600">• {{ $message }}</p>
                                        @endforeach
                                    </div>
                                @endif
                                @if(!empty($hwDaily))
                                    <div class="flex divide-x divide-gray-100">
                                        @foreach($hwDaily as $day)
                                            <div class="flex-1 px-2 py-2.5 text-center">
                                                <p class="text-[10px] font-medium text-gray-500">{{ \Carbon\Carbon::parse($day['date'])->format('D') }}</p>
                                                <p class="text-sm font-bold text-gray-900">{{ $day['temp_max'] ?? '–' }}°</p>
                                                <p class="text-[10px] text-sky-600">{{ $day['rain_prob_max'] ?? 0 }}%</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @if(auth('admin')->user()?->hasRole('Super Admin'))
                                    <a href="{{ route('admin.settings.index', ['tab' => 'weather']) }}" class="block px-4 py-2.5 text-xs font-medium text-brand-600 hover:bg-gray-50 border-t border-gray-100">Manage weather settings</a>
                                @endif
                            </div>
                        </div>
                    @else
                        @if(!empty($headerWeatherDisabled ?? false))
                            @if(auth('admin')->user()?->hasRole('Super Admin'))
                                <a href="{{ route('admin.settings.index', ['tab' => 'weather']) }}" class="hidden sm:inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100" title="Weather service is disabled — open settings">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 15a4.5 4.5 0 004.5 4.5H18a3.75 3.75 0 001.332-7.257 3 3 0 00-3.758-3.848 5.25 5.25 0 00-10.233 2.33A4.502 4.502 0 002.25 15z"/></svg>
                                    <span>Weather off</span>
                                </a>
                            @endif
                        @endif
                    @endif
                    {{-- Live clock (IST) --}}
                    <div x-data="{ now: new Date(), tick() { this.now = new Date(); }, timeFmt: new Intl.DateTimeFormat('en-IN', { timeZone: 'Asia/Kolkata', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true }), dateFmt: new Intl.DateTimeFormat('en-IN', { timeZone: 'Asia/Kolkata', weekday: 'short', day: 'numeric', month: 'short' }) }" x-init="setInterval(() => tick(), 1000)" class="hidden md:flex items-center gap-1.5 text-gray-600 p-1.5" title="India Standard Time">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-sm font-semibold text-gray-800 tabular-nums" x-text="timeFmt.format(now)"></span>
                        <span class="text-xs text-gray-400" x-text="dateFmt.format(now)"></span>
                    </div>
                    {{-- Notifications --}}
                    @php $unreadNotifications = Auth::guard('admin')->user()?->unreadNotifications ?? collect(); @endphp
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="relative text-gray-500 hover:text-gray-700 p-1.5 focus:outline-none" title="Notifications">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            @if($unreadNotifications->isNotEmpty())
                                <span class="absolute -top-0.5 -right-0.5 w-5 h-5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">{{ $unreadNotifications->count() > 9 ? '9+' : $unreadNotifications->count() }}</span>
                            @endif
                        </button>

                        <div x-show="open" @click.away="open = false" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg z-50 border border-gray-200 overflow-hidden">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-900">Notifications</p>
                                @if($unreadNotifications->isNotEmpty())
                                    <form action="{{ route('admin.notifications.read-all') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs text-brand-600 hover:text-brand-700 font-medium">Mark all read</button>
                                    </form>
                                @endif
                            </div>
                            <div class="max-h-80 overflow-y-auto divide-y divide-gray-50">
                                @forelse($unreadNotifications->take(10) as $notification)
                                    <div class="px-4 py-3 hover:bg-gray-50">
                                        @php $data = $notification->data; @endphp
                                        <p class="text-sm font-medium text-gray-900">{{ $data['title'] ?? 'Notification' }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            @if(isset($data['product_name']))
                                                {{ $data['product_name'] }} — stock {{ \App\Support\Format::qty($data['stock_qty'] ?? 0) }} {{ $data['unit'] }} (threshold {{ \App\Support\Format::qty($data['threshold'] ?? 0) }})
                                                @if(isset($data['supplier'])) · Supplier: {{ $data['supplier'] }}@endif
                                            @elseif(isset($data['number']))
                                                {{ $data['number'] }} — {{ $data['customer'] }} · {{ $data['service'] }}
                                            @elseif(isset($data['lead_id']))
                                                {{ $data['name'] }} ({{ $data['phone'] }}) · {{ $data['service'] }}
                                            @else
                                                {{ json_encode($data) }}
                                            @endif
                                        </p>
                                        <p class="text-[10px] text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                    </div>
                                @empty
                                    <div class="px-4 py-8 text-center text-sm text-gray-400">No unread notifications</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center space-x-2 text-sm text-gray-600 hover:text-gray-900 focus:outline-none">
                            <div class="w-8 h-8 bg-brand-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                {{ substr(Auth::guard('admin')->user()->name ?? 'A', 0, 1) }}
                            </div>
                            <span class="hidden md:inline">{{ Auth::guard('admin')->user()->name ?? 'Admin' }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open" @click.away="open = false" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 z-50 border border-gray-200">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-medium text-gray-900">{{ Auth::guard('admin')->user()->name ?? 'Admin' }}</p>
                                <p class="text-xs text-gray-500">{{ Auth::guard('admin')->user()->email ?? '' }}</p>
                            </div>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 lg:p-6 relative">
                {{-- Floating Toast Notifications (Auto-dismissing) --}}
                @if(session('success'))
                    <div x-data="{ show: true }"
                         x-show="show"
                         x-init="setTimeout(() => show = false, 5000)"
                         x-transition:enter="transition ease-out duration-300 transform"
                         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-200 transform"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                         class="fixed bottom-5 right-5 z-50 flex items-center gap-3 bg-gray-900 text-white px-4 py-3 rounded-2xl shadow-2xl border border-gray-800 max-w-md">
                        <div class="w-7 h-7 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="text-xs font-medium flex-1">
                            {{ session('success') }}
                        </div>
                        <button @click="show = false" class="text-gray-400 hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ show: true }"
                         x-show="show"
                         x-init="setTimeout(() => show = false, 7000)"
                         x-transition:enter="transition ease-out duration-300 transform"
                         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-200 transform"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                         class="fixed bottom-5 right-5 z-50 flex items-center gap-3 bg-gray-900 text-white px-4 py-3 rounded-2xl shadow-2xl border border-rose-900/50 max-w-md">
                        <div class="w-7 h-7 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="text-xs font-medium flex-1">
                            {{ session('error') }}
                        </div>
                        <button @click="show = false" class="text-gray-400 hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-5 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-xs">
                        <div class="flex items-center gap-2 mb-2 font-semibold text-sm text-rose-900">
                            <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Please correct the following errors:
                        </div>
                        <ul class="list-disc list-inside text-xs space-y-1 text-rose-700">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="flex-shrink-0 px-4 lg:px-6 py-3 border-t {{ $sidebarStyle === 'light' ? 'border-gray-200 bg-gray-50' : 'border-gray-100 bg-white' }}">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <p class="text-xs text-gray-400">© {{ date('Y') }} {{ $theme['site_name'] ?? 'Plant Tech Agro' }}</p>
                    <p class="text-xs text-gray-400">Version <span class="font-medium text-gray-600">{{ config('version.number', '1.0') }}</span>@if(\App\Support\AppInfo::commitHash()) <span class="font-mono text-gray-500">({{ \App\Support\AppInfo::commitHash() }})</span>@endif</p>
                </div>
            </footer>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        .html-preview h1 { font-size: 1.5rem; font-weight: 700; margin: 1rem 0 0.5rem; }
        .html-preview h2 { font-size: 1.25rem; font-weight: 600; margin: 1rem 0 0.5rem; }
        .html-preview h3 { font-size: 1.125rem; font-weight: 600; margin: 0.75rem 0 0.5rem; }
        .html-preview h4 { font-size: 1rem; font-weight: 600; margin: 0.75rem 0 0.5rem; }
        .html-preview p { margin: 0.5rem 0; line-height: 1.7; }
        .html-preview ul, .html-preview ol { margin: 0.5rem 0; padding-left: 1.5rem; }
        .html-preview ul { list-style-type: disc; }
        .html-preview ol { list-style-type: decimal; }
        .html-preview li { margin: 0.25rem 0; }
        .html-preview table { width: 100%; border-collapse: collapse; margin: 0.75rem 0; }
        .html-preview th, .html-preview td { border: 1px solid #e5e7eb; padding: 0.5rem 0.75rem; text-align: left; font-size: 0.875rem; }
        .html-preview th { background: #f9fafb; font-weight: 600; }
        .html-preview tr:nth-child(even) { background: #f9fafb; }
        .html-preview strong { font-weight: 600; }
        .html-preview blockquote { border-left: 3px solid #d1d5db; padding-left: 1rem; margin: 0.75rem 0; color: #4b5563; font-style: italic; }
        .html-preview a { color: #2563eb; text-decoration: underline; }
        .html-preview img { max-width: 100%; border-radius: 0.5rem; margin: 0.5rem 0; }
    </style>

    {{-- New-lead popup + tone (polls for unread lead alerts) --}}
    <div x-data="leadPopup({{ max(15, (int) config('automation.new_lead_popup_interval', 60)) }})" x-init="start()">
        <div x-show="current" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-900/60"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 text-center"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                <div class="w-14 h-14 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900">New Lead Received</h3>
                <p class="text-sm text-gray-500 mt-1" x-text="current ? ('Just now · ' + (current.service || 'General enquiry')) : ''"></p>
                <div class="mt-4 rounded-xl bg-gray-50 border border-gray-100 px-4 py-3">
                    <p class="text-lg font-semibold text-gray-900" x-text="current ? current.name : ''"></p>
                    <p class="text-sm text-gray-500" x-text="current ? current.phone : ''"></p>
                </div>
                <div class="mt-5 grid grid-cols-2 gap-3">
                    <button type="button" @click="dismiss()" class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">Dismiss</button>
                    <button type="button" @click="view()" class="px-4 py-2.5 rounded-xl bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 transition">View Lead</button>
                </div>
                <p class="mt-3 text-xs text-gray-400" x-show="queue.length > 0" x-text="queue.length + ' more waiting'"></p>
            </div>
        </div>
    </div>
    <script>
        function leadPopup(pollSeconds) {
            return {
                seen: [],
                queue: [],
                current: null,
                timer: null,
                start() {
                    this.check();
                    this.timer = setInterval(() => this.check(), Math.max(15, pollSeconds) * 1000);
                },
                check() {
                    if (document.hidden) return;
                    fetch('{{ route('admin.notifications.latest') }}', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(function (r) { return r.ok ? r.json() : null; })
                        .then((data) => {
                            if (!data || !data.popup_enabled || !Array.isArray(data.leads)) return;
                            data.leads.forEach((lead) => {
                                if (!lead.notification_id || this.seen.includes(lead.notification_id)) return;
                                this.seen.push(lead.notification_id);
                                this.queue.push(lead);
                            });
                            if (!this.current && this.queue.length) this.next();
                        })
                        .catch(function () {});
                },
                next() {
                    this.current = this.queue.length ? this.queue.shift() : null;
                    if (this.current) this.tone();
                },
                dismiss() {
                    this.ack();
                    this.next();
                },
                view() {
                    var url = this.current ? this.current.url : null;
                    this.ack();
                    this.next();
                    if (url) window.location.href = url;
                },
                ack() {
                    // Mark read so this lead never pops up again (any tab/page).
                    if (!this.current || !this.current.notification_id) return;
                    var token = document.querySelector('meta[name="csrf-token"]');
                    fetch('{{ url('admin/notifications') }}/' + this.current.notification_id + '/read', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': token ? token.content : ''
                        }
                    }).catch(function () {});
                },
                tone() {
                    try {
                        var Ctx = window.AudioContext || window.webkitAudioContext;
                        if (!Ctx) return;
                        // Resume suspended contexts (browser autoplay policy) so the
                        // chime is audible even on quiet tabs.
                        var ctx = new Ctx();
                        if (ctx.state === 'suspended') ctx.resume();
                        // Loud, urgent triple-beep: bright triangle waves at full gain.
                        var notes = [987.77, 987.77, 1318.51];
                        notes.forEach(function (freq, i) {
                            var osc = ctx.createOscillator();
                            var gain = ctx.createGain();
                            osc.type = 'triangle';
                            osc.frequency.value = freq;
                            var t = ctx.currentTime + i * 0.28;
                            gain.gain.setValueAtTime(0.0001, t);
                            gain.gain.exponentialRampToValueAtTime(0.9, t + 0.02);
                            gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.26);
                            osc.connect(gain).connect(ctx.destination);
                            osc.start(t);
                            osc.stop(t + 0.3);
                        });
                    } catch (e) {}
                }
            };
        }
    </script>

    {{-- Global Command Palette (Cmd/Ctrl + K) --}}
    <div x-show="commandPaletteOpen"
         x-cloak
         class="fixed inset-0 z-[120] flex items-start justify-center pt-16 px-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <!-- Backdrop -->
        <div @click="commandPaletteOpen = false" class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs"></div>

        <!-- Palette Modal Box -->
        <div x-data="{
                search: '',
                items: [
                    { title: 'Dashboard', category: 'Navigation', url: '{{ route('admin.dashboard') }}', icon: 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25' },
                    { title: 'New Work Order', category: 'Actions', url: '{{ route('admin.work-orders.create') }}', icon: 'M12 4v16m8-8H4' },
                    { title: 'All Work Orders', category: 'Operations', url: '{{ route('admin.work-orders.index') }}', icon: 'M9.75 9.75h4.5m-4.5 3h4.5m-4.5 3h4.5m-5.625 3.75h6.75a4.5 4.5 0 004.5-4.5v-3a4.5 4.5 0 00-4.5-4.5H16.5a3 3 0 00-3-3h-3a3 3 0 00-3 3H7.125a4.5 4.5 0 00-4.5 4.5v3a4.5 4.5 0 004.5 4.5h6.75M12 3h.008v.008H12V3z' },
                    { title: 'New Invoice', category: 'Actions', url: '{{ route('admin.invoices.create') }}', icon: 'M12 4v16m8-8H4' },
                    { title: 'Invoices List', category: 'Finance', url: '{{ route('admin.invoices.index') }}', icon: 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75z' },
                    { title: 'GST & Tax Reports (GSTR-1)', category: 'Finance', url: '{{ route('admin.reports.gst') }}', icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
                    { title: 'Customers Directory', category: 'CRM', url: '{{ route('admin.customers.index') }}', icon: 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z' },
                    { title: 'Leads & Enquiries', category: 'CRM', url: '{{ route('admin.leads.index') }}', icon: 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75' },
                    { title: 'Products & Inventory', category: 'Inventory', url: '{{ route('admin.products.index') }}', icon: 'M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9' },
                    { title: 'Batches & Expiry Tracking', category: 'Inventory', url: '{{ route('admin.product-batches.index') }}', icon: 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z' },
                    { title: 'Stock Movement (Stock In/Out)', category: 'Inventory', url: '{{ route('admin.stock-movements.create') }}', icon: 'M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5' },
                    { title: 'Suppliers Management', category: 'Inventory', url: '{{ route('admin.suppliers.index') }}', icon: 'M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.948c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h2.25' },
                    { title: 'General & Weather Settings', category: 'Settings', url: '{{ route('admin.settings.index') }}', icon: 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z' },
                    { title: 'Staff Accounts & Roles', category: 'Settings', url: '{{ route('admin.staff.index') }}', icon: 'M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z' }
                ],
                get filtered() {
                    if (!this.search.trim()) return this.items;
                    const q = this.search.toLowerCase();
                    return this.items.filter(i => i.title.toLowerCase().includes(q) || i.category.toLowerCase().includes(q));
                }
             }"
             x-trap="commandPaletteOpen"
             class="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 -translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">

            <!-- Search Input Header -->
            <div class="relative flex items-center px-5 py-4 border-b border-gray-100">
                <svg class="w-5 h-5 text-gray-400 mr-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
                <input x-ref="paletteInput"
                       x-model="search"
                       type="text"
                       placeholder="Jump to a page or action..."
                       class="w-full text-base font-medium text-gray-900 placeholder-gray-400 bg-transparent border-none focus:outline-none focus:ring-0">
                <button @click="commandPaletteOpen = false" class="text-xs px-2 py-1 font-mono text-gray-400 hover:text-gray-600 bg-gray-100 rounded-lg">ESC</button>
            </div>

            <!-- List Results -->
            <div class="max-h-80 overflow-y-auto p-2 divide-y divide-gray-50">
                <template x-for="item in filtered" :key="item.url">
                    <a :href="item.url"
                       class="flex items-center justify-between px-4 py-2.5 rounded-2xl hover:bg-emerald-50/80 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-gray-100 group-hover:bg-emerald-100 text-gray-500 group-hover:text-emerald-700 flex items-center justify-center transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-800 group-hover:text-emerald-900" x-text="item.title"></span>
                        </div>
                        <span class="text-[11px] font-medium text-gray-400 group-hover:text-emerald-600 px-2 py-0.5 rounded-md bg-gray-50 group-hover:bg-emerald-100/50" x-text="item.category"></span>
                    </a>
                </template>

                <div x-show="filtered.length === 0" class="py-10 text-center text-sm text-gray-400">
                    No results found for "<span x-text="search"></span>"
                </div>
            </div>

            <!-- Footer Hint -->
            <div class="px-5 py-2.5 bg-gray-50/80 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-400">
                <span>Navigation &amp; Quick Command Palette</span>
                <span class="font-mono">Press ESC to exit</span>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
