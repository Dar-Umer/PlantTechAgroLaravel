@extends('admin.layout')

@section('page-title', 'Purchase Bills (Inward Stock)')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Purchase Bills (Inward Stock)</h2>
            <p class="text-sm text-gray-500 mt-1">Bill-by-bill inventory arrivals, supplier invoices, cost breakdown, and payment status.</p>
        </div>
        <div class="flex items-center gap-3">
            <x-admin.button href="{{ route('admin.suppliers.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>'>
                Suppliers Directory
            </x-admin.button>
            <x-admin.button href="{{ route('admin.purchase-bills.create') }}" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>
                + New Purchase Bill
            </x-admin.button>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-xs hover:shadow-md border border-gray-100 p-5 transition-all duration-200 hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Inward Value</p>
                <span class="p-2 bg-blue-50 text-blue-600 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-gray-900 mt-2">₹{{ number_format($metrics['total_purchases'], 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">Cumulative purchase bills</p>
        </div>

        <div class="bg-gradient-to-br from-emerald-50/50 via-white to-emerald-50/20 rounded-2xl shadow-xs hover:shadow-md border border-emerald-100/80 p-5 transition-all duration-200 hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Total Settled</p>
                <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-emerald-600 mt-2">₹{{ number_format($metrics['total_paid'], 2) }}</p>
            <p class="text-xs text-emerald-600/70 mt-1">Paid to suppliers</p>
        </div>

        <div class="bg-gradient-to-br from-red-50/50 via-white to-red-50/20 rounded-2xl shadow-xs hover:shadow-md border border-red-100/80 p-5 transition-all duration-200 hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-red-700">Total Dues Pending</p>
                <span class="p-2 bg-red-50 text-red-600 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-red-600 mt-2">₹{{ number_format($metrics['total_balance_due'], 2) }}</p>
            <p class="text-xs text-red-500/70 mt-1">Pending payments</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xs hover:shadow-md border border-gray-100 p-5 transition-all duration-200 hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Bills This Month</p>
                <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $metrics['bills_this_month'] }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ now()->format('F Y') }} activity</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="{{ route('admin.purchase-bills.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search bill #, supplier invoice #, or supplier..."
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
        </div>

        <div>
            <select name="supplier_id" class="rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                <option value="all">All Suppliers</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>
                        {{ $s->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <select name="payment_status" class="rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                <option value="all">All Payment Statuses</option>
                <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial</option>
                <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <input type="date" name="from_date" value="{{ request('from_date') }}" title="From Date"
                   class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-xs focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
            <span class="text-xs text-gray-400">to</span>
            <input type="date" name="to_date" value="{{ request('to_date') }}" title="To Date"
                   class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-xs focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
        </div>

        <x-admin.button type="submit" variant="primary">Filter</x-admin.button>

        @if(request('q') || (request('supplier_id') && request('supplier_id') !== 'all') || (request('payment_status') && request('payment_status') !== 'all') || request('from_date') || request('to_date'))
            <a href="{{ route('admin.purchase-bills.index') }}" class="text-sm text-gray-500 hover:text-gray-700 font-medium">Clear</a>
        @endif
    </form>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50/80 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Bill Number</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Supplier</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Supplier Inv #</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Bill Date</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center">Items</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Grand Total</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Paid</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Balance Due</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center">Status</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($bills as $bill)
                        <tr class="hover:bg-gray-50/80 transition duration-150">
                            <td class="px-6 py-4 font-bold text-gray-900">
                                <a href="{{ route('admin.purchase-bills.show', $bill) }}" class="hover:text-brand-600 transition">
                                    {{ $bill->bill_number }}
                                </a>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 font-bold flex items-center justify-center text-xs border border-emerald-100">
                                        {{ strtoupper(substr($bill->supplier?->name ?? '?', 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.suppliers.show', $bill->supplier_id) }}" class="font-semibold text-gray-900 hover:text-brand-600 transition">
                                            {{ $bill->supplier?->name ?? 'Unknown Supplier' }}
                                        </a>
                                        @if($bill->supplier?->phone)
                                            <span class="block text-xs text-gray-400">{{ $bill->supplier->phone }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-xs font-medium text-gray-600">
                                {{ $bill->supplier_invoice_no ?: '—' }}
                            </td>

                            <td class="px-6 py-4 text-gray-700 whitespace-nowrap">
                                {{ $bill->bill_date->format('d M, Y') }}
                                @if($bill->due_date)
                                    <span class="block text-xs text-gray-400">Due: {{ $bill->due_date->format('d M') }}</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                    {{ $bill->items->count() }} items
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right font-bold text-gray-900">
                                ₹{{ number_format($bill->total_amount, 2) }}
                            </td>

                            <td class="px-6 py-4 text-right font-semibold text-emerald-600">
                                ₹{{ number_format($bill->paid_amount, 2) }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                @if($bill->balance_due > 0)
                                    <span class="font-bold text-red-600">₹{{ number_format($bill->balance_due, 2) }}</span>
                                @else
                                    <span class="text-xs font-semibold text-emerald-600">₹0.00</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $bill->paymentStatusBadgeClass() }}">
                                    {{ ucfirst($bill->payment_status) }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.purchase-bills.print', $bill) }}" target="_blank"
                                       class="p-1.5 text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition" title="Print Bill">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.purchase-bills.show', $bill) }}"
                                       class="px-2.5 py-1 text-xs font-medium rounded-lg text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition">
                                        View Bill
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <p class="text-base font-semibold text-gray-700">No purchase bills recorded</p>
                                    <p class="text-xs text-gray-400 mt-1">Create a purchase bill whenever stock arrives from a vendor.</p>
                                    <a href="{{ route('admin.purchase-bills.create') }}" class="mt-3 inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 transition">
                                        + Record New Purchase Bill
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bills->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $bills->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
