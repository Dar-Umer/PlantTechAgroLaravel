@extends('admin.layout')

@section('page-title', 'Record Stock Movement')

@section('content')
    <div class="space-y-6 max-w-3xl">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Record Stock Movement</h2>
            <x-admin.button href="{{ route('admin.stock-movements.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
        </div>

        <form action="{{ route('admin.stock-movements.store') }}" method="POST" class="space-y-6"
              x-data="{
                  type: '{{ old('type', 'in') }}',
                  selected: null,
                  stock: null,
                  unit: '',
                  pick(option) {
                      this.selected = option.value;
                      this.stock = option.stock;
                      this.unit = option.unit;
                  }
              }">
            @csrf
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Movement Details</h3>
                <p class="text-sm text-gray-500 mb-5">
                    <span class="font-medium text-green-700">Stock In</span> = purchase/replenishment ·
                    <span class="font-medium text-red-700">Stock Out</span> = consumption/loss ·
                    <span class="font-medium text-blue-700">Adjustment</span> = set the correct stock level.
                </p>
                <div class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1.5">Product <span class="text-red-500">*</span></label>
                            <select name="product_id" id="product_id" required x-model="selected"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                <option value="">Select a product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}"
                                            data-stock="{{ $product->stock_qty }}"
                                            data-unit="{{ $product->unit }}"
                                            {{ old('product_id', $selectedProduct) == $product->id ? 'selected' : '' }}
                                            x-init="if (document.getElementById('product_id').value === '{{ $product->id }}') { stock = '{{ $product->stock_qty }}'; unit = '{{ $product->unit }}'; }">
                                        {{ $product->name }}@if($product->sku) ({{ $product->sku }})@endif
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1.5 text-xs text-gray-400" x-show="selected" x-cloak>
                                Current stock: <span class="font-semibold" x-text="stock"></span> <span x-text="unit"></span>
                            </p>
                            @error('product_id')
                                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Movement Type <span class="text-red-500">*</span></label>
                            <div class="flex gap-2">
                                @foreach(\App\Models\StockMovement::TYPES as $key => $label)
                                    <button type="button" @click="type = '{{ $key }}'"
                                            :class="type === '{{ $key }}' ? 'bg-brand-600 text-white border-brand-600' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100'"
                                            class="flex-1 px-3 py-2.5 rounded-xl border text-sm font-medium transition">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                            <input type="hidden" name="type" :value="type">
                            @error('type')
                                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div x-show="type !== 'adjustment'">
                            <x-admin.input name="quantity" label="Quantity" type="number" step="0.001" min="0.001" :value="old('quantity')" required />
                        </div>
                        <div x-show="type === 'adjustment'" x-cloak>
                            <x-admin.input name="quantity_final" label="New Stock Level" type="number" step="0.001" min="0" :value="old('quantity_final')" helptext="Set the actual counted stock. The difference is recorded." />
                        </div>
                        <div x-show="type === 'in'" x-cloak>
                            <x-admin.input name="unit_cost" label="Unit Cost (₹, optional)" type="number" step="1" min="0" :value="old('unit_cost')" />
                        </div>
                    </div>

                    <div x-show="type === 'in'" x-cloak>
                        <x-admin.select name="supplier_id" label="Supplier" :options="$suppliers->pluck('name', 'id')->all()" :value="old('supplier_id')" placeholder="None" />
                    </div>

                    <x-admin.textarea name="note" label="Note" :value="old('note')" rows="2" helptext="Required for adjustments." />
                </div>
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Record Movement</x-admin.button>
            </div>
        </form>
    </div>
@endsection
