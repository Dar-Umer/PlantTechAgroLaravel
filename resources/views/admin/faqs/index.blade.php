@extends('admin.layout')

@section('page-title', 'FAQs')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">FAQs</h2>
                <p class="text-sm text-gray-500 mt-1">Frequently asked questions shown to visitors.</p>
            </div>
            <x-admin.button type="button" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>
                <a href="{{ route('admin.faqs.create') }}">Create FAQ</a>
            </x-admin.button>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 font-semibold text-gray-600">Question</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Category</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Sort Order</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Active</th>
                            <th class="px-6 py-3 font-semibold text-gray-600 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($faqs as $faq)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-gray-900 max-w-md truncate">{{ $faq->question }}</td>
                                <td class="px-6 py-4 text-gray-600">
                                    @if($faq->category)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-50 text-brand-700">{{ $faq->category }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $faq->sort_order }}</td>
                                <td class="px-6 py-4">
                                    @if($faq->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">Active</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700">Hidden</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <x-admin.button type="button" variant="secondary" size="sm">
                                            <a href="{{ route('admin.faqs.edit', $faq) }}">Edit</a>
                                        </x-admin.button>
                                        <form action="{{ route('admin.faqs.destroy', $faq) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this FAQ?')">
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
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p class="text-sm">No FAQs found.</p>
                                        <a href="{{ route('admin.faqs.create') }}" class="mt-2 text-sm text-brand-600 hover:text-brand-700 font-medium">Create your first FAQ</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($faqs->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $faqs->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
