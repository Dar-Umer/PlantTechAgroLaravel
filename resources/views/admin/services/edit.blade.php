@extends('admin.layout')

@section('page-title', 'Edit Service')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Edit Service</h2>
            <x-admin.button href="{{ route('admin.services.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a href="{{ route('admin.services.items.index', $service) }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4 hover:border-brand-300 hover:shadow transition group">
                <div class="w-11 h-11 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2H5a2 2 0 00-2 2v2m14 0h.01M5 11h.01"/></svg>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-gray-900 group-hover:text-brand-700 transition">Service Items</p>
                    <p class="text-sm text-gray-500">{{ $service->items()->count() }} item(s) — things included in or required for this service</p>
                </div>
                <svg class="w-5 h-5 text-gray-300 group-hover:text-brand-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            <a href="{{ route('admin.services.stages.index', $service) }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4 hover:border-brand-300 hover:shadow transition group">
                <div class="w-11 h-11 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-gray-900 group-hover:text-brand-700 transition">Service Stages</p>
                    <p class="text-sm text-gray-500">{{ $service->stages()->count() }} stage(s) — the step-by-step workflow from start to completion</p>
                </div>
                <svg class="w-5 h-5 text-gray-300 group-hover:text-brand-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <form action="{{ route('admin.services.update', $service) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Basic Information</h3>
                <p class="text-sm text-gray-500 mb-5">Core details about the service.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-admin.input name="name" label="Name" :value="old('name', $service->name)" required />
                    <x-admin.input name="category" label="Category" :value="old('category', $service->category)" placeholder="e.g. orchard-development, soil-health-management" required />
                </div>
                <div class="mt-5">
                    <x-admin.textarea name="description" label="Description" :value="old('description', $service->description)" rows="3" />
                </div>
                <div class="mt-5">
                    <x-admin.textarea name="content" label="Content" :value="old('content', $service->content)" rows="6" />
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Media & Links</h3>
                <p class="text-sm text-gray-500 mb-5">Optional media and external links.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-admin.input name="icon" label="Icon" :value="old('icon', $service->icon)" placeholder="Icon class or SVG reference" />
                    <x-admin.input name="book_url" label="Book URL" :value="old('book_url', $service->book_url)" placeholder="https://..." />
                </div>
                <div class="mt-5" x-data="{ preview: '{{ $service->image ? asset('storage/' . $service->image) : '' }}', hasImage: '{{ $service->image ? 'true' : 'false' }}' === 'true' }">
                    @if($service->image)
                        <div class="mb-3">
                            <p class="text-sm font-medium text-gray-700 mb-1.5">Current Image</p>
                            <div class="w-32 h-32 rounded-xl border border-gray-200 bg-gray-50 overflow-hidden">
                                <img src="{{ asset('storage/' . $service->image) }}" alt="{{ $service->name }}" class="w-full h-full object-cover">
                            </div>
                        </div>
                    @endif
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1.5">Upload New Image</label>
                    <input type="file"
                           name="image"
                           id="image"
                           accept="image/*"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    <p class="mt-1.5 text-xs text-gray-400">PNG, JPG, or WebP. Max 5MB. Leave empty to keep current image.</p>
                    @error('image')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Display Settings</h3>
                <p class="text-sm text-gray-500 mb-5">Control the order and visibility of this service.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-admin.input name="sort_order" label="Sort Order" type="number" :value="old('sort_order', $service->sort_order)" />
                    <x-admin.checkbox name="is_active" label="Active" :checked="old('is_active', $service->is_active)" help="Visible on the frontend when active." />
                </div>
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Update Service</x-admin.button>
            </div>
        </form>
    </div>
@endsection
