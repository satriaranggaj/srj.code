<x-app-layout
    title="{{ isset($data) ? 'Edit technology' : 'New technology' }}"
    subtitle="{{ isset($data) ? $data->display_name : 'Add a technology to the portfolio tech stack.' }}"
>
    <x-slot name="actions">
        <x-admin.button :href="route('skill.index')" variant="ghost" size="sm" icon="arrow-left">
            Back
        </x-admin.button>
    </x-slot>

    <form
        method="post"
        action="{{ isset($data) ? route('skill.update', $data->id) : route('skill.store') }}"
        enctype="multipart/form-data"
        class="mx-auto max-w-2xl space-y-5"
    >
        @csrf
        @isset($data)
            @method('put')
        @endisset

        <x-admin.panel title="Technology" description="The name is what appears as a badge on the public site. The logo is optional.">
            <div class="space-y-5">
                <x-admin.field for="name" label="Name" required>
                    <x-admin.input
                        id="name"
                        name="name"
                        type="text"
                        maxlength="60"
                        :value="old('name', $data->name ?? null)"
                        :invalid="$errors->has('name')"
                        placeholder="Laravel"
                        required
                        autofocus
                    />

                    @error('name')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                </x-admin.field>

                <x-admin.field for="category" label="Category" help="Groups the badge under the matching heading on the public site.">
                    <x-admin.select
                        id="category"
                        name="category"
                        :options="array_combine($categories ?? [], $categories ?? [])"
                        :selected="old('category', $data->category ?? null)"
                        :placeholder="'— Uncategorised —'"
                        :invalid="$errors->has('category')"
                    />

                    @error('category')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
                </x-admin.field>

                <x-admin.field for="url" label="Reference URL" help="Optional. Makes the badge a link.">
                    <x-admin.input
                        id="url"
                        name="url"
                        type="url"
                        :value="old('url', $data->url ?? null)"
                        :invalid="$errors->has('url')"
                        placeholder="https://laravel.com"
                    />

                    @error('url')<p class="text-xs text-red-300">{{ $message }}</p>@enderror
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

                <x-admin.field for="image" label="Logo" help="Optional. Square PNG or SVG works best. Maximum 512 KB.">
                    @if (isset($data) && $data->logoUrl())
                        <img
                            src="{{ $data->logoUrl() }}"
                            alt="Current logo"
                            class="mb-3 h-20 w-20 rounded-lg border border-ink-700 bg-white/5 object-contain p-2"
                        >
                    @endif

                    <input
                        id="image"
                        name="image"
                        type="file"
                        accept="image/jpeg,image/png,image/webp,image/svg+xml"
                        class="block w-full text-sm text-bone-400 file:mr-3 file:rounded-lg file:border-0 file:bg-ink-800 file:px-3.5 file:py-2 file:text-sm file:font-medium file:text-bone-100 file:hover:bg-ink-700"
                    >

                    @error('image')<p class="text-xs text-red-300">{{ $message }}</p>@enderror

                    @if (isset($data) && $data->image)
                        <x-admin.checkbox
                            class="mt-3"
                            name="remove_image"
                            value="1"
                            label="Remove current logo"
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
                    :href="route('skill.destroy', $data->id)"
                    class="sm:mr-auto"
                >
                    Delete
                </x-admin.button>
            @endisset

            <x-admin.button :href="route('skill.index')" variant="ghost">Cancel</x-admin.button>

            <x-admin.button type="submit">
                {{ isset($data) ? 'Save changes' : 'Create technology' }}
            </x-admin.button>
        </div>
    </form>
</x-app-layout>