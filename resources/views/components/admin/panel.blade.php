@props([
    'title' => null,
    'description' => null,
    'padding' => true,
])

<section {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-ink-700/80 bg-ink-850/50']) }}>
    @if ($title || isset($header))
        <header class="flex flex-wrap items-center justify-between gap-3 border-b border-ink-700/70 px-5 py-4">
            <div class="min-w-0">
                @if ($title)
                    <h2 class="text-sm font-semibold text-bone-50">{{ $title }}</h2>
                @endif

                @if ($description)
                    <p class="mt-0.5 text-sm text-bone-500">{{ $description }}</p>
                @endif
            </div>

            @isset($header)
                <div class="flex shrink-0 items-center gap-2">{{ $header }}</div>
            @endisset
        </header>
    @endif

    <div @class(['p-5', 'p-0' => ! $padding])>
        {{ $slot }}
    </div>
</section>