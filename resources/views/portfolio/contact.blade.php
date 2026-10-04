@php
    $channels = collect(config('portfolio.contact'))
        ->filter(fn ($channel) => $channel['enabled'] && filled($channel['url'] ?? null));

    $icons = [
        'WhatsApp' => 'whatsapp',
        'GitHub' => 'github',
        'Instagram' => 'instagram',
        'LinkedIn' => 'linkedin',
        'Email' => 'mail',
        'Website' => 'globe',
    ];

    $order = ['WhatsApp', 'GitHub', 'Instagram', 'Email', 'Website', 'LinkedIn'];

    $channels = $channels->sortBy(fn ($channel, $label) => array_search($label, $order, true) ?: 99)->values();

    $status = session('contact_status');
@endphp

<x-layouts.portfolio
    seo-title="Contact"
    seo-description="Get in touch with {{ config('portfolio.name') }} about full stack roles, project work or AI-powered web applications."
>
    <x-portfolio.page-header
        eyebrow="Contact"
        title="Let's talk"
        description="The fastest route is WhatsApp. If you prefer writing something down properly, use the form."
    />

    <section class="py-16 sm:py-20">
        <div class="container-page">
            <div class="grid gap-10 lg:grid-cols-5">
                {{-- ========================= CHANNELS ======================== --}}
                <div class="lg:col-span-2">
                    <h2 class="font-mono text-xs uppercase tracking-[0.18em] text-accent-400">
                        Direct channels
                    </h2>

                    <div class="mt-6 space-y-3">
                        @forelse ($channels as $channel)
                            <x-portfolio.contact-method
                                :label="$channel['label']"
                                :handle="$channel['handle']"
                                :url="$channel['url']"
                                :icon="$icons[$channel['label']] ?? 'globe'"
                                data-reveal
                            />
                        @empty
                            <x-portfolio.empty-state
                                icon="globe"
                                title="No contact channels enabled"
                                description="Add them in config/portfolio.php."
                            />
                        @endforelse
                    </div>

                    <p class="mt-6 text-sm leading-relaxed text-bone-500">
                        External links open in a new tab. I do not share contact details with anyone, and I
                        will never ask for your password or payment details.
                    </p>
                </div>

                {{-- =========================== FORM ========================== --}}
                <div class="lg:col-span-3">
                    <h2 class="font-mono text-xs uppercase tracking-[0.18em] text-accent-400">
                        Send a message
                    </h2>

                    {{-- Server-side status. Stored in the database, no third-party service. --}}
                    @if ($status === 'success')
                        <div
                            role="status"
                            class="mt-6 flex items-start gap-3 rounded-xl border border-accent-400/40 bg-accent-400/10 p-5"
                        >
                            <x-portfolio.icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-accent-400" />
                            <p class="text-sm leading-relaxed text-accent-300">
                                Thanks — your message was received. I read every one and usually reply within
                                a day or two.
                            </p>
                        </div>
                    @elseif ($status === 'error')
                        <div
                            role="alert"
                            class="mt-6 flex items-start gap-3 rounded-xl border border-red-400/40 bg-red-400/10 p-5"
                        >
                            <x-portfolio.icon name="alert" class="mt-0.5 h-4 w-4 shrink-0 text-red-300" />
                            <p class="text-sm leading-relaxed text-red-300">
                                The message could not be stored just now. Please try again, or reach me
                                directly on WhatsApp.
                            </p>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div
                            role="alert"
                            class="mt-6 rounded-xl border border-red-400/40 bg-red-400/10 p-5"
                        >
                            <p class="text-sm font-medium text-red-300">Please fix the following:</p>

                            <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-200/90">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form
                        method="POST"
                        action="{{ route('contact.store') }}"
                        class="mt-6 space-y-5"
                        x-data="{ submitting: false }"
                        x-on:submit="submitting = true"
                    >
                        @csrf

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="contact-name" class="block text-sm font-medium text-bone-200">
                                    Name <span class="text-accent-400" aria-hidden="true">*</span>
                                </label>
                                <input
                                    id="contact-name"
                                    name="name"
                                    type="text"
                                    required
                                    autocomplete="name"
                                    maxlength="120"
                                    value="{{ old('name') }}"
                                    @class([
                                        'mt-2 block w-full rounded-lg border-ink-600 bg-ink-850/70 px-3.5 py-2.5 text-sm text-bone-100 placeholder:text-bone-500 focus:border-accent-400 focus:ring-accent-400',
                                        'border-red-400/60' => $errors->has('name'),
                                    ])
                                />
                                @error('name')
                                    <p class="mt-1.5 text-xs text-red-300">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="contact-email" class="block text-sm font-medium text-bone-200">
                                    Email <span class="text-accent-400" aria-hidden="true">*</span>
                                </label>
                                <input
                                    id="contact-email"
                                    name="email"
                                    type="email"
                                    required
                                    autocomplete="email"
                                    maxlength="180"
                                    value="{{ old('email') }}"
                                    @class([
                                        'mt-2 block w-full rounded-lg border-ink-600 bg-ink-850/70 px-3.5 py-2.5 text-sm text-bone-100 placeholder:text-bone-500 focus:border-accent-400 focus:ring-accent-400',
                                        'border-red-400/60' => $errors->has('email'),
                                    ])
                                />
                                @error('email')
                                    <p class="mt-1.5 text-xs text-red-300">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="contact-subject" class="block text-sm font-medium text-bone-200">
                                Subject
                            </label>
                            <input
                                id="contact-subject"
                                name="subject"
                                type="text"
                                maxlength="150"
                                value="{{ old('subject') }}"
                                class="mt-2 block w-full rounded-lg border-ink-600 bg-ink-850/70 px-3.5 py-2.5 text-sm text-bone-100 placeholder:text-bone-500 focus:border-accent-400 focus:ring-accent-400"
                            />
                            @error('subject')
                                <p class="mt-1.5 text-xs text-red-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact-message" class="block text-sm font-medium text-bone-200">
                                Message <span class="text-accent-400" aria-hidden="true">*</span>
                            </label>
                            <textarea
                                id="contact-message"
                                name="message"
                                rows="6"
                                required
                                minlength="10"
                                maxlength="{{ config('portfolio.contact_form.max_message_length', 4000) }}"
                                placeholder="What are you building, and what do you need help with?"
                                @class([
                                    'mt-2 block w-full rounded-lg border-ink-600 bg-ink-850/70 px-3.5 py-2.5 text-sm text-bone-100 placeholder:text-bone-500 focus:border-accent-400 focus:ring-accent-400',
                                    'border-red-400/60' => $errors->has('message'),
                                ])
                            ></textarea>
                            @error('message')
                                <p class="mt-1.5 text-xs text-red-300">{{ $message }}</p>
                            @enderror
                        </div>

                        {{--
                            Honeypot. Hidden from humans and from screen readers, so it never
                            becomes an accessibility problem. Bots that fill every input get
                            rejected by the 'prohibited' validation rule.
                        --}}
                        <div class="absolute left-[-9999px] top-auto h-px w-px overflow-hidden" aria-hidden="true">
                            <label for="contact-website">Website</label>
                            <input id="contact-website" name="website" type="text" tabindex="-1" autocomplete="off" />
                        </div>

                        <div class="flex flex-wrap items-center gap-4">
                            <x-portfolio.button
                                type="submit"
                                size="lg"
                                icon="arrow-right"
                                x-bind:disabled="submitting"
                                x-bind:class="submitting ? 'opacity-70' : ''"
                            >
                                <span x-show="! submitting">Send message</span>
                                <span x-show="submitting" x-cloak>Sending…</span>
                            </x-portfolio.button>

                            <p class="text-xs leading-relaxed text-bone-500">
                                Stored securely in my database. Never shared, never used for marketing.
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</x-layouts.portfolio>