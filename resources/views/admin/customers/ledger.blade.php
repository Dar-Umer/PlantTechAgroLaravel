@extends('admin.layout')

@section('page-title', 'Customer Ledger — ' . $customer->name)

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-900">{{ $customer->name }}</h2>
                @if($customer->gstin)
                    <span class="text-xs font-mono bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded border border-emerald-200">GSTIN: {{ $customer->gstin }}</span>
                @endif
            </div>
            <p class="text-sm text-gray-500 mt-1">Account statement, debits (invoices), credits (payments), and running balance.</p>
        </div>
        <div class="flex items-center gap-2">
            <x-admin.button href="{{ route('admin.customers.show', $customer) }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
            <a href="{{ route('admin.customers.ledger.csv', [$customer, 'from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}"
               class="inline-flex items-center px-3.5 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50">
                <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export CSV
            </a>
            <a href="{{ route('admin.customers.ledger.pdf', [$customer, 'from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}"
               class="inline-flex items-center px-3.5 py-2 border border-transparent shadow-sm text-sm font-medium rounded-xl text-white bg-emerald-600 hover:bg-emerald-700">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download PDF
            </a>
        </div>
    </div>

    {{-- Filter Date Range --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <form method="GET" action="{{ route('admin.customers.ledger', $customer) }}" class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-600">From:</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-600">To:</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <button type="submit" class="px-4 py-1.5 bg-emerald-600 text-white font-medium text-sm rounded-lg hover:bg-emerald-700 transition">
                Filter
            </button>
            <a href="{{ route('admin.customers.ledger', $customer) }}" class="text-sm text-gray-500 hover:text-gray-700">Reset</a>
        </form>
    </div>

    {{-- Financial Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Opening Balance</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">₹{{ number_format($openingBalance, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">as of {{ $from->format('d M Y') }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Billed (Debits)</p>
            <p class="text-2xl font-bold text-rose-600 mt-1">₹{{ number_format($totalDebit, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">in selected period</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Paid (Credits)</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">₹{{ number_format($totalCredit, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">in selected period</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Closing Outstanding</p>
            <p class="text-2xl font-bold {{ $closingBalance > 0 ? 'text-amber-600' : 'text-gray-900' }} mt-1">
                ₹{{ number_format($closingBalance, 2) }}
            </p>
            <p class="text-xs text-gray-400 mt-1">as of {{ $to->format('d M Y') }}</p>
        </div>
    </div>

    {{-- Statement Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type / Ref</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider text-rose-600">Debit (Billed)</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider text-emerald-600">Credit (Paid)</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Running Balance</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    {{-- Opening row --}}
                    <tr class="bg-gray-50/50 italic text-gray-600">
                        <td class="px-6 py-3 text-sm whitespace-nowrap">{{ $from->format('d M Y') }}</td>
                        <td class="px-6 py-3 text-sm whitespace-nowrap font-medium text-gray-500">OPENING</td>
                        <td class="px-6 py-3 text-sm">Opening Balance brought forward</td>
                        <td class="px-6 py-3 text-sm text-right">—</td>
                        <td class="px-6 py-3 text-sm text-right">—</td>
                        <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">₹{{ number_format($openingBalance, 2) }}</td>
                    </tr>

                    @forelse($transactions as $tx)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ \Illuminate\Support\Carbon::parse($tx['date'])->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($tx['type'] === 'invoice')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">INV</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-700">PAY</span>
                                @endif
                                <span class="ml-1 text-sm font-medium text-gray-900">{{ $tx['reference'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $tx['description'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-rose-600">
                                {{ $tx['debit'] > 0 ? '₹' . number_format($tx['debit'], 2) : '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-emerald-600">
                                {{ $tx['credit'] > 0 ? '₹' . number_format($tx['credit'], 2) : '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                                ₹{{ number_format($tx['running_balance'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400">No transactions recorded in this period.</td>
                        </tr>
                    @endforelse

                    {{-- Closing row --}}
                    <tr class="bg-gray-50 font-bold border-t-2 border-gray-200">
                        <td class="px-6 py-4 text-sm" colspan="3">Totals / Closing Position</td>
                        <td class="px-6 py-4 text-sm text-right text-rose-600">₹{{ number_format($totalDebit, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right text-emerald-600">₹{{ number_format($totalCredit, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right {{ $closingBalance > 0 ? 'text-amber-600' : 'text-gray-900' }}">₹{{ number_format($closingBalance, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
