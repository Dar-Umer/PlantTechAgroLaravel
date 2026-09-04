@extends('admin.layout')

@section('page-title', 'Edit Post')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Edit Post</h2>
            <p class="text-sm text-gray-500 mt-1">Update "{{ $post->title }}"</p>
        </div>
        <a href="{{ route('admin.posts.index') }}">
            <x-admin.button type="button" variant="secondary">
                <svg class="w-4 h-4 mr-1.5 -ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back
            </x-admin.button>
        </a>
    </div>

    <form action="{{ route('admin.posts.update', $post) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Content</h3>
                    <p class="text-sm text-gray-500 mb-5">Write your post content.</p>
                    <div class="space-y-5">
                        <x-admin.input name="title" label="Title" :value="$post->title" placeholder="Enter post title" required />
                        <x-admin.textarea name="excerpt" label="Excerpt" :value="$post->excerpt" placeholder="A short summary of the post" rows="3" />
                        <x-admin.textarea name="content" label="Content" :value="$post->content" placeholder="Write your post content here..." rows="12" required />
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">SEO</h3>
                    <p class="text-sm text-gray-500 mb-5">Search engine optimization settings for this post.</p>
                    <div class="space-y-5">
                        <x-admin.input name="meta_title" label="Meta Title" :value="$post->meta_title" placeholder="Custom title for search engines" />
                        <x-admin.textarea name="meta_description" label="Meta Description" :value="$post->meta_description" placeholder="Custom description for search engines" rows="3" />
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Publishing</h3>
                    <p class="text-sm text-gray-500 mb-5">Control when and how this post appears.</p>
                    <div class="space-y-5">
                        <x-admin.select name="category_id" label="Category" :options="$categories" :value="$post->category_id" placeholder="Select a category" required />
                        <x-admin.input name="published_at" label="Publish Date" type="datetime-local" :value="$post->published_at ? $post->published_at->format('Y-m-d\TH:i') : ''" />
                        <x-admin.checkbox name="is_published" label="Published" :checked="$post->is_published" help="Make this post visible on the site" />
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Featured Image</h3>
                    <p class="text-sm text-gray-500 mb-5">Upload a cover image for this post.</p>
                    <div x-data="{ preview: '{{ $post->featured_image ? asset('storage/' . $post->featured_image) : '' }}', hasImage: '{{ $post->featured_image ? 'true' : 'false' }}' === 'true' }">
                        @if($post->featured_image)
                            <div class="mb-3">
                                <div class="w-full h-40 rounded-xl border border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden">
                                    <img src="{{ asset('storage/' . $post->featured_image) }}" alt="Current featured image" class="w-full h-full object-cover">
                                </div>
                                <p class="mt-1.5 text-xs text-gray-400">Current featured image.</p>
                            </div>
                        @endif
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Replace Image</label>
                            <input type="file" name="featured_image" accept="image/png,image/jpeg,image/webp"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <p class="mt-1.5 text-xs text-gray-400">PNG, JPG, or WebP. Recommended 1200x630px. Leave blank to keep current image.</p>
                            @error('featured_image')
                                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <x-admin.button type="submit">Update Post</x-admin.button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
