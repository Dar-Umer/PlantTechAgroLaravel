@extends('admin.layout')

@section('page-title', 'Edit Supplier')

@section('content')
    <div class="space-y-6 max-w-3xl">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Edit Supplier</h2>
            <x-admin.button href="{{ route('admin.suppliers.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
        </div>

        <form action="{{ route('admin.suppliers.update', $supplier) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            @include('admin.suppliers._form', ['submitLabel' => 'Update Supplier'])
        </form>

        @if($products->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Supplied Products</h3>
                <p class="text-sm text-gray-500 mb-5">{{ $products->count() }} product(s) assigned to this supplier.</p>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-2.5 font-semibold text-gray-600">Product</th>
                                <th class="px-4 py-2.5 font-semibold text-gray-600">Stock</th>
                                <th class="px-4 py-2.5 font-semibold text-gray-600">Threshold</th>
                                <th class="px-4 py-2.5 font-semibold text-gray-600"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($products as $product)
                                <tr>
                                    <td class="px-4 py-2.5 font-medium text-gray-900">
                                        <div class="flex items-center gap-1.5">
                                            <span>{{ $product->name }}</span>
                                            @if($product->supplier_id === $supplier->id)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700">Primary</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                            {{ \App\Support\Format::qty($product->supplier_stock ?? $product->stock_qty) }} {{ $product->unit }}
                                        </span>
                                        @if(round((float) ($product->supplier_stock ?? $product->stock_qty), 3) !== round((float) $product->stock_qty, 3))
                                            <span class="text-[11px] text-gray-400 ml-1">({{ \App\Support\Format::qty($product->stock_qty) }} total)</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-600">{{ \App\Support\Format::qty($product->low_stock_threshold) }} {{ $product->unit }}</td>
                                    <td class="px-4 py-2.5 text-right">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="text-xs text-brand-600 hover:text-brand-700 font-medium">Manage</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
