<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
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

    /**
     * Profils de service accessibles à l'utilisateur (par assignments ou par rôles).
     *
     * @return Collection<int, ServiceProfile>
     */
    public function accessibleServiceProfiles(): Collection
    {
        // 1. Profils issus des assignations explicites
        $assignedIds = $this->assignments()->pluck('service_profile_id')->all();

        // 2. Profils déduits des rôles détenus
        $roleCodes = [];

        if ($this->hasAnyRole(['administrateur', 'direction', 'responsable_site'])) {
            return ServiceProfile::active()->get();
        }

        if ($this->hasRole('direction_daf')) {
            $roleCodes = array_merge($roleCodes, ['administratif', 'financier', 'gestion_stocks', 'maintenance_logistique']);
        }

        if ($this->hasRole('responsable_administratif_financier')) {
            $roleCodes = array_merge($roleCodes, ['administratif', 'financier']);
        }

        if ($this->hasAnyRole(['responsable_administratif', 'secretaire_administratif', 'charge_scolarite', 'agent_administratif'])) {
            $roleCodes[] = 'administratif';
        }

        if ($this->hasAnyRole(['responsable_financier', 'comptable', 'caissier', 'agent_recouvrement'])) {
            $roleCodes[] = 'financier';
        }

        if ($this->hasRole('direction_daa')) {
            $roleCodes = array_merge($roleCodes, ['pedagogie_etudes', 'insertion_professionnelle']);
        }

        if ($this->hasRole('responsable_daa')) {
            $roleCodes = array_merge($roleCodes, ['pedagogie_etudes', 'insertion_professionnelle']);
        }

        if ($this->hasRole('responsable_etudes')) {
            $roleCodes[] = 'pedagogie_etudes';
        }

        if ($this->hasRole('responsable_insertion')) {
            $roleCodes[] = 'insertion_professionnelle';
        }

        if ($this->hasRole('responsable_commercial')) {
            $roleCodes[] = 'commercial';
        }

        $query = ServiceProfile::active();
        if (empty($assignedIds) && empty($roleCodes)) {
            return $query->whereRaw('1 = 0')->get();
        }

        return $query->where(function ($q) use ($assignedIds, $roleCodes) {
            if (! empty($assignedIds)) {
                $q->whereIn('id', $assignedIds);
            }
            if (! empty($roleCodes)) {
                $q->orWhereIn('code', array_unique($roleCodes));
            }
        })->get();
    }

    /**
     * Vérifie si l'utilisateur a le niveau responsable pour un service donné.
     */
    public function isServiceResponsable(ServiceProfile|int|string $service): bool
    {
        if ($this->hasAnyRole(['administrateur', 'direction', 'responsable_site'])) {
            return true;
        }

        $serviceModel = is_numeric($service)
            ? ServiceProfile::find($service)
            : ($service instanceof ServiceProfile ? $service : ServiceProfile::where('code', $service)->first());

        if (! $serviceModel) {
            return false;
        }

        // Vérifier via user_assignments
        $hasAssignment = $this->assignments()
            ->where('service_profile_id', $serviceModel->id)
            ->where('level', 'responsable')
            ->exists();

        if ($hasAssignment) {
            return true;
        }

        // Vérifier via les rôles métiers
        return match ($serviceModel->code) {
            'administratif' => $this->hasAnyRole(['responsable_administratif', 'responsable_administratif_financier', 'direction_daf']),
            'financier' => $this->hasAnyRole(['responsable_financier', 'responsable_administratif_financier', 'direction_daf']),
            'pedagogie_etudes' => $this->hasAnyRole(['responsable_etudes', 'responsable_daa', 'direction_daa']),
            'insertion_professionnelle' => $this->hasAnyRole(['responsable_insertion', 'responsable_daa', 'direction_daa']),
            'commercial' => $this->hasRole('responsable_commercial'),
            'gestion_stocks', 'maintenance_logistique' => $this->hasAnyRole(['direction_daf']),
            default => false,
        };
    }

    /**
     * L'utilisateur peut-il accéder au module des rapports de service ?
     */
    public function canAccessServiceReports(): bool
    {
        return $this->accessibleServiceProfiles()->isNotEmpty();
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

        // Direction Générale, DG DAF, DG DAA and system administrators have global vision
        if ($this->hasAnyRole(['direction', 'administrateur', 'direction_daf', 'direction_daa'])) {
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

    /**
     * @return array<int>
     */
    public function accessibleSiteIds(): array
    {
        if ($this->hasAnyRole(['direction', 'administrateur', 'direction_daf', 'direction_daa'])) {
            return Site::pluck('id')->all();
        }

        return $this->sites()->pluck('sites.id')->all();
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
