<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\DocumentCours;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Storage;

class ScheduleController extends Controller
{
    public function index()
    {
        $student = auth()->user()->studentProfile;

        if (! $student) {
            return view('student.schedule.index', [
                'enrollments' => collect(),
                'courses' => collect(),
            ]);
        }

        $enrollments = Enrollment::where('student_profile_id', $student->id)
            ->whereIn('status', Enrollment::STUDENT_ACCESSIBLE_STATUSES)
            ->with(['formation', 'academicSession'])
            ->latest('enrollment_date')
            ->get();

        $courses = collect();
        $hasCoursesInEnrolledFormations = false;
        if ($enrollments->isNotEmpty()) {
            $hasCoursesInEnrolledFormations = Course::whereIn(
                'formation_id',
                $enrollments->pluck('formation_id')->unique()
            )->exists();

            $courses = Course::where(function ($query) use ($enrollments) {
                foreach ($enrollments as $enrollment) {
                    $query->orWhere(function ($pairQuery) use ($enrollment) {
                        $pairQuery->where('formation_id', $enrollment->formation_id)
                            ->where('academic_session_id', $enrollment->academic_session_id);
                    });
                }
            })
                ->with([
                    'module',
                    'teacherProfile.user',
                    'enseignant.user',
                    'formation',
                    'academicSession',
                    'salle',
                    'documents' => fn ($query) => $query->latest(),
                ])
                ->orderBy('date')
                ->orderBy('heure_debut')
                ->get();
        }

        return view('student.schedule.index', compact('enrollments', 'courses', 'hasCoursesInEnrolledFormations'));
    }

    public function downloadDocument(DocumentCours $document)
    {
        $student = auth()->user()->studentProfile;
        $course = $document->course;

        if (! $student || ! $course) {
            abort(404);
        }

        $isEnrolled = Enrollment::where('student_profile_id', $student->id)
            ->where('formation_id', $course->formation_id)
            ->where('academic_session_id', $course->academic_session_id)
            ->whereIn('status', Enrollment::STUDENT_ACCESSIBLE_STATUSES)
            ->exists();

        abort_unless($isEnrolled, 404);
        abort_unless(Storage::disk('public')->exists($document->file_path), 404);

        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }
}
