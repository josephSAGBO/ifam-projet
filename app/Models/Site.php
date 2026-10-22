<?php

namespace App\Models;

use Database\Factories\SiteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Site extends Model
{
    /** @use HasFactory<SiteFactory> */
    use HasFactory;

    protected $fillable = [
        'nom',
        'ville',
        'adresse',
        'telephone',
        'email',
        'est_actif',
    ];

    protected function casts(): array
    {
        return [
            'est_actif' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('est_actif', true);
    }

    /**
     * @return Collection<int, Site>
     */
    public static function activeSites(): Collection
    {
        return static::query()
            ->where('est_actif', true)
            ->orderByRaw("CASE WHEN nom = 'Cotonou' THEN 0 ELSE 1 END")
            ->orderBy('nom')
            ->get();
    }

    public static function defaultActiveId(): ?int
    {
        $siteId = static::query()
            ->where('nom', 'Cotonou')
            ->where('est_actif', true)
            ->value('id');

        if ($siteId === null) {
            $siteId = static::query()
                ->where('est_actif', true)
                ->orderBy('nom')
                ->value('id');
        }

        return $siteId === null ? null : (int) $siteId;
    }

    public function academicSessions(): HasMany
    {
        return $this->hasMany(AcademicSession::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function alumnis(): HasMany
    {
        return $this->hasMany(Alumni::class);
    }

    public function contactMessages(): HasMany
    {
        return $this->hasMany(ContactMessage::class);
    }

    public function partnerships(): HasMany
    {
        return $this->hasMany(Partnership::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(UserAssignment::class);
    }
}
