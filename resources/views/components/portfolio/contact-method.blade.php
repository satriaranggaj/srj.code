@props([
    'label',
    'handle',
    'url',
    'icon',
])

@php
    $isExternal = ! str_starts_with($url, 'mailto:') && ! str_starts_with($url, 'tel:');
@endphp

<div
    {{ $attributes->merge(['class' => 'group flex items-start gap-4 rounded-xl border border-ink-700/80 bg-ink-850/40 p-5 transition-all duration-200 hover:border-ink-600 hover:bg-ink-800/60']) }}
>
    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-ink-600 bg-ink-800 text-bone-300 transition-colors duration-200 group-hover:border-accent-400/40 group-hover:text-accent-400">
        <x-portfolio.icon :name="$icon" class="h-4.5 w-4.5" />
    </span>

    <div class="min-w-0 flex-1">
        <p class="font-mono text-[0.7rem] uppercase tracking-[0.16em] text-bone-500">
            {{ $label }}
        </p>

        <a
            href="{{ $url }}"
            @if ($isExternal) target="_blank" rel="noopener noreferrer" @endif
            class="mt-1 block break-words text-base font-medium text-bone-50 transition-colors duration-200 hover:text-accent-400"
        >
            {{ $handle }}
        </a>
    </div>

    <x-portfolio.icon
        name="arrow-up-right"
        class="mt-1 h-4 w-4 shrink-0 text-bone-500 transition-all duration-200 group-hover:-translate-y-0.5 group-hover:text-accent-400 motion-safe:group-hover:translate-x-0.5"
    />
</div>