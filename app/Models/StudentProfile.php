<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Profil métier spécifique à un étudiant IFAM.
 *
 * Relation : 1 user (rôle etudiant) → 1 student_profile.
 */
class StudentProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'birth_place',
        'nationality',
        'birth_certificate_number',
        'address',
        'city',
        'guardian_name',
        'guardian_phone',
        'guardian_relation',
        'previous_school',
        'previous_diploma',
        'entry_year',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'entry_year' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function alumniProfile(): HasOne
    {
        return $this->hasOne(Alumni::class);
    }
}
