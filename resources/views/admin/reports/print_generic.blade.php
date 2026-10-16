<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Report' }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
            line-height: 1.35;
            margin: 0;
            padding: 15px;
        }
        .header {
            margin-bottom: 15px;
            border-bottom: 2px solid #059669;
            padding-bottom: 8px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            color: #059669;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .subtitle {
            font-size: 10px;
            color: #6b7280;
            margin-top: 3px;
        }
        .meta-bar {
            width: 100%;
            margin-top: 8px;
            font-size: 9px;
            color: #4b5563;
        }
        .summary-cards {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .summary-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 8px 10px;
            text-align: center;
        }
        .summary-card .lbl {
            font-size: 8px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
        }
        .summary-card .val {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 3px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            color: #334155;
        }
        .table td {
            border: 1px solid #e2e8f0;
            padding: 5px 6px;
            font-size: 9px;
        }
        .table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-emerald { color: #047857; }
        .text-red { color: #b91c1c; }
        .text-blue { color: #1d4ed8; }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-yellow { background: #fef9c3; color: #854d0e; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .footer {
            margin-top: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            font-size: 8px;
            color: #94a3b8;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ $title ?? 'Report' }}</div>
        @if(!empty($subtitle))
            <div class="subtitle">{{ $subtitle }}</div>
        @endif
        <table class="meta-bar">
            <tr>
                <td style="width: 50%;">
                    @if(isset($from) && isset($to))
                        Period: <strong>{{ \Illuminate\Support\Carbon::parse($from)->format('d M Y') }}</strong> to <strong>{{ \Illuminate\Support\Carbon::parse($to)->format('d M Y') }}</strong>
                    @endif
                </td>
                <td style="width: 50%; text-align: right;">
                    Generated on: <strong>{{ now()->format('d M Y, h:i A') }}</strong>
                </td>
            </tr>
        </table>
    </div>

    @if(!empty($summaryCards))
        <table class="summary-cards">
            <tr>
                @foreach($summaryCards as $card)
                    <td class="summary-card" style="width: {{ 100 / count($summaryCards) }}%;">
                        <div class="lbl">{{ $card['label'] }}</div>
                        <div class="val" style="{{ $card['style'] ?? '' }}">{{ $card['value'] }}</div>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    <table class="table">
        <thead>
            <tr>
                @foreach($headers as $h)
                    <th class="{{ $h['class'] ?? '' }}" style="{{ $h['style'] ?? '' }}">{{ $h['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $cell)
                        @if(is_array($cell))
                            <td class="{{ $cell['class'] ?? '' }}" style="{{ $cell['style'] ?? '' }}">{{ $cell['value'] ?? '' }}</td>
                        @else
                            <td>{{ $cell }}</td>
                        @endif
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}" class="text-center" style="padding: 15px; color: #94a3b8;">No records found for the selected period.</td>
                </tr>
            @endforelse
        </tbody>
        @if(!empty($totalsRow))
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    @foreach($totalsRow as $tCell)
                        @if(is_array($tCell))
                            <td class="{{ $tCell['class'] ?? '' }}" style="{{ $tCell['style'] ?? '' }} font-weight: bold;">{{ $tCell['value'] ?? '' }}</td>
                        @else
                            <td style="font-weight: bold;">{{ $tCell }}</td>
                        @endif
                    @endforeach
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        Plant Tech Agro CRM &bull; Confidential Business Document
    </div>
</body>
</html>
