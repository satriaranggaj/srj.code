<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap">
            <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                Certificates
            </h2>
        </div>
    </x-slot>

    <form method="post" 
        action="{{ isset($data) ? route('certificate.update', $data->id) : route('certificate.store') }}" 
        class="mt-6 space-y-6" 
        enctype="multipart/form-data">
        @csrf
        @isset($data)
            @method('put')
        @endisset

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-primary-900 md:border border-primary-700 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-black">
                        <x-row>
                            <x-col :col-md="12">
                                <div>
                                    <x-input-label style="color: white;" for="link" value="Link Certificate" />
                                    <x-text-input id="link" name="link" type="text"
                                        class="mt-1 block w-full" :value="old('link', $data->link ?? null)" required autofocus />
                                    <x-input-error class="mt-2" :messages="$errors->get('link')" />
                                </div>
                            </x-col>
                            <x-col :col-md="12">
                                <div>
                                    <x-input-label style="color: white;" for="title" value="Judul Certificate" />
                                    <x-text-input id="title" name="title" type="text"
                                        class="mt-1 block w-full" :value="old('title', $data->title ?? null)" required autofocus />
                                    <x-input-error class="mt-2" :messages="$errors->get('title')" />
                                </div>
                            </x-col>
                        </x-row>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end mt-4">
                    @isset($data)
                        <x-danger-button type="button" data-toggle="delete-button" href="{{ route('certificate.destroy', $data->id) }}" class="mr-auto">
                            Hapus
                        </x-danger-button>
                    @endisset

                    <x-back-button as="a" href="{{ route('certificate.index') }}" class="">
                        Batal
                    </x-back-button>

                    <x-primary-button type="submit" class="ml-2 bg-primary">
                        Simpan
                    </x-primary-button>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>