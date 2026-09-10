@extends('admin.layout')

@section('page-title', $movement->reference ?: 'Stock Movement #'.$movement->id)

@section('content')
    @php
        $before = $movement->stockBefore();
        $delta = (float) $movement->quantity;
        $typeClass = ['in' => 'bg-green-50 text-green-700', 'out' => 'bg-red-50 text-red-700', 'adjustment' => 'bg-blue-50 text-blue-700'][$movement->type] ?? 'bg-gray-100 text-gray-600';
        $revType = match ($movement->type) {
            'in' => 'out',
            'out' => 'in',
            default => 'adjustment',
        };
        $revQty = $movement->type === 'adjustment'
            ? $before
            : abs((float) $movement->quantity);
        $revUrl = route('admin.stock-movements.create', [
            'product_id' => $movement->product_id,
            'type' => $revType,
            'qty' => $revQty,
            'note' => 'Reversal of '.($movement->reference ?: '#'.$movement->id),
        ]);
    @endphp

    <div class="space-y-6 max-w-3xl">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-900">{{ $movement->reference ?: 'Movement #'.$movement->id }}</h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeClass }}">
                    {{ \App\Models\StockMovement::TYPES[$movement->type] ?? $movement->type }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <x-admin.button href="{{ $revUrl }}" variant="secondary" size="sm" icon='<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>'>Reverse</x-admin.button>
                <x-admin.button href="{{ route('admin.stock-movements.create') }}" variant="primary" size="sm" icon='<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>Record Movement</x-admin.button>
                <x-admin.button href="{{ route('admin.stock-movements.index') }}" variant="secondary" size="sm" icon='<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
            </div>
        </div>

        {{-- Product --}}
        @if($movement->product)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <p class="text-sm font-medium text-gray-400 mb-3">Product</p>
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="min-w-0">
                        <p class="text-lg font-semibold text-gray-900">{{ $movement->product->name }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">
                            @if($movement->product->sku)SKU: {{ $movement->product->sku }} · @endif
                            Unit: {{ $movement->product->unit }} · Rate: ₹{{ number_format((float) $movement->product->rate, 0) }}
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        @php $low = $movement->product->isLowStock(); @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $low ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700' }}">
                            {{ $low ? 'Low stock' : 'In stock' }} · {{ \App\Support\Format::qty($movement->product->stock_qty) }} {{ $movement->product->unit }}
                        </span>
                        <p class="text-xs text-gray-400 mt-1.5">Current level ({{ $movement->created_at->diffForHumans() }})</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('admin.products.edit', $movement->product) }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">View product →</a>
                </div>
            </div>
        @endif

        {{-- Change --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <p class="text-sm font-medium text-gray-400 mb-4">Stock Change</p>
            <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="text-center">
                    <p class="text-xs text-gray-400 mb-1">Before</p>
                    <p class="text-xl font-semibold text-gray-500">{{ \App\Support\Format::qty($before) }} {{ $movement->product?->unit }}</p>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2 min-w-0">
                    <span class="text-2xl font-bold {{ $delta >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $delta >= 0 ? '+' : '' }}{{ \App\Support\Format::qty($delta) }} {{ $movement->product?->unit }}
                    </span>
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $typeClass }}">
                        {{ $movement->type === 'adjustment' ? 'Adjusted to counted level' : (\App\Models\StockMovement::TYPES[$movement->type] ?? $movement->type) }}
                    </span>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-400 mb-1">After</p>
                    <p class="text-xl font-semibold text-gray-900">{{ \App\Support\Format::qty($movement->stock_after) }} {{ $movement->product?->unit }}</p>
                </div>
            </div>
            <div class="mt-5 h-2.5 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full {{ $delta >= 0 ? 'bg-green-500' : 'bg-red-500' }} transition-all" style="width: {{ $before <= 0 ? ($delta >= 0 ? '100%' : '0%') : min(100, max(8, abs($delta) / max(abs($before), 1) * 100)) }}%"></div>
            </div>
        </div>

        {{-- Details grid --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <p class="text-sm font-medium text-gray-400 mb-4">Details</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <div>
                    <p class="text-gray-400 text-xs mb-0.5">Reference</p>
                    @if($movement->reference)
                        <p class="font-mono font-medium text-gray-900">{{ $movement->reference }}</p>
                    @else
                        <p class="text-gray-300">—</p>
                    @endif
                </div>
                <div>
                    <p class="text-gray-400 text-xs mb-0.5">Recorded by</p>
                    <p class="font-medium text-gray-900">{{ $movement->createdBy?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs mb-0.5">Date & Time</p>
                    <p class="font-medium text-gray-900">{{ $movement->created_at->format('d M Y, h:i A') }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs mb-0.5">Last updated</p>
                    <p class="font-medium text-gray-900">{{ $movement->updated_at->format('d M Y, h:i A') }}</p>
                </div>
            </div>

            @if($movement->note)
                <div class="mt-5 p-4 rounded-xl bg-gray-50 border border-gray-100">
                    <p class="text-gray-400 text-xs mb-1">Note</p>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $movement->note }}</p>
                </div>
            @endif
        </div>

        {{-- Cost & supplier (purchases) --}}
        @if($movement->withUnitCost() || $movement->supplier)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <p class="text-sm font-medium text-gray-400 mb-4">Cost & Supplier</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    @if($movement->withUnitCost())
                        <div>
                            <p class="text-gray-400 text-xs mb-0.5">Unit Cost</p>
                            <p class="font-semibold text-gray-900">₹{{ number_format((float) $movement->unit_cost, 0) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-0.5">Quantity</p>
                            <p class="font-semibold text-gray-900">{{ \App\Support\Format::qty(abs((float) $movement->quantity)) }} {{ $movement->product?->unit }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-0.5">Total Value</p>
                            <p class="font-bold text-brand-700">₹{{ number_format($movement->movementValue(), 0) }}</p>
                        </div>
                    @else
                        <div class="col-span-3">
                            <p class="text-gray-400 text-xs mb-0.5">Unit Cost</p>
                            <p class="text-gray-300">Not recorded</p>
                        </div>
                    @endif
                </div>

                @if($movement->supplier)
                    <div class="mt-5 p-4 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-between flex-wrap gap-3">
                        <div>
                            <p class="text-gray-400 text-xs mb-0.5">Supplier</p>
                            <p class="font-medium text-gray-900">{{ $movement->supplier->name }}</p>
                            @if($movement->supplier->contact_person)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $movement->supplier->contact_person }}</p>
                            @endif
                        </div>
                        <div class="text-sm text-gray-500">
                            @if($movement->supplier->phone)
                                <p>{{ $movement->supplier->phone }}</p>
                            @endif
                            @if($movement->supplier->email)
                                <p class="text-xs">{{ $movement->supplier->email }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection