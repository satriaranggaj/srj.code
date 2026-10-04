<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            Messages
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-primary-900 md:border border-primary-700 shadow-sm sm:rounded-lg">
                <div class="p-4 md:p-6 text-gray-300 space-y-4">
                    @forelse ($messages as $message)
                        <article @class([
                            'rounded-lg border p-4',
                            'border-accent-500/40 bg-primary-800' => ! $message->is_read,
                            'border-primary-700' => $message->is_read,
                        ])>
                            <header class="flex flex-wrap items-baseline justify-between gap-2">
                                <h3 class="font-semibold text-white">
                                    {{ $message->subject ?: '(no subject)' }}
                                </h3>

                                <time class="font-mono text-xs text-gray-500"
                                    datetime="{{ $message->created_at->toIso8601String() }}">
                                    {{ $message->created_at->diffForHumans() }}
                                </time>
                            </header>

                            <p class="mt-1 text-sm text-gray-400">
                                {{ $message->name }} ·
                                <a href="mailto:{{ $message->email }}" class="hover:text-blue-400">{{ $message->email }}</a>
                            </p>

                            <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-gray-300">{{ $message->message }}</p>

                            <footer class="mt-4 flex flex-wrap items-center gap-4">
                                <form method="POST" action="{{ route('message.read', $message->id) }}">
                                    @csrf
                                    @method('patch')
                                    <button type="submit" class="text-sm text-white hover:text-blue-400">
                                        {{ $message->is_read ? 'Mark as unread' : 'Mark as read' }}
                                    </button>
                                </form>

                                <x-danger-button type="button" data-toggle="delete-button"
                                    href="{{ route('message.destroy', $message->id) }}"
                                    class="text-xs">
                                    Delete
                                </x-danger-button>
                            </footer>
                        </article>
                    @empty
                        <p class="py-8 text-center text-gray-500">
                            No messages yet. They arrive from the contact form on the public site.
                        </p>
                    @endforelse

                    <div class="pt-2">
                        {{ $messages->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>