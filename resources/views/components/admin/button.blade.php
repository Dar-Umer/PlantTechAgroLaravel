@props([
    'href' => null,
    'type' => 'submit',
    'variant' => 'primary',
    'size' => 'default',
    'icon' => null,
    'loading' => false,
])

@php
    $base = 'inline-flex items-center justify-center font-medium transition-all duration-150 text-sm rounded-xl shadow-sm active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed';
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'default' => 'px-4 py-2.5',
        'lg' => 'px-6 py-3 text-base',
    ];
    $variants = [
        'primary' => 'bg-emerald-600 text-white hover:bg-emerald-700 hover:shadow shadow-emerald-600/20 focus:ring-2 focus:ring-emerald-500/20',
        'secondary' => 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-gray-300 focus:ring-2 focus:ring-gray-200',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-700 hover:shadow shadow-rose-600/20 focus:ring-2 focus:ring-rose-500/20',
        'ghost' => 'text-gray-600 hover:text-gray-900 hover:bg-gray-100/80 shadow-none',
    ];
    $class = trim($base . ' ' . ($sizes[$size] ?? $sizes['default']) . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . $attributes->get('class', ''));
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->except(['class', 'href']) }} class="{{ $class }}">
        @if($loading)
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
        @endif
        @if($icon && !$loading)
            <span class="mr-2">{!! $icon !!}</span>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->except(['class', 'type']) }} class="{{ $class }}">
        @if($loading)
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
        @endif
        @if($icon && !$loading)
            <span class="mr-2">{!! $icon !!}</span>
        @endif
        {{ $slot }}
    </button>
@endif
