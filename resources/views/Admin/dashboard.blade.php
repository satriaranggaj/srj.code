<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                {{ __('Dashboard') }}
            </h2>

            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer"
                    class="text-gray-300 hover:text-white">View site</a>
                <a href="{{ route('profile.edit') }}" class="text-gray-300 hover:text-white">Profile</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-primary-900 md:border border-primary-700 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h1 class="text-2xl text-white">
                        {{ greeting() }}, {{ auth()->user()->name }}
                    </h1>

                    <p class="mt-1 text-sm text-gray-400">
                        Manage everything the public portfolio renders from here.
                    </p>
                </div>
            </div>

            {{-- Content counts --}}
            <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['label' => 'Projects', 'value' => $stats['projects'], 'route' => 'project.index'],
                    ['label' => 'Featured', 'value' => $stats['featured'], 'route' => 'project.index'],
                    ['label' => 'Technologies', 'value' => $stats['skills'], 'route' => 'skill.index'],
                    ['label' => 'Certificates', 'value' => $stats['certificates'], 'route' => 'certificate.index'],
                ] as $tile)
                    <div class="rounded-lg border border-primary-700 bg-primary-900 p-5">
                        <dt class="text-sm text-gray-400">{{ $tile['label'] }}</dt>
                        <dd class="mt-1 text-3xl font-semibold text-white">{{ $tile['value'] }}</dd>
                        <a href="{{ route($tile['route']) }}"
                            class="mt-3 inline-block text-sm text-gray-300 hover:text-white">Manage</a>
                    </div>
                @endforeach
            </dl>

            <div class="grid gap-6 lg:grid-cols-2">
                {{-- Latest projects --}}
                <div class="rounded-lg border border-primary-700 bg-primary-900 p-5">
                    <h2 class="font-semibold text-white">Latest projects</h2>

                    <ul class="mt-4 space-y-3">
                        @forelse ($latestProjects as $project)
                            <li class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="truncate text-sm text-gray-200">{{ $project->title }}</p>
                                    <p class="font-mono text-xs text-gray-500">
                                        /projects/{{ $project->slug ?: 'no-slug' }}
                                    </p>
                                </div>

                                <a href="{{ route('project.edit', $project->id) }}"
                                    class="shrink-0 text-sm text-gray-300 hover:text-white">Edit</a>
                            </li>
                        @empty
                            <li class="text-sm text-gray-500">No projects yet.</li>
                        @endforelse
                    </ul>
                </div>

                {{-- Recent contact messages --}}
                <div class="rounded-lg border border-primary-700 bg-primary-900 p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="font-semibold text-white">Recent messages</h2>

                        <a href="{{ route('message.index') }}" class="text-sm text-gray-300 hover:text-white">
                            Inbox ({{ $stats['unreadMessages'] }} unread)
                        </a>
                    </div>

                    <ul class="mt-4 space-y-3">
                        @forelse ($recentMessages as $message)
                            <li>
                                <p class="text-sm text-gray-200">
                                    {{ $message->subject ?: '(no subject)' }}
                                    @unless ($message->is_read)
                                        <span class="ml-1 inline-block h-1.5 w-1.5 rounded-full bg-blue-400"
                                            aria-label="Unread"></span>
                                    @endunless
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $message->name }} · {{ $message->created_at->diffForHumans() }}
                                </p>
                            </li>
                        @empty
                            <li class="text-sm text-gray-500">No messages yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>