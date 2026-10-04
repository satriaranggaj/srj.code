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
            'projects' => Project::published()->ordered()->get(),
        ]);
    }

    /**
     * Route model binding resolves `{project:slug}`; an unknown slug therefore
     * produces a 404 rather than an empty case study.
     */
    public function show(Project $project): View
    {
        abort_unless($project->status !== Project::STATUS_ARCHIVED, 404);

        return view('portfolio.projects.show', [
            'project' => $project,
            'moreProjects' => Project::published()
                ->whereKeyNot($project->getKey())
                ->ordered()
                ->limit(3)
                ->get(),
        ]);
    }

    /**
     * Dynamic sitemap covering the public pages plus every published case study.
     */
    public function sitemap(): Response
    {
        return response()
            ->view('portfolio.sitemap', [
                'projects' => Project::published()->ordered()->get(['id', 'slug', 'updated_at']),
            ])
            ->header('Content-Type', 'application/xml');
    }
}
