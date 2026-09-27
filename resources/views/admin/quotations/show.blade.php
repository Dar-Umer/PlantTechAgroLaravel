@extends('admin.layout')

@section('page-title', 'Quotation — ' . $quotation->number)

@section('content')
@php
    $accentColor = $quotation->getAccentColor();
    $docTitle = $quotation->getDocumentTitle();
@endphp

<div class="space-y-6 max-w-5xl">
    {{-- Header --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-extrabold text-base shadow-xs"
                     style="background-color: {{ $accentColor }}18; color: {{ $accentColor }};">
                    QT
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $quotation->number }}</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $quotation->statusBadge() }}">
                            {{ $quotation->statusLabel() }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold border shadow-2xs"
                              style="background-color: {{ $accentColor }}12; color: {{ $accentColor }}; border-color: {{ $accentColor }}30;">
                            <span class="w-2 h-2 rounded-full" style="background-color: {{ $accentColor }};"></span>
                            <span>{{ $docTitle }}</span>
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
                    Print Proforma
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
                            Approve &amp; Start Work
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
                    <p class="text-sm font-bold text-green-900">Quotation Approved &amp; Work Order Active</p>
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
                Open Work Order &rarr;
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
                    <p class="text-xs text-amber-700 mt-0.5">Once the farmer confirms this quotation, click "Approve &amp; Start Work" to convert the lead into an active Work Order.</p>
                </div>
            </div>
            <form action="{{ route('admin.quotations.approve', $quotation) }}" method="POST"
                  onsubmit="return confirm('Approve this quotation and convert into an active Work Order?');">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-green-600 text-white text-xs font-bold hover:bg-green-700 transition shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Approve &amp; Start Work
                </button>
            </form>
        </div>
    @endif

    {{-- Details Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Client Details Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center justify-between">
                <span>Quotation Prepared For</span>
                @if($quotation->lead)
                    <a href="{{ route('admin.leads.show', $quotation->lead) }}" class="text-xs font-medium text-brand-600 hover:underline">
                        View Lead #{{ $quotation->lead_id }} &rarr;
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
                    <dt class="text-xs font-medium text-gray-400 uppercase">Area</dt>
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

        {{-- Project / Variety Details Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3">Project / Variety Scope</h3>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase">Project Scope</dt>
                    <dd class="mt-1 font-semibold text-emerald-800">
                        {{ $quotation->scope_title ?: ($quotation->service?->name ?? 'Book an Orchard') }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase">Booked Variety</dt>
                    <dd class="mt-1 font-bold text-gray-900">{{ $quotation->variety_name ?: 'Devil Gala' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase">Rootstock</dt>
                    <dd class="mt-1 text-gray-800">{{ $quotation->rootstock ?: 'M9 / T337 (High Density)' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase">Plants per Kanal</dt>
                    <dd class="mt-1 text-gray-800">{{ $quotation->plants_per_kanal ?: '150 (Standard)' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase">Quotation Date</dt>
                    <dd class="mt-1 text-gray-800">{{ $quotation->date->format('d M Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase">Valid Until</dt>
                    <dd class="mt-1 text-gray-800">{{ $quotation->valid_until ? $quotation->valid_until->format('d M Y') : '—' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Line Items Breakdown Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-900">Deliverables / Service Description</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="text-white text-xs uppercase tracking-wider" style="background-color: {{ $accentColor }};">
                    <tr>
                        <th class="py-3 px-4 font-semibold w-12">#</th>
                        <th class="py-3 px-4 font-semibold">Deliverable / Service Description</th>
                        <th class="py-3 px-4 font-semibold text-center">Unit</th>
                        <th class="py-3 px-4 font-semibold text-center">Qty</th>
                        <th class="py-3 px-4 font-semibold text-right">Rate (₹)</th>
                        <th class="py-3 px-4 font-semibold text-right">Discount</th>
                        <th class="py-3 px-4 font-semibold text-center">GST</th>
                        <th class="py-3 px-4 font-semibold text-right">Total (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($quotation->items as $idx => $item)
                        <tr>
                            <td class="py-3 px-4 text-gray-400 font-bold">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4 font-semibold text-gray-900">{{ $item->name }}</td>
                            <td class="py-3 px-4 text-center text-gray-500">{{ $item->unit ?: '—' }}</td>
                            <td class="py-3 px-4 text-center tabular-nums">{{ (float) $item->qty }}</td>
                            <td class="py-3 px-4 text-right tabular-nums">₹{{ number_format((float) $item->rate, 2) }}</td>
                            <td class="py-3 px-4 text-right tabular-nums text-gray-400">
                                {{ $item->discount > 0 ? '₹' . number_format((float) $item->discount, 2) : '—' }}
                            </td>
                            <td class="py-3 px-4 text-center tabular-nums text-gray-500">{{ (float) $item->gst_rate }}%</td>
                            <td class="py-3 px-4 text-right font-bold tabular-nums" style="color: {{ $accentColor }};">₹{{ number_format((float) $item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Totals Summary --}}
        <div class="p-6 bg-gray-50/50 border-t border-gray-100 flex justify-end">
            <div class="w-80 space-y-2 text-sm">
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
                    <span>GST / Applicable Taxes:</span>
                    <span class="font-medium text-gray-900 tabular-nums">+₹{{ number_format((float) $quotation->gst_total, 2) }}</span>
                </div>
                <div class="flex justify-between items-center text-white rounded-xl p-3.5 mt-2 shadow-xs" style="background-color: {{ $accentColor }};">
                    <span class="font-bold uppercase tracking-wider text-xs">Grand Total (INR):</span>
                    <span class="text-xl font-extrabold tabular-nums">₹{{ number_format((float) $quotation->grand_total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment Schedule & Package Inclusions --}}
    @php
        $hasPackage = $quotation->hasPackageInclusions();
    @endphp
    <div class="grid grid-cols-1 {{ $hasPackage ? 'md:grid-cols-2' : '' }} gap-6">
        {{-- Payment Schedule --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-3">
            <h4 class="text-sm font-bold uppercase tracking-wider text-gray-700 flex items-center gap-2 border-b border-gray-100 pb-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span>Payment Schedule</span>
            </h4>
            <div class="space-y-2.5 pt-1">
                @foreach($quotation->getCalculatedPaymentSchedule() as $stage)
                    <div class="flex items-center justify-between text-xs p-2.5 rounded-xl bg-gray-50 border border-gray-100">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-md text-white font-bold text-[10px] tabular-nums" style="background-color: {{ $accentColor }};">{{ (int) $stage['percent'] }}%</span>
                            <span class="font-semibold text-gray-800">{{ $stage['stage'] }}</span>
                        </div>
                        <span class="font-bold text-gray-900 tabular-nums">₹{{ number_format((float) $stage['amount'], 2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        @if($hasPackage)
            {{-- Package Inclusions --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-3">
                <h4 class="text-sm font-bold uppercase tracking-wider text-gray-700 flex items-center gap-2 border-b border-gray-100 pb-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span>{{ $quotation->package_title ?: 'Per Kanal Standard Package' }}</span>
                </h4>
                <div class="grid grid-cols-3 gap-3 pt-1">
                    <div class="rounded-xl p-3 text-center border" style="background-color: {{ $accentColor }}0a; border-color: {{ $accentColor }}25;">
                        <div class="text-xs font-semibold uppercase" style="color: {{ $accentColor }};">Poles</div>
                        <div class="text-2xl font-black mt-1 tabular-nums" style="color: {{ $accentColor }};">{{ $quotation->package_poles ?? 19 }}</div>
                    </div>
                    <div class="rounded-xl p-3 text-center border" style="background-color: {{ $accentColor }}0a; border-color: {{ $accentColor }}25;">
                        <div class="text-xs font-semibold uppercase" style="color: {{ $accentColor }};">Anchors</div>
                        <div class="text-2xl font-black mt-1 tabular-nums" style="color: {{ $accentColor }};">{{ $quotation->package_anchors ?? 6 }}</div>
                    </div>
                    <div class="rounded-xl p-3 text-center border" style="background-color: {{ $accentColor }}0a; border-color: {{ $accentColor }}25;">
                        <div class="text-xs font-semibold uppercase" style="color: {{ $accentColor }};">Plants</div>
                        <div class="text-2xl font-black mt-1 tabular-nums" style="color: {{ $accentColor }};">{{ $quotation->package_plants ?? 150 }}</div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Additional Notes & Terms --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Additional Notes --}}
        <div class="bg-amber-50/60 rounded-2xl border border-amber-200 p-6 space-y-3">
            <h4 class="text-sm font-bold uppercase tracking-wider text-amber-900 flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Additional Notes</span>
            </h4>
            <ul class="text-xs text-amber-900 space-y-1.5 list-disc list-inside">
                @foreach($quotation->getAdditionalNotesList() as $note)
                    <li>{{ $note }}</li>
                @endforeach
            </ul>
        </div>

        {{-- Bank Details --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-3">
            <h4 class="text-sm font-bold uppercase tracking-wider text-gray-700 flex items-center gap-2 border-b border-gray-100 pb-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <span>Bank Account Details</span>
            </h4>
            <dl class="grid grid-cols-2 gap-3 text-xs">
                <div>
                    <dt class="text-gray-400">Account Name</dt>
                    <dd class="font-bold text-gray-900">{{ $quotation->bank_account_name ?: 'Plant Tech Agro' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-400">Account Number</dt>
                    <dd class="font-mono font-bold text-sm tracking-wide" style="color: {{ $accentColor }};">{{ $quotation->bank_account_no ?: '0942 0100 0000 0275' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-400">Branch</dt>
                    <dd class="text-gray-800">{{ $quotation->bank_branch ?: 'Migrant Colony Hall Pulwama' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-400">IFSC Code</dt>
                    <dd class="font-mono font-bold text-gray-900">{{ $quotation->bank_ifsc ?: 'JAKA0MIGRNT' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection
