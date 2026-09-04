@extends('admin.layout')

@section('page-title', 'Add Service Item')

@section('content')
    <div class="space-y-6 max-w-3xl">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Add Item to {{ $service->name }}</h2>
            <x-admin.button href="{{ route('admin.services.items.index', $service) }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
        </div>

        <form action="{{ route('admin.services.items.store', $service) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="space-y-5">
                    <x-admin.input name="name" label="Item Name" :value="old('name')" required placeholder="e.g. M9-T337 Apple Plants" />
                    <x-admin.textarea name="description" label="Description" :value="old('description')" rows="3" placeholder="Details about this item (optional)" />

                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-1.5">Photo</label>
                        <input type="file" name="image" id="image" accept="image/*"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        <p class="mt-1.5 text-xs text-gray-400">Optional. PNG, JPG, or WebP. Max 2MB.</p>
                        @error('image')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-admin.input name="sort_order" label="Sort Order" type="number" :value="old('sort_order', 0)" />
                </div>
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Add Item</x-admin.button>
            </div>
        </form>
    </div>
@endsection
