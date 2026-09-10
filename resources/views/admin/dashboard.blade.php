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
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ $leadCount }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $newLeads }} awaiting first call</p>
            </a>

            <a href="{{ route('admin.customers.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 transition hover:shadow-md">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500">Customers</span>
                    <div class="w-9 h-9 bg-brand-50 text-brand-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ $customerCount }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $activeCustomers }} active</p>
            </a>

            <a href="{{ route('admin.work-orders.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 transition hover:shadow-md">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500">Work Orders</span>
                    <div class="w-9 h-9 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ $workOrderCount }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $activeWorkOrders }} in progress</p>
            </a>

            <a href="{{ route('admin.invoices.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 transition hover:shadow-md">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500">Invoices</span>
                    <div class="w-9 h-9 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8h4m-4 4h4"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ $invoiceCount }}</p>
                <p class="text-xs text-gray-400 mt-1">₹{{ number_format($outstanding, 0) }} outstanding</p>
            </a>

            <a href="{{ route('admin.products.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 transition hover:shadow-md">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500">Products</span>
                    <div class="w-9 h-9 bg-teal-50 text-teal-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ $productCount }}</p>
                <p class="text-xs {{ $lowStockCount > 0 ? 'text-red-500' : 'text-gray-400' }} mt-1">{{ $lowStockCount }} low stock</p>
            </a>

            <a href="{{ route('admin.staff.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 transition hover:shadow-md">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500">Staff</span>
                    <div class="w-9 h-9 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
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
                    <a href="{{ route('admin.leads.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">View all →</a>
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
                    <a href="{{ route('admin.invoices.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">View all →</a>
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
                    <a href="{{ route('admin.stock-movements.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">View all →</a>
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
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.leads.index') }}"
                       class="flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm font-medium text-gray-700 hover:bg-brand-50 hover:border-brand-200 hover:text-brand-700 transition">
                        Leads
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.work-orders.index') }}"
                       class="flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm font-medium text-gray-700 hover:bg-brand-50 hover:border-brand-200 hover:text-brand-700 transition">
                        Work Orders
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.invoices.index') }}"
                       class="flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm font-medium text-gray-700 hover:bg-brand-50 hover:border-brand-200 hover:text-brand-700 transition">
                        Invoices
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.products.index') }}"
                       class="flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm font-medium text-gray-700 hover:bg-brand-50 hover:border-brand-200 hover:text-brand-700 transition">
                        Products
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.automation.index') }}"
                       class="flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm font-medium text-gray-700 hover:bg-brand-50 hover:border-brand-200 hover:text-brand-700 transition">
                        Automation
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
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
                    Configure automation →
                </a>
            </div>
        </div>
    </div>
@endsection

