@php
    /**
     * Navigation definition. Adding an item here is the only change needed to
     * extend the navigation — desktop and mobile stay in sync automatically.
     */
    $links = [
        ['route' => 'home', 'label' => 'Home', 'patterns' => ['home']],
        ['route' => 'project', 'label' => 'Projects', 'patterns' => ['project', 'project.show']],
        ['route' => 'about', 'label' => 'About', 'patterns' => ['about']],
        ['route' => 'certificate', 'label' => 'Certificates', 'patterns' => ['certificate']],
        ['route' => 'contact', 'label' => 'Contact', 'patterns' => ['contact']],
    ];

    $github = collect(config('portfolio.contact'))->firstWhere('label', 'GitHub');

    $itemClasses = 'relative rounded-md px-3 py-2 text-sm font-medium transition-colors duration-200';
    $activeClasses = 'text-bone-50';
    $inactiveClasses = 'text-bone-400 hover:text-bone-100';
@endphp

<nav
    x-data="{ open: false }"
    x-on:keydown.escape.window="open = false"
    aria-label="Primary"
    class="sticky top-0 z-50 border-b border-ink-700/60 bg-ink-950/85 backdrop-blur supports-[backdrop-filter]:bg-ink-950/70"
>
    <div class="container-page">
        <div class="flex h-16 items-center justify-between gap-4">
            {{-- Brand --}}
            <a
                href="{{ route('home') }}"
                class="group flex items-baseline gap-1 rounded-md text-base font-semibold tracking-tightest"
                @class(['text-bone-50' => request()->routeIs('home'), 'text-bone-200' => ! request()->routeIs('home')])
            >
                <span>{{ config('portfolio.short_name') }}</span>
                <span class="text-accent-400 transition-opacity duration-200 group-hover:opacity-100" aria-hidden="true">.</span>
                <span class="sr-only">— {{ config('portfolio.name') }}, home</span>
            </a>

            {{-- Desktop navigation --}}
            <div class="hidden items-center gap-1 md:flex">
                <ul class="flex items-center gap-1">
                    @foreach ($links as $link)
                        @php $isActive = request()->routeIs(...$link['patterns']); @endphp
                        <li>
                            <a
                                href="{{ route($link['route']) }}"
                                @if ($isActive) aria-current="page" @endif
                                @class([$itemClasses, $isActive ? $activeClasses : $inactiveClasses])
                            >
                                {{ $link['label'] }}

                                {{-- Active indicator doubles as the non-colour state signal. --}}
                                @if ($isActive)
                                    <span
                                        aria-hidden="true"
                                        class="absolute inset-x-3 -bottom-px h-px bg-accent-400"
                                    ></span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>

                <span class="mx-2 h-5 w-px bg-ink-700" aria-hidden="true"></span>

                <div class="flex items-center gap-1">
                    @if ($github && $github['enabled'])
                        <a
                            href="{{ $github['url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="rounded-md p-2 text-bone-400 transition-colors duration-200 hover:bg-ink-800 hover:text-bone-50"
                        >
                            <x-portfolio.icon name="github" class="h-4 w-4" />
                            <span class="sr-only">{{ $github['label'] }} ({{ $github['handle'] }})</span>
                        </a>
                    @endif

                    <button
                        type="button"
                        x-on:click="$store.theme.toggle()"
                        class="rounded-md p-2 text-bone-400 transition-colors duration-200 hover:bg-ink-800 hover:text-bone-50"
                    >
                        <x-portfolio.icon name="moon" class="h-4 w-4 dark:hidden" />
                        <x-portfolio.icon name="sun" class="hidden h-4 w-4 dark:block" />
                        <span class="sr-only">Toggle colour theme</span>
                    </button>

                    <x-portfolio.button
                        :href="route('contact')"
                        size="sm"
                        class="ml-2"
                    >
                        Get in touch
                    </x-portfolio.button>
                </div>
            </div>

            {{-- Mobile controls --}}
            <div class="flex items-center gap-1 md:hidden">
                @if ($github && $github['enabled'])
                    <a
                        href="{{ $github['url'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="rounded-md p-2 text-bone-400 transition-colors duration-200 hover:text-bone-50"
                    >
                        <x-portfolio.icon name="github" class="h-4 w-4" />
                        <span class="sr-only">{{ $github['label'] }} ({{ $github['handle'] }})</span>
                    </a>
                @endif

                <button
                    type="button"
                    x-on:click="$store.theme.toggle()"
                    class="rounded-md p-2 text-bone-400 transition-colors duration-200 hover:text-bone-50"
                >
                    <x-portfolio.icon name="moon" class="h-4 w-4 dark:hidden" />
                    <x-portfolio.icon name="sun" class="hidden h-4 w-4 dark:block" />
                    <span class="sr-only">Toggle colour theme</span>
                </button>

                <button
                    type="button"
                    x-on:click="open = ! open"
                    x-bind:aria-expanded="open ? 'true' : 'false'"
                    aria-controls="mobile-navigation"
                    class="-mr-1 rounded-md p-2 text-bone-200 transition-colors duration-200 hover:bg-ink-800"
                >
                    <x-portfolio.icon name="menu" class="h-5 w-5" x-show="! open" />
                    <x-portfolio.icon name="close" class="hidden h-5 w-5" x-cloak x-show="open" />
                    <span class="sr-only">Toggle navigation menu</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile navigation --}}
    <div
        id="mobile-navigation"
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="border-t border-ink-700/60 bg-ink-950 md:hidden"
    >
        <ul class="container-page flex flex-col py-3">
            @foreach ($links as $link)
                @php $isActive = request()->routeIs(...$link['patterns']); @endphp
                <li>
                    <a
                        href="{{ route($link['route']) }}"
                        @if ($isActive) aria-current="page" @endif
                        @class([
                            'block rounded-md px-3 py-2.5 text-sm font-medium transition-colors duration-200',
                            $isActive
                                ? 'bg-ink-800 text-bone-50'
                                : 'text-bone-300 hover:bg-ink-850 hover:text-bone-50',
                        ])
                    >
                        {{ $link['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</nav>