@extends('admin.layout')

@section('page-title', 'POS Sale ' . $sale->invoice_number)

@section('content')
<div class="space-y-6 max-w-4xl">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-900">{{ $sale->invoice_number }}</h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $sale->statusBadge()['bg'] }}">
                    {{ $sale->statusBadge()['label'] }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $sale->paymentStatusBadge()['bg'] }}">
                    {{ $sale->paymentStatusBadge()['label'] }}
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-1">Sale completed on {{ $sale->sale_date->format('d M Y, h:i A') }}</p>
        </div>

        <div class="flex items-center gap-2">
            <x-admin.button href="{{ route('admin.pos.sales') }}" variant="secondary">Back to Sales</x-admin.button>
            <a href="{{ route('admin.pos.receipt', $sale) }}" target="_blank" class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 shadow-sm transition">
                80mm Receipt
            </a>
            <a href="{{ route('admin.pos.invoice', $sale) }}" target="_blank" class="inline-flex items-center px-3.5 py-2 text-xs font-bold text-white bg-green-700 hover:bg-green-800 rounded-xl shadow-sm transition">
                Retail Invoice
            </a>
        </div>
    </div>

    {{-- Info Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <span class="text-xs font-semibold uppercase text-gray-400">Customer Details</span>
            <p class="text-base font-bold text-gray-900 mt-1">{{ $sale->customer_name }}</p>
            @if($sale->customer_phone)
                <p class="text-xs text-gray-500 mt-0.5">Phone: {{ $sale->customer_phone }}</p>
            @endif
            @if($sale->customer && (float) $sale->customer->outstanding_balance > 0)
                <p class="text-xs text-amber-700 font-bold mt-1">Outstanding Balance: ₹{{ number_format($sale->customer->outstanding_balance, 2) }}</p>
            @endif
        </div>

        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <span class="text-xs font-semibold uppercase text-gray-400">Payment Breakdown</span>
            <p class="text-base font-bold text-gray-900 mt-1">{{ strtoupper(\App\Models\PosSale::PAYMENT_METHODS[$sale->payment_method] ?? $sale->payment_method) }}</p>
            @if($sale->payments->count() > 0)
                <div class="text-xs text-gray-600 mt-1 space-y-0.5">
                    @foreach($sale->payments as $payment)
                        <div class="flex justify-between">
                            <span>{{ $payment->methodLabel() }}:</span>
                            <span class="font-semibold text-gray-900">₹{{ number_format($payment->amount, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
            @if((float) $sale->balance_due > 0)
                <p class="text-xs text-red-600 font-bold mt-1 bg-red-50 px-2 py-0.5 rounded">Balance Due: ₹{{ number_format($sale->balance_due, 2) }}</p>
            @endif
            @if((float) $sale->change_amount > 0)
                <p class="text-xs text-emerald-600 font-semibold mt-1">Change: ₹{{ number_format($sale->change_amount, 2) }}</p>
            @endif
        </div>

        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <span class="text-xs font-semibold uppercase text-gray-400">Billing Counter</span>
            <p class="text-base font-bold text-gray-900 mt-1">{{ $sale->cashier?->name ?? 'Admin Staff' }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Main POS Terminal</p>
        </div>
    </div>

    {{-- Items Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-900">Items Sold</h3>
        </div>

        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                <tr>
                    <th class="py-3 px-4">Item</th>
                    <th class="py-3 px-4">Batch / Lot</th>
                    <th class="py-3 px-4 text-right">Quantity</th>
                    <th class="py-3 px-4 text-right">Unit Price</th>
                    <th class="py-3 px-4 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-xs">
                @foreach($sale->items as $item)
                    <tr>
                        <td class="py-3 px-4 font-semibold text-gray-900">{{ $item->product_name }}</td>
                        <td class="py-3 px-4 font-mono text-gray-500">{{ $item->batch?->batch_number ?? 'Auto Lot' }}</td>
                        <td class="py-3 px-4 text-right font-medium">{{ number_format($item->quantity, 2) }} {{ $item->unit }}</td>
                        <td class="py-3 px-4 text-right">₹{{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-3 px-4 text-right font-bold text-gray-900">₹{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Financial Summary Footer --}}
        <div class="p-5 bg-gray-50 border-t border-gray-100 flex justify-end">
            <div class="w-72 space-y-1.5 text-xs text-gray-600">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span class="font-medium text-gray-900">₹{{ number_format($sale->subtotal, 2) }}</span>
                </div>
                @if($sale->discount_amount > 0)
                    <div class="flex justify-between text-emerald-700 font-medium">
                        <span>Discount:</span>
                        <span>-₹{{ number_format($sale->discount_amount, 2) }}</span>
                    </div>
                @endif
                @if($sale->round_off != 0)
                    <div class="flex justify-between text-gray-500">
                        <span>Round Off:</span>
                        <span>{{ $sale->round_off > 0 ? '+' : '' }}₹{{ number_format($sale->round_off, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between pt-2 border-t border-gray-200 text-base font-extrabold text-gray-900">
                    <span>Grand Total:</span>
                    <span class="text-green-800">₹{{ number_format($sale->grand_total, 2) }}</span>
                </div>
                <div class="flex justify-between text-gray-700 font-semibold pt-1">
                    <span>Amount Paid:</span>
                    <span>₹{{ number_format($sale->amount_paid ?: $sale->grand_total, 2) }}</span>
                </div>
                @if((float) $sale->balance_due > 0)
                    <div class="flex justify-between text-red-600 font-bold bg-red-50 px-2 py-0.5 rounded">
                        <span>Balance Due:</span>
                        <span>₹{{ number_format($sale->balance_due, 2) }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
