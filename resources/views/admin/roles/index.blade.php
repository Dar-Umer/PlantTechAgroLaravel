@extends('admin.layout')

@section('page-title', 'Role & Permission Management')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Roles & Permissions</h2>
                <p class="text-sm text-gray-500 mt-1">Define fine-grained access control across operations, POS, inventory, finance, CMS, and system administration.</p>
            </div>
            <div class="flex items-center gap-3">
                <x-admin.button href="{{ route('admin.roles.create') }}" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>
                    Add Custom Role
                </x-admin.button>
            </div>
        </div>

        <!-- Metrics Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-medium uppercase tracking-wider text-gray-400">Total Roles</span>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $roles->count() }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Presets & custom profiles</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-medium uppercase tracking-wider text-gray-400">System Permissions</span>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $totalPermissions }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Across 7 business modules</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-medium uppercase tracking-wider text-gray-400">Staff Assigned</span>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $roles->sum('users_count') }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Active staff accounts mapped</p>
                </div>
            </div>
        </div>

        <!-- Roles Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Configured Roles</h3>
                <span class="text-xs text-gray-500">Guard: <code class="px-1.5 py-0.5 bg-gray-100 text-gray-700 rounded">admin</code></span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 font-semibold text-gray-600">Role Name</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Type</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Permissions Scope</th>
                            <th class="px-6 py-3 font-semibold text-gray-600 text-center">Assigned Staff</th>
                            <th class="px-6 py-3 font-semibold text-gray-600 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($roles as $role)
                            @php
                                $permPct = $totalPermissions > 0 ? min(100, round(($role->permissions_count / $totalPermissions) * 100)) : 0;
                                if ($role->is_super) {
                                    $permPct = 100;
                                }
                            @endphp
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm
                                            {{ $role->is_super ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700' }}">
                                            {{ strtoupper(substr($role->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <span class="font-semibold text-gray-900">{{ $role->name }}</span>
                                            @if($role->is_super)
                                                <span class="block text-xs text-purple-600 font-medium">Unrestricted Master Access</span>
                                            @else
                                                <span class="block text-xs text-gray-400">Custom Authorization Policy</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if(in_array($role->name, ['Super Admin', 'Manager', 'POS & Stock Operator', 'Field Agent', 'Accountant', 'Content Editor']))
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                            System Default
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            Custom Role
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1.5 max-w-xs">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="font-medium text-gray-700">
                                                @if($role->is_super)
                                                    All {{ $totalPermissions }} Permissions (100%)
                                                @else
                                                    {{ $role->permissions_count }} of {{ $totalPermissions }} permissions
                                                @endif
                                            </span>
                                            <span class="text-gray-400 font-mono">{{ $permPct }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                            <div class="h-1.5 rounded-full {{ $role->is_super ? 'bg-purple-600' : ($permPct > 50 ? 'bg-brand-600' : 'bg-amber-500') }}"
                                                 style="width: {{ $permPct }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('admin.staff.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        {{ $role->users_count }} staff
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <x-admin.button href="{{ route('admin.roles.edit', $role) }}" variant="secondary" size="sm">
                                            {{ $role->is_super ? 'View Matrix' : 'Edit Matrix' }}
                                        </x-admin.button>

                                        @if($role->is_super)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium text-gray-400 bg-gray-50 cursor-not-allowed border border-gray-200" title="Super Admin is protected and cannot be deleted">
                                                Locked
                                            </span>
                                        @elseif($role->users_count > 0)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium text-gray-400 bg-gray-50 cursor-not-allowed border border-gray-200" title="Assigned to active staff members. Reassign them to delete this role.">
                                                In Use
                                            </span>
                                        @else
                                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete role \'{{ $role->name }}\'?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-admin.button type="submit" variant="danger" size="sm">
                                                    Delete
                                                </x-admin.button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    No roles found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Role Architecture & Presets Guide -->
        <div class="bg-gradient-to-r from-brand-50/50 via-emerald-50/30 to-blue-50/40 rounded-2xl p-6 border border-brand-100">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-brand-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="space-y-2">
                    <h4 class="text-base font-bold text-gray-900">Granular Role-Based Access Control (RBAC)</h4>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Every staff member is bound to an authorized role. Permissions are synchronized instantly across the administrative navigation and backend controllers. 
                        <strong>Super Admin</strong> retains absolute system privileges and cannot be locked out.
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 pt-2">
                        <div class="bg-white/80 p-3 rounded-xl border border-gray-200/70 text-xs">
                            <span class="font-semibold text-gray-900">POS & Stock Operator</span>
                            <p class="text-gray-500 mt-0.5">Strictly restricted to POS terminal, receipts, inwards, stock adjustments & batches.</p>
                        </div>
                        <div class="bg-white/80 p-3 rounded-xl border border-gray-200/70 text-xs">
                            <span class="font-semibold text-gray-900">Field Agent</span>
                            <p class="text-gray-500 mt-0.5">Focused on field work order execution, customer visits, and stage updates.</p>
                        </div>
                        <div class="bg-white/80 p-3 rounded-xl border border-gray-200/70 text-xs">
                            <span class="font-semibold text-gray-900">Accountant</span>
                            <p class="text-gray-500 mt-0.5">Invoices, payment reconciliation, GST GSTR-1 outward reports & customer ledgers.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
