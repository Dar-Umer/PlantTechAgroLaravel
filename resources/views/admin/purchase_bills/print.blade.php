<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Purchase Bill - {{ $purchaseBill->bill_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, sans-serif; color: #111827; font-size: 13px; background: #f3f4f6; }
        .page { max-width: 820px; margin: 24px auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        @media print {
            body { background: #fff; }
            .page { box-shadow: none; margin: 0; border-radius: 0; padding: 24px; max-width: none; }
            .no-print { display: none !important; }
        }
        .header { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; padding-bottom: 20px; border-bottom: 3px solid #059669; }
        .company h1 { font-size: 20px; font-weight: 800; color: #111827; }
        .company p { font-size: 11px; color: #6b7280; line-height: 1.5; margin-top: 4px; }
        .meta { text-align: right; flex-shrink: 0; }
        .meta .voucher-label { font-size: 20px; font-weight: 800; color: #059669; letter-spacing: .05em; }
        .meta table { margin-top: 8px; font-size: 11.5px; }
        .meta td { padding: 2px 0; color: #374151; }
        .meta td:first-child { text-align: right; color: #6b7280; padding-right: 12px; }
        .meta td:last-child { text-align: right; font-weight: 600; }
        .billto { display: flex; justify-content: space-between; margin: 24px 0; gap: 24px; }
        .billto .block h2 { font-size: 11px; text-transform: uppercase; letter-spacing: .08em; color: #6b7280; margin-bottom: 6px; }
        .billto .block p { font-size: 12px; line-height: 1.6; }
        .billto .block .name { font-weight: 700; font-size: 14px; color: #111827; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.items th { background: #059669; color: #fff; text-align: left; font-size: 10.5px; text-transform: uppercase; letter-spacing: .05em; padding: 8px 10px; }
        table.items th.r, table.items td.r { text-align: right; }
        table.items th.c, table.items td.c { text-align: center; }
        table.items td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; font-size: 12px; }
        table.items tr:nth-child(even) td { background: #f9fafb; }
        .totals { display: flex; justify-content: flex-end; margin-top: 16px; }
        .totals table { width: 300px; font-size: 12px; }
        .totals td { padding: 4px 10px; }
        .totals td:first-child { color: #6b7280; }
        .totals td:last-child { text-align: right; font-weight: 600; }
        .totals .grand td { border-top: 2px solid #059669; padding-top: 8px; font-size: 14px; }
        .totals .grand td:last-child { color: #059669; font-weight: 800; font-size: 15px; }
        .totals .balance td { border-top: 1px solid #e5e7eb; padding-top: 6px; font-size: 13px; font-weight: 700; }
        .totals .balance.due td:last-child { color: #dc2626; }
        .totals .balance.clear td:last-child { color: #059669; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
        .status-unpaid { background: #fee2e2; color: #b91c1c; }
        .status-partial { background: #fef3c7; color: #b45309; }
        .status-paid { background: #dcfce7; color: #15803d; }
        .signatures { display: flex; justify-content: space-between; margin-top: 48px; padding-top: 12px; }
        .signatures div { text-align: center; }
        .signatures .line { width: 180px; border-top: 1px solid #9ca3af; padding-top: 6px; font-size: 11px; color: #6b7280; }
        .print-btn { position: fixed; top: 16px; right: 16px; }
        .print-btn button { background: #059669; color: #fff; border: 0; padding: 10px 20px; border-radius: 10px; font-weight: 700; font-size: 13px; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .payments-table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 11.5px; }
        .payments-table th { background: #f3f4f6; text-align: left; padding: 6px 8px; font-size: 10px; text-transform: uppercase; color: #4b5563; }
        .payments-table td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="print-btn no-print"><button onclick="window.print()">🖨 Print / Save as PDF</button></div>

    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="company">
                <h1>{{ config('invoice.company_name', 'Plant Tech Agro') }}</h1>
                <p>{!! nl2br(e(config('invoice.address', "Agro Hub, Pulwama / Shopian\nJammu & Kashmir, India"))) !!}</p>
                @if(config('invoice.phone'))<p>Phone: {{ config('invoice.phone') }}</p>@endif
                @if(config('invoice.email'))<p>Email: {{ config('invoice.email') }}</p>@endif
                @if(config('invoice.gst_no'))<p>GSTIN: {{ config('invoice.gst_no') }}</p>@endif
            </div>
            <div class="meta">
                <div class="voucher-label">PURCHASE BILL (INWARD)</div>
                <table>
                    <tr><td>Bill Number</td><td>{{ $purchaseBill->bill_number }}</td></tr>
                    <tr><td>Supplier Inv #</td><td>{{ $purchaseBill->supplier_invoice_no ?: '—' }}</td></tr>
                    <tr><td>Bill Date</td><td>{{ $purchaseBill->bill_date->format('d M, Y') }}</td></tr>
                    @if($purchaseBill->due_date)
                        <tr><td>Due Date</td><td>{{ $purchaseBill->due_date->format('d M, Y') }}</td></tr>
                    @endif
                    <tr>
                        <td>Payment Status</td>
                        <td><span class="status-badge status-{{ $purchaseBill->payment_status }}">{{ strtoupper($purchaseBill->payment_status) }}</span></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Supplier & Receiving Details -->
        <div class="billto">
            <div class="block">
                <h2>Supplier / Vendor</h2>
                <p class="name">{{ $purchaseBill->supplier->name }}</p>
                @if($purchaseBill->supplier->contact_person)<p>Attn: {{ $purchaseBill->supplier->contact_person }}</p>@endif
                @if($purchaseBill->supplier->phone)<p>Phone: {{ $purchaseBill->supplier->phone }}</p>@endif
                @if($purchaseBill->supplier->email)<p>Email: {{ $purchaseBill->supplier->email }}</p>@endif
                @if($purchaseBill->supplier->address)<p>{!! nl2br(e($purchaseBill->supplier->address)) !!}</p>@endif
                @if($purchaseBill->supplier->gst_no)<p>GSTIN: {{ $purchaseBill->supplier->gst_no }}</p>@endif
            </div>

            <div class="block" style="text-align: right;">
                <h2>Inward Receipt & Warehouse</h2>
                <p>Warehouse: Main PTA Hub</p>
                <p>Recorded By: {{ $purchaseBill->createdBy?->name ?? 'System Admin' }}</p>
                @if($purchaseBill->notes)
                    <p style="margin-top: 6px; font-style: italic; color: #4b5563; max-width: 260px;">Notes: {{ $purchaseBill->notes }}</p>
                @endif
            </div>
        </div>

        <!-- Line Items -->
        <table class="items">
            <thead>
                <tr>
                    <th style="width: 30px;">#</th>
                    <th>Product & Description</th>
                    <th class="c" style="width: 110px;">Batch / Lot #</th>
                    <th class="c" style="width: 80px;">Exp Date</th>
                    <th class="r" style="width: 90px;">Qty</th>
                    <th class="r" style="width: 90px;">Unit Cost</th>
                    <th class="r" style="width: 60px;">GST %</th>
                    <th class="r" style="width: 100px;">Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseBill->items as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $item->product?->name ?? 'Item' }}</strong>
                            @if($item->product?->sku)
                                <div style="font-size: 10px; color: #6b7280;">SKU: {{ $item->product->sku }}</div>
                            @endif
                        </td>
                        <td class="c">{{ $item->batch_number ?: '—' }}</td>
                        <td class="c">{{ $item->expiry_date ? $item->expiry_date->format('M Y') : '—' }}</td>
                        <td class="r">
                            <strong>{{ \App\Support\Format::qty($item->quantity) }}</strong> {{ $item->product?->unit ?? 'units' }}
                        </td>
                        <td class="r">₹{{ number_format($item->unit_cost, 2) }}</td>
                        <td class="r">{{ $item->tax_percent > 0 ? $item->tax_percent . '%' : '0%' }}</td>
                        <td class="r"><strong>₹{{ number_format($item->line_total, 2) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Breakdown -->
        <div class="totals">
            <table>
                <tr>
                    <td>Taxable Subtotal</td>
                    <td>₹{{ number_format($purchaseBill->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td>Taxes / GST</td>
                    <td>₹{{ number_format($purchaseBill->tax_amount, 2) }}</td>
                </tr>
                @if($purchaseBill->discount > 0)
                    <tr>
                        <td>Discount</td>
                        <td>-₹{{ number_format($purchaseBill->discount, 2) }}</td>
                    </tr>
                @endif
                @if($purchaseBill->shipping_cost > 0)
                    <tr>
                        <td>Freight / Shipping</td>
                        <td>+₹{{ number_format($purchaseBill->shipping_cost, 2) }}</td>
                    </tr>
                @endif
                <tr class="grand">
                    <td>Grand Total</td>
                    <td>₹{{ number_format($purchaseBill->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td>Amount Paid</td>
                    <td>₹{{ number_format($purchaseBill->paid_amount, 2) }}</td>
                </tr>
                <tr class="balance {{ $purchaseBill->balance_due > 0 ? 'due' : 'clear' }}">
                    <td>Balance Due</td>
                    <td>₹{{ number_format($purchaseBill->balance_due, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Payments Log (if any) -->
        @if($purchaseBill->payments->isNotEmpty())
            <div style="margin-top: 24px;">
                <h3 style="font-size: 11px; text-transform: uppercase; color: #6b7280; letter-spacing: .08em; margin-bottom: 6px;">Payment Transactions</h3>
                <table class="payments-table">
                    <thead>
                        <tr>
                            <th>Receipt #</th>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Reference / UTR</th>
                            <th style="text-align: right;">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseBill->payments as $pm)
                            <tr>
                                <td>{{ $pm->payment_number }}</td>
                                <td>{{ $pm->payment_date->format('d M, Y') }}</td>
                                <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $pm->payment_method) }}</td>
                                <td>{{ $pm->reference_no ?: '—' }}</td>
                                <td style="text-align: right; font-weight: 700; color: #059669;">₹{{ number_format($pm->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Signatures -->
        <div class="signatures">
            <div>
                <div class="line">Received By (Store Incharge)</div>
            </div>
            <div>
                <div class="line">Verified & Approved By</div>
            </div>
        </div>
    </div>
</body>
</html>
