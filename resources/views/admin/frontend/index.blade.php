@extends('admin.layout')

@section('page-title', 'Frontend')

@section('content')
    <div class="space-y-6" x-data="{ activeTab: '{{ request('tab', 'lead_form') }}' }">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Frontend</h2>
        </div>

        {{-- Tab Navigation --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-2 py-1">
            <nav class="flex gap-1 overflow-x-auto" aria-label="Frontend tabs">
                <button @click="activeTab = 'lead_form'"
                    :class="activeTab === 'lead_form' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Lead Form
                </button>
                <button @click="activeTab = 'home_sections'"
                    :class="activeTab === 'home_sections' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Home Sections
                </button>
                <button @click="activeTab = 'footer'"
                    :class="activeTab === 'footer' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Footer
                </button>
            </nav>
        </div>

        {{-- Lead Form Tab --}}
        <div x-show="activeTab === 'lead_form'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">

            {{-- Form Settings --}}
            <form action="{{ route('admin.frontend.lead-form.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Lead Form Settings</h3>
                    <p class="text-sm text-gray-500 mb-5">Texts shown on the booking form of your landing page.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.input name="lead_form_heading" label="Form Heading" :value="$settings['lead_form_heading']" required />
                        <x-admin.input name="lead_form_button_text" label="Submit Button Text" :value="$settings['lead_form_button_text']" required />
                    </div>
                    <div class="mt-5 space-y-5">
                        <x-admin.textarea name="lead_form_description" label="Form Description" :value="$settings['lead_form_description']" rows="2" />
                        <x-admin.textarea name="lead_form_success_message" label="Success Message" :value="$settings['lead_form_success_message']" rows="2" helptext="Shown after the visitor submits the form." />
                    </div>
                    <div class="mt-5 flex justify-end">
                        <x-admin.button type="submit">Save Settings</x-admin.button>
                    </div>
                </div>
            </form>

            {{-- System Fields --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Mandatory Fields</h3>
                <p class="text-sm text-gray-500 mb-5">These three fields are always present in the form and cannot be removed or reordered.</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-50 text-brand-700">Text</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">Required</span>
                        </div>
                        <p class="font-semibold text-gray-900">Name</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-50 text-brand-700">Phone</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">Required</span>
                        </div>
                        <p class="font-semibold text-gray-900">Phone Number</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-50 text-brand-700">Dropdown</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">Required</span>
                        </div>
                        <p class="font-semibold text-gray-900">Service</p>
                        <p class="text-xs text-gray-500 mt-0.5">Lists your {{ $activeServicesCount }} active service(s)</p>
                    </div>
                </div>
            </div>

            {{-- Custom Fields --}}
            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Custom Fields</h3>
                    <p class="text-sm text-gray-500 mb-5">Additional input fields for your lead form. They appear after the mandatory fields.</p>

                    {{-- Add Field Form --}}
                    <form action="{{ route('admin.lead-form-fields.store') }}" method="POST" class="mb-6 rounded-xl bg-gray-50 border border-gray-100 p-5"
                          x-data="{ type: 'text' }">
                        @csrf
                        <p class="text-sm font-semibold text-gray-700 mb-4">Add New Field</p>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <x-admin.input name="label" label="Label" placeholder="e.g. Address" required />
                            <div>
                                <label for="add-type" class="block text-sm font-medium text-gray-700 mb-1.5">Field Type <span class="text-red-500">*</span></label>
                                <select name="type" id="add-type" x-model="type" required
                                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                    @foreach(\App\Models\LeadFormField::TYPES as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div x-show="type === 'select'" x-cloak>
                                <x-admin.input name="options" label="Dropdown Options" placeholder="Option A, Option B, Option C" helptext="Comma-separated" />
                            </div>
                            <x-admin.input name="sort_order" label="Sort Order" type="number" value="0" />
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex items-center gap-6">
                                <x-admin.checkbox name="is_required" label="Required field" />
                                <x-admin.checkbox name="is_active" label="Active" :checked="true" />
                            </div>
                            <x-admin.button type="submit">Add Field</x-admin.button>
                        </div>
                    </form>

                    {{-- Fields Table --}}
                    @if($fields->isEmpty())
                        <p class="text-sm text-gray-500 text-center py-8 border-t border-gray-100">No custom fields yet. The form currently shows only the mandatory fields.</p>
                    @else
                        <form action="{{ route('admin.lead-form-fields.reorder') }}" method="POST">
                            @csrf
                            <div class="overflow-x-auto border-t border-gray-100">
                                <table class="w-full text-sm text-left">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 font-semibold text-gray-600">Label</th>
                                            <th class="px-4 py-3 font-semibold text-gray-600">Type</th>
                                            <th class="px-4 py-3 font-semibold text-gray-600">Required</th>
                                            <th class="px-4 py-3 font-semibold text-gray-600">Active</th>
                                            <th class="px-4 py-3 font-semibold text-gray-600 w-28">Order</th>
                                            <th class="px-4 py-3 font-semibold text-gray-600 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($fields as $field)
                                            <tr class="hover:bg-gray-50 transition">
                                                <td class="px-4 py-3 font-medium text-gray-900">
                                                    {{ $field->label }}
                                                    <span class="block text-xs text-gray-400">{{ $field->name }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-gray-600">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-50 text-brand-700">
                                                        {{ \App\Models\LeadFormField::TYPES[$field->type] ?? $field->type }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    @if($field->is_required)
                                                        <span class="text-green-600 text-xs font-medium">Yes</span>
                                                    @else
                                                        <span class="text-gray-400 text-xs font-medium">No</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3">
                                                    @if($field->is_active)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">Active</span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700">Hidden</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3">
                                                    <input type="number" name="orders[{{ $field->id }}]" value="{{ $field->sort_order }}" min="0"
                                                           class="w-20 rounded-lg border border-gray-200 bg-gray-50 px-2 py-1.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <div class="flex items-center justify-end gap-2" x-data="{ open: false }">
                                                        <x-admin.button type="button" variant="secondary" size="sm" @click="open = true">Edit</x-admin.button>
                                                        <form action="{{ route('admin.lead-form-fields.destroy', $field) }}" method="POST" onsubmit="return confirm('Delete this field? Leads already collected will keep their data.')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <x-admin.button type="submit" variant="danger" size="sm">Delete</x-admin.button>
                                                        </form>

                                                        {{-- Edit Modal --}}
                                                        <div x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
                                                            <div class="fixed inset-0 bg-gray-900/50" @click="open = false"></div>
                                                            <div class="relative max-w-lg mx-auto mt-16 mb-8 bg-white rounded-2xl shadow-xl p-6" @click.away="open = false" x-data="{ type: '{{ $field->type }}' }">
                                                                <h4 class="text-lg font-semibold text-gray-900 mb-4">Edit Field: {{ $field->label }}</h4>
                                                                <form action="{{ route('admin.lead-form-fields.update', $field) }}" method="POST">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <div class="space-y-4">
                                                                        <x-admin.input name="label" label="Label" :value="$field->label" required />
                                                                        <div>
                                                                            <label for="edit-type-{{ $field->id }}" class="block text-sm font-medium text-gray-700 mb-1.5">Field Type</label>
                                                                            <select name="type" id="edit-type-{{ $field->id }}" x-model="type"
                                                                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                                                                @foreach(\App\Models\LeadFormField::TYPES as $value => $label)
                                                                                    <option value="{{ $value }}" {{ $field->type === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div x-show="type === 'select'" x-cloak>
                                                                            <x-admin.input name="options" label="Dropdown Options" :value="implode(', ', $field->options ?? [])" helptext="Comma-separated" />
                                                                        </div>
                                                                        <x-admin.input name="sort_order" label="Sort Order" type="number" :value="$field->sort_order" />
                                                                        <div class="flex items-center gap-6">
                                                                            <x-admin.checkbox name="is_required" label="Required field" :checked="$field->is_required" />
                                                                            <x-admin.checkbox name="is_active" label="Active" :checked="$field->is_active" />
                                                                        </div>
                                                                    </div>
                                                                    <div class="mt-6 flex justify-end gap-2">
                                                                        <x-admin.button type="button" variant="secondary" @click="open = false">Cancel</x-admin.button>
                                                                        <x-admin.button type="submit">Save Changes</x-admin.button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4 flex justify-end">
                                <x-admin.button type="submit">Save Field Order</x-admin.button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Home Sections Tab --}}
        <div x-show="activeTab === 'home_sections'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="space-y-6">
            <form action="{{ route('admin.frontend.home-sections.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                @forelse($sections as $section)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="text-lg font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $section->section_key)) }}</h3>
                            <span class="text-xs font-mono text-gray-400">{{ $section->section_key }}</span>
                        </div>
                        <p class="text-sm text-gray-500 mb-5">Content of this landing page section.</p>
                        <div class="space-y-5">
                            <x-admin.input name="sections[{{ $section->id }}][title]" label="Title" :value="$section->title" />
                            <x-admin.input name="sections[{{ $section->id }}][subtitle]" label="Subtitle" :value="$section->subtitle" />
                            <x-admin.textarea name="sections[{{ $section->id }}][description]" label="Description" :value="$section->content['description'] ?? ''" rows="3" />
                            <x-admin.checkbox name="sections[{{ $section->id }}][is_active]" label="Visible on landing page" :checked="$section->is_active" />

                            @if($section->section_key === 'about_preview')
                                {{-- About images --}}
                                <div class="rounded-xl bg-gray-50 border border-gray-100 p-5"
                                     x-data="{
                                         p1: '{{ \App\Support\Media::url($section->content['image_1'] ?? null) }}',
                                         h1: '{{ !empty($section->content['image_1']) ? '1' : '' }}',
                                         p2: '{{ \App\Support\Media::url($section->content['image_2'] ?? null) }}',
                                         h2: '{{ !empty($section->content['image_2']) ? '1' : '' }}'
                                     }">
                                    <p class="text-sm font-semibold text-gray-700 mb-1">About Images</p>
                                    <p class="text-xs text-gray-500 mb-4">The two photos in the About section. Blank keeps the current one (or falls back to the gallery).</p>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                        @foreach(['image_1' => 'Main image', 'image_2' => 'Secondary image'] as $imgKey => $imgLabel)
                                            <div>
                                                <div class="aspect-[3/4] rounded-xl border-2 border-dashed border-gray-200 bg-white overflow-hidden flex items-center justify-center mb-2">
                                                    <template x-if="h{{ substr($imgKey, -1) }}">
                                                        <img :src="p{{ substr($imgKey, -1) }}" alt="Preview" class="w-full h-full object-cover">
                                                    </template>
                                                    <template x-if="!h{{ substr($imgKey, -1) }}">
                                                        <div class="text-center p-3">
                                                            <svg class="w-7 h-7 text-gray-300 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                            <span class="text-xs text-gray-400">{{ $imgLabel }}</span>
                                                        </div>
                                                    </template>
                                                </div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Upload {{ $imgLabel }}</label>
                                                <input type="file" name="sections[{{ $section->id }}][{{ $imgKey }}]" accept="image/png,image/jpeg,image/webp,image/gif"
                                                       class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                                                       onchange="if(this.files[0]){const r=new FileReader();r.onload=e=>{p{{ substr($imgKey, -1) }}=e.target.result;h{{ substr($imgKey, -1) }}='1'};r.readAsDataURL(this.files[0])}">
                                                @if(!empty($section->content[$imgKey] ?? null))
                                                    <label class="mt-2 flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                                        <input type="checkbox" name="sections[{{ $section->id }}][remove_{{ $imgKey }}]" value="1" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                                        Remove current image
                                                    </label>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- About highlights --}}
                                <div class="rounded-xl bg-gray-50 border border-gray-100 p-5"
                                     x-data="{ points: {{ json_encode(array_values(array_filter($section->content['points'] ?? []))) }} }">
                                    <p class="text-sm font-semibold text-gray-700 mb-1">Highlights</p>
                                    <p class="text-xs text-gray-500 mb-4">Bullet points shown under the description. Leave empty to use the default highlights.</p>
                                    <div class="space-y-2">
                                        <template x-for="(point, i) in points" :key="i">
                                            <div class="flex items-center gap-2">
                                                <span class="mt-0 flex-shrink-0 w-6 h-6 rounded-full bg-brand-50 border border-brand-100 flex items-center justify-center">
                                                    <svg class="w-3.5 h-3.5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                </span>
                                                <input type="text" x-model="points[i]" name="sections[{{ $section->id }}][points][]"
                                                       placeholder="Highlight text"
                                                       class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                                <button type="button" @click="points.splice(i, 1)" class="text-red-400 hover:text-red-600 transition p-1.5 flex-shrink-0" title="Remove this highlight">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                    <button type="button" @click="points.push('')"
                                            class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-600 hover:text-brand-700 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Add Highlight
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center text-gray-500">
                        <p class="text-sm">No home sections found. Run <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">php artisan db:seed --class=ContentSeeder</code> to create the default sections.</p>
                    </div>
                @endforelse

                @if($stats->isNotEmpty())
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Impact Stats</h3>
                        <p class="text-sm text-gray-500 mb-5">The numbers shown in the "Our Impact" section.</p>
                        <div class="space-y-4">
                            @foreach($stats as $stat)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 rounded-xl bg-gray-50 border border-gray-100 p-4">
                                    <x-admin.input name="stats[{{ $stat->id }}][label]" label="Label" :value="$stat->label" />
                                    <x-admin.input name="stats[{{ $stat->id }}][value]" label="Value" :value="$stat->value" helptext="Suffix (e.g. +) is added automatically." />
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex justify-end">
                    <x-admin.button type="submit">Save Home Sections</x-admin.button>
                </div>
            </form>
        </div>

        {{-- Footer Tab --}}
        <div x-show="activeTab === 'footer'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="space-y-6">
            <form action="{{ route('admin.frontend.footer.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Footer & Contact Info</h3>
                    <p class="text-sm text-gray-500 mb-6">These appear in the site footer (tagline, contact details and social links).</p>
                    <div class="space-y-5">
                        <x-admin.input name="site_name" label="Site Name" :value="$settings['site_name'] ?? ''" />
                        <x-admin.textarea name="footer_tagline" label="Footer Tagline" :value="$settings['footer_tagline'] ?? ''" rows="2" />
                        <x-admin.input name="site_email" label="Contact Email" type="email" :value="$settings['site_email'] ?? ''" />
                        <x-admin.input name="site_phone" label="Contact Phone" :value="$settings['site_phone'] ?? ''" />
                        <x-admin.input name="support_hours" label="Support Hours" :value="$settings['support_hours'] ?? ''" helptext="Shown in the footer contact section." />
                        <x-admin.textarea name="site_address" label="Address" :value="$settings['site_address'] ?? ''" rows="2" />
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Social Links</h3>
                    <p class="text-sm text-gray-500 mb-6">Links shown in the footer social icons. Paste full URLs.</p>
                    <div class="space-y-5">
                        <x-admin.input name="social_facebook" label="Facebook" :value="$settings['social_facebook'] ?? ''" />
                        <x-admin.input name="social_instagram" label="Instagram" :value="$settings['social_instagram'] ?? ''" />
                        <x-admin.input name="social_youtube" label="YouTube" :value="$settings['social_youtube'] ?? ''" />
                        <x-admin.input name="social_whatsapp" label="WhatsApp" :value="$settings['social_whatsapp'] ?? ''" />
                        <x-admin.input name="social_x" label="X (Twitter) URL" :value="$settings['social_x'] ?? ''" placeholder="https://x.com/yourhandle" helptext="Blank to hide" />
                    </div>
                </div>

                <div class="flex justify-end">
                    <x-admin.button type="submit">Save Footer</x-admin.button>
                </div>
            </form>
        </div>
    </div>
@endsection
