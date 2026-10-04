<?php

use App\Support\PortfolioText;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('projects')) {
            return;
        }

        $projects = DB::table('projects')->select('id', 'title', 'link')->get();

        $used = [];

        foreach ($projects as $project) {
            $update = [];

            if (blank($project->slug)) {
                $update['slug'] = $this->uniqueSlug(
                    PortfolioText::slugify($project->title ?: 'project'),
                    $project->id,
                    $used
                );
            }

            // `link` is preserved untouched. `live_url` only ever receives a copy of it,
            // and only when it actually looks like a URL, so nothing can be invented.
            if (blank($project->link) === false && str_starts_with(ltrim($project->link), 'http')) {
                $update['live_url'] = $project->link;
            }

            if ($update !== []) {
                DB::table('projects')->where('id', $project->id)->update($update);
            }
        }

        if (Schema::hasTable('skills')) {
            foreach (DB::table('skills')->select('id', 'image', 'name')->get() as $skill) {
                if (filled($skill->name)) {
                    continue;
                }

                DB::table('skills')
                    ->where('id', $skill->id)
                    ->update(['name' => PortfolioText::nameFromFilename($skill->image)]);
            }
        }
    }

    public function down(): void
    {
        // Intentionally empty. The backfill only fills columns that Portfolio V2 added and
        // copies values out of `link`; there is nothing that needs to be undone, and a
        // rollback must never destroy data written through the new admin forms.
    }

    private function uniqueSlug(string $base, int $ignoreId, array &$used): string
    {
        $base = $base !== '' ? $base : 'project';
        $slug = $base;
        $suffix = 2;

        while (in_array($slug, $used, true) || $this->slugExists($slug, $ignoreId)) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        $used[] = $slug;

        return $slug;
    }

    private function slugExists(string $slug, int $ignoreId): bool
    {
        return DB::table('projects')
            ->where('slug', $slug)
            ->where('id', '!=', $ignoreId)
            ->exists();
    }
};
