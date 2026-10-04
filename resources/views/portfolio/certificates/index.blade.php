<x-layouts.portfolio
    seo-title="Certificates"
    seo-description="Certificates and credentials earned by {{ config('portfolio.name') }}, a full stack developer working with Laravel, Vue, Python and FastAPI."
>
    <x-portfolio.page-header
        eyebrow="Credentials"
        title="Certificates"
        description="Verified credentials, linked to the issuing organisation wherever a credential URL is available."
    />

    <section class="py-16 sm:py-20">
        <div class="container-page">
            @if ($certificates->isNotEmpty())
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($certificates as $certificate)
                        <x-portfolio.certificate-card :certificate="$certificate" data-reveal />
                    @endforeach
                </div>
            @else
                <x-portfolio.empty-state
                    icon="award"
                    title="No certificates listed yet"
                    description="Certificates added in the dashboard appear here automatically. Nothing is ever added by hand."
                />
            @endif
        </div>
    </section>
</x-layouts.portfolio>