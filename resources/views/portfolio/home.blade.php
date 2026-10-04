<x-layouts.portfolio
    :seo-title="null"
    :seo-description="config('portfolio.tagline')"
    :seo-json-ld="[
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => config('portfolio.name'),
        'url' => url('/'),
        'description' => config('portfolio.description'),
        'inLanguage' => 'en',
    ]"
>
    {{-- ============================ HERO ============================ --}}
    <section class="relative overflow-hidden border-b border-ink-700/70">
        {{-- Restrained, single-accent backdrop. No gradients on content. --}}
        <div
            aria-hidden="true"
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(60rem_32rem_at_50%_-10%,rgba(52,211,153,0.10),transparent_70%)]"
        ></div>

        <div class="container-page relative py-20 sm:py-28 lg:py-32">
            <div class="max-w-3xl">
                <p
                    class="inline-flex items-center gap-2 rounded-full border border-ink-700 bg-ink-850/60 px-3 py-1 font-mono text-xs text-bone-300"
                    data-reveal
                >
                    <span class="h-1.5 w-1.5 rounded-full bg-accent-400" aria-hidden="true"></span>
                    {{ config('portfolio.availability') }}
                </p>

                <h1
                    class="mt-6 text-4xl font-semibold tracking-tightest sm:text-5xl lg:text-6xl"
                    data-reveal
                >
                    {{ config('portfolio.name') }}
                </h1>

                <p class="mt-4 text-xl text-bone-200 sm:text-2xl" data-reveal>
                    {{ config('portfolio.role') }}
                    <span class="text-bone-500" aria-hidden="true">·</span>
                    <span class="text-accent-400">{{ config('portfolio.role_secondary') }}</span>
                </p>

                <p class="mt-6 max-w-prose text-base leading-relaxed text-bone-400 sm:text-lg" data-reveal>
                    {{ config('portfolio.tagline') }}
                </p>

                <div class="mt-9 flex flex-wrap items-center gap-3" data-reveal>
                    <x-portfolio.button :href="route('project')" size="lg" icon="arrow-right">
                        View projects
                    </x-portfolio.button>

                    <x-portfolio.button :href="route('contact')" variant="secondary" size="lg">
                        Contact me
                    </x-portfolio.button>

                    @php
                        $githubLink = collect(config('portfolio.contact'))->firstWhere('label', 'GitHub');
                    @endphp

                    @if ($githubLink && $githubLink['enabled'])
                        <x-portfolio.button
                            :href="$githubLink['url']"
                            variant="ghost"
                            size="lg"
                            icon="github"
                            external
                        >
                            GitHub
                        </x-portfolio.button>
                    @endif
                </div>

                {{-- Only technologies that appear in real project data or in the configured stack. --}}
                @php
                    $heroStack = collect(config('portfolio.stack_groups'))
                        ->pluck('items')
                        ->flatten()
                        ->filter(fn ($item) => in_array($item, ['Laravel', 'Vue', 'TypeScript', 'Python', 'FastAPI', 'Tailwind CSS', 'Linux'], true))
                        ->values();
                @endphp

                @if ($heroStack->isNotEmpty())
                    <ul class="mt-12 flex flex-wrap gap-2" aria-label="Core technologies" data-reveal>
                        @foreach ($heroStack as $technology)
                            <li>
                                <x-portfolio.skill-badge :name="$technology" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </section>

    {{-- ======================= SELECTED WORK ======================= --}}
    @php
        $showcase = $featuredProjects->isNotEmpty() ? $featuredProjects : $recentProjects;
    @endphp

    <section class="border-b border-ink-700/70 py-20 sm:py-24" aria-labelledby="selected-work">
        <div class="container-page">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <x-portfolio.section-heading
                    id="selected-work"
                    label="Selected work"
                    title="Case studies, not screenshots"
                    description="Each project links straight to the build, the source, or a full breakdown of how it works."
                />

                <x-portfolio.button :href="route('project')" variant="secondary" icon="arrow-right" class="shrink-0">
                    All projects
                </x-portfolio.button>
            </div>

            @if ($showcase->isNotEmpty())
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($showcase as $project)
                        <x-portfolio.project-card :project="$project" data-reveal />
                    @endforeach
                </div>
            @else
                <div class="mt-12">
                    <x-portfolio.empty-state
                        icon="layers"
                        title="No projects published yet"
                        description="Projects added in the dashboard appear here automatically, newest first."
                    />
                </div>
            @endif
        </div>
    </section>

    {{-- ======================== WHAT I BUILD ======================= --}}
    <section class="border-b border-ink-700/70 py-20 sm:py-24" aria-labelledby="what-i-build">
        <div class="container-page">
            <x-portfolio.section-heading
                id="what-i-build"
                label="What I build"
                title="The four things I work on"
                description="A narrow, deliberate scope — and I take each of them from interface to deployment."
            />

            <div class="mt-12 grid gap-5 sm:grid-cols-2">
                @foreach (config('portfolio.capabilities') as $index => $capability)
                    <x-portfolio.capability-card
                        :title="$capability['title']"
                        :icon="['layers', 'sparkles', 'terminal', 'server'][$index] ?? 'layers'"
                        data-reveal
                    >
                        {{ $capability['description'] }}
                    </x-portfolio.capability-card>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ========================= TECH STACK ======================== --}}
    <section class="border-b border-ink-700/70 py-20 sm:py-24" aria-labelledby="tech-stack">
        <div class="container-page">
            <x-portfolio.section-heading
                id="tech-stack"
                label="Tech stack"
                title="Technologies I ship with"
                description="A badge means the technology was used in real work. There are no invented proficiency scores."
            />

            @php
                $dbSkills = $skills->filter(fn ($skill) => filled($skill->category))->groupBy('category');
                $hasDbSkills = $dbSkills->isNotEmpty();
            @endphp

            <div class="mt-12 space-y-10">
                @foreach (config('portfolio.stack_groups') as $group)
                    <div data-reveal>
                        <h3 class="font-mono text-xs uppercase tracking-[0.16em] text-bone-500">
                            {{ $group['label'] }}
                        </h3>

                        <ul class="mt-4 flex flex-wrap gap-2">
                            @foreach ($group['items'] as $item)
                                <li>
                                    <x-portfolio.skill-badge :name="$item" />
                                </li>
                            @endforeach

                            {{-- Technology recorded in the dashboard, grouped by its own category. --}}
                            @if ($hasDbSkills && ($dbSkills[$group['label']] ?? null))
                                @foreach ($dbSkills[$group['label']] as $skill)
                                    <li>
                                        <x-portfolio.skill-badge :name="$skill->display_name" :url="$skill->url" />
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                @endforeach

                @if ($hasDbSkills)
                    @foreach ($dbSkills as $category => $categorySkills)
                        @if (! in_array($category, collect(config('portfolio.stack_groups'))->pluck('label')->all(), true))
                            <div data-reveal>
                                <h3 class="font-mono text-xs uppercase tracking-[0.16em] text-bone-500">
                                    {{ $category }}
                                </h3>

                                <ul class="mt-4 flex flex-wrap gap-2">
                                    @foreach ($categorySkills as $skill)
                                        <li>
                                            <x-portfolio.skill-badge :name="$skill->display_name" :url="$skill->url" />
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    </section>

    {{-- ==================== DEVELOPMENT JOURNEY =================== --}}
    @php $journey = collect(config('portfolio.journey')); @endphp

    @if ($journey->isNotEmpty())
        <section class="border-b border-ink-700/70 py-20 sm:py-24" aria-labelledby="journey">
            <div class="container-page">
                <x-portfolio.section-heading
                    id="journey"
                    label="Journey"
                    title="How I got here"
                />

                <div class="mt-12 space-y-6">
                    @foreach ($journey as $entry)
                        <x-portfolio.timeline-item
                            :title="$entry['title']"
                            :period="$entry['period'] ?? null"
                            :organisation="$entry['organisation'] ?? null"
                            data-reveal
                        >
                            {{ $entry['description'] ?? '' }}
                        </x-portfolio.timeline-item>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ======================== CERTIFICATES ======================= --}}
    @if ($certificates->isNotEmpty())
        <section class="border-b border-ink-700/70 py-20 sm:py-24" aria-labelledby="certificates">
            <div class="container-page">
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <x-portfolio.section-heading
                        id="certificates"
                        label="Credentials"
                        title="Certificates"
                    />

                    <x-portfolio.button :href="route('certificate')" variant="secondary" icon="arrow-right" class="shrink-0">
                        All certificates
                    </x-portfolio.button>
                </div>

                <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($certificates as $certificate)
                        <x-portfolio.certificate-card :certificate="$certificate" data-reveal />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ========================= CONTACT CTA ======================= --}}
    <section class="py-20 sm:py-28" aria-labelledby="contact-cta">
        <div class="container-page">
            <div
                class="relative overflow-hidden rounded-2xl border border-ink-700 bg-ink-900 p-8 sm:p-12 lg:p-16"
                data-reveal
            >
                <div
                    aria-hidden="true"
                    class="pointer-events-none absolute inset-0 bg-[radial-gradient(40rem_24rem_at_100%_0%,rgba(52,211,153,0.10),transparent_70%)]"
                ></div>

                <div class="relative max-w-2xl">
                    <h2 id="contact-cta" class="text-2xl font-semibold tracking-tightest sm:text-3xl lg:text-4xl">
                        Have a project or opportunity in mind?
                    </h2>

                    <p class="mt-4 max-w-prose text-base leading-relaxed text-bone-400 sm:text-lg">
                        I am open to full-time roles and project work. The fastest way to reach me is WhatsApp —
                        I read every message.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        @php
                            $whatsapp = collect(config('portfolio.contact'))->firstWhere('label', 'WhatsApp');
                        @endphp

                        @if ($whatsapp && $whatsapp['enabled'])
                            <x-portfolio.button :href="$whatsapp['url']" size="lg" icon="whatsapp" external>
                                WhatsApp
                            </x-portfolio.button>
                        @endif

                        <x-portfolio.button :href="route('contact')" variant="secondary" size="lg">
                            All contact options
                        </x-portfolio.button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.portfolio>