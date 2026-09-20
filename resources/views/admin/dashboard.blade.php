@extends('admin.layout')

@section('page-title', 'Dashboard')

@section('content')
@php
    $badgeMap = [
        'blue' => 'bg-blue-50 text-blue-700',
        'yellow' => 'bg-amber-50 text-amber-700',
        'gray' => 'bg-gray-100 text-gray-600',
        'purple' => 'bg-purple-50 text-purple-700',
        'green' => 'bg-green-50 text-green-700',
        'red' => 'bg-red-50 text-red-700',
    ];
@endphp
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Welcome back, {{ Auth::guard('admin')->user()->name }}!</h2>
                <p class="text-sm text-gray-500 mt-0.5">Here's real-time overview and health of your business operations.</p>
            </div>
            <div class="flex items-center gap-2 self-start sm:self-auto">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100/80 shadow-xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live Syncing
                </span>
            </div>
        </div>

        {{-- Row 1: KPI Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <a href="{{ route('admin.leads.index') }}" class="group bg-white rounded-2xl shadow-xs hover:shadow-lg border border-gray-100/90 hover:border-blue-200 p-5 transition-all duration-200 hover:-translate-y-1">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 group-hover:text-blue-600 transition-colors">Leads</span>
                    <div class="w-9 h-9 bg-blue-50/80 text-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:bg-blue-100 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900 tracking-tight">{{ $leadCount }}</p>
                <div class="flex items-center gap-1.5 mt-1.5 text-xs text-gray-500">
                    <span class="inline-block w-1.5 h-1.5 rounded-full {{ $newLeads > 0 ? 'bg-blue-500' : 'bg-gray-300' }}"></span>
                    <span>{{ $newLeads }} awaiting call</span>
                </div>
            </a>

            <a href="{{ route('admin.customers.index') }}" class="group bg-white rounded-2xl shadow-xs hover:shadow-lg border border-gray-100/90 hover:border-brand-200 p-5 transition-all duration-200 hover:-translate-y-1">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 group-hover:text-brand-600 transition-colors">Customers</span>
                    <div class="w-9 h-9 bg-brand-50/80 text-brand-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:bg-brand-100 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900 tracking-tight">{{ $customerCount }}</p>
                <div class="flex items-center gap-1.5 mt-1.5 text-xs text-gray-500">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>{{ $activeCustomers }} active</span>
                </div>
            </a>

            <a href="{{ route('admin.work-orders.index') }}" class="group bg-white rounded-2xl shadow-xs hover:shadow-lg border border-gray-100/90 hover:border-indigo-200 p-5 transition-all duration-200 hover:-translate-y-1">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 group-hover:text-indigo-600 transition-colors">Work Orders</span>
                    <div class="w-9 h-9 bg-indigo-50/80 text-indigo-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:bg-indigo-100 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 9.75h4.5m-4.5 3h4.5m-4.5 3h4.5m-5.625 3.75h6.75a4.5 4.5 0 004.5-4.5v-3a4.5 4.5 0 00-4.5-4.5H16.5a3 3 0 00-3-3h-3a3 3 0 00-3 3H7.125a4.5 4.5 0 00-4.5 4.5v3a4.5 4.5 0 004.5 4.5h6.75M12 3h.008v.008H12V3z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900 tracking-tight">{{ $workOrderCount }}</p>
                <div class="flex items-center gap-1.5 mt-1.5 text-xs text-gray-500">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    <span>{{ $activeWorkOrders }} in progress</span>
                </div>
            </a>

            <a href="{{ route('admin.invoices.index') }}" class="group bg-white rounded-2xl shadow-xs hover:shadow-lg border border-gray-100/90 hover:border-emerald-200 p-5 transition-all duration-200 hover:-translate-y-1">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 group-hover:text-emerald-600 transition-colors">Invoices</span>
                    <div class="w-9 h-9 bg-emerald-50/80 text-emerald-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:bg-emerald-100 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900 tracking-tight">{{ $invoiceCount }}</p>
                <div class="flex items-center gap-1.5 mt-1.5 text-xs text-gray-500">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>₹{{ number_format($outstanding, 0) }} due</span>
                </div>
            </a>

            <a href="{{ route('admin.products.index') }}" class="group bg-white rounded-2xl shadow-xs hover:shadow-lg border border-gray-100/90 hover:border-teal-200 p-5 transition-all duration-200 hover:-translate-y-1">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 group-hover:text-teal-600 transition-colors">Products</span>
                    <div class="w-9 h-9 bg-teal-50/80 text-teal-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:bg-teal-100 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900 tracking-tight">{{ $productCount }}</p>
                <div class="flex items-center gap-1.5 mt-1.5 text-xs {{ $lowStockCount > 0 ? 'text-red-500 font-medium' : 'text-gray-500' }}">
                    <span class="inline-block w-1.5 h-1.5 rounded-full {{ $lowStockCount > 0 ? 'bg-red-500' : 'bg-teal-500' }}"></span>
                    <span>{{ $lowStockCount }} low stock</span>
                </div>
            </a>

            <a href="{{ route('admin.staff.index') }}" class="group bg-white rounded-2xl shadow-xs hover:shadow-lg border border-gray-100/90 hover:border-orange-200 p-5 transition-all duration-200 hover:-translate-y-1">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 group-hover:text-orange-600 transition-colors">Staff</span>
                    <div class="w-9 h-9 bg-orange-50/80 text-orange-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:bg-orange-100 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900 tracking-tight">{{ $staffCount }}</p>
                <div class="flex items-center gap-1.5 mt-1.5 text-xs text-gray-500">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Active users</span>
                </div>
            </a>
        </div>

        {{-- Row 2: Automation Alert Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('admin.invoices.index', ['status' => 'overdue']) }}"
               class="group bg-white rounded-2xl shadow-xs hover:shadow-md border border-gray-100 p-5 transition-all duration-200 hover:border-red-300 hover:-translate-y-0.5">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">Overdue Invoices</span>
                    <span class="w-2.5 h-2.5 rounded-full {{ $overdueInvoices > 0 ? 'bg-red-500' : 'bg-gray-300' }}"></span>
                </div>
                <p class="text-3xl font-bold {{ $overdueInvoices > 0 ? 'text-red-600' : 'text-gray-900' }} mt-2">{{ $overdueInvoices }}</p>
                <p class="text-xs text-gray-400 mt-1 font-medium">₹{{ number_format($overdueAmount, 0) }} total overdue amount</p>
            </a>
            <a href="{{ route('admin.work-orders.index') }}"
               class="group bg-white rounded-2xl shadow-xs hover:shadow-md border border-gray-100 p-5 transition-all duration-200 hover:border-amber-300 hover:-translate-y-0.5">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">Stale Work Orders</span>
                    <span class="w-2.5 h-2.5 rounded-full {{ $staleWorkOrders > 0 ? 'bg-amber-500' : 'bg-gray-300' }}"></span>
                </div>
                <p class="text-3xl font-bold {{ $staleWorkOrders > 0 ? 'text-amber-600' : 'text-gray-900' }} mt-2">{{ $staleWorkOrders }}</p>
                <p class="text-xs text-gray-400 mt-1 font-medium">No activity in {{ config('automation.work_order_stale_days', 7) }}+ days</p>
            </a>
            <a href="{{ route('admin.leads.index') }}"
               class="group bg-white rounded-2xl shadow-xs hover:shadow-md border border-gray-100 p-5 transition-all duration-200 hover:border-amber-300 hover:-translate-y-0.5">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">Stale Leads</span>
                    <span class="w-2.5 h-2.5 rounded-full {{ $staleLeads > 0 ? 'bg-amber-500' : 'bg-gray-300' }}"></span>
                </div>
                <p class="text-3xl font-bold {{ $staleLeads > 0 ? 'text-amber-600' : 'text-gray-900' }} mt-2">{{ $staleLeads }}</p>
                <p class="text-xs text-gray-400 mt-1 font-medium">Require customer outreach</p>
            </a>
        </div>

        {{-- Row 3: Revenue Snapshot --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-emerald-50/70 via-white to-emerald-50/30 rounded-2xl shadow-xs border border-emerald-100/80 p-6 relative overflow-hidden group hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Collected This Month</p>
                    <span class="p-1.5 bg-emerald-100/70 text-emerald-700 rounded-lg text-xs font-semibold">₹ INR</span>
                </div>
                <p class="text-3xl font-bold text-gray-900 mt-3 tracking-tight">₹{{ number_format($collectedThisMonth, 0) }}</p>
                <p class="text-xs text-emerald-600/80 mt-1.5 font-medium">From {{ now()->format('F Y') }} payments</p>
            </div>
            <a href="{{ route('admin.invoices.index', ['status' => 'unpaid']) }}" class="bg-gradient-to-br from-amber-50/70 via-white to-amber-50/30 rounded-2xl shadow-xs border border-amber-100/80 p-6 relative overflow-hidden group hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Total Outstanding</p>
                    <span class="p-1.5 bg-amber-100/70 text-amber-700 rounded-lg text-xs font-semibold">Pending</span>
                </div>
                <p class="text-3xl font-bold text-gray-900 mt-3 tracking-tight">₹{{ number_format($outstanding, 0) }}</p>
                <p class="text-xs text-amber-600/80 mt-1.5 font-medium">Across unpaid, partial & overdue</p>
            </a>
            <a href="{{ route('admin.invoices.index', ['status' => 'overdue']) }}" class="bg-gradient-to-br from-red-50/70 via-white to-red-50/30 rounded-2xl shadow-xs border border-red-100/80 p-6 relative overflow-hidden group hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-red-700">Overdue Invoices</p>
                    <span class="p-1.5 bg-red-100/70 text-red-700 rounded-lg text-xs font-semibold">Past Due</span>
                </div>
                <p class="text-3xl font-bold text-gray-900 mt-3 tracking-tight">₹{{ number_format($overdueAmount, 0) }}</p>
                <p class="text-xs text-red-600/80 mt-1.5 font-medium">Past due date & requires follow-up</p>
            </a>
        </div>

        {{-- Row 4: Recent Activity --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Recent Leads</h3>
                        <p class="text-xs text-gray-400">Latest customer inquiries</p>
                    </div>
                    <a href="{{ route('admin.leads.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 bg-brand-50 hover:bg-brand-100 px-2.5 py-1 rounded-lg transition">View all</a>
                </div>
                @if($recentLeads->isNotEmpty())
                    <div class="divide-y divide-gray-50">
                        @foreach($recentLeads as $lead)
                            <a href="{{ route('admin.leads.show', $lead) }}" class="flex items-center justify-between py-3 hover:bg-gray-50/80 -mx-2 px-2 rounded-xl transition group">
                                <div class="flex items-center gap-3 min-w-0">
                                    <x-admin.avatar :name="$lead->name" size="sm" class="group-hover:scale-105 transition-transform" />
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-800 truncate group-hover:text-brand-700 transition-colors">{{ $lead->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $lead->phone }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="text-[11px] font-medium px-2 py-0.5 rounded-full {{ $badgeMap[\App\Models\Lead::STATUS_COLORS[$lead->status] ?? 'gray'] ?? 'bg-gray-100 text-gray-600' }}">{{ \App\Models\Lead::STATUSES[$lead->status] ?? $lead->status }}</span>
                                    <span class="text-xs text-gray-400">{{ $lead->created_at->diffForHumans(null, true) }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center">
                        <div class="w-10 h-10 bg-gray-50 rounded-xl flex items-center justify-center mx-auto text-gray-400 mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        </div>
                        <p class="text-xs text-gray-400">No leads recorded yet.</p>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Recent Payments</h3>
                        <p class="text-xs text-gray-400">Incoming cashflow</p>
                    </div>
                    <a href="{{ route('admin.invoices.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 bg-brand-50 hover:bg-brand-100 px-2.5 py-1 rounded-lg transition">View all</a>
                </div>
                @if($recentPayments->isNotEmpty())
                    <div class="divide-y divide-gray-50">
                        @foreach($recentPayments as $payment)
                            <a href="{{ route('admin.invoices.show', $payment->invoice) }}" class="flex items-center justify-between py-3 hover:bg-gray-50/80 -mx-2 px-2 rounded-xl transition group">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-xs font-bold shadow-xs flex-shrink-0 group-hover:scale-105 transition-transform">
                                        ₹
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-800 group-hover:text-emerald-700 transition-colors">{{ \App\Models\Payment::METHODS[$payment->method] ?? $payment->method }}</p>
                                        <p class="text-xs text-gray-400">Invoice #{{ $payment->invoice?->number }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="text-sm font-bold text-emerald-600">₹{{ number_format((float) $payment->amount, 0) }}</span>
                                    <span class="text-xs text-gray-400">{{ $payment->paid_at ? $payment->paid_at->diffForHumans(null, true) : '' }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center">
                        <div class="w-10 h-10 bg-gray-50 rounded-xl flex items-center justify-center mx-auto text-gray-400 mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        </div>
                        <p class="text-xs text-gray-400">No payments received yet.</p>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Recent Stock Movements</h3>
                        <p class="text-xs text-gray-400">Inventory ins and outs</p>
                    </div>
                    <a href="{{ route('admin.stock-movements.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 bg-brand-50 hover:bg-brand-100 px-2.5 py-1 rounded-lg transition">View all</a>
                </div>
                @if($recentStockMovements->isNotEmpty())
                    <div class="divide-y divide-gray-50">
                        @foreach($recentStockMovements as $movement)
                            @php
                                $mTypeClass = ['in' => 'bg-emerald-50 text-emerald-700', 'out' => 'bg-red-50 text-red-700', 'adjustment' => 'bg-blue-50 text-blue-700'][$movement->type] ?? 'bg-gray-100 text-gray-600';
                            @endphp
                            <a href="{{ route('admin.stock-movements.show', $movement) }}" class="flex items-center justify-between py-3 hover:bg-gray-50/80 -mx-2 px-2 rounded-xl transition group">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-xl {{ $movement->quantity >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }} flex items-center justify-center text-xs font-bold shadow-xs flex-shrink-0 group-hover:scale-105 transition-transform">
                                        @if($movement->quantity >= 0)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-800 truncate group-hover:text-brand-700 transition-colors">{{ $movement->product?->name ?? 'Deleted product' }}</p>
                                        <span class="text-[10px] px-2 py-0.2 rounded-full font-medium {{ $mTypeClass }}">{{ \App\Models\StockMovement::TYPES[$movement->type] ?? $movement->type }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="text-sm font-bold {{ $movement->quantity >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ $movement->quantity >= 0 ? '+' : '' }}{{ \App\Support\Format::qty($movement->quantity) }} {{ $movement->product?->unit }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $movement->created_at->diffForHumans(null, true) }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center">
                        <div class="w-10 h-10 bg-gray-50 rounded-xl flex items-center justify-center mx-auto text-gray-400 mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <p class="text-xs text-gray-400">No stock movements recorded yet.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Row 5: Quick Actions + Automation Status --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6">
                <h3 class="text-base font-bold text-gray-900 mb-0.5">Quick Actions</h3>
                <p class="text-xs text-gray-400 mb-4">Direct shortcuts to high-frequency workflows.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="{{ route('admin.frontend.index', ['tab' => 'lead_form']) }}"
                       class="group flex items-center justify-between px-4 py-3 bg-gray-50/70 border border-gray-100 hover:border-brand-200 rounded-xl text-sm font-medium text-gray-700 hover:text-brand-700 hover:bg-brand-50/60 transition-all duration-200">
                        <span class="flex items-center gap-2.5">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            Lead Form Settings
                        </span>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-brand-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.leads.index') }}"
                       class="group flex items-center justify-between px-4 py-3 bg-gray-50/70 border border-gray-100 hover:border-brand-200 rounded-xl text-sm font-medium text-gray-700 hover:text-brand-700 hover:bg-brand-50/60 transition-all duration-200">
                        <span class="flex items-center gap-2.5">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            Leads Directory
                        </span>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-brand-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.work-orders.index') }}"
                       class="group flex items-center justify-between px-4 py-3 bg-gray-50/70 border border-gray-100 hover:border-brand-200 rounded-xl text-sm font-medium text-gray-700 hover:text-brand-700 hover:bg-brand-50/60 transition-all duration-200">
                        <span class="flex items-center gap-2.5">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            Work Orders
                        </span>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-brand-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.invoices.index') }}"
                       class="group flex items-center justify-between px-4 py-3 bg-gray-50/70 border border-gray-100 hover:border-brand-200 rounded-xl text-sm font-medium text-gray-700 hover:text-brand-700 hover:bg-brand-50/60 transition-all duration-200">
                        <span class="flex items-center gap-2.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Invoices &amp; Billing
                        </span>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-brand-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.products.index') }}"
                       class="group flex items-center justify-between px-4 py-3 bg-gray-50/70 border border-gray-100 hover:border-brand-200 rounded-xl text-sm font-medium text-gray-700 hover:text-brand-700 hover:bg-brand-50/60 transition-all duration-200">
                        <span class="flex items-center gap-2.5">
                            <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                            Products &amp; Stock
                        </span>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-brand-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.reports.gst') }}"
                       class="group flex items-center justify-between px-4 py-3 bg-gray-50/70 border border-gray-100 hover:border-brand-200 rounded-xl text-sm font-medium text-gray-700 hover:text-brand-700 hover:bg-brand-50/60 transition-all duration-200">
                        <span class="flex items-center gap-2.5">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                            GST Tax Reports
                        </span>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-brand-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-base font-bold text-gray-900">Automation Status</h3>
                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                        Active
                    </span>
                </div>
                <p class="text-xs text-gray-400 mb-4">Background tasks and notifications status.</p>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-gray-500">Delivery channel</span>
                        <span class="font-medium text-gray-800 capitalize bg-gray-50 px-2 py-0.5 rounded-md text-xs">{{ str_replace('_', ' ', config('automation.channel', 'both')) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-gray-500">Auto-invoice on completion</span>
                        <span class="inline-flex items-center gap-1 font-semibold text-xs {{ config('automation.auto_invoice_on_completion', true) ? 'text-emerald-600' : 'text-gray-400' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ config('automation.auto_invoice_on_completion', true) ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                            {{ config('automation.auto_invoice_on_completion', true) ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-gray-500">Overdue tracking</span>
                        <span class="inline-flex items-center gap-1 font-semibold text-xs {{ config('automation.overdue_enabled', true) ? 'text-emerald-600' : 'text-gray-400' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ config('automation.overdue_enabled', true) ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                            {{ config('automation.overdue_enabled', true) ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-gray-500">New-lead alerts</span>
                        <span class="inline-flex items-center gap-1 font-semibold text-xs {{ config('automation.new_lead_alerts_enabled', true) ? 'text-emerald-600' : 'text-gray-400' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ config('automation.new_lead_alerts_enabled', true) ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                            {{ config('automation.new_lead_alerts_enabled', true) ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-gray-500">Mail delivery</span>
                        <span class="font-medium text-gray-700 text-xs">{{ config('mail.default', 'log') === 'smtp' ? 'SMTP configured' : 'Logging only (dev mode)' }}</span>
                    </div>
                </div>
                <a href="{{ route('admin.automation.index') }}" class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-700">
                    <span>Configure automation parameters</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    </div>
@endsection

