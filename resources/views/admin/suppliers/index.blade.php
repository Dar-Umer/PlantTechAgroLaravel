@extends('admin.layout')

@section('page-title', 'Suppliers Directory')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Suppliers Directory</h2>
                <p class="text-sm text-gray-500 mt-1">Manage vendor contacts, procurement ledgers, bill history, and outstanding balances.</p>
            </div>
            <div class="flex items-center gap-3">
                <x-admin.button href="{{ route('admin.purchase-bills.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'>
                    All Purchase Bills
                </x-admin.button>
                <x-admin.button href="{{ route('admin.suppliers.create') }}" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>
                    Add Supplier
                </x-admin.button>
            </div>
        </div>

        <!-- Metrics Overview -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl shadow-xs hover:shadow-md border border-gray-100 p-5 transition-all duration-200 hover:-translate-y-0.5">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Suppliers</p>
                    <span class="p-2 bg-gray-50 text-gray-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </span>
                </div>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($metrics['total_suppliers']) }}</p>
                <p class="text-xs text-gray-400 mt-1">Active vendor network</p>
            </div>

            <div class="bg-white rounded-2xl shadow-xs hover:shadow-md border border-gray-100 p-5 transition-all duration-200 hover:-translate-y-0.5">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Purchase Bills</p>
                    <span class="p-2 bg-blue-50 text-blue-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                </div>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($metrics['total_bills']) }}</p>
                <p class="text-xs text-gray-400 mt-1">Recorded inward shipments</p>
            </div>

            <div class="bg-gradient-to-br from-emerald-50/50 via-white to-emerald-50/20 rounded-2xl shadow-xs hover:shadow-md border border-emerald-100/80 p-5 transition-all duration-200 hover:-translate-y-0.5">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Total Purchased</p>
                    <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <p class="text-3xl font-bold text-emerald-600 mt-2">₹{{ number_format($metrics['total_purchased'], 2) }}</p>
                <p class="text-xs text-emerald-600/70 mt-1">Lifetime inward stock value</p>
            </div>

            <div class="bg-gradient-to-br from-red-50/50 via-white to-red-50/20 rounded-2xl shadow-xs hover:shadow-md border border-red-100/80 p-5 transition-all duration-200 hover:-translate-y-0.5">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-wider text-red-700">Total Payables Due</p>
                    <span class="p-2 bg-red-50 text-red-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <p class="text-3xl font-bold text-red-600 mt-2">₹{{ number_format($metrics['total_payables'], 2) }}</p>
                <p class="text-xs text-red-500/70 mt-1">Pending supplier dues</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <form method="GET" action="{{ route('admin.suppliers.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[240px]">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by supplier name, contact, phone, GSTIN..."
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
            </div>

            <div>
                <select name="status" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    <option value="">All Suppliers</option>
                    <option value="has_due" {{ request('status') === 'has_due' ? 'selected' : '' }}>Pending Payables Only</option>
                </select>
            </div>

            <x-admin.button type="submit" variant="primary">Filter</x-admin.button>

            @if(request('q') || request('status'))
                <a href="{{ route('admin.suppliers.index') }}" class="text-sm text-gray-500 hover:text-gray-700 font-medium">Clear</a>
            @endif
        </form>

        <!-- Suppliers Table -->
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50/80 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Supplier Name</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Contact Person</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">GSTIN / Tax ID</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center">Bills</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Total Purchased</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Balance Due</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center">Status</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($suppliers as $supplier)
                            <tr class="hover:bg-gray-50/80 transition duration-150">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 font-bold flex items-center justify-center text-sm border border-emerald-100">
                                            {{ strtoupper(substr($supplier->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.suppliers.show', $supplier) }}" class="font-semibold text-gray-900 hover:text-brand-600 transition">
                                                {{ $supplier->name }}
                                            </a>
                                            @if($supplier->city || $supplier->state)
                                                <p class="text-xs text-gray-400 mt-0.5">
                                                    {{ implode(', ', array_filter([$supplier->city, $supplier->state])) }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="text-gray-900 font-medium">{{ $supplier->contact_person ?: '—' }}</div>
                                    <div class="text-xs text-gray-500 space-y-0.5 mt-0.5">
                                        @if($supplier->phone)
                                            <div><a href="tel:{{ $supplier->phone }}" class="hover:text-brand-600 font-medium">{{ $supplier->phone }}</a></div>
                                        @endif
                                        @if($supplier->email)
                                            <div><a href="mailto:{{ $supplier->email }}" class="hover:text-brand-600 truncate block max-w-[180px]">{{ $supplier->email }}</a></div>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-xs font-medium text-gray-700">
                                    {{ $supplier->gst_no ?: '—' }}
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('admin.suppliers.show', [$supplier, 'tab' => 'bills']) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition">
                                        {{ $supplier->purchase_bills_count }} bills
                                    </a>
                                </td>

                                <td class="px-6 py-4 text-right font-semibold text-gray-900">
                                    ₹{{ number_format((float) ($supplier->total_purchased ?? 0), 2) }}
                                </td>

                                <td class="px-6 py-4 text-right">
                                    @php $due = (float) ($supplier->balance_due ?? 0); @endphp
                                    @if($due > 0)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                            ₹{{ number_format($due, 2) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Clear (₹0.00)
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-center">
                                    @if($supplier->is_active)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">Active</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Inactive</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.purchase-bills.create', ['supplier_id' => $supplier->id]) }}"
                                           title="New Purchase Bill"
                                           class="p-1.5 rounded-lg text-gray-500 hover:text-emerald-700 hover:bg-emerald-50 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        </a>
                                        <a href="{{ route('admin.suppliers.show', $supplier) }}"
                                           class="px-2.5 py-1 text-xs font-medium rounded-lg text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition">
                                            Ledger & Bills
                                        </a>
                                        <x-admin.button href="{{ route('admin.suppliers.edit', $supplier) }}" variant="secondary" size="sm">Edit</x-admin.button>
                                        <form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="POST" class="inline"
                                              onsubmit="return confirm('⚠️ DANGER: Deleting supplier \'{{ addslashes($supplier->name) }}\' will permanently delete all associated purchase bills, inward stock movements, and payment records, and reverse added product inventory. Are you absolutely sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    title="Delete Supplier"
                                                    class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        <p class="text-base font-semibold text-gray-700">No suppliers found</p>
                                        <p class="text-xs text-gray-400 mt-1">Add your material vendors to track inward stock and purchase invoices.</p>
                                        <a href="{{ route('admin.suppliers.create') }}" class="mt-3 inline-flex items-center px-3.5 py-2 rounded-xl text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 transition">
                                            + Add Supplier
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($suppliers->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $suppliers->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
