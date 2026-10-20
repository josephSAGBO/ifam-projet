<?php

namespace App\Models;

use Database\Factories\ParentProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['user_id', 'profession', 'employer', 'admin_notes'])]
class ParentProfile extends Model
{
    /** @use HasFactory<ParentProfileFactory> */
    use HasFactory;

    /**
     * L'utilisateur parent lié à ce profil.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Étudiants (enfants) rattachés à ce parent via la table pivot parent_etudiant.
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_etudiant', 'parent_id', 'student_id', 'user_id', 'id')
            ->withPivot('relationship')
            ->withTimestamps();
    }

    public function etudiants(): BelongsToMany
    {
        return $this->children();
    }
}
