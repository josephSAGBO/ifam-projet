<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'first_name', 'last_name', 'gender', 'birth_date', 'phone', 'photo_path',
    'matricule', 'email', 'password', 'is_active', 'must_change_password', 'created_by',
    'activation_token', 'activation_expires_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'birth_date' => 'date',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'activation_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ─── Accessors ────────────────────────────────────────────────

    /**
     * Full name (prénom + nom).
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Initiales (pour les avatars).
     */
    public function getInitialsAttribute(): string
    {
        return mb_strtoupper(
            mb_substr($this->first_name, 0, 1).mb_substr($this->last_name, 0, 1)
        );
    }

    // ─── Relations ────────────────────────────────────────────────

    /**
     * Admin qui a créé cet utilisateur.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Profil étudiant (si rôle etudiant).
     */
    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    /**
     * Profil enseignant (si rôle enseignant).
     */
    public function teacherProfile(): HasOne
    {
        return $this->hasOne(TeacherProfile::class);
    }

    /**
     * Profil parent (si rôle parent).
     */
    public function parentProfile(): HasOne
    {
        return $this->hasOne(ParentProfile::class);
    }

    /**
     * Profil gestionnaire (si rôle gestionnaire_session).
     */
    public function sessionManagerProfile(): HasOne
    {
        return $this->hasOne(SessionManagerProfile::class);
    }

    /**
     * Profil alumni (si rôle alumni).
     */
    public function alumniProfile(): ?Alumni
    {
        return $this->studentProfile?->alumniProfile ?? Alumni::where('email', $this->email)->first();
    }

    /**
     * Étudiants liés à ce parent.
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_etudiant', 'parent_id', 'student_id')
            ->withPivot('relationship')
            ->withTimestamps();
    }

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(UserAssignment::class);
    }

    public function serviceProfiles(): BelongsToMany
    {
        return $this->belongsToMany(ServiceProfile::class, 'user_assignments')
            ->withPivot(['site_id', 'level', 'is_department_head', 'department_name'])
            ->withTimestamps();
    }

    public function hasServiceAssignment(string $serviceCode, ?string $level = null, ?int $siteId = null): bool
    {
        return $this->assignments()
            ->whereHas('serviceProfile', fn ($q) => $q->where('code', $serviceCode))
            ->when($level !== null, fn ($q) => $q->where('level', $level))
            ->when($siteId !== null, fn ($q) => $q->where(fn ($sq) => $sq->whereNull('site_id')->orWhere('site_id', $siteId)))
            ->exists();
    }

    public function isDepartmentHead(?string $departmentName = null): bool
    {
        return $this->assignments()
            ->where('is_department_head', true)
            ->when($departmentName !== null, fn ($q) => $q->where('department_name', $departmentName))
            ->exists();
    }

    public function isSiteManager(?int $siteId = null): bool
    {
        if (! $this->hasRole('responsable_site')) {
            return false;
        }

        if ($siteId === null) {
            return true;
        }

        return $this->sites()->where('sites.id', $siteId)->exists();
    }

    public function hasSiteAccess(Site|int $site): bool
    {
        $siteId = $site instanceof Site ? $site->id : $site;

        // Direction Générale and system administrators have global vision
        if ($this->hasAnyRole(['direction', 'administrateur'])) {
            return true;
        }

        // Responsable de site assigned to this site
        if ($this->isSiteManager($siteId)) {
            return true;
        }

        // User assigned to this site or assigned globally
        return $this->sites()->where('sites.id', $siteId)->exists()
            || $this->assignments()->where(fn ($q) => $q->whereNull('site_id')->orWhere('site_id', $siteId))->exists();
    }

    public function etudiants(): BelongsToMany
    {
        return $this->children();
    }

    /**
     * Parents liés à cet étudiant.
     */
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_etudiant', 'student_id', 'parent_id')
            ->withPivot('relationship')
            ->withTimestamps();
    }

    /**
     * Génère un matricule unique pour un étudiant.
     * Format : ETU-YYYY-XXXXXX (ex: ETU-2026-000001)
     */
    public static function generateMatricule(): string
    {
        $year = date('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;

        return 'ETU-'.$year.'-'.str_pad($count, 6, '0', STR_PAD_LEFT);
    }
}
