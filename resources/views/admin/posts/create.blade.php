@extends('admin.layout')

@section('page-title', 'Create Blog Post')

@section('content')
<div class="space-y-6" x-data="{
    title: '',
    slug: '',
    metaTitle: '',
    metaDesc: '',
    updateSlug() {
        this.slug = this.title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    }
}">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Create Blog Post</h2>
            <p class="text-sm text-gray-500 mt-1">Write, format, and optimize your blog article.</p>
        </div>
        <x-admin.button href="{{ route('admin.posts.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>
            Back to Posts
        </x-admin.button>
    </div>

    <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column: Main Editor & SEO --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Content Card --}}
                <div class="bg-white rounded-2xl shadow-2xs border border-gray-100 p-6 space-y-5">
                    <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3">Article Content</h3>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Article Title *</label>
                        <input type="text"
                               name="title"
                               x-model="title"
                               @input="updateSlug()"
                               placeholder="e.g. 10 Benefits of Agriculture Drones in Modern Farming"
                               required
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Short Excerpt / Summary</label>
                        <textarea name="excerpt"
                                  rows="3"
                                  placeholder="Provide a brief introductory summary of this article..."
                                  class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Full Post Body *</label>
                        <x-admin.textarea id="post-content-editor" name="content" placeholder="Write your post content here..." rows="12" />
                    </div>
                </div>

                {{-- SEO Card with Google Snippet Preview --}}
                <div class="bg-white rounded-2xl shadow-2xs border border-gray-100 p-6 space-y-5">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 mb-0.5">Search Engine Optimization (SEO)</h3>
                        <p class="text-xs text-gray-400">Control how this post appears on Google and social media.</p>
                    </div>

                    {{-- Live Google Preview Widget --}}
                    <div class="p-4 rounded-2xl bg-gray-50 border border-gray-200 space-y-1">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Google Search Result Preview</span>
                        <div class="text-xs text-emerald-700 font-mono truncate">
                            {{ config('app.url') }}/blog/<span x-text="slug || 'your-article-slug'"></span>
                        </div>
                        <div class="text-base font-medium text-blue-800 hover:underline cursor-pointer truncate"
                             x-text="metaTitle || title || 'Title of your blog post will appear here'"></div>
                        <div class="text-xs text-gray-600 line-clamp-2"
                             x-text="metaDesc || 'Meta description will be automatically generated from the excerpt or title if left empty.'"></div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Custom Meta Title</label>
                            <input type="text"
                                   name="meta_title"
                                   x-model="metaTitle"
                                   placeholder="Custom title for search engines (defaults to article title)"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Custom Meta Description</label>
                            <textarea name="meta_description"
                                      x-model="metaDesc"
                                      rows="3"
                                      placeholder="Custom description for search engine result snippet..."
                                      class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Publishing Controls & Media --}}
            <div class="space-y-6">
                {{-- Publishing Card --}}
                <div class="bg-white rounded-2xl shadow-2xs border border-gray-100 p-6 space-y-5">
                    <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3">Publish Settings</h3>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Category *</label>
                        <select name="category_id" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <option value="">Select a category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Publish Date & Time</label>
                        <input type="datetime-local" name="published_at" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        <p class="mt-1 text-[11px] text-gray-400">Leave blank to publish immediately upon checking Published.</p>
                    </div>

                    <div class="pt-2 border-t border-gray-100">
                        <label class="inline-flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="is_published" value="1" {{ old('is_published') ? 'checked' : '' }} class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-gray-300">
                            <span class="text-sm font-semibold text-gray-900">Publish Immediately</span>
                        </label>
                        <p class="text-xs text-gray-400 mt-0.5">Unchecked articles will save as Drafts.</p>
                    </div>
                </div>

                {{-- Featured Cover Image Card --}}
                <div class="bg-white rounded-2xl shadow-2xs border border-gray-100 p-6 space-y-4">
                    <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3">Cover Image</h3>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Upload Cover Image</label>
                        <input type="file" name="featured_image" accept="image/png,image/jpeg,image/webp"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                        <p class="mt-1.5 text-[11px] text-gray-400">Recommended size: 1200x630 px (PNG, JPG, or WebP).</p>
                    </div>
                </div>

                {{-- Action Submit --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <x-admin.button href="{{ route('admin.posts.index') }}" variant="secondary">
                        Cancel
                    </x-admin.button>

                    <x-admin.button type="submit" variant="primary">
                        Save Article
                    </x-admin.button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('#post-content-editor'), {
            toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|', 'undo', 'redo' ]
        })
        .then(editor => {
            editor.ui.view.editable.element.style.minHeight = '360px';
            editor.ui.view.editable.element.classList.add('prose', 'max-w-none');
        })
        .catch(error => {
            console.error(error);
        });
</script>
<style>
    .ck-editor__editable_inline {
        min-height: 380px;
        border-bottom-left-radius: 0.75rem !important;
        border-bottom-right-radius: 0.75rem !important;
    }
    .ck-toolbar {
        border-top-left-radius: 0.75rem !important;
        border-top-right-radius: 0.75rem !important;
        background: #f9fafb !important;
        border-color: #e5e7eb !important;
    }
    .ck.ck-editor__main>.ck-editor__editable {
        border-color: #e5e7eb !important;
    }
    .ck.ck-editor__main>.ck-editor__editable.ck-focused {
        border-color: #10b981 !important;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2) !important;
    }
</style>
@endpush
@endsection
