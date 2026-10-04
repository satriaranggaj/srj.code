<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'link',
        'issuer',
        'issued_at',
        'image',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'sort_order' => 'integer',
    ];

    /**
     * `link` is the original credential column and stays. It is simply the fallback
     * for the newer, more explicit `credential_url`.
     */
    public function getCredentialUrlAttribute(): ?string
    {
        return filled($this->attributes['link'] ?? null) ? $this->attributes['link'] : null;
    }

    public function getIssuedYearAttribute(): ?string
    {
        return $this->issued_at?->format('Y');
    }

    public function hasCredential(): bool
    {
        return filled($this->credentialUrl);
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('issued_at')->orderByDesc('id');
    }
}
