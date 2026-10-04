@props([
    'title',
    'period' => null,
    'organisation' => null,
])

@php
    $hasHeading = filled($period) || filled($organisation);
@endphp

<div class="relative pl-8 sm:pl-0">
    <span class="absolute left-[0.4375rem] top-2 h-2.5 w-2.5 rounded-full border-2 border-accent-400 bg-ink-950 sm:left-[-0.3125rem]" aria-hidden="true"></span>

    <div class="sm:grid sm:grid-cols-12 sm:gap-6">
        <div class="sm:col-span-3">
            @if (filled($period))
                <p class="font-mono text-xs uppercase tracking-[0.14em] text-bone-400">
                    {{ $period }}
                </p>
            @endif
        </div>

        <div class="mt-1 sm:col-span-9 sm:mt-0">
            <h3 class="text-base font-semibold">{{ $title }}</h3>

            @if (filled($organisation))
                <p class="mt-0.5 text-sm text-accent-400">{{ $organisation }}</p>
            @endif

            @if (! $slot->isEmpty())
                <div class="mt-2 text-sm leading-relaxed text-bone-400">
                    {{ $slot }}
                </div>
            @endif
        </div>
    </div>

    @if ($hasHeading || ! $slot->isEmpty())
        <div class="mt-6 border-t border-ink-700/70 sm:mt-6"></div>
    @endif
</div>