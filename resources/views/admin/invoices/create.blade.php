@extends('admin.layout')

@section('page-title', 'New Invoice')

@section('content')
    <div class="space-y-6" x-data="invoiceForm()" x-init="init()">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-900">New Invoice</h2>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border shadow-2xs transition-all"
                      :style="{ backgroundColor: accentColor + '15', color: accentColor, borderColor: accentColor + '35' }">
                    <span class="w-2.5 h-2.5 rounded-full shadow-2xs" :style="{ backgroundColor: accentColor }"></span>
                    <span x-text="docTitle"></span>
                </span>
            </div>
            <x-admin.button href="{{ route('admin.invoices.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
        </div>

        <form action="{{ route('admin.invoices.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Invoice Details</h3>
                    <p class="text-sm text-gray-500">Document numbering prefix and layout adapt automatically from the selected Service Reference.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                    <div class="md:col-span-1">
                        <x-admin.select name="customer_id" label="Customer" :options="$customers->mapWithKeys(fn ($c) => [$c->id => $c->name . ($c->phone ? ' — ' . $c->phone : '')])->all()" :value="old('customer_id', $preselectCustomer)" placeholder="Select a customer" required />
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Service Reference</label>
                        <select name="service_id" x-model="selectedServiceId" @change="onServiceChange($event)"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <option value="">-- General Invoice (No Service) --</option>
                            @foreach($services as $svc)
                                <option value="{{ $svc->id }}" {{ (string)old('service_id') === (string)$svc->id ? 'selected' : '' }}>
                                    {{ $svc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-1">
                        <x-admin.input name="invoice_date" label="Invoice Date" type="date" :value="old('invoice_date', now()->toDateString())" required />
                    </div>

                    <div class="md:col-span-1">
                        <x-admin.input name="due_date" label="Due Date" type="date" :value="old('due_date')" />
                    </div>
                </div>

                {{-- Package / Density Variations Selector --}}
                <div x-show="variations && variations.length > 0" x-transition class="rounded-2xl border border-brand-200 bg-brand-50/50 p-4 space-y-3">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-brand-600 text-white flex items-center justify-center text-xs font-bold shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2H5a2 2 0 00-2 2v2m14 0h.01M5 11h.01"/></svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Service Package / Density Variations</h4>
                                <p class="text-[11px] text-gray-500">Select a variation to automatically add or update the line item with standard density and rate.</p>
                            </div>
                        </div>

                        <div class="w-full sm:w-auto">
                            <select @change="applyVariation($event.target.value)"
                                    class="w-full sm:w-72 text-xs font-bold rounded-xl border-brand-300 bg-white py-2 px-3 text-brand-900 shadow-2xs focus:border-brand-500 focus:ring-brand-500">
                                <option value="">-- Choose Variation Preset --</option>
                                <template x-for="(v, vIdx) in variations" :key="vIdx">
                                    <option :value="vIdx" :selected="selectedVariationIndex === vIdx"
                                            x-text="v.name + ' — ₹' + Number(v.rate || 0).toLocaleString('en-IN') + ' / ' + (v.unit || 'Kanal')"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    {{-- Quick Click Pills --}}
                    <div class="flex items-center gap-2 flex-wrap pt-1 border-t border-brand-100">
                        <span class="text-[10px] uppercase font-bold text-brand-700 tracking-wider">Quick Presets:</span>
                        <template x-for="(v, vIdx) in variations" :key="vIdx">
                            <button type="button" @click="applyVariation(vIdx)"
                                    class="px-2.5 py-1 text-xs font-semibold rounded-lg border transition-all inline-flex items-center gap-1.5 shadow-2xs cursor-pointer"
                                    :class="selectedVariationIndex === vIdx ? 'bg-brand-700 text-white border-brand-700 ring-2 ring-brand-300' : 'bg-white text-gray-800 border-gray-200 hover:border-brand-300 hover:bg-brand-50/50'">
                                <span class="w-2 h-2 rounded-full" :class="selectedVariationIndex === vIdx ? 'bg-white' : 'bg-brand-500'"></span>
                                <span x-text="v.name"></span>
                                <span class="text-[10px] font-bold" :class="selectedVariationIndex === vIdx ? 'text-brand-100' : 'text-brand-700'" x-text="'₹' + Number(v.rate || 0).toLocaleString('en-IN')"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-lg font-semibold text-gray-900">Line Items</h3>
                    <button type="button" @click="addEmpty()" class="inline-flex items-center justify-center font-semibold transition text-sm rounded-xl shadow-sm px-3.5 py-1.5 text-xs bg-brand-600 text-white hover:bg-brand-700">+ Add Custom</button>
                </div>
                <p class="text-sm text-gray-500 mb-4">Click a product below to add it instantly, or search and add manually.</p>

                {{-- Quick-Add Product Grid --}}
                <div x-show="products.length > 0" class="mb-5">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="relative flex-1 max-w-sm">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" x-model="productSearch" placeholder="Filter products..."
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 pl-9 pr-4 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>
                        <span class="text-xs text-gray-400" x-text="filteredProducts.length + ' product' + (filteredProducts.length !== 1 ? 's' : '')"></span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2 max-h-64 overflow-y-auto pr-1">
                        <template x-for="p in filteredProducts" :key="p.id">
                            <button type="button" @click="quickAdd(p)"
                                    class="flex items-center gap-3 w-full text-left px-3 py-2.5 rounded-xl border transition
                                           hover:border-brand-300 hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand-200"
                                    :class="p.stock_qty <= 0 ? 'border-gray-200 bg-gray-50 opacity-60' : 'border-gray-200 bg-white'">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate" x-text="p.name"></p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-xs text-gray-500" x-text="p.sku || p.unit"></span>
                                        <span class="text-xs font-semibold text-gray-700" x-text="'₹' + Number(p.rate).toFixed(0)"></span>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full"
                                          :class="p.stock_qty <= 0 ? 'bg-red-50 text-red-600' : (p.stock_qty <= (p.low_stock || 5) ? 'bg-amber-50 text-amber-600' : 'bg-green-50 text-green-600')"
                                          x-text="p.stock_qty + ' ' + p.unit"></span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Line Items --}}
                <div class="space-y-2">
                    {{-- Header Row --}}
                    <div class="hidden md:grid md:grid-cols-12 gap-2 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wide" x-show="rows.length > 0">
                        <div class="col-span-4">Product</div>
                        <div class="col-span-2 text-center">Qty</div>
                        <div class="col-span-2 text-center">Rate ₹</div>
                        <div class="col-span-1 text-center">Disc ₹</div>
                        <div class="col-span-1 text-center">GST %</div>
                        <div class="col-span-1 text-right">Total</div>
                        <div class="col-span-1"></div>
                    </div>

                    <template x-for="(row, i) in rows" :key="i">
                        <div class="rounded-xl bg-gray-50 border border-gray-100 p-3 md:p-4">
                            {{-- Main row --}}
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-2 items-end">
                                {{-- Product selector --}}
                                <div class="md:col-span-4" x-data="{ open: false, query: '' }" @click.away="open = false" class="relative">
                                    <label class="block text-xs font-medium text-gray-500 mb-1 md:sr-only">Product</label>
                                    <div class="relative">
                                        <input type="text" x-model="query" @input="open = query.length > 0" @focus="open = query.length > 0"
                                               :placeholder="row.name || 'Search product...'"
                                               class="w-full rounded-xl border border-gray-200 bg-white pl-3 pr-8 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                        <button type="button" @click="query = ''; row.product_id = ''; row.name = ''; row.unit = ''; row.rate = 0; row.gst_rate = 0;" x-show="row.product_id"
                                                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <div x-show="open" x-cloak class="absolute z-30 mt-1 w-full bg-white rounded-xl shadow-lg border border-gray-200 max-h-56 overflow-y-auto">
                                        <template x-for="p in products.filter(x => x.name.toLowerCase().includes(query.toLowerCase()) || (x.sku && x.sku.toLowerCase().includes(query.toLowerCase())))" :key="p.id">
                                            <button type="button" @click="pickProduct(row, p); query = ''; open = false;"
                                                    class="w-full text-left px-3 py-2 hover:bg-brand-50 flex items-center justify-between text-sm border-b border-gray-50 last:border-0">
                                                <span>
                                                    <span class="font-medium text-gray-900" x-text="p.name"></span>
                                                    <span class="text-xs text-gray-400 ml-1" x-text="p.sku ? '(' + p.sku + ')' : ''"></span>
                                                </span>
                                                <span class="text-xs font-medium" :class="p.stock_qty <= 0 ? 'text-red-500' : 'text-green-600'" x-text="p.stock_qty + ' ' + p.unit"></span>
                                            </button>
                                        </template>
                                        <div x-show="products.filter(x => x.name.toLowerCase().includes(query.toLowerCase()) || (x.sku && x.sku.toLowerCase().includes(query.toLowerCase()))).length === 0" class="px-3 py-3 text-sm text-gray-400 text-center">
                                            No products found
                                        </div>
                                    </div>
                                    <input type="hidden" :name="'items[' + i + '][product_id]'" :value="row.product_id || ''">
                                </div>

                                {{-- Name (visible for custom items) --}}
                                <div class="md:col-span-4" x-show="!row.product_id">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Item Name</label>
                                    <input type="text" x-model="row.name" :name="'items[' + i + '][name]'" required placeholder="Enter item name"
                                           class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                </div>
                                <input type="hidden" x-model="row.name" :name="'items[' + i + '][name]'" x-show="row.product_id">

                                {{-- Qty --}}
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-gray-500 mb-1 md:sr-only">Qty</label>
                                    <input type="number" step="0.001" min="0.001" x-model.number="row.qty" :name="'items[' + i + '][qty]'" required
                                           class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-center focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                </div>

                                {{-- Rate --}}
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-gray-500 mb-1 md:sr-only">Rate ₹</label>
                                    <input type="number" step="1" min="0" x-model.number="row.rate" :name="'items[' + i + '][rate]'" required
                                           class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-center focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                </div>

                                {{-- Discount --}}
                                <div class="md:col-span-1">
                                    <label class="block text-xs font-medium text-gray-500 mb-1 md:sr-only">Disc ₹</label>
                                    <input type="number" step="1" min="0" x-model.number="row.discount" :name="'items[' + i + '][discount]'"
                                           class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-center focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                </div>

                                {{-- GST --}}
                                <div class="md:col-span-1">
                                    <label class="block text-xs font-medium text-gray-500 mb-1 md:sr-only">GST %</label>
                                    <input type="number" step="0.01" min="0" max="100" x-model.number="row.gst_rate" :name="'items[' + i + '][gst_rate]'"
                                           class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-center focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                </div>

                                {{-- Line Total --}}
                                <div class="md:col-span-1 text-right flex items-center gap-1 justify-end md:justify-end">
                                    <span class="text-sm font-bold text-gray-900" x-text="'₹' + fmt(lineTotal(row))"></span>
                                    <button type="button" @click="remove(i)" class="text-red-400 hover:text-red-600 p-1 flex-shrink-0" title="Remove">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Unit (hidden input) --}}
                            <input type="hidden" x-model="row.unit" :name="'items[' + i + '][unit]'">
                        </div>
                    </template>

                    <p class="text-sm text-gray-400 py-4 text-center" x-show="rows.length === 0">
                        Click a product above or press "+ Add Custom" to add line items.
                    </p>
                </div>

                {{-- Totals --}}
                <div class="mt-6 flex justify-end" x-show="rows.length > 0">
                    <div class="w-full max-w-xs bg-gray-50 border border-gray-100 rounded-2xl p-4 space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span class="font-semibold" x-text="'₹' + fmt(subtotal)"></span></div>
                        <div class="flex justify-between" x-show="discountTotal > 0"><span class="text-gray-500">Discount</span><span class="font-semibold text-red-600" x-text="'− ₹' + fmt(discountTotal)"></span></div>
                        <div class="flex justify-between" x-show="gstTotal > 0"><span class="text-gray-500">GST</span><span class="font-semibold" x-text="'₹' + fmt(gstTotal)"></span></div>
                        <div class="flex justify-between border-t border-gray-200 pt-2"><span class="font-semibold text-gray-900">Grand Total</span><span class="font-bold text-brand-700 text-base" x-text="'₹' + fmt(grandTotal)"></span></div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Terms & Conditions</label>
                        <textarea name="terms" x-model="invoiceTerms" rows="3"
                                  class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"></textarea>
                    </div>
                    <x-admin.textarea name="notes" label="Internal Notes (optional)" :value="old('notes')" rows="3" />
                </div>
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Create Invoice</x-admin.button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function invoiceForm() {
            return {
                rows: [],
                productSearch: '',
                products: @js($products->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'sku' => $p->sku, 'unit' => $p->unit, 'rate' => (float) $p->rate, 'gst' => (float) $p->gst_rate, 'stock_qty' => (float) $p->stock_qty, 'low_stock' => (float) $p->low_stock_threshold])),
                services: @js($servicesJson),
                selectedServiceId: '{{ old('service_id', '') }}',
                variations: [],
                selectedVariationIndex: '',
                invoiceTerms: @js(old('terms', $terms)),
                accentColor: '#16a34a',
                docTitle: 'TAX INVOICE',

                init() {
                    if (this.selectedServiceId) {
                        const svc = this.services.find(s => String(s.id) === String(this.selectedServiceId));
                        if (svc) {
                            const defs = svc.defaults || {};
                            const invDefs = svc.invoice_defaults || {};
                            this.variations = (defs.variations && Array.isArray(defs.variations)) ? defs.variations : [];
                            this.accentColor = invDefs.accent_color || '#16a34a';
                            this.docTitle = invDefs.document_title || 'TAX INVOICE';
                        }
                    }
                    if (this.rows.length === 0) this.addEmpty();
                },

                onServiceChange(event) {
                    const sId = event.target.value;
                    this.selectedServiceId = sId;
                    const svc = this.services.find(s => String(s.id) === String(sId));
                    if (svc) {
                        const defs = svc.defaults || {};
                        const invDefs = svc.invoice_defaults || {};
                        this.variations = (defs.variations && Array.isArray(defs.variations)) ? defs.variations : [];
                        this.selectedVariationIndex = '';
                        this.accentColor = invDefs.accent_color || '#16a34a';
                        this.docTitle = invDefs.document_title || 'TAX INVOICE';

                        if (invDefs.terms) {
                            this.invoiceTerms = invDefs.terms;
                        }

                        if (this.rows.length === 1 && (!this.rows[0].name || this.rows[0].name === '')) {
                            this.rows[0].name = svc.name;
                            if (defs.base_price) {
                                this.rows[0].rate = parseFloat(defs.base_price) || 0;
                            }
                            if (defs.unit) {
                                this.rows[0].unit = defs.unit;
                            }
                        }
                    } else {
                        this.variations = [];
                        this.selectedVariationIndex = '';
                        this.accentColor = '#16a34a';
                        this.docTitle = 'TAX INVOICE';
                    }
                },

                applyVariation(vIdx) {
                    this.selectedVariationIndex = vIdx;
                    if (vIdx === '' || vIdx === null || vIdx === undefined) return;
                    const v = this.variations[vIdx];
                    if (!v) return;

                    const svc = this.services.find(s => String(s.id) === String(this.selectedServiceId));
                    const title = (svc ? svc.name : 'Service') + ' (' + v.name + ')';

                    if (this.rows.length === 1 && (!this.rows[0].name || this.rows[0].name === '' || (svc && this.rows[0].name.startsWith(svc.name)))) {
                        this.rows[0].name = title;
                        this.rows[0].rate = parseFloat(v.rate) || 0;
                        this.rows[0].unit = v.unit || 'Kanal';
                    } else {
                        this.rows.push({
                            product_id: '',
                            name: title,
                            unit: v.unit || 'Kanal',
                            qty: 1,
                            rate: parseFloat(v.rate) || 0,
                            discount: 0,
                            gst_rate: 0
                        });
                    }
                },

                get filteredProducts() {
                    const q = this.productSearch.toLowerCase();
                    if (!q) return this.products;
                    return this.products.filter(p =>
                        p.name.toLowerCase().includes(q) ||
                        (p.sku && p.sku.toLowerCase().includes(q))
                    );
                },

                addEmpty() {
                    this.rows.push({ product_id: '', name: '', unit: '', qty: 1, rate: 0, discount: 0, gst_rate: 0 });
                },

                quickAdd(product) {
                    this.rows.push({
                        product_id: product.id,
                        name: product.name,
                        unit: product.unit,
                        qty: 1,
                        rate: product.rate,
                        discount: 0,
                        gst_rate: product.gst,
                    });
                },

                pickProduct(row, product) {
                    row.product_id = product.id;
                    row.name = product.name;
                    row.unit = product.unit;
                    row.rate = product.rate;
                    row.gst_rate = product.gst;
                },

                remove(i) {
                    this.rows.splice(i, 1);
                },

                lineTotal(row) {
                    return Math.max(0, (parseFloat(row.qty) || 0) * (parseFloat(row.rate) || 0) - (parseFloat(row.discount) || 0));
                },

                lineGst(row) {
                    return Math.round(this.lineTotal(row) * (parseFloat(row.gst_rate) || 0)) / 100;
                },

                get subtotal() {
                    return Math.round(this.rows.reduce((s, r) => s + (parseFloat(r.qty) || 0) * (parseFloat(r.rate) || 0), 0) * 100) / 100;
                },

                get discountTotal() {
                    return Math.round(this.rows.reduce((s, r) => s + (parseFloat(r.discount) || 0), 0) * 100) / 100;
                },

                get gstTotal() {
                    return Math.round(this.rows.reduce((s, r) => s + this.lineGst(r), 0) * 100) / 100;
                },

                get grandTotal() {
                    return Math.max(0, this.subtotal - this.discountTotal + this.gstTotal);
                },

                fmt(v) {
                    return Number(v || 0).toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                }
            }
        }
    </script>
    @endpush
@endsection
