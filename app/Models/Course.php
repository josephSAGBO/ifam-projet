<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'formation_id',
        'academic_session_id',
        'module_id',
        'salle_id',
        'enseignant_id',
        'teacher_profile_id',
        'name',
        'statut',
        'color',
        'date',
        'heure_debut',
        'heure_fin',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class);
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function session(): BelongsTo
    {
        return $this->academicSession();
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function salle(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'salle_id');
    }

    public function room(): BelongsTo
    {
        return $this->salle();
    }

    public function enseignant(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class, 'enseignant_id');
    }

    public function teacherProfile(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class, 'teacher_profile_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DocumentCours::class, 'cours_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'cours_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'course_id');
    }

    public function attendanceSheets(): HasMany
    {
        return $this->hasMany(AttendanceSheet::class);
    }

    /**
     * @return Collection<int, StudentProfile>
     */
    public function etudiantsConcernes(bool $includeCompletedEnrollments = false): Collection
    {
        $formationId = $this->formation_id;
        $sessionId = $this->academic_session_id;

        if (! $formationId || ! $sessionId) {
            return StudentProfile::whereRaw('1 = 0')->get();
        }

        $enrollmentStatuses = $includeCompletedEnrollments
            ? Enrollment::STUDENT_ACCESSIBLE_STATUSES
            : ['active'];

        return StudentProfile::whereHas('enrollments', function ($query) use ($formationId, $sessionId, $enrollmentStatuses) {
            $query->where('formation_id', $formationId)
                ->where('academic_session_id', $sessionId)
                ->whereIn('status', $enrollmentStatuses);
        })
            ->with('user')
            ->get()
            ->sortBy(function ($sp) {
                return ($sp->user->last_name ?? '').' '.($sp->user->first_name ?? '');
            })
            ->values();
    }

    /**
     * Obtenir le nom complet du cours à afficher.
     */
    public function getDisplayNameAttribute(): string
    {
        if (! empty($this->name)) {
            return $this->name;
        }

        if ($this->module) {
            return $this->module->name;
        }

        return 'Cours #'.$this->id;
    }
}
