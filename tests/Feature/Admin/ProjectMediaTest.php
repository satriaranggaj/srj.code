<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Screenshot and thumbnail media safety.
 *
 * The invariant under test: an existing media set is only ever destroyed once the
 * replacement is safely stored and persisted.
 */
class ProjectMediaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->admin()->create();
    }

    /**
     * @param  array<int, string>  $paths
     */
    private function projectWithScreenshots(array $paths, string $title = 'Lensku'): Project
    {
        return Project::factory()->create([
            'title' => $title,
            'screenshots' => $paths,
        ]);
    }

    private function fakeShots(int $count = 2): array
    {
        $files = [];

        for ($i = 1; $i <= $count; $i++) {
            $files[] = UploadedFile::fake()->image("shot{$i}.png", 1280, 720);
        }

        return $files;
    }

    public function test_uploading_screenshots_stores_them(): void
    {
        $response = $this->actingAs($this->admin)->post(route('project.store'), [
            'title' => 'Davina Event',
            'status' => Project::STATUS_LIVE,
            'screenshots' => $this->fakeShots(3),
        ]);

        $response->assertRedirect(route('project.index'));

        $project = Project::firstOrFail();

        $this->assertCount(3, $project->screenshots);

        foreach ($project->screenshots as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_updating_without_screenshots_preserves_the_existing_set(): void
    {
        $existing = ['projects/screenshots/original-a.png', 'projects/screenshots/original-b.png'];

        foreach ($existing as $path) {
            Storage::disk('public')->put($path, 'original');
        }

        $project = $this->projectWithScreenshots($existing);

        // No `screenshots` key at all, which is what a form without a file selection sends.
        $this->actingAs($this->admin)
            ->put(route('project.update', $project->id), [
                'title' => 'Lensku',
                'status' => Project::STATUS_LIVE,
            ])
            ->assertRedirect(route('project.index'));

        $project->refresh();

        $this->assertSame($existing, $project->screenshots);

        foreach ($existing as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_updating_with_new_screenshots_replaces_the_database_paths(): void
    {
        $existing = ['projects/screenshots/original-a.png'];

        Storage::disk('public')->put($existing[0], 'original');

        $project = $this->projectWithScreenshots($existing);

        $this->actingAs($this->admin)->put(route('project.update', $project->id), [
            'title' => 'Lensku',
            'status' => Project::STATUS_LIVE,
            'screenshots' => $this->fakeShots(2),
        ])->assertRedirect(route('project.index'));

        $project->refresh();

        $this->assertCount(2, $project->screenshots);

        // The stored paths are the new ones, not the previous set.
        $this->assertEmpty(array_intersect($existing, $project->screenshots));

        foreach ($project->screenshots as $path) {
            $this->assertStringStartsWith('projects/screenshots/', $path);
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_replaced_screenshots_are_deleted_only_after_successful_replacement(): void
    {
        $existing = ['projects/screenshots/original-a.png', 'projects/screenshots/original-b.png'];

        foreach ($existing as $path) {
            Storage::disk('public')->put($path, 'original');
        }

        $project = $this->projectWithScreenshots($existing);

        $this->actingAs($this->admin)->put(route('project.update', $project->id), [
            'title' => 'Lensku',
            'status' => Project::STATUS_LIVE,
            'screenshots' => $this->fakeShots(2),
        ]);

        // The database points at the new set...
        $project->refresh();

        foreach ($project->screenshots as $path) {
            $this->assertStringStartsWith('projects/screenshots/', $path);
            Storage::disk('public')->assertExists($path);
        }

        // ...and the replaced files are gone.
        foreach ($existing as $path) {
            Storage::disk('public')->assertMissing($path);
        }
    }

    public function test_validation_failure_does_not_delete_existing_screenshots(): void
    {
        $existing = ['projects/screenshots/original-a.png'];

        Storage::disk('public')->put($existing[0], 'original');

        $project = $this->projectWithScreenshots($existing);

        // Title is required, so the request never reaches the controller.
        $response = $this->actingAs($this->admin)
            ->from(route('project.edit', $project->id))
            ->put(route('project.update', $project->id), [
                'title' => '',
                'status' => Project::STATUS_LIVE,
                'screenshots' => $this->fakeShots(1),
            ]);

        $response->assertSessionHasErrors('title');

        $project->refresh();

        $this->assertSame($existing, $project->screenshots);
        Storage::disk('public')->assertExists($existing[0]);
    }

    public function test_invalid_screenshot_upload_does_not_delete_existing_screenshots(): void
    {
        $existing = ['projects/screenshots/original-a.png'];

        Storage::disk('public')->put($existing[0], 'original');

        $project = $this->projectWithScreenshots($existing);

        // Passes form validation rules but is rejected by the mime/dimension rules.
        $this->actingAs($this->admin)
            ->from(route('project.edit', $project->id))
            ->put(route('project.update', $project->id), [
                'title' => 'Lensku',
                'status' => Project::STATUS_LIVE,
                'screenshots' => [UploadedFile::fake()->create('payload.php', 8, 'application/x-php')],
            ])
            ->assertSessionHasErrors('screenshots.0');

        $project->refresh();

        $this->assertSame($existing, $project->screenshots);
        Storage::disk('public')->assertExists($existing[0]);
    }

    public function test_unrelated_files_outside_the_managed_directory_are_never_deleted(): void
    {
        // Legacy/shared assets that must survive any media operation, even if such a
        // path were somehow stored against the project.
        $shared = 'shared/company-banner.png';
        $otherRecord = 'certificates/someone-elses.png';
        $owned = 'projects/screenshots/owned.png';

        Storage::disk('public')->put($shared, 'do not delete me');
        Storage::disk('public')->put($otherRecord, 'another record');
        Storage::disk('public')->put($owned, 'owned by this project');

        $project = Project::factory()->create([
            'thumbnail' => $shared,
            'screenshots' => [$shared, $otherRecord, $owned],
        ]);

        $this->actingAs($this->admin)->delete(route('project.destroy', $project->id));

        Storage::disk('public')->assertExists($shared);
        Storage::disk('public')->assertExists($otherRecord);
        Storage::disk('public')->assertMissing($owned);
    }

    public function test_a_thumbnail_outside_the_managed_directory_is_not_deleted_on_replace(): void
    {
        $legacyThumbnail = 'img/legacy-thumbnail.png';
        Storage::disk('public')->put($legacyThumbnail, 'legacy');

        $project = Project::factory()->create(['thumbnail' => $legacyThumbnail]);

        $this->actingAs($this->admin)->put(route('project.update', $project->id), [
            'title' => $project->title,
            'status' => Project::STATUS_LIVE,
            'thumbnail' => UploadedFile::fake()->image('new.png', 1280, 720),
        ]);

        $project->refresh();

        $this->assertStringStartsWith('projects/thumbnails/', $project->thumbnail);
        Storage::disk('public')->assertExists($legacyThumbnail);
    }

    public function test_removing_the_thumbnail_deletes_the_managed_file(): void
    {
        $this->actingAs($this->admin)->post(route('project.store'), [
            'title' => 'Thumbnail Project',
            'status' => Project::STATUS_LIVE,
            'thumbnail' => UploadedFile::fake()->image('thumb.png', 1280, 720),
        ]);

        $project = Project::firstOrFail();
        $path = $project->thumbnail;

        $this->actingAs($this->admin)->put(route('project.update', $project->id), [
            'title' => $project->title,
            'status' => Project::STATUS_LIVE,
            'remove_thumbnail' => '1',
        ]);

        $project->refresh();

        $this->assertNull($project->thumbnail);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_updating_without_a_thumbnail_upload_keeps_the_existing_thumbnail(): void
    {
        $this->actingAs($this->admin)->post(route('project.store'), [
            'title' => 'Thumbnail Project',
            'status' => Project::STATUS_LIVE,
            'thumbnail' => UploadedFile::fake()->image('thumb.png', 1280, 720),
        ]);

        $project = Project::firstOrFail();
        $path = $project->thumbnail;

        $this->actingAs($this->admin)->put(route('project.update', $project->id), [
            'title' => 'Renamed Project',
            'status' => Project::STATUS_LIVE,
        ]);

        $project->refresh();

        $this->assertSame($path, $project->thumbnail);
        Storage::disk('public')->assertExists($path);
    }

    public function test_destroy_deletes_managed_screenshots(): void
    {
        $paths = ['projects/screenshots/a.png', 'projects/screenshots/b.png'];

        foreach ($paths as $path) {
            Storage::disk('public')->put($path, 'x');
        }

        $project = $this->projectWithScreenshots($paths);

        $this->actingAs($this->admin)->delete(route('project.destroy', $project->id));

        foreach ($paths as $path) {
            Storage::disk('public')->assertMissing($path);
        }
    }
}
