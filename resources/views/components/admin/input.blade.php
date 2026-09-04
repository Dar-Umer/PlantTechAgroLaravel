@props([
    'name',
    'type' => 'text',
    'label' => null,
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'readonly' => false,
    'disabled' => false,
    'helptext' => null,
])

@php
    $id = $attributes->get('id', $name);
    $displayValue = $value ?? old($name);
    $baseClass = 'w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100';
    if ($disabled || $readonly) {
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

    <input type="{{ $type }}"
           name="{{ $name }}"
           id="{{ $id }}"
           value="{{ $displayValue }}"
           placeholder="{{ $placeholder }}"
           {!! $required ? 'required' : '' !!}
           {!! $readonly ? 'readonly' : '' !!}
           {!! $disabled ? 'disabled' : '' !!}
           {{ $attributes->except(['id', 'class', 'style']) }}
           class="{{ $mergedClass }}">

    @error($name)
        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
    @enderror

    @if($helptext && !$errors->has($name))
        <p class="mt-1.5 text-xs text-gray-400">{!! $helptext !!}</p>
    @endif
</div>
