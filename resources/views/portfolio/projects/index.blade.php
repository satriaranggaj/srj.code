<x-layouts.portfolio
    seo-title="Projects"
    seo-description="Production web applications, AI-powered products and backend systems built by {{ config('portfolio.name') }}."
>
    <x-portfolio.page-header
        eyebrow="Work"
        title="Projects"
        description="Complete products rather than exercises: interfaces, APIs, AI services and the deployment work that keeps them online."
    />

    <section class="py-16 sm:py-20">
        <div class="container-page">
            @if ($projects->isNotEmpty())
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $project)
                        <x-portfolio.project-card :project="$project" data-reveal />
                    @endforeach
                </div>
            @else
                <x-portfolio.empty-state
                    icon="layers"
                    title="No projects published yet"
                    description="Projects added in the dashboard appear here automatically."
                >
                    <x-portfolio.button :href="route('contact')" variant="secondary" size="sm">
                        Get in touch
                    </x-portfolio.button>
                </x-portfolio.empty-state>
            @endif
        </div>
    </section>
</x-layouts.portfolio>