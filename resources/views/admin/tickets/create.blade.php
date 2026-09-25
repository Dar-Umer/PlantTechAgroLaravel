@extends('admin.layout')

@section('page-title', 'Create Support Ticket')

@section('content')
@php
    $farmerOptions = [];
    foreach($customers as $c) {
        $farmerOptions[$c->id] = "{$c->name} (" . ($c->phone ?? 'No Phone') . ')' . ($c->orchardist_id ? " - {$c->orchardist_id}" : '');
    }

    $staffOptions = [];
    foreach($staffList as $s) {
        $staffOptions[$s->id] = "{$s->name} (" . ucfirst(str_replace('_', ' ', $s->role ?? 'Staff')) . ')';
    }

    $categoryOptions = [];
    foreach(\App\Models\Ticket::CATEGORIES as $key => $lbl) {
        $categoryOptions[$key] = explode('/', $lbl)[0];
    }
@endphp

<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Create Support Ticket</h2>
            <p class="text-sm text-gray-500 mt-1">Open a technical ticket on behalf of a farmer (walk-in inquiry, phone call, or field report).</p>
        </div>
        <x-admin.button href="{{ route('admin.tickets.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>
            Back to Tickets
        </x-admin.button>
    </div>

    {{-- Form Card --}}
    <form method="POST" action="{{ route('admin.tickets.store') }}" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            {{-- Farmer Selector --}}
            <x-admin.select name="customer_id" label="Farmer" :required="true" placeholder="-- Select Farmer --" :options="$farmerOptions" />

            {{-- Staff Assignment --}}
            <x-admin.select name="assigned_to" label="Assign Support Staff" placeholder="-- Leave Unassigned for now --" :options="$staffOptions" />

            {{-- Category --}}
            <x-admin.select name="category" label="Category" :required="true" :options="$categoryOptions" />

            {{-- Priority --}}
            <x-admin.select name="priority" label="Priority" :required="true" :options="\App\Models\Ticket::PRIORITIES" value="medium" />
        </div>

        {{-- Subject --}}
        <x-admin.input name="subject" label="Ticket Subject / Summary" :required="true" placeholder="e.g. Severe leaf yellowing and mite webbing on Gale Gala block" />

        {{-- Message / Description --}}
        <x-admin.textarea name="message" label="Detailed Inquiry / Symptoms / Notes" :required="true" :rows="5" placeholder="Describe the plant symptoms, orchard conditions, spray history, or specific questions..." />

        {{-- Attachment Upload --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Attach Diagnostic Photo / Document (Optional)
            </label>
            <input type="file" name="attachment"
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 p-2.5 text-xs text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
        </div>

        {{-- Footer Actions --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <x-admin.button href="{{ route('admin.tickets.index') }}" variant="ghost">
                Cancel
            </x-admin.button>
            <x-admin.button type="submit" variant="primary">
                Create & Open Ticket
            </x-admin.button>
        </div>
    </form>
</div>
@endsection
