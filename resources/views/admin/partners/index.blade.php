@extends('admin.layout')

@section('page-title', 'Partners Management')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Brand Partners & Affiliates</h2>
            <p class="text-sm text-gray-500 mt-1">Manage partner logos displayed on the front page auto-scrolling marquee.</p>
        </div>
        <x-admin.button href="{{ route('admin.partners.create') }}" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>
            Add Partner
        </x-admin.button>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-2xs">
        <form method="GET" action="{{ route('admin.partners.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="flex-1 w-full">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search partner by name..."
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold transition">
                    Search
                </button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.partners.index') }}" class="px-3 py-2 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-100 text-xs font-medium transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-2xs border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50/80 border-b border-gray-100 text-xs text-gray-500 uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-4">Logo</th>
                        <th class="px-6 py-4">Partner Name</th>
                        <th class="px-6 py-4">Website Link</th>
                        <th class="px-6 py-4">Order</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($partners as $partner)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-6 py-4">
                                <div class="w-16 h-12 rounded-xl bg-gray-50 border border-gray-200 overflow-hidden flex items-center justify-center p-2">
                                    @if($partner->logo && \App\Support\Media::exists($partner->logo))
                                        <img src="{{ \App\Support\Media::url($partner->logo) }}" alt="{{ $partner->name }}" class="max-h-8 max-w-full object-contain">
                                    @else
                                        <span class="text-xs font-bold text-gray-400">No Logo</span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-6 py-4 font-bold text-gray-900">
                                {{ $partner->name }}
                            </td>

                            <td class="px-6 py-4 text-xs text-gray-500">
                                @if($partner->website_url)
                                    <a href="{{ $partner->website_url }}" target="_blank" class="text-brand-600 hover:underline flex items-center gap-1">
                                        {{ Str::limit($partner->website_url, 30) }}
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-xs font-mono font-medium text-gray-600">
                                {{ $partner->sort_order }}
                            </td>

                            <td class="px-6 py-4">
                                <form action="{{ route('admin.partners.toggle-active', $partner) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    @if($partner->is_active)
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 transition">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                        </button>
                                    @else
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200 transition">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Hidden
                                        </button>
                                    @endif
                                </form>
                            </td>

                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <x-admin.button href="{{ route('admin.partners.edit', $partner) }}" variant="secondary" size="sm">Edit</x-admin.button>

                                    <form action="{{ route('admin.partners.destroy', $partner) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this partner?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-admin.button type="submit" variant="danger" size="sm">Delete</x-admin.button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <p class="text-gray-500 font-medium">No partners added yet.</p>
                                <p class="text-xs text-gray-400 mt-1">Add brand partner logos to display them on the home page auto-scrolling marquee.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($partners->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $partners->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
