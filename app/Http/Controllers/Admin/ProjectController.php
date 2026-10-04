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

        if ($request->hasFile('screenshots')) {
            // storeScreenshots() rolls back its own partial write, so a failure here
            // cannot leave orphaned files behind.
            $project->screenshots = $this->storeScreenshots($request);
            $project->save();
        }

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
        $previousScreenshots = $project->screenshots ?? [];

        $project->fill($this->payload($request));

        /*
         * The slug and the legacy `link` column are only touched when the form
         * actually changes them. Regenerating a slug on every save would silently
         * break published URLs, and clearing `link` would discard data the public
         * site still falls back to.
         *
         * A submitted-but-blank slug is treated as "leave it alone", not "generate a
         * new one": the form always posts the field, so keying off the field merely
         * being present would re-derive the slug from the new title and change the
         * public URL of a project the owner never renamed.
         */
        $submittedSlug = $request->input('slug');

        if (filled($submittedSlug) || blank($project->slug)) {
            $project->slug = Project::uniqueSlug($submittedSlug, $project->title, $project->getKey());
        }

        if ($request->has('link')) {
            $project->link = $request->input('link') ?: null;
        }

        if ($request->boolean('remove_thumbnail')) {
            $project->thumbnail = null;
        }

        if ($request->hasFile('thumbnail')) {
            $project->thumbnail = $this->storeImage($request->file('thumbnail'), self::THUMBNAIL_DIRECTORY);
        }

        /*
         * Screenshot replacement is strictly ordered so an existing set can never be
         * destroyed by a failed upload:
         *
         *   1. detect a real upload with hasFile() (an empty file input is not an upload)
         *   2. store every new file, rolling back the partial set if any store fails
         *   3. persist the new paths
         *   4. only then delete the replaced managed files
         *
         * With no upload the existing paths and files are left completely untouched.
         * Validation has already run, so a rejected request never reaches this code.
         */
        if ($request->hasFile('screenshots')) {
            try {
                $newScreenshots = $this->storeScreenshots($request);
            } catch (\Throwable $e) {
                // storeScreenshots() has already removed only the files this request
                // wrote. The database and the previous screenshot set are untouched.
                report($e);

                return back()
                    ->withInput()
                    ->with('message', [['error', 'The screenshots could not be saved. The previous screenshots were left untouched.']]);
            }

            $project->screenshots = $newScreenshots;
            $project->save();

            $this->deleteReplacedScreenshots($previousScreenshots, $newScreenshots);

            return $this->backToIndex('Data updated successfully.');
        }

        $project->save();

        /*
         * Everything is persisted. Only now is it safe to remove the files that were
         * genuinely replaced.
         */
        if ($request->boolean('remove_thumbnail') || $request->hasFile('thumbnail')) {
            $this->deleteManagedImage($previousThumbnail);
        }

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
        $validated = $request->safe()->only([
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
        ]);

        /*
         * Explicitly assigned rather than combined with `+`: array union keeps the left
         * operand's value for a key present on both sides, which meant these fallbacks
         * were silently unreachable whenever the field had been submitted - so
         * cleanList() never actually cleaned anything.
         */
        $payload = [
            'featured' => $request->boolean('featured'),
            'sort_order' => (int) ($request->input('sort_order') ?? 0),
            'tech_stack' => $this->cleanList($request->input('tech_stack', [])),
            'highlights' => $this->cleanList($request->input('highlights', [])),
        ];

        // Drop the normalised values so the cleaned versions above are authoritative.
        unset($validated['featured'], $validated['sort_order'], $validated['tech_stack'], $validated['highlights']);

        return $validated + $payload;
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
     * Store every uploaded screenshot.
     *
     * If one file fails to store, everything already written by this request is
     * removed before the exception propagates, so a failed upload can never leave a
     * partial set on disk.
     *
     * @return array<int, string>
     */
    private function storeScreenshots(ProjectRequest $request): array
    {
        $paths = [];

        try {
            foreach ($request->file('screenshots') as $file) {
                if (! $file->isValid()) {
                    throw new \RuntimeException('One of the uploaded screenshots could not be read.');
                }

                $paths[] = $file->store(self::SCREENSHOT_DIRECTORY, 'public');
            }
        } catch (\Throwable $e) {
            foreach ($paths as $path) {
                $this->deleteManagedImage($path, self::SCREENSHOT_DIRECTORY);
            }

            throw $e;
        }

        return $paths;
    }

    /**
     * Delete only the previous screenshots that this controller owns, skipping any
     * path that is still referenced by the new set.
     *
     * @param  array<int, string>  $previous
     * @param  array<int, string>  $current
     */
    private function deleteReplacedScreenshots(array $previous, array $current): void
    {
        foreach ($previous as $path) {
            if (! in_array($path, $current, true)) {
                $this->deleteManagedImage($path, self::SCREENSHOT_DIRECTORY);
            }
        }
    }

    /**
     * Delete only files this controller wrote. Anything outside the managed
     * directories - a shared asset, a default placeholder, a legacy path - is
     * left untouched.
     */
    private function deleteManagedImage(?string $path, string $mustStartWith = null): void
    {
        if (blank($path)) {
            return;
        }

        if ($mustStartWith === null) {
            // Default: only the two directories this controller writes to.
            if (! str_starts_with($path, self::THUMBNAIL_DIRECTORY.'/')) {
                return;
            }

            $mustStartWith = self::THUMBNAIL_DIRECTORY;
        }

        if (! str_starts_with($path, $mustStartWith.'/')) {
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
