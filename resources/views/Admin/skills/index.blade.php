<x-app-layout title="Technologies" subtitle="{{ $skills->count() }} {{ Str::plural('technology', $skills->count()) }} recorded.">
    <x-slot name="actions">
        <x-admin.button :href="route('skill.create')" size="sm" icon="arrow-right">
            <span class="hidden sm:inline">Add technology</span>
            <span class="sm:hidden">Add</span>
        </x-admin.button>
    </x-slot>

    @forelse ($skills as $skill)
        <article class="mb-3 overflow-hidden rounded-xl border border-ink-700/80 bg-ink-850/50 transition-colors duration-150 hover:border-ink-600">
            <div class="flex items-center gap-4 p-4 sm:p-5">
                @if ($skill->logoUrl())
                    <img
                        src="{{ $skill->logoUrl() }}"
                        alt=""
                        width="40"
                        height="40"
                        loading="lazy"
                        decoding="async"
                        class="h-10 w-10 shrink-0 rounded-lg border border-ink-700 bg-white/5 object-contain p-1"
                    >
                @else
                    <span
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-dashed border-ink-600 font-mono text-[0.65rem] text-bone-400"
                        aria-hidden="true"
                    >—</span>
                @endif

                <div class="min-w-0 flex-1">
                    <h2 class="truncate text-sm font-semibold text-bone-50">{{ $skill->display_name }}</h2>

                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        @if ($skill->category)
                            <x-admin.badge tone="accent">{{ $skill->category }}</x-admin.badge>
                        @else
                            <span class="font-mono text-xs text-bone-400">uncategorised</span>
                        @endif

                        <span class="font-mono text-xs text-bone-400">
                            order {{ $skill->sort_order }}
                        </span>

                        @if ($skill->url)
                            <a
                                href="{{ $skill->url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1 font-mono text-xs text-bone-500 transition-colors hover:text-accent-400"
                            >
                                reference
                                <x-portfolio.icon name="external" class="h-3 w-3" />
                                <span class="sr-only">opens in a new tab</span>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <x-admin.button :href="route('skill.edit', $skill->id)" variant="secondary" size="sm" icon="terminal">
                        Edit
                    </x-admin.button>

                    {{-- Preserves the existing delete confirmation chain. --}}
                    <x-admin.button
                        variant="danger-ghost"
                        size="sm"
                        icon="close"
                        data-toggle="delete-button"
                        :href="route('skill.destroy', $skill->id)"
                    >
                        <span class="sr-only">Delete {{ $skill->display_name }}</span>
                    </x-admin.button>
                </div>
            </div>
        </article>
    @empty
        <x-admin.empty-state
            icon="sparkles"
            title="No technologies yet"
            description="Technologies you add here appear as badges in the tech stack section of your portfolio."
        >
            <x-admin.button :href="route('skill.create')" icon="arrow-right">
                Add your first technology
            </x-admin.button>
        </x-admin.empty-state>
    @endforelse
</x-app-layout>