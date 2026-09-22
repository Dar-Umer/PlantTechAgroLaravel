<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt #{{ $sale->invoice_number }} - Plant Tech Agro</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Courier New', Courier, monospace, monospace;
        }
        body {
            background-color: #f3f4f6;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 12px 8px;
        }
        .receipt {
            background: #fff;
            width: 80mm;
            max-width: 100%;
            padding: 12px 10px;
            font-size: 11px;
            line-height: 1.35;
            color: #000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        .divider-double {
            border-top: 2px dashed #000;
            margin: 6px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0;
        }
        th, td {
            padding: 3px 0;
        }
        .actions {
            margin-bottom: 15px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .btn {
            background: #16a34a;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-secondary {
            background: #4b5563;
        }
        @media print {
            body {
                background: none;
                padding: 0;
            }
            .actions { display: none; }
            .receipt {
                box-shadow: none;
                width: 100%;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div style="display: flex; flex-direction: column; align-items: center;">
        <div class="actions">
            <button class="btn" onclick="window.print()">Print Receipt</button>
            <a href="{{ route('admin.pos.terminal') }}" class="btn btn-secondary">Back to POS</a>
        </div>

        <div class="receipt">
            <div class="center">
                <div class="bold" style="font-size: 15px;">PLANT TECH AGRO</div>
                <div style="font-size: 9px; margin-top: 2px;">Modern Orchard & Precision Agriculture</div>
                <div style="font-size: 9px;">Gourigund, Pulwama, J&K - 192301</div>
                <div style="font-size: 9px;">Phone: +91 94190 00000 / 0194 000000</div>
                <div style="font-size: 9px; font-weight: bold;">GSTIN: 01AAAAA0000A1Z5</div>
            </div>

            <div class="divider"></div>

            <div class="center bold" style="font-size: 12px; letter-spacing: 1px;">TAX INVOICE / RETAIL RECEIPT</div>

            <div class="divider"></div>

            <div>
                <div><strong>Invoice:</strong> {{ $sale->invoice_number }}</div>
                <div><strong>Date:</strong> {{ $sale->sale_date->format('d/m/Y h:i A') }}</div>
                <div><strong>Cashier:</strong> {{ $sale->cashier?->name ?? 'Admin Staff' }}</div>
                @if($sale->customer_name && $sale->customer_name !== 'Walk-in Customer')
                    <div><strong>Customer:</strong> {{ $sale->customer_name }}</div>
                    @if($sale->customer_phone) <div><strong>Phone:</strong> {{ $sale->customer_phone }}</div> @endif
                    @if($sale->customer_gstin) <div><strong>Cust GST:</strong> {{ $sale->customer_gstin }}</div> @endif
                @else
                    <div><strong>Customer:</strong> Walk-in Customer</div>
                @endif
            </div>

            <div class="divider"></div>

            <table>
                <thead>
                    <tr style="border-bottom: 1px dashed #000;">
                        <th style="text-align: left;">Item</th>
                        <th class="right">Qty</th>
                        <th class="right">Rate</th>
                        <th class="right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $item)
                        <tr>
                            <td colspan="4" class="bold">{{ $item->product_name }}</td>
                        </tr>
                        <tr style="font-size: 10px;">
                            <td style="color: #444;">{{ $item->product?->hsn_code ? 'HSN: '.$item->product->hsn_code : '' }}</td>
                            <td class="right">{{ number_format($item->quantity, $item->quantity == (int)$item->quantity ? 0 : 2) }} {{ $item->unit }}</td>
                            <td class="right">₹{{ number_format($item->unit_price, 2) }}</td>
                            <td class="right bold">₹{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="divider"></div>

            <table style="font-size: 11px;">
                <tr>
                    <td>Items Subtotal:</td>
                    <td class="right">₹{{ number_format($sale->subtotal, 2) }}</td>
                </tr>
                @if($sale->gst_amount > 0)
                    <tr>
                        <td>Total GST:</td>
                        <td class="right">₹{{ number_format($sale->gst_amount, 2) }}</td>
                    </tr>
                @endif
                @if($sale->discount_amount > 0)
                    <tr>
                        <td>Discount:</td>
                        <td class="right">-₹{{ number_format($sale->discount_amount, 2) }}</td>
                    </tr>
                @endif
                @if($sale->round_off != 0)
                    <tr>
                        <td>Round Off:</td>
                        <td class="right">{{ $sale->round_off > 0 ? '+' : '' }}₹{{ number_format($sale->round_off, 2) }}</td>
                    </tr>
                @endif
            </table>

            <div class="divider-double"></div>

            <div style="display: flex; justify-content: space-between; font-size: 14px;" class="bold">
                <span>NET PAYABLE:</span>
                <span>₹{{ number_format($sale->grand_total, 2) }}</span>
            </div>

            <div class="divider-double"></div>

            <table style="font-size: 10px;">
                <tr>
                    <td>Payment Mode:</td>
                    <td class="right bold">{{ strtoupper(\App\Models\PosSale::PAYMENT_METHODS[$sale->payment_method] ?? $sale->payment_method) }}</td>
                </tr>
                @if($sale->payment_method === 'cash')
                    <tr>
                        <td>Cash Tendered:</td>
                        <td class="right">₹{{ number_format($sale->amount_tendered, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Change Returned:</td>
                        <td class="right bold">₹{{ number_format($sale->change_amount, 2) }}</td>
                    </tr>
                @endif
            </table>

            <div class="divider"></div>

            <div class="center" style="font-size: 9px; margin-top: 8px;">
                <div>Thank you for choosing Plant Tech Agro!</div>
                <div>Keep invoice for warranty/returns within 7 days.</div>
                <div style="margin-top: 4px; font-weight: bold;">www.planttechagro.com</div>
            </div>
        </div>
    </div>
</body>
</html>
