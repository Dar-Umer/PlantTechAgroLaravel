@extends('admin.layout')

@section('page-title', 'Work Order ' . $workOrder->number)

@section('content')
    @php
        $color = \App\Models\WorkOrder::STATUS_COLORS[$workOrder->status] ?? 'gray';
        $statusClass = ['gray' => 'bg-gray-100 text-gray-600', 'blue' => 'bg-blue-50 text-blue-700', 'yellow' => 'bg-yellow-50 text-yellow-700', 'green' => 'bg-green-50 text-green-700', 'red' => 'bg-red-50 text-red-700'][$color];
        $totalStages = $workOrder->stages->count();
        $completedStages = $workOrder->stages->whereIn('status', ['completed', 'skipped'])->count();
        $progressPct = $totalStages > 0 ? round(($completedStages / $totalStages) * 100) : 0;
    @endphp

    <div class="space-y-6">
        {{-- Header --}}
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
                    <form action="{{ route('admin.work-orders.cancel', $workOrder) }}" method="POST" onsubmit="return confirm('Cancel this work order? All remaining stages will be abandoned.')">
                        @csrf
                        @method('PATCH')
                        <x-admin.button type="submit" variant="danger">Cancel</x-admin.button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Progress Bar --}}
        @if(! $workOrder->isCancelled() && $totalStages > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-gray-900">{{ $completedStages }} of {{ $totalStages }} stages completed</span>
                    <span class="text-sm font-bold {{ $progressPct === 100 ? 'text-green-600' : 'text-brand-600' }}">{{ $progressPct }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2.5">
                    <div class="h-2.5 rounded-full transition-all duration-500 {{ $progressPct === 100 ? 'bg-green-500' : 'bg-brand-500' }}"
                         style="width: {{ $progressPct }}%"></div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Stages checklist --}}
            <div class="lg:col-span-2 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Stages</h3>
                    @if($totalStages > 0)
                        <span class="text-xs text-gray-500">{{ $completedStages }} / {{ $totalStages }} done</span>
                    @endif
                </div>

                @forelse($workOrder->stages as $index => $stage)
                    @php
                        $stageStatusClass = match($stage->status) {
                            'completed' => 'bg-green-50 text-green-700',
                            'skipped' => 'bg-gray-100 text-gray-600',
                            'in_progress' => 'bg-yellow-50 text-yellow-700',
                            default => 'bg-gray-100 text-gray-500',
                        };
                        $stageIconClass = match($stage->status) {
                            'completed' => 'bg-green-600 text-white',
                            'skipped' => 'bg-gray-300 text-gray-600',
                            'in_progress' => 'bg-yellow-500 text-white',
                            default => 'bg-gray-200 text-gray-600',
                        };
                    @endphp

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ open: false, completing: false }">
                        {{-- Checklist row --}}
                        <button type="button" @click="open = !open" class="w-full flex items-center gap-3 sm:gap-4 px-5 py-4 text-left hover:bg-gray-50 transition">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs flex-shrink-0 {{ $stageIconClass }}">
                                @if($stage->status === 'completed')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                @elseif($stage->status === 'skipped')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M18 12H6"/></svg>
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-gray-900">{{ $stage->name }}</span>
                                    @if($stage->requires_photo)
                                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">📷 min {{ $stage->min_photos }}</span>
                                    @endif
                                    @if($stage->requires_pdf)
                                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-purple-50 text-purple-700">📄 PDF</span>
                                    @endif
                                </div>
                                @if($stage->description)
                                    <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $stage->description }}</p>
                                @elseif($stage->completed_at)
                                    <p class="text-xs text-gray-400 mt-0.5">Completed {{ $stage->completed_at->format('d M Y, h:i A') }}</p>
                                @endif
                            </div>

                            <span class="text-xs px-2.5 py-0.5 rounded-full flex-shrink-0 {{ $stageStatusClass }}">
                                {{ \App\Models\WorkOrderStage::STATUSES[$stage->status] ?? $stage->status }}
                            </span>

                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        {{-- Expanded details --}}
                        <div x-show="open" x-cloak x-collapse>
                            <div class="border-t border-gray-100 px-5 py-5 space-y-5">
                                {{-- Notes / details --}}
                                @if($stage->description && $stage->notes)
                                    <div class="text-sm text-gray-600 space-y-1">
                                        <p>{{ $stage->description }}</p>
                                        <p class="italic text-gray-500">"{{ $stage->notes }}"</p>
                                    </div>
                                @elseif($stage->description)
                                    <p class="text-sm text-gray-600">{{ $stage->description }}</p>
                                @elseif($stage->notes)
                                    <p class="text-sm text-gray-600 italic">"{{ $stage->notes }}"</p>
                                @endif

                                {{-- Attachments --}}
                                @if($stage->attachments->isNotEmpty())
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Attachments</p>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            @foreach($stage->attachments as $attachment)
                                                @if($attachment->type === 'photo')
                                                    <div class="relative group">
                                                        <a href="{{ asset('storage/'.$attachment->file_path) }}" target="_blank" class="block">
                                                            <img src="{{ asset('storage/'.$attachment->file_path) }}" alt="{{ $attachment->original_name }}"
                                                                 class="w-16 h-16 object-cover rounded-lg border border-gray-200 hover:ring-2 hover:ring-brand-200 transition">
                                                        </a>
                                                        @if(! $workOrder->isCancelled())
                                                            <form action="{{ route('admin.work-orders.stages.attachments.destroy', [$workOrder, $stage, $attachment]) }}" method="POST" class="absolute -top-1.5 -right-1.5" onsubmit="return confirm('Remove this photo?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-[10px] shadow hover:bg-red-600 transition">
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div class="relative group">
                                                        <a href="{{ asset('storage/'.$attachment->file_path) }}" target="_blank"
                                                           class="inline-flex items-center gap-1.5 text-xs font-medium text-purple-700 bg-purple-50 hover:bg-purple-100 px-3 py-1.5 rounded-lg transition">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                            {{ $attachment->original_name }}
                                                        </a>
                                                        @if(! $workOrder->isCancelled())
                                                            <form action="{{ route('admin.work-orders.stages.attachments.destroy', [$workOrder, $stage, $attachment]) }}" method="POST" class="absolute -top-1.5 -right-1.5" onsubmit="return confirm('Remove this PDF?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-[10px] shadow hover:bg-red-600 transition">
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Materials --}}
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Materials</p>
                                        @if($stage->products->isNotEmpty())
                                            <span class="text-xs text-gray-500">₹{{ number_format($stage->products->sum(fn ($p) => $p->lineTotal()), 0) }}</span>
                                        @endif
                                    </div>

                                    @if($stage->products->isNotEmpty())
                                        <div class="space-y-2 mb-3">
                                            @foreach($stage->products as $row)
                                                <div class="flex items-center justify-between gap-3 rounded-xl bg-gray-50 border border-gray-100 px-4 py-2.5"
                                                     x-data="{ editing: false, qty: '{{ $row->quantity }}', rate: '{{ $row->rate }}' }">
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $row->name }}</p>
                                                        <div x-show="!editing" class="text-xs text-gray-500">
                                                            {{ $row->quantity }} {{ $row->unit }} × ₹{{ number_format((float) $row->rate, 0) }}
                                                            <span class="font-semibold text-gray-900">= ₹{{ number_format($row->lineTotal(), 0) }}</span>
                                                        </div>
                                                    </div>

                                                    {{-- Edit form --}}
                                                    <div x-show="editing" x-cloak class="flex flex-wrap items-end gap-2">
                                                        <form action="{{ route('admin.work-orders.stages.products.update', [$workOrder, $stage, $row]) }}" method="POST" class="flex flex-wrap items-end gap-2">
                                                            @csrf
                                                            @method('PATCH')
                                                            <div>
                                                                <label class="text-[11px] text-gray-500 mb-1 block">Qty</label>
                                                                <input type="number" name="quantity" step="0.001" min="0.001" x-model="qty" class="w-20 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-sm" required>
                                                            </div>
                                                            <div>
                                                                <label class="text-[11px] text-gray-500 mb-1 block">Rate (₹)</label>
                                                                <input type="number" name="rate" step="1" min="0" x-model="rate" class="w-24 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-sm" required>
                                                            </div>
                                                            <x-admin.button type="submit" size="sm">Save</x-admin.button>
                                                        </form>
                                                        <button type="button" @click="editing = false" class="text-xs font-medium text-gray-500 hover:text-gray-700 px-2 py-1.5">Cancel</button>
                                                    </div>

                                                    {{-- Actions --}}
                                                    <div class="flex items-center gap-1 flex-shrink-0" x-show="!editing">
                                                        @if(! $workOrder->isCancelled())
                                                            <button type="button" @click="editing = !editing" class="text-xs font-medium text-brand-600 hover:text-brand-700 px-2 py-1.5">Edit</button>
                                                            <form action="{{ route('admin.work-orders.stages.products.destroy', [$workOrder, $stage, $row]) }}" method="POST" onsubmit="return confirm('Remove this material and restore stock?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-700 px-2 py-1.5">Remove</button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-400 mb-3">No materials recorded for this stage.</p>
                                    @endif

                                    {{-- Add material --}}
                                    @if(! $workOrder->isCancelled())
                                        <form action="{{ route('admin.work-orders.stages.products.store', [$workOrder, $stage]) }}" method="POST" class="flex flex-wrap items-end gap-2 bg-gray-50 rounded-xl border border-gray-100 p-3">
                                            @csrf
                                            <div class="flex-1 min-w-[160px]">
                                                <label class="text-[11px] text-gray-500 mb-1 block">Add material</label>
                                                <select name="product_id" required class="w-full rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                                    <option value="">Select product...</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}" data-rate="{{ $product->rate }}">{{ $product->name }} ({{ $product->stock_qty }} {{ $product->unit }} in stock)</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="text-[11px] text-gray-500 mb-1 block">Qty</label>
                                                <input type="number" name="quantity" step="0.001" min="0.001" placeholder="Qty" required
                                                       class="w-20 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                            </div>
                                            <div>
                                                <label class="text-[11px] text-gray-500 mb-1 block">Rate ₹</label>
                                                <input type="number" name="rate" step="1" min="0" placeholder="Rate"
                                                       class="w-24 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                            </div>
                                            <x-admin.button type="submit" size="sm">Add & Deduct</x-admin.button>
                                        </form>
                                    @endif
                                </div>

                                {{-- Stage actions --}}
                                @if(! $workOrder->isCancelled() && ! in_array($stage->status, ['completed', 'skipped']))
                                    <div class="flex items-center gap-2 pt-4 border-t border-gray-100">
                                        <x-admin.button type="button" size="sm" @click="completing = !completing" icon='<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'>
                                            Mark Complete
                                        </x-admin.button>
                                        <form action="{{ route('admin.work-orders.stages.skip', [$workOrder, $stage]) }}" method="POST" onsubmit="return confirm('Skip this stage?')">
                                            @csrf
                                            @method('PATCH')
                                            <x-admin.button type="submit" variant="ghost" size="sm">Skip</x-admin.button>
                                        </form>
                                    </div>
                                @endif

                                {{-- Completion form --}}
                                @if(! $workOrder->isCancelled() && ! in_array($stage->status, ['completed', 'skipped']))
                                    <div x-show="completing" x-cloak x-collapse class="bg-gray-50 rounded-xl border border-gray-100 p-4">
                                        <form action="{{ route('admin.work-orders.stages.complete', [$workOrder, $stage]) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PATCH')
                                            <x-admin.textarea name="notes" label="Completion Notes (optional)" rows="2" placeholder="What was done, observations, next steps..." />

                                            @if($stage->requires_photo)
                                                <div class="mt-4">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                                        Photos <span class="text-red-500">*</span>
                                                        <span class="text-xs font-normal text-gray-400">(minimum {{ $stage->min_photos }})</span>
                                                    </label>
                                                    <input type="file" name="photos[]" accept="image/*" multiple class="block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                                                    <div id="photo-preview-{{ $stage->id }}" class="mt-2 grid grid-cols-3 gap-2 sm:grid-cols-4"></div>
                                                </div>
                                            @endif

                                            @if($stage->requires_pdf)
                                                <div class="mt-4">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">PDF Document <span class="text-red-500">*</span></label>
                                                    <input type="file" name="pdf" accept="application/pdf" class="block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                                                </div>
                                            @endif

                                            <div class="mt-3 flex justify-end gap-2">
                                                <x-admin.button type="button" variant="secondary" size="sm" @click="completing = false">Cancel</x-admin.button>
                                                <x-admin.button type="submit">Confirm Completion</x-admin.button>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center text-gray-500">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <p class="text-sm">This work order has no stages.</p>
                    </div>
                @endforelse
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Order Info --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Order Info</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500">Customer</dt>
                            <dd class="font-medium text-gray-900 text-right">
                                @if($workOrder->customer)
                                    <a href="{{ route('admin.customers.show', $workOrder->customer) }}" class="hover:text-brand-600">{{ $workOrder->customer_name }}</a>
                                @else
                                    {{ $workOrder->customer_name }}
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500">Service</dt>
                            <dd class="font-medium text-gray-900 text-right">{{ $workOrder->service_name }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500">Timeline</dt>
                            <dd class="text-gray-900 text-right">
                                @if($workOrder->started_at || $workOrder->completed_at)
                                    {{ $workOrder->started_at?->format('d M') ?? '—' }} → {{ $workOrder->completed_at?->format('d M Y') ?? 'now' }}
                                @else
                                    Not started
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500">Created</dt>
                            <dd class="text-gray-900 text-right">{{ $workOrder->created_at->format('d M Y, h:i A') }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500">Created By</dt>
                            <dd class="text-gray-900 text-right">{{ $workOrder->createdBy?->name ?? '—' }}</dd>
                        </div>
                    </dl>

                    {{-- Assigned agent + reassign --}}
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 mb-2">Assigned Agent</p>
                        @if($workOrder->agent)
                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-bold">{{ strtoupper(substr($workOrder->agent->name, 0, 1)) }}</div>
                                <span class="text-sm font-semibold text-gray-900">{{ $workOrder->agent->name }}</span>
                            </div>
                        @else
                            <p class="text-sm text-gray-400 mb-3">Unassigned</p>
                        @endif

                        @if(! $workOrder->isCancelled() && $workOrder->status !== 'completed')
                            <form action="{{ route('admin.work-orders.assign', $workOrder) }}" method="POST" class="space-y-2">
                                @csrf
                                @method('PATCH')
                                <x-admin.select name="assigned_agent_id" label="" :options="$agents->pluck('name', 'id')->all()" :value="(string) $workOrder->assigned_agent_id" placeholder="Assign agent..." />
                                <x-admin.button type="submit" size="sm" class="w-full">Assign / Reassign</x-admin.button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Invoice --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Invoice</h3>
                    <p class="text-sm text-gray-500 mb-4">Generated from recorded material usage.</p>

                    @if($workOrder->invoice)
                        <div class="rounded-xl bg-gray-50 border border-gray-100 p-4 mb-4">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-sm font-semibold text-gray-900">{{ $workOrder->invoice->number }}</p>
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $workOrder->invoice->status === 'paid' ? 'bg-green-50 text-green-700' : 'bg-yellow-50 text-yellow-700' }}">
                                    {{ \App\Models\Invoice::STATUSES[$workOrder->invoice->status] }}
                                </span>
                            </div>
                            <p class="text-lg font-bold text-gray-900">₹{{ number_format((float) $workOrder->invoice->grand_total, 0) }}</p>
                        </div>
                        <x-admin.button href="{{ route('admin.invoices.show', $workOrder->invoice) }}" variant="secondary" class="w-full">View Invoice</x-admin.button>
                    @else
                        <div class="rounded-xl bg-gray-50 border border-gray-100 p-4 mb-4 space-y-1">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Materials</span>
                                <span class="font-semibold text-gray-900">₹{{ number_format($materialTotal, 0) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">GST</span>
                                <span class="font-semibold text-gray-900">₹{{ number_format($materialGst, 0) }}</span>
                            </div>
                            <div class="border-t border-gray-200 mt-1 pt-1 flex items-center justify-between">
                                <span class="text-sm font-semibold text-gray-900">Total</span>
                                <span class="text-lg font-bold text-gray-900">₹{{ number_format($materialTotal + $materialGst, 0) }}</span>
                            </div>
                        </div>
                        @if($materialTotal > 0 && ! $workOrder->isCancelled())
                            <form action="{{ route('admin.work-orders.invoice', $workOrder) }}" method="POST" onsubmit="return confirm('Generate invoice for ₹{{ number_format($materialTotal + $materialGst, 0) }}?')">
                                @csrf
                                <x-admin.button type="submit" class="w-full">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                                    Generate Invoice
                                </x-admin.button>
                            </form>
                        @else
                            <p class="text-xs text-gray-400 text-center py-2">Record materials in stages to generate an invoice.</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('input[type="file"][name="photos[]"]').forEach(function (input) {
                input.addEventListener('change', function () {
                    var form = this.closest('form');
                    if (!form) return;
                    var preview = form.querySelector('[id^="photo-preview-"]');
                    if (!preview) return;
                    preview.innerHTML = '';
                    Array.from(this.files).forEach(function (file) {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            var img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'w-full h-20 object-cover rounded-lg border border-gray-200';
                            var wrap = document.createElement('div');
                            wrap.appendChild(img);
                            preview.appendChild(wrap);
                        };
                        reader.readAsDataURL(file);
                    });
                });
            });
        </script>
    @endpush
@endsection