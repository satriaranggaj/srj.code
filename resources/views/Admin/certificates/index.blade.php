<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <h2 class="font-semibold text-xl text-white leading-tight">
                Certificates
            </h2>

            <x-primary-button as="a" href="{{ route('certificate.create') }}" class="text-decoration-none">
                New certificate
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
                                    <th scope="col" class="px-4 py-2 text-white font-bold whitespace-nowrap">Issuer</th>
                                    <th scope="col" class="px-4 py-2 text-white font-bold whitespace-nowrap">Issued</th>
                                    <th scope="col" class="px-4 py-2 text-white font-bold whitespace-nowrap">Credential URL</th>
                                    <th scope="col" class="px-4 py-2 text-white font-bold whitespace-nowrap">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($certificates as $certificate)
                                    <tr class="border-b border-gray-600">
                                        <td class="px-4 py-2">{{ $certificate->title }}</td>
                                        <td class="px-4 py-2">{{ $certificate->issuer ?: '—' }}</td>
                                        <td class="px-4 py-2">{{ $certificate->issued_at?->format('M Y') ?: '—' }}</td>
                                        <td class="px-4 py-2 max-w-xs truncate text-xs text-gray-400">
                                            {{ $certificate->credentialUrl ?: '—' }}
                                        </td>
                                        <td class="px-4 py-2">
                                            <a href="{{ route('certificate.edit', $certificate->id) }}"
                                                class="text-white hover:text-blue-400 text-decoration-none">
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-gray-500">
                                            No certificates yet.
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