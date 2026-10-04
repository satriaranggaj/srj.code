<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Only the data the homepage actually renders, and only as much of it as it
     * needs. The previous version loaded every skill, project and post row into
     * memory via Project::all()/Post::all().
     */
    public function index(): View
    {
        return view('portfolio.home', [
            'featuredProjects' => Project::featured()->published()->ordered()->limit(6)->get(),
            'recentProjects' => Project::published()->ordered()->limit(3)->get(),
            'skills' => Skill::ordered()->get(),
            'certificates' => Certificate::ordered()->limit(3)->get(),
        ]);
    }

    public function about(): View
    {
        return view('portfolio.about', [
            'skills' => Skill::ordered()->get(),
        ]);
    }
}
