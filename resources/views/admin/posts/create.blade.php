@extends('admin.layout')

@section('page-title', 'Create Post')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Create Post</h2>
            <p class="text-sm text-gray-500 mt-1">Write a new blog post or article.</p>
        </div>
            <x-admin.button href="{{ route('admin.posts.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>
                Back
            </x-admin.button>
    </div>

    <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Content</h3>
                    <p class="text-sm text-gray-500 mb-5">Write your post content.</p>
                    <div class="space-y-5">
                        <x-admin.input name="title" label="Title" placeholder="Enter post title" required />
                        <x-admin.textarea name="excerpt" label="Excerpt" placeholder="A short summary of the post" rows="3" />
                        <x-admin.textarea name="content" label="Content" placeholder="Write your post content here..." rows="12" required />
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">SEO</h3>
                    <p class="text-sm text-gray-500 mb-5">Search engine optimization settings for this post.</p>
                    <div class="space-y-5">
                        <x-admin.input name="meta_title" label="Meta Title" placeholder="Custom title for search engines" />
                        <x-admin.textarea name="meta_description" label="Meta Description" placeholder="Custom description for search engines" rows="3" />
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Publishing</h3>
                    <p class="text-sm text-gray-500 mb-5">Control when and how this post appears.</p>
                    <div class="space-y-5">
                        <x-admin.select name="category_id" label="Category" :options="$categories" placeholder="Select a category" required />
                        <x-admin.input name="published_at" label="Publish Date" type="datetime-local" />
                        <x-admin.checkbox name="is_published" label="Published" :checked="old('is_published', false)" help="Make this post visible on the site" />
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Featured Image</h3>
                    <p class="text-sm text-gray-500 mb-5">Upload a cover image for this post.</p>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Upload Image</label>
                        <input type="file" name="featured_image" accept="image/png,image/jpeg,image/webp"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        <p class="mt-1.5 text-xs text-gray-400">PNG, JPG, or WebP. Recommended 1200x630px.</p>
                        @error('featured_image')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end">
                    <x-admin.button type="submit">Create Post</x-admin.button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
