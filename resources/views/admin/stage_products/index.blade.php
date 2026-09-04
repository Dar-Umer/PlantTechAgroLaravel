@extends('admin.layout')

@section('page-title', 'Stage Materials — ' . $stage->name)

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Stage Materials</h2>
                <p class="text-sm text-gray-500 mt-1">Default materials for <span class="font-medium text-gray-700">{{ $stage->name }}</span> ({{ $stage->service->name }}). Copied into every new work order of this service.</p>
            </div>
            <x-admin.button href="{{ route('admin.services.stages.index', $stage->service) }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back to Stages</x-admin.button>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-1">Add Material</h3>
            <p class="text-sm text-gray-500 mb-5">Rate and GST are pulled from the product and refreshed when the work order is created.</p>
            <form action="{{ route('admin.stage-products.store', $stage) }}" method="POST" class="flex flex-wrap items-end gap-3">
                @csrf
                <div class="flex-1 min-w-[220px]">
                    <x-admin.select name="product_id" label="Product" :options="$products->mapWithKeys(fn ($p) => [$p->id => $p->name . ($p->sku ? ' (' . $p->sku . ')' : '') . ' — ₹' . number_format((float) $p->rate, 2) . '/' . $p->unit])->all()" placeholder="Select a product" required />
                </div>
                <div>
                    <x-admin.input name="quantity" label="Quantity" type="number" step="0.001" min="0.001" :value="old('quantity', 1)" required />
                </div>
                <div class="flex-1 min-w-[160px]">
                    <x-admin.input name="note" label="Note (optional)" :value="old('note')" placeholder="e.g. per kanal of land" />
                </div>
                <x-admin.button type="submit">Add</x-admin.button>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 font-semibold text-gray-600">Product</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Current Rate</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Default Qty</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Note</th>
                            <th class="px-6 py-3 font-semibold text-gray-600 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($stage->products as $templateProduct)
                            <tr class="hover:bg-gray-50 transition"
                                x-data="{ editing: false, qty: '{{ $templateProduct->quantity }}', note: @js($templateProduct->note) }">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ $templateProduct->product?->name ?? 'Deleted product' }}
                                    @if($templateProduct->product)
                                        <span class="block text-xs text-gray-400">Stock: {{ $templateProduct->product->stock_qty }} {{ $templateProduct->product->unit }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    @if($templateProduct->product)
                                        ₹{{ number_format((float) $templateProduct->product->rate, 2) }} / {{ $templateProduct->product->unit }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-4" x-show="!editing">{{ $templateProduct->quantity }}</td>
                                <td class="px-6 py-4 text-gray-600" x-show="!editing">{{ $templateProduct->note ?: '—' }}</td>
                                <td class="px-6 py-4 text-right" x-show="!editing">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" @click="editing = true" class="text-xs font-medium text-brand-600 hover:text-brand-700 px-2 py-1.5">Edit</button>
                                        <form action="{{ route('admin.stage-products.destroy', [$stage, $templateProduct]) }}" method="POST" onsubmit="return confirm('Remove this material from the stage template?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-700 px-2 py-1.5">Remove</button>
                                        </form>
                                    </div>
                                </td>
                                <td colspan="4" x-show="editing" x-cloak>
                                    <form action="{{ route('admin.stage-products.update', [$stage, $templateProduct]) }}" method="POST" class="flex flex-wrap items-end gap-3">
                                        @csrf
                                        @method('PUT')
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Quantity</label>
                                            <input type="number" name="quantity" step="0.001" min="0.001" x-model="qty" required
                                                   class="w-28 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                        </div>
                                        <div class="flex-1 min-w-[160px]">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Note</label>
                                            <input type="text" name="note" x-model="note"
                                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                        </div>
                                        <x-admin.button type="submit" size="sm">Save</x-admin.button>
                                        <button type="button" @click="editing = false" class="text-xs font-medium text-gray-500 hover:text-gray-700 px-2 py-1.5">Cancel</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <p class="text-sm">No materials in this stage template yet. Add the first one above.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
