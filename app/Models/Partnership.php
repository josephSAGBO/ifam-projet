<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Partnership extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'site_id',
        'prenom',
        'telephone',
        'email',
        'type_partenariat',
        'duree',
        'secteur_intervention',
        'ce_qu_ils_offrent',
        'ce_qu_on_offre',
        'personne_ressource_interne',
        'suivi',
        'date_dernier_contact',
        'statut',
    ];

    protected $casts = [
        'date_dernier_contact' => 'date',
    ];

    public function getFullContactNameAttribute(): string
    {
        return trim(($this->prenom ?? '').' '.$this->nom);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
