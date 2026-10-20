<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;

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
            ->orderByDesc('date')
            ->orderBy('heure_debut')
            ->get();

        $courseSessions = $courses->pluck('academicSession')
            ->filter()
            ->unique('id')
            ->sortByDesc('start_date')
            ->values();

        return view('teacher.courses.index', compact('courses', 'courseSessions'));
    }

    public function show(Course $course)
    {
        $teacherProfile = auth()->user()->teacherProfile;

        // Sécurité : vérifier que ce cours appartient bien à cet enseignant
        if ($course->enseignant_id !== $teacherProfile?->id && $course->teacher_profile_id !== $teacherProfile?->id) {
            abort(403, 'Accès non autorisé à ce cours.');
        }

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
