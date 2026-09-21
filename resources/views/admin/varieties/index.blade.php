@extends('admin.layout')

@section('page-title', 'Varieties Management')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Apple & Orchard Varieties</h2>
            <p class="text-sm text-gray-500 mt-1">Manage fruit varieties, rootstocks, season timings, and taste profiles.</p>
        </div>
        <x-admin.button href="{{ route('admin.varieties.create') }}" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>
            Add New Variety
        </x-admin.button>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-2xs">
        <form method="GET" action="{{ route('admin.varieties.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search variety by name, taste, or season..."
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
            </div>

            <div>
                <select name="category"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm text-gray-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold transition">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'category']))
                    <a href="{{ route('admin.varieties.index') }}" class="px-3 py-2 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-100 text-xs font-medium transition">
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
                        <th class="px-6 py-4">Variety</th>
                        <th class="px-6 py-4">Category</th>
                        <th class="px-6 py-4">Harvest Season</th>
                        <th class="px-6 py-4">Taste & Flavor</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($varieties as $variety)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-12 h-12 rounded-xl bg-gray-100 border border-gray-200 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                        @if($variety->image && \App\Support\Media::exists($variety->image))
                                            <img src="{{ \App\Support\Media::url($variety->image) }}" alt="{{ $variety->name }}" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-6 h-6 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.varieties.edit', $variety) }}" class="font-bold text-gray-900 hover:text-brand-600 transition">
                                            {{ $variety->name }}
                                        </a>
                                        @if($variety->is_featured)
                                            <span class="ml-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Featured</span>
                                        @endif
                                        <p class="text-xs text-gray-400 mt-0.5 line-clamp-1">{{ $variety->short_description ?: 'No summary description.' }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                @if($variety->category)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-50 text-brand-700 border border-brand-100">
                                        {{ $variety->category }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-xs font-medium text-gray-700">
                                {{ $variety->season ?: '—' }}
                            </td>

                            <td class="px-6 py-4 text-xs font-medium text-gray-700">
                                {{ $variety->taste ?: '—' }}
                            </td>

                            <td class="px-6 py-4">
                                <form action="{{ route('admin.varieties.toggle-active', $variety) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    @if($variety->is_active)
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 transition">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                        </button>
                                    @else
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200 transition">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactive
                                        </button>
                                    @endif
                                </form>
                            </td>

                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <x-admin.button href="{{ route('admin.varieties.edit', $variety) }}" variant="secondary" size="sm">Edit</x-admin.button>

                                    <form action="{{ route('admin.varieties.destroy', $variety) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this variety?')">
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
                                <p class="text-gray-500 font-medium">No varieties found.</p>
                                <p class="text-xs text-gray-400 mt-1">Get started by creating your first variety entry.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($varieties->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $varieties->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
