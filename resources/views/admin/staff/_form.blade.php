<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-1">Staff Details</h3>
    <p class="text-sm text-gray-500 mb-5">Staff log into the admin panel with email + password.</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <x-admin.input name="name" label="Full Name" :value="old('name', $staff->name ?? '')" required />
        <x-admin.input name="email" label="Email (Login ID)" type="email" :value="old('email', $staff->email ?? '')" required />
        <x-admin.input name="phone" label="Phone" :value="old('phone', $staff->phone ?? '')" />
        <x-admin.select name="role" label="Role" :options="$roles" :value="old('role', $currentRole ?? 'Field Agent')" required helptext="Field Agents execute work order stages; Managers oversee operations." />
    </div>
    <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-5">
        <x-admin.input name="password" label="{{ isset($staff) ? 'New Password' : 'Password' }}" type="password" :required="$passwordRequired" helptext="{{ isset($staff) ? 'Leave blank to keep the current password.' : 'Minimum 6 characters.' }}" />
        <x-admin.input name="password_confirmation" label="Confirm Password" type="password" :required="$passwordRequired" />
    </div>
    <div class="mt-5">
        <x-admin.checkbox name="is_active" label="Active" :checked="old('is_active', $staff->is_active ?? true)" help="Inactive staff cannot log in." />
    </div>
</div>

<div class="flex justify-end">
    <x-admin.button type="submit">{{ $submitLabel }}</x-admin.button>
</div>
