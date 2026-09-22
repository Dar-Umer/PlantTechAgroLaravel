<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tax Invoice #{{ $sale->invoice_number }} - Plant Tech Agro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .print-shadow-none { box-shadow: none !important; border: none !important; }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 antialiased p-4 md:p-8 font-sans">
    <div class="max-w-4xl mx-auto space-y-4">
        {{-- Top Action Bar --}}
        <div class="no-print flex items-center justify-between bg-white p-4 rounded-2xl shadow-sm border border-gray-200">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.pos.terminal') }}" class="inline-flex items-center text-xs font-semibold text-gray-600 hover:text-gray-900 bg-gray-100 px-3 py-2 rounded-xl transition">
                    &larr; Back to Terminal
                </a>
                <span class="text-xs text-gray-500">POS Tax Invoice</span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.pos.receipt', $sale) }}" class="inline-flex items-center px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                    80mm Thermal Receipt
                </a>
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 text-xs font-bold text-white bg-green-700 hover:bg-green-800 rounded-xl transition shadow">
                    Print Invoice
                </button>
            </div>
        </div>

        {{-- Printable A4 Sheet --}}
        <div class="bg-white p-8 md:p-12 rounded-2xl shadow-sm border border-gray-200 print-shadow-none text-sm space-y-6">
            {{-- Header --}}
            <div class="flex justify-between items-start border-b border-gray-200 pb-6">
                <div>
                    <h1 class="text-2xl font-black text-green-800 tracking-tight">PLANT TECH AGRO</h1>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">High Density Modern Orchard Systems & Agri Technologies</p>
                    <p class="text-xs text-gray-600 mt-2">Gourigund, Pulwama, Jammu & Kashmir - 192301</p>
                    <p class="text-xs text-gray-600">Phone: +91 94190 00000 | Email: contact@planttechagro.com</p>
                    <p class="text-xs font-bold text-gray-800 mt-1">GSTIN: 01AAAAA0000A1Z5</p>
                </div>
                <div class="text-right">
                    <span class="inline-block px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold uppercase tracking-wider">
                        Tax Invoice / Cash Bill
                    </span>
                    <h2 class="text-xl font-mono font-bold text-gray-900 mt-2">{{ $sale->invoice_number }}</h2>
                    <p class="text-xs text-gray-500 mt-1">Date: <strong class="text-gray-800">{{ $sale->sale_date->format('d M Y, h:i A') }}</strong></p>
                    <p class="text-xs text-gray-500">Cashier: <strong class="text-gray-800">{{ $sale->cashier?->name ?? 'Admin Staff' }}</strong></p>
                </div>
            </div>

            {{-- Bill To --}}
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 flex justify-between items-start">
                <div>
                    <span class="text-[11px] font-bold uppercase text-gray-400 tracking-wider">Customer / Buyer Details</span>
                    <p class="text-base font-bold text-gray-900 mt-1">{{ $sale->customer_name }}</p>
                    @if($sale->customer_phone)
                        <p class="text-xs text-gray-600">Phone: {{ $sale->customer_phone }}</p>
                    @endif
                    @if($sale->customer_gstin)
                        <p class="text-xs font-mono font-semibold text-gray-800 mt-1">GSTIN: {{ $sale->customer_gstin }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <span class="text-[11px] font-bold uppercase text-gray-400 tracking-wider">Payment Info</span>
                    <p class="text-sm font-bold text-gray-900 mt-1">{{ strtoupper(\App\Models\PosSale::PAYMENT_METHODS[$sale->payment_method] ?? $sale->payment_method) }}</p>
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold mt-1 {{ $sale->statusBadge()['bg'] }}">
                        {{ $sale->statusBadge()['label'] }}
                    </span>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b-2 border-gray-200 text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <th class="py-3 px-2">#</th>
                            <th class="py-3 px-2">Item Description</th>
                            <th class="py-3 px-2">HSN</th>
                            <th class="py-3 px-2 text-right">Qty</th>
                            <th class="py-3 px-2 text-right">Rate (₹)</th>
                            <th class="py-3 px-2 text-right">GST %</th>
                            <th class="py-3 px-2 text-right">Tax (₹)</th>
                            <th class="py-3 px-2 text-right">Total (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs">
                        @foreach($sale->items as $i => $item)
                            <tr>
                                <td class="py-3 px-2 text-gray-400">{{ $i + 1 }}</td>
                                <td class="py-3 px-2 font-semibold text-gray-900">
                                    {{ $item->product_name }}
                                </td>
                                <td class="py-3 px-2 text-gray-500 font-mono">{{ $item->product?->hsn_code ?? '—' }}</td>
                                <td class="py-3 px-2 text-right font-medium text-gray-800">{{ number_format($item->quantity, 2) }} {{ $item->unit }}</td>
                                <td class="py-3 px-2 text-right font-medium text-gray-800">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="py-3 px-2 text-right text-gray-600">{{ $item->tax_rate > 0 ? $item->tax_rate.'%' : '0%' }}</td>
                                <td class="py-3 px-2 text-right text-gray-600">{{ number_format($item->tax_amount, 2) }}</td>
                                <td class="py-3 px-2 text-right font-bold text-gray-900">{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Financial Summary & Signatures --}}
            <div class="flex flex-col sm:flex-row justify-between items-start gap-8 pt-4 border-t border-gray-200">
                <div class="w-full sm:w-1/2 space-y-3 text-xs text-gray-600">
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <p class="font-bold text-gray-800 mb-1">Bank Payment Details:</p>
                        <p>Bank: <strong>HDFC Bank / J&K Bank</strong></p>
                        <p>Account Name: <strong>Plant Tech Agro</strong></p>
                        <p>A/C No: <strong>50200000000000</strong> | IFSC: <strong>HDFC0000000</strong></p>
                        <p>UPI ID: <strong>planttechagro@hdfcbank</strong></p>
                    </div>
                    <p class="text-[11px] text-gray-400 italic">This is a computer-generated tax invoice issued at POS Terminal.</p>
                </div>

                <div class="w-full sm:w-2/5 space-y-2 text-xs">
                    <div class="flex justify-between py-1 text-gray-600">
                        <span>Items Subtotal:</span>
                        <span class="font-medium text-gray-900">₹{{ number_format($sale->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-1 text-gray-600">
                        <span>Total GST Amount:</span>
                        <span class="font-medium text-gray-900">₹{{ number_format($sale->gst_amount, 2) }}</span>
                    </div>
                    @if($sale->discount_amount > 0)
                        <div class="flex justify-between py-1 text-emerald-700 font-medium">
                            <span>Special Discount:</span>
                            <span>-₹{{ number_format($sale->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    @if($sale->round_off != 0)
                        <div class="flex justify-between py-1 text-gray-500">
                            <span>Round Off:</span>
                            <span>{{ $sale->round_off > 0 ? '+' : '' }}₹{{ number_format($sale->round_off, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between py-2 border-t-2 border-gray-900 text-base font-extrabold text-gray-900">
                        <span>Grand Total:</span>
                        <span class="text-green-800">₹{{ number_format($sale->grand_total, 2) }}</span>
                    </div>

                    @if($sale->payment_method === 'cash')
                        <div class="flex justify-between py-1 text-[11px] text-gray-500 pt-2 border-t border-gray-100">
                            <span>Amount Tendered:</span>
                            <span>₹{{ number_format($sale->amount_tendered, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-1 text-[11px] text-gray-500">
                            <span>Change Returned:</span>
                            <span class="font-bold text-gray-800">₹{{ number_format($sale->change_amount, 2) }}</span>
                        </div>
                    @endif

                    <div class="pt-8 text-center sm:text-right">
                        <div class="inline-block border-t border-gray-400 pt-1 px-8 text-center text-[11px] text-gray-500">
                            Authorized Signatory
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
