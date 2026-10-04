@props([
    'title',
    'description' => null,
    'icon' => 'layers',
    'align' => 'start',
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center rounded-xl border border-dashed border-ink-600/70 bg-ink-900/40 px-6 py-14 text-center']) }}>
    <span class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-ink-600 bg-ink-850 text-bone-400">
        <x-portfolio.icon :name="$icon" class="h-5 w-5" />
    </span>

    <p class="mt-4 text-base font-medium text-bone-100">{{ $title }}</p>

    @if ($description)
        <p class="mt-1.5 max-w-sm text-sm leading-relaxed text-bone-500">{{ $description }}</p>
    @endif

    @if (! $slot->isEmpty())
        <div class="mt-6">{{ $slot }}</div>
    @endif
</div>