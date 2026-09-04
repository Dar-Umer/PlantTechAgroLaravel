@extends('admin.layout')

@section('page-title', 'Leads')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Leads</h2>
                <p class="text-sm text-gray-500 mt-1">Captured from the landing page. Call the lead and convert interested ones into customers.</p>
            </div>
        </div>

        {{-- Status Summary --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @php $badgeStyles = \App\Models\Lead::STATUS_COLORS; @endphp
            <a href="{{ route('admin.leads.index') }}"
               class="bg-white rounded-2xl shadow-sm border p-4 text-center transition {{ request('status') ? 'border-gray-100 hover:border-brand-200' : 'border-brand-300 ring-1 ring-brand-200' }}">
                <p class="text-2xl font-bold text-gray-900">{{ \App\Models\Lead::count() }}</p>
                <p class="text-xs text-gray-500 mt-0.5">All Leads</p>
            </a>
            @foreach(\App\Models\Lead::STATUSES as $key => $label)
                <a href="{{ route('admin.leads.index', ['status' => $key]) }}"
                   class="bg-white rounded-2xl shadow-sm border p-4 text-center transition {{ request('status') === $key ? 'border-brand-300 ring-1 ring-brand-200' : 'border-gray-100 hover:border-brand-200' }}">
                    <p class="text-2xl font-bold text-gray-900">{{ $statusCounts[$key] ?? 0 }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $label }}</p>
                </a>
            @endforeach
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.leads.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-wrap items-center gap-3">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by name or phone..."
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
            </div>
            <div>
                <select name="service_id"
                        class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    <option value="">All Services</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
            <x-admin.button type="submit" variant="primary">Filter</x-admin.button>
            @if(request('q') || request('service_id'))
                <a href="{{ request('status') ? route('admin.leads.index', ['status' => request('status')]) : route('admin.leads.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
            @endif
        </form>

        {{-- Leads Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 font-semibold text-gray-600">Name</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Phone</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Service</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Status</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Received</th>
                            <th class="px-6 py-3 font-semibold text-gray-600 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($leads as $lead)
                            @php
                                $color = $badgeStyles[$lead->status] ?? 'gray';
                                $statusClass = [
                                    'blue' => 'bg-blue-50 text-blue-700',
                                    'yellow' => 'bg-yellow-50 text-yellow-700',
                                    'gray' => 'bg-gray-100 text-gray-600',
                                    'purple' => 'bg-purple-50 text-purple-700',
                                    'green' => 'bg-green-50 text-green-700',
                                    'red' => 'bg-red-50 text-red-700',
                                ][$color];
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $lead->name }}</td>
                                <td class="px-6 py-4 text-gray-600">
                                    <a href="tel:{{ $lead->phone }}" class="hover:text-brand-600">{{ $lead->phone }}</a>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    @if($lead->service)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-50 text-brand-700">{{ $lead->service->name }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                        {{ \App\Models\Lead::STATUSES[$lead->status] ?? $lead->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-500 text-xs">{{ $lead->created_at->format('d M Y, h:i A') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($lead->status !== 'converted')
                                            <x-admin.button href="{{ route('admin.leads.convert', $lead) }}" variant="primary" size="sm">Convert</x-admin.button>
                                        @endif
                                        <x-admin.button href="{{ route('admin.leads.show', $lead) }}" variant="secondary" size="sm">View</x-admin.button>
                                        <form action="{{ route('admin.leads.destroy', $lead) }}" method="POST" onsubmit="return confirm('Delete this lead?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-admin.button type="submit" variant="danger" size="sm">Delete</x-admin.button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        <p class="text-sm">No leads found.</p>
                                        <p class="text-sm text-gray-400 mt-1">Leads arrive from the landing page contact form.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($leads->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $leads->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
