@props([
    'name',
    'label' => null,
    'options' => [],
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
])

@php
    $id = $attributes->get('id', $name);
    $selected = old($name, $attributes->get('value', ''));
    $baseClass = 'w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100';
    if ($disabled) {
        $baseClass .= ' opacity-70 cursor-not-allowed';
    }
    $extraClass = $attributes->get('class', '');
    $mergedClass = trim($baseClass . ' ' . $extraClass);
@endphp

<div {{ $attributes->only(['class:wrapper']) }}>
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-gray-700 mb-1.5">
            {{ $label }} {!! $required ? '<span class="text-red-500">*</span>' : '' !!}
        </label>
    @endif

    <select name="{{ $name }}"
            id="{{ $id }}"
            {!! $required ? 'required' : '' !!}
            {!! $disabled ? 'disabled' : '' !!}
            {{ $attributes->except(['id', 'class', 'value']) }}
            class="{{ $mergedClass }}">
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options as $value => $label)
            <option value="{{ $value }}" {{ $selected == $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>

    @error($name)
        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>
