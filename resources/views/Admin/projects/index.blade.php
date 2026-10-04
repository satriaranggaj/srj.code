<x-app-layout title="Projects" subtitle="{{ $projects->count() }} {{ Str::plural('project', $projects->count()) }} in the database.">
    <x-slot name="actions">
        <x-admin.button :href="route('project.create')" size="sm" icon="arrow-right">
            <span class="hidden sm:inline">Add project</span>
            <span class="sm:hidden">Add</span>
        </x-admin.button>
    </x-slot>

    @forelse ($projects as $project)
        {{-- Structured rows rather than a wide table: nothing is crushed on mobile,
             and every column stays readable from 320px upwards. --}}
        <article class="mb-4 overflow-hidden rounded-xl border border-ink-700/80 bg-ink-850/50 transition-colors duration-150 hover:border-ink-600">
            <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-start sm:gap-5 sm:p-5">
                {{-- Thumbnail / placeholder --}}
                <div class="flex shrink-0 gap-3 sm:block">
                    @if ($project->thumbnailUrl())
                        <a href="{{ route('project.edit', $project->id) }}" class="block">
                            <img
                                src="{{ $project->thumbnailUrl() }}"
                                alt=""
                                width="160"
                                height="90"
                                loading="lazy"
                                decoding="async"
                                class="h-16 w-28 rounded-lg border border-ink-700 object-cover sm:h-[4.5rem] sm:w-40"
                            />
                        </a>
                    @else
                        <span
                            class="inline-flex h-16 w-28 items-center justify-center rounded-lg border border-dashed border-ink-600 font-mono text-xs text-bone-400 sm:h-[4.5rem] sm:w-40"
                            aria-hidden="true"
                        >no thumbnail</span>
                    @endif
                </div>

                {{-- Main content --}}
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-sm font-semibold text-bone-50">
                            <a href="{{ route('project.edit', $project->id) }}" class="rounded hover:text-accent-400">
                                {{ $project->title }}
                            </a>
                        </h2>

                        <x-admin.badge :tone="$project->status === 'live' ? 'success' : ($project->status === 'in_progress' ? 'warning' : 'neutral')">
                            {{ $project->display_status }}
                        </x-admin.badge>

                        @if ($project->featured)
                            <x-admin.badge tone="accent">Featured</x-admin.badge>
                        @endif
                    </div>

                    <p class="mt-1.5 truncate font-mono text-xs text-bone-500">
                        /projects/{{ $project->slug ?: 'no-slug' }}
                    </p>

                    @if ($project->short_description)
                        <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-bone-400">
                            {{ $project->short_description }}
                        </p>
                    @endif

                    {{-- Only fields that exist on the model. --}}
                    <dl class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs">
                        @if ($project->project_type)
                            <div class="flex items-center gap-1.5">
                                <dt class="text-bone-400">Type</dt>
                                <dd class="text-bone-400">{{ $project->project_type }}</dd>
                            </div>
                        @endif

                        @if ($project->tech_stack !== [])
                            <div class="flex items-center gap-1.5">
                                <dt class="text-bone-400">Stack</dt>
                                <dd class="text-bone-400">{{ implode(', ', array_slice($project->tech_stack, 0, 4)) }}</dd>
                            </div>
                        @endif

                        <div class="flex items-center gap-1.5">
                            <dt class="text-bone-400">Updated</dt>
                            <dd class="text-bone-400">{{ $project->updated_at?->diffForHumans() }}</dd>
                        </div>
                    </dl>

                    {{-- Legacy `link` is surfaced because it still exists in the schema. --}}
                    @if ($project->link)
                        <p class="mt-2 truncate font-mono text-xs text-bone-400">
                            legacy link: {{ $project->link }}
                        </p>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex shrink-0 items-center gap-2 sm:flex-col sm:items-stretch">
                    @if ($project->hasCaseStudy())
                        <x-admin.button
                            :href="route('project.show', $project)"
                            variant="secondary"
                            size="sm"
                            icon="external"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex-1 sm:justify-center"
                        >
                            <span class="sm:sr-only">View</span>
                        </x-admin.button>
                    @endif

                    <x-admin.button
                        :href="route('project.edit', $project->id)"
                        variant="secondary"
                        size="sm"
                        icon="terminal"
                        class="flex-1 sm:justify-center"
                    >
                        Edit
                    </x-admin.button>

                    {{--
                        Delete keeps the existing confirmation chain:
                        data-toggle="delete-button" + href -> admin.js -> #form-delete.
                        Changing this would silently break the action.
                    --}}
                    <x-admin.button
                        variant="danger-ghost"
                        size="sm"
                        icon="close"
                        data-toggle="delete-button"
                        :href="route('project.destroy', $project->id)"
                        class="flex-1 sm:justify-center"
                    >
                        <span class="sm:sr-only">Delete {{ $project->title }}</span>
                    </x-admin.button>
                </div>
            </div>
        </article>
    @empty
        <x-admin.empty-state
            icon="briefcase"
            title="No projects yet"
            description="Add your first project to make it appear on your portfolio. You can save a draft with status set to in progress."
        >
            <x-admin.button :href="route('project.create')" icon="arrow-right">
                Add your first project
            </x-admin.button>
        </x-admin.empty-state>
    @endforelse
</x-app-layout>