<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternshipSite extends Model
{
    protected $fillable = [
        'nom',
        'type',
        'secteur_activite',
        'description',
        'pays',
        'departement',
        'commune',
        'quartier',
        'adresse',
        'repere',
        'contact_nom',
        'contact_fonction',
        'telephone_principal',
        'telephone_secondaire',
        'email',
        'site_web',
        'capacite_max',
        'statut',
        'site_id',
    ];

    protected function casts(): array
    {
        return [
            'capacite_max' => 'integer',
        ];
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('statut', 'actif');
    }

    public function scopeBySite(Builder $query, int $siteId): Builder
    {
        return $query->where('site_id', $siteId);
    }

    // ── Relations ─────────────────────────────────────────────────────────

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
