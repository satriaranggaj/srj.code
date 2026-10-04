<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                {{ isset($data) ? 'Edit certificate' : 'New certificate' }}
            </h2>
        </div>
    </x-slot>

    <form method="post"
        action="{{ isset($data) ? route('certificate.update', $data->id) : route('certificate.store') }}"
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
                        <h3 class="font-semibold text-white">Certificate</h3>
                        <p class="mt-1 text-sm text-gray-400">
                            Only certificates with real, verifiable details belong here.
                        </p>
                    </div>

                    <div class="p-6 text-gray-100 space-y-5">
                        <div>
                            <x-input-label for="title" value="Title" class="text-gray-200" />
                            <x-text-input id="title" name="title" type="text" maxlength="180"
                                class="mt-1 block w-full"
                                :value="old('title', $data->title ?? null)" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('title')" />
                        </div>

                        <div>
                            <x-input-label for="issuer" value="Issuer" class="text-gray-200" />
                            <x-text-input id="issuer" name="issuer" type="text" maxlength="120"
                                class="mt-1 block w-full"
                                :value="old('issuer', $data->issuer ?? null)" />
                            <x-input-error class="mt-2" :messages="$errors->get('issuer')" />
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <x-input-label for="issued_at" value="Issued on" class="text-gray-200" />
                                <x-text-input id="issued_at" name="issued_at" type="date"
                                    class="mt-1 block w-full"
                                    :value="old('issued_at', isset($data) && $data->issued_at ? $data->issued_at->toDateString() : null)" />
                                <x-input-error class="mt-2" :messages="$errors->get('issued_at')" />
                            </div>

                            <div>
                                <x-input-label for="sort_order" value="Sort order" class="text-gray-200" />
                                <x-text-input id="sort_order" name="sort_order" type="number" min="0" max="65535"
                                    class="mt-1 block w-full"
                                    :value="old('sort_order', $data->sort_order ?? 0)" />
                                <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="description" value="Description" class="text-gray-200" />
                            <x-text-input id="description" name="description" type="text" maxlength="300"
                                class="mt-1 block w-full"
                                :value="old('description', $data->description ?? null)" />
                            <p class="mt-1 text-xs text-gray-400">Optional. One short line.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div>
                            <x-input-label for="link" value="Credential URL" class="text-gray-200" />
                            <x-text-input id="link" name="link" type="url"
                                placeholder="https://…"
                                class="mt-1 block w-full"
                                :value="old('link', $data->link ?? null)" />
                            <p class="mt-1 text-xs text-gray-400">
                                Only add a link you have verified. The public site hides the button when empty.
                            </p>
                            <x-input-error class="mt-2" :messages="$errors->get('link')" />
                        </div>

                        <div>
                            <x-input-label for="image" value="Image" class="text-gray-200" />

                            @if (isset($data) && $data->imageUrl())
                                <img src="{{ $data->imageUrl() }}" alt="Current certificate image"
                                    class="mt-2 max-w-sm rounded-lg border border-primary-700">
                            @endif

                            <input id="image" name="image" type="file"
                                accept="image/jpeg,image/png,image/webp"
                                class="mt-2 block w-full text-sm text-gray-400 file:mr-4 file:rounded-md file:border-0 file:bg-primary-700 file:px-4 file:py-2 file:text-sm file:font-medium file:text-gray-100 hover:file:bg-primary-600">

                            <p class="mt-1 text-xs text-gray-400">Optional. Maximum 2 MB.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('image')" />

                            @if (isset($data) && $data->image)
                                <label class="mt-3 inline-flex items-center gap-2 text-sm text-gray-300">
                                    <input type="checkbox" name="remove_image" value="1"
                                        class="rounded border-primary-600 bg-primary-800 text-red-500 focus:ring-red-500">
                                    Remove current image
                                </label>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-3 mt-6">
                    @isset($data)
                        <x-danger-button type="button" data-toggle="delete-button"
                            href="{{ route('certificate.destroy', $data->id) }}" class="mr-auto">
                            Delete
                        </x-danger-button>
                    @endisset

                    <x-back-button as="a" href="{{ route('certificate.index') }}">Cancel</x-back-button>

                    <x-primary-button type="submit" class="bg-primary">
                        {{ isset($data) ? 'Save changes' : 'Create certificate' }}
                    </x-primary-button>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>