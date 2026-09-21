@extends('admin.layout')

@section('page-title', 'Create Variety')

@section('content')
    <div class="space-y-6 max-w-3xl">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Create Variety</h2>
            <x-admin.button href="{{ route('admin.varieties.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
        </div>

        <form action="{{ route('admin.varieties.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Variety Details</h3>
                <p class="text-sm text-gray-500 mb-5">Core information about this variety.</p>
                <div class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.input name="name" label="Name" :value="old('name')" required placeholder="e.g. Red Delicious" />
                        <x-admin.input name="category" label="Category" :value="old('category')" placeholder="e.g. Apple" />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.input name="season" label="Season" :value="old('season')" placeholder="e.g. Early / Late" />
                        <x-admin.input name="taste" label="Taste" :value="old('taste')" placeholder="e.g. Sweet, crisp" />
                    </div>
                    <x-admin.textarea name="short_description" label="Short Description" :value="old('short_description')" rows="3" placeholder="A short summary shown on the varieties page." />
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Image & Display</h3>
                <p class="text-sm text-gray-500 mb-5">Photo and front-end visibility.</p>
                <div class="space-y-5">
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-1.5">Variety Image</label>
                        <input type="file" name="image" id="image" accept="image/*"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        <p class="mt-1.5 text-xs text-gray-400">Optional. Recommended 800x600 or larger.</p>
                        @error('image')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.input name="sort_order" label="Sort Order" type="number" :value="old('sort_order', 0)" />
                        <div class="pt-6">
                            <x-admin.checkbox name="is_active" label="Active" :checked="old('is_active', true)" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Create Variety</x-admin.button>
            </div>
        </form>
    </div>
@endsection
