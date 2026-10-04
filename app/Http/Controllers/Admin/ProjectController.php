<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProjectController extends Controller
{
    private const THUMBNAIL_DIRECTORY = 'projects/thumbnails';

    private const SCREENSHOT_DIRECTORY = 'projects/screenshots';

    public function index()
    {
        return view('Admin.projects.index', [
            'projects' => Project::ordered()->get(),
        ]);
    }

    public function create(): View
    {
        return view('Admin.projects.form', [
            'projectTypes' => config('portfolio.project_types', []),
        ]);
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $project = new Project;

        $project->fill($this->payload($request));
        $project->slug = Project::uniqueSlug($request->input('slug'), $project->title);
        $project->save();

        if ($request->hasFile('thumbnail')) {
            $project->thumbnail = $this->storeImage($request->file('thumbnail'), self::THUMBNAIL_DIRECTORY);
            $project->save();
        }

        $project->screenshots = $this->storeScreenshots($request);
        $project->save();

        return $this->backToIndex('Data saved successfully.');
    }

    public function edit(Project $project): View
    {
        return view('Admin.projects.form', [
            'data' => $project,
            'projectTypes' => config('portfolio.project_types', []),
        ]);
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $previousThumbnail = $project->thumbnail;

        $project->fill($this->payload($request));

        /*
         * The slug and the legacy `link` column are only touched when the form
         * actually submits them. Regenerating a slug on every save would silently
         * break published URLs, and clearing `link` would discard data the public
         * site still falls back to.
         */
        if ($request->has('slug') || blank($project->slug)) {
            $project->slug = Project::uniqueSlug($request->input('slug'), $project->title, $project->getKey());
        }

        if ($request->has('link')) {
            $project->link = $request->input('link') ?: null;
        }

        if ($request->boolean('remove_thumbnail')) {
            $this->deleteManagedImage($previousThumbnail);
            $project->thumbnail = null;
        }

        if ($request->hasFile('thumbnail')) {
            $project->thumbnail = $this->storeImage($request->file('thumbnail'), self::THUMBNAIL_DIRECTORY);
            $this->deleteManagedImage($previousThumbnail);
        }

        if ($request->has('screenshots')) {
            foreach ($project->screenshots ?? [] as $screenshot) {
                $this->deleteManagedImage($screenshot, self::SCREENSHOT_DIRECTORY);
            }
        }

        $project->screenshots = $this->storeScreenshots($request, $project->screenshots ?? []);
        $project->save();

        return $this->backToIndex('Data updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->deleteManagedImage($project->thumbnail);

        foreach ($project->screenshots ?? [] as $screenshot) {
            $this->deleteManagedImage($screenshot, self::SCREENSHOT_DIRECTORY);
        }

        $project->delete();

        return $this->backToIndex('Data deleted successfully.');
    }

    /**
     * Only the fields Portfolio V2 owns. `link`, `slug` and file paths are handled
     * separately so a missing optional column can never blank out existing data.
     *
     * @return array<string, mixed>
     */
    private function payload(ProjectRequest $request): array
    {
        return $request->safe()->only([
            'title',
            'short_description',
            'description',
            'project_type',
            'tech_stack',
            'live_url',
            'github_url',
            'featured',
            'status',
            'sort_order',
            'problem',
            'solution',
            'highlights',
            'challenges',
            'outcome',
            'role',
            'year',
        ]) + [
            'featured' => $request->boolean('featured'),
            'sort_order' => (int) ($request->input('sort_order') ?? 0),
            'tech_stack' => $this->cleanList($request->input('tech_stack', [])),
            'highlights' => $this->cleanList($request->input('highlights', [])),
        ];
    }

    /**
     * @param  array<int, mixed>  $items
     * @return array<int, string>
     */
    private function cleanList($items): array
    {
        return collect(is_array($items) ? $items : [])
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }

    private function storeImage($file, string $directory): string
    {
        return $file->store($directory, 'public');
    }

    /**
     * @param  array<int, string>  $existing
     * @return array<int, string>
     */
    private function storeScreenshots(ProjectRequest $request, array $existing = []): array
    {
        if (! $request->hasFile('screenshots')) {
            return $existing;
        }

        $paths = [];

        foreach ($request->file('screenshots') as $file) {
            $paths[] = $file->store(self::SCREENSHOT_DIRECTORY, 'public');
        }

        return $paths;
    }

    /**
     * Deletes only files this controller wrote. Anything outside the managed
     * directories - a shared asset, a default placeholder, a legacy path - is
     * left untouched.
     */
    private function deleteManagedImage(?string $path, string $mustStartWith = null): void
    {
        if (blank($path) || ! str_contains($path, 'projects/')) {
            return;
        }

        if ($mustStartWith !== null && ! str_starts_with($path, $mustStartWith)) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function backToIndex(string $message): RedirectResponse
    {
        return redirect(route('project.index'))->with('message', [
            ['success', $message],
        ]);
    }
}
