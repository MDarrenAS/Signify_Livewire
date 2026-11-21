@props([
    'variant' => 'primary', // primary | secondary
    'type'    => 'button',
    'href'    => null,
    'size'    => 'md',      // sm | md | lg
])

@php
    $base = 'inline-flex items-center justify-center gap-2 font-extrabold uppercase tracking-wide rounded-xl border-2 border-b-4 transition-all select-none cursor-pointer active:translate-y-0.5 active:border-b-2 disabled:opacity-45 disabled:cursor-not-allowed disabled:active:translate-y-0 disabled:active:border-b-4';

    $variants = [
        'primary'   => 'bg-[#F9B92F] hover:bg-[#fabf45] border-[#C98A06] text-[#1C1C1E]',
        'secondary' => 'bg-white hover:bg-zinc-50 border-[#E4E4E7] text-[#52525B]',
    ];

    $sizes = [
        'sm' => 'px-4 py-2 text-xs',
        'md' => 'px-6 py-3 text-xs sm:text-sm',
        'lg' => 'px-7 py-3.5 text-sm',
    ];

    $classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif