@php
    /**
     * Dynamic XML sitemap covering the public pages plus every published case study.
     *
     * Rendered by ProjectController::sitemap() and served with an
     * application/xml content type. Referenced from robots.txt.
     */
    $staticPages = [
        ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'weekly'],
        ['loc' => route('project'), 'priority' => '0.9', 'changefreq' => 'weekly'],
        ['loc' => route('about'), 'priority' => '0.7', 'changefreq' => 'monthly'],
        ['loc' => route('certificate'), 'priority' => '0.6', 'changefreq' => 'monthly'],
        ['loc' => route('contact'), 'priority' => '0.6', 'changefreq' => 'monthly'],
    ];

    $lastModified = now()->toAtomString();
@endphp
{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($staticPages as $page)
    <url>
        <loc>{{ $page['loc'] }}</loc>
        <lastmod>{{ $lastModified }}</lastmod>
        <changefreq>{{ $page['changefreq'] }}</changefreq>
        <priority>{{ $page['priority'] }}</priority>
    </url>
@endforeach
@foreach ($projects as $project)
    <url>
        <loc>{{ route('project.show', $project) }}</loc>
        <lastmod>{{ ($project->updated_at ?? now())->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
@endforeach
</urlset>