<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HasSiteFilter;
use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\BulletinPublication;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\StudentProfile;
use App\Services\BulletinPdf;
use App\Services\BulletinService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class BulletinController extends Controller
{
    use HasSiteFilter;

    public function __construct(
        protected BulletinService $bulletinService,
        protected NotificationService $notificationService
    ) {}

    /**
     * IDs des sites gérés par l'utilisateur courant.
     * null = accès global ; tableau = périmètre restreint.
     *
     * @return int[]|null
     */
    protected function managedSiteIds(): ?array
    {
        $user = auth()->user();

        if ($user && $user->hasAnyRole(['administrateur', 'direction', 'direction_daa'])) {
            return null; // accès global
        }

        return $user ? $user->sites()->pluck('sites.id')->all() : [];
    }

    public function index(Request $request)
    {
        ['sites' => $sites, 'selectedSiteId' => $selectedSiteId] = $this->siteFilter($request);
        $managed = $this->managedSiteIds();
        $sessionsQuery = AcademicSession::orderByDesc('start_date');
        if ($managed !== null) {
            $sessionsQuery->whereIn('site_id', $managed);
        }
        if ($selectedSiteId !== null) {
            $sessionsQuery->where('site_id', $selectedSiteId);
        }
        $sessions = $sessionsQuery->get();

        $selectedSessionId = $request->integer('session_id') ?: null;
        if ($selectedSessionId && ! $sessions->contains('id', $selectedSessionId)) {
            $selectedSessionId = null;
        }
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
            'students',
            'sites',
            'selectedSiteId'
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
        $managed = $this->managedSiteIds();
        if ($managed !== null && ! in_array($academicSession->site_id, $managed, true)) {
            abort(403, 'Accès refusé : ce bulletin appartient à un autre site.');
        }

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
        if (auth()->check() && auth()->user()->hasAnyRole([
            'direction_daf',
            'responsable_administratif_financier',
            'responsable_administratif',
            'secretaire_administratif',
            'charge_scolarite',
            'agent_administratif',
            'responsable_financier',
            'comptable',
            'caissier',
            'agent_recouvrement',
            'agent_financier',
        ]) && ! auth()->user()->hasAnyRole(['administrateur', 'direction'])) {
            abort(403, 'Accès interdit : les profils administratifs et financiers ne sont pas autorisés à modifier les résultats pédagogiques.');
        }

        $selectedSessionId = $request->integer('session_id') ?: null;
        $selectedFormationId = $request->integer('formation_id') ?: null;

        abort_unless($selectedSessionId && $selectedFormationId, 404, 'Session et formation requises pour publier le bulletin.');

        $academicSession = AcademicSession::findOrFail($selectedSessionId);
        $managed = $this->managedSiteIds();
        if ($managed !== null && ! in_array($academicSession->site_id, $managed, true)) {
            abort(403, 'Accès refusé : ce bulletin appartient à un autre site.');
        }

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

        $formation = Formation::findOrFail($selectedFormationId);
        $session = AcademicSession::findOrFail($selectedSessionId);

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

        // Notifier l'étudiant et ses parents
        $this->notificationService->bulletinPublie($student, $formation, $session);

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

        $enrollment = Enrollment::with('academicSession')
            ->where('student_profile_id', $student->id)
            ->where('formation_id', $selectedFormationId)
            ->where('academic_session_id', $selectedSessionId)
            ->firstOrFail();

        $managed = $this->managedSiteIds();
        if ($managed !== null) {
            $siteId = $enrollment->site_id ?? $enrollment->academicSession?->site_id;
            if ($siteId !== null && ! in_array($siteId, $managed, true)) {
                abort(403, 'Accès refusé : ce bulletin appartient à un autre site.');
            }
        }

        $pdf = $bulletinPdf->generate($enrollment, [
            'show_grid' => config('bulletins.show_grid', false),
        ]);

        $fileName = 'bulletin_'.str($student->user?->full_name ?? 'etudiant')->slug().'.pdf';

        return $pdf->stream($fileName);
    }
}
