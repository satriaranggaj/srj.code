@props([
    'title',
    'description' => null,
    'canonical' => null,
    'image' => null,
    'type' => 'website',
    'jsonLd' => [],
    'noindex' => false,
])

@php
    $siteName = config('portfolio.name');
    $defaultTitle = config('portfolio.seo.title');
    $rawTitle = filled($title) ? $title : $defaultTitle;

    // Avoid "Home | Home | Name" style duplication on the homepage.
    $fullTitle = $rawTitle === $defaultTitle
        ? $rawTitle
        : $rawTitle.' — '.$siteName;

    $metaDescription = \Illuminate\Support\Str::limit(
        filled($description) ? $description : config('portfolio.description'),
        300,
        ''
    );

    /*
     * Absolute asset URLs are built from APP_URL rather than from the incoming
     * request, so the canonical URL, og:url and og:image can never disagree.
     */
    $absoluteUrl = function (?string $path): string {
        return rtrim(config('app.url'), '/').'/'.ltrim((string) $path, '/');
    };

    $canonicalUrl = $canonical ?: $absoluteUrl(request()->getPathInfo());

    $ogImage = $image
        ? (str_starts_with((string) $image, 'http') ? (string) $image : $absoluteUrl($image))
        : $absoluteUrl(config('portfolio.seo.og_image'));

    $twitterSite = config('portfolio.seo.twitter_site');

    $defaultJsonLd = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Person',
        'name' => $siteName,
        'jobTitle' => config('portfolio.role'),
        'description' => config('portfolio.description'),
        'url' => url('/'),
        'sameAs' => collect(config('portfolio.contact'))
            ->filter(fn ($channel) => $channel['enabled'] && filled($channel['url'] ?? null))
            ->pluck('url')
            ->values()
            ->all(),
        'knowsAbout' => collect(config('portfolio.stack_groups'))->pluck('items')->flatten()->values()->all(),
    ]);

    $structuredData = $jsonLd ?: $defaultJsonLd;
@endphp

<title>{{ $fullTitle }}</title>

<meta name="description" content="{{ $metaDescription }}">
<link rel="canonical" href="{{ $canonicalUrl }}">
@if ($noindex)
    <meta name="robots" content="noindex, nofollow">
@else
    <meta name="robots" content="index, follow, max-image-preview:large">
@endif

{{-- Open Graph --}}
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $rawTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:alt" content="{{ $rawTitle }}">
<meta property="og:locale" content="en_US">

{{-- Twitter / X --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $rawTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
<meta name="twitter:image" content="{{ $ogImage }}">
@if ($twitterSite)
    <meta name="twitter:site" content="{{ $twitterSite }}">
@endif

{{-- Structured data: only real, verifiable values. --}}
@if ($structuredData)
    <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif