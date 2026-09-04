@extends('admin.layout')

@section('page-title', 'Lead — ' . $lead->name)

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-900">Lead Details</h2>
                @php
                    $color = \App\Models\Lead::STATUS_COLORS[$lead->status] ?? 'gray';
                    $statusClass = [
                        'blue' => 'bg-blue-50 text-blue-700',
                        'yellow' => 'bg-yellow-50 text-yellow-700',
                        'gray' => 'bg-gray-100 text-gray-600',
                        'purple' => 'bg-purple-50 text-purple-700',
                        'green' => 'bg-green-50 text-green-700',
                        'red' => 'bg-red-50 text-red-700',
                    ][$color];
                @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                    {{ \App\Models\Lead::STATUSES[$lead->status] ?? $lead->status }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <x-admin.button href="{{ route('admin.leads.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
                @if($lead->status !== 'converted')
                    <x-admin.button href="{{ route('admin.leads.convert', $lead) }}" variant="primary">Convert to Customer</x-admin.button>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Details --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-5">Submitted Details</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Name</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $lead->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Phone Number</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900">
                                <a href="tel:{{ $lead->phone }}" class="text-brand-600 hover:text-brand-700">{{ $lead->phone }}</a>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Service Interested In</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($lead->service)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-50 text-brand-700">{{ $lead->service->name }}</span>
                                @else
                                    <span class="text-gray-400">Not specified</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Source</dt>
                            <dd class="mt-1 text-sm text-gray-900 capitalize">{{ $lead->source }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Received</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $lead->created_at->format('d M Y, h:i A') }}</dd>
                        </div>
                        @if($lead->convertedCustomer)
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Converted To</dt>
                                <dd class="mt-1 text-sm">
                                    <a href="{{ route('admin.customers.show', $lead->convertedCustomer) }}" class="text-brand-600 hover:text-brand-700 font-semibold">
                                        {{ $lead->convertedCustomer->name }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                    </dl>

                    @if($lead->custom_fields)
                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Additional Details</h4>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                                @foreach($lead->custom_fields as $key => $value)
                                    @if(!in_array($key, ['name', 'phone', 'service_id']) && !empty($value))
                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ is_array($value) ? implode(', ', $value) : $value }}</dd>
                                        </div>
                                    @endif
                                @endforeach
                            </dl>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Update Status --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Call Status</h3>
                    <p class="text-sm text-gray-500 mb-4">Update after each call attempt.</p>
                    <form action="{{ route('admin.leads.status', $lead) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PATCH')
                        <x-admin.select name="status" label="Status" :options="\App\Models\Lead::STATUSES" :value="$lead->status" />
                        <x-admin.button type="submit" class="w-full">Update Status</x-admin.button>
                    </form>
                </div>

                {{-- Quick Actions --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-3">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Quick Actions</h3>
                    <a href="tel:{{ $lead->phone }}" class="block w-full text-center px-4 py-2.5 rounded-xl bg-green-50 text-green-700 text-sm font-semibold hover:bg-green-100 transition">
                        📞 Call {{ $lead->phone }}
                    </a>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->phone) }}" target="_blank" rel="noopener" class="block w-full text-center px-4 py-2.5 rounded-xl bg-emerald-50 text-emerald-700 text-sm font-semibold hover:bg-emerald-100 transition">
                        💬 WhatsApp
                    </a>
                    <x-admin.button href="{{ route('admin.leads.edit', $lead) }}" variant="secondary" class="w-full">Edit Lead Details</x-admin.button>
                </div>
            </div>
        </div>
    </div>
@endsection
