<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Ledger — {{ $customer->name }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            margin-bottom: 25px;
            border-bottom: 2px solid #059669;
            padding-bottom: 12px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            color: #059669;
            text-transform: uppercase;
        }
        .meta-table {
            width: 100%;
            margin-top: 15px;
            margin-bottom: 20px;
        }
        .meta-table td {
            vertical-align: top;
            font-size: 11px;
        }
        .summary-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px;
            margin-bottom: 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
        }
        .table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .debit {
            color: #b91c1c;
        }
        .credit {
            color: #047857;
        }
        .bold {
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="title">{{ config('app.name', 'Plant Tech Agro') }}</div>
                    <div style="font-size: 10px; color: #666;">Customer Statement of Account / Ledger</div>
                </td>
                <td class="text-right" style="font-size: 10px; color: #666;">
                    Statement Period:<br>
                    <strong>{{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}</strong>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 60%;">
                <strong>Customer Details:</strong><br>
                <strong>{{ $customer->name }}</strong><br>
                Phone: {{ $customer->phone }}<br>
                @if($customer->email) Email: {{ $customer->email }}<br> @endif
                @if($customer->gstin) <strong>GSTIN: {{ $customer->gstin }}</strong><br> @endif
                @if($customer->address) Address: {{ $customer->address }} @endif
            </td>
            <td style="width: 40%;" class="text-right">
                <div class="summary-box">
                    <table style="width: 100%;">
                        <tr>
                            <td>Opening Balance:</td>
                            <td class="text-right bold">₹{{ number_format($openingBalance, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Total Invoiced:</td>
                            <td class="text-right bold debit">₹{{ number_format($totalDebit, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Total Paid:</td>
                            <td class="text-right bold credit">₹{{ number_format($totalCredit, 2) }}</td>
                        </tr>
                        <tr style="border-top: 1px solid #cbd5e1;">
                            <td style="padding-top: 4px;"><strong>Closing Balance:</strong></td>
                            <td class="text-right bold" style="padding-top: 4px; font-size: 12px;">₹{{ number_format($closingBalance, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type / Ref</th>
                <th>Description</th>
                <th class="text-right">Debit (₹)</th>
                <th class="text-right">Credit (₹)</th>
                <th class="text-right">Running Balance (₹)</th>
            </tr>
        </thead>
        <tbody>
            <tr style="background-color: #f8fafc;">
                <td>{{ $from->format('d/m/Y') }}</td>
                <td>OPENING</td>
                <td>Opening Balance brought forward</td>
                <td class="text-right">—</td>
                <td class="text-right">—</td>
                <td class="text-right bold">₹{{ number_format($openingBalance, 2) }}</td>
            </tr>
            @forelse($transactions as $tx)
                <tr>
                    <td>{{ \Illuminate\Support\Carbon::parse($tx['date'])->format('d/m/Y') }}</td>
                    <td><strong>{{ strtoupper($tx['type']) }}</strong> #{{ $tx['reference'] }}</td>
                    <td>{{ $tx['description'] }}</td>
                    <td class="text-right debit">{{ $tx['debit'] > 0 ? number_format($tx['debit'], 2) : '—' }}</td>
                    <td class="text-right credit">{{ $tx['credit'] > 0 ? number_format($tx['credit'], 2) : '—' }}</td>
                    <td class="text-right bold">₹{{ number_format($tx['running_balance'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 15px; color: #999;">No transactions found in this period.</td>
                </tr>
            @endforelse
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="3">Totals / Closing Balance</td>
                <td class="text-right debit">₹{{ number_format($totalDebit, 2) }}</td>
                <td class="text-right credit">₹{{ number_format($totalCredit, 2) }}</td>
                <td class="text-right">₹{{ number_format($closingBalance, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('d M Y, h:i A') }} | This is a computer-generated account statement.
    </div>
</body>
</html>
