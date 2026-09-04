@extends('admin.layout')

@section('page-title', 'Edit Lead')

@section('content')
    <div class="space-y-6 max-w-3xl">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Edit Lead</h2>
            <x-admin.button href="{{ route('admin.leads.show', $lead) }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
        </div>

        <form action="{{ route('admin.leads.update', $lead) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Lead Information</h3>
                <p class="text-sm text-gray-500 mb-5">Fix any wrong details captured from the form before converting this lead.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-admin.input name="name" label="Name" :value="old('name', $lead->name)" required />
                    <x-admin.input name="phone" label="Phone Number" :value="old('phone', $lead->phone)" required />
                </div>
                <div class="mt-5">
                    <x-admin.select name="service_id" label="Service Interested In" :options="$services->pluck('name', 'id')->all()" :value="(string) old('service_id', $lead->service_id)" placeholder="Not specified" />
                </div>
                <div class="mt-5">
                    <x-admin.textarea name="notes" label="Notes" :value="old('notes', $lead->notes)" rows="4" helptext="Internal notes about calls and discussions." />
                </div>
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Update Lead</x-admin.button>
            </div>
        </form>
    </div>
@endsection
