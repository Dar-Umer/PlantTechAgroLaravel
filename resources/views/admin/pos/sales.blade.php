@extends('admin.layout')

@section('page-title', 'POS Sales & Invoices')

@section('content')
@php
    $showRoute = Route::has('pos.sales.show') ? 'pos.sales.show' : 'admin.pos.sales.show';
    $receiptRoute = Route::has('pos.receipt') ? 'pos.receipt' : 'admin.pos.receipt';
    $invoiceRoute = Route::has('pos.invoice') ? 'pos.invoice' : 'admin.pos.invoice';
    $cancelRoute = Route::has('pos.sales.cancel') ? 'pos.sales.cancel' : 'admin.pos.sales.cancel';
    $customerShowRoute = Route::has('pos.customers.show') ? 'pos.customers.show' : 'admin.pos.customers.show';
    $paymentUrlBase = Route::has('pos.sales.payments.store') ? url('/sales') : url('/admin/pos/sales');
@endphp
<div class="space-y-6 pb-12"
     x-data="{
        receiveModal: false,
        paySaleId: null,
        payInvoiceNo: '',
        payCustomer: '',
        payDue: 0,
        payAmount: 0,
        payMethod: 'cash',
        payRef: '',
        openReceiveModal(id, invoiceNo, customer, due) {
            this.paySaleId = id;
            this.payInvoiceNo = invoiceNo;
            this.payCustomer = customer;
            this.payDue = Number(due);
            this.payAmount = Number(due);
            this.payMethod = 'cash';
            this.payRef = '';
            this.receiveModal = true;
        }
     }">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = true" class="lg:hidden p-2 text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-xl transition" title="Open Menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">POS Sales & Invoices</h2>
                <p class="text-sm text-gray-500 mt-0.5">Manage all counter sales, receipts, and retail sales invoices.</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <x-admin.button href="{{ route('pos.terminal') }}" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>Open Terminal</x-admin.button>
            <form method="POST" action="{{ route('pos.logout') }}" class="inline">
                @csrf
                <button type="submit" title="Logout" class="px-3.5 py-2 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-xl border border-red-200 transition">Logout</button>
            </form>
        </div>
    </div>

    {{-- Today's KPI Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <span class="text-xs font-semibold uppercase text-gray-400">Today's POS Sales</span>
            <p class="text-2xl font-extrabold text-gray-900 mt-1">₹{{ number_format($todaySales, 2) }}</p>
            <p class="text-xs text-emerald-600 font-medium mt-1">Completed counter revenue</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <span class="text-xs font-semibold uppercase text-gray-400">Transactions Today</span>
            <p class="text-2xl font-extrabold text-brand-600 mt-1">{{ number_format($todayOrders) }}</p>
            <p class="text-xs text-gray-500 mt-1">Customers billed</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <span class="text-xs font-semibold uppercase text-gray-400">Cash Collected</span>
            <p class="text-2xl font-extrabold text-green-600 mt-1">₹{{ number_format($cashSales, 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">Cash drawer amount</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <span class="text-xs font-semibold uppercase text-gray-400">Total Outstanding Due</span>
            <p class="text-2xl font-extrabold text-red-600 mt-1">₹{{ number_format($totalDue, 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">Unpaid customer balance</p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('pos.sales') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-3">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by Invoice #, customer name, phone..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
            </div>
            <div>
                <select name="payment_method" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    <option value="">All Payment Modes</option>
                    @foreach(\App\Models\PosSale::PAYMENT_METHODS as $key => $lbl)
                        <option value="{{ $key }}" {{ request('payment_method') == $key ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="payment_status" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    <option value="">All Payment Statuses</option>
                    @foreach(\App\Models\PosSale::PAYMENT_STATUSES as $key => $lbl)
                        <option value="{{ $key }}" {{ request('payment_status') == $key ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="status" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    <option value="">All Statuses</option>
                    @foreach(\App\Models\PosSale::STATUSES as $key => $lbl)
                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <input type="date" name="from" value="{{ request('from') }}" class="rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm text-gray-700">
            </div>
            <div>
                <input type="date" name="to" value="{{ request('to') }}" class="rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm text-gray-700">
            </div>
            <button type="submit" class="px-4 py-2.5 bg-gray-900 hover:bg-gray-800 text-white rounded-xl text-sm font-semibold transition">
                Filter
            </button>
            @if(request()->anyFilled(['q', 'payment_method', 'payment_status', 'status', 'from', 'to']))
                <a href="{{ route('pos.sales') }}" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</a>
            @endif
        </div>
    </form>

    {{-- Sales Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                    <tr>
                        <th class="py-3.5 px-4">Invoice #</th>
                        <th class="py-3.5 px-4">Date & Time</th>
                        <th class="py-3.5 px-4">Customer</th>
                        <th class="py-3.5 px-4">Items</th>
                        <th class="py-3.5 px-4">Payment</th>
                        <th class="py-3.5 px-4 text-right">Grand Total</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-gray-50/75 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-gray-900">
                                <a href="{{ route($showRoute, $sale) }}"
                                   class="font-mono font-bold text-brand-600 hover:text-brand-800 hover:underline inline-flex items-center gap-1 group/inv"
                                   title="Open Invoice & Details">
                                    <span>{{ $sale->invoice_number }}</span>
                                    <svg class="w-3.5 h-3.5 text-gray-400 group-hover/inv:text-brand-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </td>
                            <td class="py-3.5 px-4 text-xs text-gray-600">
                                <div>{{ $sale->sale_date->format('d M Y') }}</div>
                                <div class="text-gray-400">{{ $sale->sale_date->format('h:i A') }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($sale->customer)
                                    <a href="{{ route($customerShowRoute, $sale->customer) }}"
                                       class="font-semibold text-brand-700 hover:text-brand-900 hover:underline inline-flex items-center gap-1 group"
                                       title="View Customer Profile, Ledger & Transactions">
                                        <span>{{ $sale->customer_name }}</span>
                                        <svg class="w-3 h-3 text-brand-400 group-hover:text-brand-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                @else
                                    <div class="font-semibold text-gray-900">{{ $sale->customer_name }}</div>
                                @endif
                                @if($sale->customer_phone)
                                    <div class="text-xs text-gray-400">{{ $sale->customer_phone }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-xs text-gray-600">
                                <span class="font-semibold text-gray-900">{{ $sale->items->count() }}</span> item(s)
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                <div class="font-medium text-gray-800">{{ \App\Models\PosSale::PAYMENT_METHODS[$sale->payment_method] ?? $sale->payment_method }}</div>
                                <div class="mt-0.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $sale->paymentStatusBadge()['bg'] }}">
                                        {{ $sale->paymentStatusBadge()['label'] }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <span class="font-extrabold text-gray-900 block">₹{{ number_format($sale->grand_total, 2) }}</span>
                                @if((float) $sale->balance_due > 0)
                                    <span class="text-[11px] font-bold text-red-600 block">Due: ₹{{ number_format($sale->balance_due, 2) }}</span>
                                    @if($sale->isCompleted())
                                        <button type="button"
                                                @click="openReceiveModal({{ $sale->id }}, '{{ $sale->invoice_number }}', '{{ addslashes($sale->customer_name) }}', {{ (float) $sale->balance_due }})"
                                                class="mt-1 inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition shadow-2xs cursor-pointer">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                            <span>Receive Due</span>
                                        </button>
                                    @endif
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $sale->statusBadge()['bg'] }}">
                                    {{ $sale->statusBadge()['label'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route($showRoute, $sale) }}"
                                       class="px-2.5 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 hover:text-brand-900 rounded-lg font-bold text-xs inline-flex items-center gap-1 transition shadow-2xs"
                                       title="Open Full Invoice & Payment Track Record">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Open</span>
                                    </a>
                                    <a href="{{ route($receiptRoute, $sale) }}" target="_blank" title="80mm Thermal Receipt" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-gray-100 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    </a>
                                    <a href="{{ route($invoiceRoute, $sale) }}" target="_blank" title="Retail Sales Invoice (A4)" class="p-1.5 text-gray-500 hover:text-green-600 hover:bg-gray-100 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </a>
                                    @if($sale->isCompleted())
                                        <form method="POST" action="{{ route($cancelRoute, $sale) }}" onsubmit="return confirm('Cancel this sale? Deducted inventory and customer balance will be reversed.');" class="inline">
                                            @csrf
                                            <button type="submit" title="Cancel Sale & Reverse Stock" class="p-1.5 text-red-600 bg-red-50 hover:bg-red-100 hover:text-red-700 rounded-lg transition border border-red-200">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <p class="text-sm font-medium">No POS sales recorded yet</p>
                                <a href="{{ route('pos.terminal') }}" class="mt-2 inline-block text-xs font-bold text-brand-600 hover:underline">Open Terminal to start selling &rarr;</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $sales->links() }}
            </div>
        @endif
    </div>

    {{-- Quick Receive Due Modal --}}
    <div x-show="receiveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl" @click.away="receiveModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Receive Outstanding Due</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Invoice <span class="font-mono font-bold text-slate-800" x-text="payInvoiceNo"></span></p>
                </div>
                <button type="button" @click="receiveModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="bg-amber-50 p-3.5 rounded-xl border border-amber-200 text-xs text-amber-900 space-y-1">
                <div class="flex justify-between">
                    <span>Customer:</span>
                    <strong class="font-bold" x-text="payCustomer"></strong>
                </div>
                <div class="flex justify-between items-center pt-1 border-t border-amber-200/60">
                    <span class="font-semibold">Current Balance Due:</span>
                    <strong class="text-base font-black text-amber-700" x-text="'₹' + Number(payDue).toFixed(2)"></strong>
                </div>
            </div>

            <form method="POST" :action="'{{ $paymentUrlBase }}/' + paySaleId + '/payments'" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Amount to Receive (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" name="amount" x-model.number="payAmount" min="0.01" :max="payDue" step="0.01" required
                           class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-extrabold focus:border-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Payment Mode <span class="text-rose-500">*</span></label>
                    <select name="payment_method" x-model="payMethod" required class="w-full rounded-xl border border-gray-200 px-3 py-2 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                        <option value="cash">Cash</option>
                        <option value="upi">UPI / Online QR</option>
                        <option value="card">Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Reference / Note (Optional)</label>
                    <input type="text" name="reference" x-model="payRef" placeholder="e.g. Counter cash, GPay UPI ref #, etc."
                           class="w-full rounded-xl border border-gray-200 px-3 py-2 text-xs focus:border-brand-500 focus:outline-none">
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-gray-100">
                    <button type="button" @click="receiveModal = false" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-sm">
                        Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
