<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Restricted — {{ config('app.name', 'Plant Tech Agro') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex items-center justify-center p-4 antialiased">
    <div class="max-w-md w-full bg-white rounded-3xl p-8 sm:p-10 shadow-xl border border-slate-100 text-center relative overflow-hidden">
        {{-- Decorative background glow --}}
        <div class="absolute -top-24 -left-24 w-48 h-48 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>

        {{-- Icon --}}
        <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-amber-50 border border-amber-200/80 flex items-center justify-center shadow-xs">
            <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>

        {{-- Badge --}}
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 mb-3 tracking-wide uppercase">
            Error 403 • Unauthorized
        </span>

        <h1 class="text-2xl font-bold text-slate-900 tracking-tight mb-2">
            Access Restricted
        </h1>

        <p class="text-sm text-slate-500 mb-6 leading-relaxed">
            {{ $exception?->getMessage() ?: 'Your staff account does not have sufficient role permissions to access this module.' }}
        </p>

        @auth('admin')
            <div class="bg-slate-50 border border-slate-200/60 rounded-2xl p-4 mb-6 text-left">
                <div class="text-xs text-slate-400 uppercase font-bold tracking-wider mb-1">Logged In As</div>
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-semibold text-slate-800">{{ auth('admin')->user()->name }}</div>
                        <div class="text-xs text-slate-500">{{ auth('admin')->user()->email }}</div>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-200/70 text-slate-700">
                        {{ auth('admin')->user()->roles->first()?->name ?? auth('admin')->user()->role ?? 'Staff' }}
                    </span>
                </div>
            </div>
        @endauth

        <div class="flex flex-col sm:flex-row items-center gap-3">
            <button onclick="window.history.back()" class="w-full sm:w-1/2 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition cursor-pointer">
                Go Back
            </button>
            @php
                $user = auth('admin')->user();
                $destUrl = ($user && $user->isPosOnly()) ? route('pos.terminal') : route('admin.dashboard');
                $destLabel = ($user && $user->isPosOnly()) ? 'Open POS' : 'Dashboard';
            @endphp
            <a href="{{ $destUrl }}" class="w-full sm:w-1/2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow-sm transition inline-flex items-center justify-center">
                {{ $destLabel }}
            </a>
        </div>
    </div>
</body>
</html>
