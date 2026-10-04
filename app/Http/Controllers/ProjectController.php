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
     */
    public function sitemap(): Response
    {
        return response()
            ->view('portfolio.sitemap', [
                'projects' => Project::publiclyVisible()
                    ->withPublicCaseStudy()
                    ->ordered()
                    ->get(['id', 'slug', 'title', 'updated_at']),
            ])
            ->header('Content-Type', 'application/xml');
    }
}
