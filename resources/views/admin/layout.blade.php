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
            'light' => 'bg-brand-50 text-brand-700 font-semibold shadow-xs',
            'brand' => 'bg-brand-600/80 text-white font-semibold shadow-xs',
            default => 'bg-emerald-600 text-white font-semibold shadow-sm',
        };
        $sidebarActiveChildBg = match($sidebarStyle) {
            'light' => 'bg-brand-50 text-brand-700 font-semibold',
            'brand' => 'bg-brand-600/40 text-white font-semibold',
            default => 'bg-emerald-500/15 text-emerald-400 font-semibold',
        };
        $sidebarGroupActive = match($sidebarStyle) {
            'light' => 'text-brand-700 font-semibold bg-brand-50/50',
            'brand' => 'text-white font-semibold bg-brand-600/30',
            default => 'text-white font-semibold bg-white/5',
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
        $sidebarHeadingText = match($sidebarStyle) {
            'light' => 'text-gray-400',
            'brand' => 'text-brand-300',
            default => 'text-gray-500',
        };
        $topbarBg = $sidebarStyle === 'light' ? 'bg-gray-50' : 'bg-white';
    @endphp
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div x-data="{ sidebarOpen: false }"
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
            <div class="flex items-center justify-between h-20 px-6 {{ $sidebarBg }} border-b {{ $sidebarBorder }}">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 py-1">
                    @if(!empty($theme['logo_url']))
                        <img src="{{ $theme['logo_url'] }}" alt="{{ $theme['site_name'] }}" class="h-12 max-h-14 w-auto max-w-[180px] object-contain rounded-lg">
                    @else
                        <span class="text-2xl font-bold text-brand-400">{{ $theme['brand_first'] }}</span>
                        @if($theme['brand_rest'])
                            <span class="text-2xl font-light {{ $sidebarStyle === 'light' ? 'text-gray-900' : 'text-white' }}">{{ $theme['brand_rest'] }}</span>
                        @endif
                    @endif
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden {{ $sidebarChildText }} hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5">
                @php
                    $openTicketsCount = 0;
                    try {
                        if (class_exists(\App\Models\Ticket::class) && \Illuminate\Support\Facades\Schema::hasTable('tickets')) {
                            $openTicketsCount = \App\Models\Ticket::where('status', 'open')->count();
                        }
                    } catch (\Throwable $e) {
                        $openTicketsCount = 0;
                    }

                    $groups = [
                        // Core Direct Navigation
                        [
                            'label' => null,
                            'items' => [
                                [
                                    'route' => 'admin.dashboard',
                                    'label' => 'Dashboard',
                                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>',
                                ],
                                [
                                    'route' => 'admin.tickets.index',
                                    'label' => 'Support Tickets',
                                    'badge' => $openTicketsCount > 0 ? $openTicketsCount . ' open' : null,
                                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>',
                                ],
                            ],
                        ],

                        // Farmers & CRM
                        [
                            'label' => 'Farmers & CRM',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>',
                            'items' => [
                                ['route' => 'admin.customers.index', 'label' => 'Farmers Directory'],
                                ['route' => 'admin.orchards.index', 'label' => 'Farmer Orchards'],
                                ['route' => 'admin.leads.index', 'label' => 'Leads & Enquiries'],
                            ],
                        ],

                        // Sales & Operations
                        [
                            'label' => 'Sales & Operations',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>',
                            'items' => [
                                ['route' => 'admin.work-orders.index', 'label' => 'Work Orders (Field Jobs)'],
                                ['route' => 'admin.invoices.index', 'label' => 'Invoices & Billing'],
                                ['route' => 'admin.quotations.index', 'label' => 'Quotations / Estimates'],
                                ['route' => 'admin.reports.gst', 'label' => 'GST & Tax Reports'],
                            ],
                        ],

                        // Retail POS
                        [
                            'label' => 'Point of Sale (POS)',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>',
                            'items' => [
                                ['route' => 'admin.pos.terminal', 'label' => 'POS Terminal'],
                                ['route' => 'admin.pos.sales', 'label' => 'POS Invoices & Sales'],
                            ],
                        ],

                        // Inventory & Warehouse
                        [
                            'label' => 'Inventory & Stock',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>',
                            'items' => [
                                ['route' => 'admin.products.index', 'label' => 'Products Catalogue'],
                                ['route' => 'admin.product-batches.index', 'label' => 'Batches & Expiry'],
                                ['route' => 'admin.stock-movements.index', 'label' => 'Stock Movements (In/Out)'],
                                ['route' => 'admin.suppliers.index', 'label' => 'Suppliers & Vendors'],
                            ],
                        ],

                        // Website & CMS
                        [
                            'label' => 'CMS & Content',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>',
                            'items' => [
                                ['route' => 'admin.posts.index', 'label' => 'Blog & Advisory Posts'],
                                ['route' => 'admin.varieties.index', 'label' => 'Crop & Fruit Varieties'],
                                ['route' => 'admin.services.index', 'label' => 'Agro Services'],
                                ['route' => 'admin.projects.index', 'label' => 'Field Projects'],
                                ['route' => 'admin.partners.index', 'label' => 'Brand Partners'],
                                ['route' => 'admin.testimonials.index', 'label' => 'Farmer Testimonials'],
                                ['route' => 'admin.gallery.index', 'label' => 'Photo Gallery'],
                                ['route' => 'admin.faqs.index', 'label' => 'FAQs'],
                                ['route' => 'admin.frontend.index', 'label' => 'Frontend Editor'],
                            ],
                        ],

                        // System Administration
                        [
                            'label' => 'Settings & System',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
                            'items' => [
                                ['route' => 'admin.staff.index', 'label' => 'Staff Accounts'],
                                ['route' => 'admin.roles.index', 'label' => 'Roles & Permissions'],
                                ['route' => 'admin.mobile-apps.index', 'label' => 'Mobile Apps Manager'],
                                ['route' => 'admin.automation.index', 'label' => 'Automation Rules'],
                                ['route' => 'admin.system.diagnostics', 'label' => 'System Health & Performance'],
                                ['route' => 'admin.settings.index', 'label' => 'General Settings'],
                            ],
                        ],
                    ];
                @endphp

                @php
                    $currentAdmin = Auth::guard('admin')->user();
                    $superOnlyRoutes = ['admin.roles.index', 'admin.staff.index', 'admin.automation.index', 'admin.settings.index', 'admin.mobile-apps.index'];
                    $isPosOnlyUser = $currentAdmin?->isPosOnly();
                    $posAndStockAllowed = [
                        'admin.dashboard',
                        'admin.pos.terminal',
                        'admin.pos.sales',
                        'admin.products.index',
                        'admin.product-batches.index',
                        'admin.stock-movements.index',
                        'admin.suppliers.index',
                    ];

                    $routePermissionMap = [
                        'admin.dashboard' => null,
                        'admin.pos.terminal' => 'pos.terminal',
                        'admin.pos.sales' => 'pos.sales.view',
                        'admin.work-orders.index' => 'work-orders.view',
                        'admin.tickets.index' => 'tickets.view',
                        'admin.orchards.index' => 'orchards.view',
                        'admin.leads.index' => 'leads.view',
                        'admin.quotations.index' => 'quotations.view',
                        'admin.customers.index' => 'customers.view',
                        'admin.invoices.index' => 'invoices.view',
                        'admin.products.index' => 'inventory.view',
                        'admin.product-batches.index' => 'inventory.batches',
                        'admin.stock-movements.index' => ['inventory.stock-in', 'inventory.stock-out', 'inventory.view'],
                        'admin.suppliers.index' => 'suppliers.manage',
                        'admin.posts.index' => 'content.manage',
                        'admin.varieties.index' => 'content.manage',
                        'admin.partners.index' => 'content.manage',
                        'admin.services.index' => 'services.view',
                        'admin.projects.index' => 'content.manage',
                        'admin.testimonials.index' => 'content.manage',
                        'admin.gallery.index' => 'content.manage',
                        'admin.faqs.index' => 'content.manage',
                        'admin.frontend.index' => 'frontend.editor',
                        'admin.reports.gst' => 'reports.gst',
                        'admin.roles.index' => 'roles.manage',
                        'admin.staff.index' => ['staff.view', 'staff.manage'],
                        'admin.mobile-apps.index' => 'automation.manage',
                        'admin.automation.index' => 'automation.manage',
                        'admin.settings.index' => 'settings.manage',
                        'admin.system.diagnostics' => null,
                    ];

                    $canView = function ($route) use ($currentAdmin, $superOnlyRoutes, $isPosOnlyUser, $posAndStockAllowed, $routePermissionMap) {
                        if (!$currentAdmin) return false;
                        if (!Route::has($route)) return false;

                        // Super Admin has master access to everything
                        if ($currentAdmin->hasRole('Super Admin')) {
                            return true;
                        }

                        if ($isPosOnlyUser && !in_array($route, $posAndStockAllowed, true)) {
                            return false;
                        }

                        if (array_key_exists($route, $routePermissionMap)) {
                            $required = $routePermissionMap[$route];
                            if ($required === null) {
                                return true;
                            }
                            try {
                                if (is_array($required)) {
                                    foreach ($required as $perm) {
                                        if ($currentAdmin->hasPermissionTo($perm, 'admin')) {
                                            return true;
                                        }
                                    }
                                    return false;
                                }
                                return $currentAdmin->hasPermissionTo($required, 'admin');
                            } catch (\Throwable $e) {
                                return false;
                            }
                        }

                        if (in_array($route, $superOnlyRoutes, true)) {
                            return false;
                        }

                        return true;
                    };
                @endphp

                @foreach ($groups as $group)
                    @if($group['label'] === null)
                        @php
                            $visibleDirectItems = collect($group['items'])->filter(fn($i) => Route::has($i['route']) && $canView($i['route']));
                        @endphp
                        @if($visibleDirectItems->isNotEmpty())
                            <div class="space-y-1">
                                @foreach($visibleDirectItems as $item)
                                    @php
                                        $isDirectActive = Route::currentRouteNamed($item['route']) || (str_ends_with($item['route'], '.index') && Route::is(substr($item['route'], 0, -6) . '.*'));
                                    @endphp
                                    <a href="{{ route($item['route']) }}"
                                       class="relative flex items-center justify-between px-3.5 py-2.5 text-sm font-medium rounded-xl transition-all duration-150 {{ $isDirectActive ? $sidebarActiveBg : $sidebarText . ' ' . $sidebarTextHover }}">
                                        <span class="flex items-center gap-3">
                                            <svg class="w-5 h-5 flex-shrink-0 {{ $isDirectActive ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                {!! $item['icon'] !!}
                                            </svg>
                                            <span>{{ $item['label'] }}</span>
                                        </span>
                                        @if(!empty($item['badge']))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap {{ $isDirectActive ? 'bg-white/20 text-white font-bold' : ($item['badgeClass'] ?? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30') }}">
                                                {{ $item['badge'] }}
                                            </span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                            <div class="my-2.5 border-t {{ $sidebarBorder }} opacity-80 mx-1"></div>
                        @endif
                    @else
                        @php
                            $isItemActive = fn($r) => Route::currentRouteNamed($r) || (str_ends_with($r, '.index') && Route::is(substr($r, 0, -6) . '.*'));
                            $hasActive = collect($group['items'])->contains(fn($i) => Route::has($i['route']) && $canView($i['route']) && $isItemActive($i['route']));
                            $visibleRoutes = collect($group['items'])->filter(fn($i) => Route::has($i['route']) && $canView($i['route']));
                        @endphp

                        @if($visibleRoutes->isNotEmpty())
                            <div x-data="{ open: {{ $hasActive ? 'true' : 'false' }} }" class="mt-0.5">
                                <button @click="open = !open"
                                        class="w-full flex items-center justify-between px-3.5 py-2.5 text-sm font-medium rounded-xl transition-all duration-150 {{ $hasActive ? $sidebarGroupActive : $sidebarText . ' ' . $sidebarTextHover }}">
                                    <span class="flex items-center gap-3">
                                        <svg class="w-5 h-5 flex-shrink-0 {{ $hasActive ? 'text-emerald-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            {!! $group['icon'] !!}
                                        </svg>
                                        <span>{{ $group['label'] }}</span>
                                    </span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-90 text-white' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>

                                <div x-show="open" x-collapse x-cloak class="ml-4 mt-1 space-y-0.5 border-l {{ $sidebarChildBorder }} pl-3.5">
                                    @foreach($group['items'] as $item)
                                        @if(Route::has($item['route']) && $canView($item['route']))
                                            <a href="{{ route($item['route']) }}"
                                               class="flex items-center justify-between px-3 py-2 text-xs font-medium rounded-lg transition-all duration-150 {{ $isItemActive($item['route']) ? $sidebarActiveChildBg : $sidebarChildText . ' ' . $sidebarTextHover }}">
                                                <span>{{ $item['label'] }}</span>
                                                @if(!empty($item['badge']))
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold whitespace-nowrap {{ $item['badgeClass'] ?? 'bg-emerald-500/20 text-emerald-400' }}">
                                                        {{ $item['badge'] }}
                                                    </span>
                                                @endif
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
            <header class="{{ $topbarBg }} shadow-sm border-b border-gray-200 h-20 flex items-center justify-between px-4 lg:px-6 flex-shrink-0">
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
                    <button @click="window.dispatchEvent(new CustomEvent('open-command-palette'))"
                            type="button"
                            class="sm:hidden p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition cursor-pointer"
                            title="Search pages or actions (Ctrl+K)">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                    </button>

                    <button @click="window.dispatchEvent(new CustomEvent('open-command-palette'))"
                            type="button"
                            class="hidden sm:flex items-center gap-2.5 px-3 py-1.5 text-xs text-gray-400 bg-gray-50 hover:bg-gray-100 hover:text-gray-700 border border-gray-200 rounded-xl transition shadow-xs cursor-pointer">
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
                                <a href="{{ route('admin.tickets.create') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition">
                                    <span class="w-6 h-6 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-xs font-bold">TKT</span>
                                    New Support Ticket
                                </a>
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
                    {{-- Page Speed & System Health Indicator --}}
                    @php $pageLoadMs = defined('LARAVEL_START') ? round((microtime(true) - LARAVEL_START) * 1000) : 15; @endphp
                    <div x-data="{ loadMs: '{{ $pageLoadMs }}' }"
                         x-init="window.addEventListener('load', () => { const nav = performance.getEntriesByType('navigation')[0]; if (nav && nav.duration) loadMs = Math.round(nav.duration); })">
                        <a href="{{ route('admin.system.diagnostics') }}"
                           class="hidden md:flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 border border-gray-200/90 hover:border-brand-300 rounded-xl shadow-2xs transition active:scale-95 group focus:outline-none focus:ring-2 focus:ring-brand-500/20 cursor-pointer"
                           title="Click to view full System Diagnostics, site load, and server health page">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span class="text-gray-400 font-normal">Load</span>
                            <span class="font-bold text-gray-800 group-hover:text-brand-600 transition" x-text="loadMs + 'ms'"></span>
                        </a>
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
                             class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl z-50 border border-gray-100 overflow-hidden">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 bg-gray-50/50">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-gray-900">Notifications</p>
                                    @if($unreadNotifications->isNotEmpty())
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-brand-50 text-brand-700 border border-brand-200">
                                            {{ $unreadNotifications->count() }} new
                                        </span>
                                    @endif
                                </div>
                                @if($unreadNotifications->isNotEmpty())
                                    <form action="{{ route('admin.notifications.read-all') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs text-brand-600 hover:text-brand-700 font-medium">Mark all read</button>
                                    </form>
                                @endif
                            </div>
                            <div class="max-h-96 overflow-y-auto divide-y divide-gray-50">
                                @forelse($unreadNotifications->take(10) as $notification)
                                    @php
                                        $data = $notification->data;
                                        $isTicket = isset($data['ticket_id']) || (isset($data['type']) && str_starts_with($data['type'], 'ticket'));
                                        $isWorkOrder = isset($data['work_order_id']) || (isset($data['number']) && !isset($data['ticket_number']));
                                        $isLead = isset($data['lead_id']);
                                        $isStock = isset($data['product_name']);
                                        $url = route('admin.notifications.go', $notification->id);
                                    @endphp
                                    <div class="group relative flex items-start gap-3 px-4 py-3 hover:bg-gray-50/80 transition">
                                        {{-- Type Icon --}}
                                        <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5
                                            @if($isTicket) bg-emerald-50 text-emerald-600
                                            @elseif($isWorkOrder) bg-indigo-50 text-indigo-600
                                            @elseif($isLead) bg-sky-50 text-sky-600
                                            @elseif($isStock) bg-amber-50 text-amber-600
                                            @else bg-gray-100 text-gray-600
                                            @endif">
                                            @if($isTicket)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                            @elseif($isWorkOrder)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            @elseif($isLead)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            @elseif($isStock)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @endif
                                        </div>

                                        {{-- Content --}}
                                        <div class="flex-1 min-w-0">
                                            <a href="{{ $url }}" class="block focus:outline-none">
                                                <div class="flex items-center justify-between gap-1">
                                                    <p class="text-xs font-semibold text-gray-900 group-hover:text-brand-700 transition truncate">
                                                        {{ $data['title'] ?? ($data['subject'] ?? 'Notification') }}
                                                    </p>
                                                    <span class="text-[10px] text-gray-400 whitespace-nowrap">{{ $notification->created_at->diffForHumans(null, true, true) }}</span>
                                                </div>
                                                <div class="text-xs text-gray-500 mt-0.5 line-clamp-2">
                                                    @if($isTicket)
                                                        <span class="font-medium text-gray-700">{{ $data['farmer_name'] ?? 'Farmer' }}</span>
                                                        @if(!empty($data['subject']))
                                                            — {{ $data['subject'] }}
                                                        @elseif(!empty($data['snippet']))
                                                            — {{ $data['snippet'] }}
                                                        @endif
                                                        @if(!empty($data['priority']) && in_array(strtolower($data['priority']), ['urgent', 'high']))
                                                            <span class="ml-1 inline-flex items-center px-1.5 py-0.2 text-[9px] font-bold rounded bg-red-100 text-red-700 uppercase tracking-wider">{{ $data['priority'] }}</span>
                                                        @endif
                                                    @elseif($isStock)
                                                        {{ $data['product_name'] }} — stock {{ \App\Support\Format::qty($data['stock_qty'] ?? 0) }} {{ $data['unit'] ?? '' }}
                                                        @if(isset($data['threshold'])) (threshold {{ \App\Support\Format::qty($data['threshold']) }})@endif
                                                    @elseif($isWorkOrder)
                                                        {{ $data['number'] ?? '' }} — {{ $data['customer'] ?? '' }} · {{ $data['service'] ?? '' }}
                                                    @elseif($isLead)
                                                        {{ $data['name'] ?? '' }} ({{ $data['phone'] ?? '' }}) · {{ $data['service'] ?? '' }}
                                                    @else
                                                        {{ $data['message'] ?? ($data['body'] ?? ($data['description'] ?? 'View notification details')) }}
                                                    @endif
                                                </div>
                                            </a>
                                        </div>

                                        {{-- Quick Mark as Read --}}
                                        <form action="{{ route('admin.notifications.read', $notification->id) }}" method="POST" class="flex-shrink-0" @submit.prevent="
                                            fetch('{{ route('admin.notifications.read', $notification->id) }}', {
                                                method: 'POST',
                                                headers: {
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                    'Accept': 'application/json'
                                                }
                                            }).then(() => { $el.closest('.group').remove(); });
                                        ">
                                            @csrf
                                            <button type="submit" title="Mark as read" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-gray-600 p-1 rounded transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                @empty
                                    <div class="px-4 py-8 text-center text-sm text-gray-400">No unread notifications</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center space-x-2 text-sm text-gray-600 hover:text-gray-900 focus:outline-none">
                            <x-admin.avatar :name="Auth::guard('admin')->user()->name ?? 'Admin'" size="sm" />
                            <span class="hidden md:inline font-medium text-gray-800">{{ Auth::guard('admin')->user()->name ?? 'Admin' }}</span>
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
                    <p class="text-xs text-gray-400">Version <span class="font-medium text-gray-600">{{ config('version.number', '1.0') }}</span></p>
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

        function adminCommandPalette() {
            return {
                isOpen: false,
                search: '',
                selectedIndex: 0,
                items: [
                    { title: 'Dashboard', category: 'Navigation', url: '{{ route('admin.dashboard') }}', icon: 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25' },
                    { title: 'Support Tickets & Farmer Queries', category: 'Support & Advisory', url: '{{ route('admin.tickets.index') }}', icon: 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z' },
                    { title: 'New Support Ticket', category: 'Actions', url: '{{ route('admin.tickets.create') }}', icon: 'M12 4v16m8-8H4' },
                    { title: 'POS Terminal (Billing & Counter)', category: 'Point of Sale', url: '{{ route('admin.pos.terminal') }}', icon: 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z' },
                    { title: 'POS Sales & Invoices', category: 'Point of Sale', url: '{{ route('admin.pos.sales') }}', icon: 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75z' },
                    { title: 'New Work Order', category: 'Actions', url: '{{ route('admin.work-orders.create') }}', icon: 'M12 4v16m8-8H4' },
                    { title: 'All Work Orders', category: 'Operations', url: '{{ route('admin.work-orders.index') }}', icon: 'M9.75 9.75h4.5m-4.5 3h4.5m-4.5 3h4.5m-5.625 3.75h6.75a4.5 4.5 0 004.5-4.5v-3a4.5 4.5 0 00-4.5-4.5H16.5a3 3 0 00-3-3h-3a3 3 0 00-3 3H7.125a4.5 4.5 0 00-4.5 4.5v3a4.5 4.5 0 004.5 4.5h6.75M12 3h.008v.008H12V3z' },
                    { title: 'New Invoice', category: 'Actions', url: '{{ route('admin.invoices.create') }}', icon: 'M12 4v16m8-8H4' },
                    { title: 'Invoices List', category: 'Finance', url: '{{ route('admin.invoices.index') }}', icon: 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75z' },
                    { title: 'GST & Tax Reports (GSTR-1)', category: 'Finance', url: '{{ route('admin.reports.gst') }}', icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
                    { title: 'Customers Directory', category: 'CRM', url: '{{ route('admin.customers.index') }}', icon: 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z' },
                    { title: 'Farmer Orchards', category: 'Operations', url: '{{ route('admin.orchards.index') }}', icon: 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z' },
                    { title: 'Leads & Enquiries', category: 'CRM', url: '{{ route('admin.leads.index') }}', icon: 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75' },
                    { title: 'Products & Inventory', category: 'Inventory', url: '{{ route('admin.products.index') }}', icon: 'M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9' },
                    { title: 'Batches & Expiry Tracking', category: 'Inventory', url: '{{ route('admin.product-batches.index') }}', icon: 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z' },
                    { title: 'Stock Movement (Stock In/Out)', category: 'Inventory', url: '{{ route('admin.stock-movements.create') }}', icon: 'M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5' },
                    { title: 'Suppliers Management', category: 'Inventory', url: '{{ route('admin.suppliers.index') }}', icon: 'M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.948c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h2.25' },
                    { title: 'System Health & Performance Diagnostics', category: 'Diagnostics', url: '{{ route('admin.system.diagnostics') }}', icon: 'M13 10V3L4 14h7v7l9-11h-7z' },
                    { title: 'General & Weather Settings', category: 'Settings', url: '{{ route('admin.settings.index') }}', icon: 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z' },
                    { title: 'Staff Accounts & Roles', category: 'Settings', url: '{{ route('admin.staff.index') }}', icon: 'M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z' }
                ],
                init() {
                    this.$watch('search', () => {
                        this.selectedIndex = 0;
                    });
                },
                get filtered() {
                    if (!this.search || !this.search.trim()) return this.items;
                    var q = this.search.trim().toLowerCase();
                    return this.items.filter(function(i) {
                        return i.title.toLowerCase().includes(q) || i.category.toLowerCase().includes(q);
                    });
                },
                open() {
                    var self = this;
                    self.isOpen = true;
                    self.search = '';
                    self.selectedIndex = 0;
                    self.$nextTick(function() {
                        if (self.$refs.paletteInput) {
                            self.$refs.paletteInput.focus();
                        }
                    });
                },
                close() {
                    this.isOpen = false;
                },
                toggle() {
                    if (this.isOpen) {
                        this.close();
                    } else {
                        this.open();
                    }
                },
                selectNext() {
                    if (this.filtered.length === 0) return;
                    this.selectedIndex = (this.selectedIndex + 1) % this.filtered.length;
                    this.scrollSelectedIntoView();
                },
                selectPrev() {
                    if (this.filtered.length === 0) return;
                    this.selectedIndex = (this.selectedIndex - 1 + this.filtered.length) % this.filtered.length;
                    this.scrollSelectedIntoView();
                },
                scrollSelectedIntoView() {
                    var self = this;
                    self.$nextTick(function() {
                        var container = self.$refs.resultsList;
                        var active = container ? container.querySelector('[data-selected="true"]') : null;
                        if (active && container) {
                            var aTop = active.offsetTop;
                            var aBottom = aTop + active.offsetHeight;
                            if (aTop < container.scrollTop) {
                                container.scrollTop = aTop;
                            } else if (aBottom > container.scrollTop + container.clientHeight) {
                                container.scrollTop = aBottom - container.clientHeight;
                            }
                        }
                    });
                },
                chooseSelected() {
                    if (this.filtered.length > 0 && this.filtered[this.selectedIndex]) {
                        window.location.href = this.filtered[this.selectedIndex].url;
                    }
                }
            };
        }
    </script>

    {{-- Global Command Palette (Cmd/Ctrl + K) --}}
    <div x-data="adminCommandPalette()"
         x-init="init()"
         x-cloak
         x-show="isOpen"
         @open-command-palette.window="open()"
         @keydown.window.prevent.cmd.k="toggle()"
         @keydown.window.prevent.ctrl.k="toggle()"
         @keydown.window.escape="close()"
         class="fixed inset-0 z-[120] flex items-start justify-center pt-16 px-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <!-- Backdrop -->
        <div @click="close()" class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs"></div>

        <!-- Palette Modal Box -->
        <div @click.stop
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
                       @keydown.down.prevent="selectNext()"
                       @keydown.up.prevent="selectPrev()"
                       @keydown.enter.prevent="chooseSelected()"
                       type="text"
                       placeholder="Jump to a page, ticket, invoice, or action..."
                       class="w-full text-base font-medium text-gray-900 placeholder-gray-400 bg-transparent border-none focus:outline-none focus:ring-0">
                <button @click="close()" type="button" class="text-xs px-2.5 py-1 font-mono text-gray-400 hover:text-gray-700 bg-gray-100 rounded-lg transition cursor-pointer">ESC</button>
            </div>

            <!-- List Results -->
            <div class="max-h-80 overflow-y-auto p-2 divide-y divide-gray-50" x-ref="resultsList">
                <template x-for="(item, index) in filtered" :key="item.url">
                    <a :href="item.url"
                       @mouseenter="selectedIndex = index"
                       :data-selected="selectedIndex === index ? 'true' : 'false'"
                       :class="selectedIndex === index ? 'bg-emerald-50 text-emerald-950 ring-1 ring-emerald-200/60' : 'hover:bg-gray-50 text-gray-800'"
                       class="flex items-center justify-between px-4 py-2.5 rounded-2xl transition group cursor-pointer">
                        <div class="flex items-center gap-3">
                            <div :class="selectedIndex === index ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 group-hover:bg-emerald-100 group-hover:text-emerald-700'"
                                 class="w-8 h-8 rounded-xl flex items-center justify-center transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium" :class="selectedIndex === index ? 'text-emerald-900 font-semibold' : 'text-gray-800'" x-text="item.title"></span>
                        </div>
                        <span class="text-[11px] font-medium px-2 py-0.5 rounded-md"
                              :class="selectedIndex === index ? 'text-emerald-700 bg-emerald-100' : 'text-gray-400 bg-gray-50 group-hover:bg-emerald-100/50 group-hover:text-emerald-700'"
                              x-text="item.category"></span>
                    </a>
                </template>

                <div x-show="filtered.length === 0" class="py-10 text-center text-sm text-gray-400">
                    <p class="font-medium">No results found for "<span class="text-gray-700" x-text="search"></span>"</p>
                    <p class="text-xs text-gray-400 mt-1">Try searching for tickets, pos, orders, customers, or diagnostics</p>
                </div>
            </div>

            <!-- Footer Hint -->
            <div class="px-5 py-2.5 bg-gray-50/80 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-400">
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1"><kbd class="px-1 py-0.5 bg-white border border-gray-200 rounded font-mono text-[10px]">↑</kbd><kbd class="px-1 py-0.5 bg-white border border-gray-200 rounded font-mono text-[10px]">↓</kbd> to navigate</span>
                    <span class="flex items-center gap-1"><kbd class="px-1 py-0.5 bg-white border border-gray-200 rounded font-mono text-[10px]">↵</kbd> to select</span>
                </div>
                <span class="font-mono">Press ESC to exit</span>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
