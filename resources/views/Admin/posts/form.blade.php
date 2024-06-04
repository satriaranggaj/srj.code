<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap">
            <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                Posts
            </h2>
        </div>
    </x-slot>

    <form method="post" 
        action="{{ isset($data) ? route('post.update', $data->id) : route('post.store') }}" 
        class="mt-6 space-y-6" 
        enctype="multipart/form-data">
        @csrf
        @isset($data)
            @method('put')
        @endisset

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-primary-900 md:border border-primary-700 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-100">
                        <x-row>
                            <x-col :col-md="12">
                                <div>
                                    <!-- Menampilkan gambar yang sudah ada atau gambar yang baru dipilih -->
                                    @if (isset($data) && $data->thumbnail)
                                        <img id="previewthumbnail" src="{{ asset('storage/' . $data->thumbnail) }}" alt="Logo post" width="150px">
                                        <input type="hidden" name="existingthumbnail" value="{{ $data->thumbnail }}">
                                    @else
                                        <img id="previewthumbnail" src="{{ asset('storage/posts-logo/logo-placeholder.jpg') }}" alt="Logo post" width="150px">
                                    @endif
                                    <x-input-label style="color: white;" for="thumbnail" value="post Logo" />
                                    <input id="thumbnail" name="thumbnail" type="file" class="mt-1 block w-full">
                                    <x-input-error class="mt-2" :messages="$errors->get('thumbnail')" />
                                </div>
                            </x-col>
                            <x-col :col-md="12">
                                <div>
                                    <x-input-label for="category_id" style="color: white;" value="Category" />
                                    <select id="category_id" name="category_id"
                                        class="mt-1 block w-full bg-primary-800 border-2 border-primary-600 focus:border-primary-300 focus:ring-primary-300 rounded-md shadow-sm text-white">
                                        @foreach ($categorys as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('category_id', $post->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </x-col>
                            <x-col :col-md="12">
                                <div>
                                    <x-input-label style="color: white;" for="link" value="Link Project" />
                                    <x-text-input id="link" name="link" type="text"
                                        class="mt-1 block w-full" :value="old('link', $data->link ?? null)" required autofocus />
                                    <x-input-error class="mt-2" :messages="$errors->get('link')" />
                                </div>
                            </x-col>
                        </x-row>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end mt-4">
                    @isset($data)
                        <x-danger-button type="button" data-toggle="delete-button" href="{{ route('post.destroy', $data->id) }}" class="mr-auto">
                            Hapus
                        </x-danger-button>
                    @endisset

                    <x-back-button as="a" href="{{ route('post.index') }}" class="">
                        Batal
                    </x-back-button>

                    <x-primary-button type="submit" class="ml-2 bg-primary">
                        Simpan
                    </x-primary-button>
                </div>
            </div>
        </div>
    </form>

    <script>
        document.getElementById("thumbnail").addEventListener("change", function(e) {
            var previewthumbnail = document.getElementById("previewthumbnail");
            var file = e.target.files[0];

            if (file) {
                var objectURL = URL.createObjectURL(file);
                previewthumbnail.src = objectURL;
                previewthumbnail.style.display = 'block';
            } else {
                previewthumbnail.src = '';
                previewthumbnail.style.display = 'none';
            }
        });
    </script>
</x-app-layout>