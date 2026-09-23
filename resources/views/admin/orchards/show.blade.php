@extends('admin.layout')

@section('page-title', 'Orchard — ' . $orchard->name)

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-700 flex items-center justify-center font-bold text-xl flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h2 class="text-2xl font-bold text-gray-900">{{ $orchard->name }}</h2>
                        <span class="font-mono text-xs font-semibold text-gray-600 bg-gray-100 px-2 py-0.5 rounded">
                            {{ $orchard->orchard_id }}
                        </span>
                        @if($orchard->is_company_established)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Established by Plant Tech Agro
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                Self Registered
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 text-xs text-gray-400 mt-1">
                        <span>Farmer: {{ $orchard->customer?->name ?? 'Unknown' }}</span>
                        <span>·</span>
                        <span class="font-mono text-indigo-700 font-semibold">{{ $orchard->customer?->orchardist_id ?? 'OID-N/A' }}</span>
                        @if($orchard->address)
                            <span>·</span>
                            <span>{{ $orchard->address }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <x-admin.button href="{{ route('admin.orchards.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
                <x-admin.button href="{{ route('admin.work-orders.create', ['customer_id' => $orchard->customer_id, 'orchard_id' => $orchard->id]) }}" variant="secondary">Book Service</x-admin.button>
                <x-admin.button href="{{ route('admin.orchards.edit', $orchard) }}" variant="primary">Edit Orchard</x-admin.button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left column --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Orchard Details Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-lg font-semibold text-gray-900">Orchard Specifications</h3>
                        <a href="{{ route('admin.orchards.edit', $orchard) }}" class="inline-flex items-center text-xs font-medium text-brand-600 hover:text-brand-700">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </a>
                    </div>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Orchard Name</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $orchard->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">System Orchard ID</dt>
                            <dd class="mt-1 text-sm font-mono font-semibold text-gray-900">{{ $orchard->orchard_id }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Cultivated Area</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900">{{ number_format($orchard->area_kanals, 2) }} <span class="font-normal text-gray-500">Kanals</span></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Number of Trees / Plants</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900">{{ number_format($orchard->tree_count) }} <span class="font-normal text-gray-500">Plants</span></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Date of Establishment</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $orchard->date_of_establishment?->format('d M Y') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Calculated Orchard Age</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $orchard->age ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Status</dt>
                            <dd class="mt-1 text-sm font-semibold capitalize text-gray-900">{{ $orchard->status }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">GPS Coordinates</dt>
                            <dd class="mt-1 text-sm font-mono text-gray-900">
                                @if($orchard->latitude && $orchard->longitude)
                                    <span>{{ $orchard->latitude }}, {{ $orchard->longitude }}</span>
                                    <a href="{{ $orchard->google_maps_url }}" target="_blank" rel="noopener noreferrer" class="ml-1 text-xs text-brand-600 hover:underline font-sans font-medium">Open Map ↗</a>
                                @else
                                    <span class="text-gray-400 font-sans">Not recorded</span>
                                @endif
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Address / Location</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $orchard->address ?: '—' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Apple Varieties & Rootstocks</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $orchard->variety_notes ?: 'No specific cultivars logged' }}</dd>
                        </div>
                    </dl>

                    @if($orchard->notes)
                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Internal Notes</h4>
                            <p class="text-sm text-gray-900 whitespace-pre-line">{{ $orchard->notes }}</p>
                        </div>
                    @endif
                </div>

                {{-- Work Orders Table Card matching Work Orders list style --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="text-lg font-semibold text-gray-900">Services & Work Orders</h3>
                        <a href="{{ route('admin.work-orders.create', ['customer_id' => $orchard->customer_id, 'orchard_id' => $orchard->id]) }}" class="text-xs font-medium text-brand-600 hover:text-brand-700">+ Book Service</a>
                    </div>
                    <p class="text-sm text-gray-500 mb-5">History of service requests and field jobs performed on this orchard.</p>

                    @forelse($orchard->workOrders as $wo)
                        <div class="flex items-center gap-4 py-4 {{ !$loop->first ? 'border-t border-gray-100' : '' }}">
                            <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 9.75h4.5m-4.5 3h4.5m-4.5 3h4.5m-5.625 3.75h6.75a4.5 4.5 0 004.5-4.5v-3a4.5 4.5 0 00-4.5-4.5H16.5a3 3 0 00-3-3h-3a3 3 0 00-3 3H7.125a4.5 4.5 0 00-4.5 4.5v3a4.5 4.5 0 004.5 4.5h6.75M12 3h.008v.008H12V3z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('admin.work-orders.show', $wo) }}" class="font-semibold text-brand-600 hover:text-brand-700">
                                        {{ $wo->number }}
                                    </a>
                                    <span class="text-gray-400">·</span>
                                    <span class="font-medium text-gray-900">{{ $wo->service_name }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $wo->status === 'completed' ? 'bg-green-50 text-green-700' : '' }}
                                        {{ $wo->status === 'in_progress' ? 'bg-yellow-50 text-yellow-700' : '' }}
                                        {{ $wo->status === 'assigned' ? 'bg-blue-50 text-blue-700' : '' }}
                                        {{ $wo->status === 'pending' ? 'bg-gray-100 text-gray-600' : '' }}
                                        {{ $wo->status === 'cancelled' ? 'bg-red-50 text-red-700' : '' }}">
                                        {{ \App\Models\WorkOrder::STATUSES[$wo->status] ?? $wo->status }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 text-xs text-gray-500 mt-1">
                                    <span>Agent: {{ $wo->agent?->name ?? 'Unassigned' }}</span>
                                    <span>·</span>
                                    <span>Created {{ $wo->created_at->format('d M Y') }}</span>
                                    @if($wo->invoice)
                                        <span>·</span>
                                        <a href="{{ route('admin.invoices.show', $wo->invoice) }}" class="font-mono text-brand-600 hover:underline">{{ $wo->invoice->number }}</a>
                                    @endif
                                </div>
                            </div>
                            <x-admin.button href="{{ route('admin.work-orders.show', $wo) }}" variant="secondary" size="sm">View</x-admin.button>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-6">No service requests or work orders logged for this orchard yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Right column --}}
            <div class="space-y-6">
                {{-- Orchardist Card matching Customer Info in Work Orders --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Orchardist Information</h3>
                    <p class="text-sm text-gray-500 mb-4">Farmer profile and registered account.</p>

                    @if($orchard->customer)
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl mb-4">
                            <x-admin.avatar :name="$orchard->customer->name" size="sm" :status="$orchard->customer->status" />
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('admin.customers.show', $orchard->customer) }}" class="font-semibold text-gray-900 hover:text-brand-600 block truncate">
                                    {{ $orchard->customer->name }}
                                </a>
                                <p class="text-xs text-gray-500 font-mono">{{ $orchard->customer->phone }}</p>
                            </div>
                        </div>

                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500">Orchardist ID</dt>
                                <dd class="font-mono font-bold text-indigo-700 text-right">{{ $orchard->customer->orchardist_id ?? 'OID-N/A' }}</dd>
                            </div>
                            @if($orchard->customer->email)
                                <div class="flex justify-between gap-3">
                                    <dt class="text-gray-500">Email</dt>
                                    <dd class="text-gray-900 text-right truncate">{{ $orchard->customer->email }}</dd>
                                </div>
                            @endif
                            @if($orchard->customer->area)
                                <div class="flex justify-between gap-3">
                                    <dt class="text-gray-500">Locality</dt>
                                    <dd class="text-gray-900 text-right">{{ $orchard->customer->area }}</dd>
                                </div>
                            @endif
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500">Total Orchards</dt>
                                <dd class="font-semibold text-gray-900 text-right">{{ $orchard->customer->orchards()->count() }}</dd>
                            </div>
                        </dl>

                        <div class="mt-5 pt-4 border-t border-gray-100">
                            <x-admin.button href="{{ route('admin.customers.show', $orchard->customer) }}" variant="secondary" class="w-full">
                                View Customer Profile & Ledger
                            </x-admin.button>
                        </div>
                    @else
                        <p class="text-sm text-gray-400">Customer record not found.</p>
                    @endif
                </div>

                {{-- Provenance Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Plant Tech Agro Provenance</h3>
                    <p class="text-sm text-gray-500 mb-4">Official establishment status.</p>

                    @if($orchard->is_company_established)
                        <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-4 space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">Verified Project</span>
                            </div>
                            <p class="text-sm font-semibold text-emerald-950">Established by Plant Tech Agro</p>
                            <p class="text-xs text-emerald-700 leading-relaxed">
                                This high-density orchard was developed and engineered by Plant Tech Agro. It displays the verified badge on the Customer App.
                            </p>
                        </div>

                        @if($orchard->establishmentWorkOrder)
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <p class="text-xs text-gray-500 font-medium">Original Work Order:</p>
                                <a href="{{ route('admin.work-orders.show', $orchard->establishmentWorkOrder) }}" class="font-mono text-sm font-semibold text-brand-600 hover:underline block mt-0.5">
                                    {{ $orchard->establishmentWorkOrder->number }} ({{ $orchard->establishmentWorkOrder->service_name }})
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="rounded-xl bg-gray-50 border border-gray-100 p-4 space-y-1">
                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Independent Farm</span>
                            <p class="text-sm font-semibold text-gray-900">Self Registered</p>
                            <p class="text-xs text-gray-500">Registered by the customer or staff as an existing orchard.</p>
                        </div>
                    @endif
                </div>

                {{-- Delete action --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Danger Zone</h4>
                    <p class="text-xs text-gray-500 mb-4">Permanently remove this orchard record. This action cannot be reversed.</p>
                    <form method="POST" action="{{ route('admin.orchards.destroy', $orchard) }}" onsubmit="return confirm('Are you sure you want to delete this orchard? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <x-admin.button type="submit" variant="danger" size="sm" class="w-full">
                            Delete Orchard
                        </x-admin.button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
