<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                {{ isset($data) ? 'Edit project' : 'New project' }}
            </h2>

            @isset($data)
                <span class="font-mono text-xs text-gray-400">
                    Public URL: /projects/{{ $data->slug ?: 'no-slug-yet' }}
                </span>
            @endisset
        </div>
    </x-slot>

    <form
        method="post"
        action="{{ isset($data) ? route('project.update', $data->id) : route('project.store') }}"
        enctype="multipart/form-data"
        class="mt-6 space-y-6"
    >
        @csrf
        @isset($data)
            @method('put')
        @endisset

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
                {{-- ====================== BASICS ====================== --}}
                <section class="bg-primary-900 md:border border-primary-700 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="border-b border-primary-700 px-6 py-4">
                        <h3 class="font-semibold text-white">Basics</h3>
                        <p class="mt-1 text-sm text-gray-400">
                            Only the title is required. Everything else stays hidden until it has content.
                        </p>
                    </div>

                    <div class="p-6 text-gray-100 space-y-5">
                        <div>
                            <x-input-label for="title" value="Title" class="text-gray-200" />
                            <x-text-input id="title" name="title" type="text" maxlength="150"
                                class="mt-1 block w-full"
                                :value="old('title', $data->title ?? null)" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('title')" />
                        </div>

                        <div>
                            <x-input-label for="slug" value="Slug" class="text-gray-200" />
                            <x-text-input id="slug" name="slug" type="text" maxlength="180"
                                placeholder="generated-from-the-title"
                                class="mt-1 block w-full"
                                :value="old('slug', $data->slug ?? null)" />
                            <p class="mt-1 text-xs text-gray-400">
                                Leave blank to generate one. Must be URL-safe (letters, numbers, dashes).
                            </p>
                            <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                        </div>

                        <div>
                            <x-input-label for="short_description" value="Short description" class="text-gray-200" />
                            <x-text-input id="short_description" name="short_description" type="text" maxlength="300"
                                class="mt-1 block w-full"
                                :value="old('short_description', $data->short_description ?? null)" />
                            <p class="mt-1 text-xs text-gray-400">
                                One or two sentences. Shown on cards, in search results and as the social preview.
                            </p>
                            <x-input-error class="mt-2" :messages="$errors->get('short_description')" />
                        </div>

                        <div>
                            <x-input-label for="description" value="Description" class="text-gray-200" />
                            <textarea id="description" name="description" rows="8"
                                class="mt-1 block w-full rounded-md border-primary-700 bg-primary-800 text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="The overview shown on the case study page. Blank paragraphs separate paragraphs.">{{ old('description', $data->description ?? null) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>
                    </div>
                </section>

                {{-- ==================== CLASSIFICATION =================== --}}
                <section class="bg-primary-900 md:border border-primary-700 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="border-b border-primary-700 px-6 py-4">
                        <h3 class="font-semibold text-white">Classification</h3>
                    </div>

                    <div class="p-6 text-gray-100 space-y-5">
                        <div>
                            <x-input-label for="project_type" value="Project type" class="text-gray-200" />
                            <select id="project_type" name="project_type"
                                class="mt-1 block w-full rounded-md border-primary-700 bg-primary-800 text-gray-100">
                                <option value="">— None —</option>
                                @foreach ($projectTypes ?? [] as $type)
                                    <option value="{{ $type }}" @selected(old('project_type', $data->project_type ?? null) === $type)>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('project_type')" />
                        </div>

                        <div>
                            <x-input-label for="tech_stack_input" value="Tech stack" class="text-gray-200" />
                            <input id="tech_stack_input" type="text"
                                class="mt-1 block w-full rounded-md border-primary-700 bg-primary-800 text-gray-100"
                                placeholder="Laravel, FastAPI, FAISS"
                                value="{{ old('tech_stack_csv', isset($data) ? implode(', ', $data->tech_stack) : '') }}"
                                data-tech-stack-input>
                            <p class="mt-1 text-xs text-gray-400">Comma-separated. Rendered as badges.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('tech_stack')" />
                        </div>

                        <div class="grid gap-5 sm:grid-cols-3">
                            <div>
                                <x-input-label for="role" value="Role" class="text-gray-200" />
                                <x-text-input id="role" name="role" type="text" maxlength="120"
                                    class="mt-1 block w-full"
                                    :value="old('role', $data->role ?? null)" />
                                <x-input-error class="mt-2" :messages="$errors->get('role')" />
                            </div>

                            <div>
                                <x-input-label for="year" value="Year" class="text-gray-200" />
                                <x-text-input id="year" name="year" type="text" maxlength="4" inputmode="numeric"
                                    placeholder="2025"
                                    class="mt-1 block w-full"
                                    :value="old('year', $data->year ?? null)" />
                                <x-input-error class="mt-2" :messages="$errors->get('year')" />
                            </div>

                            <div>
                                <x-input-label for="sort_order" value="Sort order" class="text-gray-200" />
                                <x-text-input id="sort_order" name="sort_order" type="number" min="0" max="65535"
                                    class="mt-1 block w-full"
                                    :value="old('sort_order', $data->sort_order ?? 0)" />
                                <p class="mt-1 text-xs text-gray-400">Lower numbers appear first.</p>
                                <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <x-input-label for="status" value="Status" class="text-gray-200" />
                                <select id="status" name="status"
                                    class="mt-1 block w-full rounded-md border-primary-700 bg-primary-800 text-gray-100">
                                    @foreach (['live' => 'Live', 'in_progress' => 'In progress', 'archived' => 'Archived'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status', $data->status ?? 'live') === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-400">Archived projects are hidden from the public site.</p>
                                <x-input-error class="mt-2" :messages="$errors->get('status')" />
                            </div>

                            <div class="flex items-end pb-2">
                                <label class="inline-flex items-center gap-3 text-gray-200">
                                    <input type="hidden" name="featured" value="0">
                                    <input type="checkbox" value="1" name="featured"
                                        @checked(old('featured', $data->featured ?? false))
                                        class="rounded border-primary-600 bg-primary-800 text-indigo-500 focus:ring-indigo-500">
                                    Show in Selected work on the homepage
                                </label>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ======================== LINKS ======================= --}}
                <section class="bg-primary-900 md:border border-primary-700 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="border-b border-primary-700 px-6 py-4">
                        <h3 class="font-semibold text-white">Links</h3>
                        <p class="mt-1 text-sm text-gray-400">
                            Buttons only appear on the public site when the matching URL is filled in.
                            Nothing is invented.
                        </p>
                    </div>

                    <div class="p-6 text-gray-100 space-y-5">
                        <div>
                            <x-input-label for="live_url" value="Live URL" class="text-gray-200" />
                            <x-text-input id="live_url" name="live_url" type="url"
                                placeholder="https://example.com"
                                class="mt-1 block w-full"
                                :value="old('live_url', $data->live_url ?? null)" />
                            <x-input-error class="mt-2" :messages="$errors->get('live_url')" />
                        </div>

                        <div>
                            <x-input-label for="link" value="Legacy link column" class="text-gray-200" />
                            <x-text-input id="link" name="link" type="text"
                                class="mt-1 block w-full"
                                :value="old('link', $data->link ?? null)" />
                            <p class="mt-1 text-xs text-gray-400">
                                Kept for backwards compatibility. The public site falls back to this value when
                                Live URL is empty.
                            </p>
                            <x-input-error class="mt-2" :messages="$errors->get('link')" />
                        </div>

                        <div>
                            <x-input-label for="github_url" value="GitHub URL" class="text-gray-200" />
                            <x-text-input id="github_url" name="github_url" type="url"
                                placeholder="https://github.com/…"
                                class="mt-1 block w-full"
                                :value="old('github_url', $data->github_url ?? null)" />
                            <x-input-error class="mt-2" :messages="$errors->get('github_url')" />
                        </div>
                    </div>
                </section>

                {{-- ==================== CASE STUDY ===================== --}}
                <section class="bg-primary-900 md:border border-primary-700 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="border-b border-primary-700 px-6 py-4">
                        <h3 class="font-semibold text-white">Case study</h3>
                        <p class="mt-1 text-sm text-gray-400">
                            Each block appears only when it has content. Leave a field blank and the section
                            is hidden entirely.
                        </p>
                    </div>

                    <div class="p-6 text-gray-100 space-y-5">
                        <div>
                            <x-input-label for="problem" value="Problem" class="text-gray-200" />
                            <textarea id="problem" name="problem" rows="5"
                                class="mt-1 block w-full rounded-md border-primary-700 bg-primary-800 text-gray-100">{{ old('problem', $data->problem ?? null) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('problem')" />
                        </div>

                        <div>
                            <x-input-label for="solution" value="Solution" class="text-gray-200" />
                            <textarea id="solution" name="solution" rows="5"
                                class="mt-1 block w-full rounded-md border-primary-700 bg-primary-800 text-gray-100">{{ old('solution', $data->solution ?? null) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('solution')" />
                        </div>

                        <div>
                            <x-input-label for="highlights_input" value="Key features" class="text-gray-200" />
                            <textarea id="highlights_input" name="highlights_csv" rows="5"
                                class="mt-1 block w-full rounded-md border-primary-700 bg-primary-800 text-gray-100"
                                placeholder="One feature per line">{{ old('highlights_csv', isset($data) ? implode("\n", $data->highlights) : '') }}</textarea>
                            <p class="mt-1 text-xs text-gray-400">One per line. Never invented — only real features.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('highlights')" />
                        </div>

                        <div>
                            <x-input-label for="challenges" value="Challenges" class="text-gray-200" />
                            <textarea id="challenges" name="challenges" rows="4"
                                class="mt-1 block w-full rounded-md border-primary-700 bg-primary-800 text-gray-100">{{ old('challenges', $data->challenges ?? null) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('challenges')" />
                        </div>

                        <div>
                            <x-input-label for="outcome" value="Outcome" class="text-gray-200" />
                            <textarea id="outcome" name="outcome" rows="4"
                                class="mt-1 block w-full rounded-md border-primary-700 bg-primary-800 text-gray-100">{{ old('outcome', $data->outcome ?? null) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('outcome')" />
                        </div>
                    </div>
                </section>

                {{-- ======================= MEDIA ======================== --}}
                <section class="bg-primary-900 md:border border-primary-700 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="border-b border-primary-700 px-6 py-4">
                        <h3 class="font-semibold text-white">Media</h3>
                        <p class="mt-1 text-sm text-gray-400">
                            A 16:9 thumbnail is used on cards, as the case study hero and as the social
                            preview image. Projects without one fall back to a designed placeholder.
                        </p>
                    </div>

                    <div class="p-6 text-gray-100 space-y-5">
                        <div>
                            <x-input-label for="thumbnail" value="Thumbnail" class="text-gray-200" />

                            @if (isset($data) && $data->thumbnailUrl())
                                <img src="{{ $data->thumbnailUrl() }}" alt="Current thumbnail preview"
                                    class="mt-2 max-w-sm rounded-lg border border-primary-700">
                            @endif

                            <input id="thumbnail" name="thumbnail" type="file"
                                accept="image/jpeg,image/png,image/webp,image/avif"
                                class="mt-2 block w-full text-sm text-gray-400 file:mr-4 file:rounded-md file:border-0 file:bg-primary-700 file:px-4 file:py-2 file:text-sm file:font-medium file:text-gray-100 hover:file:bg-primary-600">

                            <p class="mt-1 text-xs text-gray-400">JPEG, PNG, WebP or AVIF. Maximum 2 MB.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('thumbnail')" />

                            @if (isset($data) && $data->thumbnail)
                                <label class="mt-3 inline-flex items-center gap-2 text-sm text-gray-300">
                                    <input type="checkbox" name="remove_thumbnail" value="1"
                                        class="rounded border-primary-600 bg-primary-800 text-red-500 focus:ring-red-500">
                                    Remove current thumbnail
                                </label>
                            @endif
                        </div>

                        <div>
                            <x-input-label for="screenshots" value="Case study screenshots" class="text-gray-200" />
                            <input id="screenshots" name="screenshots[]" type="file" multiple
                                accept="image/jpeg,image/png,image/webp,image/avif"
                                class="mt-2 block w-full text-sm text-gray-400 file:mr-4 file:rounded-md file:border-0 file:bg-primary-700 file:px-4 file:py-2 file:text-sm file:font-medium file:text-gray-100 hover:file:bg-primary-600">
                            <p class="mt-1 text-xs text-gray-400">
                                Uploading new screenshots replaces the existing set.
                            </p>
                            <x-input-error class="mt-2" :messages="$errors->get('screenshots')" />

                            @if (isset($data) && $data->screenshots !== [])
                                <ul class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
                                    @foreach ($data->screenshots as $screenshot)
                                        <img src="{{ asset('storage/'.$screenshot) }}"
                                            alt="Existing case study screenshot"
                                            class="rounded-lg border border-primary-700">
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </section>

                <div class="flex flex-wrap items-center justify-end gap-3">
                    @isset($data)
                        <x-danger-button type="button" data-toggle="delete-button"
                            href="{{ route('project.destroy', $data->id) }}" class="mr-auto">
                            Delete
                        </x-danger-button>
                    @endisset

                    <x-back-button as="a" href="{{ route('project.index') }}">Cancel</x-back-button>

                    <x-primary-button type="submit" class="bg-primary">
                        {{ isset($data) ? 'Save changes' : 'Create project' }}
                    </x-primary-button>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            /*
             * Converts the friendly comma-separated / one-per-line text inputs into the
             * array inputs the controller expects. No dependency, and it degrades to
             * a normal form submission if JavaScript never runs (validation then
             * rejects the absent arrays gracefully).
             */
            (function () {
                function split(text, separator) {
                    return (text || '')
                        .split(separator)
                        .map(function (value) { return value.trim(); })
                        .filter(function (value) { return value.length > 0; });
                }

                function transfer(sourceSelector, targetName, values) {
                    var source = document.querySelector(sourceSelector);
                    if (!source) return;

                    values.forEach(function (value) {
                        var field = document.createElement('input');
                        field.type = 'hidden';
                        field.name = targetName + '[]';
                        field.value = value;
                        source.form.appendChild(field);
                    });
                }

                var stackInput = document.querySelector('[data-tech-stack-input]');
                var highlightsInput = document.getElementById('highlights_input');

                document.querySelector('form').addEventListener('submit', function () {
                    transfer('[data-tech-stack-input]', 'tech_stack', split(stackInput ? stackInput.value : '', ','));
                    transfer('#highlights_input', 'highlights', split(highlightsInput ? highlightsInput.value : '\n'));
                });
            })();
        </script>
    @endpush
</x-app-layout>