@props(['certificate'])

@php
    $credentialUrl = $certificate->credentialUrl;
    $imageUrl = $certificate->imageUrl();
@endphp

<article
    {{ $attributes->merge(['class' => 'group relative flex flex-col overflow-hidden rounded-xl border border-ink-700/80 bg-ink-850/50 transition-all duration-200 hover:-translate-y-0.5 hover:border-ink-600 hover:bg-ink-800/60 hover:shadow-card']) }}
>
    @if ($imageUrl)
        <div class="aspect-[16/9] w-full overflow-hidden border-b border-ink-700/80 bg-ink-800">
            <img
                src="{{ $imageUrl }}"
                alt="{{ $certificate->title }} certificate"
                width="640"
                height="360"
                loading="lazy"
                decoding="async"
                class="h-full w-full object-cover transition-transform duration-500 motion-safe:group-hover:scale-[1.02]"
            />
        </div>
    @endif

    <div class="flex flex-1 flex-col p-5">
        <div class="flex items-start justify-between gap-3">
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-ink-600 bg-ink-800 text-accent-400">
                <x-portfolio.icon name="award" class="h-4 w-4" />
            </span>

            @if ($certificate->issued_at)
                <span class="inline-flex items-center gap-1.5 font-mono text-xs text-bone-500">
                    <x-portfolio.icon name="calendar" class="h-3.5 w-3.5" />
                    <time datetime="{{ $certificate->issued_at->toDateString() }}">
                        {{ $certificate->issued_at->format('M Y') }}
                    </time>
                </span>
            @endif
        </div>

        <h3 class="mt-4 text-base font-semibold leading-snug">
            @if ($credentialUrl)
                {{-- Focus ring intentionally kept visible on the primary link. --}}
                <a
                    href="{{ $credentialUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="after:absolute after:inset-0"
                >
                    {{ $certificate->title }}
                </a>
            @else
                {{ $certificate->title }}
            @endif
        </h3>

        @if (filled($certificate->issuer))
            <p class="mt-1 text-sm text-accent-400">{{ $certificate->issuer }}</p>
        @endif

        @if (filled($certificate->description))
            <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-bone-400">
                {{ $certificate->description }}
            </p>
        @endif

        @if ($credentialUrl)
            <p class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-bone-300 transition-colors duration-200 group-hover:text-accent-400">
                View credential
                <x-portfolio.icon name="external" class="h-3.5 w-3.5" />
            </p>
        @endif
    </div>
</article>