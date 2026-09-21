@extends('admin.layout')

@section('page-title', 'Varieties')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Varieties</h2>
                <p class="text-sm text-gray-500 mt-1">Fruit varieties shown on the public Varieties page.</p>
            </div>
            <x-admin.button href="{{ route('admin.varieties.create') }}" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>
                Create Variety
            </x-admin.button>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 font-semibold text-gray-600">Variety</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Category</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Season</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Active</th>
                            <th class="px-6 py-3 font-semibold text-gray-600 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($varieties as $variety)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($variety->image && \App\Support\Media::exists($variety->image))
                                            <img src="{{ \App\Support\Media::url($variety->image) }}" alt="{{ $variety->name }}" class="w-12 h-12 rounded-xl object-cover border border-gray-100 flex-shrink-0">
                                        @else
                                            <div class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4m-4-4l4-4m0 0l-4-4m4 4h-4M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4"/></svg>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="font-medium text-gray-900 truncate">{{ $variety->name }}</p>
                                            @if($variety->taste)
                                                <p class="text-xs text-gray-500 truncate">{{ $variety->taste }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($variety->category)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-50 text-brand-700">{{ $variety->category }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $variety->season ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    @if($variety->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">Active</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700">Hidden</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
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
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        <p class="text-sm">No varieties found.</p>
                                        <a href="{{ route('admin.varieties.create') }}" class="mt-2 text-sm text-brand-600 hover:text-brand-700 font-medium">Create your first variety</a>
                                    </div>
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
