<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                Projects
            </h2>

            <x-primary-button as="a" href="{{ route('project.create') }}" class="text-decoration-none">
                New project
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
                                    <th scope="col" class="px-4 py-2 text-white font-bold whitespace-nowrap">Title</th>
                                    <th scope="col" class="px-4 py-2 text-white font-bold whitespace-nowrap">Slug</th>
                                    <th scope="col" class="px-4 py-2 text-white font-bold whitespace-nowrap">Type</th>
                                    <th scope="col" class="px-4 py-2 text-white font-bold whitespace-nowrap">Status</th>
                                    <th scope="col" class="px-4 py-2 text-white font-bold whitespace-nowrap">Featured</th>
                                    <th scope="col" class="px-4 py-2 text-white font-bold whitespace-nowrap">Legacy link</th>
                                    <th scope="col" class="px-4 py-2 text-white font-bold whitespace-nowrap">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($projects as $project)
                                    <tr class="border-b border-gray-600">
                                        <td class="px-4 py-2">{{ $project->title }}</td>
                                        <td class="px-4 py-2 font-mono text-xs text-gray-400">
                                            {{ $project->slug ?: '—' }}
                                        </td>
                                        <td class="px-4 py-2">{{ $project->project_type ?: '—' }}</td>
                                        <td class="px-4 py-2">{{ $project->display_status }}</td>
                                        <td class="px-4 py-2">{{ $project->featured ? 'Yes' : 'No' }}</td>
                                        <td class="px-4 py-2 max-w-xs truncate text-xs text-gray-400">
                                            {{ $project->link ?: '—' }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            @if ($project->hasCaseStudy())
                                                <a href="{{ route('project.show', $project) }}"
                                                    target="_blank" rel="noopener noreferrer"
                                                    class="text-white hover:text-blue-400 text-decoration-none mr-3">
                                                    View
                                                </a>
                                            @endif

                                            <a href="{{ route('project.edit', $project->id) }}"
                                                class="text-white hover:text-blue-400 text-decoration-none">
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-gray-500">
                                            No projects yet.
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