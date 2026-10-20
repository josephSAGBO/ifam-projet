<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Evaluation;
use App\Models\Formation;
use App\Models\Grade;
use App\Models\Module;
use App\Models\StudentProfile;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    /**
     * Barème par défaut — toutes les notes sont sur 20.
     */
    private const BAREME = 20;

    /**
     * Display a listing of evaluations to grade.
     */
    public function index(Request $request)
    {
        $sessions = AcademicSession::orderByDesc('start_date')->get();
        $selectedSessionId = $request->integer('session_id') ?: null;
        $selectedFormationId = $request->integer('formation_id') ?: null;
        $selectedModuleId = $request->integer('module_id') ?: null;
        $formations = collect();
        $modules = collect();
        $evaluations = collect();

        if ($selectedSessionId && $sessions->contains('id', $selectedSessionId)) {
            $formations = Formation::where(function ($query) use ($selectedSessionId) {
                $query->whereHas('evaluations', fn ($evaluationQuery) => $evaluationQuery->where('session_id', $selectedSessionId))
                    ->orWhereHas('courses', function ($courseQuery) use ($selectedSessionId) {
                        $courseQuery->where('academic_session_id', $selectedSessionId)
                            ->whereHas('evaluations', fn ($evaluationQuery) => $evaluationQuery
                                ->where('session_id', $selectedSessionId)
                                ->whereNull('formation_id'));
                    });
            })
                ->orderBy('name')
                ->get();
        } else {
            $selectedSessionId = null;
        }

        if ($selectedFormationId && $formations->contains('id', $selectedFormationId)) {
            $modules = Module::where(function ($query) use ($selectedSessionId, $selectedFormationId) {
                $query->whereHas('evaluations', function ($evaluationQuery) use ($selectedSessionId, $selectedFormationId) {
                    $evaluationQuery->where('session_id', $selectedSessionId)
                        ->where('formation_id', $selectedFormationId);
                })
                    ->orWhereHas('courses', function ($courseQuery) use ($selectedSessionId, $selectedFormationId) {
                        $courseQuery->where('formation_id', $selectedFormationId)
                            ->where('academic_session_id', $selectedSessionId)
                            ->whereHas('evaluations', fn ($evaluationQuery) => $evaluationQuery
                                ->where('session_id', $selectedSessionId)
                                ->whereNull('formation_id'));
                    });
            })
                ->orderBy('name')
                ->get();
        } else {
            $selectedFormationId = null;
        }

        if ($selectedModuleId && $modules->contains('id', $selectedModuleId)) {
            $evaluations = Evaluation::with(['module', 'formation', 'academicSession', 'course.formation', 'assignedTeacher.user'])
                ->withCount('grades')
                ->where('session_id', $selectedSessionId)
                ->where(function ($query) use ($selectedFormationId) {
                    $query->where('formation_id', $selectedFormationId)
                        ->orWhere(function ($query) use ($selectedFormationId) {
                            $query->whereNull('formation_id')
                                ->whereHas('course', fn ($courseQuery) => $courseQuery->where('formation_id', $selectedFormationId));
                        });
                })
                ->where('module_id', $selectedModuleId)
                ->orderBy('date')
                ->get();
        } else {
            $selectedModuleId = null;
        }

        return view('admin.grades.index', compact(
            'sessions',
            'formations',
            'modules',
            'evaluations',
            'selectedSessionId',
            'selectedFormationId',
            'selectedModuleId',
        ));
    }

    /**
     * Show the form for creating or editing grades for a specific evaluation.
     */
    public function show(Evaluation $grade)
    {
        $grade->load(['course.module', 'course.formation', 'module', 'formation', 'academicSession']);

        if ($grade->course) {
            $students = $grade->course->etudiantsConcernes();
        } elseif ($grade->formation_id && $grade->session_id) {
            $students = StudentProfile::whereHas('enrollments', function ($query) use ($grade) {
                $query->where('formation_id', $grade->formation_id)
                    ->where('academic_session_id', $grade->session_id)
                    ->where('status', 'active');
            })
                ->with('user')
                ->get()
                ->sortBy(fn (StudentProfile $student) => $student->user->last_name.' '.$student->user->first_name)
                ->values();
        } else {
            $students = collect();
        }

        $grades = Grade::where('evaluation_id', $grade->id)
            ->get()
            ->keyBy('student_profile_id');

        $bareme = self::BAREME;
        $evaluation = $grade; // alias pour la vue

        return view('admin.grades.show', compact('evaluation', 'students', 'grades', 'bareme'));
    }

    /**
     * The teacher is the only one allowed to record grades.
     * The admin can consult the evaluation and the current marks only.
     */
    public function store(Request $request, Evaluation $grade)
    {
        abort(403, 'La saisie des notes est réservée à l’enseignant assigné à l’évaluation.');
    }
}
