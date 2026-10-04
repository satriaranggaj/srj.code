@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'submit',
    'icon' => null,
    'iconAfter' => null,
    'size' => 'md',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-lg font-medium transition-all duration-150 disabled:cursor-not-allowed disabled:opacity-60';

    $variants = [
        'primary' => 'bg-accent-400 text-ink-950 hover:bg-accent-300',
        'secondary' => 'border border-ink-600 bg-ink-850 text-bone-100 hover:border-ink-500 hover:bg-ink-800',
        'ghost' => 'text-bone-400 hover:bg-ink-850 hover:text-bone-100',
        'danger' => 'bg-red-500/90 text-white hover:bg-red-500',
        'danger-ghost' => 'border border-red-500/40 text-red-300 hover:bg-red-500/10',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-2.5 text-[0.95rem]',
        'icon' => 'p-2',
    ];

    $classes = trim($base.' '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']));
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-portfolio.icon :name="$icon" class="h-4 w-4 shrink-0" />@endif
        <span>{{ $slot }}</span>
        @if ($iconAfter)<x-portfolio.icon :name="$iconAfter" class="h-4 w-4 shrink-0" />@endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-portfolio.icon :name="$icon" class="h-4 w-4 shrink-0" />@endif
        <span>{{ $slot }}</span>
        @if ($iconAfter)<x-portfolio.icon :name="$iconAfter" class="h-4 w-4 shrink-0" />@endif
    </button>
@endif