@extends('admin.layout')

@section('page-title', 'Add Product')

@section('content')
    <div class="space-y-6 max-w-3xl">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Add Product</h2>
            <x-admin.button href="{{ route('admin.products.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
        </div>

        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Product Details</h3>
                <p class="text-sm text-gray-500 mb-5">Rate and GST are used as defaults on invoices and work order materials.</p>
                <div class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.input name="name" label="Product Name" :value="old('name')" required placeholder="e.g. M9-T337 Apple Plant" />
                        <x-admin.input name="sku" label="SKU" :value="old('sku')" placeholder="Optional code" />
                        <x-admin.select name="unit" label="Unit" :options="$units" :value="old('unit', 'pcs')" required />
                        <x-admin.input name="rate" label="Rate (₹ per unit)" type="number" step="0.01" min="0" :value="old('rate')" required />
                        <x-admin.input name="gst_rate" label="GST Rate (%)" type="number" step="0.01" min="0" max="100" :value="old('gst_rate', 0)" />
                        <x-admin.select name="supplier_id" label="Primary Supplier" :options="$suppliers->pluck('name', 'id')->all()" :value="old('supplier_id')" placeholder="None" helptext="Receives low stock alert emails." />
                    </div>
                    <x-admin.textarea name="description" label="Description" :value="old('description')" rows="2" />
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Stock</h3>
                <p class="text-sm text-gray-500 mb-5">Opening stock is recorded as the first stock movement.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-admin.input name="stock_qty" label="Opening Stock" type="number" step="0.001" min="0" :value="old('stock_qty', 0)" />
                    <x-admin.input name="low_stock_threshold" label="Low Stock Threshold" type="number" step="0.001" min="0" :value="old('low_stock_threshold', 0)" helptext="Alerts trigger when stock falls to or below this. 0 disables." />
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Image</h3>
                <p class="text-sm text-gray-500 mb-5">Optional product photo.</p>
                <input type="file" name="image" accept="image/*"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                @error('image')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
                <div class="mt-5">
                    <x-admin.checkbox name="is_active" label="Active" :checked="old('is_active', true)" />
                </div>
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Create Product</x-admin.button>
            </div>
        </form>
    </div>
@endsection
