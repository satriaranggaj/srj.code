@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<header class="border-b border-ink-700/70 bg-ink-900/40">
    <div class="container-page py-14 sm:py-20">
        <div class="max-w-3xl" data-reveal>
            @if ($eyebrow)
                <p class="mb-4 font-mono text-xs uppercase tracking-[0.18em] text-accent-400">
                    {{ $eyebrow }}
                </p>
            @endif

            <h1 class="text-3xl font-semibold tracking-tightest sm:text-4xl lg:text-5xl">
                {{ $title }}
            </h1>

            @if ($description)
                <p class="mt-5 max-w-prose text-base leading-relaxed text-bone-400 sm:text-lg">
                    {{ $description }}
                </p>
            @endif
        </div>
    </div>
</header>