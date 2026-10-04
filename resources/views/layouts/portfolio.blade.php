<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Theme is applied before first paint so the wrong palette is never flashed. --}}
    <script>
        (function () {
            try {
                var stored = window.localStorage.getItem('srj-theme');
                var theme = stored === 'light' ? 'light' : 'dark';
                document.documentElement.classList.toggle('dark', theme !== 'light');
                document.documentElement.dataset.theme = theme;
            } catch (e) {
                document.documentElement.dataset.theme = 'dark';
            }
        })();
    </script>

    <x-portfolio.seo
        :title="$seoTitle ?? null"
        :description="$seoDescription ?? null"
        :canonical="$seoCanonical ?? null"
        :image="$seoImage ?? null"
        :type="$seoType ?? 'website'"
        :json-ld="$seoJsonLd ?? []"
        :noindex="$seoNoindex ?? false"
    />

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('frontend/img/icon/srj-icon.png') }}">

    <link rel="preconnect" href="{{ asset('/') }}" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-ink-950">
    <a
        href="#main"
        class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-accent-400 focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-ink-950"
    >
        Skip to content
    </a>

    <x-portfolio.navbar />

    <main id="main" tabindex="-1" class="focus:outline-none">
        {{ $slot }}
    </main>

    <x-portfolio.footer />
</body>
</html>