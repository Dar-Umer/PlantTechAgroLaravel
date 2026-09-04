@extends('admin.layout')

@section('page-title', 'Edit Service Item')

@section('content')
    <div class="space-y-6 max-w-3xl">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Edit Item</h2>
            <x-admin.button type="button" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>
                <a href="{{ route('admin.services.items.index', $service) }}">Back</a>
            </x-admin.button>
        </div>

        <form action="{{ route('admin.services.items.update', [$service, $item]) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="space-y-5">
                    <x-admin.input name="name" label="Item Name" :value="old('name', $item->name)" required />
                    <x-admin.textarea name="description" label="Description" :value="old('description', $item->description)" rows="3" />

                    <div>
                        @if($item->image)
                            <p class="text-sm font-medium text-gray-700 mb-1.5">Current Photo</p>
                            <div class="w-32 h-32 rounded-xl border border-gray-200 bg-gray-50 overflow-hidden mb-3">
                                <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                            </div>
                        @endif
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-1.5">Upload New Photo</label>
                        <input type="file" name="image" id="image" accept="image/*"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        <p class="mt-1.5 text-xs text-gray-400">Leave empty to keep the current photo.</p>
                        @error('image')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-admin.input name="sort_order" label="Sort Order" type="number" :value="old('sort_order', $item->sort_order)" />
                </div>
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Update Item</x-admin.button>
            </div>
        </form>
    </div>
@endsection
