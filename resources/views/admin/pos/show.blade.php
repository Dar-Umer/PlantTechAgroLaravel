@extends('admin.layout')

@section('page-title', 'POS Invoice ' . $sale->invoice_number)

@section('content')
@php
    $showRoute = Route::has('pos.sales.show') ? 'pos.sales.show' : 'admin.pos.sales.show';
    $receiptRoute = Route::has('pos.receipt') ? 'pos.receipt' : 'admin.pos.receipt';
    $invoiceRoute = Route::has('pos.invoice') ? 'pos.invoice' : 'admin.pos.invoice';
    $cancelRoute = Route::has('pos.sales.cancel') ? 'pos.sales.cancel' : 'admin.pos.sales.cancel';
    $salesListRoute = Route::has('pos.sales') ? 'pos.sales' : 'admin.pos.sales';
    $paymentUrl = Route::has('pos.sales.payments.store') ? route('pos.sales.payments.store', $sale) : route('admin.pos.sales.payments.store', $sale);
@endphp

<div class="space-y-6 max-w-6xl pb-16" x-data="{ showPaymentModal: {{ request('action') === 'pay' ? 'true' : 'false' }} }">

    {{-- Breadcrumb & Navigation Bar --}}
    <div class="flex items-center justify-between text-xs text-gray-500">
        <div class="flex items-center gap-2">
            <a href="{{ route($salesListRoute) }}" class="hover:text-brand-600 font-medium inline-flex items-center gap-1 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                <span>Sales & Invoices</span>
            </a>
            <span>/</span>
            <span class="font-mono font-bold text-gray-800">{{ $sale->invoice_number }}</span>
        </div>
        <span class="text-gray-400">POS Terminal System</span>
    </div>

    {{-- Header with Prominent Title & Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div>
            <div class="flex items-center flex-wrap gap-2.5">
                <h2 class="text-2xl font-black font-mono text-gray-900 tracking-tight">{{ $sale->invoice_number }}</h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $sale->statusBadge()['bg'] }}">
                    {{ $sale->statusBadge()['label'] }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $sale->paymentStatusBadge()['bg'] }}">
                    {{ $sale->paymentStatusBadge()['label'] }}
                </span>
            </div>
            <p class="text-xs text-gray-500 mt-1 flex items-center gap-2">
                <span>Billed on <strong class="text-gray-700">{{ $sale->sale_date->format('d M Y, h:i A') }}</strong></span>
                <span>·</span>
                <span>Cashier: <strong class="text-gray-700">{{ $sale->cashier?->name ?? 'Admin Staff' }}</strong></span>
            </p>
        </div>

        <div class="flex items-center flex-wrap gap-2">
            <a href="{{ route($salesListRoute) }}" class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                &larr; Back to Sales
            </a>

            @if((float) $sale->balance_due > 0 && $sale->isCompleted())
                <button type="button"
                        @click="showPaymentModal = true"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition animate-pulse">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Receive Due (₹{{ number_format($sale->balance_due, 2) }})</span>
                </button>
            @endif

            <a href="{{ route($receiptRoute, $sale) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 shadow-sm transition">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>80mm Receipt</span>
            </a>

            <a href="{{ route($invoiceRoute, $sale) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-green-700 hover:bg-green-800 rounded-xl shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Retail Invoice (A4)</span>
            </a>

            @if($sale->isCompleted())
                <form method="POST" action="{{ route($cancelRoute, $sale) }}" onsubmit="return confirm('Cancel this entire sale? Deducted inventory and customer balance will be reversed.');" class="inline">
                    @csrf
                    <button type="submit" title="Cancel Sale & Reverse Stock" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition border border-transparent hover:border-red-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Info Cards Overview Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Customer Details --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-sm flex flex-col justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Customer Details</span>
                <h3 class="text-base font-extrabold text-gray-900 mt-1">{{ $sale->customer_name }}</h3>
                <div class="text-xs text-gray-500 mt-1 space-y-0.5">
                    @if($sale->customer_phone)
                        <p class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>{{ $sale->customer_phone }}</span>
                        </p>
                    @endif
                    @if($sale->customer_gstin)
                        <p class="font-mono text-[11px] text-gray-600">GSTIN: {{ $sale->customer_gstin }}</p>
                    @endif
                </div>
            </div>
            @if($sale->customer && (float) $sale->customer->outstanding_balance > 0)
                <div class="mt-3 pt-2.5 border-t border-amber-100 flex items-center justify-between text-xs">
                    <span class="text-amber-800 font-semibold">Account Total Due:</span>
                    <strong class="text-amber-700 font-extrabold">₹{{ number_format($sale->customer->outstanding_balance, 2) }}</strong>
                </div>
            @endif
        </div>

        {{-- Card 2: Invoice Totals & Balance --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-sm flex flex-col justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Invoice Total</span>
                <div class="text-2xl font-black text-gray-900 mt-1">₹{{ number_format($sale->grand_total, 2) }}</div>
                <div class="text-xs text-gray-500 mt-1 flex items-center justify-between">
                    <span>Paid: <strong class="text-gray-900 font-bold">₹{{ number_format($sale->amount_paid ?: $sale->grand_total, 2) }}</strong></span>
                    @if((float) $sale->change_amount > 0)
                        <span>Change: ₹{{ number_format($sale->change_amount, 2) }}</span>
                    @endif
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-gray-100">
                @if((float) $sale->balance_due > 0)
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-red-600 font-bold">Current Bill Due:</span>
                        <strong class="text-sm font-black text-red-600">₹{{ number_format($sale->balance_due, 2) }}</strong>
                    </div>
                @else
                    <div class="flex items-center gap-1.5 text-xs text-emerald-600 font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Invoice Fully Settled</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Card 3: Payment Mode & Billing --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-sm flex flex-col justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Payment Mode</span>
                <p class="text-base font-extrabold text-gray-900 mt-1">
                    {{ strtoupper(\App\Models\PosSale::PAYMENT_METHODS[$sale->payment_method] ?? $sale->payment_method) }}
                </p>
                <div class="text-xs text-gray-500 mt-1 space-y-0.5">
                    <p>Counter: <strong class="text-gray-700">Main POS Terminal</strong></p>
                    <p>Transactions: <strong class="text-gray-700">{{ max(1, $sale->payments->count()) }} recorded</strong></p>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-gray-100 text-[11px] text-gray-500">
                Status: <strong class="text-gray-800">{{ ucfirst($sale->payment_status) }}</strong>
            </div>
        </div>

        {{-- Card 4: Profit & Margins --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-sm flex flex-col justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Estimated Net Profit</span>
                <p class="text-2xl font-black text-emerald-700 mt-1">₹{{ number_format($sale->totalProfit(), 2) }}</p>
                @if($sale->labour_charges > 0 || $sale->labour_cost > 0)
                    <p class="text-xs text-gray-500 mt-1">
                        Labour Profit: <strong class="{{ $sale->labourProfit() >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">+₹{{ number_format($sale->labourProfit(), 2) }}</strong>
                    </p>
                @endif
            </div>
            <div class="mt-3 pt-2.5 border-t border-gray-100 text-[11px] text-gray-400">
                Tax: <strong class="text-gray-600">0% Retail Scheme</strong>
            </div>
        </div>
    </div>

    {{-- Outstanding Balance Alert Banner (When balance_due > 0) --}}
    @if((float) $sale->balance_due > 0 && $sale->isCompleted())
        <div class="bg-gradient-to-r from-amber-500 via-amber-600 to-amber-700 text-white rounded-2xl p-4 sm:p-5 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h4 class="text-sm font-extrabold">Outstanding Balance Pending: ₹{{ number_format($sale->balance_due, 2) }}</h4>
                    <p class="text-xs text-amber-100">Customer has an unpaid balance remaining on this invoice.</p>
                </div>
            </div>
            <button type="button"
                    @click="showPaymentModal = true"
                    class="px-4 py-2.5 bg-white text-amber-900 hover:bg-amber-50 font-black rounded-xl text-xs transition shadow-sm flex-shrink-0 flex items-center justify-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>Receive Payment Now</span>
            </button>
        </div>
    @endif

    {{-- Section: Proper Details of Products (Items Table) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <h3 class="text-base font-bold text-gray-900">Products & Items Sold</h3>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ $sale->items->count() }} line items</span>
            </div>
            <span class="text-xs text-gray-400">Multi-batch & FIFO tracked</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">Product Details</th>
                        <th class="py-3 px-4">Batch / Lot & Origin</th>
                        <th class="py-3 px-4 text-right">Unit Rate</th>
                        <th class="py-3 px-4 text-right">Quantity</th>
                        <th class="py-3 px-4 text-right">Line Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    @foreach($sale->items as $index => $item)
                        <tr class="hover:bg-gray-50/75 transition-colors">
                            <td class="py-3.5 px-4 text-center font-bold text-gray-400">{{ $index + 1 }}</td>
                            <td class="py-3.5 px-4">
                                <div class="font-extrabold text-gray-900 text-sm">{{ $item->product_name }}</div>
                                <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                    @if($item->product?->sku)
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded font-mono text-[10px] bg-gray-100 text-gray-700 border border-gray-200">
                                            SKU: {{ $item->product->sku }}
                                        </span>
                                    @endif
                                    @if($item->product?->type)
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] bg-slate-100 text-slate-600">
                                            {{ ucfirst($item->product->type) }}
                                        </span>
                                    @endif
                                    <span class="text-[10px] text-gray-400">Unit: {{ $item->unit }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-mono font-bold text-gray-800">
                                    {{ $item->batch?->batch_number ?? ($item->batch_id ? 'Batch #' . $item->batch_id : 'Auto Lot') }}
                                </div>
                                <div class="text-[10px] text-gray-500 mt-0.5 space-y-0.5">
                                    @if($item->supplier)
                                        <div>Supplier: <strong class="text-gray-700 font-sans">{{ $item->supplier->name }}</strong></div>
                                    @endif
                                    @if($item->batch?->expiry_date)
                                        <div class="flex items-center gap-1">
                                            <span>Expiry:</span>
                                            <span class="font-semibold {{ $item->batch->expiry_date->isPast() ? 'text-rose-600 font-bold' : ($item->batch->isNearExpiry(60) ? 'text-amber-600' : 'text-emerald-700') }}">
                                                {{ $item->batch->expiry_date->format('d M Y') }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <span class="font-bold text-gray-900 text-sm">₹{{ number_format($item->unit_price, 2) }}</span>
                                <span class="text-[10px] text-gray-400 block">per {{ $item->unit }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <span class="font-extrabold text-gray-900 text-sm">{{ number_format($item->quantity, 2) }}</span>
                                <span class="text-[10px] text-gray-500 block">{{ $item->unit }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <span class="font-black text-gray-900 text-base">₹{{ number_format($item->total_price, 2) }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Financial Summary Footer --}}
        <div class="p-5 bg-gray-50 border-t border-gray-100 flex flex-col md:flex-row md:items-start justify-between gap-6">
            {{-- Notes & Billing Remarks --}}
            <div class="flex-1 text-xs text-gray-500">
                <span class="font-bold uppercase tracking-wider text-gray-400 block mb-1">Invoice Notes & Terms</span>
                <p class="text-gray-600">Goods sold are non-refundable after dispatch. This is a computer-generated tax/retail receipt issued under POS Counter Billing.</p>
                @if($sale->notes)
                    <div class="mt-2.5 p-3 rounded-xl bg-white border border-gray-200/80 text-gray-800">
                        <strong class="font-bold text-gray-900 block mb-0.5">Counter Note:</strong>
                        <span>{{ $sale->notes }}</span>
                    </div>
                @endif
            </div>

            {{-- Calculations Breakdown Card --}}
            <div class="w-full md:w-80 space-y-2 text-xs text-gray-600 bg-white p-4 rounded-xl border border-gray-200/90 shadow-2xs">
                <div class="flex justify-between">
                    <span>Products Subtotal:</span>
                    <span class="font-bold text-gray-900">₹{{ number_format($sale->subtotal, 2) }}</span>
                </div>

                @if($sale->labour_charges > 0 || $sale->labour_cost > 0)
                    <div class="py-1.5 border-t border-dashed border-gray-200 space-y-1">
                        <div class="flex justify-between text-gray-800 font-semibold">
                            <span>Labour / Installation:</span>
                            <span class="font-bold text-gray-900">+₹{{ number_format($sale->labour_charges, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-500 text-[11px]">
                            <span>Internal Labour Cost:</span>
                            <span>₹{{ number_format($sale->labour_cost, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-[11px] font-bold {{ $sale->labourProfit() >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                            <span>Labour Profit:</span>
                            <span>+₹{{ number_format($sale->labourProfit(), 2) }}</span>
                        </div>
                    </div>
                @endif

                @if($sale->discount_amount > 0)
                    <div class="flex justify-between text-emerald-700 font-semibold">
                        <span>Order Discount:</span>
                        <span>-₹{{ number_format($sale->discount_amount, 2) }}</span>
                    </div>
                @endif

                @if($sale->round_off != 0)
                    <div class="flex justify-between text-gray-500">
                        <span>Round Off:</span>
                        <span>{{ $sale->round_off > 0 ? '+' : '' }}₹{{ number_format($sale->round_off, 2) }}</span>
                    </div>
                @endif

                <div class="flex justify-between pt-2 border-t border-gray-200 text-base font-black text-gray-900">
                    <span>Grand Total:</span>
                    <span class="text-green-800">₹{{ number_format($sale->grand_total, 2) }}</span>
                </div>

                <div class="flex justify-between text-gray-700 font-semibold pt-1">
                    <span>Amount Received:</span>
                    <span class="font-black text-emerald-700">₹{{ number_format($sale->amount_paid ?: $sale->grand_total, 2) }}</span>
                </div>

                @if((float) $sale->balance_due > 0)
                    <div class="flex justify-between text-red-600 font-bold bg-red-50 p-2 rounded-lg border border-red-200/80">
                        <span>Outstanding Balance Due:</span>
                        <span class="font-black">₹{{ number_format($sale->balance_due, 2) }}</span>
                    </div>
                @else
                    <div class="flex items-center justify-between text-emerald-700 font-bold bg-emerald-50 p-2 rounded-lg border border-emerald-200/80">
                        <span>Balance Due:</span>
                        <span>₹0.00 (Fully Settled)</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Section: Proper Track Record of Payments Received --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <h3 class="text-base font-bold text-gray-900">Payment Track Record & Transactions</h3>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                    {{ max(1, $sale->payments->count()) }} transaction(s)
                </span>
            </div>

            @if((float) $sale->balance_due > 0 && $sale->isCompleted())
                <button type="button"
                        @click="showPaymentModal = true"
                        class="px-3.5 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-sm inline-flex items-center gap-1 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Record Payment</span>
                </button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">Date & Time</th>
                        <th class="py-3 px-4">Payment Method</th>
                        <th class="py-3 px-4">Reference / Notes</th>
                        <th class="py-3 px-4 text-right">Amount Received</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    @if($sale->payments->count() > 0)
                        @foreach($sale->payments as $idx => $payment)
                            <tr class="hover:bg-gray-50/75 transition-colors">
                                <td class="py-3.5 px-4 text-center font-bold text-gray-400">#{{ $idx + 1 }}</td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-gray-900">{{ $payment->created_at->format('d M Y') }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $payment->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $methodBg = match($payment->method) {
                                            'cash' => 'bg-green-50 text-green-700 border-green-200',
                                            'upi' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                            'card' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'bank_transfer' => 'bg-purple-50 text-purple-700 border-purple-200',
                                            default => 'bg-gray-100 text-gray-700 border-gray-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold border {{ $methodBg }}">
                                        {{ $payment->methodLabel() }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-gray-600">
                                    {{ $payment->reference ?: 'Counter Cash / Direct Checkout' }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <span class="font-black text-emerald-600 text-sm">₹{{ number_format($payment->amount, 2) }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Received & Verified
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        {{-- Fallback for legacy single-payment sales where amount_paid exists --}}
                        <tr class="hover:bg-gray-50/75 transition-colors">
                            <td class="py-3.5 px-4 text-center font-bold text-gray-400">#1</td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-gray-900">{{ $sale->sale_date->format('d M Y') }}</div>
                                <div class="text-[11px] text-gray-400">{{ $sale->sale_date->format('h:i A') }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-200">
                                    {{ \App\Models\PosSale::PAYMENT_METHODS[$sale->payment_method] ?? ucfirst($sale->payment_method) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-gray-600">
                                {{ $sale->payment_reference ?: 'Initial Checkout Settlement' }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <span class="font-black text-emerald-600 text-sm">₹{{ number_format($sale->amount_paid ?: $sale->grand_total, 2) }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Received & Verified
                                </span>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-gray-50 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
            <div class="flex items-center gap-2">
                <span class="text-gray-500">Total Money Collected:</span>
                <strong class="text-emerald-700 font-extrabold text-sm">₹{{ number_format($sale->amount_paid ?: $sale->grand_total, 2) }}</strong>
            </div>

            @if((float) $sale->balance_due > 0)
                <div class="flex items-center gap-2">
                    <span class="text-red-600 font-semibold">Remaining Due to Collect:</span>
                    <strong class="text-red-600 font-black text-sm">₹{{ number_format($sale->balance_due, 2) }}</strong>
                </div>
            @else
                <div class="text-emerald-700 font-bold flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>All payments settled. No dues remaining.</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Record Payment Modal (Option to receive the outstanding) --}}
    @if((float) $sale->balance_due > 0 && $sale->isCompleted())
    <div x-show="showPaymentModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl" @click.away="showPaymentModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Receive Outstanding Payment</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Invoice <span class="font-mono font-bold text-gray-900">{{ $sale->invoice_number }}</span></p>
                </div>
                <button type="button" @click="showPaymentModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="bg-amber-50 p-3.5 rounded-xl border border-amber-200 text-xs text-amber-900 space-y-1">
                <div class="flex justify-between">
                    <span>Customer:</span>
                    <strong class="font-bold">{{ $sale->customer_name }}</strong>
                </div>
                <div class="flex justify-between items-center pt-1 border-t border-amber-200/60">
                    <span class="font-semibold">Current Bill Balance Due:</span>
                    <strong class="text-base font-black text-amber-700">₹{{ number_format($sale->balance_due, 2) }}</strong>
                </div>
            </div>

            <form method="POST" action="{{ $paymentUrl }}" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Payment Amount (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" name="amount" value="{{ (float) $sale->balance_due }}" min="0.01" max="{{ (float) $sale->balance_due }}" step="0.01" required
                           class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-extrabold focus:border-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Payment Mode <span class="text-rose-500">*</span></label>
                    <select name="payment_method" required class="w-full rounded-xl border border-gray-200 px-3 py-2 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                        <option value="cash">Cash</option>
                        <option value="upi">UPI / Online QR</option>
                        <option value="card">Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Reference / Note (Optional)</label>
                    <input type="text" name="reference" placeholder="e.g. Counter payment, GPay Ref, UPI Txn ID"
                           class="w-full rounded-xl border border-gray-200 px-3 py-2 text-xs focus:border-brand-500 focus:outline-none">
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-gray-100">
                    <button type="button" @click="showPaymentModal = false" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-sm">
                        Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
