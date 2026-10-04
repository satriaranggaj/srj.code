@props([
    'title',
    'description' => null,
    'icon' => null,
])

<div
    {{ $attributes->merge(['class' => 'flex flex-col items-center rounded-xl border border-dashed border-ink-600/70 bg-ink-850/30 px-6 py-14 text-center']) }}
>
    @if ($icon)
        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-ink-600 bg-ink-800 text-bone-400">
            <x-portfolio.icon :name="$icon" class="h-4 w-4" />
        </span>
    @endif

    <p class="mt-4 text-base font-medium text-bone-200">{{ $title }}</p>

    @if ($description)
        <p class="mt-1 max-w-prose text-sm leading-relaxed text-bone-500">{{ $description }}</p>
    @endif

    @if (! $slot->isEmpty())
        <div class="mt-5 text-sm text-bone-400">{{ $slot }}</div>
    @endif
</div>