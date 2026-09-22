<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quotation {{ $quotation->number }} — {{ $quotation->customer_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; color: #111827; font-size: 13px; background: #f3f4f6; }
        .page { max-width: 820px; margin: 24px auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        @media print {
            body { background: #fff; }
            .page { box-shadow: none; margin: 0; border-radius: 0; padding: 20px; max-width: none; }
            .no-print { display: none !important; }
        }
        .header { display: table; width: 100%; padding-bottom: 20px; border-bottom: 3px solid #16a34a; }
        .header-left { display: table-cell; vertical-align: top; width: 60%; }
        .header-right { display: table-cell; vertical-align: top; width: 40%; text-align: right; }
        .logo { max-height: 56px; max-width: 180px; object-fit: contain; margin-bottom: 8px; }
        .company-name { font-size: 20px; font-weight: 800; color: #111827; }
        .company-info { font-size: 11px; color: #4b5563; line-height: 1.5; margin-top: 4px; }
        .doc-title { font-size: 20px; font-weight: 800; color: #16a34a; letter-spacing: .04em; }
        .doc-subtitle { font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; }
        .meta-table { margin-top: 8px; font-size: 11.5px; width: 100%; }
        .meta-table td { padding: 2px 0; color: #374151; }
        .meta-table td:first-child { text-align: right; color: #6b7280; padding-right: 12px; font-weight: 500; }
        .meta-table td:last-child { text-align: right; font-weight: 700; }

        .client-section { display: table; width: 100%; margin: 24px 0; }
        .client-col { display: table-cell; vertical-align: top; width: 55%; }
        .service-col { display: table-cell; vertical-align: top; width: 45%; text-align: right; }
        .section-label { font-size: 10.5px; text-transform: uppercase; letter-spacing: .08em; color: #6b7280; font-weight: 700; margin-bottom: 6px; }
        .client-name { font-weight: 700; font-size: 14px; color: #111827; }
        .client-text { font-size: 12px; line-height: 1.55; color: #374151; }

        table.items { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.items th { background: #16a34a; color: #fff; text-align: left; font-size: 10.5px; text-transform: uppercase; letter-spacing: .05em; padding: 9px 10px; }
        table.items th.r, table.items td.r { text-align: right; }
        table.items th.c, table.items td.c { text-align: center; }
        table.items td { padding: 9px 10px; border-bottom: 1px solid #e5e7eb; font-size: 12px; }
        table.items tr:nth-child(even) td { background: #f9fafb; }
        .item-unit { color: #6b7280; font-size: 11px; margin-left: 3px; }

        .totals-section { display: table; width: 100%; margin-top: 18px; }
        .totals-notes { display: table-cell; vertical-align: top; width: 50%; font-size: 11px; color: #6b7280; padding-right: 20px; }
        .totals-box { display: table-cell; vertical-align: top; width: 50%; }
        .totals-table { width: 100%; font-size: 12.5px; border-collapse: collapse; }
        .totals-table td { padding: 5px 10px; }
        .totals-table td:first-child { color: #4b5563; }
        .totals-table td:last-child { text-align: right; font-weight: 600; }
        .totals-table .grand td { border-top: 2px solid #16a34a; padding-top: 8px; font-size: 15px; }
        .totals-table .grand td:last-child { color: #16a34a; font-weight: 800; font-size: 16px; }

        .terms { margin-top: 24px; padding-top: 16px; border-top: 1px solid #e5e7eb; font-size: 11px; color: #4b5563; }
        .terms h3 { font-size: 11px; text-transform: uppercase; letter-spacing: .08em; color: #111827; margin-bottom: 6px; font-weight: 700; }
        .terms p { line-height: 1.6; white-space: pre-line; }

        .bank-details { margin-top: 16px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; font-size: 11px; }
        .bank-details strong { color: #111827; }

        .footer-sig { display: table; width: 100%; margin-top: 40px; padding-top: 10px; }
        .sig-col { display: table-cell; width: 50%; vertical-align: bottom; }
        .sig-right { display: table-cell; width: 50%; text-align: right; vertical-align: bottom; }
        .sig-line { display: inline-block; width: 180px; border-top: 1px solid #9ca3af; padding-top: 5px; font-size: 11px; color: #6b7280; text-align: center; }

        .badge { display: inline-block; padding: 3px 8px; border-radius: 999px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .badge-approved { background: #dcfce7; color: #15803d; }
        .badge-sent { background: #dbeafe; color: #1d4ed8; }
        .badge-draft { background: #f3f4f6; color: #4b5563; }

        .print-btn { position: fixed; top: 16px; right: 16px; z-index: 99; }
        .print-btn button { background: #16a34a; color: #fff; border: 0; padding: 10px 20px; border-radius: 10px; font-weight: 700; font-size: 13px; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0,0,0,.1); }
        .print-btn button:hover { background: #15803d; }
    </style>
</head>
<body>
    @if(empty($forPdf))
        <div class="print-btn no-print">
            <button onclick="window.print()">🖨 Print / Save as PDF</button>
        </div>
    @endif

    <div class="page">
        {{-- Header --}}
        <div class="header">
            <div class="header-left">
                @if(config('invoice.logo') && \App\Support\Media::exists(config('invoice.logo')))
                    @if(!empty($forPdf))
                        <img src="{{ public_path(ltrim(config('invoice.logo'), '/')) }}" alt="Logo" class="logo">
                    @else
                        <img src="{{ \App\Support\Media::url(config('invoice.logo')) }}" alt="Logo" class="logo">
                    @endif
                @endif
                <div class="company-name">{{ config('invoice.company_name', config('shop.site_name', 'Plant Tech Agro')) }}</div>
                <div class="company-info">
                    {!! nl2br(e(config('invoice.address', config('shop.site_address', '56 Murad House, Pine Lane-8, Kurso Rajbagh, Srinagar-190008')))) !!}
                    @if(config('invoice.phone', config('shop.site_phone')))<br>Phone: {{ config('invoice.phone', config('shop.site_phone')) }}@endif
                    @if(config('invoice.email', config('shop.site_email')))<br>Email: {{ config('invoice.email', config('shop.site_email')) }}@endif
                    @if(config('invoice.gst_no'))<br>GSTIN: {{ config('invoice.gst_no') }}@endif
                </div>
            </div>
            <div class="header-right">
                <div class="doc-title">PROFORMA INVOICE</div>
                <div class="doc-subtitle">Price Estimate & Quotation</div>
                <table class="meta-table">
                    <tr>
                        <td>Quotation No</td>
                        <td>{{ $quotation->number }}</td>
                    </tr>
                    <tr>
                        <td>Date</td>
                        <td>{{ $quotation->date ? $quotation->date->format('d M Y') : date('d M Y') }}</td>
                    </tr>
                    @if($quotation->valid_until)
                        <tr>
                            <td>Valid Until</td>
                            <td>{{ $quotation->valid_until->format('d M Y') }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td>Status</td>
                        <td>
                            <span class="badge badge-{{ $quotation->status }}">
                                {{ strtoupper($quotation->statusLabel()) }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Client & Service Information --}}
        <div class="client-section">
            <div class="client-col">
                <div class="section-label">Quotation Prepared For</div>
                <div class="client-name">{{ $quotation->customer_name }}</div>
                <div class="client-text">
                    Phone: {{ $quotation->customer_phone }}
                    @if($quotation->customer_email)<br>Email: {{ $quotation->customer_email }}@endif
                    @if($quotation->customer_area)<br>Area: {{ $quotation->customer_area }}@endif
                    @if($quotation->customer_address)<br>Orchard Location: {!! nl2br(e($quotation->customer_address)) !!}@endif
                </div>
            </div>
            <div class="service-col">
                <div class="section-label">Project / Service Scope</div>
                <div class="client-name" style="color: #16a34a;">
                    {{ $quotation->service?->name ?? 'Custom Agricultural Project' }}
                </div>
                @if($quotation->workOrder)
                    <div class="client-text" style="margin-top: 4px; font-weight: 600; color: #15803d;">
                        Work Order: {{ $quotation->workOrder->number }}
                    </div>
                @endif
                @if($quotation->lead_id)
                    <div class="client-text" style="color: #6b7280;">
                        Lead Reference: #{{ $quotation->lead_id }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Items Table --}}
        <table class="items">
            <thead>
                <tr>
                    <th style="width: 32px;" class="c">#</th>
                    <th>Deliverable / Service Description</th>
                    <th style="width: 70px;" class="c">Unit</th>
                    <th class="r" style="width: 70px;">Qty</th>
                    <th class="r" style="width: 95px;">Rate (₹)</th>
                    <th class="r" style="width: 80px;">Disc (₹)</th>
                    <th class="r" style="width: 60px;">GST</th>
                    <th class="r" style="width: 105px;">Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->items as $idx => $item)
                    <tr>
                        <td class="c" style="color: #6b7280;">{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $item->name }}</strong>
                        </td>
                        <td class="c">{{ $item->unit ?: '—' }}</td>
                        <td class="r">{{ (float) $item->qty }}</td>
                        <td class="r">₹{{ number_format((float) $item->rate, 2) }}</td>
                        <td class="r">{{ (float) $item->discount > 0 ? '₹' . number_format((float) $item->discount, 2) : '—' }}</td>
                        <td class="r">{{ (float) $item->gst_rate }}%</td>
                        <td class="r"><strong>₹{{ number_format((float) $item->total, 2) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="totals-section">
            <div class="totals-notes">
                @if($quotation->notes)
                    <div class="section-label">Scope Notes</div>
                    <p style="font-size: 11px; line-height: 1.5; color: #4b5563;">{{ $quotation->notes }}</p>
                @endif
            </div>
            <div class="totals-box">
                <table class="totals-table">
                    <tr>
                        <td>Taxable Subtotal</td>
                        <td>₹{{ number_format((float) $quotation->subtotal, 2) }}</td>
                    </tr>
                    @if($quotation->discount_total > 0)
                        <tr>
                            <td style="color: #dc2626;">Discount</td>
                            <td style="color: #dc2626;">-₹{{ number_format((float) $quotation->discount_total, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td>GST / Applicable Taxes</td>
                        <td>+₹{{ number_format((float) $quotation->gst_total, 2) }}</td>
                    </tr>
                    <tr class="grand">
                        <td>Grand Total (INR)</td>
                        <td>₹{{ number_format((float) $quotation->grand_total, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Terms & Conditions --}}
        @if($quotation->terms)
            <div class="terms">
                <h3>Terms & Project Conditions</h3>
                <p>{{ $quotation->terms }}</p>
            </div>
        @endif

        {{-- Bank Payment Details --}}
        @if(config('invoice.bank_name'))
            <div class="bank-details">
                <strong>Bank Account Details for Payment:</strong><br>
                Bank: {{ config('invoice.bank_name') }} | A/C Name: {{ config('invoice.company_name', 'Plant Tech Agro') }} | A/C No: {{ config('invoice.account_no') }} | IFSC: {{ config('invoice.ifsc') }}
                @if(config('invoice.upi_id')) | UPI: {{ config('invoice.upi_id') }}@endif
            </div>
        @endif

        {{-- Signatures --}}
        <div class="footer-sig">
            <div class="sig-col">
                <div class="sig-line">Client Acceptance Signature</div>
            </div>
            <div class="sig-right">
                <div class="sig-line">For {{ config('invoice.company_name', 'Plant Tech Agro') }}<br>Authorized Signatory</div>
            </div>
        </div>
    </div>
</body>
</html>
