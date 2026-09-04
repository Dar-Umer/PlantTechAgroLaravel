@extends('admin.layout')

@section('page-title', 'Create Testimonial')

@section('content')
    <div class="space-y-6 max-w-3xl">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Create Testimonial</h2>
            <x-admin.button type="button" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>
                <a href="{{ route('admin.testimonials.index') }}">Back</a>
            </x-admin.button>
        </div>

        <form action="{{ route('admin.testimonials.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Testimonial Details</h3>
                <p class="text-sm text-gray-500 mb-5">Who said it and what they said.</p>
                <div class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.input name="name" label="Name" :value="old('name')" required placeholder="e.g. Farooq Ahmad" />
                        <x-admin.input name="role" label="Role" :value="old('role')" placeholder="e.g. Apple Grower, Srinagar" />
                    </div>
                    <x-admin.textarea name="content" label="Testimonial" :value="old('content')" rows="5" required />
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Rating & Display</h3>
                <p class="text-sm text-gray-500 mb-5">Star rating, ordering and visibility.</p>
                <div class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.select name="rating" label="Rating" :options="[1 => '1 Star', 2 => '2 Stars', 3 => '3 Stars', 4 => '4 Stars', 5 => '5 Stars']" :value="(string) old('rating', 5)" />
                        <x-admin.input name="sort_order" label="Sort Order" type="number" :value="old('sort_order', 0)" />
                    </div>
                    <div>
                        <label for="avatar" class="block text-sm font-medium text-gray-700 mb-1.5">Avatar</label>
                        <input type="file" name="avatar" id="avatar" accept="image/*"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        <p class="mt-1.5 text-xs text-gray-400">Optional. Square image works best.</p>
                        @error('avatar')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <x-admin.checkbox name="is_active" label="Active" :checked="old('is_active', true)" help="Visible on the landing page when active." />
                </div>
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Create Testimonial</x-admin.button>
            </div>
        </form>
    </div>
@endsection
