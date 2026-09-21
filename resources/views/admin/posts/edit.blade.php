@extends('admin.layout')

@section('page-title', 'Edit Blog Post')

@section('content')
<div class="space-y-6" x-data="{
    title: '{{ addslashes($post->title) }}',
    slug: '{{ $post->slug }}',
    metaTitle: '{{ addslashes($post->meta_title ?? '') }}',
    metaDesc: '{{ addslashes($post->meta_description ?? '') }}',
    updateSlug() {
        this.slug = this.title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    }
}">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Edit Blog Post</h2>
            <p class="text-sm text-gray-500 mt-1">Update "{{ $post->title }}"</p>
        </div>
        <div class="flex items-center gap-3">
            @if($post->is_published)
                <a href="{{ route('post.show', $post) }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 text-xs font-semibold shadow-2xs transition">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Preview on Site
                </a>
            @endif

            <x-admin.button href="{{ route('admin.posts.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>
                Back to Posts
            </x-admin.button>
        </div>
    </div>

    <form action="{{ route('admin.posts.update', $post) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column: Main Content & SEO --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Article Content --}}
                <div class="bg-white rounded-2xl shadow-2xs border border-gray-100 p-6 space-y-5">
                    <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3">Article Content</h3>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Article Title *</label>
                        <input type="text"
                               name="title"
                               x-model="title"
                               @input="updateSlug()"
                               required
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Short Excerpt / Summary</label>
                        <textarea name="excerpt"
                                  rows="3"
                                  placeholder="A short summary of the post..."
                                  class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">{{ old('excerpt', $post->excerpt) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Full Post Body *</label>
                        <x-admin.textarea id="post-content-editor" name="content" :value="$post->content" placeholder="Write your post content here..." rows="12" />
                    </div>
                </div>

                {{-- SEO Card with Google Preview --}}
                <div class="bg-white rounded-2xl shadow-2xs border border-gray-100 p-6 space-y-5">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 mb-0.5">Search Engine Optimization (SEO)</h3>
                        <p class="text-xs text-gray-400">Control search snippet preview and meta tags.</p>
                    </div>

                    {{-- Live Google Preview Widget --}}
                    <div class="p-4 rounded-2xl bg-gray-50 border border-gray-200 space-y-1">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Google Search Result Preview</span>
                        <div class="text-xs text-emerald-700 font-mono truncate">
                            {{ config('app.url') }}/blog/<span x-text="slug || '{{ $post->slug }}'"></span>
                        </div>
                        <div class="text-base font-medium text-blue-800 hover:underline cursor-pointer truncate"
                             x-text="metaTitle || title || '{{ addslashes($post->title) }}'"></div>
                        <div class="text-xs text-gray-600 line-clamp-2"
                             x-text="metaDesc || 'Meta description snippet will be displayed here.'"></div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Custom Meta Title</label>
                            <input type="text"
                                   name="meta_title"
                                   x-model="metaTitle"
                                   value="{{ old('meta_title', $post->meta_title) }}"
                                   placeholder="Custom title for search engines"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Custom Meta Description</label>
                            <textarea name="meta_description"
                                      x-model="metaDesc"
                                      rows="3"
                                      placeholder="Custom description for search engines..."
                                      class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">{{ old('meta_description', $post->meta_description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Publishing Controls & Cover Image --}}
            <div class="space-y-6">
                {{-- Publishing Card --}}
                <div class="bg-white rounded-2xl shadow-2xs border border-gray-100 p-6 space-y-5">
                    <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3">Publish Settings</h3>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Category *</label>
                        <select name="category_id" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <option value="">Select a category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $post->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Publish Date & Time</label>
                        <input type="datetime-local"
                               name="published_at"
                               value="{{ $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '' }}"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    </div>

                    <div class="pt-2 border-t border-gray-100">
                        <label class="inline-flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="is_published" value="1" {{ old('is_published', $post->is_published) ? 'checked' : '' }} class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-gray-300">
                            <span class="text-sm font-semibold text-gray-900">Published Status</span>
                        </label>
                        <p class="text-xs text-gray-400 mt-0.5">Unchecked articles will be saved as Drafts.</p>
                    </div>
                </div>

                {{-- Featured Cover Image Card --}}
                <div class="bg-white rounded-2xl shadow-2xs border border-gray-100 p-6 space-y-4">
                    <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3">Cover Image</h3>

                    @if($post->featured_image && \App\Support\Media::exists($post->featured_image))
                        <div class="rounded-xl overflow-hidden border border-gray-200 bg-gray-50">
                            <img src="{{ \App\Support\Media::url($post->featured_image) }}" alt="Featured Image" class="w-full h-40 object-cover">
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Replace Cover Image</label>
                        <input type="file" name="featured_image" accept="image/png,image/jpeg,image/webp"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                        <p class="mt-1.5 text-[11px] text-gray-400">Leave blank to keep current cover image.</p>
                    </div>
                </div>

                {{-- Action Submit --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <x-admin.button href="{{ route('admin.posts.index') }}" variant="secondary">
                        Cancel
                    </x-admin.button>

                    <x-admin.button type="submit" variant="primary">
                        Update Article
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
