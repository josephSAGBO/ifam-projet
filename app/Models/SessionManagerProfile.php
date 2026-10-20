<?php

namespace App\Models;

use Database\Factories\SessionManagerProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'department', 'internal_role', 'admin_notes'])]
class SessionManagerProfile extends Model
{
    /** @use HasFactory<SessionManagerProfileFactory> */
    use HasFactory;

    /**
     * L'utilisateur gestionnaire lié à ce profil.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
