@extends('admin.layout')

@section('page-title', 'Edit Variety')

@section('content')
    <div class="space-y-6 max-w-3xl">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Edit Variety</h2>
            <x-admin.button href="{{ route('admin.varieties.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
        </div>

        <form action="{{ route('admin.varieties.update', $variety) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Variety Details</h3>
                <p class="text-sm text-gray-500 mb-5">Core information about this variety.</p>
                <div class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.input name="name" label="Name" :value="old('name', $variety->name)" required />
                        <x-admin.input name="category" label="Category" :value="old('category', $variety->category)" />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.input name="season" label="Season" :value="old('season', $variety->season)" />
                        <x-admin.input name="taste" label="Taste" :value="old('taste', $variety->taste)" />
                    </div>
                    <x-admin.textarea name="short_description" label="Short Description" :value="old('short_description', $variety->short_description)" rows="3" />
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Image & Display</h3>
                <p class="text-sm text-gray-500 mb-5">Photo and front-end visibility.</p>
                <div class="space-y-5">
                    @if($variety->image)
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-1.5">Current Image</p>
                            <img src="{{ \App\Support\Media::url($variety->image) }}" alt="{{ $variety->name }}" class="w-40 h-32 rounded-xl border border-gray-200 object-cover">
                        </div>
                    @endif
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-1.5">Replace Image</label>
                        <input type="file" name="image" id="image" accept="image/*"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        <p class="mt-1.5 text-xs text-gray-400">Leave empty to keep the current image.</p>
                        @error('image')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.input name="sort_order" label="Sort Order" type="number" :value="old('sort_order', $variety->sort_order)" />
                        <div class="pt-6">
                            <x-admin.checkbox name="is_active" label="Active" :checked="old('is_active', $variety->is_active)" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Update Variety</x-admin.button>
            </div>
        </form>
    </div>
@endsection
