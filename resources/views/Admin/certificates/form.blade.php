<x-app-layout
    title="{{ isset($data) ? 'Edit certificate' : 'New certificate' }}"
    subtitle="{{ isset($data) ? $data->title : 'Add a certificate and its credential link.' }}"
>
    <x-slot name="actions">
        <x-admin.button :href="route('certificate.index')" variant="ghost" size="sm" icon="arrow-left">
            Back
        </x-admin.button>
    </x-slot>

    <form
        method="post"
        action="{{ isset($data) ? route('certificate.update', $data->id) : route('certificate.store') }}"
        enctype="multipart/form-data"
        class="mx-auto max-w-2xl space-y-5"
    >
        @csrf
        @isset($data)
            @method('put')
        @endisset

        <x-admin.panel title="Certificate" description="Only certificates with real, verifiable details belong here.">
            <div class="space-y-5">
                <x-admin.field for="title" label="Title" required>
                    <x-admin.input
                        id="title"
                        name="title"
                        type="text"
                        maxlength="180"
                        :value="old('title', $data->title ?? null)"
                        :invalid="$errors->has('title')"
                        placeholder="Backend Web Development"
                        required
                        autofocus
                    />

                    @error('title')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                </x-admin.field>

                <x-admin.field for="issuer" label="Issuer">
                    <x-admin.input
                        id="issuer"
                        name="issuer"
                        type="text"
                        maxlength="120"
                        :value="old('issuer', $data->issuer ?? null)"
                        :invalid="$errors->has('issuer')"
                        placeholder="Issuing organisation"
                    />

                    @error('issuer')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                </x-admin.field>

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-admin.field for="issued_at" label="Issued on">
                        <x-admin.input
                            id="issued_at"
                            name="issued_at"
                            type="date"
                            :value="old('issued_at', isset($data) && $data->issued_at ? $data->issued_at->toDateString() : null)"
                            :invalid="$errors->has('issued_at')"
                            class="font-mono"
                        />

                        @error('issued_at')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
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

                <x-admin.field for="description" label="Description" help="Optional. One short line.">
                    <x-admin.textarea
                        id="description"
                        name="description"
                        rows="2"
                        maxlength="300"
                        :invalid="$errors->has('description')"
                    >{{ old('description', $data->description ?? null) }}</x-admin.textarea>

                    @error('description')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                </x-admin.field>

                <x-admin.field for="link" label="Credential URL" help="Only add a link you have verified. The public button hides when this is empty.">
                    <x-admin.input
                        id="link"
                        name="link"
                        type="url"
                        :value="old('link', $data->link ?? null)"
                        :invalid="$errors->has('link')"
                        placeholder="https://…"
                        class="font-mono"
                    />

                    @error('link')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                </x-admin.field>

                <x-admin.field for="image" label="Image" help="Optional. Maximum 2 MB.">
                    @if (isset($data) && $data->imageUrl())
                        <img
                            src="{{ $data->imageUrl() }}"
                            alt="Current certificate image"
                            class="mb-3 w-full max-w-xs rounded-lg border border-ink-700 object-cover"
                        >
                    @endif

                    <input
                        id="image"
                        name="image"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="block w-full text-sm text-bone-400 file:mr-3 file:rounded-lg file:border-0 file:bg-ink-800 file:px-3.5 file:py-2 file:text-sm file:font-medium file:text-bone-100 file:hover:bg-ink-700"
                    >

                    @error('image')<p class="text-xs text-red-300">{{ $message }}</p>@enderror

                    @if (isset($data) && $data->image)
                        <x-admin.checkbox
                            class="mt-3"
                            name="remove_image"
                            value="1"
                            label="Remove current image"
                        />
                    @endif
                </x-admin.field>
            </div>
        </x-admin.panel>

        <div class="flex flex-wrap items-center gap-3 border-t border-ink-700/70 pt-5">
            @isset($data)
                <x-admin.button
                    variant="danger-ghost"
                    icon="close"
                    data-toggle="delete-button"
                    :href="route('certificate.destroy', $data->id)"
                    class="sm:mr-auto"
                >
                    Delete
                </x-admin.button>
            @endisset

            <x-admin.button :href="route('certificate.index')" variant="ghost">Cancel</x-admin.button>

            <x-admin.button type="submit">
                {{ isset($data) ? 'Save changes' : 'Create certificate' }}
            </x-admin.button>
        </div>
    </form>
</x-app-layout>