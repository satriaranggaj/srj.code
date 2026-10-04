<x-layouts.portfolio
    seo-title="About"
    seo-description="How {{ config('portfolio.name') }} works as a full stack developer: focus areas, engineering principles, technologies and current direction."
>
    <x-portfolio.page-header
        eyebrow="About"
        title="About me"
        description="A short account of what I build, how I work, and where I am heading next."
    />

    {{-- ========================== ABOUT ME ========================= --}}
    <section class="border-b border-ink-700/70 py-16 sm:py-20" aria-labelledby="about-me">
        <div class="container-page">
            <div class="grid gap-10 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <h2 id="about-me" class="font-mono text-xs uppercase tracking-[0.18em] text-accent-400">
                        About me
                    </h2>

                    <div class="mt-6 max-w-prose space-y-4 text-base leading-relaxed text-bone-300">
                        <p>
                            I am {{ config('portfolio.name') }}, a full stack developer working across
                            Laravel, Vue and TypeScript on the front of a stack, and Python and FastAPI
                            behind it. Most of what I build ends up as a working product: an interface, an
                            API, a database and a Linux server that keeps it online.
                        </p>

                        <p>
                            My interest in AI is practical rather than academic. I have built retrieval systems
                            that match a product photograph to a catalogue entry using vision embeddings and a
                            vector index, wrapped behind a FastAPI service and consumed by a Laravel
                            application. The interesting part is always the seam between the two runtimes.
                        </p>

                        <p>
                            I care about the unglamorous parts — input validation, error states, responsive
                            layouts, accessible markup, and code that a new developer can read at 2am
                            without guessing. Those decisions compound across every project.
                        </p>
                    </div>
                </div>

                <aside class="lg:col-span-1">
                    <dl class="space-y-5 rounded-xl border border-ink-700/80 bg-ink-850/40 p-6">
                        <div>
                            <dt class="font-mono text-[0.7rem] uppercase tracking-[0.16em] text-bone-500">
                                Role
                            </dt>
                            <dd class="mt-1 text-sm text-bone-200">{{ config('portfolio.role') }}</dd>
                        </div>

                        <div>
                            <dt class="font-mono text-[0.7rem] uppercase tracking-[0.16em] text-bone-500">
                                Focus
                            </dt>
                            <dd class="mt-1 text-sm text-bone-200">{{ config('portfolio.role_secondary') }}</dd>
                        </div>

                        <div>
                            <dt class="font-mono text-[0.7rem] uppercase tracking-[0.16em] text-bone-500">
                                Based in
                            </dt>
                            <dd class="mt-1 text-sm text-bone-200">{{ config('portfolio.location') }}</dd>
                        </div>

                        <div>
                            <dt class="font-mono text-[0.7rem] uppercase tracking-[0.16em] text-bone-500">
                                Availability
                            </dt>
                            <dd class="mt-1 text-sm text-accent-400">{{ config('portfolio.availability') }}</dd>
                        </div>
                    </dl>

                    @php
                        $github = collect(config('portfolio.contact'))->firstWhere('label', 'GitHub');
                    @endphp

                    @if ($github && $github['enabled'])
                        <div class="mt-4 rounded-xl border border-ink-700/80 bg-ink-850/40 p-6">
                            <x-portfolio.social-link
                                :label="'GitHub · '.$github['handle']"
                                :href="$github['url']"
                                icon="github"
                            />
                        </div>
                    @endif
                </aside>
            </div>
        </div>
    </section>

    {{-- ======================== WHAT I FOCUS ON =================== --}}
    <section class="border-b border-ink-700/70 py-16 sm:py-20" aria-labelledby="focus">
        <div class="container-page">
            <x-portfolio.section-heading
                id="focus"
                label="Focus"
                title="What I focus on"
                description="Four categories of work, each taken from the first sketch to a running server."
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

    {{-- ========================== HOW I WORK ======================= --}}
    <section class="border-b border-ink-700/70 py-16 sm:py-20" aria-labelledby="how-i-work">
        <div class="container-page">
            <x-portfolio.section-heading
                id="how-i-work"
                label="Method"
                title="How I work"
                description="The same four habits on every project, regardless of stack."
            />

            <ol class="mt-12 grid gap-5 sm:grid-cols-2">
                @foreach (config('portfolio.working_principles') as $index => $principle)
                    <li
                        class="rounded-xl border border-ink-700/80 bg-ink-850/40 p-6"
                        data-reveal
                    >
                        <span class="font-mono text-xs text-accent-400">
                            {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                        </span>

                        <h3 class="mt-3 text-base font-semibold">{{ $principle['title'] }}</h3>

                        <p class="mt-2 text-sm leading-relaxed text-bone-400">
                            {{ $principle['description'] }}
                        </p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- =========================== TECHNOLOGY ======================= --}}
    <section class="border-b border-ink-700/70 py-16 sm:py-20" aria-labelledby="technology">
        <div class="container-page">
            <x-portfolio.section-heading
                id="technology"
                label="Technology"
                title="Tools I work with"
                description="Grouped by role. No invented proficiency scores — a badge means it shipped."
            />

            <x-portfolio.tech-stack :skills="$skills" class="mt-12" />
        </div>
    </section>

    {{-- ======================= CURRENT DIRECTION ================== --}}
    <section class="py-16 sm:py-20" aria-labelledby="current-direction">
        <div class="container-page">
            <div class="max-w-3xl rounded-2xl border border-ink-700 bg-ink-900 p-8 sm:p-12" data-reveal>
                <h2 id="current-direction" class="font-mono text-xs uppercase tracking-[0.18em] text-accent-400">
                    Current direction
                </h2>

                <div class="mt-6 space-y-4 text-base leading-relaxed text-bone-300">
                    <p>
                        I am consolidating the AI retrieval work into something more production-shaped:
                        faster indexing, more predictable evaluation, and failure modes that are visible
                        rather than silent.
                    </p>

                    <p>
                        On the application side I am tightening my Laravel and TypeScript foundations —
                        testing, typed contracts between the Laravel API and the Python service, and
                        deployment that is reproducible instead of documented-from-memory.
                    </p>
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <x-portfolio.button :href="route('project')" icon="arrow-right">
                        See the work
                    </x-portfolio.button>

                    <x-portfolio.button :href="route('contact')" variant="secondary">
                        Start a conversation
                    </x-portfolio.button>
                </div>
            </div>
        </div>
    </section>
</x-layouts.portfolio>