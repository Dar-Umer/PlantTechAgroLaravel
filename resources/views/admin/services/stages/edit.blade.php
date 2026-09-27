@extends('admin.layout')

@section('page-title', 'Edit Service Stage')

@section('content')
    <div class="space-y-6 max-w-3xl">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Edit Stage</h2>
            <x-admin.button href="{{ route('admin.services.stages.index', $service) }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
        </div>

        <form action="{{ route('admin.services.stages.update', $stage) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="space-y-5">
                    <x-admin.input name="name" label="Stage Name" :value="old('name', $stage->name)" required />
                    <x-admin.textarea name="description" label="Description" :value="old('description', $stage->description)" rows="3" />
                    <x-admin.input name="sort_order" label="Sort Order" type="number" :value="old('sort_order', $stage->sort_order)" />
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Field Agent Requirements</h3>
                <p class="text-sm text-gray-500 mb-5">What the Field Agent must submit when completing this stage.</p>
                <div class="space-y-4">
                    <div class="flex items-start gap-8 flex-wrap">
                        <div>
                            <x-admin.checkbox name="requires_photo" label="Photo required" :checked="old('requires_photo', $stage->requires_photo)" />
                        </div>
                        <div>
                            <x-admin.checkbox name="requires_pdf" label="PDF required" :checked="old('requires_pdf', $stage->requires_pdf)" />
                        </div>
                    </div>
                    <x-admin.input name="min_photos" label="Minimum Photos" type="number" :value="old('min_photos', $stage->min_photos)" min="1" max="20" />
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6" x-data="{ notifyCustomer: {{ old('notify_customer', $stage->notify_customer ?? true) ? 'true' : 'false' }} }">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-semibold text-gray-900">Push Notification (Firebase)</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Mobile App
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mt-0.5">Send a real-time push notification with photo attachments to the farmer upon stage completion.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <x-admin.checkbox name="notify_customer" label="Notify Customer when stage is completed" :checked="old('notify_customer', $stage->notify_customer ?? true)" help="Delivers instant alert to Farmer Mobile App via Firebase Cloud Messaging" @change="notifyCustomer = $event.target.checked" />

                    <div x-show="notifyCustomer" x-cloak x-collapse class="space-y-4 pt-2 border-t border-gray-100">
                        <x-admin.input name="notification_title" label="Custom Notification Title (Optional)" :value="old('notification_title', $stage->notification_title)" placeholder="e.g. Stage Completed: {stage_name}" helptext="Available tags: {stage_name}, {work_order_number}, {service_name}, {orchard_name}, {customer_name}" />

                        <x-admin.textarea name="notification_body" label="Custom Notification Message (Optional)" :value="old('notification_body', $stage->notification_body)" rows="2" placeholder="e.g. Work for {stage_name} has been finished on your orchard." helptext="Leave empty to use the system default notification template." />
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Update Stage</x-admin.button>
            </div>
        </form>
    </div>
@endsection
