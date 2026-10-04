<x-app-layout
    title="Messages"
    subtitle="{{ $messages->total() }} {{ Str::plural('message', $messages->total()) }} from the public contact form."
>
    @if ($messages->isEmpty())
        <x-admin.empty-state
            icon="mail"
            title="No messages yet"
            description="Enquiries submitted through the contact form on your portfolio arrive here."
        />
    @else
        {{-- Unread messages are visually distinct, but the state comes from the
             real `is_read` column rather than being inferred or invented. --}}
        <div class="space-y-4">
            @foreach ($messages as $message)
                <article @class([
                    'overflow-hidden rounded-xl border transition-colors duration-150',
                    'border-accent-400/30 bg-ink-850/70' => ! $message->is_read,
                    'border-ink-700/80 bg-ink-850/40' => $message->is_read,
                ])>
                    <div class="p-4 sm:p-5">
                        <header class="flex flex-wrap items-start justify-between gap-x-4 gap-y-2">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-sm font-semibold text-bone-50">
                                        {{ $message->subject ?: '(no subject)' }}
                                    </h2>

                                    @unless ($message->is_read)
                                        <x-admin.badge tone="accent">Unread</x-admin.badge>
                                    @endunless
                                </div>

                                <p class="mt-1 flex flex-wrap items-center gap-x-2 text-xs text-bone-500">
                                    <span class="text-bone-300">{{ $message->name }}</span>
                                    <span aria-hidden="true">·</span>
                                    <a
                                        href="mailto:{{ $message->email }}"
                                        class="rounded font-mono transition-colors hover:text-accent-400"
                                    >{{ $message->email }}</a>
                                </p>
                            </div>

                            <time
                                class="shrink-0 font-mono text-xs text-bone-500"
                                datetime="{{ $message->created_at->toIso8601String() }}"
                                title="{{ $message->created_at->format('j M Y, H:i') }}"
                            >{{ $message->created_at->diffForHumans() }}</time>
                        </header>

                        <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-bone-300">
                            {{ $message->message }}
                        </p>

                        <footer class="mt-4 flex flex-wrap items-center gap-3 border-t border-ink-700/60 pt-3">
                            <a
                                href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.($message->subject ?: 'Your message')) }}"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-ink-600 bg-ink-850 px-3 py-1.5 text-sm text-bone-200 transition-colors hover:border-ink-500 hover:bg-ink-800"
                            >
                                <x-portfolio.icon name="mail" class="h-3.5 w-3.5" />
                                Reply
                            </a>

                            {{-- Existing read/unread toggle, unchanged endpoint. --}}
                            <form method="POST" action="{{ route('message.read', $message->id) }}">
                                @csrf
                                @method('patch')
                                <button
                                    type="submit"
                                    class="rounded-lg px-3 py-1.5 text-sm text-bone-400 transition-colors hover:bg-ink-800 hover:text-bone-100"
                                >
                                    {{ $message->is_read ? 'Mark as unread' : 'Mark as read' }}
                                </button>
                            </form>

                            {{-- Preserves the existing delete confirmation chain. --}}
                            <x-admin.button
                                variant="danger-ghost"
                                size="sm"
                                icon="close"
                                data-toggle="delete-button"
                                :href="route('message.destroy', $message->id)"
                                class="ml-auto"
                            >
                                Delete
                            </x-admin.button>
                        </footer>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Laravel's default Tailwind paginator; restyled below to match Admin V2. --}}
        <div class="mt-6">
            {{ $messages->links() }}
        </div>
    @endif
</x-app-layout>