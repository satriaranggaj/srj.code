@props([
    'label',
    'href',
    'icon' => null,
    'external' => true,
])

@php
    $classes = 'group inline-flex items-center gap-2 rounded-md text-sm text-bone-300 transition-colors duration-200 hover:text-accent-400';
@endphp

<a
    href="{{ $href }}"
    @if ($external) target="_blank" rel="noopener noreferrer" @endif
    {{ $attributes->merge(['class' => $classes]) }}
>
    @if ($icon)
        <x-portfolio.icon :name="$icon" class="h-4 w-4 shrink-0 text-bone-400 transition-colors duration-200 group-hover:text-accent-400" />
    @endif

    <span>{{ $label }}</span>
</a>