<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <h2 class="font-semibold text-xl text-white leading-tight">
                Technologies
            </h2>

            <x-primary-button as="a" href="{{ route('skill.create') }}" class="text-decoration-none">
                New technology
            </x-primary-button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-primary-900 md:border border-primary-700 shadow-sm sm:rounded-lg">
                <div class="p-4 md:p-6 text-gray-300">
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full border border-collapse border-primary-700">
                            <thead class="bg-primary-800">
                                <tr class="text-left">
                                    <th scope="col" class="px-4 py-2 text-white font-bold whitespace-nowrap">Name</th>
                                    <th scope="col" class="px-4 py-2 text-white font-bold whitespace-nowrap">Category</th>
                                    <th scope="col" class="px-4 py-2 text-white font-bold whitespace-nowrap">Order</th>
                                    <th scope="col" class="px-4 py-2 text-white font-bold whitespace-nowrap">Logo</th>
                                    <th scope="col" class="px-4 py-2 text-white font-bold whitespace-nowrap">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($skills as $skill)
                                    <tr class="border-b border-gray-600">
                                        <td class="px-4 py-2">{{ $skill->display_name }}</td>
                                        <td class="px-4 py-2">{{ $skill->category ?: '—' }}</td>
                                        <td class="px-4 py-2">{{ $skill->sort_order }}</td>
                                        <td class="px-4 py-2">
                                            @if ($skill->logoUrl())
                                                <img src="{{ $skill->logoUrl() }}" alt="{{ $skill->display_name }} logo"
                                                    class="h-9 w-9 rounded object-contain">
                                            @else
                                                <span class="text-xs text-gray-500">None</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">
                                            <a href="{{ route('skill.edit', $skill->id) }}"
                                                class="text-white hover:text-blue-400 text-decoration-none">
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-gray-500">
                                            No technologies yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>