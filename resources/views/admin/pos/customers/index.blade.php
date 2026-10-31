@extends('admin.layout')

@section('page-title', 'POS Customers Directory')

@section('content')
@php
    $showRoute = Route::has('pos.customers.show') ? 'pos.customers.show' : 'admin.pos.customers.show';
    $statementRoute = Route::has('pos.customers.statement') ? 'pos.customers.statement' : 'admin.pos.customers.statement';
    $storeRoute = Route::has('pos.customers.store') ? route('pos.customers.store') : route('admin.pos.customers.store');
    $customerBaseUrl = url('/customers');
@endphp

<div class="space-y-6 pb-12"
     x-data="{
        addModal: false,
        settleModal: false,
        settleCustId: null,
        settleCustName: '',
        settleDue: 0,
        settleAmount: 0,
        settleMethod: 'cash',
        settleNote: '',
        openSettleModal(id, name, due) {
            this.settleCustId = id;
            this.settleCustName = name;
            this.settleDue = Number(due);
            this.settleAmount = Number(due);
            this.settleMethod = 'cash';
            this.settleNote = '';
            this.settleModal = true;
        }
     }">

    {{-- Top Action Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = true" class="lg:hidden p-2 text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-xl transition" title="Open Menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">POS Retail Customers</h2>
                <p class="text-sm text-gray-500 mt-0.5">Counter client ledger, contact profiles, retail purchase histories, and credit dues.</p>
            </div>
        </div>
        <div class="flex items-center flex-wrap gap-2.5">
            <button type="button"
                    @click="addModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 rounded-xl shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>+ Add Customer</span>
            </button>
            <a href="{{ route('pos.terminal') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-gray-700 bg-white hover:bg-gray-50 border border-gray-200 rounded-xl shadow-xs transition">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <span>Open Terminal</span>
            </a>
            <a href="{{ route('pos.sales') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-gray-700 bg-white hover:bg-gray-50 border border-gray-200 rounded-xl shadow-xs transition">
                <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>Sales Invoices</span>
            </a>
        </div>
    </div>

    {{-- Top KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total POS Clients</span>
            <p class="text-2xl font-extrabold text-gray-900 mt-1 font-sans">{{ number_format($totalCustomers) }}</p>
            <p class="text-xs text-gray-500 font-medium mt-1">Registered counter accounts</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-amber-600">Clients With Balance Due</span>
            <p class="text-2xl font-extrabold text-amber-600 mt-1 font-sans">{{ number_format($duesCount) }}</p>
            <p class="text-xs text-gray-500 font-medium mt-1">Pending credit settlements</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-rose-500">Total Outstanding Balance</span>
            <p class="text-2xl font-extrabold text-rose-600 mt-1 font-sans">₹{{ number_format($totalDue, 2) }}</p>
            <p class="text-xs text-gray-500 font-medium mt-1">Total pending receivables</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Total Retail Purchases</span>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1 font-sans">₹{{ number_format($totalSpent, 2) }}</p>
            <p class="text-xs text-gray-500 font-medium mt-1">Lifetime customer purchases</p>
        </div>
    </div>

    {{-- Search & Filter Bar --}}
    <form method="GET" action="{{ route('pos.customers.index') }}" class="bg-white rounded-2xl shadow-xs border border-gray-100 p-4 space-y-3">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[220px]">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text"
                           name="q"
                           value="{{ request('q') }}"
                           placeholder="Search by customer name, phone, email, GSTIN, or city..."
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 focus:bg-white transition">
                </div>
            </div>
            <div>
                <select name="status" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-xs font-semibold text-gray-700 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 focus:bg-white transition">
                    <option value="">All Accounts</option>
                    <option value="dues" {{ request('status') === 'dues' ? 'selected' : '' }}>Has Balance Due (> ₹0)</option>
                    <option value="cleared" {{ request('status') === 'cleared' ? 'selected' : '' }}>Cleared / Zero Due</option>
                    <option value="b2b" {{ request('status') === 'b2b' ? 'selected' : '' }}>B2B (GST Registered)</option>
                </select>
            </div>
            <div>
                <select name="sort" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-xs font-semibold text-gray-700 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 focus:bg-white transition">
                    <option value="due_desc" {{ request('sort', 'due_desc') === 'due_desc' ? 'selected' : '' }}>Sort: Highest Due First</option>
                    <option value="due_asc" {{ request('sort') === 'due_asc' ? 'selected' : '' }}>Sort: Lowest Due First</option>
                    <option value="spent_desc" {{ request('sort') === 'spent_desc' ? 'selected' : '' }}>Sort: Highest Spend</option>
                    <option value="orders_desc" {{ request('sort') === 'orders_desc' ? 'selected' : '' }}>Sort: Most Orders</option>
                    <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Sort: Customer Name (A-Z)</option>
                    <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Sort: Recently Added</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-xl transition shadow-xs">
                    Apply Filter
                </button>
                @if(request()->hasAny(['q', 'status', 'sort']))
                    <a href="{{ route('pos.customers.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl transition">
                        Reset
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Customers Directory Table --}}
    <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50/75 border-b border-gray-100 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                        <th class="py-3.5 px-4">Customer Details</th>
                        <th class="py-3.5 px-4">Contact & Location</th>
                        <th class="py-3.5 px-4 text-center">Orders</th>
                        <th class="py-3.5 px-4 text-right">Lifetime Spent</th>
                        <th class="py-3.5 px-4 text-right">Balance Due</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($customers as $customer)
                        @php
                            $bal = (float) $customer->outstanding_balance;
                            $hasDue = $bal > 0.009;
                            $profileUrl = route($showRoute, $customer);
                            $stmtUrl = route($statementRoute, $customer);
                        @endphp
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            {{-- Customer Name & Avatar --}}
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-extrabold text-sm {{ $hasDue ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-brand-50 text-brand-700 border border-brand-100' }} flex-shrink-0">
                                        {{ strtoupper(substr($customer->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ $profileUrl }}" class="font-bold text-gray-900 hover:text-brand-600 transition block truncate text-sm">
                                            {{ $customer->name }}
                                        </a>
                                        <div class="flex items-center gap-1.5 flex-wrap mt-0.5">
                                            @if($customer->customer_id)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200/60" title="Linked CRM Orchardist">
                                                    Farmer Account
                                                </span>
                                            @endif
                                            @if($customer->gstin)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/60" title="GSTIN: {{ $customer->gstin }}">
                                                    GST: {{ $customer->gstin }}
                                                </span>
                                            @endif
                                            <span class="text-[11px] text-gray-400">ID: #{{ $customer->id }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Contact & Address --}}
                            <td class="py-4 px-4">
                                <div class="text-xs space-y-0.5">
                                    @if($customer->phone)
                                        <div class="font-semibold text-gray-800 flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            <span>{{ $customer->phone }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic">No phone</span>
                                    @endif
                                    @if($customer->address)
                                        <div class="text-gray-500 truncate max-w-[200px]" title="{{ $customer->address }}">
                                            {{ $customer->address }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Orders --}}
                            <td class="py-4 px-4 text-center">
                                <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg text-xs font-bold bg-gray-100 text-gray-700">
                                    {{ number_format($customer->orders_count) }}
                                </span>
                            </td>

                            {{-- Lifetime Spend --}}
                            <td class="py-4 px-4 text-right">
                                <span class="font-bold text-gray-900 text-sm">
                                    ₹{{ number_format($customer->total_spent, 2) }}
                                </span>
                            </td>

                            {{-- Outstanding Due --}}
                            <td class="py-4 px-4 text-right">
                                @if($hasDue)
                                    <div>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                            ₹{{ number_format($bal, 2) }}
                                        </span>
                                        <p class="text-[10px] text-rose-500 font-semibold mt-0.5">Due Pending</p>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Cleared / ₹0
                                    </span>
                                @endif
                            </td>

                            {{-- Action Buttons --}}
                            <td class="py-4 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    @if($hasDue)
                                        <button type="button"
                                                @click="openSettleModal({{ $customer->id }}, '{{ addslashes($customer->name) }}', {{ $bal }})"
                                                class="px-2.5 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition"
                                                title="Settle Outstanding Due">
                                            Receive Due
                                        </button>
                                    @endif

                                    <a href="{{ $profileUrl }}"
                                       class="px-3 py-1.5 text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 rounded-lg transition"
                                       title="View Profile & Ledger">
                                        Profile
                                    </a>

                                    <a href="{{ $stmtUrl }}"
                                       target="_blank"
                                       class="p-1.5 text-gray-500 hover:text-gray-800 hover:bg-gray-100 border border-gray-200 rounded-lg transition inline-flex items-center"
                                       title="Print Statement">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-500">
                                <div class="max-w-sm mx-auto space-y-3">
                                    <div class="w-12 h-12 bg-gray-100 text-gray-400 rounded-2xl flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    </div>
                                    <p class="font-bold text-gray-700">No POS customers found</p>
                                    <p class="text-xs text-gray-400">Try adjusting your search keywords or filter criteria, or add a new customer.</p>
                                    <button type="button" @click="addModal = true" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition">
                                        + Add Customer
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

    {{-- Quick Settle Balance Due Modal --}}
    <div x-show="settleModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl" @click.away="settleModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Settle Customer Balance Due</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Collects outstanding balance and applies payment to unpaid invoices.</p>
                </div>
                <button type="button" @click="settleModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="bg-amber-50 p-3.5 rounded-xl border border-amber-200 text-xs text-amber-900 space-y-1">
                <div class="flex justify-between">
                    <span>Customer:</span>
                    <strong class="font-bold" x-text="settleCustName"></strong>
                </div>
                <div class="flex justify-between items-center pt-1 border-t border-amber-200/60">
                    <span class="font-semibold">Current Balance Due:</span>
                    <strong class="text-base font-black text-amber-700 font-sans" x-text="'₹' + Number(settleDue).toFixed(2)"></strong>
                </div>
            </div>

            <form method="POST" :action="'{{ $customerBaseUrl }}/' + settleCustId + '/settle-balance'" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Amount to Receive (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" name="amount" x-model.number="settleAmount" min="0.01" :max="settleDue" step="0.01" required
                           class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-extrabold focus:border-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Payment Mode <span class="text-rose-500">*</span></label>
                    <select name="payment_method" x-model="settleMethod" required class="w-full rounded-xl border border-gray-200 px-3 py-2 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                        <option value="cash">Cash</option>
                        <option value="upi">UPI / Online QR</option>
                        <option value="card">Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Reference / Note (Optional)</label>
                    <input type="text" name="note" x-model="settleNote" placeholder="e.g. Counter settlement, GPay UPI ref #, etc."
                           class="w-full rounded-xl border border-gray-200 px-3 py-2 text-xs focus:border-brand-500 focus:outline-none">
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-gray-100">
                    <button type="button" @click="settleModal = false" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-xs">
                        Record Settlement
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add POS Customer Modal --}}
    <div x-show="addModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl" @click.away="addModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Add New POS Retail Customer</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Register a counter client for retail billing and credit tracking.</p>
                </div>
                <button type="button" @click="addModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ $storeRoute }}" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Customer Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Mohd Altaf"
                           class="w-full rounded-xl border border-gray-200 px-3 py-2 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Mobile / Phone</label>
                        <input type="text" name="phone" placeholder="e.g. 9876543210"
                               class="w-full rounded-xl border border-gray-200 px-3 py-2 text-xs focus:border-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" placeholder="e.g. client@example.com"
                               class="w-full rounded-xl border border-gray-200 px-3 py-2 text-xs focus:border-brand-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">GSTIN (Optional, for B2B Retail Invoices)</label>
                    <input type="text" name="gstin" placeholder="e.g. 01AAAAA0000A1Z5"
                           class="w-full rounded-xl border border-gray-200 px-3 py-2 text-xs uppercase focus:border-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Billing Address / Village / City</label>
                    <textarea name="address" rows="2" placeholder="e.g. Shop #4, Main Market, Sopore"
                              class="w-full rounded-xl border border-gray-200 px-3 py-2 text-xs focus:border-brand-500 focus:outline-none"></textarea>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-gray-100">
                    <button type="button" @click="addModal = false" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-xs">
                        Create Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
