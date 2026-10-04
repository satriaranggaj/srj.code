@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'submit',
    'icon' => null,
    'size' => 'md',
    'external' => false,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-lg text-sm font-medium transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent-400 focus-visible:ring-offset-2 focus-visible:ring-offset-ink-950 disabled:cursor-not-allowed disabled:opacity-60';

    $variants = [
        'primary' => 'bg-accent-400 text-ink-950 hover:bg-accent-300 shadow-sm hover:shadow-card',
        'secondary' => 'border border-ink-600 bg-ink-850/60 text-bone-100 hover:border-ink-500 hover:bg-ink-800',
        'ghost' => 'text-bone-300 hover:text-bone-50 hover:bg-ink-800/70',
    ];

    $sizes = [
        'sm' => 'px-3.5 py-2',
        'md' => 'px-5 py-2.5',
        'lg' => 'px-6 py-3 text-[0.95rem]',
    ];

    $classes = trim($base.' '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']));

    $iconAfter = in_array($icon, ['arrow-right', 'external', 'arrow-up-right'], true);
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        @if ($external) target="_blank" rel="noopener noreferrer" @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if ($icon && ! $iconAfter)
            <x-portfolio.icon :name="$icon" class="h-4 w-4 shrink-0" />
        @endif

        <span>{{ $slot }}</span>

        @if ($iconAfter)
            <x-portfolio.icon :name="$icon" class="h-4 w-4 shrink-0" />
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon && ! $iconAfter)
            <x-portfolio.icon :name="$icon" class="h-4 w-4 shrink-0" />
        @endif

        <span>{{ $slot }}</span>

        @if ($iconAfter)
            <x-portfolio.icon :name="$icon" class="h-4 w-4 shrink-0" />
        @endif
    </button>
@endif