@extends('admin.layout')

@section('page-title', 'Service Items — ' . $service->name)

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Service Items</h2>
                <p class="text-sm text-gray-500 mt-1">Things included in or required for <span class="font-medium text-gray-700">{{ $service->name }}</span>.</p>
            </div>
            <div class="flex items-center gap-2">
                <x-admin.button type="button" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>
                    <a href="{{ route('admin.services.edit', $service) }}">Back to Service</a>
                </x-admin.button>
                <x-admin.button type="button" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>
                    <a href="{{ route('admin.services.items.create', $service) }}">Add Item</a>
                </x-admin.button>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 font-semibold text-gray-600">Image</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Name</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Description</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Sort Order</th>
                            <th class="px-6 py-3 font-semibold text-gray-600 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($items as $item)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    @if($item->image)
                                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-10 h-10 rounded-lg object-cover border border-gray-200">
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $item->name }}</td>
                                <td class="px-6 py-4 text-gray-600 max-w-xs truncate">{{ $item->description }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $item->sort_order }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <x-admin.button type="button" variant="secondary" size="sm">
                                            <a href="{{ route('admin.services.items.edit', [$service, $item]) }}">Edit</a>
                                        </x-admin.button>
                                        <form action="{{ route('admin.services.items.destroy', [$service, $item]) }}" method="POST" onsubmit="return confirm('Delete this item?')">
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
                                    <p class="text-sm">No items yet. Click "Add Item" to create the first one.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
