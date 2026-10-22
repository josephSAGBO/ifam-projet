<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ServiceReport extends Model
{
    use HasFactory;

    public const STATUT_BROUILLON = 'brouillon';

    public const STATUT_ENVOYE = 'envoye';

    public const STATUT_LU = 'lu';

    public const STATUT_RETOURNE = 'retourne';

    public const TYPE_HEBDOMADAIRE = 'hebdomadaire';

    public const TYPE_MENSUEL = 'mensuel';

    public const TYPE_PONCTUEL = 'ponctuel';

    protected $fillable = [
        'service_profile_id',
        'site_id',
        'auteur_id',
        'type',
        'periode_debut',
        'periode_fin',
        'titre',
        'resume',
        'activites',
        'difficultes',
        'besoins',
        'indicateurs',
        'statut',
        'envoye_le',
        'lu_le',
        'lu_par',
        'commentaire_direction',
    ];

    protected function casts(): array
    {
        return [
            'periode_debut' => 'date',
            'periode_fin' => 'date',
            'indicateurs' => 'array',
            'envoye_le' => 'datetime',
            'lu_le' => 'datetime',
        ];
    }

    public function serviceProfile(): BelongsTo
    {
        return $this->belongsTo(ServiceProfile::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auteur_id');
    }

    public function luParUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lu_par');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function scopeVisiblesDirection(Builder $query): Builder
    {
        return $query->where('statut', '!=', self::STATUT_BROUILLON);
    }

    public function scopeForService(Builder $query, int|array $serviceProfileId): Builder
    {
        if (is_array($serviceProfileId)) {
            return $query->whereIn('service_profile_id', $serviceProfileId);
        }

        return $query->where('service_profile_id', $serviceProfileId);
    }

    public function isEditable(): bool
    {
        return in_array($this->statut, [self::STATUT_BROUILLON, self::STATUT_RETOURNE]);
    }

    public function isDraft(): bool
    {
        return $this->statut === self::STATUT_BROUILLON;
    }

    public function isSent(): bool
    {
        return in_array($this->statut, [self::STATUT_ENVOYE, self::STATUT_LU, self::STATUT_RETOURNE]);
    }
}
