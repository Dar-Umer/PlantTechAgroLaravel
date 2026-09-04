@props([
    'name',
    'label' => null,
    'value' => '1',
    'checked' => null,
    'help' => null,
])

@php
    $id = $attributes->get('id', $name);
    $isChecked = $checked ?? old($name, $attributes->get('default', false));
@endphp

<div>
    <input type="hidden" name="{{ $name }}" value="0">
    <label for="{{ $id }}" class="flex items-center gap-3 cursor-pointer group">
        <input type="checkbox"
               name="{{ $name }}"
               id="{{ $id }}"
               value="{{ $value }}"
               {{ $isChecked ? 'checked' : '' }}
               {{ $attributes->except(['id', 'class', 'default']) }}
               class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 transition">
        <span class="text-sm text-gray-700 group-hover:text-gray-900 transition">
            {{ $label }}
            @if($help)
                <span class="text-xs text-gray-400 ml-1">{{ $help }}</span>
            @endif
        </span>
    </label>

    @error($name)
        <p class="mt-1.5 text-xs text-red-500 ml-7">{{ $message }}</p>
    @enderror
</div>
