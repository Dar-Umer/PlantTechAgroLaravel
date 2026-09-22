@extends('admin.layout')

@section('page-title', 'Batches & Expiry Tracking')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Batches & Expiry Tracking</h2>
                <p class="text-sm text-gray-500 mt-1">Monitor product lot numbers, manufacturing batches, shelf life, and expiration alerts.</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.product-batches.export', request()->query()) }}" class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 shadow-sm transition-colors">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Export CSV
                </a>
                <x-admin.button href="{{ route('admin.stock-movements.create') }}" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>Stock In Batch</x-admin.button>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <span class="text-sm font-medium text-gray-500">Total Batches</span>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalBatches) }}</p>
                <p class="text-xs text-gray-400 mt-1">Recorded lots in system</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <span class="text-sm font-medium text-gray-500">Active Lots</span>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ number_format($activeBatches) }}</p>
                <p class="text-xs text-gray-400 mt-1">Currently in stock & safe</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <span class="text-sm font-medium text-gray-500">Expiring in &lt;60 Days</span>
                <p class="text-2xl font-bold {{ $expiringSoon > 0 ? 'text-amber-600' : 'text-gray-900' }} mt-1">{{ number_format($expiringSoon) }}</p>
                <p class="text-xs text-amber-500 mt-1">Prioritize for usage / dispatch</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <span class="text-sm font-medium text-gray-500">Expired Batches</span>
                <p class="text-2xl font-bold {{ $expiredCount > 0 ? 'text-red-600' : 'text-gray-900' }} mt-1">{{ number_format($expiredCount) }}</p>
                <p class="text-xs text-red-500 mt-1">Requires write-off / disposal</p>
            </div>
        </div>

        {{-- Filter Bar --}}
        <form method="GET" action="{{ route('admin.product-batches.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-3">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search batch #, product or SKU..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                </div>
                <div class="min-w-[180px]">
                    <select name="product_id" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        <option value="">All Products</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="status" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        <option value="">All Statuses</option>
                        @foreach(\App\Models\ProductBatch::STATUSES as $key => $label)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="expiry" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        <option value="">All Expiry Windows</option>
                        <option value="30" {{ request('expiry') == '30' ? 'selected' : '' }}>Expiring in 30 Days</option>
                        <option value="60" {{ request('expiry') == '60' ? 'selected' : '' }}>Expiring in 60 Days</option>
                        <option value="90" {{ request('expiry') == '90' ? 'selected' : '' }}>Expiring in 90 Days</option>
                        <option value="expired" {{ request('expiry') == 'expired' ? 'selected' : '' }}>Already Expired</option>
                    </select>
                </div>
                <button type="submit" class="px-5 py-2.5 bg-gray-900 text-white rounded-xl text-sm font-semibold hover:bg-gray-800 transition-colors">Filter</button>
                @if(request()->anyFilled(['q', 'product_id', 'status', 'expiry', 'supplier_id']))
                    <a href="{{ route('admin.product-batches.index') }}" class="px-3 py-2.5 text-sm text-gray-500 hover:text-gray-700">Clear</a>
                @endif
            </div>
        </form>

        {{-- Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="py-3.5 px-4">Batch Number</th>
                            <th class="py-3.5 px-4">Product</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-right">Current Stock</th>
                            <th class="py-3.5 px-4 text-right">Cost Rate</th>
                            <th class="py-3.5 px-4 text-right">Lot Valuation</th>
                            <th class="py-3.5 px-4 text-right">Initial Qty</th>
                            <th class="py-3.5 px-4">Mfg Date</th>
                            <th class="py-3.5 px-4">Expiry Date</th>
                            <th class="py-3.5 px-4">Shelf Life</th>
                            <th class="py-3.5 px-4">Supplier</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($batches as $batch)
                            <tr class="hover:bg-gray-50/75 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-semibold text-gray-900">
                                    {{ $batch->batch_number }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-gray-900">{{ $batch->product?->name ?? '—' }}</div>
                                    @if($batch->product?->sku)
                                        <div class="text-xs text-gray-400 font-mono">{{ $batch->product->sku }}</div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $badgeStyles = [
                                            'active' => 'bg-green-50 text-green-700 border-green-200',
                                            'near_expiry' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'expired' => 'bg-red-50 text-red-700 border-red-200',
                                            'depleted' => 'bg-gray-100 text-gray-600 border-gray-200',
                                        ];
                                        $style = $badgeStyles[$batch->status] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $style }}">
                                        {{ \App\Models\ProductBatch::STATUSES[$batch->status] ?? ucfirst($batch->status) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right font-semibold {{ (float)$batch->current_qty <= 0 ? 'text-gray-400' : 'text-gray-900' }}">
                                    {{ \App\Support\Format::qty($batch->current_qty) }} <span class="text-xs text-gray-400">{{ $batch->product?->unit }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-medium text-gray-900">
                                    {{ $batch->unit_cost !== null ? '₹' . number_format($batch->unit_cost, 2) : '—' }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-gray-900">
                                    {{ $batch->unit_cost !== null ? '₹' . number_format($batch->batchValuation(), 2) : '—' }}
                                </td>
                                <td class="py-3.5 px-4 text-right text-gray-500">
                                    {{ \App\Support\Format::qty($batch->initial_qty) }} <span class="text-xs text-gray-400">{{ $batch->product?->unit }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-gray-600 text-xs">
                                    {{ $batch->mfg_date ? $batch->mfg_date->format('d M Y') : '—' }}
                                </td>
                                <td class="py-3.5 px-4 text-xs font-medium {{ $batch->isExpired() ? 'text-red-600 font-bold' : ($batch->isNearExpiry() ? 'text-amber-600 font-semibold' : 'text-gray-900') }}">
                                    {{ $batch->expiry_date ? $batch->expiry_date->format('d M Y') : '—' }}
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    @if($batch->expiry_date)
                                        @php $days = $batch->daysUntilExpiry(); @endphp
                                        @if($days < 0)
                                            <span class="text-red-600 font-bold">Expired {{ abs($days) }}d ago</span>
                                        @elseif($days <= 60)
                                            <span class="text-amber-600 font-semibold">{{ $days }} days left</span>
                                        @else
                                            <span class="text-gray-500">{{ $days }} days left</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">No expiry</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-xs text-gray-600">
                                    {{ $batch->supplier?->name ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-gray-400">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    No batches found matching the selected criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($batches->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $batches->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
