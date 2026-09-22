@extends('admin.layout')

@section('page-title', $isEdit ? 'Edit Role: ' . $role->name : 'Create Custom Role')

@section('content')
    <div class="space-y-6" x-data="roleFormHandler()">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.roles.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">
                        {{ $isEdit ? 'Edit Role: ' . $role->name : 'Create Custom Role' }}
                    </h2>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $isEdit ? 'Configure system privileges and domain access for this role.' : 'Define a customized staff role with tailored functional permissions.' }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <x-admin.button href="{{ route('admin.roles.index') }}" variant="secondary">
                    Cancel
                </x-admin.button>
                @if(!$isEdit || $role->name !== 'Super Admin')
                    <x-admin.button type="button" @click="submitForm()" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'>
                        {{ $isEdit ? 'Save Changes' : 'Create Role' }}
                    </x-admin.button>
                @endif
            </div>
        </div>

        <form id="role-form"
              action="{{ $isEdit ? route('admin.roles.update', $role) : route('admin.roles.store') }}"
              method="POST"
              class="space-y-6">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- Role Metadata Card -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Role Identity
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Role Display Name <span class="text-red-500">*</span>
                        </label>
                        @if($isEdit && $role->name === 'Super Admin')
                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="{{ $role->name }}"
                                   readonly
                                   class="w-full rounded-xl border-gray-200 bg-gray-50 text-gray-500 font-medium px-4 py-2.5 cursor-not-allowed shadow-sm">
                            <p class="text-xs text-purple-600 mt-1.5 font-medium">The master Super Admin role cannot be renamed or deleted.</p>
                        @else
                            <input type="text"
                                   id="name"
                                   name="name"
                                   required
                                   value="{{ old('name', $role->name) }}"
                                   placeholder="e.g., Regional Sales Supervisor or Warehouse Lead"
                                   class="w-full rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 shadow-sm px-4 py-2.5 text-sm">
                            @error('name')
                                <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Security Guard</label>
                        <input type="text"
                               value="admin (Administrative & Staff Portal)"
                               readonly
                               class="w-full rounded-xl border-gray-200 bg-gray-50 text-gray-500 font-mono text-sm px-4 py-2.5 cursor-not-allowed shadow-sm">
                        <p class="text-xs text-gray-400 mt-1.5">Applies exclusively to staff logins and dashboard sessions.</p>
                    </div>
                </div>
            </div>

            @if($isEdit && $role->name === 'Super Admin')
                <!-- Super Admin Protected Banner -->
                <div class="bg-purple-50 border border-purple-200 rounded-2xl p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-purple-900">Unrestricted Master Permissions</h4>
                            <p class="text-sm text-purple-700 mt-1">
                                <strong>Super Admin</strong> inherits 100% of all current and future system permissions automatically. It cannot be demoted or stripped of rights to prevent system lockouts.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Permission Matrix Controls -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-gray-100 pb-5">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Permission Assignment Matrix
                        </h3>
                        <p class="text-sm text-gray-500 mt-0.5">
                            Selected: <span class="font-bold text-brand-600" x-text="selectedCount">0</span> of <span class="font-medium text-gray-700" x-text="totalCount">0</span> permissions
                        </p>
                    </div>

                    @if(!$isEdit || $role->name !== 'Super Admin')
                        <div class="flex flex-wrap items-center gap-3">
                            <!-- Live Search -->
                            <div class="relative min-w-[200px]">
                                <input type="text"
                                       x-model="searchQuery"
                                       placeholder="Filter permissions..."
                                       class="w-full text-xs rounded-xl border-gray-200 pl-8 pr-3 py-2 focus:ring-brand-500 focus:border-brand-500">
                                <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>

                            <button type="button"
                                    @click="selectAll()"
                                    class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-brand-50 text-brand-700 hover:bg-brand-100 transition">
                                Select All
                            </button>
                            <button type="button"
                                    @click="deselectAll()"
                                    class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                                Clear All
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Grouped Categories -->
                <div class="space-y-6">
                    @foreach($groupedPermissions as $groupName => $group)
                        @php
                            $groupKeys = array_keys($group['permissions']);
                        @endphp
                        <div class="rounded-2xl border border-gray-200 overflow-hidden"
                             x-show="categoryMatches('{{ addslashes($groupName) }}', {{ json_encode($groupKeys) }})">
                            <!-- Category Header -->
                            <div class="bg-gray-50/90 px-5 py-3.5 flex items-center justify-between border-b border-gray-200">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-brand-500"></span>
                                    <h4 class="font-bold text-gray-800 text-sm tracking-wide">{{ $groupName }}</h4>
                                    <span class="text-xs text-gray-500 font-medium">({{ count($group['permissions']) }} items)</span>
                                </div>

                                @if(!$isEdit || $role->name !== 'Super Admin')
                                    <button type="button"
                                            @click="toggleGroup({{ json_encode($groupKeys) }})"
                                            class="text-xs font-semibold text-brand-600 hover:text-brand-800 transition px-2 py-1 rounded hover:bg-brand-50">
                                        Toggle Category
                                    </button>
                                @endif
                            </div>

                            <!-- Category Permission Checkboxes -->
                            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4 bg-white">
                                @foreach($group['permissions'] as $permKey => $permMeta)
                                    <label class="relative flex items-start gap-3 p-3 rounded-xl border border-gray-100 hover:border-brand-200 hover:bg-brand-50/20 transition cursor-pointer"
                                           x-show="itemMatches('{{ addslashes($permMeta['label']) }}', '{{ $permKey }}', '{{ addslashes($permMeta['desc']) }}')">
                                        <div class="flex items-center h-5 mt-0.5">
                                            <input type="checkbox"
                                                   name="permissions[]"
                                                   value="{{ $permKey }}"
                                                   x-model="selected"
                                                   @if($isEdit && $role->name === 'Super Admin') disabled checked @endif
                                                   class="w-4 h-4 text-brand-600 rounded border-gray-300 focus:ring-brand-500">
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <span class="block text-sm font-semibold text-gray-900 leading-tight">
                                                {{ $permMeta['label'] }}
                                            </span>
                                            <p class="text-xs text-gray-500 mt-0.5 leading-snug">
                                                {{ $permMeta['desc'] }}
                                            </p>
                                            <code class="inline-block mt-1.5 px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px] font-mono">
                                                {{ $permKey }}
                                            </code>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Sticky / Bottom Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <x-admin.button href="{{ route('admin.roles.index') }}" variant="secondary">
                    Cancel
                </x-admin.button>
                @if(!$isEdit || $role->name !== 'Super Admin')
                    <x-admin.button type="submit" variant="primary">
                        {{ $isEdit ? 'Update Role & Permissions' : 'Create Role' }}
                    </x-admin.button>
                @endif
            </div>
        </form>
    </div>

    <script>
        function roleFormHandler() {
            const allPermissions = @json(\App\Support\PermissionCatalog::allNames());
            const initialSelected = @json($isEdit ? ($role->name === 'Super Admin' ? \App\Support\PermissionCatalog::allNames() : $selectedPermissions) : old('permissions', []));

            return {
                selected: Array.isArray(initialSelected) ? initialSelected : [],
                searchQuery: '',
                allPermissions: allPermissions,
                get totalCount() {
                    return this.allPermissions.length;
                },
                get selectedCount() {
                    return this.selected.length;
                },
                selectAll() {
                    this.selected = [...this.allPermissions];
                },
                deselectAll() {
                    this.selected = [];
                },
                toggleGroup(keys) {
                    const allInGroup = keys.every(k => this.selected.includes(k));
                    if (allInGroup) {
                        this.selected = this.selected.filter(k => !keys.includes(k));
                    } else {
                        const merged = new Set([...this.selected, ...keys]);
                        this.selected = Array.from(merged);
                    }
                },
                itemMatches(label, key, desc) {
                    if (!this.searchQuery) return true;
                    const q = this.searchQuery.toLowerCase();
                    return label.toLowerCase().includes(q) || key.toLowerCase().includes(q) || desc.toLowerCase().includes(q);
                },
                categoryMatches(groupName, keys) {
                    if (!this.searchQuery) return true;
                    const q = this.searchQuery.toLowerCase();
                    if (groupName.toLowerCase().includes(q)) return true;
                    return keys.some(k => k.toLowerCase().includes(q));
                },
                submitForm() {
                    document.getElementById('role-form').submit();
                }
            };
        }
    </script>
@endsection
