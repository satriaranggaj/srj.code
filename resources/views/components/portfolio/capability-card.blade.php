@props([
    'title',
    'href' => null,
    'icon' => 'sparkles',
])

<div
    {{ $attributes->merge(['class' => 'group relative flex flex-col justify-between overflow-hidden rounded-xl border border-ink-700/80 bg-ink-850/50 p-6 transition-all duration-200 hover:-translate-y-0.5 hover:border-ink-600 hover:bg-ink-800/60']) }}
>
    <div>
        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-ink-600 bg-ink-800 text-accent-400 transition-colors duration-200 group-hover:border-accent-400/40">
            <x-portfolio.icon :name="$icon" class="h-4 w-4" />
        </span>

        <h3 class="mt-4 text-base font-semibold">
            <a @if ($href) href="{{ $href }}" @endif>{{ $title }}</a>
        </h3>

        <div class="mt-2 text-sm leading-relaxed text-bone-400">
            {{ $slot }}
        </div>
    </div>
</div>