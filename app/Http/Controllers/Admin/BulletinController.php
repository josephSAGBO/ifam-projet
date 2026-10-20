<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\BulletinPublication;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\StudentProfile;
use App\Services\BulletinPdf;
use App\Services\BulletinService;
use Illuminate\Http\Request;

class BulletinController extends Controller
{
    public function __construct(protected BulletinService $bulletinService) {}

    public function index(Request $request)
    {
        $sessions = AcademicSession::orderByDesc('start_date')->get();
        $selectedSessionId = $request->integer('session_id') ?: null;
        $selectedFormationId = $request->integer('formation_id') ?: null;
        $formations = collect();
        $students = collect();

        if ($selectedSessionId) {
            $formations = Formation::whereHas('enrollments', function ($query) use ($selectedSessionId) {
                $query->where('academic_session_id', $selectedSessionId);
            })->orderBy('name')->get();
        }

        if ($selectedSessionId && $selectedFormationId && $formations->contains('id', $selectedFormationId)) {
            $students = Enrollment::where('academic_session_id', $selectedSessionId)
                ->where('formation_id', $selectedFormationId)
                ->where('status', 'active')
                ->with(['studentProfile.user'])
                ->get()
                ->map(function ($enrollment) use ($selectedSessionId, $selectedFormationId) {
                    $student = $enrollment->studentProfile;
                    $formation = Formation::findOrFail($selectedFormationId);
                    $average = $this->bulletinService->moyenneGenerale($student, $formation, $selectedSessionId);

                    return [
                        'student' => $student,
                        'average' => $average !== null ? round($average, 2) : null,
                    ];
                });
        }

        return view('admin.bulletins.index', compact(
            'sessions',
            'formations',
            'selectedSessionId',
            'selectedFormationId',
            'students'
        ));
    }

    public function show(StudentProfile $student, Request $request)
    {
        $selectedSessionId = $request->integer('session_id') ?: null;
        $selectedFormationId = $request->integer('formation_id') ?: null;

        if (! $selectedSessionId || ! $selectedFormationId) {
            abort(404, 'Session et formation requises pour générer le bulletin.');
        }

        $academicSession = AcademicSession::findOrFail($selectedSessionId);
        $formation = Formation::with('modules')->findOrFail($selectedFormationId);
        $bulletin = $this->bulletinService->bulletin($student, $formation, $selectedSessionId);
        $publication = BulletinPublication::where('student_profile_id', $student->id)
            ->where('formation_id', $formation->id)
            ->where('academic_session_id', $selectedSessionId)
            ->first();

        return view('admin.bulletins.show', compact(
            'student',
            'formation',
            'academicSession',
            'selectedSessionId',
            'selectedFormationId',
            'bulletin',
            'publication',
        ));
    }

    public function publish(StudentProfile $student, Request $request)
    {
        $selectedSessionId = $request->integer('session_id') ?: null;
        $selectedFormationId = $request->integer('formation_id') ?: null;

        abort_unless($selectedSessionId && $selectedFormationId, 404, 'Session et formation requises pour publier le bulletin.');

        $publication = BulletinPublication::where('student_profile_id', $student->id)
            ->where('formation_id', $selectedFormationId)
            ->where('academic_session_id', $selectedSessionId)
            ->first();

        if ($publication && $publication->is_published) {
            $publication->update([
                'is_published' => false,
                'published_at' => null,
                'published_by' => null,
            ]);

            return redirect()->route('admin.bulletins.show', [
                'student' => $student->id,
                'session_id' => $selectedSessionId,
                'formation_id' => $selectedFormationId,
            ])->with('success', 'Le bulletin a été dépublié avec succès.');
        }

        BulletinPublication::updateOrCreate(
            [
                'student_profile_id' => $student->id,
                'formation_id' => $selectedFormationId,
                'academic_session_id' => $selectedSessionId,
            ],
            [
                'is_published' => true,
                'published_at' => now(),
                'published_by' => auth()->id(),
            ]
        );

        return redirect()->route('admin.bulletins.show', [
            'student' => $student->id,
            'session_id' => $selectedSessionId,
            'formation_id' => $selectedFormationId,
        ])->with('success', 'Le bulletin a été publié avec succès.');
    }

    public function pdf(StudentProfile $student, Request $request, BulletinPdf $bulletinPdf)
    {
        $selectedSessionId = $request->integer('session_id') ?: null;
        $selectedFormationId = $request->integer('formation_id') ?: null;

        abort_unless($selectedSessionId && $selectedFormationId, 404, 'Session et formation requises pour télécharger le bulletin.');

        $enrollment = Enrollment::where('student_profile_id', $student->id)
            ->where('formation_id', $selectedFormationId)
            ->where('academic_session_id', $selectedSessionId)
            ->firstOrFail();

        $pdf = $bulletinPdf->generate($enrollment, [
            'show_grid' => config('bulletins.show_grid', false),
        ]);

        $fileName = 'bulletin_'.str($student->user?->full_name ?? 'etudiant')->slug().'.pdf';

        return $pdf->stream($fileName);
    }
}
