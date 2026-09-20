@props([
    'name' => '',
    'src' => null,
    'size' => 'md',
    'status' => null, // 'active', 'inactive', or null
])

@php
    $trimmed = trim((string) $name);
    $words = preg_split('/\s+/', $trimmed, -1, PREG_SPLIT_NO_EMPTY);
    
    if (count($words) >= 2) {
        $initials = mb_substr($words[0], 0, 1) . mb_substr(end($words), 0, 1);
    } elseif (!empty($trimmed)) {
        $initials = mb_substr($trimmed, 0, min(2, mb_strlen($trimmed)));
    } else {
        $initials = 'U';
    }
    $initials = mb_strtoupper($initials);

    // Deterministic palette based on name hash
    $palettes = [
        ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200/70', 'ring' => 'ring-emerald-500/10'],
        ['bg' => 'bg-sky-50',     'text' => 'text-sky-700',     'border' => 'border-sky-200/70',     'ring' => 'ring-sky-500/10'],
        ['bg' => 'bg-indigo-50',  'text' => 'text-indigo-700',  'border' => 'border-indigo-200/70',  'ring' => 'ring-indigo-500/10'],
        ['bg' => 'bg-purple-50',  'text' => 'text-purple-700',  'border' => 'border-purple-200/70',  'ring' => 'ring-purple-500/10'],
        ['bg' => 'bg-amber-50',   'text' => 'text-amber-800',   'border' => 'border-amber-200/70',   'ring' => 'ring-amber-500/10'],
        ['bg' => 'bg-rose-50',    'text' => 'text-rose-700',    'border' => 'border-rose-200/70',    'ring' => 'ring-rose-500/10'],
        ['bg' => 'bg-teal-50',    'text' => 'text-teal-700',    'border' => 'border-teal-200/70',    'ring' => 'ring-teal-500/10'],
        ['bg' => 'bg-blue-50',    'text' => 'text-blue-700',    'border' => 'border-blue-200/70',    'ring' => 'ring-blue-500/10'],
    ];

    $hash = abs(crc32($trimmed));
    $theme = $palettes[$hash % count($palettes)];

    $sizes = [
        'xs' => ['box' => 'w-6 h-6',   'text' => 'text-[10px]', 'dot' => 'w-1.5 h-1.5 ring-1'],
        'sm' => ['box' => 'w-8 h-8',   'text' => 'text-xs',      'dot' => 'w-2 h-2 ring-1.5'],
        'md' => ['box' => 'w-10 h-10', 'text' => 'text-sm',     'dot' => 'w-2.5 h-2.5 ring-2'],
        'lg' => ['box' => 'w-14 h-14', 'text' => 'text-lg',     'dot' => 'w-3.5 h-3.5 ring-2'],
        'xl' => ['box' => 'w-20 h-20', 'text' => 'text-2xl',    'dot' => 'w-4 h-4 ring-2'],
    ];

    $s = $sizes[$size] ?? $sizes['md'];
@endphp

<div {{ $attributes->merge(['class' => "relative inline-flex flex-shrink-0 {$s['box']}"]) }}>
    @if(!empty($src))
        <img src="{{ $src }}"
             alt="{{ $name }}"
             class="w-full h-full rounded-full object-cover shadow-xs border border-gray-100"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div style="display: none;"
             class="w-full h-full rounded-full {{ $theme['bg'] }} {{ $theme['text'] }} border {{ $theme['border'] }} ring-1 {{ $theme['ring'] }} items-center justify-center font-bold {{ $s['text'] }} tracking-wider select-none shadow-xs">
            {{ $initials }}
        </div>
    @else
        <div class="w-full h-full rounded-full {{ $theme['bg'] }} {{ $theme['text'] }} border {{ $theme['border'] }} ring-1 {{ $theme['ring'] }} flex items-center justify-center font-bold {{ $s['text'] }} tracking-wider select-none shadow-xs">
            {{ $initials }}
        </div>
    @endif

    @if($status === 'active')
        <span class="absolute bottom-0 right-0 block {{ $s['dot'] }} rounded-full bg-emerald-500 ring-white" title="Active"></span>
    @elseif($status === 'inactive')
        <span class="absolute bottom-0 right-0 block {{ $s['dot'] }} rounded-full bg-gray-400 ring-white" title="Inactive"></span>
    @endif
</div>
