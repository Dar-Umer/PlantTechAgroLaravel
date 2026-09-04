<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-1">Supplier Details</h3>
    <p class="text-sm text-gray-500 mb-5">Contact details used for purchase records and low stock alert emails.</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <x-admin.input name="name" label="Supplier Name" :value="old('name', $supplier->name ?? '')" required />
        <x-admin.input name="contact_person" label="Contact Person" :value="old('contact_person', $supplier->contact_person ?? '')" />
        <x-admin.input name="phone" label="Phone" :value="old('phone', $supplier->phone ?? '')" placeholder="+91 98765 43210" />
        <x-admin.input name="email" label="Email" type="email" :value="old('email', $supplier->email ?? '')" helptext="Low stock alerts are emailed here." />
        <x-admin.input name="gst_no" label="GST Number" :value="old('gst_no', $supplier->gst_no ?? '')" />
    </div>
    <div class="mt-5">
        <x-admin.textarea name="address" label="Address" :value="old('address', $supplier->address ?? '')" rows="2" />
    </div>
    <div class="mt-5">
        <x-admin.textarea name="notes" label="Notes" :value="old('notes', $supplier->notes ?? '')" rows="2" />
    </div>
    <div class="mt-5">
        <x-admin.checkbox name="is_active" label="Active" :checked="old('is_active', $supplier->is_active ?? true)" />
    </div>
</div>

<div class="flex justify-end">
    <x-admin.button type="submit">{{ $submitLabel }}</x-admin.button>
</div>
