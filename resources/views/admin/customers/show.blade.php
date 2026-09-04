@extends('admin.layout')

@section('page-title', 'Customer — ' . $customer->name)

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-900">Customer Details</h2>
                @if($customer->status === 'active')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">Active</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700">Inactive</span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                <x-admin.button type="button" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>
                    <a href="{{ route('admin.customers.index') }}">Back</a>
                </x-admin.button>
                <x-admin.button type="button" variant="primary">
                    <a href="{{ route('admin.customers.edit', $customer) }}">Edit Customer</a>
                </x-admin.button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-5">Details</h3>
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
            </div>

            <div class="space-y-6">
                @if($lead)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Origin</h3>
                        <p class="text-sm text-gray-500 mb-4">This customer was converted from a lead.</p>
                        <a href="{{ route('admin.leads.show', $lead) }}" class="block rounded-xl bg-gray-50 border border-gray-100 p-4 hover:border-brand-200 transition">
                            <p class="text-sm font-semibold text-gray-900">{{ $lead->name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $lead->phone }} · {{ $lead->created_at->format('d M Y') }}</p>
                            <p class="text-xs text-brand-600 mt-2 font-medium">View original lead →</p>
                        </a>
                    </div>
                @endif

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Quick Actions</h3>
                    <p class="text-sm text-gray-500 mb-4">Reach the customer directly.</p>
                    <div class="space-y-3">
                        <a href="tel:{{ $customer->phone }}" class="block w-full text-center px-4 py-2.5 rounded-xl bg-green-50 text-green-700 text-sm font-semibold hover:bg-green-100 transition">
                            📞 Call {{ $customer->phone }}
                        </a>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->phone) }}" target="_blank" rel="noopener" class="block w-full text-center px-4 py-2.5 rounded-xl bg-emerald-50 text-emerald-700 text-sm font-semibold hover:bg-emerald-100 transition">
                            💬 WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
