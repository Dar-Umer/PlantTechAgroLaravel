@extends('admin.layout')

@section('page-title', 'Customers')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Customers</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $totalCustomers }} total · {{ $activeCustomers }} active. Customers log into the Customer App with phone + password.</p>
            </div>
            <x-admin.button type="button" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>
                <a href="{{ route('admin.customers.create') }}">Add Customer</a>
            </x-admin.button>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.customers.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by name, phone or email..."
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
            </div>
            <div>
                <select name="status" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <x-admin.button type="submit" variant="primary">Filter</x-admin.button>
            @if(request('q') || request('status'))
                <a href="{{ route('admin.customers.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
            @endif
        </form>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 font-semibold text-gray-600">Name</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Phone</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Area</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Status</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Added</th>
                            <th class="px-6 py-3 font-semibold text-gray-600 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($customers as $customer)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    <a href="{{ route('admin.customers.show', $customer) }}" class="hover:text-brand-600">{{ $customer->name }}</a>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $customer->phone }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $customer->area ?: '—' }}</td>
                                <td class="px-6 py-4">
                                    @if($customer->status === 'active')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">Active</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-500 text-xs">{{ $customer->created_at->format('d M Y') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <x-admin.button type="button" variant="secondary" size="sm">
                                            <a href="{{ route('admin.customers.edit', $customer) }}">Edit</a>
                                        </x-admin.button>
                                        <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Delete this customer? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <x-admin.button type="submit" variant="danger" size="sm">Delete</x-admin.button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        <p class="text-sm">No customers yet.</p>
                                        <a href="{{ route('admin.customers.create') }}" class="mt-2 text-sm text-brand-600 hover:text-brand-700 font-medium">Add your first customer</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($customers->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
