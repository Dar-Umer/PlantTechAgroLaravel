@extends('admin.layout')

@section('page-title', 'Edit Quotation — ' . $quotation->number)

@section('content')
@php
    $initialItems = [];
    if (old('items')) {
        $initialItems = old('items');
    } else {
        foreach ($quotation->items as $it) {
            $initialItems[] = [
                'product_id' => $it->product_id ?? '',
                'name' => $it->name,
                'unit' => $it->unit ?? 'Kanal',
                'qty' => (float) $it->qty,
                'rate' => (float) $it->rate,
                'discount' => (float) $it->discount,
                'gst_rate' => (float) $it->gst_rate,
            ];
        }
    }

    $productsJson = $products->map(fn($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'unit' => $p->unit,
        'rate' => (float) $p->rate,
        'gst_rate' => (float) $p->gst_rate,
    ]);
@endphp

<div class="space-y-6 max-w-5xl"
     x-data="{
        items: {{ json_encode($initialItems) }},
        products: {{ json_encode($productsJson) }},
        addItem() {
            this.items.push({
                product_id: '',
                name: '',
                unit: 'Kanal',
                qty: 1,
                rate: 0,
                discount: 0,
                gst_rate: 0
            });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        onProductChange(index, event) {
            const pId = event.target.value;
            if (!pId) return;
            const p = this.products.find(x => x.id == pId);
            if (p) {
                this.items[index].name = p.name;
                this.items[index].unit = p.unit || 'Pcs';
                this.items[index].rate = p.rate || 0;
                this.items[index].gst_rate = p.gst_rate || 0;
            }
        },
        lineTaxable(item) {
            const base = (parseFloat(item.qty || 0) * parseFloat(item.rate || 0)) - parseFloat(item.discount || 0);
            return Math.max(0, base);
        },
        lineTax(item) {
            return this.lineTaxable(item) * (parseFloat(item.gst_rate || 0) / 100);
        },
        lineTotal(item) {
            return this.lineTaxable(item) + this.lineTax(item);
        },
        get subtotal() {
            return this.items.reduce((acc, it) => acc + this.lineTaxable(it), 0);
        },
        get discountTotal() {
            return this.items.reduce((acc, it) => acc + parseFloat(it.discount || 0), 0);
        },
        get gstTotal() {
            return this.items.reduce((acc, it) => acc + this.lineTax(it), 0);
        },
        get grandTotal() {
            return this.items.reduce((acc, it) => acc + this.lineTotal(it), 0);
        },
        formatMoney(val) {
            return parseFloat(val || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
     }">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Edit Quotation: {{ $quotation->number }}</h2>
            <p class="text-sm text-gray-500 mt-1">Update items, pricing, or client details before sending or approving.</p>
        </div>
        <x-admin.button href="{{ route('admin.quotations.show', $quotation) }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>
            Back to Quotation
        </x-admin.button>
    </div>

    @if($errors->any())
        <div class="rounded-2xl bg-red-50 border border-red-200 p-4">
            <p class="text-sm font-semibold text-red-800">Please correct the errors below:</p>
            <ul class="mt-2 text-xs text-red-700 list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.quotations.update', $quotation) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Client & Service Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
            <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-100 pb-3">Client & Service Details</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <x-admin.input name="customer_name" label="Client / Farmer Name"
                               :value="old('customer_name', $quotation->customer_name)" required />

                <x-admin.input name="customer_phone" label="Phone Number"
                               :value="old('customer_phone', $quotation->customer_phone)" required />

                <x-admin.input name="customer_email" label="Email (Optional)" type="email"
                               :value="old('customer_email', $quotation->customer_email)" />

                <x-admin.input name="customer_area" label="Area / Locality"
                               :value="old('customer_area', $quotation->customer_area)" />

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Service / Project</label>
                    <select name="service_id" class="w-full text-sm rounded-xl border border-gray-200 py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        <option value="">-- Select Service (Optional) --</option>
                        @foreach($services as $svc)
                            <option value="{{ $svc->id }}" {{ old('service_id', $quotation->service_id) == $svc->id ? 'selected' : '' }}>
                                {{ $svc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Status</label>
                    <select name="status" class="w-full text-sm rounded-xl border border-gray-200 py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 font-medium">
                        @foreach(\App\Models\Quotation::STATUSES as $k => $label)
                            <option value="{{ $k }}" {{ old('status', $quotation->status) === $k ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2">
                <x-admin.input name="date" label="Quotation Date" type="date"
                               :value="old('date', $quotation->date?->format('Y-m-d'))" required />

                <x-admin.input name="valid_until" label="Valid Until" type="date"
                               :value="old('valid_until', $quotation->valid_until?->format('Y-m-d'))" />
            </div>

            <div>
                <x-admin.textarea name="customer_address" label="Orchard Location / Address"
                                  :value="old('customer_address', $quotation->customer_address)" rows="2" />
            </div>
        </div>

        {{-- Line Items Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 flex-wrap gap-2">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Quotation Items & Cost Breakdown</h3>
                    <p class="text-xs text-gray-500">Add services, machinery hire, and materials.</p>
                </div>
                <button type="button" @click="addItem()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-brand-50 text-brand-700 text-xs font-semibold hover:bg-brand-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Item
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500 border-b border-gray-100">
                        <tr>
                            <th class="py-2.5 px-3 font-semibold w-1/4">Quick Pick / Item Description</th>
                            <th class="py-2.5 px-3 font-semibold w-24">Unit</th>
                            <th class="py-2.5 px-3 font-semibold w-20">Qty</th>
                            <th class="py-2.5 px-3 font-semibold w-28">Rate (₹)</th>
                            <th class="py-2.5 px-3 font-semibold w-24">Disc (₹)</th>
                            <th class="py-2.5 px-3 font-semibold w-20">GST %</th>
                            <th class="py-2.5 px-3 font-semibold text-right w-28">Total (₹)</th>
                            <th class="py-2.5 px-2 text-center w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="hover:bg-gray-50/40">
                                <td class="py-2.5 px-3">
                                    <select :name="'items[' + index + '][product_id]'"
                                            x-model="item.product_id"
                                            @change="onProductChange(index, $event)"
                                            class="w-full text-xs rounded-lg border border-gray-200 py-1.5 px-2 mb-1 text-gray-600 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                        <option value="">-- Choose from Catalogue (or type custom) --</option>
                                        <template x-for="p in products" :key="p.id">
                                            <option :value="p.id" x-text="p.name + ' (₹' + p.rate + '/' + (p.unit || 'unit') + ')'"></option>
                                        </template>
                                    </select>
                                    <input type="text" :name="'items[' + index + '][name]'"
                                           x-model="item.name"
                                           placeholder="e.g. Drone Spraying - 15 Kanals"
                                           required
                                           class="w-full text-sm font-semibold rounded-lg border border-gray-200 py-1.5 px-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                </td>
                                <td class="py-2.5 px-3">
                                    <input type="text" :name="'items[' + index + '][unit]'"
                                           x-model="item.unit"
                                           placeholder="Kanal, Acre, Pcs"
                                           class="w-full text-sm rounded-lg border border-gray-200 py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                </td>
                                <td class="py-2.5 px-3">
                                    <input type="number" step="0.001" :name="'items[' + index + '][qty]'"
                                           x-model.number="item.qty"
                                           min="0.001"
                                           required
                                           class="w-full text-sm rounded-lg border border-gray-200 py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 font-medium">
                                </td>
                                <td class="py-2.5 px-3">
                                    <input type="number" step="0.01" :name="'items[' + index + '][rate]'"
                                           x-model.number="item.rate"
                                           min="0"
                                           required
                                           class="w-full text-sm rounded-lg border border-gray-200 py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 font-medium">
                                </td>
                                <td class="py-2.5 px-3">
                                    <input type="number" step="0.01" :name="'items[' + index + '][discount]'"
                                           x-model.number="item.discount"
                                           min="0"
                                           class="w-full text-sm rounded-lg border border-gray-200 py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                </td>
                                <td class="py-2.5 px-3">
                                    <input type="number" step="0.01" :name="'items[' + index + '][gst_rate]'"
                                           x-model.number="item.gst_rate"
                                           min="0" max="100"
                                           class="w-full text-sm rounded-lg border border-gray-200 py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                </td>
                                <td class="py-2.5 px-3 text-right font-bold text-gray-900 tabular-nums">
                                    ₹<span x-text="formatMoney(lineTotal(item))"></span>
                                </td>
                                <td class="py-2.5 px-2 text-center">
                                    <button type="button" @click="removeItem(index)"
                                            class="p-1 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition"
                                            title="Remove Item">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Totals Summary Box --}}
            <div class="flex justify-end pt-4 border-t border-gray-100">
                <div class="w-80 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Taxable Subtotal</span>
                        <span class="font-medium text-gray-900 tabular-nums">₹<span x-text="formatMoney(subtotal)"></span></span>
                    </div>
                    <div class="flex justify-between text-gray-600" x-show="discountTotal > 0">
                        <span>Discount</span>
                        <span class="font-medium text-red-600 tabular-nums">-₹<span x-text="formatMoney(discountTotal)"></span></span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>GST / Taxes</span>
                        <span class="font-medium text-gray-900 tabular-nums">+₹<span x-text="formatMoney(gstTotal)"></span></span>
                    </div>
                    <div class="flex justify-between pt-2.5 border-t border-gray-200 text-base font-extrabold text-brand-700">
                        <span>Grand Total (INR)</span>
                        <span class="text-xl tabular-nums">₹<span x-text="formatMoney(grandTotal)"></span></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Notes & Terms Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
            <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-100 pb-3">Terms & Conditions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <x-admin.textarea name="terms" label="Quotation Terms" rows="4"
                                  :value="old('terms', $quotation->terms)" helptext="Will be printed on the quotation / proforma invoice PDF." />

                <x-admin.textarea name="notes" label="Internal Notes / Instructions" rows="4"
                                  :value="old('notes', $quotation->notes)" />
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <x-admin.button href="{{ route('admin.quotations.show', $quotation) }}" variant="secondary">
                Cancel
            </x-admin.button>
            <x-admin.button type="submit" variant="primary">
                Update Quotation
            </x-admin.button>
        </div>
    </form>
</div>
@endsection
