@extends('admin.layout')

@section('page-title', 'Create Project')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Create Project</h2>
            <x-admin.button type="button" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>
                <a href="{{ route('admin.projects.index') }}">Back</a>
            </x-admin.button>
        </div>

        <form action="{{ route('admin.projects.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Project Details</h3>
                <p class="text-sm text-gray-500 mb-5">Core information about the project.</p>
                <div class="space-y-5">
                    <x-admin.input name="title" label="Title" :value="old('title')" required placeholder="e.g. High Density Orchard — Pulwama" />
                    <x-admin.input name="location" label="Location" :value="old('location')" placeholder="e.g. Pulwama, J&K" />
                    <x-admin.textarea name="description" label="Description" :value="old('description')" rows="3" />
                    <x-admin.textarea name="content" label="Content" :value="old('content')" rows="6" />
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Media & Completion</h3>
                <p class="text-sm text-gray-500 mb-5">Featured image and completion details.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-admin.input name="completed_at" label="Completed At" type="date" :value="old('completed_at')" />
                    <div>
                        <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-1.5">Featured Image</label>
                        <input type="file" name="featured_image" id="featured_image" accept="image/*"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        <p class="mt-1.5 text-xs text-gray-400">PNG, JPG, or WebP. Max 2MB.</p>
                        @error('featured_image')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Display Settings</h3>
                <p class="text-sm text-gray-500 mb-5">Control the visibility of this project.</p>
                <div class="flex items-center gap-8 flex-wrap">
                    <x-admin.checkbox name="is_featured" label="Featured" :checked="old('is_featured', false)" help="Shown in the Featured Projects section." />
                    <x-admin.checkbox name="is_published" label="Published" :checked="old('is_published', false)" help="Visible on the landing page." />
                </div>
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Create Project</x-admin.button>
            </div>
        </form>
    </div>
@endsection
