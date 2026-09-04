@extends('admin.layout')

@section('page-title', 'Staff')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Staff</h2>
                <p class="text-sm text-gray-500 mt-1">Admins, Managers and Field Agents. Staff log into the admin panel; Field Agents will use the Agent App.</p>
            </div>
            <x-admin.button href="{{ route('admin.staff.create') }}" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>Add Staff</x-admin.button>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 font-semibold text-gray-600">Name</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Email</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Phone</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Role</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Status</th>
                            <th class="px-6 py-3 font-semibold text-gray-600 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($staff as $member)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $member->name }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $member->email }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $member->phone ?: '—' }}</td>
                                <td class="px-6 py-4">
                                    @foreach($member->roles as $role)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $role->name === 'Super Admin' ? 'bg-purple-50 text-purple-700' : ($role->name === 'Manager' ? 'bg-blue-50 text-blue-700' : 'bg-brand-50 text-brand-700') }}">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td class="px-6 py-4">
                                    @if($member->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">Active</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <x-admin.button href="{{ route('admin.staff.edit', $member) }}" variant="secondary" size="sm">Edit</x-admin.button>
                                        @if($member->id !== auth('admin')->id())
                                            <form action="{{ route('admin.staff.destroy', $member) }}" method="POST" onsubmit="return confirm('Delete this staff member?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-admin.button type="submit" variant="danger" size="sm">Delete</x-admin.button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        <p class="text-sm">No staff found.</p>
                                        <a href="{{ route('admin.staff.create') }}" class="mt-2 text-sm text-brand-600 hover:text-brand-700 font-medium">Add your first team member</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($staff->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $staff->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
