<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    /**
     * Canonical grouping used by the public tech-stack section.
     *
     * @var array<int, string>
     */
    public const CATEGORIES = [
        'Frontend',
        'Backend',
        'AI / Machine Learning',
        'Database',
        'Infrastructure',
        'Tools',
    ];

    protected $fillable = [
        'name',
        'image',
        'category',
        'url',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * A badge always needs something to render. Rows created before Portfolio V2
     * only had an image, so fall back to the file name rather than showing a blank.
     */
    public function getDisplayNameAttribute(): string
    {
        if (filled($this->name)) {
            return $this->name;
        }

        if (filled($this->image)) {
            return \App\Support\PortfolioText::nameFromFilename($this->image) ?: 'Technology';
        }

        return 'Technology';
    }

    public function getIsLinkedAttribute(): bool
    {
        return filled($this->url);
    }

    public function logoUrl(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeInCategory(Builder $query, ?string $category): Builder
    {
        return $query->when($category, fn (Builder $q) => $q->where('category', $category));
    }
}
