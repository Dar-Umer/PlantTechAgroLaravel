@extends('admin.layout')

@section('page-title', 'Work Orders')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Work Orders</h2>
                <p class="text-sm text-gray-500 mt-1">Assign services to customers, track stages, record materials and generate invoices.</p>
            </div>
            <x-admin.button href="{{ route('admin.work-orders.create') }}" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>
                New Work Order
            </x-admin.button>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <a href="{{ route('admin.work-orders.index') }}"
               class="bg-white rounded-2xl shadow-sm border p-4 text-center transition {{ request('status') ? 'border-gray-100 hover:border-brand-200' : 'border-brand-300 ring-1 ring-brand-200' }}">
                <p class="text-2xl font-bold text-gray-900">{{ \App\Models\WorkOrder::count() }}</p>
                <p class="text-xs text-gray-500 mt-0.5">All</p>
            </a>
            @foreach(\App\Models\WorkOrder::STATUSES as $key => $label)
                <a href="{{ route('admin.work-orders.index', ['status' => $key]) }}"
                   class="bg-white rounded-2xl shadow-sm border p-4 text-center transition {{ request('status') === $key ? 'border-brand-300 ring-1 ring-brand-200' : 'border-gray-100 hover:border-brand-200' }}">
                    <p class="text-2xl font-bold text-gray-900">{{ $counts[$key] ?? 0 }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $label }}</p>
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.work-orders.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-wrap items-center gap-3">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by number or customer..."
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
            </div>
            <x-admin.button type="submit" variant="primary">Search</x-admin.button>
            @if(request('q'))
                <a href="{{ request('status') ? route('admin.work-orders.index', ['status' => request('status')]) : route('admin.work-orders.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
            @endif
        </form>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 font-semibold text-gray-600">Number</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Customer</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Service</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Agent</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Status</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Invoice</th>
                            <th class="px-6 py-3 font-semibold text-gray-600 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($workOrders as $workOrder)
                            @php
                                $color = \App\Models\WorkOrder::STATUS_COLORS[$workOrder->status] ?? 'gray';
                                $statusClass = ['gray' => 'bg-gray-100 text-gray-600', 'blue' => 'bg-blue-50 text-blue-700', 'yellow' => 'bg-yellow-50 text-yellow-700', 'green' => 'bg-green-50 text-green-700', 'red' => 'bg-red-50 text-red-700'][$color];
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $workOrder->number }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $workOrder->customer_name }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $workOrder->service_name }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $workOrder->agent?->name ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                        {{ \App\Models\WorkOrder::STATUSES[$workOrder->status] ?? $workOrder->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($workOrder->invoice)
                                        <a href="{{ route('admin.invoices.show', $workOrder->invoice) }}" class="text-brand-600 hover:text-brand-700 text-xs font-medium">{{ $workOrder->invoice->number }}</a>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <x-admin.button href="{{ route('admin.work-orders.show', $workOrder) }}" variant="secondary" size="sm">Open</x-admin.button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        <p class="text-sm">No work orders found.</p>
                                        <a href="{{ route('admin.work-orders.create') }}" class="mt-2 text-sm text-brand-600 hover:text-brand-700 font-medium">Create your first work order</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($workOrders->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $workOrders->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
