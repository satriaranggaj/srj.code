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
    | `live_url` and `link` are both real database columns and are always readable
    | as-is. `resolved_live_url` is the single derived value the public site renders,
    | so the live_url -> link fallback is defined in exactly one place.
    |
    */

    /**
     * The live URL actually used for rendering.
     *
     * Prefers the explicit `live_url` column and falls back to the legacy `link`
     * column so records created before Portfolio V2 keep rendering exactly as they
     * did. Returns null when neither is set.
     */
    public function getResolvedLiveUrlAttribute(): ?string
    {
        // Read the raw attributes so this never shadows or recurses into a column.
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
    | Publication rules
    |--------------------------------------------------------------------------
    |
    | Visibility and case-study availability are defined here once and reused by the
    | homepage, the listing, the case-study route and the sitemap, so those four can
    | never disagree about what is public.
    |
    */

    /**
     * Projects that may appear anywhere on the public site.
     *
     * `live` and `in_progress` are both public: an in-progress project is real work
     * and is shown with its status badge. Only `archived` is withdrawn.
     */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_LIVE, self::STATUS_IN_PROGRESS]);
    }

    /**
     * Archived and unknown statuses are treated as withdrawn.
     *
     * Used by `scopePubliclyVisible()`, and kept separate so the admin listing can
     * still show every status without inheriting a public filter.
     */
    public function scopeNotArchived(Builder $query): Builder
    {
        return $query->where('status', '!=', self::STATUS_ARCHIVED);
    }

    /**
     * Backwards-compatible alias for the pre-hardening scope name.
     *
     * @deprecated Use scopePubliclyVisible(): it states the actual rule instead of
     *             implying "published", which in-progress projects are not.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->publiclyVisible();
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    /**
     * Publicly visible projects that also have something to read, i.e. the projects
     * whose case-study URL resolves instead of returning 404.
     */
    public function scopeWithPublicCaseStudy(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNotNull('description')->where('description', '!=', '')
                ->orWhereNotNull('problem')->where('problem', '!=', '')
                ->orWhereNotNull('solution')->where('solution', '!=', '')
                ->orWhereNotNull('challenges')->where('challenges', '!=', '')
                ->orWhereNotNull('outcome')->where('outcome', '!=', '')
                ->orWhereNotNull('highlights')->where('highlights', '!=', '[]')
                ->orWhereNotNull('screenshots')->where('screenshots', '!=', '[]');
        });
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('created_at')->orderByDesc('id');
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
     * Whether a public case study exists for this project.
     *
     * This is the single source of truth for case-study availability. It is used by
     * the project card (to decide whether to render a Case Study link), by
     * ProjectController::show() (404 when false) and by the sitemap (exclude when
     * false), so a project can never advertise a case-study URL that 404s.
     *
     * A thumbnail is deliberately NOT part of this: missing imagery must never make a
     * written case study inaccessible.
     */
    public function hasCaseStudy(): bool
    {
        return filled($this->description)
            || filled($this->problem)
            || filled($this->solution)
            || filled($this->challenges)
            || filled($this->outcome)
            || $this->highlights !== []
            || $this->screenshots !== [];
    }

    public function isPubliclyVisible(): bool
    {
        return in_array($this->status, [self::STATUS_LIVE, self::STATUS_IN_PROGRESS], true);
    }

    /**
     * Whether /projects/{slug} will actually resolve for this project.
     */
    public function hasPublicCaseStudyPage(): bool
    {
        return $this->isPubliclyVisible() && $this->hasCaseStudy();
    }

    public function hasResolvedLiveUrl(): bool
    {
        return filled($this->resolved_live_url);
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
     * Root-relative storage path for the thumbnail.
     *
     * Used for social preview metadata, where the URL must be built from APP_URL
     * rather than from the incoming request so og:image can never disagree with the
     * canonical URL. Prefer this over thumbnailUrl() when composing absolute URLs.
     */
    public function thumbnailStoragePath(): ?string
    {
        return $this->thumbnail ? 'storage/'.$this->thumbnail : null;
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
