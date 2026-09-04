@extends('admin.layout')

@section('page-title', 'New Invoice')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">New Invoice</h2>
            <x-admin.button href="{{ route('admin.invoices.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
        </div>

        <form action="{{ route('admin.invoices.store') }}" method="POST" class="space-y-6"
              x-data="{
                  rows: [],
                  products: @js($products->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'sku' => $p->sku, 'unit' => $p->unit, 'rate' => (float) $p->rate, 'gst' => (float) $p->gst_rate])),
                  add() { this.rows.push({ product_id: '', name: '', unit: '', qty: 1, rate: 0, discount: 0, gst_rate: 0 }); },
                  remove(i) { this.rows.splice(i, 1); },
                  pick(row) {
                      const p = this.products.find(x => x.id == row.product_id);
                      if (p) { row.name = p.name; row.unit = p.unit; row.rate = p.rate; row.gst_rate = p.gst; }
                  },
                  lineTotal(row) { return Math.max(0, (parseFloat(row.qty) || 0) * (parseFloat(row.rate) || 0) - (parseFloat(row.discount) || 0)); },
                  lineGst(row) { return Math.round(this.lineTotal(row) * (parseFloat(row.gst_rate) || 0)) / 100; },
                  get subtotal() { return Math.round(this.rows.reduce((s, r) => s + (parseFloat(r.qty) || 0) * (parseFloat(r.rate) || 0), 0) * 100) / 100; },
                  get discountTotal() { return Math.round(this.rows.reduce((s, r) => s + (parseFloat(r.discount) || 0), 0) * 100) / 100; },
                  get gstTotal() { return Math.round(this.rows.reduce((s, r) => s + this.lineGst(r), 0) * 100) / 100; },
                  get grandTotal() { return Math.max(0, this.subtotal - this.discountTotal + this.gstTotal); },
                  fmt(v) { return Number(v || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
              }"
              x-init="if (rows.length === 0) add()">
            @csrf

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Invoice Details</h3>
                <p class="text-sm text-gray-500 mb-5">Number is generated automatically from your prefix in Settings → Invoice.</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <x-admin.select name="customer_id" label="Customer" :options="$customers->mapWithKeys(fn ($c) => [$c->id => $c->name . ($c->phone ? ' — ' . $c->phone : '')])->all()" :value="old('customer_id', $preselectCustomer)" placeholder="Select a customer" required />
                    <x-admin.input name="invoice_date" label="Invoice Date" type="date" :value="old('invoice_date', now()->toDateString())" required />
                    <x-admin.input name="due_date" label="Due Date" type="date" :value="old('due_date')" />
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-lg font-semibold text-gray-900">Line Items</h3>
                    <button type="button" @click="add()" class="inline-flex items-center justify-center font-semibold transition text-sm rounded-xl shadow-sm px-3.5 py-1.5 text-xs bg-brand-600 text-white hover:bg-brand-700">+ Add Item</button>
                </div>
                <p class="text-sm text-gray-500 mb-4">Pick a product to autofill rate & GST, or type a custom item. Product lines deduct stock on save.</p>

                <div class="space-y-3">
                    <template x-for="(row, i) in rows" :key="i">
                        <div class="rounded-xl bg-gray-50 border border-gray-100 p-4">
                            <div class="flex flex-wrap items-end gap-3">
                                <div class="flex-1 min-w-[200px]">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Product / Item</label>
                                    <select x-model="row.product_id" @change="pick(row)"
                                            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                        <option value="">Custom item</option>
                                        <template x-for="p in products" :key="p.id">
                                            <option :value="p.id" x-text="p.name + (p.sku ? ' (' + p.sku + ')' : '')"></option>
                                        </template>
                                    </select>
                                    <input type="hidden" :name="'items[' + i + '][product_id]'" :value="row.product_id || ''">
                                </div>
                                <div class="flex-1 min-w-[180px]">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Item Name</label>
                                    <input type="text" x-model="row.name" :name="'items[' + i + '][name]'" required
                                           class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Qty</label>
                                    <input type="number" step="0.001" min="0.001" x-model="row.qty" :name="'items[' + i + '][qty]'" required
                                           class="w-24 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Unit</label>
                                    <input type="text" x-model="row.unit" :name="'items[' + i + '][unit]'"
                                           class="w-20 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Rate ₹</label>
                                    <input type="number" step="0.01" min="0" x-model="row.rate" :name="'items[' + i + '][rate]'" required
                                           class="w-28 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Discount ₹</label>
                                    <input type="number" step="0.01" min="0" x-model="row.discount" :name="'items[' + i + '][discount]'"
                                           class="w-24 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">GST %</label>
                                    <input type="number" step="0.01" min="0" max="100" x-model="row.gst_rate" :name="'items[' + i + '][gst_rate]'"
                                           class="w-20 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                </div>
                                <div class="flex items-center gap-2 pb-1">
                                    <span class="text-sm font-bold text-gray-900 w-24 text-right" x-text="'₹' + fmt(lineTotal(row))"></span>
                                    <button type="button" @click="remove(i)" class="text-red-500 hover:text-red-700 p-1" title="Remove line">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                    <p class="text-sm text-gray-400" x-show="rows.length === 0">No items yet — click "+ Add Item".</p>
                </div>

                <div class="mt-6 flex justify-end">
                    <div class="w-full max-w-xs bg-gray-50 border border-gray-100 rounded-2xl p-4 space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span class="font-semibold" x-text="'₹' + fmt(subtotal)"></span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Discount</span><span class="font-semibold text-red-600" x-text="'− ₹' + fmt(discountTotal)"></span></div>
                        <div class="flex justify-between"><span class="text-gray-500">GST</span><span class="font-semibold" x-text="'₹' + fmt(gstTotal)"></span></div>
                        <div class="flex justify-between border-t border-gray-200 pt-2"><span class="font-semibold text-gray-900">Grand Total</span><span class="font-bold text-brand-700 text-base" x-text="'₹' + fmt(grandTotal)"></span></div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-admin.textarea name="terms" label="Terms & Conditions" :value="old('terms', $terms)" rows="3" />
                    <x-admin.textarea name="notes" label="Internal Notes (optional)" :value="old('notes')" rows="3" />
                </div>
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Create Invoice</x-admin.button>
            </div>
        </form>
    </div>
@endsection
