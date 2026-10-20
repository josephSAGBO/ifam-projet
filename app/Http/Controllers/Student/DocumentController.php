<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentCours;
use App\Models\Enrollment;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function index()
    {
        $student = auth()->user()->studentProfile;

        $documents = collect();
        $courseDocuments = collect();

        if ($student) {
            $documents = Document::where('documentable_type', StudentProfile::class)
                ->where('documentable_id', $student->id)
                ->orderByDesc('created_at')
                ->get();

            $courseDocuments = DocumentCours::whereHas('course', function ($courseQuery) use ($student) {
                $courseQuery->whereHas('formation.enrollments', function ($enrollmentQuery) use ($student) {
                    $enrollmentQuery
                        ->where('student_profile_id', $student->id)
                        ->whereIn('enrollments.status', Enrollment::STUDENT_ACCESSIBLE_STATUSES)
                        ->whereColumn('enrollments.academic_session_id', 'courses.academic_session_id');
                });
            })
                ->with(['course.module', 'course.formation', 'course.academicSession'])
                ->latest()
                ->get();
        }

        return view('student.documents.index', compact('student', 'documents', 'courseDocuments'));
    }

    public function downloadCourseDocument(DocumentCours $courseDocument): StreamedResponse
    {
        $student = auth()->user()->studentProfile;
        $course = $courseDocument->course;

        if (! $student || ! $course) {
            abort(404);
        }

        $isEnrolled = Enrollment::where('student_profile_id', $student->id)
            ->where('formation_id', $course->formation_id)
            ->where('academic_session_id', $course->academic_session_id)
            ->whereIn('status', Enrollment::STUDENT_ACCESSIBLE_STATUSES)
            ->exists();

        abort_unless($isEnrolled, 403);

        if (! Storage::disk('public')->exists($courseDocument->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download($courseDocument->file_path, $courseDocument->original_name);
    }

    public function store(Request $request)
    {
        $student = auth()->user()->studentProfile;
        if (! $student) {
            return back()->with('error', 'Profil étudiant introuvable.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:identity,diploma,transcript,enrollment,medical,contract,invoice,other',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'file.max' => 'Le fichier ne doit pas dépasser 10 Mo.',
            'file.mimes' => 'Seuls les formats PDF, JPG et PNG sont acceptés.',
        ]);

        $file = $request->file('file');
        $path = $file->store('documents/students/'.$student->id, 'public');

        Document::create([
            'title' => $request->title,
            'type' => $request->type,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'is_verified' => false,
            'uploaded_by' => auth()->id(),
            'documentable_id' => $student->id,
            'documentable_type' => StudentProfile::class,
        ]);

        return back()->with('success', 'Document soumis avec succès. Il sera vérifié par l\'administration.');
    }

    public function destroy(Document $document)
    {
        $student = auth()->user()->studentProfile;

        // Sécurité : seul le propriétaire peut supprimer ses documents non vérifiés
        if (
            ! $student
            || $document->documentable_type !== StudentProfile::class
            || $document->documentable_id !== $student->id
        ) {
            abort(403);
        }

        if ($document->is_verified) {
            return back()->with('error', 'Un document vérifié ne peut pas être supprimé. Contactez l\'administration.');
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Document supprimé.');
    }
}
