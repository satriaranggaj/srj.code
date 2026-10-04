@php
    $channels = collect(config('portfolio.contact'))
        ->filter(fn ($channel) => $channel['enabled'] && filled($channel['url'] ?? null));

    $icons = [
        'GitHub' => 'github',
        'Instagram' => 'instagram',
        'LinkedIn' => 'linkedin',
        'WhatsApp' => 'whatsapp',
        'Email' => 'mail',
        'Website' => 'globe',
    ];

    $navLinks = [
        ['route' => 'home', 'label' => 'Home'],
        ['route' => 'project', 'label' => 'Projects'],
        ['route' => 'about', 'label' => 'About'],
        ['route' => 'certificate', 'label' => 'Certificates'],
        ['route' => 'contact', 'label' => 'Contact'],
    ];

    $iconClasses = 'inline-flex h-9 w-9 items-center justify-center rounded-lg border border-ink-700 bg-ink-850/60 text-bone-400 transition-all duration-200 hover:border-accent-400/40 hover:text-accent-400';
@endphp

<footer class="border-t border-ink-700/70 bg-ink-900/40">
    <div class="container-page py-12 sm:py-16">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-3">
            <div class="max-w-sm">
                <p class="text-base font-semibold tracking-tightest text-bone-50">
                    {{ config('portfolio.short_name') }}<span class="text-accent-400">.</span>
                </p>

                <p class="mt-1 text-sm text-bone-400">{{ config('portfolio.role') }}</p>
                <p class="text-sm text-bone-400">{{ config('portfolio.role_secondary') }}</p>

                <p class="mt-4 text-sm leading-relaxed text-bone-500">
                    {{ config('portfolio.tagline') }}
                </p>
            </div>

            <nav aria-label="Footer">
                <h2 class="font-mono text-[0.7rem] uppercase tracking-[0.16em] text-bone-500">Navigate</h2>

                <ul class="mt-4 space-y-2.5">
                    @foreach ($navLinks as $link)
                        <li>
                            <a
                                href="{{ route($link['route']) }}"
                                @class([
                                    'text-sm transition-colors duration-200',
                                    request()->routeIs($link['route']) ? 'text-bone-100' : 'text-bone-400 hover:text-bone-100',
                                ])
                            >
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div>
                <h2 class="font-mono text-[0.7rem] uppercase tracking-[0.16em] text-bone-500">Elsewhere</h2>

                <ul class="mt-4 flex flex-wrap gap-2">
                    @foreach ($channels as $channel)
                        <li>
                            <a
                                href="{{ $channel['url'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="{{ $iconClasses }}"
                            >
                                <x-portfolio.icon :name="$icons[$channel['label']] ?? 'globe'" class="h-4 w-4" />
                                <span class="sr-only">{{ $channel['label'] }} — {{ $channel['handle'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                <p class="mt-5 text-sm text-bone-500">
                    {{ config('portfolio.availability') }}
                </p>
            </div>
        </div>

        <div class="mt-10 flex flex-col gap-3 border-t border-ink-700/70 pt-6 sm:flex-row sm:items-center sm:justify-between">
            <p class="font-mono text-xs text-bone-500">
                &copy; {{ date('Y') }} {{ config('portfolio.name') }}. All rights reserved.
            </p>

            <p class="font-mono text-xs text-bone-500">
                Built with Laravel, Tailwind CSS and Alpine.js
            </p>
        </div>
    </div>
</footer>