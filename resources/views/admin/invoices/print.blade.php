<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $invoice->number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, sans-serif; color: #111827; font-size: 13px; background: #f3f4f6; }
        .page { max-width: 800px; margin: 24px auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        @media print { body { background: #fff; } .page { box-shadow: none; margin: 0; border-radius: 0; padding: 20px; max-width: none; } .no-print { display: none !important; } }
        .header { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; padding-bottom: 20px; border-bottom: 3px solid #16a34a; }
        .logo { max-height: 64px; max-width: 180px; object-fit: contain; }
        .company h1 { font-size: 20px; font-weight: 800; color: #111827; }
        .company p { font-size: 11px; color: #6b7280; line-height: 1.5; margin-top: 4px; }
        .meta { text-align: right; flex-shrink: 0; }
        .meta .inv-label { font-size: 22px; font-weight: 800; color: #16a34a; letter-spacing: .05em; }
        .meta table { margin-top: 8px; font-size: 11.5px; }
        .meta td { padding: 1.5px 0; color: #374151; }
        .meta td:first-child { text-align: right; color: #6b7280; padding-right: 12px; }
        .meta td:last-child { text-align: right; font-weight: 600; }
        .billto { display: flex; justify-content: space-between; margin: 24px 0; gap: 24px; }
        .billto .block h2 { font-size: 11px; text-transform: uppercase; letter-spacing: .08em; color: #6b7280; margin-bottom: 6px; }
        .billto .block p { font-size: 12.5px; line-height: 1.6; }
        .billto .block .name { font-weight: 700; font-size: 14px; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.items th { background: #16a34a; color: #fff; text-align: left; font-size: 10.5px; text-transform: uppercase; letter-spacing: .05em; padding: 8px 10px; }
        table.items th.r, table.items td.r { text-align: right; }
        table.items td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; font-size: 12px; }
        table.items tr:nth-child(even) td { background: #f9fafb; }
        .totals { display: flex; justify-content: flex-end; margin-top: 16px; }
        .totals table { width: 280px; font-size: 12.5px; }
        .totals td { padding: 5px 10px; }
        .totals td:first-child { color: #6b7280; }
        .totals td:last-child { text-align: right; font-weight: 600; }
        .totals .grand td { border-top: 2px solid #16a34a; padding-top: 8px; font-size: 15px; }
        .totals .grand td:last-child { color: #16a34a; font-weight: 800; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
        .status-unpaid { background: #fee2e2; color: #b91c1c; }
        .status-partial { background: #fef3c7; color: #b45309; }
        .status-paid { background: #dcfce7; color: #15803d; }
        .status-cancelled { background: #f3f4f6; color: #6b7280; }
        .terms { margin-top: 28px; font-size: 10.5px; color: #6b7280; }
        .terms h3 { font-size: 11px; text-transform: uppercase; letter-spacing: .08em; color: #374151; margin-bottom: 4px; }
        .signature { display: flex; justify-content: flex-end; margin-top: 48px; }
        .signature div { text-align: center; }
        .signature .line { width: 200px; border-top: 1px solid #9ca3af; padding-top: 6px; font-size: 11px; color: #6b7280; }
        .print-btn { position: fixed; top: 16px; right: 16px; }
        .print-btn button { background: #16a34a; color: #fff; border: 0; padding: 10px 20px; border-radius: 10px; font-weight: 700; font-size: 13px; cursor: pointer; }
        .cancelled-stamp { position: absolute; top: 180px; left: 50%; transform: translateX(-50%) rotate(-12deg); font-size: 60px; font-weight: 800; color: rgba(107,114,128,.25); border: 4px solid rgba(107,114,128,.25); padding: 4px 24px; border-radius: 12px; letter-spacing: .1em; }
        .relative { position: relative; }
        .note { margin-top: 16px; font-size: 11px; color: #374151; background: #f9fafb; padding: 10px 12px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="print-btn no-print"><button onclick="window.print()">🖨 Print / Save as PDF</button></div>

    <div class="page relative">
        @if($invoice->isCancelled())
            <div class="cancelled-stamp">CANCELLED</div>
        @endif

        <div class="header">
            <div class="company">
                @if(config('invoice.logo') && \App\Support\Media::exists(config('invoice.logo')))
                    <img src="{{ public_path(ltrim(config('invoice.logo'), '/')) }}" alt="Logo" class="logo" style="margin-bottom: 10px;">
                @endif
                <h1>{{ config('invoice.company_name') }}</h1>
                <p>{!! nl2br(e(config('invoice.address'))) !!}</p>
                @if(config('invoice.phone'))<p>Phone: {{ config('invoice.phone') }}</p>@endif
                @if(config('invoice.email'))<p>Email: {{ config('invoice.email') }}</p>@endif
                @if(config('invoice.gst_no'))<p>GSTIN: {{ config('invoice.gst_no') }}</p>@endif
            </div>
            <div class="meta">
                <div class="inv-label">INVOICE</div>
                <table>
                    <tr><td>Invoice No</td><td>{{ $invoice->number }}</td></tr>
                    <tr><td>Date</td><td>{{ $invoice->invoice_date->format('d M Y') }}</td></tr>
                    @if($invoice->due_date)<tr><td>Due Date</td><td>{{ $invoice->due_date->format('d M Y') }}</td></tr>@endif
                    <tr><td>Status</td><td><span class="status-badge status-{{ $invoice->status }}">{{ strtoupper(\App\Models\Invoice::STATUSES[$invoice->status] ?? $invoice->status) }}</span></td></tr>
                </table>
            </div>
        </div>

        <div class="billto">
            <div class="block">
                <h2>Bill To</h2>
                <p class="name">{{ $invoice->customer_name }}</p>
                @if($invoice->customer)
                    @if($invoice->customer->phone)<p>{{ $invoice->customer->phone }}</p>@endif
                    @if($invoice->customer->address)<p>{!! nl2br(e($invoice->customer->address)) !!}</p>@endif
                @endif
            </div>
            @if($invoice->workOrder)
                <div class="block" style="text-align: right;">
                    <h2>Work Order</h2>
                    <p>{{ $invoice->workOrder->number }}</p>
                    <p>{{ $invoice->workOrder->service_name }}</p>
                </div>
            @endif
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th style="width: 36px;">#</th>
                    <th>Item</th>
                    <th class="r" style="width: 90px;">Qty</th>
                    <th class="r" style="width: 90px;">Rate</th>
                    <th class="r" style="width: 80px;">Discount</th>
                    <th class="r" style="width: 60px;">GST</th>
                    <th class="r" style="width: 100px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->name }}@if($item->unit) <span style="color:#9ca3af;">({{ $item->unit }})</span>@endif</td>
                        <td class="r">{{ $item->qty }}</td>
                        <td class="r">₹{{ number_format((float) $item->rate, 0) }}</td>
                        <td class="r">{{ (float) $item->discount > 0 ? '₹' . number_format((float) $item->discount, 0) : '—' }}</td>
                        <td class="r">{{ $item->gst_rate }}%</td>
                        <td class="r">₹{{ number_format((float) $item->total, 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr><td>Subtotal</td><td>₹{{ number_format((float) $invoice->subtotal, 0) }}</td></tr>
                <tr><td>Discount</td><td>₹{{ number_format((float) $invoice->discount_total, 0) }}</td></tr>
                <tr><td>GST</td><td>₹{{ number_format((float) $invoice->gst_total, 0) }}</td></tr>
                <tr class="grand"><td>Grand Total</td><td>₹{{ number_format((float) $invoice->grand_total, 0) }}</td></tr>
                <tr><td>Amount Paid</td><td>₹{{ number_format((float) $invoice->amount_paid, 0) }}</td></tr>
                <tr><td><strong>Balance Due</strong></td><td><strong>₹{{ number_format($invoice->balanceDue(), 0) }}</strong></td></tr>
            </table>
        </div>

        @if($invoice->terms)
            <div class="terms">
                <h3>Terms & Conditions</h3>
                {!! nl2br(e($invoice->terms)) !!}
            </div>
        @endif

        @if($invoice->notes)
            <div class="note"><strong>Notes:</strong> {{ $invoice->notes }}</div>
        @endif

        <div class="signature">
            <div>
                <div class="line">For {{ config('invoice.company_name') }}</div>
            </div>
        </div>
    </div>

    @if(empty($forPdf))
        <script>
            setTimeout(function () { window.print(); }, 400);
        </script>
    @endif
</body>
</html>
