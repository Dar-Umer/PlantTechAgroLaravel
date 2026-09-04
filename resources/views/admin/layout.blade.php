<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $theme['site_name'] }} - Admin</title>
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
    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">

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
                                ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>'],
                            ],
                        ],
                        [
                            'label' => 'Content',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>',
                            'items' => [
                                ['route' => 'admin.posts.index', 'label' => 'Posts'],
                                ['route' => 'admin.testimonials.index', 'label' => 'Testimonials'],
                                ['route' => 'admin.faqs.index', 'label' => 'FAQs'],
                                ['route' => 'admin.gallery.index', 'label' => 'Gallery'],
                            ],
                        ],
                        [
                            'label' => 'Services & Projects',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
                            'items' => [
                                ['route' => 'admin.services.index', 'label' => 'Services'],
                                ['route' => 'admin.projects.index', 'label' => 'Projects'],
                            ],
                        ],
                        [
                            'label' => 'Website',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>',
                            'items' => [
                                ['route' => 'admin.frontend.index', 'label' => 'Frontend'],
                            ],
                        ],
                        [
                            'label' => 'Sales',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>',
                            'items' => [
                                ['route' => 'admin.leads.index', 'label' => 'Leads'],
                                ['route' => 'admin.customers.index', 'label' => 'Customers'],
                                ['route' => 'admin.invoices.index', 'label' => 'Invoices'],
                            ],
                        ],
                        [
                            'label' => 'Operations',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>',
                            'items' => [
                                ['route' => 'admin.work-orders.index', 'label' => 'Work Orders'],
                            ],
                        ],
                        [
                            'label' => 'Inventory',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
                            'items' => [
                                ['route' => 'admin.products.index', 'label' => 'Products'],
                                ['route' => 'admin.suppliers.index', 'label' => 'Suppliers'],
                                ['route' => 'admin.stock-movements.index', 'label' => 'Stock Movements'],
                            ],
                        ],
                        [
                            'label' => 'Administration',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
                            'items' => [
                                ['route' => 'admin.staff.index', 'label' => 'Staff'],
                                ['route' => 'admin.settings.index', 'label' => 'Settings'],
                            ],
                        ],
                    ];
                @endphp

                @php
                    // Super Admin bypasses all permission checks.
                    $currentAdmin = Auth::guard('admin')->user();
                    $canView = function ($route) use ($currentAdmin) {
                        if (!$currentAdmin) return false;
                        return Route::has($route);
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
                <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <h1 class="text-lg font-semibold text-gray-800 hidden lg:block">
                    @yield('page-title', 'Admin Panel')
                </h1>

                <div class="flex items-center space-x-4">
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
                                                {{ $data['product_name'] }} — stock {{ $data['stock_qty'] }} {{ $data['unit'] }} (threshold {{ $data['threshold'] }})
                                                @if(isset($data['supplier'])) · Supplier: {{ $data['supplier'] }}@endif
                                            @elseif(isset($data['number']))
                                                {{ $data['number'] }} — {{ $data['customer'] }} · {{ $data['service'] }}
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
            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-lg">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-lg">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
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

    @stack('scripts')
</body>
</html>
