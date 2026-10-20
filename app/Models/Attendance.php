<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'formation_id',
        'student_profile_id',
        'assigned_teacher_profile_id',
        'date',
        'status',
        'remarks',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the course.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    public function assignedTeacher()
    {
        return $this->belongsTo(TeacherProfile::class, 'assigned_teacher_profile_id');
    }

    /**
     * Get the student profile.
     */
    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class);
    }
}
