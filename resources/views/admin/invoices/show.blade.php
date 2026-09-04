@extends('admin.layout')

@section('page-title', 'Invoice ' . $invoice->number)

@section('content')
    @php
        $color = \App\Models\Invoice::STATUS_COLORS[$invoice->status] ?? 'gray';
        $statusClass = ['red' => 'bg-red-50 text-red-700', 'yellow' => 'bg-yellow-50 text-yellow-700', 'green' => 'bg-green-50 text-green-700', 'gray' => 'bg-gray-100 text-gray-600'][$color];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-900">{{ $invoice->number }}</h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                    {{ \App\Models\Invoice::STATUSES[$invoice->status] ?? $invoice->status }}
                </span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <x-admin.button href="{{ route('admin.invoices.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
                <x-admin.button href="{{ route('admin.invoices.print', $invoice) }}" variant="secondary" target="_blank">Print</x-admin.button>
                @if(! $invoice->isCancelled())
                    <x-admin.button href="{{ route('admin.invoices.pdf', $invoice) }}" variant="primary">Download PDF</x-admin.button>
                @endif
            </div>
        </div>

        @if($invoice->workOrder)
            <a href="{{ route('admin.work-orders.show', $invoice->workOrder) }}" class="block bg-brand-50 border border-brand-100 rounded-2xl p-4 text-sm text-brand-800 hover:bg-brand-100 transition">
                Generated from work order <span class="font-semibold">{{ $invoice->workOrder->number }}</span> — {{ $invoice->workOrder->service_name }} · View work order →
            </a>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-3 font-semibold text-gray-600">Item</th>
                                    <th class="px-6 py-3 font-semibold text-gray-600">Qty</th>
                                    <th class="px-6 py-3 font-semibold text-gray-600">Rate</th>
                                    <th class="px-6 py-3 font-semibold text-gray-600">Discount</th>
                                    <th class="px-6 py-3 font-semibold text-gray-600">GST</th>
                                    <th class="px-6 py-3 font-semibold text-gray-600 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($invoice->items as $item)
                                    <tr>
                                        <td class="px-6 py-3 font-medium text-gray-900">{{ $item->name }}</td>
                                        <td class="px-6 py-3 text-gray-600">{{ $item->qty }} {{ $item->unit }}</td>
                                        <td class="px-6 py-3 text-gray-600">₹{{ number_format((float) $item->rate, 2) }}</td>
                                        <td class="px-6 py-3 text-gray-600">{{ (float) $item->discount > 0 ? '₹' . number_format((float) $item->discount, 2) : '—' }}</td>
                                        <td class="px-6 py-3 text-gray-600">{{ $item->gst_rate }}%</td>
                                        <td class="px-6 py-3 font-semibold text-gray-900 text-right">₹{{ number_format((float) $item->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No items.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
                        <div class="w-full max-w-xs space-y-2 text-sm">
                            <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span class="font-semibold">₹{{ number_format((float) $invoice->subtotal, 2) }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Discount</span><span class="font-semibold text-red-600">− ₹{{ number_format((float) $invoice->discount_total, 2) }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">GST</span><span class="font-semibold">₹{{ number_format((float) $invoice->gst_total, 2) }}</span></div>
                            <div class="flex justify-between border-t border-gray-200 pt-2"><span class="font-semibold text-gray-900">Grand Total</span><span class="font-bold text-brand-700 text-base">₹{{ number_format((float) $invoice->grand_total, 2) }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Paid</span><span class="font-semibold text-green-600">₹{{ number_format((float) $invoice->amount_paid, 2) }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Balance Due</span><span class="font-bold text-gray-900">₹{{ number_format($invoice->balanceDue(), 2) }}</span></div>
                        </div>
                    </div>
                </div>

                {{-- Payments --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Payments</h3>
                    <p class="text-sm text-gray-500 mb-4">{{ $invoice->payments->count() }} payment(s) recorded.</p>
                    @if($invoice->payments->isNotEmpty())
                        <div class="space-y-2 mb-4">
                            @foreach($invoice->payments as $payment)
                                <div class="flex items-center justify-between rounded-xl bg-gray-50 border border-gray-100 px-4 py-2.5 text-sm">
                                    <div>
                                        <span class="font-semibold text-gray-900">₹{{ number_format((float) $payment->amount, 2) }}</span>
                                        <span class="text-gray-500">· {{ \App\Models\Payment::METHODS[$payment->method] ?? $payment->method }}</span>
                                        @if($payment->reference)
                                            <span class="text-xs text-gray-400">· {{ $payment->reference }}</span>
                                        @endif
                                    </div>
                                    <span class="text-xs text-gray-400">{{ $payment->paid_at->format('d M Y') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if(! $invoice->isCancelled() && $invoice->balanceDue() > 0)
                        <form action="{{ route('admin.invoices.payments.store', $invoice) }}" method="POST" class="flex flex-wrap items-end gap-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Amount (max ₹{{ number_format($invoice->balanceDue(), 2) }})</label>
                                <input type="number" name="amount" step="0.01" min="0.01" max="{{ $invoice->balanceDue() }}" required
                                       class="w-32 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Method</label>
                                <select name="method" class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                    @foreach(\App\Models\Payment::METHODS as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Date</label>
                                <input type="date" name="paid_at" value="{{ now()->toDateString() }}" required
                                       class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Reference</label>
                                <input type="text" name="reference" placeholder="UTR / Cheque no."
                                       class="w-36 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            </div>
                            <x-admin.button type="submit" size="sm">Record Payment</x-admin.button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Details</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">Customer</dt><dd class="font-medium text-gray-900">{{ $invoice->customer_name }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Invoice Date</dt><dd class="text-gray-900">{{ $invoice->invoice_date->format('d M Y') }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Due Date</dt><dd class="text-gray-900">{{ $invoice->due_date?->format('d M Y') ?? '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Created By</dt><dd class="text-gray-900">{{ $invoice->createdBy?->name ?? '—' }}</dd></div>
                    </dl>
                </div>

                @if(! $invoice->isCancelled())
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Danger Zone</h3>
                        <p class="text-sm text-gray-500 mb-4">Cancelling reverses stock for product-linked items and marks the invoice void.</p>
                        <form action="{{ route('admin.invoices.cancel', $invoice) }}" method="POST" onsubmit="return confirm('Cancel this invoice and reverse stock?')">
                            @csrf
                            @method('PATCH')
                            <x-admin.button type="submit" variant="danger" class="w-full">Cancel Invoice</x-admin.button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
