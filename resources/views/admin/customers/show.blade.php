@extends('admin.layout')

@section('page-title', 'Customer — ' . $customer->name)

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
    $woStatusColor = \App\Models\WorkOrder::STATUS_COLORS ?? ['pending' => 'gray', 'assigned' => 'blue', 'in_progress' => 'yellow', 'completed' => 'green', 'cancelled' => 'red'];
    $invoiceStatusColor = \App\Models\Invoice::STATUS_COLORS ?? ['unpaid' => 'red', 'partial' => 'yellow', 'overdue' => 'red', 'paid' => 'green', 'cancelled' => 'gray'];
    $woLabels = \App\Models\WorkOrder::STATUSES;
    $invLabels = \App\Models\Invoice::STATUSES;
@endphp
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <x-admin.avatar :name="$customer->name" size="lg" :status="$customer->status" class="shadow-xs" />
                <div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h2 class="text-2xl font-bold text-gray-900">{{ $customer->name }}</h2>
                        <span class="font-mono text-xs font-bold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-200 shadow-2xs">
                            Orchardist ID: {{ $customer->orchardist_id ?? 'OID-N/A' }}
                        </span>
                        @if($customer->status === 'active')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                Inactive
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 text-xs text-gray-400 mt-1">
                        <span>Phone: {{ $customer->phone }}</span>
                        @if($customer->gstin)
                            <span>·</span>
                            <span class="font-mono text-emerald-600 font-semibold">GSTIN: {{ $customer->gstin }}</span>
                        @endif
                        @if($customer->area)
                            <span>·</span>
                            <span>{{ $customer->area }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <x-admin.button href="{{ route('admin.customers.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
                <a href="{{ route('admin.customers.ledger', $customer) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    View Statement
                </a>
                <x-admin.button href="{{ route('admin.customers.edit', $customer) }}" variant="primary">Edit Customer</x-admin.button>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <p class="text-sm font-medium text-gray-500">Work Orders</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $workOrdersTotal }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $workOrdersActive }} active</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <p class="text-sm font-medium text-gray-500">In Progress</p>
                <p class="text-3xl font-bold text-amber-600 mt-1">{{ $workOrdersActive }}</p>
                <p class="text-xs text-gray-400 mt-1">pending + assigned + running</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <p class="text-sm font-medium text-gray-500">Completed</p>
                <p class="text-3xl font-bold text-green-600 mt-1">{{ $workOrdersCompleted }}</p>
                <p class="text-xs text-gray-400 mt-1">of {{ $workOrdersTotal }} work orders</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <p class="text-sm font-medium text-gray-500">Total Paid</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">₹{{ number_format($totalPaid, 0) }}</p>
                <p class="text-xs text-gray-400 mt-1">collected so far</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 {{ $outstanding > 0 ? 'border-red-200' : '' }}">
                <p class="text-sm font-medium text-gray-500">Outstanding</p>
                <p class="text-3xl font-bold {{ $outstanding > 0 ? 'text-red-600' : 'text-gray-900' }} mt-1">₹{{ number_format($outstanding, 0) }}</p>
                <p class="text-xs {{ $overdueInvoices > 0 ? 'text-red-500' : 'text-gray-400' }} mt-1">{{ $overdueInvoices }} overdue invoice(s)</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <p class="text-sm font-medium text-gray-500">Invoices</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $customer->invoices()->count() }}</p>
                <p class="text-xs text-gray-400 mt-1">issued to customer</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                {{-- Details --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-lg font-semibold text-gray-900">Details</h3>
                        <a href="{{ route('admin.customers.edit', $customer) }}" class="inline-flex items-center text-xs font-medium text-brand-600 hover:text-brand-700">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </a>
                    </div>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Name</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $customer->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Phone (Login ID)</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $customer->phone }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $customer->email ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Area</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $customer->area ?: '—' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Address</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $customer->address ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Added On</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $customer->created_at->format('d M Y, h:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Last Login</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $customer->last_login_at?->format('d M Y, h:i A') ?? 'Never logged in' }}</dd>
                        </div>
                    </dl>

                    @if($customer->notes)
                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Notes</h4>
                            <p class="text-sm text-gray-900 whitespace-pre-line">{{ $customer->notes }}</p>
                        </div>
                    @endif
                </div>

                {{-- Orchards Owned by Customer --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-1">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Orchards & Farm Holdings</h3>
                            <p class="text-sm text-gray-500">Orchards managed under this farmer's account ({{ $customer->orchards()->count() }} Total).</p>
                        </div>
                        <x-admin.button href="{{ route('admin.orchards.create', ['customer_id' => $customer->id]) }}" variant="primary" size="sm">
                            + Add Orchard
                        </x-admin.button>
                    </div>

                    <div class="mt-4 divide-y divide-gray-100">
                        @forelse($customer->orchards()->latest()->get() as $orchard)
                            <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.orchards.show', $orchard) }}" class="font-bold text-gray-900 hover:text-brand-600 transition">
                                            {{ $orchard->name }}
                                        </a>
                                        <span class="font-mono text-xs font-semibold text-gray-600 bg-gray-100 px-2 py-0.5 rounded">
                                            {{ $orchard->orchard_id }}
                                        </span>
                                        @if($orchard->is_company_established)
                                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Established by PTA
                                            </span>
                                        @else
                                            <span class="text-[11px] font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">
                                                Self Registered
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $orchard->address ?: 'No address specified' }} ·
                                        <strong class="text-gray-700 font-semibold">{{ number_format($orchard->area_kanals, 1) }} Kanals</strong> ·
                                        <strong class="text-gray-700 font-semibold">{{ number_format($orchard->tree_count) }} Plants</strong> ·
                                        <span>Age: {{ $orchard->age ?? 'N/A' }}</span>
                                    </p>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <x-admin.button href="{{ route('admin.work-orders.create', ['customer_id' => $customer->id, 'orchard_id' => $orchard->id]) }}" variant="secondary" size="sm">
                                        Book Service
                                    </x-admin.button>
                                    <x-admin.button href="{{ route('admin.orchards.show', $orchard) }}" variant="secondary" size="sm">
                                        View
                                    </x-admin.button>
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center text-xs text-gray-400">
                                No orchards registered under this customer yet.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Work Orders / Services --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="text-lg font-semibold text-gray-900">Work Orders & Services</h3>
                        <a href="{{ route('admin.work-orders.index', ['customer_id' => $customer->id]) }}" class="text-xs font-medium text-brand-600 hover:text-brand-700">View all</a>
                    </div>
                    <p class="text-sm text-gray-500 mb-5">Service requests this customer has booked.</p>

                    @forelse($workOrders as $wo)
                        <div class="flex items-center gap-4 py-4 {{ !$loop->first ? 'border-t border-gray-100' : '' }}">
                            <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 9.75h4.5m-4.5 3h4.5m-4.5 3h4.5m-5.625 3.75h6.75a4.5 4.5 0 004.5-4.5v-3a4.5 4.5 0 00-4.5-4.5H16.5a3 3 0 00-3-3h-3a3 3 0 00-3 3H7.125a4.5 4.5 0 00-4.5 4.5v3a4.5 4.5 0 004.5 4.5h6.75M12 3h.008v.008H12V3z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('admin.work-orders.show', $wo) }}" class="text-sm font-semibold text-gray-900 hover:text-brand-600">{{ $wo->service_name }}</a>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeMap[$woStatusColor[$wo->status] ?? 'gray'] ?? $badgeMap['gray'] }}">
                                        {{ $woLabels[$wo->status] ?? $wo->status }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5 truncate">
                                    {{ $wo->number }}
                                    @if($wo->agent) · {{ $wo->agent->name }} @endif
                                    · {{ $wo->created_at->format('d M Y') }}
                                </p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                @if($wo->invoice)
                                    <p class="text-sm font-semibold text-gray-900">₹{{ number_format($wo->invoice->grand_total, 0) }}</p>
                                    <p class="text-xs {{ $wo->invoice->balanceDue() > 0 ? 'text-red-500' : 'text-green-600' }}">{{ $wo->invoice->balanceDue() > 0 ? '₹ ' . number_format($wo->invoice->balanceDue(), 0) . ' due' : 'Paid' }}</p>
                                @else
                                    <p class="text-xs text-gray-400">No invoice yet</p>
                                @endif
                            </div>
                            <a href="{{ route('admin.work-orders.show', $wo) }}" class="text-brand-600 hover:text-brand-700 flex-shrink-0" aria-label="Open work order">
                            </a>
                        </div>
                    @empty
                        <div class="rounded-xl bg-gray-50 border border-gray-100 px-6 py-10 text-center">
                            <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p class="text-sm text-gray-500">No work orders yet.</p>
                        </div>
                    @endforelse

                    @if($workOrders->hasPages())
                        <div class="pt-4 border-t border-gray-100">
                            {{ $workOrders->links() }}
                        </div>
                    @endif
                </div>

                {{-- Invoices & Payments --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="text-lg font-semibold text-gray-900">Invoices</h3>
                        <a href="{{ route('admin.invoices.index', ['customer_id' => $customer->id]) }}" class="text-xs font-medium text-brand-600 hover:text-brand-700">View all</a>
                    </div>
                    <p class="text-sm text-gray-500 mb-5">Billing history for this customer.</p>

                    @forelse($invoices as $invoice)
                        <div class="py-4 {{ !$loop->first ? 'border-t border-gray-100' : '' }}">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-sm font-semibold text-gray-900 hover:text-brand-600">{{ $invoice->number }}</a>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeMap[$invoiceStatusColor[$invoice->status] ?? 'gray'] ?? $badgeMap['gray'] }}">
                                            {{ $invLabels[$invoice->status] ?? $invoice->status }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $invoice->invoice_date->format('d M Y') }}
                                        @if($invoice->workOrder) · {{ $invoice->workOrder->number }} @endif
                                    </p>
                                    @php $pending = count($invoice->payments); @endphp
                                    @if($pending)
                                        <p class="text-xs text-gray-500 mt-1">{{ $pending }} payment(s)</p>
                                    @endif
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="text-sm font-semibold text-gray-900">₹{{ number_format($invoice->grand_total, 0) }}</p>
                                    <p class="text-xs {{ $invoice->balanceDue() > 0 ? 'text-red-500' : 'text-green-600' }}">
                                        {{ $invoice->balanceDue() > 0 ? '₹ ' . number_format($invoice->balanceDue(), 0) . ' due' : 'Fully paid' }}
                                    </p>
                                </div>
                                <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-brand-600 hover:text-brand-700 flex-shrink-0 -translate-x-1" aria-label="View invoice">
                                </a>
                            </div>

                            @if($invoice->payments->isNotEmpty())
                                <div class="mt-3 ml-14 space-y-1.5">
                                    @foreach($invoice->payments as $payment)
                                        <div class="flex items-center gap-2 text-xs text-gray-500">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-400 flex-shrink-0"></span>
                                            <span class="font-medium text-gray-700">₹{{ number_format($payment->amount, 0) }}</span>
                                            <span>via {{ \App\Models\Payment::METHODS[$payment->method] ?? $payment->method }}</span>
                                            <span>· {{ $payment->paid_at->format('d M Y') }}</span>
                                            @if($payment->reference)<span>· Ref: {{ $payment->reference }}</span>@endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="rounded-xl bg-gray-50 border border-gray-100 px-6 py-10 text-center">
                            <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                            <p class="text-sm text-gray-500">No invoices yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="space-y-6">
                {{-- Services Availed --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Services Availed</h3>
                    <p class="text-sm text-gray-500 mb-4">What this customer has booked, with totals.</p>
                    @if($servicesAvailed->isNotEmpty())
                        <div class="space-y-3">
                            @foreach($servicesAvailed as $service)
                                <div class="flex items-center gap-3 rounded-xl bg-gray-50 border border-gray-100 p-3">
                                    <div class="w-9 h-9 rounded-lg bg-brand-50 text-brand-700 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $service['name'] }}</p>
                                        <p class="text-xs text-gray-500">Last: {{ $service['last_booked'] ?? '—' }}</p>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-50 text-brand-700 flex-shrink-0">{{ $service['total'] }}×</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center py-6">No services availed yet.</p>
                    @endif
                </div>

                {{-- Support Tickets --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="text-lg font-semibold text-gray-900">Support Tickets</h3>
                        <a href="{{ route('admin.tickets.index', ['q' => $customer->phone]) }}" class="text-xs font-medium text-brand-600 hover:text-brand-700">View all</a>
                    </div>
                    <p class="text-sm text-gray-500 mb-4">Farmer inquiries and crop diagnosis requests.</p>
                    @if($tickets->isNotEmpty())
                        <div class="space-y-3">
                            @foreach($tickets as $t)
                                <a href="{{ route('admin.tickets.show', $t) }}" class="block rounded-xl bg-gray-50 border border-gray-100 p-3 hover:border-brand-200 transition">
                                    <div class="flex items-center justify-between gap-2 mb-1">
                                        <span class="font-mono text-xs font-bold text-brand-600">{{ $t->ticket_number }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $t->statusBadgeClasses() }}">
                                            {{ $t->statusLabel() }}
                                        </span>
                                    </div>
                                    <p class="text-xs font-semibold text-gray-800 line-clamp-1">{{ $t->subject }}</p>
                                    <p class="text-[11px] text-gray-400 mt-1">{{ $t->created_at->format('d M Y') }} · {{ $t->assignedStaff ? $t->assignedStaff->name : 'Unassigned' }}</p>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center py-6">No support tickets raised yet.</p>
                    @endif
                </div>

                @if($lead)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Origin</h3>
                        <p class="text-sm text-gray-500 mb-4">This customer was converted from a lead.</p>
                        <a href="{{ route('admin.leads.show', $lead) }}" class="block rounded-xl bg-gray-50 border border-gray-100 p-4 hover:border-brand-200 transition">
                            <p class="text-sm font-semibold text-gray-900">{{ $lead->name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $lead->phone }} · {{ $lead->created_at->format('d M Y') }}</p>
                            <p class="text-xs text-brand-600 mt-2 font-medium">View original lead</p>
                        </a>
                    </div>
                @endif

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Quick Actions</h3>
                    <p class="text-sm text-gray-500 mb-4">Reach the customer directly.</p>
                    <div class="space-y-3">
                        <a href="tel:{{ $customer->phone }}" class="block w-full text-center px-4 py-2.5 rounded-xl bg-green-50 text-green-700 text-sm font-semibold hover:bg-green-100 transition">
                            <svg class="w-4 h-4 inline-block mr-1.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            Call {{ $customer->phone }}
                        </a>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->phone) }}" target="_blank" rel="noopener" class="block w-full text-center px-4 py-2.5 rounded-xl bg-emerald-50 text-emerald-700 text-sm font-semibold hover:bg-emerald-100 transition">
                            <svg class="w-4 h-4 inline-block mr-1.5 -mt-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38a9.87 9.87 0 004.74 1.21c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0012.04 2zm0 18.15a8.2 8.2 0 01-4.19-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 01-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 012.41 5.83c0 4.54-3.7 8.23-8.23 8.23zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.17.25-.64.81-.78.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43s.17-.25.25-.41c.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.43.06-.66.31-.22.25-.86.85-.86 2.07 0 1.22.89 2.4 1.01 2.56.12.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.14-1.18s-.22-.16-.47-.28z"/></svg>
                            WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection