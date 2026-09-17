@props([
    'name',
    'label' => 'Image',
    'value' => null,
    'helptext' => null,
    'removeName' => null,
    'accept' => 'image/png,image/jpeg,image/webp,image/gif',
    'previewClass' => 'w-44 h-24',
])

@php
    $display = filled($value) ? (\App\Support\Media::url($value) ?? '') : '';
@endphp

<div {{ $attributes->merge(['class' => '']) }}>
    @if($label)
        <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ $label }}</label>
    @endif

    <div x-data="{ preview: @js($display), hasImage: @js($display !== '') }" class="flex items-start gap-5">
        {{-- Preview --}}
        <div class="shrink-0">
            <div class="{{ $previewClass }} rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden">
                <template x-if="hasImage">
                    <img :src="preview" alt="{{ $label }}" class="w-full h-full object-contain p-2">
                </template>
                <template x-if="!hasImage">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </template>
            </div>
        </div>

        {{-- Upload --}}
        <div class="flex-1 space-y-3">
            <input type="file" name="{{ $name }}" accept="{{ $accept }}"
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                   x-on:change="const f = $event.target.files[0]; if (f) { const r = new FileReader(); r.onload = e => { preview = e.target.result; hasImage = true }; r.readAsDataURL(f) }">
            @error($name)
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
            @if($helptext)
                <p class="text-xs text-gray-400">{!! $helptext !!}</p>
            @endif
            @if($removeName)
                <label x-show="hasImage" class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" name="{{ $removeName }}" value="1" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                    Remove current image
                </label>
            @endif
        </div>
    </div>
</div>
