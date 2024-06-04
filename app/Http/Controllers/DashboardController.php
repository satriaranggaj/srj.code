<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\Project;
use App\Models\Certificate;
use App\Http\Requests\ListViewRequest;
use App\Utilities\AppUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    // Skill Controller
    public function indexSkill(ListViewRequest $request)
    {
        $query = Skill::query();

        return view('Admin.skills.index', [
            'list_view' => AppUtility::listView($query, $request),
            'skills' => Skill::all(),
        ]);
    }

    public function createSkill()
    {
        return view('Admin.skills.form');
    }

    public function storeSkill(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('skills-logo', 'public');
            $validated['image'] = $imagePath;
        }

        Skill::create($validated);

        return redirect(route('skill.index'))->with('message', [
            ['success', 'Data saved successfully.']
        ]);
    }

    public function editSkill(Skill $skill)
    {
        return view('Admin.skills.form', [
            'data' => $skill
        ]);
    }

    public function updateSkill(Request $request, Skill $skill)
    {
        $validated = $request->validate([
            'image' => 'image|mimes:jpeg,png,jpg,gif,webp',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('skills-logo', 'public');
            $validated['image'] = $imagePath;

            if ($skill->image) {
                Storage::disk('public')->delete($skill->image);
            }
        } else {
            $validated['image'] = $skill->image;
        }

        $skill->update($validated);

        return redirect(route('skill.index'))->with('message', [
            ['success', 'Data saved successfully.']
        ]);
    }

    public function destroySkill(Skill $skill)
    {
        try {
            Storage::disk('public')->delete($skill->image);
            $skill->delete();

            return redirect(route('skill.index'))->with('message', [
                ['success', 'Data deleted successfully.']
            ]);
        } catch (\Exception $e) {
            return back()->with('message', [
                ['error', 'Failed to delete data.']
            ]);
        }
    }
    // Skill Controller

    // Project Controller
    public function indexProject(ListViewRequest $request)
    {
        $query = Project::query();

        return view('Admin.projects.index', [
            'list_view' => AppUtility::listView($query, $request),
            'projects' => Project::all(),
        ]);
    }

    public function createProject()
    {
        return view('Admin.projects.form');
    }

    public function storeProject(Request $request)
    {
        $validated = $request->validate([
            'link' => 'required',
            'title' => 'required',
        ]);

        Project::create($validated);

        return redirect(route('project.index'))->with('message', [
            ['success', 'Data saved successfully.']
        ]);
    }

    public function editProject(Project $project)
    {
        return view('Admin.projects.form', [
            'data' => $project
        ]);
    }

    public function updateProject(Request $request, Project $project)
    {
        $validated = $request->validate([
            'link' => 'required',
            'title' => 'required',
        ]);

        $project->update($validated);

        return redirect(route('project.index'))->with('message', [
            ['success', 'Data saved successfully.']
        ]);
    }

    public function destroyProject(Project $project)
    {
        $project->delete();

        return redirect(route('project.index'))->with('message', [
            ['success', 'Data deleted successfully.']
        ]);
    }
    // Project Controller

    // Certificate Controller
    public function indexCertificate(ListViewRequest $request)
    {
        $query = Certificate::query();

        return view('Admin.certificates.index', [
            'list_view' => AppUtility::listView($query, $request),
            'certificates' => Certificate::all(),
        ]);
    }

    public function createCertificate()
    {
        return view('Admin.certificates.form');
    }

    public function storeCertificate(Request $request)
    {
        $validated = $request->validate([
            'link' => 'required',
            'title' => 'required',
        ]);

        Certificate::create($validated);

        return redirect(route('certificate.index'))->with('message', [
            ['success', 'Data saved successfully.']
        ]);
    }

    public function editCertificate(Certificate $certificate)
    {
        return view('Admin.certificates.form', [
            'data' => $certificate
        ]);
    }

    public function updateCertificate(Request $request, Certificate $certificate)
    {
        $validated = $request->validate([
            'link' => 'required',
            'title' => 'required',
        ]);

        $certificate->update($validated);

        return redirect(route('certificate.index'))->with('message', [
            ['success', 'Data saved successfully.']
        ]);
    }

    public function destroyCertificate(Certificate $certificate)
    {
        $certificate->delete();

        return redirect(route('certificate.index'))->with('message', [
            ['success', 'Data deleted successfully.']
        ]);
    }
    // Certificate Controller

    // Post Controller
    // public function indexPost(ListViewRequest $request)
    // {
    //     $query = Post::query();

    //     return view('Admin.posts.index', [
    //         'list_view' => AppUtility::listView($query, $request),
    //         'posts' => Post::all(),
    //     ]);
    // }

    // public function createPost()
    // {
    //     return view('Admin.posts.form');
    // }

    // public function storePost(Request $request)
    // {
    //     $validated = $request->validate([
    //         'category_id' => 'required',
    //         'slug' => 'required',
    //         'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,webp',
    //         'title' => 'required',
    //         'excerpt' => 'required',
    //         'body' => 'required',
    //         'published_at' => 'nullable',
    //     ]);

    //     if ($request->hasFile('thumbnail')) {
    //         $thumbnailPath = $request->file('thumbnail')->store('post', 'public');
    //         $validated['thumbnail'] = $thumbnailPath;
    //     }

    //     Post::create($validated);

    //     return redirect(route('post.index'))->with('message', [
    //         ['success', 'Data saved successfully.']
    //     ]);
    // }

    // public function editPost(Post $post)
    // {
    //     return view('Admin.posts.form', [
    //         'data' => $post
    //     ]);
    // }

    // public function updatePost(Request $request, Post $post)
    // {
    //     $validated = $request->validate([
    //         'slug' => 'required',
    //         'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,webp',
    //         'title' => 'required',
    //         'excerpt' => 'required',
    //         'body' => 'required',
    //         'published_at' => 'nullable',
    //     ]);

    //     if ($request->hasFile('thumbnail')) {
    //         $thumbnailPath = $request->file('thumbnail')->store('post', 'public');
    //         $validated['thumbnail'] = $thumbnailPath;

    //         if ($skill->thumbnail) {
    //             Storage::disk('public')->delete($skill->thumbnail);
    //         }
    //     } else {
    //         $validated['thumbnail'] = $skill->thumbnail;
    //     }

    //     $validated['category_id'] = $request->input('category_id');

    //     $project->update($validated);

    //     return redirect(route('post.index'))->with('message', [
    //         ['success', 'Data saved successfully.']
    //     ]);
    // }

    // public function destroyPost(Post $post)
    // {
    //     try {
    //         Storage::disk('public')->delete($post->thumbnail);
    //         $post->delete();

    //         return redirect(route('post.index'))->with('message', [
    //             ['success', 'Data deleted successfully.']
    //         ]);
    //     } catch (\Exception $e) {
    //         return back()->with('message', [
    //             ['error', 'Failed to delete data.']
    //         ]);
    //     }
    // }
}