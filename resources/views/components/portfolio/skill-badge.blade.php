@props(['name', 'url' => null, 'icon' => null])

@php
    $tag = $url ? 'a' : 'span';
    $classes = 'inline-flex items-center gap-2 rounded-full border border-ink-600 bg-ink-850/70 px-3 py-1.5 text-sm text-bone-200 transition-all duration-200';
    $linkClasses = 'hover:border-accent-400/60 hover:bg-ink-800 hover:text-bone-50';
@endphp

<{{ $tag }}
    @if ($url)
        href="{{ $url }}" target="_blank" rel="noopener noreferrer"
        class="{{ $classes }} {{ $linkClasses }}"
    @else
        class="{{ $classes }}"
    @endif
>
    @if ($icon)
        <x-portfolio.icon :name="$icon" class="h-3.5 w-3.5 shrink-0 text-bone-400" />
    @endif

    {{ $name }}
</{{ $tag }}>