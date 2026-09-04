@props([
    'type' => 'submit',
    'variant' => 'primary',
    'size' => 'default',
    'icon' => null,
    'loading' => false,
])

@php
    $base = 'inline-flex items-center justify-center font-semibold transition text-sm rounded-xl shadow-sm';
    $sizes = [
        'sm' => 'px-3.5 py-1.5 text-xs',
        'default' => 'px-5 py-2.5',
        'lg' => 'px-6 py-3',
    ];
    $variants = [
        'primary' => 'bg-brand-600 text-white hover:bg-brand-700 focus:ring-2 focus:ring-brand-200',
        'secondary' => 'bg-gray-100 text-gray-700 hover:bg-gray-200 focus:ring-2 focus:ring-gray-200',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-2 focus:ring-red-200',
        'ghost' => 'text-gray-600 hover:text-gray-900 hover:bg-gray-50',
    ];
    $class = trim($base . ' ' . ($sizes[$size] ?? $sizes['default']) . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . $attributes->get('class', ''));
@endphp

<button type="{{ $type }}" {{ $attributes->except(['class', 'type']) }} class="{{ $class }}">
    @if($loading)
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
    @endif
    @if($icon && !$loading)
        <span class="mr-2">{!! $icon !!}</span>
    @endif
    {{ $slot }}
</button>
