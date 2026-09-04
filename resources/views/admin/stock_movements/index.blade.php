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

        <form method="GET" action="{{ route('admin.stock-movements.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-wrap items-center gap-3">
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
            <x-admin.button type="submit" variant="primary">Filter</x-admin.button>
            @if(request('product_id') || request('type'))
                <a href="{{ route('admin.stock-movements.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
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
                            <th class="px-6 py-3 font-semibold text-gray-600">Change</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Stock After</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Reference</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($movements as $movement)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-gray-500 text-xs whitespace-nowrap">{{ $movement->created_at->format('d M Y, h:i A') }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $movement->product->name }}</td>
                                <td class="px-6 py-4">
                                    @php $typeClass = ['in' => 'bg-green-50 text-green-700', 'out' => 'bg-red-50 text-red-700', 'adjustment' => 'bg-blue-50 text-blue-700'][$movement->type] ?? 'bg-gray-100 text-gray-600'; @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeClass }}">
                                        {{ \App\Models\StockMovement::TYPES[$movement->type] ?? $movement->type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold {{ $movement->quantity >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $movement->quantity >= 0 ? '+' : '' }}{{ $movement->quantity }} {{ $movement->product->unit }}
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $movement->stock_after }} {{ $movement->product->unit }}</td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $movement->reference ?: '—' }}
                                    @if($movement->supplier)
                                        <span class="block text-xs text-gray-400">{{ $movement->supplier->name }}</span>
                                    @endif
                                    @if($movement->note)
                                        <span class="block text-xs text-gray-400">{{ Str::limit($movement->note, 40) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-500 text-xs">{{ $movement->createdBy?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        <p class="text-sm">No stock movements recorded yet.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($movements->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $movements->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
