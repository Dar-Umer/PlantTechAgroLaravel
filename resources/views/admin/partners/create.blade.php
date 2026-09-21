@extends('admin.layout')

@section('page-title', 'Create Partner')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Add Partner</h2>
            <p class="text-sm text-gray-500 mt-1">Add a brand or affiliate logo to the front page scrolling banner.</p>
        </div>
        <x-admin.button href="{{ route('admin.partners.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>
            Back to Partners
        </x-admin.button>
    </div>

    <form action="{{ route('admin.partners.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 max-w-2xl">
        @csrf

        <div class="bg-white rounded-2xl shadow-2xs border border-gray-100 p-6 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Partner / Company Name *</label>
                <input type="text" name="name" required placeholder="e.g. Bayer CropScience / AgriTech Global" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Website URL (Optional)</label>
                <input type="url" name="website_url" placeholder="https://example.com" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Partner Logo</label>
                <input type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                <p class="mt-1 text-[11px] text-gray-400">PNG, SVG, or WebP with transparent background is recommended.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Sort Order</label>
                <input type="number" name="sort_order" value="0" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
            </div>

            <div class="pt-2 border-t border-gray-100">
                <label class="inline-flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-gray-300">
                    <span class="text-sm font-semibold text-gray-900">Active (Show on home page scrolling banner)</span>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <x-admin.button href="{{ route('admin.partners.index') }}" variant="secondary">
                Cancel
            </x-admin.button>

            <x-admin.button type="submit" variant="primary">
                Save Partner
            </x-admin.button>
        </div>
    </form>
</div>
@endsection
