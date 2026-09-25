@extends('admin.layout')

@section('title', 'System Health & Performance Diagnostics')
@section('page-title', 'System Health & Performance')

@section('content')
<div x-data="systemDiagnosticsPage()" x-init="init()" class="space-y-6">

    {{-- Top Action & Status Bar --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">System Health &amp; Diagnostics</h2>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100/80 shadow-xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Operational
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-0.5">Real-time performance metrics, database latency, server resources &amp; maintenance tools.</p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <x-admin.button variant="secondary" size="sm" type="button" @click="refreshMetrics()" x-bind:disabled="loading">
                <svg class="w-3.5 h-3.5 mr-1.5" :class="loading ? 'animate-spin text-emerald-600' : 'text-gray-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span>Re-check Metrics</span>
            </x-admin.button>

            <x-admin.button variant="primary" size="sm" type="button" @click="clearViews()" x-bind:disabled="cacheActionLoading">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                <span>Clear Views</span>
            </x-admin.button>

            <x-admin.button variant="secondary" size="sm" type="button" @click="clearAllCache()" x-bind:disabled="cacheActionLoading">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>Flush Cache</span>
            </x-admin.button>
        </div>
    </div>

    {{-- Feedback Message Toast --}}
    <div x-show="toastMessage"
         x-transition
         class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span x-text="toastMessage"></span>
        </div>
        <button type="button" @click="toastMessage = ''" class="text-emerald-600 hover:text-emerald-800 font-bold">&times;</button>
    </div>

    {{-- 4 Core KPI Summary Cards (Matching PTA Dashboard Architecture) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Page Load (Live Client) --}}
        <div class="group bg-white rounded-2xl shadow-xs hover:shadow-md border border-gray-100/90 p-5 transition-all duration-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 group-hover:text-emerald-600 transition-colors">Live Page Load</span>
                <div class="w-9 h-9 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900 tracking-tight">
                <span x-text="perf.loadTime"></span> <span class="text-sm font-normal text-gray-400">ms</span>
            </p>
            <div class="flex items-center gap-1.5 mt-2 text-xs text-gray-500">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>Speed Rating: <strong class="text-emerald-700 font-semibold" x-text="perf.rating"></strong></span>
            </div>
        </div>

        {{-- Database Latency --}}
        <div class="group bg-white rounded-2xl shadow-xs hover:shadow-md border border-gray-100/90 p-5 transition-all duration-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 group-hover:text-brand-600 transition-colors">Database Latency</span>
                <div class="w-9 h-9 bg-brand-50 text-brand-600 rounded-xl flex items-center justify-center transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900 tracking-tight">
                <span x-text="metrics?.database?.latency_ms ?? '{{ $stats['database']['latency_ms'] }}'"></span> <span class="text-sm font-normal text-gray-400">ms</span>
            </p>
            <div class="flex items-center gap-1.5 mt-2 text-xs text-gray-500">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>{{ $stats['database']['tables_count'] }} tables &bull; {{ $stats['database']['size_mb'] }} MB footprint</span>
            </div>
        </div>

        {{-- PHP Peak Memory --}}
        <div class="group bg-white rounded-2xl shadow-xs hover:shadow-md border border-gray-100/90 p-5 transition-all duration-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 group-hover:text-indigo-600 transition-colors">PHP Memory Usage</span>
                <div class="w-9 h-9 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900 tracking-tight">
                {{ round(memory_get_peak_usage(true) / 1024 / 1024, 1) }} <span class="text-sm font-normal text-gray-400">MB</span>
            </p>
            <div class="flex items-center gap-1.5 mt-2 text-xs text-gray-500">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                <span>PHP Limit: <strong class="font-medium text-gray-700">{{ $stats['server']['memory_limit'] }}</strong></span>
            </div>
        </div>

        {{-- Free Disk Space --}}
        <div class="group bg-white rounded-2xl shadow-xs hover:shadow-md border border-gray-100/90 p-5 transition-all duration-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 group-hover:text-teal-600 transition-colors">Storage Volume</span>
                <div class="w-9 h-9 bg-teal-50 text-teal-600 rounded-xl flex items-center justify-center transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900 tracking-tight">
                {{ $stats['storage']['free_gb'] }} <span class="text-sm font-normal text-gray-400">GB free</span>
            </p>
            <div class="flex items-center gap-1.5 mt-2 text-xs text-gray-500">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                <span>{{ $stats['storage']['total_gb'] }} GB Total ({{ $stats['storage']['used_percent'] }}% used)</span>
            </div>
        </div>
    </div>

    {{-- Two Column Detailed Breakdown --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left 2 Columns: Page Lifecycle, Assets & Database Tables --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Page Speed & Waterfall Timeline --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Page Lifecycle Waterfall</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Browser Navigation Timing breakdown for the current request.</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-xl bg-gray-100 text-gray-700">
                        Total: <span x-text="perf.loadTime"></span> ms
                    </span>
                </div>

                {{-- Horizontal Stacked Bar --}}
                <div class="w-full h-3.5 bg-gray-100 rounded-xl overflow-hidden flex shadow-inner">
                    <div class="bg-sky-400 h-full transition-all duration-500"
                         :style="'width: ' + Math.max(3, Math.min(25, (perf.dns / (perf.loadTime || 1)) * 100)) + '%'"
                         :title="'DNS: ' + perf.dns + 'ms'"></div>
                    <div class="bg-indigo-400 h-full transition-all duration-500"
                         :style="'width: ' + Math.max(3, Math.min(25, (perf.tcp / (perf.loadTime || 1)) * 100)) + '%'"
                         :title="'TCP: ' + perf.tcp + 'ms'"></div>
                    <div class="bg-emerald-500 h-full transition-all duration-500"
                         :style="'width: ' + Math.max(10, Math.min(60, (perf.ttfb / (perf.loadTime || 1)) * 100)) + '%'"
                         :title="'Server TTFB: ' + perf.ttfb + 'ms'"></div>
                    <div class="bg-amber-400 h-full transition-all duration-500 flex-1"
                         :title="'DOM & Render: ' + Math.max(0, perf.loadTime - perf.ttfb) + 'ms'"></div>
                </div>

                {{-- Legend & Detailed Metrics --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2 text-xs">
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100/80">
                        <div class="flex items-center gap-1.5 text-gray-500 text-[11px]">
                            <span class="w-2 h-2 rounded-full bg-sky-400"></span>
                            <span>DNS Lookup</span>
                        </div>
                        <p class="text-base font-bold text-gray-800 mt-1"><span x-text="perf.dns"></span> ms</p>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100/80">
                        <div class="flex items-center gap-1.5 text-gray-500 text-[11px]">
                            <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                            <span>TCP Connect</span>
                        </div>
                        <p class="text-base font-bold text-gray-800 mt-1"><span x-text="perf.tcp"></span> ms</p>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100/80">
                        <div class="flex items-center gap-1.5 text-gray-500 text-[11px]">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Server TTFB</span>
                        </div>
                        <p class="text-base font-bold text-emerald-700 mt-1"><span x-text="perf.ttfb"></span> ms</p>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100/80">
                        <div class="flex items-center gap-1.5 text-gray-500 text-[11px]">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            <span>DOM Ready</span>
                        </div>
                        <p class="text-base font-bold text-gray-800 mt-1"><span x-text="perf.domReady"></span> ms</p>
                    </div>
                </div>

                {{-- Transfer Resources --}}
                <div class="pt-3 border-t border-gray-100 flex items-center justify-between flex-wrap gap-2 text-xs">
                    <span class="text-gray-500">Transferred Assets on this Page:</span>
                    <div class="flex items-center gap-4 text-gray-700 font-medium flex-wrap">
                        <span><strong class="text-gray-900" x-text="perf.scriptsCount"></strong> Scripts</span>
                        <span><strong class="text-gray-900" x-text="perf.stylesCount"></strong> Stylesheets</span>
                        <span><strong class="text-gray-900" x-text="perf.imagesCount"></strong> Images</span>
                        <span><strong class="text-gray-900" x-text="perf.fetchCount"></strong> API Calls</span>
                        <span class="text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-100">(<span x-text="perf.transferKb"></span> KB)</span>
                    </div>
                </div>
            </div>

            {{-- Database Tables & Storage Distribution with Search Filter --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Database Tables &amp; Physical Footprint</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Top tables in <strong class="text-gray-700">{{ $stats['database']['name'] }}</strong> ordered by storage size.</p>
                    </div>

                    {{-- Search Input (matching PTA <x-admin.input> styling) --}}
                    <div class="relative w-full sm:w-64">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text"
                               x-model="tableSearch"
                               placeholder="Search table name..."
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 pl-10 pr-8 py-2 text-xs text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        <button type="button"
                                x-show="tableSearch"
                                @click="tableSearch = ''"
                                class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-xl border border-gray-100">
                    <table class="min-w-full divide-y divide-gray-100 text-left text-xs text-gray-600">
                        <thead class="bg-gray-50/75">
                            <tr class="text-gray-500 uppercase font-semibold text-[10px] tracking-wider">
                                <th class="py-3 px-4">Table Name</th>
                                <th class="py-3 px-4 text-right">Estimated Rows</th>
                                <th class="py-3 px-4 text-right">Data + Index Footprint</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 bg-white">
                            @forelse($tables as $table)
                                <tr x-show="!tableSearch.trim() || '{{ strtolower($table->table_name) }}'.includes(tableSearch.trim().toLowerCase())"
                                    class="hover:bg-gray-50/75 transition">
                                    <td class="py-2.5 px-4 font-semibold text-gray-900 font-mono">{{ $table->table_name }}</td>
                                    <td class="py-2.5 px-4 text-right text-gray-600">{{ number_format($table->table_rows) }}</td>
                                    <td class="py-2.5 px-4 text-right text-emerald-700 font-bold font-mono">{{ number_format($table->size_kb, 1) }} KB</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-gray-400">No database tables found</td>
                                </tr>
                            @endforelse

                            <tr x-show="tableSearch.trim() && filteredTablesCount === 0" x-cloak>
                                <td colspan="3" class="py-8 text-center text-gray-400 text-xs">
                                    No database tables matching "<span class="font-semibold text-gray-700" x-text="tableSearch.trim()"></span>"
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right 1 Column: Environment, Server Limits & Maintenance --}}
        <div class="space-y-6">

            {{-- Server Environment & Config --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-xs space-y-4">
                <h3 class="text-base font-bold text-gray-900">Server &amp; Environment Specs</h3>

                <div class="space-y-3 text-xs divide-y divide-gray-50">
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-gray-500">PHP Version:</span>
                        <span class="font-semibold text-gray-800">{{ $stats['server']['php_version'] }} ({{ $stats['server']['php_sapi'] }})</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-gray-500">Laravel Version:</span>
                        <span class="font-semibold text-emerald-700">v{{ $stats['application']['laravel_version'] }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-gray-500">Environment:</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $stats['application']['environment'] === 'production' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $stats['application']['environment'] }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-gray-500">Debug Mode:</span>
                        <span class="font-semibold {{ $stats['application']['debug'] ? 'text-amber-600' : 'text-emerald-600' }}">
                            {{ $stats['application']['debug'] ? 'Enabled (Local)' : 'Disabled (Secure)' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-gray-500">Cache Driver:</span>
                        <span class="font-semibold text-gray-800 capitalize">{{ $stats['application']['cache_driver'] }} ({{ $stats['application']['cache_latency_ms'] }}ms)</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-gray-500">Session Driver:</span>
                        <span class="text-gray-800 capitalize font-medium">{{ $stats['application']['session_driver'] }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-gray-500">Queue Driver:</span>
                        <span class="text-gray-800 capitalize font-medium">{{ $stats['application']['queue_driver'] }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-gray-500">Max Execution Time:</span>
                        <span class="font-semibold text-gray-800">{{ $stats['server']['max_execution_time'] }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-gray-500">Upload Max Limit:</span>
                        <span class="font-semibold text-gray-800">{{ $stats['server']['upload_max_filesize'] }} / {{ $stats['server']['post_max_size'] }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-gray-500">Public Storage:</span>
                        <span class="font-semibold {{ $stats['storage']['symlink_ok'] ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $stats['storage']['symlink_ok'] ? 'Symlinked & Active' : 'Missing Symlink' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Maintenance Toolkit Box with Standardized Buttons --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Maintenance &amp; Cache Tools</h3>
                        <p class="text-xs text-gray-500">Run quick administrative cache flushes safely.</p>
                    </div>
                </div>

                <div class="space-y-3 pt-1">
                    <x-admin.button variant="secondary" size="default" type="button" class="w-full justify-between" @click="clearViews()" x-bind:disabled="cacheActionLoading">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                            <span>Clear Compiled Views</span>
                        </span>
                        <span class="text-[11px] text-gray-400 font-mono">view:clear</span>
                    </x-admin.button>

                    <x-admin.button variant="secondary" size="default" type="button" class="w-full justify-between" @click="clearAllCache()" x-bind:disabled="cacheActionLoading">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span>Flush All App Caches</span>
                        </span>
                        <span class="text-[11px] text-gray-400 font-mono">optimize:clear</span>
                    </x-admin.button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function systemDiagnosticsPage() {
        return {
            loading: false,
            cacheActionLoading: false,
            toastMessage: '',
            tableSearch: '',
            tableList: @json(collect($tables)->pluck('table_name')),
            get filteredTablesCount() {
                if (!this.tableSearch || !this.tableSearch.trim()) return this.tableList.length;
                var q = this.tableSearch.trim().toLowerCase();
                return this.tableList.filter(function(t) { return t.toLowerCase().includes(q); }).length;
            },
            metrics: null,
            perf: {
                loadTime: 0,
                ttfb: 0,
                domReady: 0,
                dns: 0,
                tcp: 0,
                transferKb: 0,
                scriptsCount: 0,
                stylesCount: 0,
                imagesCount: 0,
                fetchCount: 0,
                rating: 'Analyzing...'
            },
            init() {
                var self = this;
                var update = function() { self.measureTiming(); };
                if (document.readyState === 'complete') {
                    update();
                } else {
                    window.addEventListener('load', update);
                }
            },
            measureTiming() {
                try {
                    var nav = performance.getEntriesByType('navigation')[0];
                    if (nav) {
                        this.perf.loadTime = Math.round(nav.duration || (nav.loadEventEnd - nav.startTime)) || 0;
                        this.perf.ttfb = Math.round(nav.responseStart - nav.requestStart) || 0;
                        this.perf.domReady = Math.round(nav.domContentLoadedEventEnd - nav.startTime) || 0;
                        this.perf.dns = Math.round(nav.domainLookupEnd - nav.domainLookupStart) || 0;
                        this.perf.tcp = Math.round(nav.connectEnd - nav.connectStart) || 0;

                        if (this.perf.loadTime < 600) {
                            this.perf.rating = 'Lightning Fast';
                        } else if (this.perf.loadTime < 1500) {
                            this.perf.rating = 'Good Performance';
                        } else {
                            this.perf.rating = 'High Latency';
                        }
                    }

                    var resources = performance.getEntriesByType('resource') || [];
                    var bytes = 0;
                    var scripts = 0, styles = 0, images = 0, fetches = 0;
                    resources.forEach(function(r) {
                        bytes += (r.transferSize || 0);
                        if (r.initiatorType === 'script') scripts++;
                        else if (r.initiatorType === 'css' || r.initiatorType === 'link') styles++;
                        else if (r.initiatorType === 'img' || r.initiatorType === 'image') images++;
                        else if (r.initiatorType === 'fetch' || r.initiatorType === 'xmlhttprequest') fetches++;
                    });
                    this.perf.transferKb = Math.round(bytes / 1024);
                    this.perf.scriptsCount = scripts;
                    this.perf.stylesCount = styles;
                    this.perf.imagesCount = images;
                    this.perf.fetchCount = fetches;
                } catch(e) {}
            },
            refreshMetrics() {
                var self = this;
                self.loading = true;
                fetch('{{ route('admin.system.health') }}', { headers: { 'Accept': 'application/json' } })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        self.metrics = data;
                        self.loading = false;
                        self.toastMessage = 'System diagnostics refreshed successfully.';
                    })
                    .catch(function() { self.loading = false; });
            },
            clearViews() {
                var self = this;
                self.cacheActionLoading = true;
                self.toastMessage = '';
                var token = document.querySelector('meta[name=csrf-token]') ? document.querySelector('meta[name=csrf-token]').content : '';
                fetch('{{ route('admin.system.clear-views') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
                })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    self.cacheActionLoading = false;
                    self.toastMessage = res.message || 'Views cleared successfully.';
                })
                .catch(function() {
                    self.cacheActionLoading = false;
                    self.toastMessage = 'Failed to clear view cache.';
                });
            },
            clearAllCache() {
                var self = this;
                self.cacheActionLoading = true;
                self.toastMessage = '';
                var token = document.querySelector('meta[name=csrf-token]') ? document.querySelector('meta[name=csrf-token]').content : '';
                fetch('{{ route('admin.system.clear-cache') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
                })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    self.cacheActionLoading = false;
                    self.toastMessage = res.message || 'Cache flushed successfully.';
                })
                .catch(function() {
                    self.cacheActionLoading = false;
                    self.toastMessage = 'Failed to flush cache.';
                });
            }
        };
    }
</script>
@endsection
