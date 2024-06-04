<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Skill;
use App\Models\Project;
use App\Models\Certificate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{   
    // home page
    public function index()
    {
        
        return view('Home.home', [
            "title" => "Home",
            "skills" => Skill::all(),
            "posts" => Post::lastPost(),
            "projects" => Project::lastProject(),
        ]);
    }
    // home page

    // about page
    public function bio()
    {
        return view('Home.about', [
            "title" => "Bio",
            "image" => "IMG_1666.jpg",
        ]);
    }
    // about page

    // projects page
    public function projects()
    {
        return view('Home.projects',[
            "title" => "Projects",
            "projects" => Project::rev(),
        ]);
    }
    // projects page

    // public function blog()
    // {
    //     // blog page
    //     return view('Home.posts', [
    //         "title" => "Blog",
    //         "posts" => Post::rev(),
    //     ]);
    //     // blog page
    // }

    public function certificate()
    {
        return view('Home.certificates',[
            "title" => "Certificates",
            "certificates" => Certificate::all(),
        ]);
    }

    public function resume()
    {
        return view('Home.resume');
    }

    public function contact()
    {
        return view('Home.contact',[
            "title" => "Contact"
        ]);
    }
    // public function blogPost(Post $post)
    // {
    //     return view('Home.post',[
    //         "title" => "Blog",
    //         "post" => $post,
    //     ]);
    // }
}
