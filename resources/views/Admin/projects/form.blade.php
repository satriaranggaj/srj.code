<x-app-layout
    title="{{ isset($data) ? 'Edit project' : 'New project' }}"
    subtitle="{{ isset($data) ? 'Changes appear on the public portfolio as soon as you save.' : 'Create a project and publish it to your portfolio.' }}"
>
    <x-slot name="actions">
        <x-admin.button :href="route('project.index')" variant="ghost" size="sm" icon="arrow-left">
            <span class="hidden sm:inline">Back</span>
            <span class="sm:hidden">Back</span>
        </x-admin.button>
    </x-slot>

    <form
        method="post"
        action="{{ isset($data) ? route('project.update', $data->id) : route('project.store') }}"
        enctype="multipart/form-data"
        class="mx-auto max-w-3xl space-y-5"
    >
        @csrf
        @isset($data)
            @method('put')
        @endisset

        {{-- ── Basic information ───────────────────────────────────── --}}
        <x-admin.panel title="Basic information" description="Only the title is required. Everything else stays hidden until it has content.">
            <div class="space-y-5">
                <x-admin.field for="title" label="Title" required>
                    <x-admin.input
                        id="title"
                        name="title"
                        type="text"
                        maxlength="150"
                        :value="old('title', $data->title ?? null)"
                        :invalid="$errors->has('title')"
                        placeholder="Lensku"
                        required
                        autofocus
                    />

                    @error('title')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                </x-admin.field>

                <x-admin.field
                    for="slug"
                    label="Slug"
                    help="Leave blank to generate one from the title. This becomes the public URL and changing it after publication breaks existing links."
                >
                    <x-admin.input
                        id="slug"
                        name="slug"
                        type="text"
                        maxlength="180"
                        :value="old('slug', $data->slug ?? null)"
                        :invalid="$errors->has('slug')"
                        placeholder="generated-from-the-title"
                        class="font-mono"
                    />

                    @if (isset($data) && $data->slug)
                        <p class="font-mono text-xs text-bone-500">
                            /projects/{{ $data->slug }}
                        </p>
                    @endif

                    @error('slug')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                </x-admin.field>

                <x-admin.field
                    for="short_description"
                    label="Short description"
                    help="One or two sentences. Used on cards and as the social preview text."
                >
                    <x-admin.textarea
                        id="short_description"
                        name="short_description"
                        rows="2"
                        maxlength="300"
                        :invalid="$errors->has('short_description')"
                        placeholder="AI-powered visual SKU retrieval application."
                    >{{ old('short_description', $data->short_description ?? null) }}</x-admin.textarea>

                    @error('short_description')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                </x-admin.field>

                <x-admin.field
                    for="description"
                    label="Description"
                    help="The case study overview. A blank line starts a new paragraph."
                >
                    <x-admin.textarea
                        id="description"
                        name="description"
                        rows="6"
                        :invalid="$errors->has('description')"
                    >{{ old('description', $data->description ?? null) }}</x-admin.textarea>

                    @error('description')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                </x-admin.field>
            </div>
        </x-admin.panel>

        {{-- ── Media ───────────────────────────────────────────────── --}}
        <x-admin.panel title="Media" description="A 16:9 thumbnail is used on cards, as the case study hero and as the social preview image.">
            <div class="space-y-6">
                <x-admin.field for="thumbnail" label="Thumbnail">
                    @if (isset($data) && $data->thumbnailUrl())
                        <img
                            src="{{ $data->thumbnailUrl() }}"
                            alt="Current thumbnail"
                            class="mb-3 w-full max-w-xs rounded-lg border border-ink-700 object-cover"
                        >
                    @endif

                    <input
                        id="thumbnail"
                        name="thumbnail"
                        type="file"
                        accept="image/jpeg,image/png,image/webp,image/avif"
                        class="block w-full text-sm text-bone-400 file:mr-3 file:rounded-lg file:border-0 file:bg-ink-800 file:px-3.5 file:py-2 file:text-sm file:font-medium file:text-bone-100 file:hover:bg-ink-700"
                    >

                    <p class="mt-1.5 text-xs text-bone-500">JPEG, PNG, WebP or AVIF. Maximum 2 MB.</p>

                    @error('thumbnail')<p class="text-xs text-red-300">{{ $message }}</p>@enderror

                    @if (isset($data) && $data->thumbnail)
                        <x-admin.checkbox
                            class="mt-3"
                            name="remove_thumbnail"
                            value="1"
                            label="Remove current thumbnail"
                        />
                    @endif
                </x-admin.field>

                <x-admin.field
                    for="screenshots"
                    label="Case study screenshots"
                    help="Uploading new screenshots replaces the existing set. Nothing is deleted unless the new files save successfully."
                >
                    <input
                        id="screenshots"
                        name="screenshots[]"
                        type="file"
                        multiple
                        accept="image/jpeg,image/png,image/webp,image/avif"
                        class="block w-full text-sm text-bone-400 file:mr-3 file:rounded-lg file:border-0 file:bg-ink-800 file:px-3.5 file:py-2 file:text-sm file:font-medium file:text-bone-100 file:hover:bg-ink-700"
                    >

                    <p class="mt-1.5 text-xs text-bone-500">Maximum 12 files, 2 MB each.</p>

                    @error('screenshots')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                    @error('screenshots.*')<p class="text-xs text-red-300">{{ $message }}</p>@enderror

                    @if (isset($data) && $data->screenshots !== [])
                        <ul class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                            @foreach ($data->screenshots as $screenshot)
                                <li>
                                    <img
                                        src="{{ asset('storage/'.$screenshot) }}"
                                        alt="Existing case study screenshot"
                                        loading="lazy"
                                        decoding="async"
                                        class="aspect-video w-full rounded-lg border border-ink-700 object-cover"
                                    >
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-admin.field>
            </div>
        </x-admin.panel>

        {{-- ── Classification ─────────────────────────────────────── --}}
        <x-admin.panel title="Classification">
            <div class="space-y-5">
                <x-admin.field for="project_type" label="Project type">
                    <x-admin.select
                        id="project_type"
                        name="project_type"
                        :options="array_combine($projectTypes ?? [], $projectTypes ?? [])"
                        :selected="old('project_type', $data->project_type ?? null)"
                        :placeholder="'— None —'"
                        :invalid="$errors->has('project_type')"
                    />

                    @error('project_type')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                </x-admin.field>

                <x-admin.field for="tech_stack_input" label="Tech stack" help="Comma-separated. Rendered as badges on the project card.">
                    {{--
                        The visible input is free text for usability; a small inline
                        script converts it into the tech_stack[] array the controller
                        validates. It carries a real `name` so a validation failure can
                        restore what was typed - without one the value is never submitted
                        and old() could never have anything to return.

                        This field is deliberately NOT part of the validated payload: the
                        controller builds tech_stack[] from it, and payload() only reads
                        keys that passed validation.
                    --}}
                    <x-admin.input
                        id="tech_stack_input"
                        name="tech_stack_csv"
                        type="text"
                        data-tech-stack-input
                        :value="old('tech_stack_csv', isset($data) ? implode(', ', $data->tech_stack) : '')"
                        placeholder="Laravel, FastAPI, FAISS"
                        class="font-mono"
                    />

                    @error('tech_stack')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                    @error('tech_stack.*')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                </x-admin.field>

                <div class="grid gap-5 sm:grid-cols-3">
                    <x-admin.field for="role" label="Role">
                        <x-admin.input
                            id="role"
                            name="role"
                            type="text"
                            maxlength="120"
                            :value="old('role', $data->role ?? null)"
                            :invalid="$errors->has('role')"
                            placeholder="Full Stack Developer"
                        />

                        @error('role')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                    </x-admin.field>

                    <x-admin.field for="year" label="Year">
                        <x-admin.input
                            id="year"
                            name="year"
                            type="text"
                            maxlength="4"
                            inputmode="numeric"
                            :value="old('year', $data->year ?? null)"
                            :invalid="$errors->has('year')"
                            placeholder="2025"
                            class="font-mono"
                        />

                        @error('year')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                    </x-admin.field>

                    <x-admin.field for="sort_order" label="Sort order" help="Lower numbers appear first.">
                        <x-admin.input
                            id="sort_order"
                            name="sort_order"
                            type="number"
                            min="0"
                            max="65535"
                            :value="old('sort_order', $data->sort_order ?? 0)"
                            :invalid="$errors->has('sort_order')"
                            class="font-mono"
                        />

                        @error('sort_order')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                    </x-admin.field>
                </div>
            </div>
        </x-admin.panel>

        {{-- ── Links ───────────────────────────────────────────────── --}}
        <x-admin.panel title="Links" description="Buttons appear on the public site only when the matching URL is filled in. Nothing is invented.">
            <div class="space-y-5">
                <x-admin.field for="live_url" label="Live URL">
                    <x-admin.input
                        id="live_url"
                        name="live_url"
                        type="url"
                        :value="old('live_url', $data->live_url ?? null)"
                        :invalid="$errors->has('live_url')"
                        placeholder="https://example.com"
                    />

                    @error('live_url')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                </x-admin.field>

                <x-admin.field for="github_url" label="GitHub URL">
                    <x-admin.input
                        id="github_url"
                        name="github_url"
                        type="url"
                        :value="old('github_url', $data->github_url ?? null)"
                        :invalid="$errors->has('github_url')"
                        placeholder="https://github.com/…"
                    />

                    @error('github_url')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                </x-admin.field>

                <x-admin.field
                    for="link"
                    label="Legacy link column"
                    help="Kept for backwards compatibility. The public site falls back to this value when Live URL is empty."
                >
                    <x-admin.input
                        id="link"
                        name="link"
                        type="text"
                        :value="old('link', $data->link ?? null)"
                        :invalid="$errors->has('link')"
                        class="font-mono"
                    />

                    @error('link')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                </x-admin.field>
            </div>
        </x-admin.panel>

        {{-- ── Case study ──────────────────────────────────────────── --}}
        <x-admin.panel title="Case study" description="Each section appears only when it has content. Leave a field blank and its section is hidden entirely.">
            <div class="space-y-5">
                <x-admin.field for="problem" label="Problem">
                    <x-admin.textarea id="problem" name="problem" rows="4" :invalid="$errors->has('problem')">{{ old('problem', $data->problem ?? null) }}</x-admin.textarea>
                    @error('problem')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                </x-admin.field>

                <x-admin.field for="solution" label="Solution">
                    <x-admin.textarea id="solution" name="solution" rows="4" :invalid="$errors->has('solution')">{{ old('solution', $data->solution ?? null) }}</x-admin.textarea>
                    @error('solution')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                </x-admin.field>

                <x-admin.field for="highlights_input" label="Key features" help="One feature per line. Only real features — nothing is generated.">
                    <x-admin.textarea
                        id="highlights_input"
                        name="highlights_csv"
                        rows="4"
                        data-highlights-input
                        placeholder="One feature per line"
                    >{{ old('highlights_csv', isset($data) ? implode("\n", $data->highlights) : '') }}</x-admin.textarea>

                    @error('highlights')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                    @error('highlights.*')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                </x-admin.field>

                <x-admin.field for="challenges" label="Challenges">
                    <x-admin.textarea id="challenges" name="challenges" rows="3" :invalid="$errors->has('challenges')">{{ old('challenges', $data->challenges ?? null) }}</x-admin.textarea>
                    @error('challenges')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                </x-admin.field>

                <x-admin.field for="outcome" label="Outcome">
                    <x-admin.textarea id="outcome" name="outcome" rows="3" :invalid="$errors->has('outcome')">{{ old('outcome', $data->outcome ?? null) }}</x-admin.textarea>
                    @error('outcome')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                </x-admin.field>
            </div>
        </x-admin.panel>

        {{-- ── Publication ─────────────────────────────────────────── --}}
        <x-admin.panel title="Publication">
            <div class="space-y-5">
                <x-admin.field for="status" label="Status" help="Archived projects are withdrawn from the public site and return 404.">
                    <x-admin.select
                        id="status"
                        name="status"
                        :options="[
                            \App\Models\Project::STATUS_LIVE => 'Live — publicly visible',
                            \App\Models\Project::STATUS_IN_PROGRESS => 'In progress — visible with a badge',
                            \App\Models\Project::STATUS_ARCHIVED => 'Archived — withdrawn',
                        ]"
                        :selected="old('status', $data->status ?? \App\Models\Project::STATUS_LIVE)"
                        :invalid="$errors->has('status')"
                    />

                    @error('status')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                </x-admin.field>

                {{-- Hidden first so an unchecked checkbox still submits 0. --}}
                <input type="hidden" name="featured" value="0">

                <x-admin.checkbox
                    name="featured"
                    value="1"
                    :checked="old('featured', $data->featured ?? false)"
                    label="Show in Selected work on the homepage"
                    description="Only applies to publicly visible projects."
                />
            </div>
        </x-admin.panel>

        {{-- ── Actions ─────────────────────────────────────────────── --}}
        <div class="flex flex-wrap items-center gap-3 border-t border-ink-700/70 pt-5">
            @isset($data)
                {{-- Preserves the existing delete confirmation chain. --}}
                <x-admin.button
                    variant="danger-ghost"
                    icon="close"
                    data-toggle="delete-button"
                    :href="route('project.destroy', $data->id)"
                    class="sm:mr-auto"
                >
                    Delete
                </x-admin.button>
            @endisset

            <x-admin.button :href="route('project.index')" variant="ghost">
                Cancel
            </x-admin.button>

            <x-admin.button type="submit">
                {{ isset($data) ? 'Save changes' : 'Create project' }}
            </x-admin.button>
        </div>
    </form>

    @push('scripts')
        <script>
            /*
             * Converts the friendly comma-separated / one-per-line inputs into the
             * array inputs the controller validates. No dependency.
             *
             * The generated inputs are marked and cleared before each run, so a
             * double-clicked submit button cannot append the same values twice and
             * trip the `max` rules. If this script never runs the arrays are simply
             * absent, which validation treats as empty rather than as an error.
             */
            (function () {
                function split(text, separator) {
                    return (text || '')
                        .split(separator)
                        .map(function (value) { return value.trim(); })
                        .filter(function (value) { return value.length > 0; });
                }

                function sync(source, name, values) {
                    if (!source || !source.form) return;

                    var form = source.form;

                    // Remove anything a previous (possibly duplicated) submit added.
                    form.querySelectorAll('input[data-generated-for="' + name + '"]').forEach(function (stale) {
                        stale.parentNode.removeChild(stale);
                    });

                    values.forEach(function (value) {
                        var field = document.createElement('input');
                        field.type = 'hidden';
                        field.name = name + '[]';
                        field.value = value;
                        field.setAttribute('data-generated-for', name);
                        form.appendChild(field);
                    });
                }

                var form = document.querySelector('form[enctype="multipart/form-data"]');
                if (!form) return;

                form.addEventListener('submit', function () {
                    var techStack = document.querySelector('[data-tech-stack-input]');
                    var highlights = document.querySelector('[data-highlights-input]');

                    sync(techStack, 'tech_stack', split(techStack ? techStack.value : '', ','));
                    sync(highlights, 'highlights', split(highlights ? highlights.value : '', /\r?\n/));
                });
            })();
        </script>
    @endpush
</x-app-layout>