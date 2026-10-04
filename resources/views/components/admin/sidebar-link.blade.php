@props([
    'name' => 'layers',
    'label' => null,
    'href' => null,
    'active' => false,
    'badge' => null,
])

@php
    $classes = 'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors duration-150';
    $state = $active
        ? 'bg-ink-800 text-bone-50'
        : 'text-bone-400 hover:bg-ink-850 hover:text-bone-100';
@endphp

<a
    href="{{ $href }}"
    @if ($active) aria-current="page" @endif
    {{ $attributes->merge(['class' => $classes.' '.$state]) }}
>
    <x-portfolio.icon
        :name="$name"
        @class([
            'h-4 w-4 shrink-0 transition-colors duration-150',
            'text-accent-400' => $active,
            'text-bone-500 group-hover:text-bone-300' => ! $active,
        ])
    />

    <span class="flex-1 truncate">{{ $label ?? $slot }}</span>

    @if ($badge !== null && $badge > 0)
        <span
            class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-accent-400/15 px-1.5 font-mono text-[0.7rem] text-accent-300"
        >{{ $badge }}</span>
    @endif
</a>