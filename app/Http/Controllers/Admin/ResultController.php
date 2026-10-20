<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Services\BulletinService;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function __construct(protected BulletinService $bulletinService) {}

    public function index(Request $request)
    {
        $sessions = AcademicSession::orderByDesc('start_date')->get();
        $selectedSessionId = $request->integer('session_id') ?: null;
        $selectedFormationId = $request->integer('formation_id') ?: null;

        $formations = collect();
        $results = collect();

        if ($selectedSessionId) {
            $formations = Formation::whereHas('enrollments', function ($query) use ($selectedSessionId) {
                $query->where('academic_session_id', $selectedSessionId);
            })->orderBy('name')->get();
        }

        if ($selectedSessionId && $selectedFormationId && $formations->contains('id', $selectedFormationId)) {
            $selectedFormation = Formation::with('modules')->findOrFail($selectedFormationId);

            $enrollments = Enrollment::where('academic_session_id', $selectedSessionId)
                ->where('formation_id', $selectedFormationId)
                ->where('status', 'active')
                ->with(['studentProfile.user'])
                ->orderBy('created_at')
                ->get();

            $results = $enrollments->map(function (Enrollment $enrollment) use ($selectedFormation, $selectedSessionId) {
                $student = $enrollment->studentProfile;
                $average = $this->bulletinService->moyenneGenerale($student, $selectedFormation, $selectedSessionId);

                return [
                    'student' => $student,
                    'average' => $average !== null ? round($average, 2) : null,
                    'status' => $average !== null && $average >= 10 ? 'Validé' : 'À suivre',
                ];
            });
        }

        return view('admin.results.index', compact(
            'sessions',
            'formations',
            'selectedSessionId',
            'selectedFormationId',
            'results'
        ));
    }

    public function show(Request $request, StudentProfile $student)
    {
        $selectedSessionId = $request->integer('session_id') ?: null;
        $selectedFormationId = $request->integer('formation_id') ?: null;

        if (! $selectedSessionId || ! $selectedFormationId) {
            abort(404, 'Session et formation requises pour consulter le détail des résultats.');
        }

        $formation = Formation::with('modules')->findOrFail($selectedFormationId);
        $modules = $formation->modules;

        $detail = $modules->map(function ($module) use ($student, $selectedSessionId) {
            $moyenneModule = $this->bulletinService->moyenneModule($student, $module, $selectedSessionId);
            $moyennePonderee = $this->bulletinService->moyennePonderee($student, $module, $selectedSessionId);

            return [
                'module' => $module,
                'moyenne_module' => $moyenneModule !== null ? round($moyenneModule, 2) : null,
                'moyenne_ponderee' => $moyennePonderee !== null ? round($moyennePonderee, 2) : null,
            ];
        })->filter(fn ($row) => $row['moyenne_module'] !== null);

        $average = $this->bulletinService->moyenneGenerale($student, $formation, $selectedSessionId);

        return view('admin.results.show', compact(
            'student',
            'formation',
            'selectedSessionId',
            'selectedFormationId',
            'detail',
            'average'
        ));
    }
}
