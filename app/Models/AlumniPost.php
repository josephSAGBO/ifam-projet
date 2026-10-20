<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AlumniPost extends Model
{
    use HasFactory;

    protected $table = 'alumni_posts';

    protected $fillable = [
        'alumni_id',
        'title',
        'slug',
        'content',
        'status',
        'rejection_reason',
        'validated_by',
        'published_at',
        'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (AlumniPost $post) {
            if (empty($post->slug)) {
                $baseSlug = Str::slug($post->title) ?: 'post';
                $slug = $baseSlug;
                $counter = 1;

                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }

                $post->slug = $slug;
            }
        });
    }

    // ── Relations ───────────────────────────────────────────────

    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(AlumniPostMedia::class)->orderBy('display_order');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    // ── State Checks & Helpers ──────────────────────────────────

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function getCoverMediaAttribute(): ?AlumniPostMedia
    {
        return $this->media->firstWhere('is_cover', true) ?? $this->media->first();
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->coverMedia?->url;
    }
}
