<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_id',
        'student_profile_id',
        'score',
        'comments',
        'is_absent',
    ];

    protected $casts = [
        'score' => 'float',
        'is_absent' => 'boolean',
    ];

    /**
     * Get the evaluation associated with the grade.
     */
    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    /**
     * Get the student profile associated with the grade.
     */
    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class);
    }
}
