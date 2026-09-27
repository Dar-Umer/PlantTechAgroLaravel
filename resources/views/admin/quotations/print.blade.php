<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Proforma Invoice {{ $quotation->number }} — {{ $quotation->customer_name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @php
        $accentColor = $quotation->getAccentColor();
    @endphp
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

        :root {
            --brand-accent: {{ $accentColor }};
        }
        @page {
            size: A4 portrait;
            margin: 8mm 9mm 8mm 9mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.4;
            background: #f8fafc;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            letter-spacing: -0.011em;
        }
        .proforma-container {
            max-width: 820px;
            margin: 20px auto;
            background: #ffffff;
            padding: 26px 30px 22px 30px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            border-top: 4px solid var(--brand-accent, #064e3b);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            position: relative;
        }
        @media print {
            body {
                background: #ffffff;
                font-size: 10.5px;
            }
            .proforma-container {
                box-shadow: none;
                margin: 0;
                padding: 6px 12px;
                max-width: 100%;
                border-radius: 0;
                border: none;
                border-top: 4px solid var(--brand-accent, #064e3b);
            }
            .no-print {
                display: none !important;
            }
        }

        /* Number tabular alignment */
        .tabular-nums, .num {
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum" 1;
        }

        /* Helpers & Tables */
        .w-full { width: 100%; }
        .tbl { width: 100%; border-collapse: collapse; }
        .tbl td, .tbl th { vertical-align: top; }

        /* Floating action buttons */
        .action-bar {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 100;
            display: flex;
            gap: 10px;
        }
        .btn-print {
            background: var(--brand-accent, #064e3b);
            color: #ffffff;
            border: 0;
            padding: 9px 18px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.12);
            display: inline-flex;
            align-items: center;
            gap: 7px;
            text-decoration: none;
            transition: opacity 0.15s;
        }
        .btn-print:hover { opacity: 0.92; }
        .btn-back {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 9px 16px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: background 0.15s;
        }
        .btn-back:hover { background: #f8fafc; }

        /* Header Layout */
        .header-tbl { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .brand-col { width: 62%; vertical-align: top; }
        .doc-badge-col { width: 38%; vertical-align: top; text-align: right; }

        .logo-img { height: 46px; max-height: 46px; max-width: 220px; width: auto; object-fit: contain; display: block; }
        .brand-title {
            font-size: 23px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.03em;
            line-height: 1.1;
        }
        .brand-title span.tech { color: #f97316; }
        .brand-tagline {
            font-size: 9px;
            font-weight: 800;
            color: var(--brand-accent, #064e3b);
            text-transform: uppercase;
            letter-spacing: 0.16em;
            margin-top: 3px;
        }

        .slogan-text {
            font-style: italic;
            font-size: 10.5px;
            color: #475569;
            margin-top: 5px;
            font-weight: 500;
            display: block;
        }

        .company-contacts {
            margin-top: 8px;
            font-size: 9.5px;
            color: #475569;
            line-height: 1.5;
        }
        .contact-item {
            display: inline-block;
            margin-right: 12px;
            white-space: nowrap;
        }
        .contact-icon {
            color: var(--brand-accent, #064e3b);
            font-weight: bold;
            margin-right: 3px;
        }

        /* Top Right Curved Banner */
        .proforma-banner {
            background: var(--brand-accent, #064e3b);
            color: #ffffff;
            padding: 10px 14px;
            border-radius: 8px;
            text-align: right;
            box-shadow: 0 2px 4px rgba(0,0,0,0.06);
            display: inline-block;
            width: 100%;
        }
        .proforma-title {
            font-size: 16px;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #ffffff;
            line-height: 1.15;
        }
        .proforma-sub {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: rgba(255,255,255,0.9);
            text-transform: uppercase;
            margin-top: 3px;
        }

        .meta-list {
            margin-top: 8px;
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .meta-list td { padding: 2px 0; }
        .meta-lbl {
            text-align: right;
            color: #64748b;
            padding-right: 8px;
            font-weight: 500;
            width: 50%;
        }
        .meta-val {
            text-align: left;
            font-weight: 700;
            color: #0f172a;
            width: 50%;
        }
        .badge-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: #e0f2fe;
            color: #0284c7;
            border: 1px solid #bae6fd;
        }
        .badge-status.approved { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
        .badge-status.draft { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }

        /* Top 2 Cards: Client & Scope */
        .two-cards-tbl {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 0;
            margin-bottom: 12px;
        }
        .card-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 11px 13px;
            vertical-align: top;
            width: 50%;
        }
        .card-header-bar {
            display: table;
            width: 100%;
            padding-bottom: 6px;
            margin-bottom: 7px;
            border-bottom: 1px solid #e2e8f0;
        }
        .card-icon {
            display: table-cell;
            width: 20px;
            vertical-align: middle;
            color: var(--brand-accent, #064e3b);
            font-size: 13px;
        }
        .card-header-title {
            display: table-cell;
            vertical-align: middle;
            font-size: 10px;
            font-weight: 800;
            color: var(--brand-accent, #064e3b);
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .kv-table { width: 100%; border-collapse: collapse; font-size: 10.5px; }
        .kv-table td { padding: 2px 0; }
        .kv-label { width: 34%; color: #64748b; font-weight: 500; font-size: 10px; }
        .kv-colon { width: 4%; color: #94a3b8; text-align: center; }
        .kv-val { width: 62%; font-weight: 600; color: #1e293b; }
        .kv-val.strong { font-weight: 800; color: #0f172a; font-size: 11.5px; }

        /* Scope inner variety card */
        .scope-main-title {
            font-size: 14px;
            font-weight: 800;
            color: var(--brand-accent, #064e3b);
            line-height: 1.2;
        }
        .scope-sub-title {
            font-size: 9.5px;
            color: #64748b;
            margin-bottom: 6px;
            font-weight: 500;
        }
        .variety-subbox {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 7px 9px;
            margin-top: 5px;
        }
        .variety-head {
            font-size: 9px;
            font-weight: 800;
            color: #166534;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 4px;
            padding-bottom: 3px;
            border-bottom: 1px solid #dcfce7;
        }

        /* Items / Deliverables Table */
        .deliverables-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #cbd5e1;
        }
        .deliverables-table th {
            background: var(--brand-accent, #064e3b);
            color: #ffffff;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 8px 9px;
            text-align: left;
            border-right: 1px solid rgba(255,255,255,0.12);
        }
        .deliverables-table th:last-child { border-right: none; }
        .deliverables-table td {
            padding: 7px 9px;
            font-size: 10.5px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #f1f5f9;
            color: #334155;
        }
        .deliverables-table tr:nth-child(even) td {
            background: #fafbfc;
        }
        .deliverables-table td:last-child { border-right: none; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        /* Totals Block */
        .totals-tbl {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .totals-tbl td { vertical-align: top; }
        .totals-inner-tbl {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
        }
        .totals-inner-tbl td {
            padding: 3.5px 8px;
        }
        .totals-inner-tbl .lbl {
            text-align: left;
            color: #64748b;
            font-weight: 500;
        }
        .totals-inner-tbl .val {
            text-align: right;
            font-weight: 700;
            color: #0f172a;
        }
        .grand-total-bar {
            background: var(--brand-accent, #064e3b);
            color: #ffffff;
            border-radius: 8px;
            margin-top: 6px;
            padding: 7px 12px;
        }
        .grand-total-bar td {
            padding: 0;
            color: #ffffff;
            font-size: 12.5px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .grand-total-bar .amt {
            text-align: right;
            font-size: 14.5px;
            color: #ffffff;
            font-weight: 900;
        }

        /* Middle Two Columns: Payment Schedule & Inclusions */
        .mid-tbl {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 0;
            margin-top: 12px;
        }
        .mid-col {
            width: 50%;
            vertical-align: top;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 11px 13px;
        }

        /* Payment Stepper */
        .step-row {
            display: table;
            width: 100%;
            margin-bottom: 7px;
            position: relative;
        }
        .step-row:last-child { margin-bottom: 0; }
        .step-node {
            display: table-cell;
            width: 16px;
            vertical-align: middle;
            text-align: center;
        }
        .step-dot {
            width: 10px;
            height: 10px;
            background: #f59e0b;
            border: 2px solid #ffffff;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 1px #d97706;
        }
        .step-pill {
            display: table-cell;
            width: 48px;
            padding-left: 6px;
            vertical-align: middle;
        }
        .pct-badge {
            background: var(--brand-accent, #064e3b);
            color: #ffffff;
            font-size: 9px;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 4px;
            text-align: center;
            display: inline-block;
            letter-spacing: 0.02em;
        }
        .step-desc {
            display: table-cell;
            padding-left: 6px;
            font-size: 10px;
            color: #334155;
            vertical-align: middle;
            font-weight: 500;
        }
        .step-amt {
            display: table-cell;
            text-align: right;
            font-weight: 800;
            font-size: 11px;
            color: #0f172a;
            vertical-align: middle;
        }

        /* Package Inclusions Boxes */
        .package-subhead {
            font-size: 10px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
        }
        .inclusions-tbl {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
        }
        .inc-card {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            text-align: center;
            padding: 8px 4px 6px 4px;
            width: 33.33%;
            vertical-align: middle;
        }
        .inc-icon-wrap {
            font-size: 15px;
            line-height: 1;
            margin-bottom: 2px;
        }
        .inc-label {
            font-size: 8.5px;
            font-weight: 700;
            color: #166534;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .inc-val {
            font-size: 15px;
            font-weight: 900;
            color: var(--brand-accent, #064e3b);
            margin-top: 1px;
            line-height: 1;
        }

        /* Additional Notes */
        .notes-card {
            background: #fffbeb;
            border: 1px solid #fef08a;
            border-radius: 8px;
            padding: 9px 13px;
            margin-top: 12px;
        }
        .notes-head {
            font-size: 10px;
            font-weight: 800;
            color: #92400e;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 4px;
        }
        .notes-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .notes-list li {
            font-size: 9.5px;
            color: #78350f;
            padding-left: 12px;
            position: relative;
            margin-bottom: 2px;
            line-height: 1.45;
        }
        .notes-list li:before {
            content: "•";
            position: absolute;
            left: 2px;
            color: #b45309;
            font-weight: bold;
        }

        /* Bottom Section: Bank & Terms */
        .bottom-tbl {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 0;
            margin-top: 12px;
        }
        .bottom-col {
            width: 50%;
            vertical-align: top;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 9px 13px;
        }

        .terms-ol {
            padding-left: 14px;
            margin: 0;
            font-size: 9px;
            color: #475569;
            line-height: 1.45;
        }
        .terms-ol li {
            margin-bottom: 2.5px;
        }

        /* Signatures */
        .sig-tbl {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 0;
            margin-top: 18px;
        }
        .sig-col {
            width: 50%;
            vertical-align: top;
            border-top: 1px dashed #cbd5e1;
            padding-top: 7px;
        }
        .sig-head {
            font-size: 10px;
            font-weight: 800;
            color: var(--brand-accent, #064e3b);
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .sig-line-tbl { width: 100%; border-collapse: collapse; font-size: 9.5px; color: #475569; }
        .sig-line-tbl td { padding: 2px 0; }

        /* Bottom Banner / Footer */
        .footer-banner-tbl {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .footer-left-col {
            background: var(--brand-accent, #064e3b);
            color: #ffffff;
            padding: 8px 12px;
            vertical-align: middle;
            width: 68%;
        }
        .features-tbl { width: 100%; border-collapse: collapse; }
        .feature-cell {
            text-align: center;
            padding: 0 4px;
            border-right: 1px solid rgba(255,255,255,0.15);
            width: 20%;
        }
        .feature-cell:last-child { border-right: none; }
        .feat-icon { font-size: 12px; line-height: 1; }
        .feat-text {
            font-size: 8px;
            font-weight: 700;
            color: #e2e8f0;
            line-height: 1.15;
            margin-top: 2px;
            white-space: nowrap;
            letter-spacing: 0.02em;
        }

        .footer-right-col {
            background: #f59e0b;
            color: #0f172a;
            padding: 8px 14px;
            vertical-align: middle;
            text-align: right;
            width: 32%;
        }
        .comm-title {
            font-size: 12px;
            font-weight: 900;
            color: var(--brand-accent, #064e3b);
            line-height: 1.1;
            letter-spacing: -0.02em;
        }
        .comm-sub {
            font-size: 9.5px;
            font-weight: 800;
            color: #1e293b;
            letter-spacing: 0.03em;
        }
    </style>
</head>
<body>
    @if(empty($forPdf))
        <div class="action-bar no-print">
            <a href="{{ route('admin.quotations.show', $quotation) }}" class="btn-back">
                &larr; Back to Quotation
            </a>
            <button onclick="window.print()" class="btn-print">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print / Save as PDF
            </button>
        </div>
    @endif

    <div class="proforma-container">

        {{-- 1. Header Section --}}
        <table class="header-tbl">
            <tr>
                <td class="brand-col">
                    <div class="logo-wrap">
                        @php
                            $logoDisk = $quotation->getCompanyLogoDiskPath();
                            $logoUrl = $quotation->getCompanyLogoUrl();
                            $hasLogo = !empty($forPdf) ? ($logoDisk && file_exists($logoDisk)) : !empty($logoUrl);
                        @endphp

                        @if($hasLogo)
                            @if(!empty($forPdf))
                                <img src="{{ $logoDisk }}" alt="{{ $quotation->getCompanyName() }}" class="logo-img">
                            @else
                                <img src="{{ $logoUrl }}" alt="{{ $quotation->getCompanyName() }}" class="logo-img">
                            @endif
                        @else
                            {{-- High precision SVG Vector Logo matching PlantTech Agro branding --}}
                            <div style="display: table;">
                                <div style="display: table-cell; vertical-align: middle; padding-right: 9px;">
                                    <svg width="40" height="40" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect width="64" height="64" rx="14" fill="#064E3B"/>
                                        <path d="M32 12C32 12 21 22 21 34C21 40.075 25.925 45 32 45C38.075 45 43 40.075 43 34C43 22 32 12 32 12Z" fill="#10B981"/>
                                        <path d="M32 45V20" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round"/>
                                        <path d="M32 28C35 25 38 25 40 26" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round"/>
                                        <path d="M32 35C29 32 26 32 24 33" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round"/>
                                        <circle cx="43" cy="22" r="5" fill="#F97316"/>
                                    </svg>
                                </div>
                                <div style="display: table-cell; vertical-align: middle;">
                                    <div class="brand-title">Plant<span class="tech">Tech</span> Agro</div>
                                    <div class="brand-tagline">{{ config('quotation.company_tagline', 'Complete Orchard Solution') }}</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <span class="slogan-text">{{ config('quotation.company_slogan', 'From Planning to Plantation We Build Better Orchards.') }}</span>

                    <div class="company-contacts">
                        <span class="contact-item">
                            <span class="contact-icon">&#9679;</span> {{ $quotation->getCompanyAddress() }}
                        </span><br>
                        <span class="contact-item">
                            <span class="contact-icon">&#9742;</span> {{ $quotation->getCompanyPhone() }}
                        </span>
                        <span class="contact-item">
                            <span class="contact-icon">&#9993;</span> {{ $quotation->getCompanyEmail() }}
                        </span>
                        <span class="contact-item">
                            <span class="contact-icon">&#127760;</span> {{ $quotation->getCompanyWebsite() }}
                        </span>
                    </div>
                </td>

                <td class="doc-badge-col">
                    <div class="proforma-banner">
                        <div class="proforma-title">{{ $quotation->getDocumentTitle() }}</div>
                        <div class="proforma-sub">{{ $quotation->getDocumentSubtitle() }}</div>
                    </div>

                    <table class="meta-list">
                        <tr>
                            <td class="meta-lbl">Quotation No.</td>
                            <td class="meta-val" style="font-family: ui-monospace, SFMono-Regular, monospace; font-size: 11px; color: var(--brand-accent, #064e3b);">: {{ $quotation->number }}</td>
                        </tr>
                        <tr>
                            <td class="meta-lbl">Date</td>
                            <td class="meta-val tabular-nums">: {{ $quotation->date ? $quotation->date->format('d M Y') : date('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="meta-lbl">Valid Until</td>
                            <td class="meta-val tabular-nums">: {{ $quotation->valid_until ? $quotation->valid_until->format('d M Y') : '15 Days' }}</td>
                        </tr>
                        <tr>
                            <td class="meta-lbl">Status</td>
                            <td class="meta-val">:
                                <span class="badge-status {{ $quotation->status }}">
                                    {{ strtoupper($quotation->statusLabel()) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- 2. Client Info & Project / Service Scope (Two Equal Side-by-Side Cards) --}}
        <table class="two-cards-tbl">
            <tr>
                {{-- Left: Quotation Prepared For --}}
                <td class="card-box">
                    <div class="card-header-bar">
                        <span class="card-icon">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align: middle; display: inline-block;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </span>
                        <span class="card-header-title">Quotation Prepared For</span>
                    </div>

                    <table class="kv-table">
                        <tr>
                            <td class="kv-label">Name</td>
                            <td class="kv-colon">:</td>
                            <td class="kv-val strong">{{ $quotation->customer_name }}</td>
                        </tr>
                        <tr>
                            <td class="kv-label">Phone</td>
                            <td class="kv-colon">:</td>
                            <td class="kv-val tabular-nums">{{ $quotation->customer_phone }}</td>
                        </tr>
                        <tr>
                            <td class="kv-label">Email</td>
                            <td class="kv-colon">:</td>
                            <td class="kv-val">{{ $quotation->customer_email ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td class="kv-label">Area</td>
                            <td class="kv-colon">:</td>
                            <td class="kv-val">{{ $quotation->customer_area ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td class="kv-label">Orchard Location</td>
                            <td class="kv-colon">:</td>
                            <td class="kv-val">{{ $quotation->customer_address ?: '—' }}</td>
                        </tr>
                    </table>
                </td>

                {{-- Right: Project / Service Scope & Variety Details --}}
                <td class="card-box">
                    <div class="card-header-bar">
                        <span class="card-icon">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align: middle; display: inline-block;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </span>
                        <span class="card-header-title">Project / Service Scope</span>
                    </div>

                    <div class="scope-main-title">
                        {{ $quotation->scope_title ?: ($quotation->service?->name ?? 'Book an Orchard') }}
                    </div>
                    <div class="scope-sub-title">
                        {{ $quotation->scope_subtitle ?: 'Complete Orchard Development Solution' }}
                    </div>

                    @if($quotation->hasVarietyDetails())
                        <div class="variety-subbox">
                            <div class="variety-head">Variety &amp; Rootstock Specifications</div>
                            <table class="kv-table" style="font-size: 9.5px;">
                                <tr>
                                    <td class="kv-label" style="width: 44%;">Booked Variety</td>
                                    <td class="kv-colon">:</td>
                                    <td class="kv-val strong" style="width: 52%; color: #166534;">{{ $quotation->variety_name }}</td>
                                </tr>
                                @if($quotation->variety_specification && $quotation->variety_specification !== $quotation->variety_name)
                                    <tr>
                                        <td class="kv-label">Specification</td>
                                        <td class="kv-colon">:</td>
                                        <td class="kv-val">{{ $quotation->variety_specification }}</td>
                                    </tr>
                                @endif
                                @if($quotation->rootstock)
                                    <tr>
                                        <td class="kv-label">Rootstock</td>
                                        <td class="kv-colon">:</td>
                                        <td class="kv-val">{{ $quotation->rootstock }}</td>
                                    </tr>
                                @endif
                                @if($quotation->plants_per_kanal && $quotation->plants_per_kanal !== 'N/A')
                                    <tr>
                                        <td class="kv-label">Plants per Kanal</td>
                                        <td class="kv-colon">:</td>
                                        <td class="kv-val tabular-nums">{{ $quotation->plants_per_kanal }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        {{-- 3. Deliverables / Service Description Table --}}
        <table class="deliverables-table">
            <thead>
                <tr>
                    <th style="width: 28px;" class="text-center">#</th>
                    <th>Deliverable / Service Description</th>
                    <th style="width: 58px;" class="text-center">Unit</th>
                    <th style="width: 46px;" class="text-center">Qty</th>
                    <th style="width: 88px;" class="text-right">Rate (₹)</th>
                    <th style="width: 76px;" class="text-right">Discount (₹)</th>
                    <th style="width: 52px;" class="text-center">GST</th>
                    <th style="width: 98px;" class="text-right">Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($quotation->items as $idx => $item)
                    <tr>
                        <td class="text-center" style="color: #64748b; font-weight: 700;">{{ $idx + 1 }}</td>
                        <td>
                            <strong style="color: #0f172a;">{{ $item->name }}</strong>
                        </td>
                        <td class="text-center" style="color: #64748b;">{{ $item->unit ?: '—' }}</td>
                        <td class="text-center tabular-nums" style="font-weight: 600;">{{ (float) $item->qty }}</td>
                        <td class="text-right tabular-nums">₹{{ number_format((float) $item->rate, 2) }}</td>
                        <td class="text-right tabular-nums">{{ (float) $item->discount > 0 ? '₹' . number_format((float) $item->discount, 2) : '—' }}</td>
                        <td class="text-center tabular-nums">{{ (float) $item->gst_rate }}%</td>
                        <td class="text-right tabular-nums"><strong style="color: var(--brand-accent, #064e3b);">₹{{ number_format((float) $item->total, 2) }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 16px; color: #94a3b8;">No line items added</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Totals Summary --}}
        <table class="totals-tbl">
            <tr>
                <td style="width: 50%;">
                    @if($quotation->notes)
                        <div style="padding: 8px 12px; font-size: 9.5px; color: #475569; background: #f8fafc; border-left: 3px solid var(--brand-accent, #064e3b); border-radius: 4px; margin-top: 4px;">
                            <strong style="color: #0f172a; text-transform: uppercase; font-size: 9px; letter-spacing: 0.04em;">Scope Notes:</strong>
                            <div style="margin-top: 2px;">{{ $quotation->notes }}</div>
                        </div>
                    @endif
                </td>
                <td style="width: 50%;">
                    <table class="totals-inner-tbl">
                        <tr>
                            <td class="lbl">Taxable Subtotal</td>
                            <td class="val tabular-nums">₹{{ number_format((float) $quotation->subtotal, 2) }}</td>
                        </tr>
                        @if((float) $quotation->discount_total > 0)
                            <tr>
                                <td class="lbl" style="color: #dc2626;">Total Discount</td>
                                <td class="val tabular-nums" style="color: #dc2626;">-₹{{ number_format((float) $quotation->discount_total, 2) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="lbl">GST / Applicable Taxes ({{ (float) $quotation->items->avg('gst_rate') }}%)</td>
                            <td class="val tabular-nums">₹{{ number_format((float) $quotation->gst_total, 2) }}</td>
                        </tr>
                    </table>

                    <table class="tbl grand-total-bar">
                        <tr>
                            <td>Grand Total (INR)</td>
                            <td class="amt tabular-nums">₹{{ number_format((float) $quotation->grand_total, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- 4. Middle Section: Payment Schedule & Project Package Inclusions --}}
        @php
            $hasPackage = $quotation->hasPackageInclusions();
            $paymentSchedule = $quotation->getCalculatedPaymentSchedule();
        @endphp
        <table class="mid-tbl">
            <tr>
                {{-- Payment Schedule Stepper --}}
                <td class="mid-col" style="{{ $hasPackage ? 'width: 50%;' : 'width: 100%;' }}">
                    <div class="card-header-bar">
                        <span class="card-icon">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align: middle; display: inline-block;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </span>
                        <span class="card-header-title">Payment Schedule</span>
                    </div>

                    @foreach($paymentSchedule as $milestone)
                        <div class="step-row">
                            <div class="step-node">
                                <span class="step-dot"></span>
                            </div>
                            <div class="step-pill">
                                <span class="pct-badge tabular-nums">{{ (int) $milestone['percent'] }}%</span>
                            </div>
                            <div class="step-desc">
                                {{ $milestone['stage'] }}
                            </div>
                            <div class="step-amt tabular-nums">
                                ₹{{ number_format((float) $milestone['amount'], 2) }}
                            </div>
                        </div>
                    @endforeach
                </td>

                @if($hasPackage)
                    {{-- Project Package Inclusions --}}
                    <td class="mid-col" style="width: 50%;">
                        <div class="card-header-bar">
                            <span class="card-icon">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align: middle; display: inline-block;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </span>
                            <span class="card-header-title">Project Package / Inclusions</span>
                        </div>

                        <div class="package-subhead">
                            {{ $quotation->package_title ?: 'Per Kanal Standard Package' }}
                        </div>

                        <table class="inclusions-tbl">
                            <tr>
                                <td class="inc-card">
                                    <div class="inc-icon-wrap">&#129699;</div>
                                    <div class="inc-label">Poles</div>
                                    <div class="inc-val tabular-nums">{{ $quotation->package_poles ?? 19 }}</div>
                                </td>
                                <td class="inc-card">
                                    <div class="inc-icon-wrap">&#9875;</div>
                                    <div class="inc-label">Anchors</div>
                                    <div class="inc-val tabular-nums">{{ $quotation->package_anchors ?? 6 }}</div>
                                </td>
                                <td class="inc-card">
                                    <div class="inc-icon-wrap">&#127793;</div>
                                    <div class="inc-label">Plants</div>
                                    <div class="inc-val tabular-nums">{{ $quotation->package_plants ?? 150 }}</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                @endif
            </tr>
        </table>

        {{-- 5. Additional Notes --}}
        @php
            $additionalNotes = $quotation->getAdditionalNotesList();
        @endphp
        @if(count($additionalNotes) > 0)
            <div class="notes-card">
                <div class="notes-head">Additional Notes &amp; Specifications</div>
                <ul class="notes-list">
                    @foreach($additionalNotes as $note)
                        <li>{{ $note }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 6. Bottom Section: Bank Account Details & Terms & Conditions --}}
        <table class="bottom-tbl">
            <tr>
                {{-- Bank Account Details --}}
                <td class="bottom-col">
                    <div class="card-header-bar" style="margin-bottom: 5px; padding-bottom: 5px;">
                        <span class="card-icon">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align: middle; display: inline-block;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </span>
                        <span class="card-header-title">Bank Account Details</span>
                    </div>

                    <table class="kv-table" style="font-size: 9.5px;">
                        <tr>
                            <td class="kv-label" style="width: 38%;">Account Name</td>
                            <td class="kv-colon">:</td>
                            <td class="kv-val strong" style="width: 58%;">{{ $quotation->bank_account_name ?: config('shop.bank_account_name', config('quotation.bank_account_name', 'Plant Tech Agro')) }}</td>
                        </tr>
                        <tr>
                            <td class="kv-label">Bank Name</td>
                            <td class="kv-colon">:</td>
                            <td class="kv-val">{{ $quotation->bank_name ?: config('shop.bank_name', config('quotation.bank_name', 'J&K Bank')) }}</td>
                        </tr>
                        <tr>
                            <td class="kv-label">Account Number</td>
                            <td class="kv-colon">:</td>
                            <td class="kv-val strong" style="font-family: ui-monospace, SFMono-Regular, monospace; font-size: 11px; color: var(--brand-accent, #064e3b);">
                                {{ $quotation->bank_account_no ?: config('shop.bank_account_no', config('quotation.bank_account_no', '0942 0100 0000 0275')) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="kv-label">Branch</td>
                            <td class="kv-colon">:</td>
                            <td class="kv-val">{{ $quotation->bank_branch ?: config('shop.bank_branch', config('quotation.bank_branch', 'Migrant Colony Hall Pulwama')) }}</td>
                        </tr>
                        <tr>
                            <td class="kv-label">IFSC Code</td>
                            <td class="kv-colon">:</td>
                            <td class="kv-val strong" style="font-family: ui-monospace, SFMono-Regular, monospace; font-size: 10px;">{{ $quotation->bank_ifsc ?: config('shop.bank_ifsc', config('quotation.bank_ifsc', 'JAKA0MIGRNT')) }}</td>
                        </tr>
                    </table>
                </td>

                {{-- Terms & Conditions --}}
                <td class="bottom-col">
                    <div class="card-header-bar" style="margin-bottom: 5px; padding-bottom: 5px;">
                        <span class="card-icon">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align: middle; display: inline-block;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </span>
                        <span class="card-header-title">Terms &amp; Conditions</span>
                    </div>

                    @php
                        $terms = $quotation->getTermsList();
                    @endphp
                    <ol class="terms-ol">
                        @foreach($terms as $term)
                            <li>{{ $term }}</li>
                        @endforeach
                    </ol>
                </td>
            </tr>
        </table>

        {{-- 7. Signatures --}}
        <table class="sig-tbl">
            <tr>
                <td class="sig-col">
                    <div class="sig-head">Client Acceptance</div>
                    <table class="sig-line-tbl">
                        <tr>
                            <td style="height: 28px;"></td>
                        </tr>
                        <tr>
                            <td style="border-top: 1px solid #94a3b8; font-weight: 600; color: #334155;">Client Name, Signature &amp; Stamp</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b;">Date: ________________________</td>
                        </tr>
                    </table>
                </td>

                <td class="sig-col" style="padding-left: 20px;">
                    <div class="sig-head">For Plant Tech Agro</div>
                    <table class="sig-line-tbl">
                        <tr>
                            <td style="height: 28px;"></td>
                        </tr>
                        <tr>
                            <td style="border-top: 1px solid #94a3b8; font-weight: 600; color: #334155;">Authorized Signatory</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b;">Date: ________________________</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- 8. Footer Ribbon: Feature Badges & "Your Orchard. Our Commitment." --}}
        <table class="footer-banner-tbl">
            <tr>
                <td class="footer-left-col">
                    <table class="features-tbl">
                        <tr>
                            <td class="feature-cell">
                                <div class="feat-icon">&#127795;</div>
                                <div class="feat-text">High Density Orchards</div>
                            </td>
                            <td class="feature-cell">
                                <div class="feat-icon">&#128167;</div>
                                <div class="feat-text">Trellis &amp; Irrigation</div>
                            </td>
                            <td class="feature-cell">
                                <div class="feat-icon">&#127807;</div>
                                <div class="feat-text">Quality Planting Material</div>
                            </td>
                            <td class="feature-cell">
                                <div class="feat-icon">&#128104;&#8205;&#127806;</div>
                                <div class="feat-text">Expert Agronomy</div>
                            </td>
                            <td class="feature-cell">
                                <div class="feat-icon">&#128200;</div>
                                <div class="feat-text">Better Harvests</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="footer-right-col">
                    <div class="comm-title">Your Orchard.</div>
                    <div class="comm-sub">Our Commitment.</div>
                </td>
            </tr>
        </table>

    </div>
</body>
</html>
