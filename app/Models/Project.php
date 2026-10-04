<?php

namespace App\Models;

use App\Support\PortfolioText;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;

    public const STATUS_LIVE = 'live';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'description',
        'thumbnail',
        'project_type',
        'tech_stack',
        'link',
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
        'screenshots',
    ];

    protected $casts = [
        'tech_stack' => 'array',
        'highlights' => 'array',
        'screenshots' => 'array',
        'featured' => 'boolean',
        'sort_order' => 'integer',
        'year' => 'string',
    ];

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    |
    | `link` is the original column and is never removed. Every read of the live
    | URL goes through the `liveUrl` accessor, which falls back to `link` so rows
    | before Portfolio V2 keep rendering exactly as they did.
    |
    */

    public function getLiveUrlAttribute(): ?string
    {
        // Read the raw column so this accessor does not call itself recursively.
        $live = $this->attributes['live_url'] ?? null;
        $legacy = $this->attributes['link'] ?? null;

        return filled($live) ? $live : ($legacy ?: null);
    }

    public function getTechStackAttribute($value): array
    {
        return $this->normaliseList($value);
    }

    public function getHighlightsAttribute($value): array
    {
        return $this->normaliseList($value);
    }

    public function getScreenshotsAttribute($value): array
    {
        return $this->normaliseList($value);
    }

    public function getDisplayStatusAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_IN_PROGRESS => 'In progress',
            self::STATUS_ARCHIVED => 'Archived',
            default => 'Live',
        };
    }

    public function getSummaryAttribute(): ?string
    {
        return $this->short_description
            ?: ($this->description ? Str::limit(strip_tags($this->description), 180) : null);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | These replace the removed `Project::rev()` / `Project::lastProject()`
    | helpers, which loaded the whole table into PHP just to reverse and slice it.
    |
    */

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', '!=', self::STATUS_ARCHIVED);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('created_at')->orderByDesc('id');
    }

    public function scopeForShowcase(Builder $query, int $limit = 3): Builder
    {
        return $query->published()->ordered()->limit($limit);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Build a slug that does not collide with an existing project.
     *
     * An explicit slug entered by the owner always wins. Only when it is blank
     * is one generated from the title.
     */
    public static function uniqueSlug(?string $desired, ?string $title, int $ignoreId = null): string
    {
        $base = PortfolioText::slugify($desired ?: $title) ?: 'project';
        $slug = $base;
        $suffix = 2;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn (Builder $q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * A case study only makes sense once there is something to read.
     */
    public function hasCaseStudy(): bool
    {
        return filled($this->description)
            || filled($this->problem)
            || filled($this->solution)
            || $this->highlights !== [];
    }

    public function hasLiveUrl(): bool
    {
        return filled($this->liveUrl);
    }

    public function hasGithubUrl(): bool
    {
        return filled($this->github_url);
    }

    public function thumbnailUrl(): ?string
    {
        return $this->thumbnail ? asset('storage/'.$this->thumbnail) : null;
    }

    /**
     * @return array<int, string>
     */
    private function normaliseList($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : preg_split('/\s*,\s*/', $value);
        }

        return collect($value)
            ->map(fn ($item) => is_scalar($item) ? trim((string) $item) : '')
            ->filter()
            ->values()
            ->all();
    }
}
