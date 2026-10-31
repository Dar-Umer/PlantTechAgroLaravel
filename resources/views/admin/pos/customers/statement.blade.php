<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Statement - {{ $customer->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ $theme['fontGoogle'] ?? 'Inter:wght@400;500;600;700;800' }}&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['{{ $theme['font'] ?? 'Inter' }}', 'system-ui', '-apple-system', 'sans-serif'],
                        mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', 'monospace']
                    },
                    colors: {
                        brand: {
                            50:  '{{ $theme['palette'][50] ?? '#f0fdf4' }}',
                            100: '{{ $theme['palette'][100] ?? '#dcfce7' }}',
                            200: '{{ $theme['palette'][200] ?? '#bbf7d0' }}',
                            300: '{{ $theme['palette'][300] ?? '#86efac' }}',
                            400: '{{ $theme['palette'][400] ?? '#4ade80' }}',
                            500: '{{ $theme['palette'][500] ?? '#22c55e' }}',
                            600: '{{ $theme['palette'][600] ?? '#16a34a' }}',
                            700: '{{ $theme['palette'][700] ?? '#15803d' }}',
                            800: '{{ $theme['palette'][800] ?? '#166534' }}',
                            900: '{{ $theme['palette'][900] ?? '#14532d' }}',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .statement-page { box-shadow: none !important; border: none !important; margin: 0 !important; width: 100% !important; max-width: 100% !important; }
        }
        @page {
            size: A4 portrait;
            margin: 12mm 10mm;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-900 font-sans antialiased text-xs min-h-screen p-4 sm:p-8">

    {{-- Top Action Toolbar (Hidden when printing) --}}
    <div class="max-w-4xl mx-auto mb-6 no-print flex items-center justify-between gap-4 bg-white p-4 rounded-2xl shadow-sm border border-gray-200">
        <div class="flex items-center gap-2">
            <a href="{{ url()->previous() }}" class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-xs transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back</span>
            </a>
            <span class="text-gray-400">|</span>
            <span class="font-bold text-gray-800">Customer Account Statement</span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-sm transition inline-flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print / Save PDF</span>
            </button>
        </div>
    </div>

    {{-- Printable A4 Sheet --}}
    <div class="statement-page max-w-4xl mx-auto bg-white p-8 sm:p-10 rounded-2xl shadow-md border border-gray-200">
        {{-- Statement Header --}}
        <div class="flex items-start justify-between border-b border-gray-300 pb-6 gap-6">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">{{ $settings['shop_name'] ?? 'PLANT TECH AGRO' }}</h1>
                <p class="text-xs text-gray-600 mt-1 max-w-md leading-relaxed">
                    {{ $settings['shop_address'] ?? 'Agricultural Inputs, Fertilizers, Seeds & Equipment' }}
                </p>
                <div class="mt-2 text-xs text-gray-600 space-y-0.5">
                    @if(!empty($settings['shop_phone']))
                        <p><span class="font-semibold text-gray-800">Phone:</span> {{ $settings['shop_phone'] }}</p>
                    @endif
                    @if(!empty($settings['shop_email']))
                        <p><span class="font-semibold text-gray-800">Email:</span> {{ $settings['shop_email'] }}</p>
                    @endif
                    @if(!empty($settings['shop_gstin']))
                        <p><span class="font-semibold text-gray-800">GSTIN:</span> {{ $settings['shop_gstin'] }}</p>
                    @endif
                </div>
            </div>

            <div class="text-right shrink-0">
                <div class="inline-block bg-gray-900 text-white font-black text-sm uppercase tracking-wider px-3.5 py-1.5 rounded-lg">
                    Account Statement
                </div>
                <div class="mt-3 text-xs text-gray-600 space-y-1">
                    <p><span class="font-bold text-gray-800">Statement Date:</span> {{ now()->format('d M Y, h:i A') }}</p>
                    <p>
                        <span class="font-bold text-gray-800">Period:</span>
                        @if($from && $to)
                            {{ \Illuminate\Support\Carbon::parse($from)->format('d M Y') }} - {{ \Illuminate\Support\Carbon::parse($to)->format('d M Y') }}
                        @elseif($from)
                            From {{ \Illuminate\Support\Carbon::parse($from)->format('d M Y') }}
                        @elseif($to)
                            Until {{ \Illuminate\Support\Carbon::parse($to)->format('d M Y') }}
                        @else
                            Complete Account History
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Customer Details & Financial Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 my-6 pt-2">
            {{-- Customer Info --}}
            <div class="bg-gray-50/80 p-4 rounded-xl border border-gray-200">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Statement For:</span>
                <h3 class="text-base font-black text-gray-900 mt-1">{{ $customer->name }}</h3>
                <div class="mt-2 space-y-1 text-xs text-gray-600">
                    @if($customer->phone)
                        <p><span class="font-semibold text-gray-700">Phone:</span> {{ $customer->phone }}</p>
                    @endif
                    @if($customer->email)
                        <p><span class="font-semibold text-gray-700">Email:</span> {{ $customer->email }}</p>
                    @endif
                    @if($customer->gstin)
                        <p class="font-mono"><span class="font-semibold text-gray-700">GSTIN:</span> {{ $customer->gstin }}</p>
                    @endif
                    @if($customer->address)
                        <p><span class="font-semibold text-gray-700">Address:</span> {{ $customer->address }}</p>
                    @endif
                </div>
            </div>

            {{-- Financial Balance Box --}}
            <div class="bg-gray-50/80 p-4 rounded-xl border border-gray-200 flex flex-col justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Account Summary</span>
                <div class="grid grid-cols-2 gap-2 mt-2 text-xs">
                    <div>
                        <span class="text-gray-500">Opening Balance:</span>
                        <div class="font-mono font-bold text-gray-800">₹{{ number_format($openingBalance, 2) }}</div>
                    </div>
                    <div>
                        <span class="text-gray-500">Total Invoiced (Debits):</span>
                        <div class="font-mono font-bold text-red-600">+₹{{ number_format($totalDebit, 2) }}</div>
                    </div>
                    <div>
                        <span class="text-gray-500">Total Paid (Credits):</span>
                        <div class="font-mono font-bold text-emerald-600">-₹{{ number_format($totalCredit, 2) }}</div>
                    </div>
                    <div class="border-t border-gray-300 pt-1">
                        <span class="font-extrabold text-gray-900">Closing Balance Due:</span>
                        <div class="font-mono font-black text-base {{ $closingBalance > 0 ? 'text-red-700' : 'text-gray-900' }}">
                            ₹{{ number_format($closingBalance, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detailed Ledger Table --}}
        <div class="mt-4 border border-gray-300 rounded-xl overflow-hidden">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-gray-100 border-b border-gray-300 text-[10px] font-bold uppercase tracking-wider text-gray-700">
                        <th class="py-2.5 px-3">Date</th>
                        <th class="py-2.5 px-3">Type / Ref</th>
                        <th class="py-2.5 px-3">Particulars / Details</th>
                        <th class="py-2.5 px-3 text-right">Debit (+Due)</th>
                        <th class="py-2.5 px-3 text-right">Credit (-Paid)</th>
                        <th class="py-2.5 px-3 text-right">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    {{-- Opening Balance Row --}}
                    <tr class="bg-gray-50/50 font-semibold text-gray-700">
                        <td class="py-2 px-3 text-gray-500">
                            {{ $from ? \Illuminate\Support\Carbon::parse($from)->format('d M Y') : 'Start' }}
                        </td>
                        <td class="py-2 px-3 font-mono text-gray-500">OPENING</td>
                        <td class="py-2 px-3 text-gray-600">Opening Balance Brought Forward</td>
                        <td class="py-2 px-3 text-right text-gray-400 font-mono">—</td>
                        <td class="py-2 px-3 text-right text-gray-400 font-mono">—</td>
                        <td class="py-2 px-3 text-right font-mono font-bold text-gray-900">
                            ₹{{ number_format($openingBalance, 2) }}
                        </td>
                    </tr>

                    @forelse($ledgerEntries as $entry)
                        <tr>
                            <td class="py-2 px-3 text-gray-700 whitespace-nowrap">
                                {{ $entry['date'] ? $entry['date']->format('d M Y') : '—' }}
                            </td>
                            <td class="py-2 px-3 font-mono whitespace-nowrap">
                                @if($entry['type'] === 'invoice')
                                    <span class="font-bold text-blue-900">{{ $entry['reference'] }}</span>
                                @else
                                    <span class="font-bold text-emerald-900">PAYMENT</span>
                                @endif
                            </td>
                            <td class="py-2 px-3 text-gray-800">
                                {{ $entry['description'] }}
                                @if($entry['reference'] && $entry['type'] !== 'invoice')
                                    <span class="text-[10px] text-gray-500 font-mono">({{ $entry['reference'] }})</span>
                                @endif
                            </td>
                            <td class="py-2 px-3 text-right font-mono font-semibold whitespace-nowrap">
                                @if($entry['debit'] > 0)
                                    <span class="text-red-700">₹{{ number_format($entry['debit'], 2) }}</span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="py-2 px-3 text-right font-mono font-semibold whitespace-nowrap">
                                @if($entry['credit'] > 0)
                                    <span class="text-emerald-700">₹{{ number_format($entry['credit'], 2) }}</span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="py-2 px-3 text-right font-mono font-bold whitespace-nowrap {{ $entry['running_balance'] > 0 ? 'text-amber-900' : 'text-gray-900' }}">
                                ₹{{ number_format($entry['running_balance'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-gray-400">
                                No statement transactions found for this period.
                            </td>
                        </tr>
                    @endforelse

                    {{-- Totals Summary Row --}}
                    <tr class="bg-gray-100 font-extrabold border-t-2 border-gray-300">
                        <td colspan="3" class="py-2.5 px-3 text-right uppercase tracking-wider text-gray-700">
                            Total / Net Closing Balance:
                        </td>
                        <td class="py-2.5 px-3 text-right font-mono text-red-700">
                            ₹{{ number_format($totalDebit, 2) }}
                        </td>
                        <td class="py-2.5 px-3 text-right font-mono text-emerald-700">
                            ₹{{ number_format($totalCredit, 2) }}
                        </td>
                        <td class="py-2.5 px-3 text-right font-mono font-black text-sm {{ $closingBalance > 0 ? 'text-red-700' : 'text-emerald-700' }}">
                            ₹{{ number_format($closingBalance, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Signatures & Statement Footer --}}
        <div class="mt-12 pt-6 border-t border-gray-200 flex items-end justify-between">
            <div class="text-[11px] text-gray-500 max-w-sm">
                <p class="font-bold text-gray-700">Note:</p>
                <p>This is a computer-generated account statement reflecting all invoiced debits and settled payments as of {{ now()->format('d M Y') }}.</p>
            </div>

            <div class="text-center">
                <div class="w-48 border-b border-gray-400 mb-1"></div>
                <span class="text-[11px] text-gray-600 font-semibold uppercase">Authorized Signatory</span>
            </div>
        </div>
    </div>

</body>
</html>
