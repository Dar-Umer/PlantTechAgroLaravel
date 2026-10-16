@extends('admin.layout')

@section('page-title', 'Financial & Operational Reports')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Financial &amp; Management Reports</h2>
            <p class="text-sm text-gray-500 mt-1">Audit-ready statutory compliance, sales intelligence, party statements, batch stock valuation, and profit analysis.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                14 Reports Active
            </span>
        </div>
    </div>

    {{-- Report Categories Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        {{-- 1. Sales & Invoicing Intelligence --}}
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6 flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <h3 class="text-base font-bold text-gray-900">Sales &amp; Revenue Reports</h3>
                <p class="text-xs text-gray-400 mt-1">Unified sales from customer invoices &amp; retail POS billing.</p>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li>
                        <a href="{{ route('admin.reports.sales') }}" class="group flex items-center justify-between text-gray-700 hover:text-emerald-600 font-medium transition">
                            <span>&bull; Sale Report (Consolidated)</span>
                            <span class="text-xs text-gray-400 group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reports.bill-wise-profit') }}" class="group flex items-center justify-between text-gray-700 hover:text-emerald-600 font-medium transition">
                            <span>&bull; Bill-Wise Profit</span>
                            <span class="text-xs text-gray-400 group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reports.day-book') }}" class="group flex items-center justify-between text-gray-700 hover:text-emerald-600 font-medium transition">
                            <span>&bull; Day Book (Daily Sales &amp; Collections)</span>
                            <span class="text-xs text-gray-400 group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- 2. Procurement & Inward Stock --}}
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6 flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <h3 class="text-base font-bold text-gray-900">Procurement &amp; Purchases</h3>
                <p class="text-xs text-gray-400 mt-1">Supplier inward bills, goods received, shipping &amp; item costs.</p>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li>
                        <a href="{{ route('admin.reports.purchases') }}" class="group flex items-center justify-between text-gray-700 hover:text-blue-600 font-medium transition">
                            <span>&bull; Purchase Report</span>
                            <span class="text-xs text-gray-400 group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reports.party-statement') }}" class="group flex items-center justify-between text-gray-700 hover:text-blue-600 font-medium transition">
                            <span>&bull; Party Statement (Ledgers)</span>
                            <span class="text-xs text-gray-400 group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- 3. Accounting & Financial Bottomline --}}
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6 flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-base font-bold text-gray-900">Accounting &amp; Profitability</h3>
                <p class="text-xs text-gray-400 mt-1">General ledger transactions, gross margins &amp; net operating profit.</p>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li>
                        <a href="{{ route('admin.reports.profit-loss') }}" class="group flex items-center justify-between text-gray-700 hover:text-indigo-600 font-medium transition">
                            <span>&bull; Profit &amp; Loss Statement</span>
                            <span class="text-xs text-gray-400 group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reports.transactions') }}" class="group flex items-center justify-between text-gray-700 hover:text-indigo-600 font-medium transition">
                            <span>&bull; All Transactions (Chronological)</span>
                            <span class="text-xs text-gray-400 group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- 4. Inventory, Batches & Warehouse --}}
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6 flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <h3 class="text-base font-bold text-gray-900">Inventory &amp; Stock Reports</h3>
                <p class="text-xs text-gray-400 mt-1">Warehouse balances, stock valuations, FIFO lot and batch tracking.</p>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li>
                        <a href="{{ route('admin.reports.stock-statement') }}" class="group flex items-center justify-between text-gray-700 hover:text-teal-600 font-medium transition">
                            <span>&bull; Stock Statement &amp; Valuation</span>
                            <span class="text-xs text-gray-400 group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reports.item-batches') }}" class="group flex items-center justify-between text-gray-700 hover:text-teal-600 font-medium transition">
                            <span>&bull; Item Batch &amp; Expiry Report</span>
                            <span class="text-xs text-gray-400 group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- 5. GST Compliance & Rates --}}
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6 flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/></svg>
                </div>
                <h3 class="text-base font-bold text-gray-900">GST &amp; Tax Compliance</h3>
                <p class="text-xs text-gray-400 mt-1">GSTR-1 data splits, tax rate slabs &amp; HSN classification.</p>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li>
                        <a href="{{ route('admin.reports.gst') }}" class="group flex items-center justify-between text-gray-700 hover:text-amber-600 font-medium transition">
                            <span>&bull; GST Report (B2B, B2C, HSN)</span>
                            <span class="text-xs text-gray-400 group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reports.gst-rate') }}" class="group flex items-center justify-between text-gray-700 hover:text-amber-600 font-medium transition">
                            <span>&bull; GST Rate Wise Report (0%, 5%, 12%, 18%)</span>
                            <span class="text-xs text-gray-400 group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- 6. Income Tax Compliance: TDS & TCS --}}
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6 flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-base font-bold text-gray-900">Direct Tax Compliance (TDS / TCS)</h3>
                <p class="text-xs text-gray-400 mt-1">Section 206C(1H), Form 27EQ TCS schedules &amp; contractor TDS.</p>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li>
                        <a href="{{ route('admin.reports.form-27eq') }}" class="group flex items-center justify-between text-gray-700 hover:text-rose-600 font-medium transition">
                            <span>&bull; Form No. 27EQ (TCS Statement)</span>
                            <span class="text-xs text-gray-400 group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reports.tcs-receivable') }}" class="group flex items-center justify-between text-gray-700 hover:text-rose-600 font-medium transition">
                            <span>&bull; TCS Receivable (206C)</span>
                            <span class="text-xs text-gray-400 group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reports.tds-payable') }}" class="group flex items-center justify-between text-gray-700 hover:text-rose-600 font-medium transition">
                            <span>&bull; TDS Payable (194C / 194J)</span>
                            <span class="text-xs text-gray-400 group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</div>
@endsection
