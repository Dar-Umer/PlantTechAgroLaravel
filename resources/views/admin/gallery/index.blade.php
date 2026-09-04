@extends('admin.layout')

@section('page-title', 'Gallery')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Gallery</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $images->count() }} of {{ \App\Models\GalleryImage::count() }} images shown · images appear on the landing page gallery.</p>
            </div>
        </div>

        {{-- Upload --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-1">Upload Image</h3>
            <p class="text-sm text-gray-500 mb-5">Add a new photo to the website gallery.</p>
            <form action="{{ route('admin.gallery.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-1.5">Image <span class="text-red-500">*</span></label>
                        <input type="file" name="image" id="image" required accept="image/*"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        @error('image')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <x-admin.input name="caption" label="Caption" :value="old('caption')" placeholder="Short caption" />
                    <x-admin.input name="category" label="Category" :value="old('category')" placeholder="e.g. Orchards" />
                </div>
                <div class="mt-5 flex justify-end">
                    <x-admin.button type="submit">Upload Image</x-admin.button>
                </div>
            </form>
        </div>

        {{-- Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @forelse($images as $image)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden group relative">
                    <div class="aspect-square bg-gray-50">
                        <img src="{{ asset('storage/' . $image->image) }}" alt="{{ $image->caption ?? 'Gallery image' }}" class="w-full h-full object-cover">
                    </div>
                    <div class="p-3">
                        @if($image->caption)
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $image->caption }}</p>
                        @endif
                        @if($image->category)
                            <span class="inline-flex items-center mt-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-brand-50 text-brand-700">{{ $image->category }}</span>
                        @endif
                    </div>
                    <form action="{{ route('admin.gallery.destroy', $image) }}" method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this image?')"
                          class="absolute top-2 right-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" title="Delete image"
                                class="bg-red-600 hover:bg-red-700 text-white w-8 h-8 rounded-full flex items-center justify-center shadow opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-6 py-12 text-center text-gray-500">
                        <p class="text-sm">No gallery images yet. Upload your first image above.</p>
                    </div>
                </div>
            @endforelse
        </div>

        @if($images->hasPages())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-6 py-4">
                {{ $images->links() }}
            </div>
        @endif
    </div>
@endsection
