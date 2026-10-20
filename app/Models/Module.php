<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'coefficient',
        'duree_mois',
        'duree_semaines',
        'duree_heures',
        'duree_minutes',
    ];

    protected function casts(): array
    {
        return [
            'coefficient' => 'decimal:2',
            'duree_mois' => 'integer',
            'duree_semaines' => 'integer',
            'duree_heures' => 'integer',
            'duree_minutes' => 'integer',
        ];
    }

    // ─── Relations ──────────────────────────────────────────────────

    public function formations(): BelongsToMany
    {
        return $this->belongsToMany(Formation::class, 'formation_module');
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(TeacherProfile::class, 'enseignant_module', 'module_id', 'teacher_profile_id')
            ->withTimestamps();
    }

    public function enseignants(): BelongsToMany
    {
        return $this->teachers();
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    // ─── Durée ──────────────────────────────────────────────────────

    /**
     * Retourne une chaîne lisible de la durée composite.
     * Exemple : "2 Mois, 3 Semaine(s), 23 Heure(s), 45 min"
     */
    public function getDureeAffichageAttribute(): string
    {
        $parts = [];

        if ($this->duree_mois > 0) {
            $parts[] = $this->duree_mois.' Mois';
        }
        if ($this->duree_semaines > 0) {
            $parts[] = $this->duree_semaines.' Semaine(s)';
        }
        if ($this->duree_heures > 0) {
            $parts[] = $this->duree_heures.' Heure(s)';
        }
        if ($this->duree_minutes > 0) {
            $parts[] = $this->duree_minutes.' min';
        }

        return $parts ? implode(', ', $parts) : '—';
    }

    /**
     * Convertit la durée composite en minutes totales.
     * Conventions : 1 mois = 160h, 1 semaine = 40h, 1 heure = 60 min.
     */
    public function dureeEnMinutes(): int
    {
        return (($this->duree_mois ?? 0) * 160 * 60)
            + (($this->duree_semaines ?? 0) * 40 * 60)
            + (($this->duree_heures ?? 0) * 60)
            + ($this->duree_minutes ?? 0);
    }

    /**
     * Convertit la durée composite en heures (avec décimales).
     */
    public function dureeEnHeures(): float
    {
        return $this->dureeEnMinutes() / 60;
    }
}
