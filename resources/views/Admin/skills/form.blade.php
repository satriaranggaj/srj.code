<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap">
            <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                Skills
            </h2>
        </div>
    </x-slot>

    <form method="post" 
        action="{{ isset($data) ? route('skill.update', $data->id) : route('skill.store') }}" 
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
                                    @if (isset($data) && $data->image)
                                        <img id="previewImage" src="{{ asset('storage/' . $data->image) }}" alt="Logo skill" width="150px">
                                        <input type="hidden" name="existingimage" value="{{ $data->image }}">
                                    @else
                                        <img id="previewImage" src="{{ asset('storage/skills-logo/logo-placeholder.jpg') }}" alt="Logo skill" width="150px">
                                    @endif
                                    <x-input-label style="color: white;" for="image" value="Skill Logo" />
                                    <input id="image" name="image" type="file" class="mt-1 block w-full">
                                    <x-input-error class="mt-2" :messages="$errors->get('image')" />
                                </div>
                            </x-col>
                        </x-row>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end mt-4">
                    @isset($data)
                        <x-danger-button type="button" data-toggle="delete-button" href="{{ route('skill.destroy', $data->id) }}" class="mr-auto">
                            Hapus
                        </x-danger-button>
                    @endisset

                    <x-back-button as="a" href="{{ route('skill.index') }}" class="">
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
        document.getElementById("image").addEventListener("change", function(e) {
            var previewImage = document.getElementById("previewImage");
            var file = e.target.files[0];

            if (file) {
                var objectURL = URL.createObjectURL(file);
                previewImage.src = objectURL;
                previewImage.style.display = 'block';
            } else {
                previewImage.src = '';
                previewImage.style.display = 'none';
            }
        });
    </script>
</x-app-layout>