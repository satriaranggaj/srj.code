@props([
    'project',
    'showStatus' => true,
])

@php
    $thumbnail = $project->thumbnailUrl();
    $hasCaseStudy = $project->hasCaseStudy();
    $liveUrl = $project->liveUrl;
    $githubUrl = $project->github_url;
    $summary = $project->summary;
    $caseStudyUrl = $hasCaseStudy ? route('project.show', $project) : null;
@endphp

<article
    {{ $attributes->merge(['class' => 'group relative flex flex-col overflow-hidden rounded-xl border border-ink-700/80 bg-ink-850/50 transition-all duration-200 hover:-translate-y-0.5 hover:border-ink-600 hover:bg-ink-800/60 hover:shadow-card-hover']) }}
>
    {{-- Thumbnail: real image when uploaded, designed placeholder otherwise. Never an iframe. --}}
    <div class="relative aspect-[16/9] w-full overflow-hidden border-b border-ink-700/80 bg-ink-800">
        @if ($thumbnail)
            <img
                src="{{ $thumbnail }}"
                alt="{{ $project->title }} interface preview"
                width="640"
                height="360"
                loading="lazy"
                decoding="async"
                class="h-full w-full object-cover transition-transform duration-500 motion-safe:group-hover:scale-[1.02]"
            />
        @else
            <div class="flex h-full w-full flex-col items-center justify-center gap-2 bg-ink-800/60 px-6 text-center">
                <span class="font-mono text-2xl tracking-tightest text-bone-500" aria-hidden="true">
                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($project->title, 0, 2)) }}
                </span>
                <span class="text-[0.7rem] uppercase tracking-[0.16em] text-bone-500">
                    Preview coming soon
                </span>
            </div>
        @endif

        <div class="absolute inset-x-0 top-0 flex flex-wrap items-center gap-2 p-3">
            @if ($project->project_type)
                <span class="rounded-full border border-ink-600/80 bg-ink-950/80 px-2.5 py-1 text-[0.7rem] font-medium text-bone-200 backdrop-blur-sm">
                    {{ $project->project_type }}
                </span>
            @endif

            @if ($showStatus && $project->status !== \App\Models\Project::STATUS_LIVE)
                <span class="rounded-full border border-accent-400/40 bg-accent-400/10 px-2.5 py-1 text-[0.7rem] font-medium text-accent-300">
                    {{ $project->display_status }}
                </span>
            @endif
        </div>
    </div>

    <div class="flex flex-1 flex-col p-5">
        <h3 class="text-base font-semibold leading-snug">
            {{-- Whole-card link target, but only when a case study exists. --}}
            @if ($caseStudyUrl)
                <a href="{{ $caseStudyUrl }}" class="after:absolute after:inset-0 focus-visible:outline-none">
                    {{ $project->title }}
                </a>
            @else
                <span>{{ $project->title }}</span>
            @endif
        </h3>

        @if ($summary)
            <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-bone-400">
                {{ $summary }}
            </p>
        @endif

        @if ($project->tech_stack !== [])
            <ul class="mt-4 flex flex-wrap gap-1.5" aria-label="Technology stack">
                @foreach ($project->tech_stack as $technology)
                    <li>
                        <x-portfolio.skill-badge :name="$technology" />
                    </li>
                @endforeach
            </ul>
        @endif

        {{-- Actions render only when the underlying value actually exists. --}}
        @if ($caseStudyUrl || $liveUrl || $githubUrl || filled($project->role) || filled($project->year))
            <div class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-ink-700/70 pt-4 text-sm">
                @if ($caseStudyUrl)
                    <span class="inline-flex items-center gap-1.5 font-medium text-bone-200">
                        Case study
                        <x-portfolio.icon name="arrow-right" class="h-3.5 w-3.5 transition-transform duration-200 motion-safe:group-hover:translate-x-0.5" />
                    </span>
                @endif

                @if ($liveUrl)
                    <a
                        href="{{ $liveUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="relative z-10 inline-flex items-center gap-1.5 font-medium text-bone-300 transition-colors duration-200 hover:text-accent-400"
                    >
                        Live demo
                        <x-portfolio.icon name="external" class="h-3.5 w-3.5" />
                    </a>
                @endif

                @if ($githubUrl)
                    <a
                        href="{{ $githubUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="relative z-10 inline-flex items-center gap-1.5 font-medium text-bone-300 transition-colors duration-200 hover:text-accent-400"
                    >
                        <x-portfolio.icon name="github" class="h-3.5 w-3.5" />
                        Source
                    </a>
                @endif

                @if (filled($project->role) || filled($project->year))
                    <span class="ml-auto font-mono text-xs text-bone-500">
                        {{ collect([$project->role, $project->year])->filter()->implode(' · ') }}
                    </span>
                @endif
            </div>
        @endif
    </div>
</article>