@extends('admin.layout')

@section('page-title', 'Stock Movements')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Stock Movements</h2>
                <p class="text-sm text-gray-500 mt-1">Complete audit trail of every stock change.</p>
            </div>
            <x-admin.button href="{{ route('admin.stock-movements.create') }}" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>Record Movement</x-admin.button>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <span class="text-sm font-medium text-gray-500">Stock In (month)</span>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ \App\Support\Format::qty($inMonthQty) }} <span class="text-sm font-medium text-gray-400">units</span></p>
                <p class="text-xs text-gray-400 mt-1">Purchased & reversed this month</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <span class="text-sm font-medium text-gray-500">Stock Out (month)</span>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ \App\Support\Format::qty($outMonthQty) }} <span class="text-sm font-medium text-gray-400">units</span></p>
                <p class="text-xs text-gray-400 mt-1">Sold & consumed this month</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <span class="text-sm font-medium text-gray-500">Net Change (month)</span>
                <p class="text-2xl font-bold {{ $netMonthQty > 0 ? 'text-green-600' : ($netMonthQty < 0 ? 'text-red-600' : 'text-gray-900') }} mt-1">
                    {{ $netMonthQty > 0 ? '+' : '' }}{{ \App\Support\Format::qty($netMonthQty) }}
                </p>
                <p class="text-xs text-gray-400 mt-1">{{ $adjustmentCount }} {{ Str::plural('adjustment', $adjustmentCount) }} recorded</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <span class="text-sm font-medium text-gray-500">Inventory Value</span>
                <p class="text-2xl font-bold text-gray-900 mt-1">₹{{ number_format($stockValue, 0) }}</p>
                <p class="text-xs text-gray-400 mt-1">Stock × list rate</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col justify-center">
                <span class="text-sm font-medium text-gray-500">Total Movements</span>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($movements->total()) }}</p>
                <p class="text-xs text-gray-400 mt-1">Currently filtered</p>
            </div>
        </div>

        {{-- Filter Bar --}}
        <form method="GET" action="{{ route('admin.stock-movements.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-3">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex-1 min-w-[220px]">
                    <select name="product_id" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        <option value="">All Products</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="type" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        <option value="">All Types</option>
                        @foreach(\App\Models\StockMovement::TYPES as $key => $label)
                            <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="supplier_id" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input type="date" name="from" value="{{ request('from') }}" placeholder="From"
                           class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                </div>
                <div>
                    <input type="date" name="to" value="{{ request('to') }}" placeholder="To"
                           class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                </div>
                <div class="flex items-center gap-2">
                    <x-admin.button type="submit" variant="primary" size="sm">Filter</x-admin.button>
                    <x-admin.button href="{{ route('admin.stock-movements.export', request()->query()) }}" variant="secondary" size="sm" icon='<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>'>Export CSV</x-admin.button>
                </div>
            </div>
            @if(request()->hasAny('product_id', 'type', 'supplier_id', 'from', 'to'))
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.stock-movements.index') }}" class="text-xs text-gray-500 hover:text-gray-700 font-medium">Clear filters</a>
                </div>
            @endif
        </form>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 font-semibold text-gray-600">Date</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Product</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Type</th>
                            <th class="px-6 py-3 font-semibold text-gray-600 text-right">Change</th>
                            <th class="px-6 py-3 font-semibold text-gray-600 text-right">Stock</th>
                            <th class="px-6 py-3 font-semibold text-gray-600 text-right">Value</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Reference</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($movements as $movement)
                            @php
                                $before = $movement->stockBefore();
                                $typeClass = ['in' => 'bg-green-50 text-green-700', 'out' => 'bg-red-50 text-red-700', 'adjustment' => 'bg-blue-50 text-blue-700'][$movement->type] ?? 'bg-gray-100 text-gray-600';
                                $typeIcon = [
                                    'in' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 4v8m0 0l4-4m-4 4l-4-4"/>',
                                    'out' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V8m0 0l4 4m-4-4L3 12m14 4V8m0 0l4 4m-4-4l-4 4"/>',
                                    'adjustment' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                                ][$movement->type] ?? '';
                            @endphp
                            <tr class="hover:bg-gray-50 transition cursor-pointer" onclick="window.location.href='{{ route('admin.stock-movements.show', $movement) }}'">
                                <td class="px-6 py-4 text-gray-500 text-xs whitespace-nowrap">
                                    {{ $movement->created_at->format('d M Y') }}
                                    <span class="block text-gray-400">{{ $movement->created_at->format('h:i A') }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.products.edit', $movement->product) }}" onclick="event.stopPropagation()" class="font-medium text-gray-900 hover:text-brand-600">
                                        {{ $movement->product->name }}
                                    </a>
                                    @if($movement->product->sku)
                                        <span class="block text-xs text-gray-400">{{ $movement->product->sku }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeClass }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $typeIcon !!}</svg>
                                        {{ \App\Models\StockMovement::TYPES[$movement->type] ?? $movement->type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-right whitespace-nowrap {{ $movement->quantity >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $movement->quantity >= 0 ? '+' : '' }}{{ \App\Support\Format::qty($movement->quantity) }} {{ $movement->product->unit }}
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <span class="text-xs text-gray-400">{{ \App\Support\Format::qty($before) }}</span>
                                    <span class="text-gray-300 mx-1">→</span>
                                    <span class="font-semibold text-gray-900">{{ \App\Support\Format::qty($movement->stock_after) }} {{ $movement->product->unit }}</span>
                                </td>
                                <td class="px-6 py-4 text-right text-gray-600 whitespace-nowrap">
                                    @if($movement->withUnitCost())
                                        ₹{{ number_format($movement->movementValue(), 0) }}
                                        <span class="block text-xs text-gray-400">@₹{{ number_format((float) $movement->unit_cost, 0) }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    @if($movement->reference)
                                        <span class="font-mono text-xs font-medium text-gray-700">{{ $movement->reference }}</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                    @if($movement->supplier)
                                        <span class="block text-xs text-gray-400">{{ $movement->supplier->name }}</span>
                                    @endif
                                    @if($movement->note)
                                        <span class="block text-xs text-gray-400 max-w-[220px] truncate" title="{{ $movement->note }}">{{ $movement->note }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        @if($movement->createdBy)
                                            <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 text-xs font-semibold flex items-center justify-center flex-shrink-0">
                                                {{ mb_strtoupper(mb_substr($movement->createdBy->name, 0, 1)) }}
                                            </span>
                                            <span class="text-xs text-gray-500">{{ $movement->createdBy->name }}</span>
                                        @else
                                            <span class="text-xs text-gray-300">—</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        <p class="text-sm">No stock movements found.</p>
                                        @if(request()->hasAny('product_id', 'type', 'supplier_id', 'from', 'to'))
                                            <a href="{{ route('admin.stock-movements.index') }}" class="mt-1 text-xs text-brand-600 hover:text-brand-700 font-medium">Clear filters</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($movements->total() > 0)
                <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between flex-wrap gap-3">
                    <p class="text-xs text-gray-500">
                        Showing {{ $movements->firstItem() ?? 0 }}–{{ $movements->lastItem() ?? 0 }} of {{ $movements->total() }} movements
                    </p>
                    @if($movements->hasPages())
                        {{ $movements->links() }}
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection