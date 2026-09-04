@props([
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'rows' => 3,
])

@php
    $id = $attributes->get('id', $name);
    $displayValue = $value ?? old($name);
    $baseClass = 'w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 resize-y';
    $extraClass = $attributes->get('class', '');
    $mergedClass = trim($baseClass . ' ' . $extraClass);
@endphp

<div {{ $attributes->only(['class:wrapper']) }}>
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-gray-700 mb-1.5">
            {{ $label }} {!! $required ? '<span class="text-red-500">*</span>' : '' !!}
        </label>
    @endif

    <textarea name="{{ $name }}"
              id="{{ $id }}"
              rows="{{ $rows }}"
              placeholder="{{ $placeholder }}"
              {!! $required ? 'required' : '' !!}
              {{ $attributes->except(['id', 'class', 'value', 'rows', 'style']) }}
              class="{{ $mergedClass }}">{{ $displayValue }}</textarea>

    @error($name)
        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>
