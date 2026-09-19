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
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Welcome back, {{ Auth::guard('admin')->user()->name }}!</h2>
            <p class="text-gray-500 mt-1">Here's an overview of your business.</p>
        </div>

        {{-- Row 1: KPI Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <a href="{{ route('admin.leads.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 transition hover:shadow-md">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500">Leads</span>
                    <div class="w-9 h-9 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ $leadCount }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $newLeads }} awaiting first call</p>
            </a>

            <a href="{{ route('admin.customers.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 transition hover:shadow-md">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500">Customers</span>
                    <div class="w-9 h-9 bg-brand-50 text-brand-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ $customerCount }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $activeCustomers }} active</p>
            </a>

            <a href="{{ route('admin.work-orders.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 transition hover:shadow-md">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500">Work Orders</span>
                    <div class="w-9 h-9 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 9.75h4.5m-4.5 3h4.5m-4.5 3h4.5m-5.625 3.75h6.75a4.5 4.5 0 004.5-4.5v-3a4.5 4.5 0 00-4.5-4.5H16.5a3 3 0 00-3-3h-3a3 3 0 00-3 3H7.125a4.5 4.5 0 00-4.5 4.5v3a4.5 4.5 0 004.5 4.5h6.75M12 3h.008v.008H12V3z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ $workOrderCount }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $activeWorkOrders }} in progress</p>
            </a>

            <a href="{{ route('admin.invoices.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 transition hover:shadow-md">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500">Invoices</span>
                    <div class="w-9 h-9 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ $invoiceCount }}</p>
                <p class="text-xs text-gray-400 mt-1">₹{{ number_format($outstanding, 0) }} outstanding</p>
            </a>

            <a href="{{ route('admin.products.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 transition hover:shadow-md">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500">Products</span>
                    <div class="w-9 h-9 bg-teal-50 text-teal-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ $productCount }}</p>
                <p class="text-xs {{ $lowStockCount > 0 ? 'text-red-500' : 'text-gray-400' }} mt-1">{{ $lowStockCount }} low stock</p>
            </a>

            <a href="{{ route('admin.staff.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 transition hover:shadow-md">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500">Staff</span>
                    <div class="w-9 h-9 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ $staffCount }}</p>
                <p class="text-xs text-gray-400 mt-1">active</p>
            </a>
        </div>

        {{-- Row 2: Automation Alert Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('admin.invoices.index', ['status' => 'overdue']) }}"
               class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 transition hover:border-red-200 hover:shadow-md">
                <span class="text-sm font-medium text-gray-500">Overdue Invoices</span>
                <p class="text-3xl font-bold {{ $overdueInvoices > 0 ? 'text-red-600' : 'text-gray-900' }} mt-1">{{ $overdueInvoices }}</p>
                <p class="text-xs text-gray-400 mt-1">₹{{ number_format($overdueAmount, 0) }} total due</p>
            </a>
            <a href="{{ route('admin.work-orders.index') }}"
               class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 transition hover:border-amber-200 hover:shadow-md">
                <span class="text-sm font-medium text-gray-500">Stale Work Orders</span>
                <p class="text-3xl font-bold {{ $staleWorkOrders > 0 ? 'text-amber-600' : 'text-gray-900' }} mt-1">{{ $staleWorkOrders }}</p>
                <p class="text-xs text-gray-400 mt-1">No activity in {{ config('automation.work_order_stale_days', 7) }}+ days</p>
            </a>
            <a href="{{ route('admin.leads.index') }}"
               class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 transition hover:border-amber-200 hover:shadow-md">
                <span class="text-sm font-medium text-gray-500">Stale Leads</span>
                <p class="text-3xl font-bold {{ $staleLeads > 0 ? 'text-amber-600' : 'text-gray-900' }} mt-1">{{ $staleLeads }}</p>
                <p class="text-xs text-gray-400 mt-1">Need follow-up</p>
            </a>
        </div>

        {{-- Row 3: Revenue Snapshot --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-emerald-50 to-white rounded-2xl shadow-sm border border-emerald-100 p-6">
                <p class="text-sm font-medium text-emerald-700">Collected This Month</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">₹{{ number_format($collectedThisMonth, 0) }}</p>
                <p class="text-xs text-gray-400 mt-1">From {{ now()->format('F Y') }} payments</p>
            </div>
            <a href="{{ route('admin.invoices.index', ['status' => 'unpaid']) }}" class="bg-gradient-to-br from-amber-50 to-white rounded-2xl shadow-sm border border-amber-100 p-6 transition hover:shadow-md">
                <p class="text-sm font-medium text-amber-700">Total Outstanding</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">₹{{ number_format($outstanding, 0) }}</p>
                <p class="text-xs text-gray-400 mt-1">Across unpaid, partial & overdue</p>
            </a>
            <a href="{{ route('admin.invoices.index', ['status' => 'overdue']) }}" class="bg-gradient-to-br from-red-50 to-white rounded-2xl shadow-sm border border-red-100 p-6 transition hover:shadow-md">
                <p class="text-sm font-medium text-red-700">Overdue</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">₹{{ number_format($overdueAmount, 0) }}</p>
                <p class="text-xs text-gray-400 mt-1">Past due date & unpaid</p>
            </a>
        </div>

        {{-- Row 4: Recent Activity --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Leads</h3>
                    <a href="{{ route('admin.leads.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">View all</a>
                </div>
                @if($recentLeads->isNotEmpty())
                    <div class="divide-y divide-gray-50">
                        @foreach($recentLeads as $lead)
                            <a href="{{ route('admin.leads.show', $lead) }}" class="flex items-center justify-between py-3 hover:bg-gray-50 -mx-2 px-2 rounded-lg transition">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ $lead->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $lead->phone }}</p>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="text-xs px-2 py-0.5 rounded-full {{ $badgeMap[\App\Models\Lead::STATUS_COLORS[$lead->status] ?? 'gray'] ?? 'bg-gray-100 text-gray-600' }}">{{ \App\Models\Lead::STATUSES[$lead->status] ?? $lead->status }}</span>
                                    <span class="text-xs text-gray-400">{{ $lead->created_at->diffForHumans() }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400 py-6 text-center">No leads yet.</p>
                @endif
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Payments</h3>
                    <a href="{{ route('admin.invoices.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">View all</a>
                </div>
                @if($recentPayments->isNotEmpty())
                    <div class="divide-y divide-gray-50">
                        @foreach($recentPayments as $payment)
                            <a href="{{ route('admin.invoices.show', $payment->invoice) }}" class="flex items-center justify-between py-3 hover:bg-gray-50 -mx-2 px-2 rounded-lg transition">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800">{{ \App\Models\Payment::METHODS[$payment->method] ?? $payment->method }}</p>
                                    <p class="text-xs text-gray-400">Invoice #{{ $payment->invoice?->number }}</p>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="text-sm font-semibold text-emerald-600">₹{{ number_format((float) $payment->amount, 0) }}</span>
                                    <span class="text-xs text-gray-400">{{ $payment->paid_at->diffForHumans() }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400 py-6 text-center">No payments yet.</p>
                @endif
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Stock Movements</h3>
                    <a href="{{ route('admin.stock-movements.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">View all</a>
                </div>
                @if($recentStockMovements->isNotEmpty())
                    <div class="divide-y divide-gray-50">
                        @foreach($recentStockMovements as $movement)
                            @php
                                $mTypeClass = ['in' => 'bg-green-50 text-green-700', 'out' => 'bg-red-50 text-red-700', 'adjustment' => 'bg-blue-50 text-blue-700'][$movement->type] ?? 'bg-gray-100 text-gray-600';
                            @endphp
                            <a href="{{ route('admin.stock-movements.show', $movement) }}" class="flex items-center justify-between py-3 hover:bg-gray-50 -mx-2 px-2 rounded-lg transition">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ $movement->product?->name ?? 'Deleted product' }}</p>
                                    <span class="text-xs px-2 py-0.5 rounded-full {{ $mTypeClass }}">{{ \App\Models\StockMovement::TYPES[$movement->type] ?? $movement->type }}</span>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="text-sm font-semibold {{ $movement->quantity >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ $movement->quantity >= 0 ? '+' : '' }}{{ \App\Support\Format::qty($movement->quantity) }} {{ $movement->product?->unit }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $movement->created_at->diffForHumans() }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400 py-6 text-center">No movements yet.</p>
                @endif
            </div>
        </div>

        {{-- Row 5: Quick Actions + Automation Status --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Quick Actions</h3>
                <p class="text-sm text-gray-500 mb-4">Jump to the most used sections.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="{{ route('admin.frontend.index', ['tab' => 'lead_form']) }}"
                       class="flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm font-medium text-gray-700 hover:bg-brand-50 hover:border-brand-200 hover:text-brand-700 transition">
                        Lead Form
                        
                    </a>
                    <a href="{{ route('admin.leads.index') }}"
                       class="flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm font-medium text-gray-700 hover:bg-brand-50 hover:border-brand-200 hover:text-brand-700 transition">
                        Leads
                        
                    </a>
                    <a href="{{ route('admin.work-orders.index') }}"
                       class="flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm font-medium text-gray-700 hover:bg-brand-50 hover:border-brand-200 hover:text-brand-700 transition">
                        Work Orders
                        
                    </a>
                    <a href="{{ route('admin.invoices.index') }}"
                       class="flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm font-medium text-gray-700 hover:bg-brand-50 hover:border-brand-200 hover:text-brand-700 transition">
                        Invoices
                        
                    </a>
                    <a href="{{ route('admin.products.index') }}"
                       class="flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm font-medium text-gray-700 hover:bg-brand-50 hover:border-brand-200 hover:text-brand-700 transition">
                        Products
                        
                    </a>
                    <a href="{{ route('admin.automation.index') }}"
                       class="flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm font-medium text-gray-700 hover:bg-brand-50 hover:border-brand-200 hover:text-brand-700 transition">
                        Automation
                        
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Automation Status</h3>
                <p class="text-sm text-gray-500 mb-4">How scheduled tasks are set up.</p>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-gray-500">Delivery channel</span>
                        <span class="font-medium text-gray-800 capitalize">{{ str_replace('_', ' ', config('automation.channel', 'both')) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-gray-500">Auto-invoice on completion</span>
                        <span class="font-medium {{ config('automation.auto_invoice_on_completion', true) ? 'text-green-600' : 'text-gray-400' }}">{{ config('automation.auto_invoice_on_completion', true) ? 'On' : 'Off' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-gray-500">Overdue tracking</span>
                        <span class="font-medium {{ config('automation.overdue_enabled', true) ? 'text-green-600' : 'text-gray-400' }}">{{ config('automation.overdue_enabled', true) ? 'On' : 'Off' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-gray-500">New-lead alerts</span>
                        <span class="font-medium {{ config('automation.new_lead_alerts_enabled', true) ? 'text-green-600' : 'text-gray-400' }}">{{ config('automation.new_lead_alerts_enabled', true) ? 'On' : 'Off' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-gray-500">Mail delivery</span>
                        <span class="font-medium text-gray-800">{{ config('mail.default', 'log') === 'smtp' ? 'SMTP configured' : 'Logging only (no real email)' }}</span>
                    </div>
                </div>
                <a href="{{ route('admin.automation.index') }}" class="mt-4 inline-flex items-center text-sm font-medium text-brand-600 hover:text-brand-700">
                    Configure automation
                </a>
            </div>
        </div>
    </div>
@endsection

