@extends('admin.layout')

@section('page-title', 'Quotation — ' . $quotation->number)

@section('content')
<div class="space-y-6 max-w-5xl">
    {{-- Header --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-700 flex items-center justify-center font-extrabold text-base">
                    QT
                </div>
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <h2 class="text-2xl font-bold text-gray-900">{{ $quotation->number }}</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $quotation->statusBadge() }}">
                            {{ $quotation->statusLabel() }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">
                        Client: <span class="font-semibold text-gray-800">{{ $quotation->customer_name }}</span> ({{ $quotation->customer_phone }})
                        @if($quotation->date)
                            <span class="mx-1 text-gray-300">·</span>Issued {{ $quotation->date->format('d M Y') }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <x-admin.button href="{{ route('admin.quotations.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>
                    Back
                </x-admin.button>

                <a href="{{ route('admin.quotations.print', $quotation) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gray-100 text-gray-800 text-sm font-semibold hover:bg-gray-200 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print
                </a>

                <a href="{{ route('admin.quotations.pdf', $quotation) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand-50 text-brand-700 text-sm font-semibold hover:bg-brand-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Download PDF
                </a>

                @if(! $quotation->isApproved())
                    <x-admin.button href="{{ route('admin.quotations.edit', $quotation) }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'>
                        Edit
                    </x-admin.button>
                @endif

                @if($quotation->canApprove())
                    <form action="{{ route('admin.quotations.approve', $quotation) }}" method="POST"
                          onsubmit="return confirm('Farmer has approved this quotation?\n\nThis will automatically convert the Lead, register/link the Customer, and create an active Work Order.');">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-green-600 text-white text-sm font-bold hover:bg-green-700 transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Approve & Start Work
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Conversion & Work Order Status Notice --}}
    @if($quotation->isApproved() && $quotation->workOrder)
        <div class="flex items-center justify-between gap-4 rounded-2xl bg-green-50 border border-green-200 p-5 flex-wrap">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-green-100 text-green-700 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-green-900">Quotation Approved & Work Order Active</p>
                    <p class="text-xs text-green-700 mt-0.5">
                        Active Work Order: <a href="{{ route('admin.work-orders.show', $quotation->workOrder) }}" class="font-bold underline">{{ $quotation->workOrder->number }}</a>
                        @if($quotation->approved_at)
                            (Approved on {{ $quotation->approved_at->format('d M Y, h:i A') }})
                        @endif
                    </p>
                </div>
            </div>
            <a href="{{ route('admin.work-orders.show', $quotation->workOrder) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-green-600 text-white text-xs font-semibold hover:bg-green-700 transition shadow-xs">
                Open Work Order →
            </a>
        </div>
    @elseif($quotation->canApprove())
        <div class="flex items-center justify-between gap-4 rounded-2xl bg-amber-50 border border-amber-200 p-5 flex-wrap">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-amber-900">Awaiting Farmer Approval</p>
                    <p class="text-xs text-amber-700 mt-0.5">Once the farmer confirms this quotation, click "Approve & Start Work" to convert the lead into an active Work Order.</p>
                </div>
            </div>
            <form action="{{ route('admin.quotations.approve', $quotation) }}" method="POST"
                  onsubmit="return confirm('Approve this quotation and convert into an active Work Order?');">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-green-600 text-white text-xs font-bold hover:bg-green-700 transition shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Approve & Start Work
                </button>
            </form>
        </div>
    @endif

    {{-- Details Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Client Details Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center justify-between">
                <span>Client & Farm Information</span>
                @if($quotation->lead)
                    <a href="{{ route('admin.leads.show', $quotation->lead) }}" class="text-xs font-medium text-brand-600 hover:underline">
                        View Lead #{{ $quotation->lead_id }} →
                    </a>
                @endif
            </h3>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase">Client Name</dt>
                    <dd class="mt-1 font-semibold text-gray-900">{{ $quotation->customer_name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase">Phone Number</dt>
                    <dd class="mt-1 font-semibold text-gray-900">
                        <a href="tel:{{ $quotation->customer_phone }}" class="text-brand-600 hover:underline">{{ $quotation->customer_phone }}</a>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase">Email</dt>
                    <dd class="mt-1 text-gray-800">{{ $quotation->customer_email ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase">Area / Locality</dt>
                    <dd class="mt-1 text-gray-800">{{ $quotation->customer_area ?: '—' }}</dd>
                </div>
                @if($quotation->customer_address)
                    <div class="col-span-2">
                        <dt class="text-xs font-medium text-gray-400 uppercase">Orchard Location</dt>
                        <dd class="mt-1 text-gray-800">{{ $quotation->customer_address }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Quotation Meta Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3">Quotation Details</h3>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase">Service Category</dt>
                    <dd class="mt-1 font-semibold text-gray-900">
                        @if($quotation->service)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-50 text-brand-700">
                                {{ $quotation->service->name }}
                            </span>
                        @else
                            Custom Service
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase">Quotation Status</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $quotation->statusBadge() }}">
                            {{ $quotation->statusLabel() }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase">Quotation Date</dt>
                    <dd class="mt-1 text-gray-800">{{ $quotation->date->format('d M Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase">Valid Until</dt>
                    <dd class="mt-1 text-gray-800">{{ $quotation->valid_until ? $quotation->valid_until->format('d M Y') : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase">Created By</dt>
                    <dd class="mt-1 text-gray-800">{{ $quotation->createdBy?->name ?? 'System' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase">Grand Total</dt>
                    <dd class="mt-1 font-bold text-brand-700 text-lg tabular-nums">₹{{ number_format((float) $quotation->grand_total, 2) }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Items Breakdown Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-900">Line Items & Deliverables</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50/75 text-xs uppercase tracking-wider text-gray-500 border-b border-gray-100">
                    <tr>
                        <th class="py-3 px-4 font-semibold">#</th>
                        <th class="py-3 px-4 font-semibold">Description</th>
                        <th class="py-3 px-4 font-semibold text-center">Unit</th>
                        <th class="py-3 px-4 font-semibold text-right">Qty</th>
                        <th class="py-3 px-4 font-semibold text-right">Rate (₹)</th>
                        <th class="py-3 px-4 font-semibold text-right">Discount</th>
                        <th class="py-3 px-4 font-semibold text-right">GST %</th>
                        <th class="py-3 px-4 font-semibold text-right">Total (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($quotation->items as $idx => $item)
                        <tr>
                            <td class="py-3 px-4 text-gray-400">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4 font-semibold text-gray-900">{{ $item->name }}</td>
                            <td class="py-3 px-4 text-center text-gray-500">{{ $item->unit ?: '—' }}</td>
                            <td class="py-3 px-4 text-right tabular-nums">{{ (float) $item->qty }}</td>
                            <td class="py-3 px-4 text-right tabular-nums">₹{{ number_format((float) $item->rate, 2) }}</td>
                            <td class="py-3 px-4 text-right tabular-nums text-gray-400">
                                {{ $item->discount > 0 ? '₹' . number_format((float) $item->discount, 2) : '—' }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums text-gray-500">{{ (float) $item->gst_rate }}%</td>
                            <td class="py-3 px-4 text-right font-bold text-gray-900 tabular-nums">₹{{ number_format((float) $item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Totals Summary --}}
        <div class="p-6 bg-gray-50/50 border-t border-gray-100 flex justify-end">
            <div class="w-72 space-y-2 text-sm">
                <div class="flex justify-between text-gray-600">
                    <span>Taxable Subtotal:</span>
                    <span class="font-medium text-gray-900 tabular-nums">₹{{ number_format((float) $quotation->subtotal, 2) }}</span>
                </div>
                @if($quotation->discount_total > 0)
                    <div class="flex justify-between text-red-600">
                        <span>Discount:</span>
                        <span class="font-medium tabular-nums">-₹{{ number_format((float) $quotation->discount_total, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-gray-600">
                    <span>GST / Taxes:</span>
                    <span class="font-medium text-gray-900 tabular-nums">+₹{{ number_format((float) $quotation->gst_total, 2) }}</span>
                </div>
                <div class="flex justify-between pt-2.5 border-t border-gray-200 text-base font-extrabold text-brand-700">
                    <span>Grand Total:</span>
                    <span class="text-xl tabular-nums">₹{{ number_format((float) $quotation->grand_total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Terms & Notes Card --}}
    @if($quotation->terms || $quotation->notes)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @if($quotation->terms)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Terms & Conditions</h4>
                    <p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $quotation->terms }}</p>
                </div>
            @endif

            @if($quotation->notes)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Internal Notes</h4>
                    <p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $quotation->notes }}</p>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
