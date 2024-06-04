    <div class="overflow-x-auto border-[0.5px] border-primary">
        <div class="table  min-w-full">
            @if (count($list))
                {{ $slot }}
            @else
                <div class="p-6 text-gray-100 bg-black">
                    <p class="text-center">
                        Tidak ada data.
                    </p>
                </div>
            @endif
        </div>
    </div>
