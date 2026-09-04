@extends('admin.layout')

@section('page-title', 'Create Service')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Create Service</h2>
            <x-admin.button type="button" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>
                <a href="{{ route('admin.services.index') }}">Back</a>
            </x-admin.button>
        </div>

        <form action="{{ route('admin.services.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Basic Information</h3>
                <p class="text-sm text-gray-500 mb-5">Core details about the service.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-admin.input name="name" label="Name" :value="old('name')" required />
                    <x-admin.input name="category" label="Category" :value="old('category')" placeholder="e.g. orchard-development, soil-health-management" required />
                </div>
                <div class="mt-5">
                    <x-admin.textarea name="description" label="Description" :value="old('description')" rows="3" />
                </div>
                <div class="mt-5">
                    <x-admin.textarea name="content" label="Content" :value="old('content')" rows="6" />
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Media & Links</h3>
                <p class="text-sm text-gray-500 mb-5">Optional media and external links.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-admin.input name="icon" label="Icon" :value="old('icon')" placeholder="Icon class or SVG reference" />
                    <x-admin.input name="book_url" label="Book URL" :value="old('book_url')" placeholder="https://..." />
                </div>
                <div class="mt-5">
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1.5">Image</label>
                    <input type="file"
                           name="image"
                           id="image"
                           accept="image/*"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    <p class="mt-1.5 text-xs text-gray-400">PNG, JPG, or WebP. Max 5MB.</p>
                    @error('image')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Display Settings</h3>
                <p class="text-sm text-gray-500 mb-5">Control the order and visibility of this service.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-admin.input name="sort_order" label="Sort Order" type="number" :value="old('sort_order', 0)" />
                    <x-admin.checkbox name="is_active" label="Active" :checked="old('is_active', true)" help="Visible on the frontend when active." />
                </div>
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Create Service</x-admin.button>
            </div>
        </form>
    </div>
@endsection
