@extends('admin.layout')

@section('page-title', 'Lead — ' . $lead->name)

@section('content')
@php
    $badgeMap = [
        'blue' => 'bg-blue-50 text-blue-700',
        'yellow' => 'bg-amber-50 text-amber-700',
        'gray' => 'bg-gray-100 text-gray-600',
        'purple' => 'bg-purple-50 text-purple-700',
        'green' => 'bg-green-50 text-green-700',
        'red' => 'bg-red-50 text-red-700',
    ];

    $statusKey = $lead->status;
    $statusLabel = \App\Models\Lead::STATUSES[$statusKey] ?? $statusKey;
    $statusBadge = $badgeMap[\App\Models\Lead::STATUS_COLORS[$statusKey] ?? 'gray'] ?? $badgeMap['gray'];

    $custom = (array) ($lead->custom_fields ?? []);
    $email = $custom['email'] ?? null;
    $area = $custom['area'] ?? null;
    $address = $custom['address'] ?? null;

    $knownCustom = ['name', 'phone', 'service_id', 'email', 'area', 'address'];
    $extraCustom = collect($custom)
        ->reject(fn ($value, $key) => in_array($key, $knownCustom, true))
        ->filter(fn ($value) => ! empty($value));

    $pipelineDays = max(0, (int) $lead->created_at->diffInDays());

    $quickStatuses = [
        'new' => ['label' => 'New', 'active' => 'bg-blue-600 text-white border-blue-600 shadow-sm', 'idle' => 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100'],
        'contacted' => ['label' => 'Contacted', 'active' => 'bg-amber-500 text-white border-amber-500 shadow-sm', 'idle' => 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100'],
        'no_answer' => ['label' => 'No Answer', 'active' => 'bg-gray-600 text-white border-gray-600 shadow-sm', 'idle' => 'bg-gray-100 text-gray-600 border-gray-200 hover:bg-gray-200'],
        'interested' => ['label' => 'Interested', 'active' => 'bg-purple-600 text-white border-purple-600 shadow-sm', 'idle' => 'bg-purple-50 text-purple-700 border-purple-200 hover:bg-purple-100'],
        'converted' => ['label' => 'Converted', 'active' => 'bg-green-600 text-white border-green-600 shadow-sm', 'idle' => 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100'],
        'lost' => ['label' => 'Lost', 'active' => 'bg-red-600 text-white border-red-600 shadow-sm', 'idle' => 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100'],
    ];
@endphp

    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4">
                    <x-admin.avatar :name="$lead->name" size="lg" class="shadow-xs" />
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-2xl font-bold text-gray-900">{{ $lead->name }}</h2>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBadge }}">
                                {{ $statusLabel }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1 truncate">
                            {{ $lead->phone }}
                            @if($email)
                                <span class="mx-1 text-gray-300">·</span>{{ $email }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <x-admin.button href="{{ route('admin.leads.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
                    @if($statusKey !== 'converted')
                        <x-admin.button href="{{ route('admin.leads.edit', $lead) }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'>Edit</x-admin.button>
                    @endif
                    @if($statusKey !== 'converted' && empty($existingCustomer))
                        <x-admin.button href="{{ route('admin.leads.convert', $lead) }}" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>'>Convert to Customer</x-admin.button>
                    @elseif($statusKey !== 'converted' && !empty($existingCustomer))
                        <form action="{{ route('admin.leads.work-order', $lead) }}" method="POST" class="inline">
                            @csrf
                            <x-admin.button type="submit" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 9.75h4.5m-4.5 3h4.5m-4.5 3h4.5m-5.625 3.75h6.75a4.5 4.5 0 004.5-4.5v-3a4.5 4.5 0 00-4.5-4.5H16.5a3 3 0 00-3-3h-3a3 3 0 00-3 3H7.125a4.5 4.5 0 00-4.5 4.5v3a4.5 4.5 0 004.5 4.5h6.75M12 3h.008v.008H12V3z"/></svg>'>New Work Order</x-admin.button>
                        </form>
                    @else
                        <x-admin.button href="{{ route('admin.customers.show', $lead->convertedCustomer) }}" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>'>View Customer</x-admin.button>
                    @endif
                </div>
            </div>
        </div>

        @if($statusKey === 'converted' && $lead->convertedCustomer)
            <div class="flex items-center gap-3 rounded-2xl bg-green-50 border border-green-100 px-5 py-4">
                <div class="w-10 h-10 rounded-xl bg-green-100 text-green-700 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-green-800">This lead was converted to a customer.</p>
                    <p class="text-xs text-green-600 mt-0.5">
                        <a href="{{ route('admin.customers.show', $lead->convertedCustomer) }}" class="font-medium hover:underline">
                            {{ $lead->convertedCustomer->name }}
                        </a>
                        · {{ $lead->convertedCustomer->phone }}
                        @if($lead->convertedCustomer->created_at)
                            · converted {{ $lead->convertedCustomer->created_at->format('d M Y') }}
                        @endif
                    </p>
                </div>
                <a href="{{ route('admin.customers.show', $lead->convertedCustomer) }}" class="text-xs font-medium text-green-700 hover:text-green-800 flex-shrink-0">Open customer →</a>
            </div>
        @endif

        @if($statusKey !== 'converted' && !empty($existingCustomer))
            <div class="flex items-center gap-3 rounded-2xl bg-sky-50 border border-sky-100 px-5 py-4">
                <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-sky-800">This number already belongs to a customer.</p>
                    <p class="text-xs text-sky-600 mt-0.5">
                        <a href="{{ route('admin.customers.show', $existingCustomer) }}" class="font-medium hover:underline">
                            {{ $existingCustomer->name }}
                        </a>
                        · {{ $existingCustomer->phone }} — use New Work Order instead of Convert.
                    </p>
                </div>
                <form action="{{ route('admin.leads.work-order', $lead) }}" method="POST" class="flex-shrink-0">
                    @csrf
                    <x-admin.button type="submit" variant="primary" size="sm">New Work Order</x-admin.button>
                </form>
            </div>
        @endif

        {{-- Quick stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <p class="text-sm font-medium text-gray-500">In Pipeline</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $pipelineDays }}<span class="text-base font-semibold text-gray-400 ml-1">day{{ $pipelineDays === 1 ? '' : 's' }}</span></p>
                <p class="text-xs text-gray-400 mt-1">since {{ $lead->created_at->format('d M Y') }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <p class="text-sm font-medium text-gray-500">Last Touched</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">{{ $lead->updated_at->diffForHumans() }}</p>
                <p class="text-xs text-gray-400 mt-1">last status change</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <p class="text-sm font-medium text-gray-500">Source</p>
                <p class="text-2xl font-bold text-gray-900 capitalize mt-2">{{ $lead->source }}</p>
                <p class="text-xs text-gray-400 mt-1">where the lead came from</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <p class="text-sm font-medium text-gray-500">Interested In</p>
                <p class="text-sm font-bold text-gray-900 mt-3 leading-snug">
                    @if($lead->service)
                        {{ $lead->service->name }}
                    @else
                        <span class="text-gray-300">Not specified</span>
                    @endif
                </p>
                <p class="text-xs text-gray-400 mt-2">selected service</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main column --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Details --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-lg font-semibold text-gray-900">Submitted Details</h3>
                        @if($statusKey !== 'converted')
                            <a href="{{ route('admin.leads.edit', $lead) }}" class="inline-flex items-center text-xs font-medium text-brand-600 hover:text-brand-700">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </a>
                        @endif
                    </div>
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
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($email)
                                    <a href="mailto:{{ $email }}" class="text-brand-600 hover:text-brand-700">{{ $email }}</a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Area / Locality</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $area ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Service Interested In</dt>
                            <dd class="mt-1 text-sm">
                                @if($lead->service)
                                    <a href="{{ route('admin.leads.index', ['service_id' => $lead->service_id]) }}" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-50 text-brand-700 hover:bg-brand-100 transition">{{ $lead->service->name }}</a>
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
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Last Updated</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $lead->updated_at->format('d M Y, h:i A') }}</dd>
                        </div>
                        @if($lead->last_reminder_sent_at)
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Last Reminder</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $lead->last_reminder_sent_at->format('d M Y, h:i A') }}</dd>
                            </div>
                        @endif
                        @if($address)
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Address</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $address }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                {{-- Additional details (custom fields) --}}
                @if($extraCustom->isNotEmpty())
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Additional Details</h3>
                        <p class="text-sm text-gray-500 mb-5">Extra information captured from the booking form.</p>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                            @foreach($extraCustom as $key => $value)
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ is_array($value) ? implode(', ', $value) : $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif

                {{-- Notes --}}
                @if($lead->notes)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="text-lg font-semibold text-gray-900">Notes</h3>
                            <a href="{{ route('admin.leads.edit', $lead) }}" class="inline-flex items-center text-xs font-medium text-brand-600 hover:text-brand-700">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </a>
                        </div>
                        <p class="text-sm text-gray-900 whitespace-pre-line">{{ $lead->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Update Status --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Update Call Status</h3>
                    <p class="text-sm text-gray-500 mb-4">Mark what happened after the last call attempt.</p>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($quickStatuses as $key => $style)
                            @php $isCurrent = $statusKey === $key; @endphp
                            <form action="{{ route('admin.leads.status', $lead) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $key }}">
                                @if($statusKey === 'converted' && $key !== 'converted')
                                    <button type="submit" disabled class="w-full rounded-xl border px-3 py-2.5 text-xs font-semibold transition opacity-50 cursor-not-allowed {{ $style['idle'] }}">
                                        {{ $style['label'] }}
                                    </button>
                                @else
                                    <button type="submit" class="w-full rounded-xl border px-3 py-2.5 text-xs font-semibold transition {{ $isCurrent ? $style['active'] : $style['idle'] }}">
                                        {{ $style['label'] }}
                                    </button>
                                @endif
                            </form>
                        @endforeach
                    </div>
                    @if($statusKey === 'converted')
                        <p class="text-xs text-gray-400 mt-3">This lead has been converted and is locked. Status changes are blocked.</p>
                    @endif
                </div>

                {{-- Contact & Actions --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Reach the Lead</h3>
                    <p class="text-sm text-gray-500 mb-4">Contact them directly on the channel they prefer.</p>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="tel:{{ $lead->phone }}" class="flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-green-50 text-green-700 text-sm font-semibold hover:bg-green-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            Call
                        </a>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->phone) }}?text={{ urlencode('Hello ' . $lead->name . ', this is Plant Tech Agro.') }}" target="_blank" rel="noopener" class="block w-full text-center px-4 py-2.5 rounded-xl bg-emerald-50 text-emerald-700 text-sm font-semibold hover:bg-emerald-100 transition">
                            <svg class="w-4 h-4 inline-block mr-1.5 -mt-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38a9.87 9.87 0 004.74 1.21c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0012.04 2zm0 18.15a8.2 8.2 0 01-4.19-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 01-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 012.41 5.83c0 4.54-3.7 8.23-8.23 8.23zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.17.25-.64.81-.78.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43s.17-.25.25-.41c.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.43.06-.66.31-.22.25-.86.85-.86 2.07 0 1.22.89 2.4 1.01 2.56.12.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.14-1.18s-.22-.16-.47-.28z"/></svg>
                            WhatsApp
                        </a>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-100 space-y-3">
                        @if($statusKey !== 'converted')
                            <x-admin.button href="{{ route('admin.leads.edit', $lead) }}" variant="secondary" class="w-full">Edit Lead Details</x-admin.button>
                        @endif
                        @if($statusKey !== 'converted' && empty($existingCustomer))
                            <x-admin.button href="{{ route('admin.leads.convert', $lead) }}" variant="primary" class="w-full">Convert to Customer</x-admin.button>
                        @elseif($statusKey !== 'converted' && !empty($existingCustomer))
                            <form action="{{ route('admin.leads.work-order', $lead) }}" method="POST">
                                @csrf
                                <x-admin.button type="submit" variant="primary" class="w-full">New Work Order</x-admin.button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection