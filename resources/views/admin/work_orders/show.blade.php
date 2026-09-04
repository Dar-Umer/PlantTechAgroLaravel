@extends('admin.layout')

@section('page-title', 'Work Order ' . $workOrder->number)

@section('content')
    <div class="space-y-6">
        @php
            $color = \App\Models\WorkOrder::STATUS_COLORS[$workOrder->status] ?? 'gray';
            $statusClass = ['gray' => 'bg-gray-100 text-gray-600', 'blue' => 'bg-blue-50 text-blue-700', 'yellow' => 'bg-yellow-50 text-yellow-700', 'green' => 'bg-green-50 text-green-700', 'red' => 'bg-red-50 text-red-700'][$color];
        @endphp

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-900">{{ $workOrder->number }}</h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                    {{ \App\Models\WorkOrder::STATUSES[$workOrder->status] ?? $workOrder->status }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <x-admin.button href="{{ route('admin.work-orders.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>
                    Back
                </x-admin.button>
                @if(! $workOrder->isCancelled() && $workOrder->status !== 'completed')
                    <form action="{{ route('admin.work-orders.cancel', $workOrder) }}" method="POST" onsubmit="return confirm('Cancel this work order?')">
                        @csrf
                        @method('PATCH')
                        <x-admin.button type="submit" variant="danger">Cancel Order</x-admin.button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Summary --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Customer</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900">
                            @if($workOrder->customer)
                                <a href="{{ route('admin.customers.show', $workOrder->customer) }}" class="hover:text-brand-600">{{ $workOrder->customer_name }}</a>
                            @else
                                {{ $workOrder->customer_name }}
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Service</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $workOrder->service_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Assigned Agent</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $workOrder->agent?->name ?? 'Unassigned' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Started / Completed</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $workOrder->started_at?->format('d M Y') ?? '—' }} → {{ $workOrder->completed_at?->format('d M Y') ?? '—' }}
                        </dd>
                    </div>
                </dl>
                @if($workOrder->notes)
                    <dl class="mt-5 pt-5 border-t border-gray-100">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Notes</dt>
                        <dd class="text-sm text-gray-700 whitespace-pre-line">{{ $workOrder->notes }}</dd>
                    </dl>
                @endif
            </div>

            <div class="space-y-6">
                {{-- Assign --}}
                @if(! $workOrder->isCancelled() && $workOrder->status !== 'completed')
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Field Agent</h3>
                        <p class="text-sm text-gray-500 mb-4">Assign or reassign the responsible agent.</p>
                        <form action="{{ route('admin.work-orders.assign', $workOrder) }}" method="POST" class="space-y-4">
                            @csrf
                            @method('PATCH')
                            <x-admin.select name="assigned_agent_id" label="Agent" :options="$agents->pluck('name', 'id')->all()" :value="(string) $workOrder->assigned_agent_id" required />
                            <x-admin.button type="submit" class="w-full">Assign</x-admin.button>
                        </form>
                    </div>
                @endif

                {{-- Invoice --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Invoice</h3>
                    <p class="text-sm text-gray-500 mb-4">Generated from recorded material usage.</p>
                    @if($workOrder->invoice)
                        <div class="rounded-xl bg-gray-50 border border-gray-100 p-4 mb-4">
                            <p class="text-sm font-semibold text-gray-900">{{ $workOrder->invoice->number }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">Grand total ₹{{ number_format((float) $workOrder->invoice->grand_total, 2) }} · {{ \App\Models\Invoice::STATUSES[$workOrder->invoice->status] }}</p>
                        </div>
                        <x-admin.button href="{{ route('admin.invoices.show', $workOrder->invoice) }}" variant="secondary" class="w-full">
                            View Invoice
                        </x-admin.button>
                    @else
                        <div class="rounded-xl bg-gray-50 border border-gray-100 p-4 mb-4 text-sm text-gray-600">
                            Materials recorded: <span class="font-semibold">₹{{ number_format($materialTotal, 2) }}</span> + GST ₹{{ number_format($materialGst, 2) }}
                        </div>
                        @if($materialTotal > 0 && ! $workOrder->isCancelled())
                            <form action="{{ route('admin.work-orders.invoice', $workOrder) }}" method="POST" onsubmit="return confirm('Generate invoice for ₹{{ number_format($materialTotal + $materialGst, 2) }}?')">
                                @csrf
                                <x-admin.button type="submit" class="w-full">Generate Invoice</x-admin.button>
                            </form>
                        @else
                            <p class="text-xs text-gray-400">Record materials in stages to generate an invoice.</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- Stages --}}
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900">Stages & Materials</h3>

            @forelse($workOrder->stages as $index => $stage)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-5 flex flex-wrap items-start gap-4">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0 {{ $stage->status === 'completed' ? 'bg-green-600 text-white' : ($stage->status === 'skipped' ? 'bg-gray-300 text-gray-600' : 'bg-brand-600 text-white') }}">
                            @if($stage->status === 'completed') ✓ @elseif($stage->status === 'skipped') – @else {{ $index + 1 }} @endif
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="font-semibold text-gray-900">{{ $stage->name }}</h4>
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $stage->status === 'completed' ? 'bg-green-50 text-green-700' : ($stage->status === 'skipped' ? 'bg-gray-100 text-gray-600' : ($stage->status === 'in_progress' ? 'bg-yellow-50 text-yellow-700' : 'bg-gray-100 text-gray-500')) }}">
                                    {{ \App\Models\WorkOrderStage::STATUSES[$stage->status] ?? $stage->status }}
                                </span>
                                @if($stage->requires_photo)
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">📷 Photo (min {{ $stage->min_photos }})</span>
                                @endif
                                @if($stage->requires_pdf)
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-purple-50 text-purple-700">📄 PDF</span>
                                @endif
                            </div>
                            @if($stage->description)
                                <p class="text-sm text-gray-500 mt-1">{{ $stage->description }}</p>
                            @endif
                            @if($stage->completed_at)
                                <p class="text-xs text-gray-400 mt-1">Completed {{ $stage->completed_at->format('d M Y, h:i A') }}</p>
                            @endif
                            @if($stage->notes)
                                <p class="text-xs text-gray-500 mt-1 italic">{{ $stage->notes }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2" x-data="{ completing: false }">
                            @if(! $workOrder->isCancelled() && ! in_array($stage->status, ['completed', 'skipped']))
                                <button type="button" @click="completing = !completing" class="inline-flex items-center justify-center font-semibold transition text-sm rounded-xl shadow-sm px-3.5 py-1.5 text-xs bg-brand-600 text-white hover:bg-brand-700">
                                    Mark Complete
                                </button>
                                <form action="{{ route('admin.work-orders.stages.skip', [$workOrder, $stage]) }}" method="POST" onsubmit="return confirm('Skip this stage?')">
                                    @csrf
                                    @method('PATCH')
                                    <x-admin.button type="submit" variant="secondary" size="sm">Skip</x-admin.button>
                                </form>
                            @endif
                        </div>
                    </div>

                    @if(! $workOrder->isCancelled() && ! in_array($stage->status, ['completed', 'skipped']) )
                        <form action="{{ route('admin.work-orders.stages.complete', [$workOrder, $stage]) }}" method="POST" x-show="completing" x-cloak class="px-5 pb-4">
                            @csrf
                            @method('PATCH')
                            <x-admin.textarea name="notes" label="Completion Notes (optional)" rows="2" placeholder="What was done, observations, next steps..." />
                            <div class="mt-3 flex justify-end">
                                <x-admin.button type="submit">Confirm Completion</x-admin.button>
                            </div>
                        </form>
                    @endif

                    {{-- Materials --}}
                    <div class="border-t border-gray-100 bg-gray-50/50 px-5 py-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Materials Used</p>
                        @if($stage->products->isNotEmpty())
                            <div class="space-y-2 mb-3">
                                @foreach($stage->products as $row)
                                    <div class="flex flex-wrap items-center gap-2 text-sm bg-white rounded-xl border border-gray-100 px-3 py-2"
                                         x-data="{ editing: false, qty: '{{ $row->quantity }}', rate: '{{ $row->rate }}' }">
                                        <div class="flex-1 min-w-[140px]">
                                            <span class="font-medium text-gray-900">{{ $row->name }}</span>
                                            <span class="text-xs text-gray-400">· GST {{ $row->gst_rate }}%</span>
                                        </div>
                                        <span class="text-gray-600" x-show="!editing">
                                            {{ $row->quantity }} {{ $row->unit }} × ₹{{ number_format((float) $row->rate, 2) }}
                                            <span class="font-semibold text-gray-900">= ₹{{ number_format($row->lineTotal(), 2) }}</span>
                                        </span>
                                        <form action="{{ route('admin.work-orders.stages.products.update', [$workOrder, $stage, $row]) }}" method="POST" x-show="editing" x-cloak class="flex flex-wrap items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="number" name="quantity" step="0.001" min="0.001" x-model="qty" class="w-24 rounded-lg border border-gray-200 bg-gray-50 px-2 py-1.5 text-sm" required>
                                            <input type="number" name="rate" step="0.01" min="0" x-model="rate" class="w-28 rounded-lg border border-gray-200 bg-gray-50 px-2 py-1.5 text-sm" required>
                                            <x-admin.button type="submit" size="sm">Save</x-admin.button>
                                        </form>
                                        <div class="flex items-center gap-1">
                                            @if(! $workOrder->isCancelled())
                                                <button type="button" @click="editing = !editing" class="text-xs font-medium text-brand-600 hover:text-brand-700 px-2" x-show="!editing" x-text="editing ? 'Cancel' : 'Edit'">Edit</button>
                                                <form action="{{ route('admin.work-orders.stages.products.destroy', [$workOrder, $stage, $row]) }}" method="POST" onsubmit="return confirm('Remove this material and restore stock?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-700 px-2">Remove</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-400 mb-3">No materials recorded for this stage.</p>
                        @endif

                        @if(! $workOrder->isCancelled())
                            <form action="{{ route('admin.work-orders.stages.products.store', [$workOrder, $stage]) }}" method="POST" class="flex flex-wrap items-end gap-2">
                                @csrf
                                <div class="flex-1 min-w-[180px]">
                                    <select name="product_id" required class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                        <option value="">Add material…</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-rate="{{ $product->rate }}">{{ $product->name }} ({{ $product->stock_qty }} {{ $product->unit }} in stock)</option>
                                        @endforeach
                                    </select>
                                </div>
                                <input type="number" name="quantity" step="0.001" min="0.001" placeholder="Qty" required
                                       class="w-24 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                <input type="number" name="rate" step="0.01" min="0" placeholder="Rate ₹"
                                       class="w-28 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                <x-admin.button type="submit" size="sm">Add & Deduct Stock</x-admin.button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center text-gray-500">
                    <p class="text-sm">This work order has no stages.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
