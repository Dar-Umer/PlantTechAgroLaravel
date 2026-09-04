<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-1">Customer Details</h3>
    <p class="text-sm text-gray-500 mb-5">The customer logs into the Customer App with phone number + password.</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <x-admin.input name="name" label="Full Name" :value="old('name', $customer->name ?? '')" required />
        <x-admin.input name="phone" label="Phone Number (Login ID)" :value="old('phone', $customer->phone ?? '')" required helptext="Unique. Used as the app login ID." />
        <x-admin.input name="email" label="Email (Optional)" type="email" :value="old('email', $customer->email ?? '')" />
        <x-admin.input name="area" label="Area / Locality" :value="old('area', $customer->area ?? '')" placeholder="e.g. Pulwama" />
    </div>
    <div class="mt-5">
        <x-admin.textarea name="address" label="Address" :value="old('address', $customer->address ?? '')" rows="2" />
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-1">App Login Credentials</h3>
    <p class="text-sm text-gray-500 mb-5">
        @isset($customer)
            Leave blank to keep the current password. Set a new one to reset the customer's app access.
        @else
            Set the password the customer will use to log into the Customer App. Share it with them after saving.
        @endisset
    </p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <x-admin.input name="password" label="{{ isset($customer) ? 'New Password' : 'Password' }}" type="password" :required="$passwordRequired" helptext="Minimum 6 characters." />
        <x-admin.input name="password_confirmation" label="Confirm Password" type="password" :required="$passwordRequired" />
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-1">Status & Notes</h3>
    <p class="text-sm text-gray-500 mb-5">Inactive customers cannot log into the app.</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <x-admin.select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive']" :value="old('status', $customer->status ?? 'active')" />
    </div>
    <div class="mt-5">
        <x-admin.textarea name="notes" label="Notes" :value="old('notes', $customer->notes ?? '')" rows="3" />
    </div>
</div>

<div class="flex justify-end">
    <x-admin.button type="submit">{{ $submitLabel }}</x-admin.button>
</div>
