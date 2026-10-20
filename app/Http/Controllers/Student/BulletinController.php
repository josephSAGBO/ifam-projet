<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\BulletinPublication;
use App\Models\Enrollment;
use App\Services\BulletinPdf;
use App\Services\BulletinService;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;

class BulletinController extends Controller
{
    public function index(): View
    {
        $student = auth()->user()->studentProfile;
        abort_unless($student, 403, 'Profil étudiant introuvable.');

        $publications = BulletinPublication::query()
            ->where('student_profile_id', $student->id)
            ->where('is_published', true)
            ->with(['formation', 'academicSession'])
            ->orderByDesc('published_at')
            ->get();

        return view('student.bulletins.index', compact('publications'));
    }

    public function show(BulletinPublication $publication, BulletinService $bulletinService): View
    {
        $student = auth()->user()->studentProfile;
        abort_unless($student, 403, 'Profil étudiant introuvable.');

        $publication = BulletinPublication::query()
            ->whereKey($publication->id)
            ->where('student_profile_id', $student->id)
            ->where('is_published', true)
            ->with(['formation.modules', 'academicSession', 'studentProfile.user'])
            ->firstOrFail();

        $bulletin = $bulletinService->bulletin(
            $publication->studentProfile,
            $publication->formation,
            $publication->academic_session_id,
        );

        return view('bulletins.show', [
            'bulletin' => $bulletin,
            'publication' => $publication,
            'viewerRole' => 'student',
        ]);
    }

    public function pdf(BulletinPublication $publication, BulletinPdf $bulletinPdf): Response
    {
        $student = auth()->user()->studentProfile;
        abort_unless($student, 403, 'Profil étudiant introuvable.');

        $publication = BulletinPublication::query()
            ->whereKey($publication->id)
            ->where('student_profile_id', $student->id)
            ->where('is_published', true)
            ->with(['studentProfile.user', 'formation', 'academicSession'])
            ->firstOrFail();

        $enrollment = Enrollment::where('student_profile_id', $student->id)
            ->where('formation_id', $publication->formation_id)
            ->where('academic_session_id', $publication->academic_session_id)
            ->firstOrFail();

        $pdf = $bulletinPdf->generate($enrollment, [
            'date_edition' => $publication->published_at ? $publication->published_at->format('d/m/Y') : now()->format('d/m/Y'),
        ]);

        $fileName = 'bulletin_'.str($student->user?->full_name ?? 'etudiant')->slug().'.pdf';

        return $pdf->stream($fileName);
    }
}
