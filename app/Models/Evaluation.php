<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'created_by',
        'assigned_teacher_profile_id',
        'cours_id',
        'formation_id',
        'module_id',
        'session_id',
        'titre',
        'date',
        'statut',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the course that owns the evaluation.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'cours_id');
    }

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class);
    }

    public function assignedTeacher(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class, 'assigned_teacher_profile_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'session_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all grades for this evaluation.
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }
}
