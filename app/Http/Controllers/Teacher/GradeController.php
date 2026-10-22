<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Evaluation;
use App\Models\Grade;
use App\Models\StudentProfile;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /** Note maximale fixe pour tous les contrôles continus. */
    private const NOTE_MAX = 20;

    public function index(Request $request)
    {
        $teacherProfile = auth()->user()->teacherProfile;
        if (! $teacherProfile) {
            abort(403, 'Profil enseignant introuvable.');
        }

        $history = $request->boolean('history');
        $activeSession = AcademicSession::where('is_current', true)->first();

        $coursesQuery = Course::where(function ($q) use ($teacherProfile) {
            $q->where('enseignant_id', $teacherProfile->id)
                ->orWhere('teacher_profile_id', $teacherProfile->id);
        })->notHiddenFromTeacher($teacherProfile->id)
            ->with('module');
        if (! $history) {
            $coursesQuery->where('statut', 'en_cours');
            $coursesQuery->whereHas('academicSession', fn ($query) => $query->where('status', '!=', 'terminee'));
        } else {
            $coursesQuery->where(function ($query) {
                $query->where('statut', 'termine')
                    ->orWhereHas('evaluations', fn ($evaluations) => $evaluations->where('statut', 'cloturee'))
                    ->orWhereHas('academicSession', fn ($session) => $session->where('status', 'terminee'));
            });
        }
        $courses = $coursesQuery->get();

        $assignedEvaluationsQuery = Evaluation::where('type', 'devoir')
            ->whereNull('cours_id')
            ->where('assigned_teacher_profile_id', $teacherProfile->id)
            ->with(['module', 'formation', 'academicSession', 'grades'])
            ->orderBy('date');
        if (! $history) {
            $assignedEvaluationsQuery->where('statut', 'ouverte');
        } else {
            $assignedEvaluationsQuery->where('statut', 'cloturee');
        }
        $assignedEvaluations = $assignedEvaluationsQuery->get();

        $course = null;
        $evaluations = collect();
        $students = collect();

        $courseId = $request->get('course_id');
        if ($courseId) {
            $courseQuery = Course::where(function ($q) use ($teacherProfile) {
                $q->where('enseignant_id', $teacherProfile->id)
                    ->orWhere('teacher_profile_id', $teacherProfile->id);
            })->notHiddenFromTeacher($teacherProfile->id)
                ->where('id', $courseId)
                ->with(['module', 'formation']);
            if (! $history) {
                $courseQuery->where('statut', 'en_cours');
                $courseQuery->whereHas('academicSession', fn ($query) => $query->where('status', '!=', 'terminee'));
            } else {
                $courseQuery->where(function ($query) {
                    $query->where('statut', 'termine')
                        ->orWhereHas('evaluations', fn ($evaluations) => $evaluations->where('statut', 'cloturee'))
                        ->orWhereHas('academicSession', fn ($session) => $session->where('status', 'terminee'));
                });
            }
            $course = $courseQuery->firstOrFail();

            $evaluationsQuery = Evaluation::where('cours_id', $courseId)
                ->with(['grades.studentProfile.user'])
                ->orderBy('date');
            if (! $history) {
                $evaluationsQuery->where('statut', 'ouverte');
            } elseif ($course->statut !== 'termine') {
                $evaluationsQuery->where('statut', 'cloturee');
            }
            $evaluations = $evaluationsQuery->get();

            $students = $course->etudiantsConcernes(includeCompletedEnrollments: $history);
        }

        return view('teacher.grades.index', compact(
            'courses', 'course', 'activeSession', 'evaluations', 'students', 'assignedEvaluations', 'history'
        ));
    }

    /**
     * Créer un contrôle continu (CC) pour un cours de l'enseignant connecté.
     */
    public function createEvaluation(Request $request)
    {
        $teacherProfile = auth()->user()->teacherProfile;
        if (! $teacherProfile) {
            abort(403, 'Profil enseignant introuvable.');
        }

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'titre' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|in:controle_continu,interrogation',
        ]);

        // Vérifier que ce cours appartient à cet enseignant
        $course = Course::where(function ($q) use ($teacherProfile) {
            $q->where('enseignant_id', $teacherProfile->id)
                ->orWhere('teacher_profile_id', $teacherProfile->id);
        })->notHiddenFromTeacher($teacherProfile->id)->findOrFail($validated['course_id']);
        $course->loadMissing('academicSession');
        abort_unless($course->statut === 'en_cours' && $course->academicSession?->status !== 'terminee', 403, 'Ce cours est terminé.');

        Evaluation::create([
            'cours_id' => $course->id,
            'formation_id' => $course->formation_id,
            'module_id' => $course->module_id,
            'session_id' => $course->academic_session_id,
            'created_by' => auth()->id(),
            'titre' => $validated['titre'],
            'date' => $validated['date'],
            'type' => $validated['type'],
            'statut' => 'ouverte',
        ]);

        return redirect()->route('teacher.grades.index', ['course_id' => $course->id])
            ->with('success', 'Contrôle continu créé avec succès !');
    }

    /**
     * Enregistrer les notes pour une évaluation.
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'evaluation_id' => 'required|exists:evaluations,id',
            'grades' => 'required|array',
            'grades.*.score' => 'nullable|numeric|min:0|max:20',
            'grades.*.is_absent' => 'nullable|boolean',
            'grades.*.comments' => 'nullable|string|max:255',
        ]);

        $teacherProfile = auth()->user()->teacherProfile;
        if (! $teacherProfile) {
            abort(403, 'Profil enseignant introuvable.');
        }

        $course = Course::where(function ($q) use ($teacherProfile) {
            $q->where('enseignant_id', $teacherProfile->id)
                ->orWhere('teacher_profile_id', $teacherProfile->id);
        })->notHiddenFromTeacher($teacherProfile->id)->findOrFail($request->course_id);
        $course->loadMissing('academicSession');
        abort_unless($course->statut === 'en_cours' && $course->academicSession?->status !== 'terminee', 403, 'Ce cours est terminé.');

        $evaluation = Evaluation::findOrFail($request->evaluation_id);
        abort_unless($evaluation->statut === 'ouverte', 403, 'Cette évaluation est clôturée ou indisponible.');

        if ($evaluation->type === 'devoir') {
            abort_unless(
                (int) $evaluation->assigned_teacher_profile_id === (int) $teacherProfile->id,
                403,
                'Ce devoir ne vous est pas assigné.'
            );

            if ($evaluation->cours_id && (int) $evaluation->cours_id !== (int) $course->id) {
                abort(403, 'Cette évaluation ne correspond pas à ce cours.');
            }
        } else {
            abort_unless((int) $evaluation->cours_id === (int) $course->id, 403, 'Cette évaluation ne correspond pas à ce cours.');
        }

        foreach ($request->grades as $studentProfileId => $data) {
            $grade = Grade::updateOrCreate(
                [
                    'evaluation_id' => $request->evaluation_id,
                    'student_profile_id' => $studentProfileId,
                ],
                [
                    'score' => ($data['score'] ?? '') !== '' ? $data['score'] : null,
                    'is_absent' => isset($data['is_absent']),
                    'comments' => $data['comments'] ?? null,
                ]
            );

            // Notifier l'étudiant et ses parents si une note réelle est saisie
            if ($grade->score !== null) {
                $grade->load(['evaluation', 'studentProfile.user']);
                $this->notificationService->noteSaisie($grade);
            }
        }

        return redirect()->route('teacher.grades.index', ['course_id' => $request->course_id])
            ->with('success', 'Notes enregistrées avec succès !');
    }

    /**
     * Mettre à jour une note individuelle.
     */
    public function update(Request $request, Grade $grade)
    {
        $teacherProfile = auth()->user()->teacherProfile;
        if (! $teacherProfile) {
            abort(403, 'Profil enseignant introuvable.');
        }

        $evaluation = $grade->evaluation;
        abort_unless($evaluation && $evaluation->statut === 'ouverte', 403, 'Cette évaluation est clôturée ou indisponible.');

        $course = $evaluation->course;
        if ($evaluation->type === 'devoir') {
            abort_unless(
                (int) $evaluation->assigned_teacher_profile_id === (int) $teacherProfile->id,
                403,
                'Ce devoir ne vous est pas assigné.'
            );
        } else {
            abort_unless(
                $course && (
                    (int) $course->enseignant_id === (int) $teacherProfile->id
                    || (int) $course->teacher_profile_id === (int) $teacherProfile->id
                ) && Course::notHiddenFromTeacher($teacherProfile->id)->whereKey($course->id)->exists(),
                403,
                'Cette note ne vous appartient pas.'
            );
        }

        if ($course) {
            $course->loadMissing('academicSession');
            abort_unless($course->statut === 'en_cours' && $course->academicSession?->status !== 'terminee', 403, 'Ce cours est terminé.');
        }

        $validated = $request->validate([
            'score' => 'nullable|numeric|min:0|max:20',
            'is_absent' => 'nullable|boolean',
            'comments' => 'nullable|string|max:255',
        ]);

        $grade->update([
            'score' => $validated['score'] ?? null,
            'is_absent' => $request->boolean('is_absent'),
            'comments' => $validated['comments'] ?? null,
        ]);

        // Notifier l'étudiant et ses parents si une note réelle est saisie
        if ($grade->score !== null) {
            $grade->load(['evaluation', 'studentProfile.user']);
            $this->notificationService->noteSaisie($grade);
        }

        return redirect()->back()->with('success', 'Note mise à jour.');
    }

    public function destroy(Grade $grade): RedirectResponse
    {
        $teacherProfile = auth()->user()->teacherProfile;
        if (! $teacherProfile) {
            abort(403, 'Profil enseignant introuvable.');
        }

        $evaluation = $grade->evaluation;
        abort_unless(
            $evaluation && in_array($evaluation->type, ['controle_continu', 'interrogation'], true),
            403,
            'Seules les notes des contrôles continus peuvent être supprimées.'
        );

        $course = $evaluation->course;
        abort_unless(
            $course && (
                (int) $course->enseignant_id === (int) $teacherProfile->id
                || (int) $course->teacher_profile_id === (int) $teacherProfile->id
            ) && Course::notHiddenFromTeacher($teacherProfile->id)->whereKey($course->id)->exists(),
            403,
            'Cette note ne vous appartient pas.'
        );
        abort_unless($course->statut === 'en_cours', 403, 'Ce cours est terminé.');
        $course->loadMissing('academicSession');
        abort_unless($course->academicSession?->status !== 'terminee', 403, 'Ce cours est terminé.');
        abort_unless($evaluation->statut === 'ouverte', 403, 'Cette évaluation est clôturée.');

        $grade->delete();

        return redirect()->route('teacher.grades.index', ['course_id' => $course->id])
            ->with('success', 'Note supprimée avec succès.');
    }

    public function showAssigned(Evaluation $evaluation)
    {
        $teacherProfile = auth()->user()->teacherProfile;
        if (! $teacherProfile || $evaluation->type !== 'devoir' || $evaluation->assigned_teacher_profile_id !== $teacherProfile->id) {
            abort(404);
        }

        $evaluation->load(['module', 'formation', 'academicSession', 'course', 'grades.studentProfile.user']);
        abort_if(
            $evaluation->course
                && ! Course::notHiddenFromTeacher($teacherProfile->id)->whereKey($evaluation->course->id)->exists(),
            404
        );
        $students = StudentProfile::whereHas('enrollments', function ($query) use ($evaluation) {
            $query->where('formation_id', $evaluation->formation_id)
                ->where('academic_session_id', $evaluation->session_id)
                ->where('status', 'active');
        })
            ->with('user')
            ->get()
            ->sortBy(fn (StudentProfile $student) => $student->user->last_name.' '.$student->user->first_name)
            ->values();
        $grades = $evaluation->grades->keyBy('student_profile_id');
        $isReadOnly = $evaluation->statut !== 'ouverte'
            || $evaluation->course?->statut === 'termine'
            || $evaluation->academicSession?->status === 'terminee';

        return view('teacher.grades.assigned', compact('evaluation', 'students', 'grades', 'isReadOnly'));
    }

    public function storeAssigned(Request $request, Evaluation $evaluation)
    {
        $teacherProfile = auth()->user()->teacherProfile;
        if (! $teacherProfile || $evaluation->type !== 'devoir' || $evaluation->assigned_teacher_profile_id !== $teacherProfile->id) {
            abort(404);
        }

        abort_unless($evaluation->statut === 'ouverte', 403, 'Ce devoir n’est pas ouvert à la saisie.');
        abort_unless(
            ! $evaluation->course || $evaluation->course->statut === 'en_cours',
            403,
            'Le cours lié à ce devoir est terminé.'
        );
        abort_unless(
            ! $evaluation->course || $evaluation->course->academicSession?->status !== 'terminee',
            403,
            'La rentrée du cours est terminée.'
        );
        abort_unless(
            ! $evaluation->course
                || Course::notHiddenFromTeacher($teacherProfile->id)->whereKey($evaluation->course->id)->exists(),
            404
        );

        $validated = $request->validate([
            'grades' => ['required', 'array'],
            'grades.*.score' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'grades.*.is_absent' => ['nullable', 'boolean'],
            'grades.*.comments' => ['nullable', 'string', 'max:255'],
        ]);

        $students = StudentProfile::whereHas('enrollments', function ($query) use ($evaluation) {
            $query->where('formation_id', $evaluation->formation_id)
                ->where('academic_session_id', $evaluation->session_id)
                ->where('status', 'active');
        })->pluck('id');

        if (collect(array_keys($validated['grades']))->map(fn ($id) => (int) $id)->diff($students)->isNotEmpty()) {
            abort(403, 'Un ou plusieurs étudiants ne sont pas inscrits à cette formation et cette session.');
        }

        foreach ($validated['grades'] as $studentProfileId => $data) {
            $score = ($data['score'] ?? '') !== '' ? $data['score'] : null;
            $isAbsent = (bool) ($data['is_absent'] ?? false);
            $comments = $data['comments'] ?? null;

            if ($score === null && ! $isAbsent && blank($comments)) {
                Grade::where('evaluation_id', $evaluation->id)
                    ->where('student_profile_id', $studentProfileId)
                    ->delete();

                continue;
            }

            $grade = Grade::updateOrCreate(
                ['evaluation_id' => $evaluation->id, 'student_profile_id' => $studentProfileId],
                ['score' => $isAbsent ? null : $score, 'is_absent' => $isAbsent, 'comments' => $comments],
            );

            // Notifier l'étudiant et ses parents si une note réelle est saisie
            if ($grade->score !== null) {
                $grade->load(['evaluation', 'studentProfile.user']);
                $this->notificationService->noteSaisie($grade);
            }
        }

        return redirect()->route('teacher.grades.assigned.show', $evaluation)
            ->with('success', 'Notes du devoir enregistrées.');
    }
}
