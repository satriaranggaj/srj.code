<x-app-layout title="Dashboard" subtitle="Manage everything the public portfolio renders from here.">
    <x-slot name="actions">
        <x-admin.button :href="route('project.create')" size="sm" icon="arrow-right">
            <span class="hidden sm:inline">Add project</span>
            <span class="sm:hidden">Add</span>
        </x-admin.button>
    </x-slot>

    {{-- ── Greeting ──────────────────────────────────────────────── --}}
    <section class="mb-6 overflow-hidden rounded-xl border border-ink-700/80 bg-ink-850/50">
        <div class="p-6 sm:p-8">
            <p class="font-mono text-xs uppercase tracking-[0.16em] text-accent-400">
                {{ now()->format('l, j F Y') }}
            </p>

            <h2 class="mt-3 text-2xl font-semibold tracking-tightest text-bone-50">
                {{ greeting() }}, {{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}
            </h2>

            <p class="mt-2 max-w-prose text-sm leading-relaxed text-bone-400">
                Everything on the public site comes from the records below. Adding a project,
                technology or certificate here is enough — no deploy required.
            </p>
        </div>
    </section>

    {{-- ── Content counts ────────────────────────────────────────── --}}
    {{--
        Only real counts from App\Models. No traffic, revenue or growth metrics,
        because the application does not store them.
    --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card
            label="Projects"
            :value="$stats['projects']"
            icon="briefcase"
            :href="route('project.index')"
            :accent="true"
            :hint="$stats['featured'].' featured on the homepage'"
        />

        <x-admin.stat-card
            label="Technologies"
            :value="$stats['skills']"
            icon="sparkles"
            :href="route('skill.index')"
        />

        <x-admin.stat-card
            label="Certificates"
            :value="$stats['certificates']"
            icon="award"
            :href="route('certificate.index')"
        />

        <x-admin.stat-card
            label="Messages"
            :value="$stats['messages']"
            icon="mail"
            :href="route('message.index')"
            :hint="$stats['unreadMessages'].' unread'"
        />
    </div>

    {{-- ── Quick actions ─────────────────────────────────────────── --}}
    <section class="mt-6" aria-labelledby="quick-actions">
        <h2 id="quick-actions" class="sr-only">Quick actions</h2>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            {{-- `external` is resolved in PHP: a Blade directive cannot appear inside a component attribute list. --}}
            @foreach ([
                ['label' => 'Add project', 'icon' => 'briefcase', 'href' => route('project.create')],
                ['label' => 'Add technology', 'icon' => 'sparkles', 'href' => route('skill.create')],
                ['label' => 'Add certificate', 'icon' => 'award', 'href' => route('certificate.create')],
                ['label' => 'View portfolio', 'icon' => 'external', 'href' => route('home'), 'external' => true],
            ] as $action)
                <x-admin.button
                    :href="$action['href']"
                    variant="secondary"
                    :icon="$action['icon']"
                    class="justify-start"
                    :target="! empty($action['external']) ? '_blank' : null"
                    :rel="! empty($action['external']) ? 'noopener noreferrer' : null"
                >
                    {{ $action['label'] }}
                </x-admin.button>
            @endforeach
        </div>
    </section>

    {{-- ── Recent activity ───────────────────────────────────────── --}}
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-admin.panel title="Recent projects" :padding="false">
            <x-slot name="header">
                <x-admin.button :href="route('project.index')" variant="ghost" size="sm">
                    View all
                </x-admin.button>
            </x-slot>

            @if ($latestProjects->isEmpty())
                <div class="p-5">
                    <x-admin.empty-state
                        icon="briefcase"
                        title="No projects yet"
                        description="Add your first project to make it appear on your portfolio."
                    >
                        <x-admin.button :href="route('project.create')" size="sm">
                            Add project
                        </x-admin.button>
                    </x-admin.empty-state>
                </div>
            @else
                @foreach ($latestProjects as $project)
                    <a
                        href="{{ route('project.edit', $project->id) }}"
                        class="flex items-center gap-3 border-b border-ink-700/50 px-5 py-3.5 transition-colors duration-150 last:border-0 hover:bg-ink-800/50"
                    >
                        @if ($project->thumbnailUrl())
                            <img
                                src="{{ $project->thumbnailUrl() }}"
                                alt=""
                                width="56"
                                height="32"
                                loading="lazy"
                                decoding="async"
                                class="h-8 w-14 shrink-0 rounded border border-ink-700 object-cover"
                            />
                        @else
                            <span
                                class="inline-flex h-8 w-14 shrink-0 items-center justify-center rounded border border-dashed border-ink-600 font-mono text-[0.65rem] text-bone-400"
                                aria-hidden="true"
                            >—</span>
                        @endif

                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-medium text-bone-100">{{ $project->title }}</span>
                            <span class="block truncate font-mono text-xs text-bone-500">
                                /projects/{{ $project->slug ?: 'no-slug' }}
                            </span>
                        </span>

                        <x-admin.badge :tone="$project->featured ? 'accent' : 'neutral'">
                            {{ $project->display_status }}
                        </x-admin.badge>
                    </a>
                @endforeach
            @endif
        </x-admin.panel>

        <x-admin.panel title="Recent messages" :padding="false">
            <x-slot name="header">
                <x-admin.button :href="route('message.index')" variant="ghost" size="sm">
                    Inbox
                    @if ($stats['unreadMessages'] > 0)
                        <span class="ml-1 font-mono text-xs text-accent-400">{{ $stats['unreadMessages'] }}</span>
                    @endif
                </x-admin.button>
            </x-slot>

            @if ($recentMessages->isEmpty())
                <div class="p-5">
                    <x-admin.empty-state
                        icon="mail"
                        title="No messages yet"
                        description="Enquiries from the contact form arrive here."
                    />
                </div>
            @else
                @foreach ($recentMessages as $message)
                    <div class="border-b border-ink-700/50 px-5 py-3.5 last:border-0">
                        <div class="flex items-start gap-3">
                            <span
                                @class([
                                    'mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full',
                                    'bg-accent-400' => ! $message->is_read,
                                    'bg-ink-600' => $message->is_read,
                                ])
                                aria-hidden="true"
                            ></span>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-bone-100">
                                    {{ $message->subject ?: '(no subject)' }}
                                </p>

                                <p class="mt-0.5 truncate text-xs text-bone-500">
                                    {{ $message->name }} · {{ $message->created_at->diffForHumans() }}
                                </p>
                            </div>

                            @unless ($message->is_read)
                                <span class="sr-only">Unread</span>
                                <x-admin.badge tone="accent">Unread</x-admin.badge>
                            @endunless
                        </div>
                    </div>
                @endforeach
            @endif
        </x-admin.panel>
    </div>
</x-app-layout>