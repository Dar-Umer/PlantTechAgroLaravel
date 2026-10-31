@extends('admin.layout')

@section('page-title', 'Customer Profile - ' . $customer->name)

@section('content')
@php
    $salesRoute = Route::has('pos.sales') ? 'pos.sales' : 'admin.pos.sales';
    $showSaleRoute = Route::has('pos.sales.show') ? 'pos.sales.show' : 'admin.pos.sales.show';
    $receiptRoute = Route::has('pos.receipt') ? 'pos.receipt' : 'admin.pos.receipt';
    $invoiceRoute = Route::has('pos.invoice') ? 'pos.invoice' : 'admin.pos.invoice';
    $statementRoute = Route::has('pos.customers.statement') ? 'pos.customers.statement' : 'admin.pos.customers.statement';
    $settleRoute = Route::has('pos.customers.settle-balance') ? 'pos.customers.settle-balance' : 'admin.pos.customers.settle-balance';
    $terminalRoute = Route::has('pos.terminal') ? 'pos.terminal' : 'admin.pos.terminal';

    // Initials for avatar
    $words = explode(' ', trim($customer->name));
    $initials = '';
    foreach (array_slice($words, 0, 2) as $w) {
        $initials .= strtoupper(substr($w, 0, 1));
    }
    $initials = $initials ?: 'C';
@endphp

<div class="w-full space-y-6 pb-16"
     x-data="{
        activeTab: '{{ request()->has('sales_page') ? 'sales' : (request()->has('payments_page') ? 'payments' : 'statement') }}',
        settleModal: false,
        settleAmount: '{{ (float) $customer->outstanding_balance }}',
        settleMethod: 'cash',
        settleNote: '',
        openSettleModal() {
            this.settleAmount = '{{ (float) $customer->outstanding_balance }}';
            this.settleMethod = 'cash';
            this.settleNote = '';
            this.settleModal = true;
        }
     }">

    {{-- Breadcrumb & Quick Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route($salesRoute) }}" class="hover:text-brand-600 transition flex items-center gap-1 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>POS Sales</span>
            </a>
            <span class="text-gray-300">/</span>
            <span class="font-bold text-gray-800">Customer Profile & Statement</span>
            <span class="text-gray-300">/</span>
            <span class="text-gray-700 font-semibold text-xs">{{ $customer->name }}</span>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route($statementRoute, array_merge(['customer' => $customer], request()->only(['from', 'to']))) }}"
               target="_blank"
               class="px-3.5 py-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 rounded-xl font-bold text-xs inline-flex items-center gap-1.5 shadow-2xs transition">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Statement</span>
            </a>

            <a href="{{ route($terminalRoute) }}"
               class="px-3.5 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl font-bold text-xs inline-flex items-center gap-1.5 shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>New POS Sale</span>
            </a>
        </div>
    </div>

    {{-- Main Customer Header Profile Card --}}
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-brand-600 to-indigo-700 text-white font-black text-xl flex items-center justify-center shadow-md shrink-0">
                    {{ $initials }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl font-black text-gray-900 tracking-tight">{{ $customer->name }}</h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                            POS Customer #{{ $customer->id }}
                        </span>
                        @if($customer->isB2B())
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span>B2B / GST</span>
                            </span>
                        @endif
                        @if($linkedCustomer)
                            <a href="{{ route('admin.customers.show', $linkedCustomer) }}"
                               class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition"
                               title="Linked Orchardist / CRM Profile">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                <span>CRM Orchardist #{{ $linkedCustomer->orchardist_id ?: $linkedCustomer->id }}</span>
                            </a>
                        @endif
                    </div>

                    <div class="mt-2.5 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-gray-500">
                        @if($customer->phone)
                            <div class="flex items-center gap-1.5 font-medium text-gray-700">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <a href="tel:{{ $customer->phone }}" class="hover:text-brand-600 transition">{{ $customer->phone }}</a>
                            </div>
                        @endif

                        @if($customer->email)
                            <div class="flex items-center gap-1.5 font-medium text-gray-700">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <a href="mailto:{{ $customer->email }}" class="hover:text-brand-600 transition">{{ $customer->email }}</a>
                            </div>
                        @endif

                        @if($customer->gstin)
                            <div class="flex items-center gap-1.5 font-mono text-[11px] text-gray-700 bg-gray-50 px-2 py-0.5 rounded border border-gray-200">
                                <span class="font-bold text-gray-500">GSTIN:</span>
                                <span>{{ $customer->gstin }}</span>
                            </div>
                        @endif

                        @if($customer->address)
                            <div class="flex items-center gap-1.5 text-gray-600">
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>{{ $customer->address }}</span>
                            </div>
                        @endif

                        <div class="text-gray-400">
                            Registered: {{ $customer->created_at ? $customer->created_at->format('d M Y') : 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Outstanding Balance Highlight Box --}}
            <div class="p-4 rounded-xl border {{ (float) $customer->outstanding_balance > 0 ? 'bg-amber-50/70 border-amber-200' : 'bg-emerald-50/60 border-emerald-200' }} flex flex-col justify-between shrink-0 min-w-[220px]">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider {{ (float) $customer->outstanding_balance > 0 ? 'text-amber-800' : 'text-emerald-800' }}">
                        Current Balance Due
                    </span>
                    @if((float) $customer->outstanding_balance > 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-200 text-amber-900">
                            Pending
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-200 text-emerald-900">
                            All Clear
                        </span>
                    @endif
                </div>
                <div class="mt-1 text-2xl font-black {{ (float) $customer->outstanding_balance > 0 ? 'text-amber-900' : 'text-emerald-900' }}">
                    ₹{{ number_format($customer->outstanding_balance, 2) }}
                </div>
                <div class="mt-1.5 text-[11px] {{ (float) $customer->outstanding_balance > 0 ? 'text-amber-700 font-medium' : 'text-emerald-700 font-medium' }}">
                    {{ (float) $customer->outstanding_balance > 0 ? 'Pending payment collection' : 'No outstanding dues pending.' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Outstanding Alert Banner if dues exist --}}
    @if((float) $customer->outstanding_balance > 0)
        <div class="rounded-2xl bg-gradient-to-r from-amber-500 to-rose-600 p-0.5 shadow-sm">
            <div class="rounded-[14px] bg-white p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-extrabold text-gray-900">Outstanding Credit Balance of ₹{{ number_format($customer->outstanding_balance, 2) }}</h4>
                        <p class="text-xs text-gray-500 mt-0.5">This customer has pending payments across unpaid invoices. Settlements recorded here will automatically apply FIFO to clear the oldest unpaid sales.</p>
                    </div>
                </div>
                <button type="button"
                        @click="openSettleModal()"
                        class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-bold text-xs inline-flex items-center justify-center gap-1.5 shadow-sm transition shrink-0 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Receive / Settle Dues</span>
                </button>
            </div>
        </div>
    @endif

    {{-- KPI Overview Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Total Purchases Billed --}}
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Total Billed Purchases</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-gray-900">₹{{ number_format($lifetimePurchases, 2) }}</div>
                <div class="text-xs text-gray-500 mt-1">Gross sales lifetime volume</div>
            </div>
        </div>

        {{-- Card 2: Total Collections Paid --}}
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Total Payments Received</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-emerald-600">₹{{ number_format($lifetimePaid, 2) }}</div>
                <div class="text-xs text-gray-500 mt-1">Total cash/UPI collections settled</div>
            </div>
        </div>

        {{-- Card 3: Outstanding Due Balance --}}
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Outstanding Balance</span>
                <div class="w-8 h-8 rounded-lg {{ (float) $outstandingBalance > 0 ? 'bg-red-50 text-red-600' : 'bg-gray-50 text-gray-400' }} flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black {{ (float) $outstandingBalance > 0 ? 'text-red-600' : 'text-gray-900' }}">
                    ₹{{ number_format($outstandingBalance, 2) }}
                </div>
                <div class="text-xs {{ (float) $outstandingBalance > 0 ? 'text-red-600 font-medium' : 'text-gray-500' }} mt-1">
                    {{ (float) $outstandingBalance > 0 ? 'Pending collection' : 'Zero balance / Cleared' }}
                </div>
            </div>
        </div>

        {{-- Card 4: Total Orders --}}
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Total Orders / Invoices</span>
                <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-gray-900">{{ $lifetimeOrdersCount }}</div>
                <div class="text-xs text-gray-500 mt-1">Completed retail transactions</div>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation & Content Area --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        {{-- Tabs Bar --}}
        <div class="border-b border-gray-100 bg-gray-50/70 px-4 sm:px-6 flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-1 sm:gap-2">
                <button type="button"
                        @click="activeTab = 'statement'"
                        :class="activeTab === 'statement' ? 'border-brand-600 text-brand-600 font-extrabold bg-white shadow-2xs' : 'border-transparent text-gray-500 hover:text-gray-700 font-semibold'"
                        class="px-4 py-3.5 text-xs sm:text-sm border-b-2 transition flex items-center gap-2 rounded-t-lg cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Account Statement & Ledger</span>
                </button>

                <button type="button"
                        @click="activeTab = 'sales'"
                        :class="activeTab === 'sales' ? 'border-brand-600 text-brand-600 font-extrabold bg-white shadow-2xs' : 'border-transparent text-gray-500 hover:text-gray-700 font-semibold'"
                        class="px-4 py-3.5 text-xs sm:text-sm border-b-2 transition flex items-center gap-2 rounded-t-lg cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span>Invoices & Sales ({{ $sales->total() }})</span>
                </button>

                <button type="button"
                        @click="activeTab = 'payments'"
                        :class="activeTab === 'payments' ? 'border-brand-600 text-brand-600 font-extrabold bg-white shadow-2xs' : 'border-transparent text-gray-500 hover:text-gray-700 font-semibold'"
                        class="px-4 py-3.5 text-xs sm:text-sm border-b-2 transition flex items-center gap-2 rounded-t-lg cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Payment History ({{ $payments->total() }})</span>
                </button>
            </div>

            <div class="py-2">
                <a href="{{ route($statementRoute, array_merge(['customer' => $customer], request()->only(['from', 'to']))) }}"
                   target="_blank"
                   class="text-xs text-brand-600 hover:text-brand-800 font-bold inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>Printable Ledger View</span>
                </a>
            </div>
        </div>

        {{-- TAB 1: Account Statement / Ledger --}}
        <div x-show="activeTab === 'statement'" class="p-4 sm:p-6 space-y-6">
            {{-- Date Filter Form --}}
            <form method="GET" action="{{ url()->current() }}" class="bg-gray-50/70 p-4 rounded-xl border border-gray-200/70 flex flex-wrap items-end gap-3">
                <input type="hidden" name="tab" value="statement">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-1">From Date</label>
                    <input type="date" name="from" value="{{ $from }}" class="px-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 shadow-2xs">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-1">To Date</label>
                    <input type="date" name="to" value="{{ $to }}" class="px-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 shadow-2xs">
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-lg font-bold text-xs shadow-2xs transition">
                        Filter Statement
                    </button>
                    @if($from || $to)
                        <a href="{{ url()->current() }}" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-bold text-xs transition">
                            Reset
                        </a>
                    @endif
                </div>

                {{-- Period Totals Summary Pill --}}
                <div class="ml-auto flex flex-wrap items-center gap-3 text-xs">
                    <div class="bg-white px-3 py-1.5 rounded-lg border border-gray-200 shadow-2xs">
                        <span class="text-gray-400 font-semibold">Opening:</span>
                        <strong class="font-extrabold text-gray-800">₹{{ number_format($openingBalance, 2) }}</strong>
                    </div>
                    <div class="bg-white px-3 py-1.5 rounded-lg border border-gray-200 shadow-2xs">
                        <span class="text-gray-400 font-semibold">Debit (Invoiced):</span>
                        <strong class="font-extrabold text-red-600">+₹{{ number_format($totalDebit, 2) }}</strong>
                    </div>
                    <div class="bg-white px-3 py-1.5 rounded-lg border border-gray-200 shadow-2xs">
                        <span class="text-gray-400 font-semibold">Credit (Paid):</span>
                        <strong class="font-extrabold text-emerald-600">-₹{{ number_format($totalCredit, 2) }}</strong>
                    </div>
                    <div class="bg-white px-3 py-1.5 rounded-lg border border-gray-200 shadow-2xs">
                        <span class="text-gray-400 font-semibold">Closing Due:</span>
                        <strong class="text-brand-700 font-black">₹{{ number_format($closingBalance, 2) }}</strong>
                    </div>
                </div>
            </form>

            {{-- Statement Ledger Table --}}
            <div class="overflow-x-auto rounded-xl border border-gray-100">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                            <th class="py-3 px-4">Date & Time</th>
                            <th class="py-3 px-4">Ref / Type</th>
                            <th class="py-3 px-4">Particulars / Details</th>
                            <th class="py-3 px-4 text-right">Debit (+Due)</th>
                            <th class="py-3 px-4 text-right">Credit (-Paid)</th>
                            <th class="py-3 px-4 text-right">Running Balance</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        {{-- Opening Balance Row --}}
                        <tr class="bg-gray-50/50 font-semibold text-gray-600">
                            <td class="py-3 px-4 text-gray-500">
                                {{ $from ? \Illuminate\Support\Carbon::parse($from)->format('d M Y') : 'Start of Account' }}
                            </td>
                            <td class="py-3 px-4 font-bold text-gray-500">OPENING</td>
                            <td class="py-3 px-4 text-gray-600">Opening Balance Brought Forward</td>
                            <td class="py-3 px-4 text-right text-gray-400">—</td>
                            <td class="py-3 px-4 text-right text-gray-400">—</td>
                            <td class="py-3 px-4 text-right font-extrabold text-gray-800">
                                ₹{{ number_format($openingBalance, 2) }}
                            </td>
                            <td class="py-3 px-4 text-right text-gray-400">—</td>
                        </tr>

                        @forelse($ledgerEntries as $entry)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="py-3 px-4 text-gray-600 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ $entry['date'] ? $entry['date']->format('d M Y') : '—' }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $entry['date'] ? $entry['date']->format('h:i A') : '' }}</div>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if($entry['type'] === 'invoice')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span>{{ $entry['reference'] }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>PAYMENT</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-gray-700">
                                    <div class="font-medium">{{ $entry['description'] }}</div>
                                    @if($entry['reference'] && $entry['type'] !== 'invoice')
                                        <div class="text-[11px] text-gray-400">Ref: {{ $entry['reference'] }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-extrabold whitespace-nowrap">
                                    @if($entry['debit'] > 0)
                                        <span class="text-red-600">+₹{{ number_format($entry['debit'], 2) }}</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-extrabold whitespace-nowrap">
                                    @if($entry['credit'] > 0)
                                        <span class="text-emerald-600">-₹{{ number_format($entry['credit'], 2) }}</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-extrabold whitespace-nowrap {{ $entry['running_balance'] > 0 ? 'text-amber-800' : 'text-gray-900' }}">
                                    ₹{{ number_format($entry['running_balance'], 2) }}
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    @if($entry['sale_id'])
                                        <a href="{{ route($showSaleRoute, $entry['sale_id']) }}"
                                           class="text-brand-600 hover:text-brand-800 font-bold hover:underline inline-flex items-center gap-1"
                                           title="Open Invoice & Details">
                                            <span>View</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-400">
                                    No ledger activity recorded for this period.
                                </td>
                            </tr>
                        @endforelse

                        {{-- Closing Balance Summary Row --}}
                        <tr class="bg-gray-100/70 font-extrabold border-t-2 border-gray-200">
                            <td colspan="3" class="py-3 px-4 text-right uppercase tracking-wider text-gray-700 text-[11px]">
                                Closing Balance Due (as of {{ $to ? \Illuminate\Support\Carbon::parse($to)->format('d M Y') : now()->format('d M Y') }}):
                            </td>
                            <td class="py-3 px-4 text-right font-extrabold text-red-600">
                                +₹{{ number_format($totalDebit, 2) }}
                            </td>
                            <td class="py-3 px-4 text-right font-extrabold text-emerald-600">
                                -₹{{ number_format($totalCredit, 2) }}
                            </td>
                            <td class="py-3 px-4 text-right font-black text-sm {{ $closingBalance > 0 ? 'text-red-700' : 'text-emerald-700' }}">
                                ₹{{ number_format($closingBalance, 2) }}
                            </td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TAB 2: Invoices & Sales --}}
        <div x-show="activeTab === 'sales'" class="p-4 sm:p-6 space-y-4">
            <div class="overflow-x-auto rounded-xl border border-gray-100">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                            <th class="py-3 px-4">Invoice #</th>
                            <th class="py-3 px-4">Date & Time</th>
                            <th class="py-3 px-4">Items</th>
                            <th class="py-3 px-4">Payment</th>
                            <th class="py-3 px-4 text-right">Total</th>
                            <th class="py-3 px-4 text-right">Paid</th>
                            <th class="py-3 px-4 text-right">Balance Due</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($sales as $sale)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="py-3.5 px-4 font-bold text-gray-900 whitespace-nowrap">
                                    <a href="{{ route($showSaleRoute, $sale) }}" class="text-brand-600 hover:text-brand-800 hover:underline inline-flex items-center gap-1 group">
                                        <span>{{ $sale->invoice_number }}</span>
                                        <svg class="w-3 h-3 text-brand-400 group-hover:text-brand-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap">
                                    <div>{{ $sale->sale_date->format('d M Y') }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $sale->sale_date->format('h:i A') }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-gray-600">
                                    <span class="font-bold text-gray-900">{{ $sale->items->count() }}</span> items
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-gray-800">{{ \App\Models\PosSale::PAYMENT_METHODS[$sale->payment_method] ?? $sale->payment_method }}</div>
                                    <div class="mt-0.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $sale->paymentStatusBadge()['bg'] }}">
                                            {{ $sale->paymentStatusBadge()['label'] }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold text-gray-900">
                                    ₹{{ number_format($sale->grand_total, 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-right text-emerald-600 font-bold">
                                    ₹{{ number_format($sale->amount_paid, 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    @if((float) $sale->balance_due > 0)
                                        <span class="font-bold text-red-600">₹{{ number_format($sale->balance_due, 2) }}</span>
                                    @else
                                        <span class="text-gray-400">₹0.00</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $sale->statusBadge()['bg'] }}">
                                        {{ $sale->statusBadge()['label'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route($showSaleRoute, $sale) }}"
                                           class="px-2.5 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 rounded-lg font-bold text-xs inline-flex items-center gap-1 transition">
                                            <span>Open</span>
                                        </a>
                                        <a href="{{ route($receiptRoute, $sale) }}" target="_blank" title="80mm Thermal Receipt" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-gray-100 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        </a>
                                        <a href="{{ route($invoiceRoute, $sale) }}" target="_blank" title="Download PDF / Print A4" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-gray-100 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-gray-400">
                                    No POS sales recorded for this customer yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($sales->hasPages())
                <div class="mt-4">
                    {{ $sales->links() }}
                </div>
            @endif
        </div>

        {{-- TAB 3: Payment Track Record --}}
        <div x-show="activeTab === 'payments'" class="p-4 sm:p-6 space-y-4">
            <div class="overflow-x-auto rounded-xl border border-gray-100">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                            <th class="py-3 px-4">Payment Date & Time</th>
                            <th class="py-3 px-4">Invoice #</th>
                            <th class="py-3 px-4">Payment Method</th>
                            <th class="py-3 px-4 text-right">Amount Paid</th>
                            <th class="py-3 px-4">Reference / Notes</th>
                            <th class="py-3 px-4 text-right">Invoice Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($payments as $pmt)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ $pmt->created_at ? $pmt->created_at->format('d M Y') : '—' }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $pmt->created_at ? $pmt->created_at->format('h:i A') : '' }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-gray-900 whitespace-nowrap">
                                    @if($pmt->sale)
                                        <a href="{{ route($showSaleRoute, $pmt->sale) }}" class="text-brand-600 hover:text-brand-800 hover:underline">
                                            {{ $pmt->sale->invoice_number }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">
                                        {{ $pmt->methodLabel() }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right font-extrabold text-emerald-600 text-sm whitespace-nowrap">
                                    ₹{{ number_format($pmt->amount, 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-gray-600">
                                    {{ $pmt->reference ?: 'Payment settlement' }}
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    @if($pmt->sale)
                                        <a href="{{ route($showSaleRoute, $pmt->sale) }}" class="text-brand-600 hover:text-brand-800 font-bold hover:underline">
                                            Open Invoice
                                        </a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-400">
                                    No payment transactions recorded for this customer yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="mt-4">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Settle Due Balance Modal --}}
    <div x-show="settleModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
            <div x-show="settleModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/60 transition-opacity"
                 @click="settleModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="settleModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 border border-gray-100">

                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-900">Settle Customer Balance</h3>
                    </div>
                    <button type="button" @click="settleModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route($settleRoute, $customer) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <span class="text-xs text-gray-500 font-medium">Customer:</span>
                        <div class="text-sm font-extrabold text-gray-900 mt-0.5">{{ $customer->name }}</div>
                        <div class="text-xs text-amber-700 font-bold mt-0.5">
                            Outstanding Due: ₹{{ number_format($customer->outstanding_balance, 2) }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Amount to Settle (₹) *</label>
                        <input type="number"
                               name="amount"
                               step="0.01"
                               min="0.01"
                               max="{{ (float) $customer->outstanding_balance }}"
                               x-model="settleAmount"
                               required
                               class="w-full px-3 py-2 text-sm font-bold bg-white border border-gray-300 rounded-xl focus:ring-brand-500 focus:border-brand-500 shadow-2xs">
                        <span class="text-[11px] text-gray-400 mt-1 block">Payments will apply FIFO to clear unpaid invoices.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Payment Method *</label>
                        <select name="payment_method"
                                x-model="settleMethod"
                                required
                                class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-xl focus:ring-brand-500 focus:border-brand-500 shadow-2xs">
                            <option value="cash">Cash</option>
                            <option value="upi">UPI / QR Code</option>
                            <option value="card">Card (Debit/Credit)</option>
                            <option value="bank_transfer">Bank Transfer / NEFT</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Payment Note / Reference</label>
                        <input type="text"
                               name="note"
                               x-model="settleNote"
                               placeholder="e.g. Counter payment / GPay ref"
                               class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-xl focus:ring-brand-500 focus:border-brand-500 shadow-2xs">
                    </div>

                    <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                        <button type="button"
                                @click="settleModal = false"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold text-xs transition cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-sm transition cursor-pointer">
                            Confirm Payment Receipt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
