<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-primary md:border border-primary-700 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 md:p-6 text-white">
                    <h1 class="text-2xl">
                        👋 {{ greeting() }}, {{ auth()->user()->name }}
                    </h1>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
