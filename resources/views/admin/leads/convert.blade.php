@extends('admin.layout')

@section('page-title', 'Convert Lead to Customer')

@section('content')
    <div class="space-y-6 max-w-3xl">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Convert Lead to Customer</h2>
            <x-admin.button href="{{ route('admin.leads.show', $lead) }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back to Lead</x-admin.button>
        </div>

        <div class="bg-brand-50 border border-brand-100 rounded-2xl p-4 text-sm text-brand-800">
            You are converting <span class="font-semibold">{{ $lead->name }}</span> ({{ $lead->phone }}). Review the details below — they become the customer's login credentials for the Customer App. The customer will log in with their phone number and the password you set here.
        </div>

        @if(!empty($serviceUsable))
            <div class="bg-sky-50 border border-sky-100 rounded-2xl p-4 text-sm text-sky-800">
                Confirming will also open a <span class="font-semibold">pending work order for {{ $service->name }}</span> from this lead's submitted details — no stock is deducted. You will be redirected to the new work order.
            </div>
        @else
            <div class="bg-red-50 border border-red-200 rounded-2xl p-4 text-sm text-red-700">
                This lead's service is missing or inactive, so conversion is blocked until a valid service is attached to the lead.
            </div>
        @endif

        @if(!empty($existingCustomer))
            <div class="bg-sky-50 border border-sky-100 rounded-2xl p-5">
                <p class="text-sm font-semibold text-sky-800">This number already belongs to customer {{ $existingCustomer->name }} ({{ $existingCustomer->phone }}).</p>
                <p class="text-sm text-sky-600 mt-1">Conversion is not available — open a work order for the existing customer instead.</p>
                <form action="{{ route('admin.leads.work-order', $lead) }}" method="POST" class="mt-4">
                    @csrf
                    <x-admin.button type="submit" variant="primary">New Work Order for {{ $existingCustomer->name }}</x-admin.button>
                </form>
            </div>
        @else
        <form action="{{ route('admin.leads.convert.store', $lead) }}" method="POST" class="space-y-6">
            @csrf
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Customer Details</h3>
                <p class="text-sm text-gray-500 mb-5">Edit the details before confirming. Prefilled from the lead submission.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-admin.input name="name" label="Full Name" :value="old('name', $lead->name)" required />
                    <x-admin.input name="phone" label="Phone Number (Login ID)" :value="old('phone', $lead->phone)" required helptext="The customer logs into the app with this number." />
                    <x-admin.input name="email" label="Email (Optional)" type="email" :value="old('email')" />
                    <x-admin.input name="area" label="Area / Locality" :value="old('area', $area)" placeholder="e.g. Pulwama" />
                </div>
                <div class="mt-5">
                    <x-admin.textarea name="address" label="Address" :value="old('address', $address)" rows="2" />
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">App Login Credentials</h3>
                <p class="text-sm text-gray-500 mb-5">Set the password the customer will use to log into the Customer App. Share it with them after conversion.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-admin.input name="password" label="Password" type="password" required helptext="Minimum 6 characters." />
                    <x-admin.input name="password_confirmation" label="Confirm Password" type="password" required />
                </div>
                <div class="mt-5">
                    <x-admin.textarea name="notes" label="Notes (Optional)" :value="old('notes')" rows="3" helptext="Any details about this customer's requirements." />
                </div>
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Confirm & Create Customer</x-admin.button>
            </div>
        </form>
        @endif
    </div>
@endsection
