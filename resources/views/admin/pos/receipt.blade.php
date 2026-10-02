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
            width: {{ config('pos.paper_width', '80mm') === '58mm' ? '58mm' : '80mm' }};
            max-width: 100%;
            padding: 12px 10px;
            font-size: {{ config('pos.paper_width', '80mm') === '58mm' ? '9.5px' : '11px' }};
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
            <a href="{{ route('pos.terminal') }}" class="btn btn-secondary">Back to POS</a>
        </div>

        <div class="receipt">
            <div class="center">
                @if(config('pos.show_logo') && config('pos.logo_url'))
                    <img src="{{ config('pos.logo_url') }}" alt="Store Logo" style="max-height: 40px; margin-bottom: 4px;">
                @endif
                <div class="bold" style="font-size: 15px;">{{ strtoupper(config('pos.store_name', config('shop.site_name', 'PLANT TECH AGRO'))) }}</div>
                <div style="font-size: 9px; margin-top: 2px;">{{ config('pos.header_notes', config('shop.footer_tagline', 'Modern Orchard & Precision Agriculture')) }}</div>
                <div style="font-size: 9px;">{{ config('pos.store_address', config('shop.site_address', '56 Murad House, Pine Lane-8, Kurso Rajbagh, Srinagar-190008, Jammu & Kashmir')) }}</div>
                <div style="font-size: 9px;">Phone: {{ config('pos.store_phone', config('shop.site_phone', '0194-796-1490')) }}</div>
                @if(config('pos.gstin'))
                    <div style="font-size: 9px;">GSTIN: {{ config('pos.gstin') }}</div>
                @endif
            </div>

            <div class="divider"></div>

            <div class="center bold" style="font-size: 12px; letter-spacing: 1px;">RETAIL RECEIPT / CASH BILL</div>

            <div class="divider"></div>

            <div>
                <div><strong>Invoice:</strong> {{ $sale->invoice_number }}</div>
                <div><strong>Date:</strong> {{ $sale->sale_date->format('d/m/Y h:i A') }}</div>
                @if(config('pos.show_cashier', true))
                    <div><strong>Cashier:</strong> {{ $sale->cashier?->name ?? 'Admin Staff' }}</div>
                @endif
                @if($sale->customer_name && $sale->customer_name !== 'Walk-in Customer')
                    <div><strong>Customer:</strong> {{ $sale->customer_name }}</div>
                    @if($sale->customer_phone) <div><strong>Phone:</strong> {{ $sale->customer_phone }}</div> @endif
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
                            <td style="color: #444;">{{ $item->batch?->batch_number ? 'Batch: '.$item->batch->batch_number : '' }}</td>
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

                @if($sale->payments->count() > 0)
                    @foreach($sale->payments as $payment)
                        <tr>
                            <td style="padding-left: 8px;">- {{ $payment->methodLabel() }}:</td>
                            <td class="right">₹{{ number_format($payment->amount, 2) }}</td>
                        </tr>
                    @endforeach
                @endif

                <tr>
                    <td class="bold">Total Paid:</td>
                    <td class="right bold">₹{{ number_format($sale->amount_paid ?: $sale->grand_total, 2) }}</td>
                </tr>

                @if((float) $sale->balance_due > 0)
                    <tr style="color: #b91c1c; font-weight: bold; font-size: 11px;">
                        <td>BALANCE DUE (DEBT):</td>
                        <td class="right">₹{{ number_format($sale->balance_due, 2) }}</td>
                    </tr>
                    @if($sale->customer && (float) $sale->customer->outstanding_balance > 0)
                        <tr style="color: #b91c1c; font-size: 9px;">
                            <td>Total Customer Due:</td>
                            <td class="right">₹{{ number_format($sale->customer->outstanding_balance, 2) }}</td>
                        </tr>
                    @endif
                @endif

                @if((float) $sale->change_amount > 0)
                    <tr>
                        <td>Change Returned:</td>
                        <td class="right bold">₹{{ number_format($sale->change_amount, 2) }}</td>
                    </tr>
                @endif
            </table>

            <div class="divider"></div>

            @if(config('pos.show_upi_qr') && config('pos.upi_vpa'))
                <div class="center" style="margin: 8px 0; font-size: 8px;">
                    <div style="font-weight: bold; margin-bottom: 2px;">SCAN TO PAY VIA UPI</div>
                    @php
                        $upiUrl = "upi://pay?pa=" . urlencode(config('pos.upi_vpa')) . "&pn=" . urlencode(config('pos.upi_payee_name', 'Plant Tech Agro')) . "&am=" . $sale->grand_total . "&cu=INR";
                    @endphp
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode($upiUrl) }}" alt="UPI QR" style="width: 75px; height: 75px; display: inline-block;">
                    <div style="font-family: monospace; font-size: 8px; margin-top: 2px;">{{ config('pos.upi_vpa') }}</div>
                </div>
                <div class="divider"></div>
            @endif

            <div class="center" style="font-size: 9px; margin-top: 8px;">
                <div>{{ config('pos.footer_notes', 'Thank you for choosing Plant Tech Agro!') }}</div>
                <div style="margin-top: 3px; font-size: 8px; color: #444;">{{ config('pos.return_policy', 'Keep receipt for warranty/returns within 7 days.') }}</div>
                <div style="margin-top: 4px; font-weight: bold;">{{ parse_url(config('pos.subdomain_url', 'https://pos.planttechagro.com'), PHP_URL_HOST) ?? 'pos.planttechagro.com' }}</div>
            </div>
        </div>
    </div>
</body>
</html>
