<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    public function index()
    {
        $teacherProfile = auth()->user()->teacherProfile;
        if (! $teacherProfile) {
            abort(403, 'Profil enseignant introuvable.');
        }

        $courses = Course::with(['module', 'academicSession', 'formation', 'salle', 'documents'])
            ->where(function ($q) use ($teacherProfile) {
                $q->where('enseignant_id', $teacherProfile->id)
                    ->orWhere('teacher_profile_id', $teacherProfile->id);
            })
            ->notHiddenFromTeacher($teacherProfile->id)
            ->orderByDesc('date')
            ->orderBy('heure_debut')
            ->get();

        $courseSessions = $courses->pluck('academicSession')
            ->filter()
            ->unique('id')
            ->sortByDesc('start_date')
            ->values();
        $activeCourses = $courses
            ->reject(fn (Course $course) => $course->academicSession?->status === 'terminee')
            ->values();
        $archivedCourses = $courses
            ->filter(fn (Course $course) => $course->academicSession?->status === 'terminee')
            ->values();

        return view('teacher.courses.index', compact('activeCourses', 'archivedCourses', 'courseSessions'));
    }

    public function hideArchived(Course $course): RedirectResponse
    {
        $teacherProfile = auth()->user()->teacherProfile;

        if (! $teacherProfile || ($course->enseignant_id !== $teacherProfile->id && $course->teacher_profile_id !== $teacherProfile->id)) {
            abort(403, 'Accès non autorisé à ce cours.');
        }

        $course->loadMissing('academicSession');
        abort_unless($course->academicSession?->status === 'terminee', 403, 'Seuls les cours d’une rentrée terminée peuvent être retirés.');

        DB::table('teacher_hidden_courses')->insertOrIgnore([
            'teacher_profile_id' => $teacherProfile->id,
            'course_id' => $course->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('teacher.courses.index')
            ->with('success', 'Le cours a été retiré de votre historique. Il reste disponible dans les autres espaces.');
    }

    public function show(Course $course)
    {
        $teacherProfile = auth()->user()->teacherProfile;

        // Sécurité : vérifier que ce cours appartient bien à cet enseignant
        if ($course->enseignant_id !== $teacherProfile?->id && $course->teacher_profile_id !== $teacherProfile?->id) {
            abort(403, 'Accès non autorisé à ce cours.');
        }
        abort_if(
            $teacherProfile && ! Course::notHiddenFromTeacher($teacherProfile->id)->whereKey($course->id)->exists(),
            404
        );

        $course->load(['module', 'academicSession', 'formation', 'salle', 'documents.uploader']);

        $enrolledStudents = $course->etudiantsConcernes(includeCompletedEnrollments: true);
        $hasEnrollmentsInOtherSessions = $enrolledStudents->isEmpty()
            && Enrollment::where('formation_id', $course->formation_id)
                ->where('academic_session_id', '!=', $course->academic_session_id)
                ->whereIn('status', Enrollment::STUDENT_ACCESSIBLE_STATUSES)
                ->exists();

        return view('teacher.courses.show', compact('course', 'enrolledStudents', 'hasEnrollmentsInOtherSessions'));
    }

    public function uploadDocument(Request $request, Course $course)
    {
        $teacherProfile = auth()->user()->teacherProfile;

        if (! $teacherProfile || ($course->enseignant_id !== $teacherProfile->id && $course->teacher_profile_id !== $teacherProfile->id)) {
            abort(403, 'Accès non autorisé à ce cours.');
        }
        abort_unless(Course::notHiddenFromTeacher($teacherProfile->id)->whereKey($course->id)->exists(), 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document' => ['required', 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,png,zip', 'max:20480'],
        ]);

        $file = $validated['document'];
        $path = $file->store('courses/documents', 'public');

        $course->documents()->create([
            'title' => $validated['title'],
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        return redirect()->route('teacher.courses.show', $course)
            ->with('success', 'Document ajouté au cours avec succès.');
    }
}
