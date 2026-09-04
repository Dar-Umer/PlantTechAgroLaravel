@extends('admin.layout')

@section('page-title', 'Invoices')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Invoices</h2>
                <p class="text-sm text-gray-500 mt-1">Invoices from work orders and manual billing.</p>
            </div>
            <x-admin.button href="{{ route('admin.invoices.create') }}" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>New Invoice</x-admin.button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <p class="text-sm font-medium text-gray-500">Total Invoices</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totals['count'] }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <p class="text-sm font-medium text-gray-500">Outstanding</p>
                <p class="text-3xl font-bold text-red-600 mt-2">₹{{ number_format($totals['outstanding'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <p class="text-sm font-medium text-gray-500">Collected</p>
                <p class="text-3xl font-bold text-green-600 mt-2">₹{{ number_format($totals['collected'], 2) }}</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.invoices.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by number or customer..."
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
            </div>
            <div>
                <select name="status" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    <option value="">All Statuses</option>
                    @foreach(\App\Models\Invoice::STATUSES as $key => $label)
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <x-admin.button type="submit" variant="primary">Filter</x-admin.button>
            @if(request('q') || request('status'))
                <a href="{{ route('admin.invoices.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
            @endif
        </form>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 font-semibold text-gray-600">Number</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Customer</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Date</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Grand Total</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Paid</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Status</th>
                            <th class="px-6 py-3 font-semibold text-gray-600 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($invoices as $invoice)
                            @php
                                $color = \App\Models\Invoice::STATUS_COLORS[$invoice->status] ?? 'gray';
                                $statusClass = ['red' => 'bg-red-50 text-red-700', 'yellow' => 'bg-yellow-50 text-yellow-700', 'green' => 'bg-green-50 text-green-700', 'gray' => 'bg-gray-100 text-gray-600'][$color];
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $invoice->number }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $invoice->customer_name }}</td>
                                <td class="px-6 py-4 text-gray-500 text-xs">{{ $invoice->invoice_date->format('d M Y') }}</td>
                                <td class="px-6 py-4 font-semibold text-gray-900">₹{{ number_format((float) $invoice->grand_total, 2) }}</td>
                                <td class="px-6 py-4 text-gray-600">₹{{ number_format((float) $invoice->amount_paid, 2) }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                        {{ \App\Models\Invoice::STATUSES[$invoice->status] ?? $invoice->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <x-admin.button href="{{ route('admin.invoices.show', $invoice) }}" variant="secondary" size="sm">View</x-admin.button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                                        <p class="text-sm">No invoices found.</p>
                                        <a href="{{ route('admin.work-orders.index') }}" class="mt-2 text-sm text-brand-600 hover:text-brand-700 font-medium">Open a work order to generate an invoice</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($invoices->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
