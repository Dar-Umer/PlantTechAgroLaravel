@extends('admin.layout')

@section('page-title', 'Purchase Bill ' . $purchaseBill->bill_number)

@section('content')
<div x-data="{ showPaymentModal: false }" class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.purchase-bills.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $purchaseBill->bill_number }}</h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $purchaseBill->paymentStatusBadgeClass() }}">
                        {{ ucfirst($purchaseBill->payment_status) }}
                    </span>
                    @if($purchaseBill->status === 'cancelled')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Cancelled</span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 mt-0.5">
                    Recorded on {{ $purchaseBill->bill_date->format('d M, Y') }} &bull; Created by {{ $purchaseBill->createdBy?->name ?? 'System' }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($purchaseBill->balance_due > 0)
                <button type="button" @click="showPaymentModal = true"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Record Payment (Due: ₹{{ number_format($purchaseBill->balance_due, 2) }})
                </button>
            @endif

            <a href="{{ route('admin.purchase-bills.print', $purchaseBill) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Voucher
            </a>

            @if($purchaseBill->payments->isEmpty())
                <form action="{{ route('admin.purchase-bills.destroy', $purchaseBill) }}" method="POST"
                      onsubmit="return confirm('Delete this purchase bill? Stock quantities will be reversed!')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2.5 rounded-xl border border-red-200 text-red-600 hover:bg-red-50 transition" title="Delete & Reverse Stock">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Metadata Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Supplier Details -->
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6 md:col-span-2">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 block mb-1">Supplier</span>
                    <h3 class="text-xl font-bold text-gray-900">
                        <a href="{{ route('admin.suppliers.show', $purchaseBill->supplier) }}" class="hover:text-brand-600 transition">
                            {{ $purchaseBill->supplier->name }}
                        </a>
                    </h3>
                    @if($purchaseBill->supplier->contact_person)
                        <p class="text-xs text-gray-500 mt-0.5">Contact: {{ $purchaseBill->supplier->contact_person }}</p>
                    @endif
                </div>

                <div class="text-right">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 block mb-1">GSTIN / Tax ID</span>
                    <span class="text-sm font-semibold text-gray-800">{{ $purchaseBill->supplier->gst_no ?: 'Unregistered' }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4 pt-4 border-t border-gray-100 text-xs">
                <div>
                    <span class="text-gray-400 block mb-0.5">Phone & Email</span>
                    <div class="font-medium text-gray-800">
                        {{ $purchaseBill->supplier->phone ?: '—' }}
                        @if($purchaseBill->supplier->email)
                            <span class="block text-gray-500 font-normal truncate">{{ $purchaseBill->supplier->email }}</span>
                        @endif
                    </div>
                </div>

                <div>
                    <span class="text-gray-400 block mb-0.5">Billing Address</span>
                    <div class="text-gray-700 leading-relaxed">
                        {{ $purchaseBill->supplier->address ?: '—' }}
                        @if($purchaseBill->supplier->city)
                            <span class="block text-gray-500">{{ $purchaseBill->supplier->city }}, {{ $purchaseBill->supplier->state }}</span>
                        @endif
                    </div>
                </div>

                <div>
                    <span class="text-gray-400 block mb-0.5">Supplier Ledger Balance</span>
                    <div class="text-sm font-bold {{ $purchaseBill->supplier->balance_due > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                        ₹{{ number_format($purchaseBill->supplier->balance_due, 2) }}
                    </div>
                    <a href="{{ route('admin.suppliers.show', $purchaseBill->supplier) }}" class="text-brand-600 hover:underline mt-1 block">
                        View Supplier Ledger &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Bill Dates & Invoice Reference -->
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6 space-y-4">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 block">Bill Information</span>

            <div class="space-y-3 text-xs">
                <div class="flex justify-between items-center py-1 border-b border-gray-100">
                    <span class="text-gray-500">Supplier Invoice #</span>
                    <span class="font-bold text-gray-900">{{ $purchaseBill->supplier_invoice_no ?: 'None specified' }}</span>
                </div>

                <div class="flex justify-between items-center py-1 border-b border-gray-100">
                    <span class="text-gray-500">Bill Date</span>
                    <span class="font-medium text-gray-900">{{ $purchaseBill->bill_date->format('d M, Y') }}</span>
                </div>

                <div class="flex justify-between items-center py-1 border-b border-gray-100">
                    <span class="text-gray-500">Payment Due Date</span>
                    <span class="font-medium {{ $purchaseBill->due_date && $purchaseBill->due_date->isPast() && $purchaseBill->balance_due > 0 ? 'text-red-600 font-bold' : 'text-gray-900' }}">
                        {{ $purchaseBill->due_date ? $purchaseBill->due_date->format('d M, Y') : '—' }}
                    </span>
                </div>

                @if($purchaseBill->attachment_path)
                    <div class="flex justify-between items-center py-1">
                        <span class="text-gray-500">Attachment</span>
                        <a href="{{ asset('storage/' . $purchaseBill->attachment_path) }}" target="_blank"
                           class="inline-flex items-center gap-1 font-semibold text-brand-600 hover:underline">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            View Document
                        </a>
                    </div>
                @endif
            </div>

            @if($purchaseBill->notes)
                <div class="pt-2 border-t border-gray-100 text-xs">
                    <span class="text-gray-400 block mb-0.5">Notes:</span>
                    <p class="text-gray-600 italic">{{ $purchaseBill->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Line Items Table -->
    <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900">Received Line Items & Lot Details</h3>
            <span class="text-xs text-gray-500">{{ $purchaseBill->items->count() }} items inward</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50/80 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Product</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Batch / Lot #</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Expiry Date</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Inward Qty</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Unit Cost</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Selling Rate</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Tax</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Line Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($purchaseBill->items as $item)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="px-6 py-4">
                                <span class="font-semibold text-gray-900 block">{{ $item->product?->name ?? '—' }}</span>
                                @if($item->product?->sku)
                                    <span class="text-xs text-gray-400">SKU: {{ $item->product->sku }}</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-xs font-medium">
                                @if($item->batch_number)
                                    <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-semibold border border-blue-100">
                                        {{ $item->batch_number }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-xs text-gray-600">
                                {{ $item->expiry_date ? $item->expiry_date->format('M Y') : '—' }}
                            </td>

                            <td class="px-6 py-4 text-right font-bold text-gray-900">
                                {{ \App\Support\Format::qty($item->quantity) }}
                                <span class="text-xs text-gray-500 font-normal">{{ $item->product?->unit ?? 'units' }}</span>
                            </td>

                            <td class="px-6 py-4 text-right text-gray-700 font-medium">
                                ₹{{ number_format($item->unit_cost, 2) }}
                            </td>

                            <td class="px-6 py-4 text-right text-brand-600 font-medium">
                                {{ $item->selling_price ? '₹' . number_format($item->selling_price, 2) : '—' }}
                            </td>

                            <td class="px-6 py-4 text-right text-xs text-gray-500">
                                @if($item->tax_percent > 0)
                                    <div>{{ $item->tax_percent }}%</div>
                                    <div class="text-[11px] text-gray-400">₹{{ number_format($item->tax_amount, 2) }}</div>
                                @else
                                    0%
                                @endif
                            </td>

                            <td class="px-6 py-4 text-right font-bold text-gray-900">
                                ₹{{ number_format($item->line_total, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals Breakdown Footer -->
        <div class="bg-gray-50/50 p-6 border-t border-gray-100">
            <div class="max-w-xs ml-auto space-y-2 text-sm">
                <div class="flex justify-between text-gray-600">
                    <span>Taxable Subtotal:</span>
                    <span class="font-semibold text-gray-900">₹{{ number_format($purchaseBill->subtotal, 2) }}</span>
                </div>

                <div class="flex justify-between text-gray-600">
                    <span>Total GST / Taxes:</span>
                    <span class="font-semibold text-gray-900">₹{{ number_format($purchaseBill->tax_amount, 2) }}</span>
                </div>

                @if($purchaseBill->discount > 0)
                    <div class="flex justify-between text-emerald-600">
                        <span>Discount:</span>
                        <span class="font-semibold">-₹{{ number_format($purchaseBill->discount, 2) }}</span>
                    </div>
                @endif

                @if($purchaseBill->shipping_cost > 0)
                    <div class="flex justify-between text-gray-600">
                        <span>Freight / Shipping:</span>
                        <span class="font-semibold text-gray-900">+₹{{ number_format($purchaseBill->shipping_cost, 2) }}</span>
                    </div>
                @endif

                <div class="border-t border-gray-200 pt-2 flex justify-between text-base font-bold text-gray-900">
                    <span>Grand Total:</span>
                    <span class="text-xl text-gray-900 font-bold">₹{{ number_format($purchaseBill->total_amount, 2) }}</span>
                </div>

                <div class="flex justify-between text-emerald-600 font-semibold pt-1">
                    <span>Paid to Date:</span>
                    <span>₹{{ number_format($purchaseBill->paid_amount, 2) }}</span>
                </div>

                <div class="border-t border-gray-200 pt-2 flex justify-between text-base font-bold {{ $purchaseBill->balance_due > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                    <span>Balance Due:</span>
                    <span>₹{{ number_format($purchaseBill->balance_due, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Section -->
    <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-gray-900">Payment History & Receipts</h3>
                <p class="text-xs text-gray-500">Payments specifically recorded against this purchase bill.</p>
            </div>
            @if($purchaseBill->balance_due > 0)
                <button type="button" @click="showPaymentModal = true"
                        class="px-3.5 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition">
                    + Add Payment
                </button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50/80 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Payment #</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Date</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Method</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Reference / UTR #</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Amount Paid</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Processed By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($purchaseBill->payments as $payment)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $payment->payment_number }}
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $payment->payment_date->format('d M, Y') }}
                            </td>
                            <td class="px-6 py-4 capitalize">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                    {{ str_replace('_', ' ', $payment->payment_method) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-600 font-medium">
                                {{ $payment->reference_no ?: '—' }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-emerald-600 text-base">
                                ₹{{ number_format($payment->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-600">
                                {{ $payment->createdBy?->name ?? 'System' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 text-xs">
                                No payments have been made against this bill yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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

                <form method="POST" action="{{ route('admin.purchase-bills.payments.store', $purchaseBill) }}" class="p-6 space-y-4">
                    @csrf
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Record Bill Payment</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Bill #{{ $purchaseBill->bill_number }} &bull; Max Payable: ₹{{ number_format($purchaseBill->balance_due, 2) }}</p>
                        </div>
                        <button type="button" @click="showPaymentModal = false" class="text-gray-400 hover:text-gray-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Amount (₹) *</label>
                            <input type="number" step="0.01" min="0.01" max="{{ $purchaseBill->balance_due }}" name="amount" required
                                   value="{{ $purchaseBill->balance_due }}"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Payment Date *</label>
                            <input type="date" name="payment_date" required value="{{ date('Y-m-d') }}"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Payment Method *</label>
                            <select name="payment_method" required
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                <option value="bank_transfer">Bank Transfer (NEFT/RTGS)</option>
                                <option value="cheque">Cheque</option>
                                <option value="upi">UPI / QR</option>
                                <option value="cash">Cash</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Reference / Cheque / UTR #</label>
                            <input type="text" name="reference_no" placeholder="e.g. UTR-982312"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Notes / Remarks</label>
                        <textarea name="notes" rows="2" placeholder="Optional notes..."
                                  class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="showPaymentModal = false"
                                class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-sm font-medium hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition shadow-sm">
                            Confirm Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
