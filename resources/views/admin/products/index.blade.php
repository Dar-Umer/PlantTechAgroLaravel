@extends('admin.layout')

@section('page-title', 'Products')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Products</h2>
                <p class="text-sm text-gray-500 mt-1">Materials and items used across services, invoiced to customers and tracked in stock.</p>
            </div>
            <x-admin.button href="{{ route('admin.products.create') }}" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>
                Add Product
            </x-admin.button>
        </div>

        @if($lowStockCount > 0)
            <a href="{{ route('admin.products.index', ['stock' => 'low']) }}" class="flex items-center gap-3 bg-amber-50 border border-amber-200 rounded-2xl p-4 text-sm text-amber-800 hover:bg-amber-100 transition">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span><span class="font-semibold">{{ $lowStockCount }} product(s)</span> are at or below their low stock threshold.</span>
            </a>
        @endif

        <form method="GET" action="{{ route('admin.products.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-wrap items-center gap-3">
            @if(request('stock'))
                <input type="hidden" name="stock" value="{{ request('stock') }}">
            @endif
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by name or SKU..."
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
            </div>
            <div>
                <select name="supplier_id" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <x-admin.button type="submit" variant="primary">Filter</x-admin.button>
            @if(request('q') || request('supplier_id') || request('stock'))
                <a href="{{ route('admin.products.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
            @endif
        </form>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 font-semibold text-gray-600">Product</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Supplier</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Rate</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">GST</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Stock</th>
                            <th class="px-6 py-3 font-semibold text-gray-600 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($products as $product)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $product->sku ?: '—' }} · per {{ $product->unit }}</div>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $product->supplier?->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-gray-600">₹{{ number_format((float) $product->rate, 0) }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $product->gst_rate }}%</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->isLowStock() ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700' }}">
                                        {{ $product->stock_qty }} {{ $product->unit }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <x-admin.button href="{{ route('admin.stock-movements.create', ['product_id' => $product->id]) }}" variant="secondary" size="sm">Stock</x-admin.button>
                                        @if($product->isLowStock() && $product->supplier?->email)
                                            <form action="{{ route('admin.products.notify-supplier', $product) }}" method="POST" onsubmit="return confirm('Send low stock alert email to {{ $product->supplier->email }}?')">
                                                @csrf
                                                <x-admin.button type="submit" variant="secondary" size="sm">Notify Supplier</x-admin.button>
                                            </form>
                                        @endif
                                        <x-admin.button href="{{ route('admin.products.edit', $product) }}" variant="secondary" size="sm">Edit</x-admin.button>
                                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete this product?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-admin.button type="submit" variant="danger" size="sm">Delete</x-admin.button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        <p class="text-sm">No products found.</p>
                                        <a href="{{ route('admin.products.create') }}" class="mt-2 text-sm text-brand-600 hover:text-brand-700 font-medium">Add your first product</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($products->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
