@extends('admin.layout')

@section('page-title', 'Record Stock Movement')

@section('content')
    <div class="space-y-6 max-w-3xl" x-data="stockForm(@js($preselectId))" x-init="init()">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Record Stock Movement</h2>
            <x-admin.button href="{{ route('admin.stock-movements.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
        </div>

        <form action="{{ route('admin.stock-movements.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Movement Details</h3>
                <p class="text-sm text-gray-500 mb-5">
                    <span class="font-medium text-green-700">Stock In</span> = purchase/replenishment ·
                    <span class="font-medium text-red-700">Stock Out</span> = consumption/loss ·
                    <span class="font-medium text-blue-700">Adjustment</span> = set the correct stock level.
                </p>

                <div class="space-y-5">
                    {{-- Product picker --}}
                    <div x-data="{ open: false, query: '' }">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Product <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text"
                                   x-model="query"
                                   @input="open = query.length > 0"
                                   @focus="open = true"
                                   @click.away="open = false"
                                   :placeholder="currentProduct ? currentProduct.name + ' (' + currentProduct.sku + ')' : 'Search product by name or SKU...'"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 pl-9 pr-9 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <button type="button" @click="clear(); query = ''" x-show="selected" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div x-show="open" x-cloak class="relative z-30 mt-1.5">
                            <div class="absolute w-full bg-white rounded-xl shadow-lg border border-gray-200 max-h-64 overflow-y-auto">
                                <template x-for="p in filteredProducts" :key="p.id">
                                    <button type="button"
                                            @click="pick(p); query = ''; open = false;"
                                            class="w-full text-left px-3 py-2.5 hover:bg-brand-50 flex items-center justify-between gap-3 border-b border-gray-50 last:border-0 text-sm"
                                            :class="selected == p.id ? 'bg-brand-50' : ''">
                                        <span class="min-w-0">
                                            <span class="font-medium text-gray-900 block truncate" x-text="p.name"></span>
                                            <span class="text-xs text-gray-400" x-text="p.sku ? p.sku : p.unit"></span>
                                        </span>
                                        <span class="flex items-center gap-2 flex-shrink-0">
                                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                                  :class="p.stock <= 0 ? 'bg-red-50 text-red-600' : (p.low > 0 && p.stock <= p.low ? 'bg-amber-50 text-amber-600' : 'bg-green-50 text-green-600')"
                                                  x-text="formatQty(p.stock) + ' ' + p.unit"></span>
                                            <span class="text-xs text-gray-400" x-text="'₹' + Number(p.rate).toFixed(0)"></span>
                                        </span>
                                    </button>
                                </template>
                                <p x-show="filteredProducts.length === 0" class="px-3 py-3 text-sm text-gray-400 text-center">No products found</p>
                            </div>
                        </div>

                        <div x-show="selected" x-cloak class="mt-2 flex flex-wrap items-center gap-2">
                            <span class="text-xs font-medium px-2.5 py-1 rounded-full"
                                  :class="currentProduct.stock <= 0 ? 'bg-red-50 text-red-700' : (currentProduct.low > 0 && currentProduct.stock <= currentProduct.low ? 'bg-amber-50 text-amber-700' : 'bg-green-50 text-green-700')">
                                Current stock: <span class="font-bold" x-text="formatQty(currentProduct.stock) + ' ' + currentProduct.unit"></span>
                            </span>
                            <span x-show="currentProduct.low > 0" class="text-xs text-gray-400">Low-stock threshold: <span x-text="formatQty(currentProduct.low)"></span></span>
                        </div>

                        <input type="hidden" name="product_id" :value="selected" value="{{ old('product_id', $preselectId) }}">
                        @error('product_id')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Movement type --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Movement Type <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" @click="type = 'in'"
                                    :class="type === 'in' ? 'bg-green-600 text-white border-green-600' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100'"
                                    class="flex flex-col items-center gap-1 px-3 py-3 rounded-xl border text-sm font-medium transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 4v8m0 0l4-4m-4 4l-4-4"/></svg>
                                Stock In
                            </button>
                            <button type="button" @click="type = 'out'"
                                    :class="type === 'out' ? 'bg-red-600 text-white border-red-600' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100'"
                                    class="flex flex-col items-center gap-1 px-3 py-3 rounded-xl border text-sm font-medium transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V8m0 0l4 4m-4-4L3 12m14 4V4m0 0l4 4m-4-4l-4 4"/></svg>
                                Stock Out
                            </button>
                            <button type="button" @click="type = 'adjustment'"
                                    :class="type === 'adjustment' ? 'bg-blue-600 text-white border-blue-600' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100'"
                                    class="flex flex-col items-center gap-1 px-3 py-3 rounded-xl border text-sm font-medium transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Adjustment
                            </button>
                        </div>
                        <input type="hidden" name="type" :value="type">
                        @error('type')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Quantity / cost --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div x-show="type !== 'adjustment'">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Quantity <span class="text-red-500">*</span></label>
                            <input type="number" name="quantity" step="0.001" min="0.001" x-model="quantity" x-ref="quantity"
                                   :class="previewNew !== null && previewNew < 0 ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-gray-200 focus:border-brand-500 focus:ring-brand-100'"
                                   class="w-full rounded-xl border bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:ring-2"
                                   placeholder="0">
                            @error('quantity')
                                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div x-show="type === 'adjustment'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">New Stock Level (counted) <span class="text-red-500">*</span></label>
                            <input type="number" name="quantity_final" step="0.001" min="0" x-model="quantity_final"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                                   placeholder="0">
                            <p class="mt-1.5 text-xs text-gray-400">The difference vs. booked stock is recorded.</p>
                            @error('quantity_final')
                                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div x-show="type === 'in'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Unit Cost (₹, optional)</label>
                            <input type="number" name="unit_cost" step="1" min="0" x-model="unit_cost"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                                   placeholder="0">
                            <p class="mt-1.5 text-xs text-gray-400" x-show="previewCost !== null">Total: <span class="font-semibold" x-text="'₹' + fmt(previewCost)"></span></p>
                            @error('unit_cost')
                                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Supplier --}}
                    <div x-show="type === 'in'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Supplier</label>
                        <select name="supplier_id" x-model="supplier_id"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <option value="">None</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Batch / Lot Information (Optional) --}}
                    <div class="border-t border-gray-100 pt-4" x-show="type !== 'adjustment'" x-cloak>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-semibold text-gray-800">Batch / Lot Tracking (Optional)</span>
                            <span class="text-xs text-gray-400">For chemicals, seeds, bundles</span>
                        </div>

                        {{-- When type === 'in': Create or enter new batch --}}
                        <div x-show="type === 'in'" class="space-y-3">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Batch Number</label>
                                    <input type="text" name="batch_number" value="{{ old('batch_number') }}"
                                           placeholder="e.g. LOT-2026-09"
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Mfg Date</label>
                                    <input type="date" name="mfg_date" value="{{ old('mfg_date') }}"
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Expiry Date</label>
                                    <input type="date" name="expiry_date" value="{{ old('expiry_date') }}"
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                </div>
                            </div>
                        </div>

                        {{-- When type === 'out': Select from existing active batches --}}
                        <div x-show="type === 'out'" class="space-y-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Deduct from Batch</label>
                            <template x-if="currentProduct && currentProduct.batches && currentProduct.batches.length > 0">
                                <select name="batch_id"
                                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                    <option value="">Auto / General Stock (No batch specified)</option>
                                    <template x-for="b in currentProduct.batches" :key="b.id">
                                        <option :value="b.id" x-text="b.batch_number + ' (Avail: ' + b.current_qty + ' ' + currentProduct.unit + (b.expiry_date ? ', Exp: ' + b.expiry_date : '') + ')'"></option>
                                    </template>
                                </select>
                            </template>
                            <template x-if="!currentProduct || !currentProduct.batches || currentProduct.batches.length === 0">
                                <p class="text-xs text-gray-400 italic">No tracked batches currently recorded for this product.</p>
                            </template>
                        </div>
                    </div>

                    {{-- Note --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Note</label>
                        <textarea name="note" rows="2" x-model="note"
                                  class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 resize-y"
                                  placeholder="Why is this stock changing?"></textarea>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <template x-for="chip in noteChips" :key="chip">
                                <button type="button" @click="note = chip"
                                        :class="note === chip ? 'bg-brand-100 text-brand-700 border-brand-200' : 'bg-gray-50 text-gray-500 border-gray-200 hover:bg-gray-100'"
                                        class="text-xs font-medium px-3 py-1.5 rounded-full border transition" x-text="chip"></button>
                            </template>
                        </div>
                        @error('note')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Live preview --}}
            <div x-show="selected" x-cloak class="rounded-2xl border p-5 transition"
                 :class="previewClass">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-semibold text-gray-900">Result Preview</p>
                    <span x-show="previewNew !== null && currentProduct"
                          class="text-xs font-medium px-2.5 py-1 rounded-full"
                          :class="previewNew < 0 ? 'bg-red-50 text-red-700' : (previewNew <= currentProduct.low && currentProduct.low > 0 ? 'bg-amber-50 text-amber-700' : 'bg-green-50 text-green-700')"
                          x-text="previewNew < 0 ? 'Would go negative' : (previewNew <= currentProduct.low && currentProduct.low > 0 ? 'Would drop to low stock' : 'Stock level OK')"></span>
                </div>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-2 text-sm" x-show="type !== 'adjustment'">
                    <span class="text-gray-500">Current</span>
                    <span class="font-semibold text-gray-900" x-text="formatQty(currentProduct.stock) + ' ' + currentProduct.unit"></span>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    <span class="font-bold" :class="previewDelta >= 0 ? 'text-green-600' : 'text-red-600'" x-text="(previewDelta >= 0 ? '+' : '') + formatQty(previewDelta) + ' ' + currentProduct.unit"></span>
                    <span class="text-gray-400" x-text="'(' + typeLabel + ')'"></span>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    <span class="font-bold text-gray-900" x-text="formatQty(previewNew) + ' ' + currentProduct.unit"></span>
                </div>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-2 text-sm" x-show="type === 'adjustment'">
                    <span class="text-gray-500">Booked</span>
                    <span class="font-semibold text-gray-900" x-text="formatQty(currentProduct.stock) + ' ' + currentProduct.unit"></span>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    <span class="text-gray-500">Counted</span>
                    <span class="font-semibold text-gray-900" x-text="formatQty(previewNew) + ' ' + currentProduct.unit"></span>
                    <span class="text-gray-400" x-show="currentProduct">(Δ <span :class="previewAdjustDelta >= 0 ? 'text-green-600' : 'text-red-600'" x-text="(previewAdjustDelta >= 0 ? '+' : '') + formatQty(previewAdjustDelta)"></span>)</span>
                </div>
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Record Movement</x-admin.button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function stockForm(preselectId) {
            return {
                type: @js(old('type', request('type', 'in'))),
                products: @js($products->map(fn ($p) => [
                    'id' => (string) $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'unit' => $p->unit,
                    'rate' => (float) $p->rate,
                    'stock' => (float) $p->stock_qty,
                    'low' => (float) $p->low_stock_threshold,
                    'batches' => $p->activeBatches->map(fn ($b) => [
                        'id' => (string) $b->id,
                        'batch_number' => $b->batch_number,
                        'current_qty' => (float) $b->current_qty,
                        'expiry_date' => $b->expiry_date?->format('d M Y'),
                    ])->values()->all(),
                ])->all()),
                selected: '',
                stock: null,
                unit: '',
                rate: 0,
                low: 0,
                quantity: @js(old('quantity', request('qty', ''))),
                quantity_final: @js(old('quantity_final', request('qty', ''))),
                unit_cost: @js(old('unit_cost', request('cost', ''))),
                supplier_id: @js((string) old('supplier_id', '')),
                note: @js(old('note', request('note', ''))),
                noteChips: ['Purchase replenishment', 'Return / overstock', 'Damage or loss', 'Count correction'],

                init() {
                    if (preselectId && this.products.length) {
                        const p = this.products.find(x => x.id === String(preselectId));
                        if (p) this.pick(p);
                    }
                },

                get filteredProducts() {
                    const q = this.query.toLowerCase();
                    if (!q) return this.products;
                    return this.products.filter(p =>
                        p.name.toLowerCase().includes(q) ||
                        (p.sku && p.sku.toLowerCase().includes(q))
                    );
                },

                get currentProduct() {
                    if (!this.selected) return null;
                    return this.products.find(x => x.id === this.selected) || null;
                },

                pick(p) {
                    this.selected = p.id;
                    this.stock = p.stock;
                    this.unit = p.unit;
                    this.rate = p.rate;
                    this.low = p.low;
                },

                clear() {
                    this.selected = '';
                    this.stock = null;
                    this.unit = '';
                    this.rate = 0;
                    this.low = 0;
                    this.quantity = '';
                    this.quantity_final = '';
                    this.unit_cost = '';
                },

                get currentQty() {
                    const v = this.type === 'adjustment' ? this.quantity_final : this.quantity;
                    return parseFloat(v) || 0;
                },

                get previewDelta() {
                    if (!this.currentProduct || this.currentQty <= 0) return 0;
                    return this.type === 'in' ? this.currentQty : -this.currentQty;
                },

                get previewAdjustDelta() {
                    if (!this.currentProduct) return 0;
                    return this.currentQty - this.currentProduct.stock;
                },

                get previewNew() {
                    if (!this.currentProduct) return null;
                    if (this.type === 'adjustment') return this.currentQty;
                    if (this.currentQty <= 0) return null;
                    return this.currentProduct.stock + this.previewDelta;
                },

                get previewCost() {
                    const c = parseFloat(this.unit_cost) || 0;
                    if (!this.currentProduct || c <= 0) return null;
                    return Math.round(c * this.currentQty * 100) / 100;
                },

                get previewClass() {
                    if (this.previewNew === null || !this.currentProduct) return 'border-gray-100 bg-white shadow-sm';
                    if (this.previewNew < 0) return 'border-red-200 bg-red-50/50';
                    if (this.currentProduct.low > 0 && this.previewNew <= this.currentProduct.low) return 'border-amber-200 bg-amber-50/50';
                    return 'border-green-100 bg-green-50/40';
                },

                get typeLabel() {
                    return { in: 'Stock In', out: 'Stock Out', adjustment: 'Adjustment' }[this.type] || this.type;
                },

                formatQty(v) {
                    const n = Number(v || 0);
                    if (n === 0) return '0';
                    const s = n.toFixed(3).replace(/\.?0+$/, '');
                    return s.startsWith('.') ? '0' + s : s;
                },

                fmt(v) {
                    return Number(v || 0).toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                },
            };
        }
    </script>
    @endpush
@endsection