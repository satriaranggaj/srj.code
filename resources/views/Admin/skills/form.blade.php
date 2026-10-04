<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                {{ isset($data) ? 'Edit technology' : 'New technology' }}
            </h2>
        </div>
    </x-slot>

    <form method="post"
        action="{{ isset($data) ? route('skill.update', $data->id) : route('skill.store') }}"
        enctype="multipart/form-data"
        class="mt-6 space-y-6">
        @csrf
        @isset($data)
            @method('put')
        @endisset

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-primary-900 md:border border-primary-700 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="border-b border-primary-700 px-6 py-4">
                        <h3 class="font-semibold text-white">Technology</h3>
                        <p class="mt-1 text-sm text-gray-400">
                            The name is what appears as a badge on the public site. The logo is optional.
                        </p>
                    </div>

                    <div class="p-6 text-gray-100 space-y-5">
                        <div>
                            <x-input-label for="name" value="Name" class="text-gray-200" />
                            <x-text-input id="name" name="name" type="text" maxlength="60"
                                placeholder="Laravel"
                                class="mt-1 block w-full"
                                :value="old('name', $data->name ?? null)" autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="category" value="Category" class="text-gray-200" />
                            <select id="category" name="category"
                                class="mt-1 block w-full rounded-md border-primary-700 bg-primary-800 text-gray-100">
                                <option value="">— Uncategorised —</option>
                                @foreach ($categories ?? [] as $category)
                                    <option value="{{ $category }}" @selected(old('category', $data->category ?? null) === $category)>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-400">
                                Groups the badge under the matching heading on the public site.
                            </p>
                            <x-input-error class="mt-2" :messages="$errors->get('category')" />
                        </div>

                        <div>
                            <x-input-label for="url" value="Reference URL" class="text-gray-200" />
                            <x-text-input id="url" name="url" type="url" placeholder="https://laravel.com"
                                class="mt-1 block w-full"
                                :value="old('url', $data->url ?? null)" />
                            <p class="mt-1 text-xs text-gray-400">Optional. Makes the badge a link.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('url')" />
                        </div>

                        <div>
                            <x-input-label for="sort_order" value="Sort order" class="text-gray-200" />
                            <x-text-input id="sort_order" name="sort_order" type="number" min="0" max="65535"
                                class="mt-1 block w-full"
                                :value="old('sort_order', $data->sort_order ?? 0)" />
                            <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
                        </div>

                        <div>
                            <x-input-label for="image" value="Logo" class="text-gray-200" />

                            @if (isset($data) && $data->logoUrl())
                                <img src="{{ $data->logoUrl() }}" alt="Current logo"
                                    class="mt-2 h-20 w-20 rounded-lg border border-primary-700 bg-white/5 object-contain p-2">
                            @endif

                            <input id="image" name="image" type="file"
                                accept="image/jpeg,image/png,image/webp,image/svg+xml"
                                class="mt-2 block w-full text-sm text-gray-400 file:mr-4 file:rounded-md file:border-0 file:bg-primary-700 file:px-4 file:py-2 file:text-sm file:font-medium file:text-gray-100 hover:file:bg-primary-600">

                            <p class="mt-1 text-xs text-gray-400">
                                Optional. Square PNG or SVG works best. Maximum 512 KB.
                            </p>
                            <x-input-error class="mt-2" :messages="$errors->get('image')" />

                            @if (isset($data) && $data->image)
                                <label class="mt-3 inline-flex items-center gap-2 text-sm text-gray-300">
                                    <input type="checkbox" name="remove_image" value="1"
                                        class="rounded border-primary-600 bg-primary-800 text-red-500 focus:ring-red-500">
                                    Remove current logo
                                </label>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-3 mt-6">
                    @isset($data)
                        <x-danger-button type="button" data-toggle="delete-button"
                            href="{{ route('skill.destroy', $data->id) }}" class="mr-auto">
                            Delete
                        </x-danger-button>
                    @endisset

                    <x-back-button as="a" href="{{ route('skill.index') }}">Cancel</x-back-button>

                    <x-primary-button type="submit" class="bg-primary">
                        {{ isset($data) ? 'Save changes' : 'Create technology' }}
                    </x-primary-button>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>