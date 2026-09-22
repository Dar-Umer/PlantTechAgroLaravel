@extends('admin.layout')

@section('page-title', 'Quotations & Estimates')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Quotations & Estimates</h2>
                <p class="text-sm text-gray-500 mt-1">Generate proforma invoices, price estimates, and convert approved quotations to active work orders.</p>
            </div>
            <x-admin.button href="{{ route('admin.quotations.create') }}" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>
                New Quotation
            </x-admin.button>
        </div>

        {{-- Status Filter Badges --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-1">
            <a href="{{ route('admin.quotations.index', request()->only('q')) }}"
               class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition border {{ !request('status') ? 'bg-gray-900 text-white border-gray-900 shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">
                All ({{ $statusCounts->sum() }})
            </a>
            @foreach(\App\Models\Quotation::STATUSES as $k => $label)
                <a href="{{ route('admin.quotations.index', array_merge(request()->only('q'), ['status' => $k])) }}"
                   class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition border {{ request('status') === $k ? 'bg-gray-900 text-white border-gray-900 shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">
                    {{ $label }} ({{ $statusCounts[$k] ?? 0 }})
                </a>
            @endforeach
        </div>

        {{-- Search Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <form action="{{ route('admin.quotations.index') }}" method="GET" class="flex gap-3">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by quotation #, farmer name, or phone..."
                           class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
                <button type="submit" class="px-4 py-2 bg-gray-900 text-white text-sm font-semibold rounded-xl hover:bg-gray-800 transition">
                    Search
                </button>
                @if(request('q'))
                    <a href="{{ route('admin.quotations.index', request()->only('status')) }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-200 transition">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        {{-- Quotations Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            @if($quotations->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <p class="text-sm font-medium text-gray-900">No quotations found</p>
                    <p class="text-xs text-gray-500 mt-1">Get started by creating a new quotation from a Lead or directly.</p>
                    <div class="mt-4">
                        <x-admin.button href="{{ route('admin.quotations.create') }}" variant="primary" size="sm">Create First Quotation</x-admin.button>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50/75 text-xs uppercase tracking-wider text-gray-500 border-b border-gray-100">
                            <tr>
                                <th class="py-3.5 px-4 font-semibold">Quotation #</th>
                                <th class="py-3.5 px-4 font-semibold">Client / Farmer</th>
                                <th class="py-3.5 px-4 font-semibold">Service</th>
                                <th class="py-3.5 px-4 font-semibold">Date</th>
                                <th class="py-3.5 px-4 font-semibold">Status</th>
                                <th class="py-3.5 px-4 font-semibold text-right">Total Amount</th>
                                <th class="py-3.5 px-4 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($quotations as $quotation)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="py-3.5 px-4 font-semibold text-gray-900">
                                        <a href="{{ route('admin.quotations.show', $quotation) }}" class="text-brand-600 hover:text-brand-700 hover:underline">
                                            {{ $quotation->number }}
                                        </a>
                                        @if($quotation->workOrder)
                                            <span class="block text-[11px] font-normal text-green-600">WO: {{ $quotation->workOrder->number }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <p class="font-semibold text-gray-900">{{ $quotation->customer_name }}</p>
                                        <p class="text-xs text-gray-400">{{ $quotation->customer_phone }}</p>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($quotation->service)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-50 text-brand-700">
                                                {{ $quotation->service->name }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-xs">Custom Service</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-xs">
                                        <p class="text-gray-900 font-medium">{{ $quotation->date->format('d M Y') }}</p>
                                        @if($quotation->valid_until)
                                            <p class="text-gray-400">Valid: {{ $quotation->valid_until->format('d M') }}</p>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $quotation->statusBadge() }}">
                                            {{ $quotation->statusLabel() }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-bold text-gray-900">
                                        ₹{{ number_format((float) $quotation->grand_total, 2) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            <a href="{{ route('admin.quotations.pdf', $quotation) }}" target="_blank" title="Download PDF"
                                               class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            </a>
                                            <a href="{{ route('admin.quotations.show', $quotation) }}" title="View Quotation"
                                               class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                            @if($quotation->canApprove())
                                                <form action="{{ route('admin.quotations.approve', $quotation) }}" method="POST" class="inline"
                                                      onsubmit="return confirm('Approve this quotation and convert into an active Work Order?');">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-green-600 text-white text-xs font-semibold hover:bg-green-700 transition shadow-xs">
                                                        Approve & Start
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($quotations->hasPages())
                    <div class="p-4 border-t border-gray-100">
                        {{ $quotations->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
