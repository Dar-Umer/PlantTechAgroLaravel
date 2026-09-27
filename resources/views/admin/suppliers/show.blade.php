@extends('admin.layout')

@section('page-title', $supplier->name . ' — Supplier Profile & Ledger')

@section('content')
<div x-data="{ showPaymentModal: false, selectedBillId: '' }" class="space-y-6">
    <!-- Header & Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.suppliers.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $supplier->name }}</h2>
                    @if($supplier->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">Active</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Inactive</span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 mt-0.5">Supplier Ledger, Inward Stock History & Payment Reconciliation</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @if($unpaidBills->isNotEmpty())
                <button type="button" @click="showPaymentModal = true; selectedBillId = ''"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Record Payment
                </button>
            @endif

            <x-admin.button href="{{ route('admin.purchase-bills.create', ['supplier_id' => $supplier->id]) }}" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>
                + New Purchase Bill
            </x-admin.button>

            <x-admin.button href="{{ route('admin.suppliers.edit', $supplier) }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'>
                Edit Supplier
            </x-admin.button>
        </div>
    </div>

    <!-- Supplier Details Info Box -->
    <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-sm">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 block mb-1">Contact Person</span>
                <span class="font-medium text-gray-900 block text-base">{{ $supplier->contact_person ?: '—' }}</span>
                @if($supplier->phone)
                    <div class="mt-1 flex items-center gap-1.5 text-gray-600">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <a href="tel:{{ $supplier->phone }}" class="hover:text-brand-600 text-xs font-medium">{{ $supplier->phone }}</a>
                    </div>
                @endif
                @if($supplier->email)
                    <div class="mt-0.5 flex items-center gap-1.5 text-gray-600">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <a href="mailto:{{ $supplier->email }}" class="hover:text-brand-600 text-xs truncate max-w-[200px]">{{ $supplier->email }}</a>
                    </div>
                @endif
            </div>

            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 block mb-1">GSTIN & Tax Info</span>
                <span class="font-medium text-gray-900 block">{{ $supplier->gst_no ?: 'Unregistered / Not Provided' }}</span>
                @if($supplier->payment_terms)
                    <span class="text-xs text-gray-500 mt-1 block">Terms: {{ $supplier->payment_terms }}</span>
                @endif
            </div>

            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 block mb-1">Address</span>
                <p class="text-gray-700 leading-relaxed">
                    {{ $supplier->address ?: 'No address specified' }}
                    @if($supplier->city || $supplier->state)
                        <span class="block text-gray-500 text-xs mt-0.5">{{ implode(', ', array_filter([$supplier->city, $supplier->state, $supplier->postal_code])) }}</span>
                    @endif
                </p>
            </div>

            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 block mb-1">Bank / Settlement Notes</span>
                <p class="text-gray-600 text-xs whitespace-pre-line">{{ $supplier->notes ?: 'No additional notes provided.' }}</p>
            </div>
        </div>
    </div>

    <!-- Financial KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Purchased</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">₹{{ number_format($metrics['total_purchased'], 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">Inward purchase value</p>
        </div>

        <div class="bg-gradient-to-br from-emerald-50/50 via-white to-emerald-50/20 rounded-2xl shadow-xs border border-emerald-100/80 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Total Paid</p>
            <p class="text-2xl font-bold text-emerald-600 mt-2">₹{{ number_format($metrics['total_paid'], 2) }}</p>
            <p class="text-xs text-emerald-600/70 mt-1">Settled payments</p>
        </div>

        <div class="bg-gradient-to-br {{ $metrics['balance_due'] > 0 ? 'from-red-50/60 via-white to-red-50/30 border-red-200' : 'from-gray-50 via-white to-gray-50 border-gray-100' }} rounded-2xl shadow-xs border p-5">
            <p class="text-xs font-semibold uppercase tracking-wider {{ $metrics['balance_due'] > 0 ? 'text-red-700 font-bold' : 'text-gray-500' }}">Balance Due</p>
            <p class="text-2xl font-bold {{ $metrics['balance_due'] > 0 ? 'text-red-600' : 'text-gray-900' }} mt-2">
                ₹{{ number_format($metrics['balance_due'], 2) }}
            </p>
            <p class="text-xs {{ $metrics['balance_due'] > 0 ? 'text-red-500' : 'text-gray-400' }} mt-1">
                {{ $metrics['balance_due'] > 0 ? 'Payable to supplier' : 'All clear' }}
            </p>
        </div>

        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Bills</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $metrics['bills_count'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Purchase invoices</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Stock Received</p>
            <p class="text-2xl font-bold text-blue-600 mt-2">{{ number_format($metrics['inward_qty'], 1) }}</p>
            <p class="text-xs text-gray-400 mt-1">Units received inward</p>
        </div>
    </div>

    <!-- Ledger Tabs -->
    <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
        <div class="border-b border-gray-100 px-6 pt-4 flex gap-6 overflow-x-auto">
            <a href="{{ route('admin.suppliers.show', [$supplier, 'tab' => 'bills']) }}"
               class="pb-3 text-sm font-semibold border-b-2 transition flex items-center gap-2 whitespace-nowrap {{ $tab === 'bills' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-200' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Purchase Bills
                <span class="ml-1 px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">{{ $supplier->purchaseBills()->count() }}</span>
            </a>

            <a href="{{ route('admin.suppliers.show', [$supplier, 'tab' => 'stock']) }}"
               class="pb-3 text-sm font-semibold border-b-2 transition flex items-center gap-2 whitespace-nowrap {{ $tab === 'stock' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-200' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Inward Stock Logs
                <span class="ml-1 px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">{{ $supplier->stockMovements()->where('type', 'in')->count() }}</span>
            </a>

            <a href="{{ route('admin.suppliers.show', [$supplier, 'tab' => 'payments']) }}"
               class="pb-3 text-sm font-semibold border-b-2 transition flex items-center gap-2 whitespace-nowrap {{ $tab === 'payments' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-200' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Payment History
                <span class="ml-1 px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">{{ $supplier->payments()->count() }}</span>
            </a>

            <a href="{{ route('admin.suppliers.show', [$supplier, 'tab' => 'products']) }}"
               class="pb-3 text-sm font-semibold border-b-2 transition flex items-center gap-2 whitespace-nowrap {{ $tab === 'products' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-200' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                Supplied Products
                <span class="ml-1 px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">{{ $supplier->products()->count() }}</span>
            </a>
        </div>

        <!-- Tab 1: Purchase Bills -->
        @if($tab === 'bills')
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50/80 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Bill Number</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Supplier Inv #</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Bill Date</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center">Items</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Grand Total</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Paid</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Balance Due</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center">Status</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($bills as $bill)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.purchase-bills.show', $bill) }}" class="font-bold text-gray-900 hover:text-brand-600">
                                        {{ $bill->bill_number }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-xs font-medium text-gray-600">
                                    {{ $bill->supplier_invoice_no ?: '—' }}
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $bill->bill_date->format('d M, Y') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">
                                        {{ $bill->items->count() }} items
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-gray-900">
                                    ₹{{ number_format($bill->total_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right text-emerald-600 font-semibold">
                                    ₹{{ number_format($bill->paid_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($bill->balance_due > 0)
                                        <span class="font-bold text-red-600">₹{{ number_format($bill->balance_due, 2) }}</span>
                                    @else
                                        <span class="text-emerald-600 text-xs font-semibold">Paid in full</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $bill->paymentStatusBadgeClass() }}">
                                        {{ ucfirst($bill->payment_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($bill->balance_due > 0)
                                            <button type="button"
                                                    @click="showPaymentModal = true; selectedBillId = '{{ $bill->id }}'"
                                                    class="px-2.5 py-1 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition">
                                                Pay
                                            </button>
                                        @endif
                                        <a href="{{ route('admin.purchase-bills.print', $bill) }}" target="_blank"
                                           class="p-1.5 text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition" title="Print Bill">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        </a>
                                        <a href="{{ route('admin.purchase-bills.show', $bill) }}"
                                           class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition" title="View Details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                    No purchase bills recorded for this supplier yet.
                                    <div class="mt-2">
                                        <x-admin.button href="{{ route('admin.purchase-bills.create', ['supplier_id' => $supplier->id]) }}" variant="primary" size="sm">
                                            Create First Bill
                                        </x-admin.button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($bills && $bills->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $bills->links() }}
                    </div>
                @endif
            </div>
        @endif

        <!-- Tab 2: Stock Movements Inward -->
        @if($tab === 'stock')
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50/80 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Date</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Product</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Batch / Lot #</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Qty Received</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Unit Cost</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Line Total</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Bill Reference</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($stockMovements as $mv)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-6 py-4 text-xs text-gray-600">
                                    {{ $mv->created_at->format('d M, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ $mv->product->name ?? '—' }}
                                    @if($mv->product?->sku)
                                        <span class="block text-xs text-gray-400">SKU: {{ $mv->product->sku }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs font-medium">
                                    @if($mv->batch)
                                        <span class="font-semibold text-gray-800">{{ $mv->batch->batch_number }}</span>
                                        @if($mv->batch->expiry_date)
                                            <span class="block text-[11px] text-gray-400">Exp: {{ $mv->batch->expiry_date->format('M Y') }}</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-emerald-600">
                                    +{{ \App\Support\Format::qty($mv->quantity) }} {{ $mv->product?->unit ?? 'pcs' }}
                                </td>
                                <td class="px-6 py-4 text-right text-gray-700">
                                    ₹{{ number_format((float) $mv->unit_cost, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-gray-900">
                                    ₹{{ number_format((float) $mv->quantity * (float) $mv->unit_cost, 2) }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($mv->purchaseBill)
                                        <a href="{{ route('admin.purchase-bills.show', $mv->purchaseBill) }}" class="text-xs font-semibold text-brand-600 hover:underline">
                                            {{ $mv->purchaseBill->bill_number }}
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-500">{{ $mv->reference ?: 'Manual Inward' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    No stock arrivals logged from this supplier yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($stockMovements && $stockMovements->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $stockMovements->links() }}
                    </div>
                @endif
            </div>
        @endif

        <!-- Tab 3: Payments History -->
        @if($tab === 'payments')
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50/80 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Receipt / Ref #</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Date</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Payment Method</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Allocated Bill</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Amount Paid</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Notes</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Processed By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($payments as $pm)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ $pm->payment_number }}
                                    @if($pm->reference_no)
                                        <span class="block text-xs text-gray-400">UTR: {{ $pm->reference_no }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $pm->payment_date->format('d M, Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 capitalize">
                                        {{ str_replace('_', ' ', $pm->payment_method) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($pm->purchaseBill)
                                        <a href="{{ route('admin.purchase-bills.show', $pm->purchaseBill) }}" class="text-xs font-semibold text-brand-600 hover:underline">
                                            {{ $pm->purchaseBill->bill_number }}
                                        </a>
                                        @if($pm->purchaseBill->supplier_invoice_no)
                                            <span class="text-xs text-gray-400"> (Inv: {{ $pm->purchaseBill->supplier_invoice_no }})</span>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-400">General Supplier Advance / Credit</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-emerald-600 text-base">
                                    ₹{{ number_format($pm->amount, 2) }}
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500 max-w-xs truncate">
                                    {{ $pm->notes ?: '—' }}
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-600">
                                    {{ $pm->createdBy?->name ?? 'System' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    No payments recorded for this supplier yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($payments && $payments->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $payments->links() }}
                    </div>
                @endif
            </div>
        @endif

        <!-- Tab 4: Supplied Products -->
        @if($tab === 'products')
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50/80 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Product Name</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">SKU</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">In Stock</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Latest Purchase Cost</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Retail Rate</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Active Batches</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($products as $prod)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-6 py-4 font-semibold text-gray-900">
                                    {{ $prod->name }}
                                </td>
                                <td class="px-6 py-4 text-xs font-medium text-gray-500">
                                    {{ $prod->sku ?: '—' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="font-bold text-gray-900">{{ \App\Support\Format::qty($prod->stock_qty) }}</span>
                                    <span class="text-xs text-gray-500">{{ $prod->unit }}</span>
                                </td>
                                <td class="px-6 py-4 text-right font-medium text-gray-800">
                                    ₹{{ number_format((float) $prod->latestCostPrice(), 2) }}
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-brand-600">
                                    ₹{{ number_format((float) $prod->rate, 2) }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($prod->activeBatchesFifo->isNotEmpty())
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($prod->activeBatchesFifo->take(3) as $b)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                                    {{ $b->batch_number }} ({{ \App\Support\Format::qty($b->current_qty) }})
                                                </span>
                                            @endforeach
                                            @if($prod->activeBatchesFifo->count() > 3)
                                                <span class="text-xs text-gray-400">+{{ $prod->activeBatchesFifo->count() - 3 }} more</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">No active batches</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <x-admin.button href="{{ route('admin.products.edit', $prod) }}" variant="secondary" size="sm">
                                        Edit Product
                                    </x-admin.button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    No products currently assigned to this supplier.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Record Payment Modal -->
    <div x-show="showPaymentModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showPaymentModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showPaymentModal = false"
                 class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showPaymentModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">

                <form method="POST" action="{{ route('admin.suppliers.payments.store', $supplier) }}" class="p-6 space-y-4">
                    @csrf
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Record Supplier Payment</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Pay towards a purchase invoice or record an advance.</p>
                        </div>
                        <button type="button" @click="showPaymentModal = false" class="text-gray-400 hover:text-gray-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Bill selection -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Apply to Purchase Bill</label>
                        <select name="purchase_bill_id" x-model="selectedBillId"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <option value="">General Supplier Payment (No specific bill)</option>
                            @foreach($unpaidBills as $ub)
                                <option value="{{ $ub->id }}">
                                    {{ $ub->bill_number }} @if($ub->supplier_invoice_no)(Inv: {{ $ub->supplier_invoice_no }})@endif — Due: ₹{{ number_format($ub->balance_due, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Amount & Date -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Amount Paid (₹) *</label>
                            <input type="number" step="0.01" min="0.01" name="amount" required placeholder="0.00"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Payment Date *</label>
                            <input type="date" name="payment_date" required value="{{ date('Y-m-d') }}"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>
                    </div>

                    <!-- Payment Method & Reference -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Payment Method *</label>
                            <select name="payment_method" required
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                <option value="bank_transfer">Bank Transfer (NEFT/RTGS/IMPS)</option>
                                <option value="cheque">Cheque</option>
                                <option value="upi">UPI / QR</option>
                                <option value="cash">Cash</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Reference / UTR / Cheque #</label>
                            <input type="text" name="reference_no" placeholder="e.g. UTR928374921"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Notes / Remarks</label>
                        <textarea name="notes" rows="2" placeholder="Optional settlement notes..."
                                  class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="showPaymentModal = false"
                                class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-sm font-medium hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition shadow-sm">
                            Save Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
