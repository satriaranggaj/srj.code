@php
    /**
     * Grouped technology list.
     *
     * SOURCE OF TRUTH — two sources exist, in this order:
     *
     *   config('portfolio.stack_groups')  static baseline, grouped by role. Always
     *                                      present, so the section can never be empty.
     *   the `skills` table                owner-editable technologies, grouped by their
     *                                      own `category`.
     *
     * Both are rendered, but a database technology whose name already appears in the
     * configured group is skipped. That removes the duplicate-badge artefact without
     * making the database authoritative, which would degrade the page on a database
     * whose `skills` rows are incomplete.
     *
     * No technology is ever invented here: the page can only show what the config or
     * the database actually contains.
     *
     * @var \Illuminate\Support\Collection<int, \App\Models\Skill> $skills
     */
    $configuredGroups = config('portfolio.stack_groups', []);
    $configuredLabels = collect($configuredGroups)->pluck('label');

    $databaseSkills = collect($skills)
        ->filter(fn ($skill) => filled($skill->category))
        ->groupBy('category');
@endphp

<div {{ $attributes->merge(['class' => 'space-y-10']) }}>
    @foreach ($configuredGroups as $group)
        @php
            $seen = collect($group['items'])->map(fn ($item) => mb_strtolower($item));
            $extra = collect($databaseSkills[$group['label']] ?? [])
                ->reject(fn ($skill) => $seen->contains(mb_strtolower($skill->display_name)));
        @endphp

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

                @foreach ($extra as $skill)
                    <li>
                        <x-portfolio.skill-badge :name="$skill->display_name" :url="$skill->url" />
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach

    {{-- Owner-added categories that the configured vocabulary does not cover. --}}
    @foreach ($databaseSkills as $category => $categorySkills)
        @continue($configuredLabels->contains($category))

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
    @endforeach
</div>