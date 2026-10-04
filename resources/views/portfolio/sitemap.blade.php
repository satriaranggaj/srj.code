@php
    /**
     * Dynamic XML sitemap covering the public pages plus every published case study.
     *
     * Rendered by ProjectController::sitemap() and served with an application/xml
     * content type. The controller supplies only projects whose case-study URL
     * actually resolves, so nothing listed here returns 404.
     *
     * Every <loc> is built from APP_URL, not from the incoming request, so a staging
     * or preview hostname cannot advertise URLs that contradict the APP_URL-derived
     * canonical tags and the robots.txt Sitemap line.
     *
     * Static pages intentionally carry no <lastmod>: their content lives in code, not
     * in the database, and a per-request timestamp would falsely report every page
     * as modified on every crawl. Project URLs use the row's real updated_at.
     *
     * @var string $baseUrl
     */
    $staticPages = [
        ['path' => '/', 'changefreq' => 'weekly', 'priority' => '1.0'],
        ['path' => '/projects', 'changefreq' => 'weekly', 'priority' => '0.9'],
        ['path' => '/about', 'changefreq' => 'monthly', 'priority' => '0.7'],
        ['path' => '/certificates', 'changefreq' => 'monthly', 'priority' => '0.6'],
        ['path' => '/contact', 'changefreq' => 'monthly', 'priority' => '0.6'],
    ];
@endphp
{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($staticPages as $page)
    <url>
        <loc>{{ $baseUrl }}{{ $page['path'] }}</loc>
        <changefreq>{{ $page['changefreq'] }}</changefreq>
        <priority>{{ $page['priority'] }}</priority>
    </url>
@endforeach
@foreach ($projects as $project)
    <url>
        <loc>{{ $baseUrl }}/projects/{{ $project->slug }}</loc>
        @if ($project->updated_at)
            <lastmod>{{ $project->updated_at->toAtomString() }}</lastmod>
        @endif
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
@endforeach
</urlset>