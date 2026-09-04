@extends('admin.layout')

@section('page-title', 'Suppliers')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Suppliers</h2>
                <p class="text-sm text-gray-500 mt-1">Vendors who supply your materials and receive low stock alerts.</p>
            </div>
            <x-admin.button href="{{ route('admin.suppliers.create') }}" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>
                Add Supplier
            </x-admin.button>
        </div>

        <form method="GET" action="{{ route('admin.suppliers.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by name, contact or phone..."
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
            </div>
            <x-admin.button type="submit" variant="primary">Search</x-admin.button>
            @if(request('q'))
                <a href="{{ route('admin.suppliers.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
            @endif
        </form>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 font-semibold text-gray-600">Name</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Contact</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Phone</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">GST No</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Products</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Status</th>
                            <th class="px-6 py-3 font-semibold text-gray-600 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($suppliers as $supplier)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $supplier->name }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $supplier->contact_person ?: '—' }}@if($supplier->email) <span class="block text-xs text-gray-400">{{ $supplier->email }}</span>@endif</td>
                                <td class="px-6 py-4 text-gray-600">
                                    @if($supplier->phone)
                                        <a href="tel:{{ $supplier->phone }}" class="hover:text-brand-600">{{ $supplier->phone }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $supplier->gst_no ?: '—' }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $supplier->products_count }}</td>
                                <td class="px-6 py-4">
                                    @if($supplier->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">Active</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <x-admin.button href="{{ route('admin.suppliers.edit', $supplier) }}" variant="secondary" size="sm">Edit</x-admin.button>
                                        <form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="POST" onsubmit="return confirm('Delete this supplier?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-admin.button type="submit" variant="danger" size="sm">Delete</x-admin.button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        <p class="text-sm">No suppliers found.</p>
                                        <a href="{{ route('admin.suppliers.create') }}" class="mt-2 text-sm text-brand-600 hover:text-brand-700 font-medium">Add your first supplier</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($suppliers->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $suppliers->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
