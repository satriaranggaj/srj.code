<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\ContactMessage;
use App\Models\Project;
use App\Models\Skill;

class DashboardController extends Controller
{
    public function index()
    {
        return view('Admin.dashboard', [
            'stats' => [
                'projects' => Project::count(),
                'featured' => Project::featured()->count(),
                'skills' => Skill::count(),
                'certificates' => Certificate::count(),
                'messages' => ContactMessage::count(),
                'unreadMessages' => ContactMessage::unread()->count(),
            ],
            'recentMessages' => ContactMessage::latest('id')->limit(5)->get(),
            'latestProjects' => Project::ordered()->limit(5)->get(),
        ]);
    }
}
