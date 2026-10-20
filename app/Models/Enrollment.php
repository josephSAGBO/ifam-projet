<?php

namespace App\Models;

use App\Models\Concerns\AssignsDefaultSite;
use App\Services\PaiementService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enrollment extends Model
{
    use AssignsDefaultSite, HasFactory, SoftDeletes;

    public const STUDENT_ACCESSIBLE_STATUSES = ['active', 'completed'];

    protected $fillable = [
        'student_profile_id',
        'formation_id',
        'academic_session_id',
        'site_id',
        'prix_formation',
        'bourse_type',
        'bourse_valeur',
        'montant_du',
        'status',
        'enrollment_date',
        'notes',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'prix_formation' => 'decimal:2',
        'bourse_valeur' => 'decimal:2',
        'montant_du' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Enrollment $enrollment): void {
            $enrollment->site_id = AcademicSession::query()
                ->findOrFail($enrollment->academic_session_id)
                ->site_id;
        });
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class);
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'inscription_id');
    }

    public function paiements(): HasMany
    {
        return $this->payments();
    }

    public function getTotalPayeAttribute(): float
    {
        return app(PaiementService::class)->totalPaye($this);
    }

    public function getSoldeAttribute(): float
    {
        return app(PaiementService::class)->solde($this);
    }

    public function getStatutPaiementAttribute(): string
    {
        return app(PaiementService::class)->statut($this);
    }
}
