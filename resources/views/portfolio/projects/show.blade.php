@php
    /**
     * Sections render only when there is real content behind them. Nothing is
     * invented to fill a heading.
     */
    $overview = $project->description;
    $highlights = $project->highlights;
    $screenshots = $project->screenshots;
    $techStack = $project->tech_stack;
    $summary = $project->summary;
    $liveUrl = $project->resolved_live_url;
    $githubUrl = $project->github_url;

    $caseStudyJsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'CreativeWork',
        'name' => $project->title,
        'headline' => $project->title,
        'url' => route('project.show', $project),
        'author' => [
            '@type' => 'Person',
            'name' => config('portfolio.name'),
        ],
    ];

    if ($summary) {
        $caseStudyJsonLd['description'] = $summary;
    }

    if ($project->thumbnailStoragePath()) {
        $caseStudyJsonLd['image'] = $project->thumbnailUrl();
    }

    if ($techStack !== []) {
        $caseStudyJsonLd['keywords'] = implode(', ', $techStack);
    }

    if ($project->github_url) {
        $caseStudyJsonLd['codeRepository'] = $project->github_url;
    }

    if ($liveUrl) {
        $caseStudyJsonLd['url'] = $liveUrl;
    }

    if ($project->year) {
        $caseStudyJsonLd['dateCreated'] = $project->year;
    }

    /*
     * Passed as a root-relative storage path, not asset(): the SEO component builds
     * the absolute URL from APP_URL so og:image always matches the canonical host.
     */
    $metaImage = $project->thumbnailStoragePath() ?: config('portfolio.seo.og_image');
@endphp

<x-layouts.portfolio
    :seo-title="$project->title.' — Case study'"
    :seo-description="$summary ?: config('portfolio.description')"
    :seo-image="$metaImage"
    seo-type="article"
    :seo-json-ld="$caseStudyJsonLd"
>
    {{-- ============================ HERO ============================ --}}
    <section class="border-b border-ink-700/70">
        <div class="container-page py-12 sm:py-16">
            <nav aria-label="Breadcrumb" class="mb-8">
                <ol class="flex flex-wrap items-center gap-2 font-mono text-xs text-bone-500">
                    <li>
                        <a href="{{ route('home') }}" class="transition-colors hover:text-bone-200">Home</a>
                    </li>
                    <li aria-hidden="true">/</li>
                    <li>
                        <a href="{{ route('project') }}" class="transition-colors hover:text-bone-200">Projects</a>
                    </li>
                    <li aria-hidden="true">/</li>
                    <li class="text-bone-300" aria-current="page">{{ $project->title }}</li>
                </ol>
            </nav>

            <div class="max-w-3xl">
                <div class="flex flex-wrap items-center gap-2">
                    @if ($project->project_type)
                        <span class="rounded-full border border-ink-600 bg-ink-850/70 px-2.5 py-1 text-[0.7rem] font-medium text-bone-200">
                            {{ $project->project_type }}
                        </span>
                    @endif

                    <span class="rounded-full border border-ink-600 bg-ink-850/70 px-2.5 py-1 text-[0.7rem] font-medium text-bone-200">
                        {{ $project->display_status }}
                    </span>

                    @if ($project->featured)
                        <span class="rounded-full border border-accent-400/40 bg-accent-400/10 px-2.5 py-1 text-[0.7rem] font-medium text-accent-300">
                            Featured
                        </span>
                    @endif
                </div>

                <h1 class="mt-5 text-3xl font-semibold tracking-tightest sm:text-4xl lg:text-5xl">
                    {{ $project->title }}
                </h1>

                @if ($summary)
                    <p class="mt-5 max-w-prose text-base leading-relaxed text-bone-400 sm:text-lg">
                        {{ $summary }}
                    </p>
                @endif

                <dl class="mt-8 grid gap-x-10 gap-y-4 border-t border-ink-700/70 pt-6 sm:grid-cols-3">
                    @if ($project->role)
                        <div>
                            <dt class="font-mono text-[0.7rem] uppercase tracking-[0.16em] text-bone-500">Role</dt>
                            <dd class="mt-1 text-sm text-bone-200">{{ $project->role }}</dd>
                        </div>
                    @endif

                    @if ($project->year)
                        <div>
                            <dt class="font-mono text-[0.7rem] uppercase tracking-[0.16em] text-bone-500">Year</dt>
                            <dd class="mt-1 text-sm text-bone-200">{{ $project->year }}</dd>
                        </div>
                    @endif

                    @if ($techStack !== [])
                        <div>
                            <dt class="font-mono text-[0.7rem] uppercase tracking-[0.16em] text-bone-500">Scope</dt>
                            <dd class="mt-1 text-sm text-bone-200">Full stack build</dd>
                        </div>
                    @endif
                </dl>

                {{-- Links render only when they exist. Never fabricated. --}}
                @if ($liveUrl || $githubUrl)
                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        @if ($liveUrl)
                            <x-portfolio.button :href="$liveUrl" size="lg" icon="external" external>
                                Live demo
                            </x-portfolio.button>
                        @endif

                        @if ($githubUrl)
                            <x-portfolio.button :href="$githubUrl" variant="secondary" size="lg" icon="github" external>
                                Source code
                            </x-portfolio.button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ========================= THUMBNAIL ========================= --}}
    @if ($project->thumbnailUrl())
        <div class="border-b border-ink-700/70">
            <div class="container-page py-12">
                <div class="overflow-hidden rounded-xl border border-ink-700/80 bg-ink-850">
                    <img
                        src="{{ $project->thumbnailUrl() }}"
                        alt="{{ $project->title }} interface"
                        width="1280"
                        height="720"
                        fetchpriority="high"
                        decoding="async"
                        class="aspect-[16/9] w-full object-cover"
                    />
                </div>
            </div>
        </div>
    @endif

    {{-- ========================== OVERVIEW ========================= --}}
    @if ($overview)
        <section class="border-b border-ink-700/70 py-16 sm:py-20" aria-labelledby="overview">
            <div class="container-page">
                <h2 id="overview" class="font-mono text-xs uppercase tracking-[0.18em] text-accent-400">Overview</h2>

                <div class="mt-5 max-w-prose space-y-4 text-base leading-relaxed text-bone-300">
                    @foreach (preg_split('/\n\s*\n/', trim($overview)) as $paragraph)
                        @if (filled(trim($paragraph)))
                            <p>{{ trim($paragraph) }}</p>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ===================== PROBLEM / SOLUTION ==================== --}}
    @if (filled($project->problem) || filled($project->solution))
        <section class="border-b border-ink-700/70 py-16 sm:py-20" aria-labelledby="approach">
            <div class="container-page">
                <x-portfolio.section-heading
                    id="approach"
                    label="Approach"
                    title="Problem and solution"
                />

                <div class="mt-10 grid gap-6 lg:grid-cols-2">
                    @if (filled($project->problem))
                        <div class="rounded-xl border border-ink-700/80 bg-ink-850/40 p-6" data-reveal>
                            <h3 class="font-mono text-xs uppercase tracking-[0.16em] text-bone-500">The problem</h3>
                            <div class="mt-3 space-y-4 text-sm leading-relaxed text-bone-300">
                                @foreach (preg_split('/\n\s*\n/', trim($project->problem)) as $paragraph)
                                    @if (filled(trim($paragraph)))
                                        <p>{{ trim($paragraph) }}</p>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if (filled($project->solution))
                        <div class="rounded-xl border border-ink-700/80 bg-ink-850/40 p-6" data-reveal>
                            <h3 class="font-mono text-xs uppercase tracking-[0.16em] text-accent-400">The solution</h3>
                            <div class="mt-3 space-y-4 text-sm leading-relaxed text-bone-300">
                                @foreach (preg_split('/\n\s*\n/', trim($project->solution)) as $paragraph)
                                    @if (filled(trim($paragraph)))
                                        <p>{{ trim($paragraph) }}</p>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- ======================= KEY FEATURES ======================== --}}
    @if ($highlights !== [])
        <section class="border-b border-ink-700/70 py-16 sm:py-20" aria-labelledby="key-features">
            <div class="container-page">
                <x-portfolio.section-heading
                    id="key-features"
                    label="Capabilities"
                    title="Key features"
                />

                <ul class="mt-10 grid gap-4 sm:grid-cols-2">
                    @foreach ($highlights as $highlight)
                        <li
                            class="flex gap-3 rounded-xl border border-ink-700/80 bg-ink-850/40 p-5"
                            data-reveal
                        >
                            <x-portfolio.icon
                                name="check"
                                class="mt-0.5 h-4 w-4 shrink-0 text-accent-400"
                            />
                            <span class="text-sm leading-relaxed text-bone-300">{{ $highlight }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- ======================== TECH STACK ========================= --}}
    @if ($techStack !== [])
        <section class="border-b border-ink-700/70 py-16 sm:py-20" aria-labelledby="tech-stack">
            <div class="container-page">
                <x-portfolio.section-heading
                    id="tech-stack"
                    label="Built with"
                    title="Tech stack"
                />

                <ul class="mt-8 flex flex-wrap gap-2">
                    @foreach ($techStack as $technology)
                        <li>
                            <x-portfolio.skill-badge :name="$technology" />
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- ========================= CHALLENGES ======================== --}}
    @if (filled($project->challenges))
        <section class="border-b border-ink-700/70 py-16 sm:py-20" aria-labelledby="challenges">
            <div class="container-page">
                <x-portfolio.section-heading id="challenges" label="Reality" title="Challenges" />

                <div class="mt-6 max-w-prose space-y-4 text-base leading-relaxed text-bone-300">
                    @foreach (preg_split('/\n\s*\n/', trim($project->challenges)) as $paragraph)
                        @if (filled(trim($paragraph)))
                            <p>{{ trim($paragraph) }}</p>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ========================== OUTCOME ========================== --}}
    @if (filled($project->outcome))
        <section class="border-b border-ink-700/70 py-16 sm:py-20" aria-labelledby="outcome">
            <div class="container-page">
                <x-portfolio.section-heading id="outcome" label="Result" title="Outcome" />

                <div class="mt-6 max-w-prose space-y-4 text-base leading-relaxed text-bone-300">
                    @foreach (preg_split('/\n\s*\n/', trim($project->outcome)) as $paragraph)
                        @if (filled(trim($paragraph)))
                            <p>{{ trim($paragraph) }}</p>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ========================= SCREENSHOTS ======================== --}}
    @if ($screenshots !== [])
        <section class="border-b border-ink-700/70 py-16 sm:py-20" aria-labelledby="screenshots">
            <div class="container-page">
                <x-portfolio.section-heading id="screenshots" label="Interface" title="Screenshots" />

                <div class="mt-10 grid gap-5 sm:grid-cols-2">
                    @foreach ($screenshots as $index => $screenshot)
                        <img
                            src="{{ asset('storage/'.$screenshot) }}"
                            alt="{{ $project->title }} screenshot {{ $index + 1 }}"
                            width="1280"
                            height="720"
                            loading="lazy"
                            decoding="async"
                            class="w-full rounded-xl border border-ink-700/80 object-cover"
                            data-reveal
                        />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ========================== LINKS ============================ --}}
    @if ($liveUrl || $githubUrl)
        <section class="border-b border-ink-700/70 py-16 sm:py-20" aria-labelledby="links">
            <div class="container-page">
                <h2 id="links" class="font-mono text-xs uppercase tracking-[0.18em] text-accent-400">Links</h2>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    @if ($liveUrl)
                        <a
                            href="{{ $liveUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="group flex items-center justify-between gap-4 rounded-xl border border-ink-700/80 bg-ink-850/40 p-5 transition-all duration-200 hover:border-ink-600 hover:bg-ink-800/60"
                        >
                            <span>
                                <span class="block text-sm font-medium text-bone-100">Live demo</span>
                                <span class="mt-0.5 block truncate font-mono text-xs text-bone-500">{{ $liveUrl }}</span>
                            </span>
                            <x-portfolio.icon name="external" class="h-4 w-4 shrink-0 text-bone-500 transition-colors group-hover:text-accent-400" />
                        </a>
                    @endif

                    @if ($githubUrl)
                        <a
                            href="{{ $githubUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="group flex items-center justify-between gap-4 rounded-xl border border-ink-700/80 bg-ink-850/40 p-5 transition-all duration-200 hover:border-ink-600 hover:bg-ink-800/60"
                        >
                            <span>
                                <span class="block text-sm font-medium text-bone-100">Source code</span>
                                <span class="mt-0.5 block truncate font-mono text-xs text-bone-500">{{ $githubUrl }}</span>
                            </span>
                            <x-portfolio.icon name="github" class="h-4 w-4 shrink-0 text-bone-500 transition-colors group-hover:text-accent-400" />
                        </a>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- ======================== MORE PROJECTS ======================= --}}
    @if ($moreProjects->isNotEmpty())
        <section class="py-16 sm:py-20" aria-labelledby="more-projects">
            <div class="container-page">
                <x-portfolio.section-heading id="more-projects" label="Keep reading" title="More projects" />

                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($moreProjects as $more)
                        <x-portfolio.project-card :project="$more" data-reveal />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.portfolio>