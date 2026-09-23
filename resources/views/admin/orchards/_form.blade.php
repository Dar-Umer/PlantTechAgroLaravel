<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-1">Orchardist & Ownership</h3>
    <p class="text-sm text-gray-500 mb-5">Select the registered customer (orchardist) who owns this farm.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <x-admin.select name="customer_id" label="Orchardist (Customer)"
                        :options="$customers->mapWithKeys(fn ($c) => [$c->id => $c->name . ' (' . ($c->orchardist_id ?? 'No OID') . ') — ' . $c->phone])->all()"
                        :value="old('customer_id', $orchard->customer_id ?? ($preselectCustomer->id ?? ''))"
                        placeholder="Select an Orchardist" required />

        <x-admin.input name="name" label="Orchard Name" :value="old('name', $orchard->name ?? '')" placeholder="e.g. Shopian North Block A" required helptext="Identifiable name or block reference." />
    </div>

    <div class="mt-5">
        <x-admin.textarea name="address" label="Orchard Address & Location" :value="old('address', $orchard->address ?? ($preselectCustomer->address ?? ''))" rows="2" required placeholder="Village, Tehsil, District, Landmarks" helptext="Physical farm address." />
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-1">Farm Specifications</h3>
    <p class="text-sm text-gray-500 mb-5">Land measurements, tree numbers, and establishment date.</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <x-admin.input name="area_kanals" label="Area (Kanals)" type="number" step="0.01" min="0.01" :value="old('area_kanals', $orchard->area_kanals ?? '5.00')" required helptext="1 Kanal = ~5440 sq ft." />
        <x-admin.input name="tree_count" label="Number of Plants" type="number" min="0" :value="old('tree_count', $orchard->tree_count ?? '500')" required helptext="Total trees planted." />
        <x-admin.input name="date_of_establishment" label="Date of Establishment" type="date" :value="old('date_of_establishment', isset($orchard->date_of_establishment) ? $orchard->date_of_establishment->format('Y-m-d') : now()->format('Y-m-d'))" required helptext="Used to calculate orchard age." />
        <x-admin.select name="status" label="Status" :options="['active' => 'Active', 'dormant' => 'Dormant', 'archived' => 'Archived']" :value="old('status', $orchard->status ?? 'active')" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mt-5">
        <x-admin.input name="latitude" label="Latitude (GPS)" type="number" step="0.0000001" :value="old('latitude', $orchard->latitude ?? '')" placeholder="e.g. 33.7214" helptext="Optional decimal degrees." />
        <x-admin.input name="longitude" label="Longitude (GPS)" type="number" step="0.0000001" :value="old('longitude', $orchard->longitude ?? '')" placeholder="e.g. 74.8327" helptext="Optional decimal degrees." />
    </div>

    <div class="mt-5">
        <x-admin.input name="variety_notes" label="Apple Varieties Planted" :value="old('variety_notes', $orchard->variety_notes ?? '')" placeholder="e.g. Gala Schniga, Red Velox, Jeromine" helptext="Cultivars or rootstocks planted in this block." />
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-1">Company Provenance & Notes</h3>
    <p class="text-sm text-gray-500 mb-5">Tag whether this orchard was developed and established by Plant Tech Agro.</p>

    <div class="space-y-5">
        <x-admin.checkbox name="is_company_established" label="Established by Plant Tech Agro"
                          :checked="old('is_company_established', $orchard->is_company_established ?? true)"
                          help="Awards the verified 'Established by Plant Tech Agro' badge on Customer App and system." />

        <x-admin.textarea name="notes" label="Administrative Notes" :value="old('notes', $orchard->notes ?? '')" rows="3" placeholder="Soil test details, trellis installation notes, irrigation setup, etc." />
    </div>
</div>

<div class="flex justify-end gap-3">
    <x-admin.button href="{{ route('admin.orchards.index') }}" variant="secondary">Cancel</x-admin.button>
    <x-admin.button type="submit" variant="primary">{{ $submitLabel ?? 'Save Orchard' }}</x-admin.button>
</div>
