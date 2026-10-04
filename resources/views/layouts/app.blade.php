<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- The admin must never be indexed. --}}
    <meta name="robots" content="noindex, nofollow">

    <title>{{ $title ?? config('app.name') }} · {{ config('portfolio.short_name') }} Admin</title>

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ink-950 font-sans text-bone-100 antialiased">
    {{-- Skip link: the sidebar is the first focusable content on every admin page. --}}
    <a
        href="#admin-main"
        class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-accent-400 focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-ink-950"
    >
        Skip to content
    </a>

    <div
        x-data="{ sidebar: false }"
        x-on:keydown.escape.window="sidebar = false"
        class="min-h-screen lg:flex"
    >
        {{-- ── Desktop sidebar ──────────────────────────────────────── --}}
        <aside class="hidden w-64 shrink-0 border-r border-ink-700/70 bg-ink-900 lg:sticky lg:top-0 lg:block lg:h-screen">
            @include('partials.admin.sidebar')
        </aside>

        {{-- ── Mobile drawer ───────────────────────────────────────── --}}
        <div
            x-show="sidebar"
            x-cloak
            x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 lg:hidden"
        >
            {{-- Overlay doubles as the click-outside target. --}}
            <div
                class="absolute inset-0 bg-ink-950/80 backdrop-blur-sm"
                x-on:click="sidebar = false"
                aria-hidden="true"
            ></div>

            <aside
                id="admin-navigation"
                x-show="sidebar"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="relative flex h-full w-72 max-w-[85vw] flex-col border-r border-ink-700/70 bg-ink-900"
                role="dialog"
                aria-modal="true"
                aria-label="Admin navigation"
            >
                @include('partials.admin.sidebar')
            </aside>
        </div>

        {{-- ── Main column ──────────────────────────────────────────── --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-30 border-b border-ink-700/70 bg-ink-950/90 backdrop-blur supports-[backdrop-filter]:bg-ink-950/75">
                <div class="flex min-h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                    <button
                        type="button"
                        x-on:click="sidebar = true"
                        x-bind:aria-expanded="sidebar ? 'true' : 'false'"
                        aria-controls="admin-navigation"
                        class="-ml-1 rounded-lg p-2 text-bone-300 transition-colors duration-150 hover:bg-ink-850 hover:text-bone-50 lg:hidden"
                    >
                        <x-portfolio.icon name="menu" class="h-5 w-5" />
                        <span class="sr-only">Open navigation menu</span>
                    </button>

                    <div class="min-w-0 flex-1">
                        <h1 class="truncate text-base font-semibold tracking-tightest text-bone-50 sm:text-lg">
                            {{ $title ?? 'Dashboard' }}
                        </h1>

                        @isset($subtitle)
                            <p class="truncate text-xs text-bone-500 sm:text-sm">{{ $subtitle }}</p>
                        @endisset
                    </div>

                    <div class="flex shrink-0 items-center gap-1 sm:gap-2">
                        @isset($actions)
                            {{ $actions }}
                        @endisset

                        <a
                            href="{{ route('home') }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="hidden rounded-lg p-2 text-bone-400 transition-colors duration-150 hover:bg-ink-850 hover:text-bone-50 sm:block"
                        >
                            <x-portfolio.icon name="external" class="h-4 w-4" />
                            <span class="sr-only">View site (opens in a new tab)</span>
                        </a>

                        {{-- User menu. Alpine only; no JavaScript framework added. --}}
                        <div x-data="{ open: false }" x-on:click.outside="open = false" x-on:keydown.escape.window="open = false" class="relative">
                            <button
                                type="button"
                                x-on:click="open = ! open"
                                x-bind:aria-expanded="open ? 'true' : 'false'"
                                aria-haspopup="true"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-ink-600 bg-ink-850 font-mono text-xs font-medium text-accent-400 transition-colors duration-150 hover:border-ink-500 hover:bg-ink-800"
                            >
                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name, 0, 2)) }}
                                <span class="sr-only">Account menu for {{ auth()->user()->name }}</span>
                            </button>

                            <div
                                x-show="open"
                                x-cloak
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-xl border border-ink-700 bg-ink-850 shadow-card-hover"
                            >
                                <div class="border-b border-ink-700/70 px-4 py-3">
                                    <p class="truncate text-sm font-medium text-bone-100">{{ auth()->user()->name }}</p>
                                    <p class="truncate font-mono text-xs text-bone-500">{{ auth()->user()->email }}</p>
                                </div>

                                <a
                                    href="{{ route('profile.edit') }}"
                                    class="flex items-center gap-2 px-4 py-2.5 text-sm text-bone-300 transition-colors duration-150 hover:bg-ink-800 hover:text-bone-50"
                                >
                                    <x-portfolio.icon name="terminal" class="h-4 w-4 text-bone-500" />
                                    Profile
                                </a>

                                <a
                                    href="{{ route('home') }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex items-center gap-2 px-4 py-2.5 text-sm text-bone-300 transition-colors duration-150 hover:bg-ink-800 hover:text-bone-50 sm:hidden"
                                >
                                    <x-portfolio.icon name="external" class="h-4 w-4 text-bone-500" />
                                    View site
                                </a>

                                <form method="POST" action="{{ route('logout') }}" class="border-t border-ink-700/70">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-bone-300 transition-colors duration-150 hover:bg-red-500/10 hover:text-red-300"
                                    >
                                        <x-portfolio.icon name="close" class="h-4 w-4" />
                                        Log out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Inline flash + validation feedback. Kept in addition to the existing
                 Toastr output so nothing is lost if the CDN script is unavailable. --}}
            @php
                $flash = collect(Session::get('message', []))
                    ->filter(fn ($entry) => is_array($entry) && count($entry) >= 2);
                $errorList = $errors->all();
            @endphp

            @if ($flash->isNotEmpty() || $errorList !== [])
                <div class="space-y-3 px-4 pt-5 sm:px-6 lg:px-8">
                    @foreach ($flash as $entry)
                        <x-admin.alert :type="$entry[0] === 'error' ? 'error' : ($entry[0] === 'warning' ? 'warning' : 'success')">
                            {{ $entry[1] }}
                        </x-admin.alert>
                    @endforeach

                    @if ($errorList !== [])
                        <x-admin.alert type="error" title="Please fix the following">
                            <ul class="mt-1 list-inside list-disc space-y-0.5">
                                @foreach ($errorList as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </x-admin.alert>
                    @endif
                </div>
            @endif

            <main id="admin-main" tabindex="-1" class="flex-1 px-4 py-6 focus:outline-none sm:px-6 sm:py-8 lg:px-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{--
        Delete-confirmation chain, preserved exactly:
        [data-toggle="delete-button"] -> admin.js (jQuery + SweetAlert2) repoints and
        submits this form. Renaming it would silently break every delete action.
    --}}
    <form action="" method="post" id="form-delete">
        @csrf
        @method('delete')
    </form>

    {{-- Admin-only dependencies. The public site loads none of these. --}}
    <script src="{{ asset('/libraries/jquery/jquery-3.7.0.min.js') }}"></script>
    <script src="{{ asset('/libraries/toastr/toastr.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('/script/admin.js') }}"></script>

    <script>
        /*
         * Toast notifications for flash messages and validation errors.
         *
         * Values are rendered through Laravel's json Blade directive rather than
         * string interpolation, so admin-editable text can never break out of a JS
         * string literal. Types are restricted to the four Toastr methods actually used.
         */
        (function () {
            var allowed = ['success', 'error', 'warning', 'info'];

            function notify(type, text) {
                if (allowed.indexOf(type) === -1) {
                    type = 'info';
                }

                if (typeof window.toastr === 'undefined') {
                    return;
                }

                window.toastr[type](String(text));
            }

            var flash = @json(Session::get('message', []));

            if (Array.isArray(flash)) {
                flash.forEach(function (entry) {
                    if (Array.isArray(entry) && entry.length >= 2) {
                        notify(entry[0], entry[1]);
                    }
                });
            }

            var errors = @json($errors->all());

            if (Array.isArray(errors)) {
                errors.forEach(function (error) {
                    notify('error', error);
                });
            }
        })();
    </script>

    @stack('scripts')
</body>
</html>