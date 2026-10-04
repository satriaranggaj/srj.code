<x-app-layout title="Certificates" subtitle="{{ $certificates->count() }} {{ Str::plural('certificate', $certificates->count()) }} recorded.">
    <x-slot name="actions">
        <x-admin.button :href="route('certificate.create')" size="sm" icon="arrow-right">
            <span class="hidden sm:inline">Add certificate</span>
            <span class="sm:hidden">Add</span>
        </x-admin.button>
    </x-slot>

    @forelse ($certificates as $certificate)
        <article class="mb-4 overflow-hidden rounded-xl border border-ink-700/80 bg-ink-850/50 transition-colors duration-150 hover:border-ink-600">
            <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-start sm:gap-5 sm:p-5">
                @if ($certificate->imageUrl())
                    <a href="{{ route('certificate.edit', $certificate->id) }}" class="shrink-0">
                        <img
                            src="{{ $certificate->imageUrl() }}"
                            alt=""
                            width="160"
                            height="90"
                            loading="lazy"
                            decoding="async"
                            class="aspect-video w-full rounded-lg border border-ink-700 object-cover sm:w-40"
                        >
                    </a>
                @else
                    <span
                        class="inline-flex aspect-video w-full shrink-0 items-center justify-center rounded-lg border border-dashed border-ink-600 sm:w-40"
                        aria-hidden="true"
                    >
                        <x-portfolio.icon name="award" class="h-5 w-5 text-bone-400" />
                    </span>
                @endif

                <div class="min-w-0 flex-1">
                    <h2 class="text-sm font-semibold text-bone-50">
                        <a href="{{ route('certificate.edit', $certificate->id) }}" class="rounded hover:text-accent-400">
                            {{ $certificate->title }}
                        </a>
                    </h2>

                    <div class="mt-1.5 flex flex-wrap items-center gap-2">
                        @if ($certificate->issuer)
                            <x-admin.badge tone="accent">{{ $certificate->issuer }}</x-admin.badge>
                        @else
                            <span class="font-mono text-xs text-bone-400">no issuer recorded</span>
                        @endif

                        @if ($certificate->issued_at)
                            <span class="inline-flex items-center gap-1.5 font-mono text-xs text-bone-500">
                                <x-portfolio.icon name="calendar" class="h-3 w-3" />
                                {{ $certificate->issued_at->format('M Y') }}
                            </span>
                        @endif

                        <span class="font-mono text-xs text-bone-400">order {{ $certificate->sort_order }}</span>
                    </div>

                    @if ($certificate->description)
                        <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-bone-400">
                            {{ $certificate->description }}
                        </p>
                    @endif

                    @if ($certificate->credentialUrl)
                        <a
                            href="{{ $certificate->credentialUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mt-2 inline-flex items-center gap-1.5 rounded text-xs font-medium text-bone-400 transition-colors hover:text-accent-400"
                        >
                            View credential
                            <x-portfolio.icon name="external" class="h-3 w-3" />
                            <span class="sr-only">for {{ $certificate->title }} (opens in a new tab)</span>
                        </a>
                    @endif
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <x-admin.button :href="route('certificate.edit', $certificate->id)" variant="secondary" size="sm" icon="terminal">
                        Edit
                    </x-admin.button>

                    {{-- Preserves the existing delete confirmation chain. --}}
                    <x-admin.button
                        variant="danger-ghost"
                        size="sm"
                        icon="close"
                        data-toggle="delete-button"
                        :href="route('certificate.destroy', $certificate->id)"
                    >
                        <span class="sr-only">Delete {{ $certificate->title }}</span>
                    </x-admin.button>
                </div>
            </div>
        </article>
    @empty
        <x-admin.empty-state
            icon="award"
            title="No certificates yet"
            description="Add a certificate with its issuer and credential link. Nothing is ever added by hand."
        >
            <x-admin.button :href="route('certificate.create')" icon="arrow-right">
                Add your first certificate
            </x-admin.button>
        </x-admin.empty-state>
    @endforelse
</x-app-layout>