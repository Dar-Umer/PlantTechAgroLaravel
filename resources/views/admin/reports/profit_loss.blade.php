@extends('admin.layout')

@section('page-title', 'Profit & Loss Statement')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-900">Profit &amp; Loss Statement</h2>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $netProfit >= 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                    {{ $netProfit >= 0 ? 'Net Profit: ₹' . number_format($netProfit, 2) : 'Net Loss: ₹' . number_format(abs($netProfit), 2) }}
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-1">Comprehensive accounting of operating revenue, cost of goods sold (COGS), gross margins, and net profitability.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.reports.profit-loss.export', ['format' => 'pdf', 'from' => $from->toDateString(), 'to' => $to->toDateString()]) }}" target="_blank"
               class="inline-flex items-center px-3.5 py-2 border border-gray-300 shadow-xs text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Export PDF
            </a>
            <a href="{{ route('admin.reports.profit-loss.export', ['format' => 'excel', 'from' => $from->toDateString(), 'to' => $to->toDateString()]) }}"
               class="inline-flex items-center px-3.5 py-2 border border-gray-300 shadow-xs text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export Excel / CSV
            </a>
        </div>
    </div>

    {{-- Date Filters --}}
    <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-4">
        <form method="GET" action="{{ route('admin.reports.profit-loss') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">From:</label>
                <input type="date" name="from" value="{{ $from->toDateString() }}" class="px-3 py-1.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-gray-50">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">To:</label>
                <input type="date" name="to" value="{{ $to->toDateString() }}" class="px-3 py-1.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-gray-50">
            </div>
            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white font-medium text-sm rounded-xl hover:bg-emerald-700 transition shadow-xs">
                Filter
            </button>
            <a href="{{ route('admin.reports.profit-loss') }}" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 font-medium">Reset</a>
        </form>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Operating Revenue</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">₹{{ number_format($totalRevenue, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">Invoices (Taxable) + POS Counter Sales</p>
        </div>
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Cost of Goods / Direct Purchases</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">₹{{ number_format($totalCogs, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">Stock Purchases &amp; Service Materials</p>
        </div>
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Gross Margin</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($grossMarginPercent, 1) }}%</p>
            <p class="text-xs text-gray-400 mt-1">Gross Profit: ₹{{ number_format($grossProfit, 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Net Profit / Loss</p>
            <p class="text-2xl font-bold mt-1 {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">₹{{ number_format($netProfit, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $netProfit >= 0 ? 'Profitable Operating Period' : 'Operating Deficit' }}</p>
        </div>
    </div>

    {{-- P&L Detailed Statement Table --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Revenue / Inflows Side --}}
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Operating Revenue (Income)</h3>
                    <p class="text-xs text-gray-400">Services invoiced and direct retail POS billing</p>
                </div>
                <span class="p-2 bg-emerald-50 text-emerald-700 rounded-xl text-xs font-bold">Inflows</span>
            </div>
            <div class="divide-y divide-gray-100">
                <div class="py-3 flex justify-between items-center text-sm">
                    <span class="text-gray-700 font-medium">Customer Invoices (Taxable Value)</span>
                    <span class="font-bold text-gray-900">₹{{ number_format($invoiceTaxable, 2) }}</span>
                </div>
                <div class="py-3 flex justify-between items-center text-sm">
                    <span class="text-gray-700 font-medium">Retail Storefront POS Sales (Subtotal)</span>
                    <span class="font-bold text-gray-900">₹{{ number_format($posSubtotal, 2) }}</span>
                </div>
                <div class="py-3 flex justify-between items-center text-sm text-gray-500">
                    <span>Less: Promotional Discounts Given</span>
                    <span class="text-rose-600 font-medium">− ₹{{ number_format($discountsGiven, 2) }}</span>
                </div>
                <div class="pt-4 flex justify-between items-center text-base font-bold border-t-2 border-gray-200">
                    <span class="text-gray-900">Net Operating Turnover</span>
                    <span class="text-emerald-600">₹{{ number_format($totalRevenue, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Expenses / Cost of Goods Sold Side --}}
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Direct Costs &amp; COGS (Outflows)</h3>
                    <p class="text-xs text-gray-400">Procurement of plants, fertilizers, machinery &amp; stage consumables</p>
                </div>
                <span class="p-2 bg-amber-50 text-amber-700 rounded-xl text-xs font-bold">Outflows</span>
            </div>
            <div class="divide-y divide-gray-100">
                <div class="py-3 flex justify-between items-center text-sm">
                    <span class="text-gray-700 font-medium">Supplier Purchase Bills (Net of Tax)</span>
                    <span class="font-bold text-gray-900">₹{{ number_format($purchaseSubtotal, 2) }}</span>
                </div>
                <div class="py-3 flex justify-between items-center text-sm">
                    <span class="text-gray-700 font-medium">Work Order Consumables / Direct Materials</span>
                    <span class="font-bold text-gray-900">₹{{ number_format($workOrderMaterialCost, 2) }}</span>
                </div>
                <div class="py-3 flex justify-between items-center text-sm">
                    <span class="text-gray-700 font-medium">Inward Freight &amp; Shipping Charges</span>
                    <span class="font-bold text-gray-900">₹{{ number_format($shippingCost, 2) }}</span>
                </div>
                <div class="pt-4 flex justify-between items-center text-base font-bold border-t-2 border-gray-200">
                    <span class="text-gray-900">Total Direct Operating Expenses</span>
                    <span class="text-amber-600">₹{{ number_format($totalCogs, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Net Bottomline Card --}}
    <div class="bg-gradient-to-br {{ $netProfit >= 0 ? 'from-emerald-500/10 via-white to-emerald-500/5 border-emerald-200' : 'from-rose-500/10 via-white to-rose-500/5 border-rose-200' }} rounded-2xl border p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider {{ $netProfit >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">Accounting Net Profit / Loss for Selected Period</p>
                <h3 class="text-3xl font-extrabold text-gray-900 mt-1">₹{{ number_format($netProfit, 2) }}</h3>
                <p class="text-xs text-gray-500 mt-1">Calculated as (Total Revenue ₹{{ number_format($totalRevenue, 2) }}) − (Direct COGS &amp; Purchases ₹{{ number_format($totalCogs, 2) }}).</p>
            </div>
            <div class="text-right">
                <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-bold {{ $netProfit >= 0 ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                    {{ $netProfit >= 0 ? 'PROFITABLE' : 'NET LOSS' }} ({{ number_format($grossMarginPercent, 1) }}% Margin)
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
