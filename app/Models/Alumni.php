<?php

namespace App\Models;

use App\Models\Concerns\AssignsDefaultSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Alumni extends Model
{
    use AssignsDefaultSite, HasFactory;

    protected $table = 'alumnis';

    protected $fillable = [
        'nom',
        'prenom',
        'photo_path',
        'email',
        'telephone',
        'date_diplome',
        'promotion_year',
        'current_position',
        'company',
        'city',
        'allow_public_email',
        'formation_id',
        'site_id',
        'student_profile_id',
        'lieu_residence',
        'statut_actuel',
        'entreprise_actuelle',
        'poste_actuel',
        'rapports_donnees',
        'is_approved',
        'is_visible_directory',
        'is_public',
        'linkedin_url',
        'portfolio_url',
        'bio',
    ];

    protected $attributes = [
        'is_public' => false,
    ];

    protected function casts(): array
    {
        return [
            'date_diplome' => 'date',
            'is_approved' => 'boolean',
            'is_visible_directory' => 'boolean',
            'is_public' => 'boolean',
            'allow_public_email' => 'boolean',
        ];
    }

    // ── Relations ───────────────────────────────────────────────

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(AlumniPost::class);
    }

    public function user(): ?User
    {
        return $this->studentProfile?->user;
    }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    public function scopeVisibleInDirectory(Builder $query): Builder
    {
        return $query->where('is_visible_directory', true);
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('is_approved', true)
            ->where('is_visible_directory', true)
            ->where('is_public', true);
    }

    public function scopeShowcaseData(Builder $query): Builder
    {
        return $query
            ->select([
                'id',
                'nom',
                'prenom',
                'photo_path',
                'formation_id',
                'promotion_year',
                'date_diplome',
                'statut_actuel',
                'entreprise_actuelle',
                'poste_actuel',
                'current_position',
                'company',
                'bio',
            ])
            ->with([
                'formation:id,name',
                'studentProfile:id,user_id',
                'studentProfile.user:id,first_name,last_name',
                'posts' => fn (HasMany $posts): HasMany => $posts
                    ->select(['id', 'alumni_id', 'title', 'slug'])
                    ->published()
                    ->orderByDesc('published_at')
                    ->orderByDesc('id')
                    ->limit(3),
            ]);
    }

    // ── Accessors & Helpers ─────────────────────────────────────

    /**
     * Retourne l'année de promotion. Priorité : colonne promotion_year,
     * puis date_diplome, puis null.
     */
    public function getPromotionYearAttribute(mixed $value): ?int
    {
        if (! empty($value)) {
            return (int) $value;
        }

        return $this->date_diplome?->year;
    }

    public function getNomAttribute(?string $value): string
    {
        if (! empty($value)) {
            return $value;
        }

        return $this->studentProfile?->user?->last_name ?? '';
    }

    public function getPrenomAttribute(?string $value): string
    {
        if (! empty($value)) {
            return $value;
        }

        return $this->studentProfile?->user?->first_name ?? '';
    }

    public function getFullNameAttribute(): string
    {
        $prenom = $this->prenom;
        $nom = $this->nom;

        return trim("{$prenom} {$nom}") ?: 'Alumni IFAM';
    }

    public function getInitialsAttribute(): string
    {
        $p = mb_substr($this->prenom ?: 'A', 0, 1);
        $n = mb_substr($this->nom ?: 'L', 0, 1);

        return mb_strtoupper($p.$n);
    }

    public function getEmailAttribute(?string $value): ?string
    {
        if (! empty($value)) {
            return $value;
        }

        return $this->studentProfile?->user?->email;
    }

    public function getTelephoneAttribute(?string $value): ?string
    {
        if (! empty($value)) {
            return $value;
        }

        return $this->studentProfile?->user?->phone;
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->photo_path && Storage::disk('public')->exists($this->photo_path)) {
            return Storage::url($this->photo_path);
        }

        $userPhoto = $this->studentProfile?->user?->photo_path;
        if ($userPhoto && Storage::disk('public')->exists($userPhoto)) {
            return Storage::url($userPhoto);
        }

        return null;
    }

    /**
     * Retourne l'ensemble des formations validées associées à ce diplômé.
     */
    public function getCompletedFormationsAttribute(): Collection
    {
        if ($this->studentProfile) {
            $completedEnrollments = $this->studentProfile->enrollments()
                ->where('status', 'completed')
                ->with(['formation.parcours', 'academicSession'])
                ->get();

            if ($completedEnrollments->isNotEmpty()) {
                return $completedEnrollments->map(function ($enrollment) {
                    return [
                        'formation' => $enrollment->formation,
                        'parcours' => $enrollment->formation?->parcours,
                        'session' => $enrollment->academicSession?->name,
                        'year' => $enrollment->academicSession?->end_date?->format('Y') ?? $enrollment->created_at->format('Y'),
                    ];
                });
            }
        }

        if ($this->formation) {
            return collect([[
                'formation' => $this->formation,
                'parcours' => $this->formation->parcours,
                'session' => null,
                'year' => $this->date_diplome?->format('Y'),
            ]]);
        }

        return collect();
    }
}
