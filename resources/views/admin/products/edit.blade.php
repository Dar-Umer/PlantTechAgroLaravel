@extends('admin.layout')

@section('page-title', 'Edit Product')

@section('content')
    <div class="space-y-6 max-w-3xl">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Edit Product</h2>
            <x-admin.button href="{{ route('admin.products.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
        </div>

        <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Product Details</h3>
                <p class="text-sm text-gray-500 mb-5">Rate and GST are used as defaults on invoices and work order materials.</p>
                <div class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.input name="name" label="Product Name" :value="old('name', $product->name)" required />
                        <x-admin.input name="sku" label="SKU" :value="old('sku', $product->sku)" />
                        <x-admin.select name="unit" label="Unit" :options="$units" :value="old('unit', $product->unit)" required />
                        <x-admin.input name="rate" label="Rate (₹ per unit)" type="number" step="0.01" min="0" :value="old('rate', $product->rate)" required />
                        <x-admin.input name="gst_rate" label="GST Rate (%)" type="number" step="0.01" min="0" max="100" :value="old('gst_rate', $product->gst_rate)" />
                        <x-admin.select name="supplier_id" label="Primary Supplier" :options="$suppliers->pluck('name', 'id')->all()" :value="(string) old('supplier_id', $product->supplier_id)" placeholder="None" />
                    </div>
                    <x-admin.textarea name="description" label="Description" :value="old('description', $product->description)" rows="2" />
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Stock</h3>
                <p class="text-sm text-gray-500 mb-5">Stock level is managed via stock movements. Current: <span class="font-semibold">{{ $product->stock_qty }} {{ $product->unit }}</span></p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-admin.input name="low_stock_threshold" label="Low Stock Threshold" type="number" step="0.001" min="0" :value="old('low_stock_threshold', $product->low_stock_threshold)" helptext="Alerts trigger when stock falls to or below this. 0 disables." />
                    <div class="flex items-end">
                        <x-admin.button href="{{ route('admin.stock-movements.create', ['product_id' => $product->id]) }}" variant="secondary">Adjust Stock</x-admin.button>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Image</h3>
                <p class="text-sm text-gray-500 mb-5">Optional product photo.</p>
                @if($product->image)
                    <div class="w-24 h-24 rounded-xl border border-gray-200 bg-gray-50 overflow-hidden mb-3">
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                    </div>
                @endif
                <input type="file" name="image" accept="image/*"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                @error('image')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
                <div class="mt-5">
                    <x-admin.checkbox name="is_active" label="Active" :checked="old('is_active', $product->is_active)" />
                </div>
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Update Product</x-admin.button>
            </div>
        </form>
    </div>
@endsection
