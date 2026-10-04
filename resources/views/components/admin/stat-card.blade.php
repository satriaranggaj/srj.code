@props([
    'label',
    'value',
    'icon' => 'layers',
    'href' => null,
    'hint' => null,
    'accent' => false,
])

@php
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'group flex items-start gap-4 rounded-xl border border-ink-700/80 bg-ink-850/50 p-5 transition-all duration-150 hover:border-ink-600 hover:bg-ink-800/60']) }}
>
    <span @class([
        'inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border transition-colors duration-150',
        'border-accent-400/40 bg-accent-400/10 text-accent-400' => $accent,
        'border-ink-600 bg-ink-800 text-bone-400 group-hover:text-bone-200' => ! $accent,
    ])>
        <x-portfolio.icon :name="$icon" class="h-4.5 w-4.5" />
    </span>

    <div class="min-w-0">
        <p class="font-mono text-[0.7rem] uppercase tracking-[0.16em] text-bone-500">{{ $label }}</p>

        <p class="mt-1 text-2xl font-semibold tracking-tightest text-bone-50">{{ $value }}</p>

        @if ($hint)
            <p class="mt-1 text-xs text-bone-500">{{ $hint }}</p>
        @endif
    </div>
</{{ $tag }}>