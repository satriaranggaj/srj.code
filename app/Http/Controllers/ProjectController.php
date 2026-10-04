<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        return view('portfolio.projects.index', [
            'projects' => Project::publiclyVisible()->ordered()->get(),
        ]);
    }

    /**
     * Route model binding resolves `{project:slug}`, so an unknown slug already 404s.
     *
     * A project is also withdrawn when it is archived, or when it has no
     * case-study content to render. Both rules live on the model so the card, this
     * route and the sitemap can never disagree.
     */
    public function show(Project $project): View
    {
        abort_unless($project->hasPublicCaseStudyPage(), 404);

        return view('portfolio.projects.show', [
            'project' => $project,
            'moreProjects' => Project::publiclyVisible()
                ->whereKeyNot($project->getKey())
                ->ordered()
                ->limit(3)
                ->get(),
        ]);
    }

    /**
     * Dynamic sitemap.
     *
     * Contains only URLs that actually resolve: the static public pages plus every
     * publicly visible project that has a case study. No URL here can 404, and no
     * archived project is advertised.
     *
     * Static pages carry no <lastmod> because their content is code, not data, and
     * inventing a per-request timestamp would tell crawlers every page changed on
     * every crawl. Project URLs use the row's real updated_at.
     *
     * Every <loc> is built from APP_URL rather than from the incoming request, so a
     * staging or preview hostname cannot advertise URLs that contradict the
     * APP_URL-derived canonical tags and the robots.txt sitemap line.
     */
    public function sitemap(): Response
    {
        return response()
            ->view('portfolio.sitemap', [
                'baseUrl' => rtrim((string) config('app.url'), '/'),
                'projects' => Project::publiclyVisible()
                    ->withPublicCaseStudy()
                    ->ordered()
                    ->get(['id', 'slug', 'title', 'updated_at']),
            ])
            ->header('Content-Type', 'application/xml');
    }
}
