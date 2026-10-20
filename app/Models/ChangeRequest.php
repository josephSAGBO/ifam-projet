<?php

namespace App\Models;

use Database\Factories\ChangeRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'requested_by', 'type', 'target_id', 'target_type',
    'old_value', 'new_value', 'reason', 'status',
    'processed_by', 'processed_at',
])]
class ChangeRequest extends Model
{
    /** @use HasFactory<ChangeRequestFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
            'old_value' => 'array',
            'new_value' => 'array',
        ];
    }

    /**
     * L'utilisateur qui a fait la demande.
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * L'administrateur qui a traité la demande.
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Le modèle cible concerné par la modification (User, Grade, etc.).
     */
    public function target()
    {
        return $this->morphTo();
    }

    /**
     * Applique les nouvelles valeurs sur le modèle cible.
     */
    public function applyChanges(): void
    {
        if ($this->target_type && $this->target_id && is_array($this->new_value)) {
            $target = $this->target;
            if ($target) {
                // Met à jour les champs du modèle cible avec les nouvelles valeurs
                $target->update($this->new_value);
            }
        }
    }
}
