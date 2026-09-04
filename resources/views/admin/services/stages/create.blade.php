@extends('admin.layout')

@section('page-title', 'Add Service Stage')

@section('content')
    <div class="space-y-6 max-w-3xl">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Add Stage to {{ $service->name }}</h2>
            <x-admin.button href="{{ route('admin.services.stages.index', $service) }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
        </div>

        <form action="{{ route('admin.services.stages.store', $service) }}" method="POST" class="space-y-6">
            @csrf
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="space-y-5">
                    <x-admin.input name="name" label="Stage Name" :value="old('name')" required placeholder="e.g. Land Layout" />
                    <x-admin.textarea name="description" label="Description" :value="old('description')" rows="3" placeholder="What happens in this stage (optional)" />
                    <x-admin.input name="sort_order" label="Sort Order" type="number" :value="old('sort_order', 0)" helptext="Stages are executed in ascending order." />
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Field Agent Requirements</h3>
                <p class="text-sm text-gray-500 mb-5">What the Field Agent must submit when completing this stage. These will be enforced in the Field Agent App.</p>
                <div class="space-y-4">
                    <div class="flex items-start gap-8 flex-wrap">
                        <div>
                            <x-admin.checkbox name="requires_photo" label="Photo required" :checked="old('requires_photo', false)" help="Agent must upload photos for this stage." />
                        </div>
                        <div>
                            <x-admin.checkbox name="requires_pdf" label="PDF required" :checked="old('requires_pdf', false)" help="Agent must attach a PDF document (e.g. agreement, layout design)." />
                        </div>
                    </div>
                    <x-admin.input name="min_photos" label="Minimum Photos" type="number" :value="old('min_photos', 1)" min="1" max="20" helptext="Only applies when photo is required." />
                </div>
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Add Stage</x-admin.button>
            </div>
        </form>
    </div>
@endsection
