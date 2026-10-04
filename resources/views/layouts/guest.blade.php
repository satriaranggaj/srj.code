<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Authentication pages must never be indexed. --}}
    <meta name="robots" content="noindex, nofollow">

    <title>{{ $title ?? 'Sign in' }} · {{ config('portfolio.short_name') }} Admin</title>

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-ink-950 font-sans text-bone-100 antialiased">
    <div class="flex min-h-screen flex-col lg:flex-row">
        {{--
            Brand panel. Hidden on mobile where the form is the only thing that
            matters; shown from lg up so desktop gets the premium two-column
            treatment without a second layout.
        --}}
        <div class="relative hidden overflow-hidden border-r border-ink-700/70 bg-ink-900 lg:flex lg:w-[42%] lg:max-w-xl lg:flex-col lg:justify-between lg:p-12">
            {{-- Same restrained single-accent wash used on the public hero. --}}
            <div
                aria-hidden="true"
                class="pointer-events-none absolute inset-0 bg-[radial-gradient(38rem_28rem_at_20%_0%,rgba(52,211,153,0.10),transparent_70%)]"
            ></div>

            <div class="relative">
                <a href="{{ route('home') }}" class="inline-flex items-baseline gap-0.5 rounded text-lg font-semibold tracking-tightest text-bone-50">
                    <span>{{ config('portfolio.short_name') }}</span>
                    <span class="text-accent-400" aria-hidden="true">.</span>
                    <span class="sr-only">— back to the portfolio</span>
                </a>

                <p class="mt-1 font-mono text-xs uppercase tracking-[0.16em] text-bone-500">
                    Portfolio Administration
                </p>
            </div>

            <div class="relative">
                <p class="max-w-sm text-lg leading-relaxed text-bone-300">
                    {{ config('portfolio.role') }}<br>
                    <span class="text-accent-400">{{ config('portfolio.role_secondary') }}</span>
                </p>

                <p class="mt-6 max-w-sm text-sm leading-relaxed text-bone-500">
                    Manage the projects, technologies and certificates that make up
                    {{ config('portfolio.name') }}'s public portfolio.
                </p>
            </div>

            <p class="relative font-mono text-xs text-bone-400">
                &copy; {{ date('Y') }} {{ config('portfolio.name') }}
            </p>
        </div>

        {{-- Form column --}}
        <div class="flex flex-1 flex-col">
            <div class="flex items-center justify-between gap-3 border-b border-ink-700/70 px-5 py-4 sm:px-8 lg:justify-end lg:border-0">
                <a href="{{ route('home') }}" class="inline-flex items-baseline gap-0.5 rounded text-base font-semibold tracking-tightest text-bone-50 lg:hidden">
                    <span>{{ config('portfolio.short_name') }}</span>
                    <span class="text-accent-400" aria-hidden="true">.</span>
                    <span class="sr-only">— back to the portfolio</span>
                </a>

                <a
                    href="{{ route('home') }}"
                    class="group inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-sm text-bone-400 transition-colors duration-150 hover:bg-ink-850 hover:text-bone-100"
                >
                    <x-portfolio.icon name="arrow-left" class="h-4 w-4 transition-transform duration-150 motion-safe:group-hover:-translate-x-0.5" />
                    <span class="hidden sm:inline">Back to portfolio</span>
                    <span class="sm:hidden">Portfolio</span>
                </a>
            </div>

            <div class="flex flex-1 items-center justify-center px-5 py-10 sm:px-8">
                <div class="w-full max-w-sm">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>