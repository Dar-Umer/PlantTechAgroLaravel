@extends('admin.layout')

@section('page-title', 'Create Variety')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Add New Variety</h2>
            <p class="text-sm text-gray-500 mt-1">Create a fruit variety or rootstock entry for the public catalogue.</p>
        </div>
        <x-admin.button href="{{ route('admin.varieties.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>
            Back to Varieties
        </x-admin.button>
    </div>

    <form action="{{ route('admin.varieties.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                {{-- Main Specs --}}
                <div class="bg-white rounded-2xl shadow-2xs border border-gray-100 p-6 space-y-5">
                    <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3">Variety Details</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Variety Name *</label>
                            <input type="text" name="name" required placeholder="e.g. Jeromine / Red Velox / Gala Schniga" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Category</label>
                            <input type="text" name="category" placeholder="e.g. Red Apples / Gala / Rootstocks" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Harvest Season</label>
                            <input type="text" name="season" placeholder="e.g. Early August / Mid September" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Taste & Profile</label>
                            <input type="text" name="taste" placeholder="e.g. Sweet, Crisp & Juicy" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Country / Origin</label>
                            <input type="text" name="origin" placeholder="e.g. France / Italy / USA" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Color & Skin</label>
                            <input type="text" name="color" placeholder="e.g. Intense Dark Red / Crimson" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Storage Life</label>
                            <input type="text" name="storage_life" placeholder="e.g. 6-8 Months (High Shelf Life)" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Short Summary Description</label>
                        <textarea name="short_description" rows="3" placeholder="A short introduction for catalogue cards..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Detailed Description & Growth Characteristics</label>
                        <x-admin.textarea id="variety-desc-editor" name="description" placeholder="Full growth specs, pollination requirements, yield details..." rows="8" />
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                {{-- Image & Settings --}}
                <div class="bg-white rounded-2xl shadow-2xs border border-gray-100 p-6 space-y-5">
                    <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3">Cover Image</h3>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Upload Image</label>
                        <input type="file" name="image" accept="image/png,image/jpeg,image/webp"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-2xs border border-gray-100 p-6 space-y-4">
                    <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3">Visibility & Order</h3>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Sort Order</label>
                        <input type="number" name="sort_order" value="0" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-900">
                    </div>

                    <div class="space-y-2 pt-2">
                        <label class="inline-flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-gray-300">
                            <span class="text-sm font-semibold text-gray-900">Active (Visible on website)</span>
                        </label>

                        <label class="inline-flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1" class="w-4 h-4 rounded text-amber-600 focus:ring-amber-500 border-gray-300">
                            <span class="text-sm font-semibold text-gray-900">Featured Variety</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <x-admin.button href="{{ route('admin.varieties.index') }}" variant="secondary">
                        Cancel
                    </x-admin.button>

                    <x-admin.button type="submit" variant="primary">
                        Save Variety
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
        .create(document.querySelector('#variety-desc-editor'), {
            toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|', 'undo', 'redo' ]
        })
        .catch(error => {
            console.error(error);
        });
</script>
@endpush
@endsection
