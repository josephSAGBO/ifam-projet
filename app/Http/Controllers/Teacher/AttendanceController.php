<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\AttendanceSheet;
use App\Models\Course;
use App\Models\Formation;
use App\Models\Module;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function index(Request $request)
    {
        $teacherProfile = auth()->user()->teacherProfile;
        if (! $teacherProfile) {
            abort(403, 'Profil enseignant introuvable.');
        }

        $history = $request->boolean('history');
        $selectedSessionId = $request->integer('session_id') ?: null;
        $selectedFormationId = $request->integer('formation_id') ?: null;
        $selectedModuleId = $request->integer('module_id') ?: null;
        $selectedDate = $request->input('date', now()->toDateString());
        $request->validate(['date' => 'nullable|date_format:Y-m-d']);

        $teacherSessionIds = Course::where(function ($query) use ($teacherProfile) {
            $query->where('enseignant_id', $teacherProfile->id)
                ->orWhere('teacher_profile_id', $teacherProfile->id);
        })
            ->notHiddenFromTeacher($teacherProfile->id)
            ->distinct()
            ->pluck('academic_session_id');
        $sessionsQuery = AcademicSession::whereIn('id', $teacherSessionIds);
        if (! $history) {
            $sessionsQuery->where('status', '!=', 'terminee');
            $sessionsQuery->where(function ($query) use ($selectedSessionId) {
                $query->where('is_current', true)
                    ->when($selectedSessionId, fn ($q) => $q->orWhere('id', $selectedSessionId));
            });
        }
        $sessions = $sessionsQuery->orderByDesc('start_date')->get();

        $baseCoursesQuery = Course::where(function ($q) use ($teacherProfile) {
            $q->where('enseignant_id', $teacherProfile->id)
                ->orWhere('teacher_profile_id', $teacherProfile->id);
        })
            ->notHiddenFromTeacher($teacherProfile->id)
            ->with(['module', 'formation', 'academicSession', 'salle']);
        if (! $history) {
            $baseCoursesQuery->where('statut', 'en_cours');
            $baseCoursesQuery->whereHas('academicSession', fn ($query) => $query->where('status', '!=', 'terminee'));
            if (! $selectedSessionId) {
                $baseCoursesQuery->whereHas('academicSession', fn ($q) => $q->where('is_current', true));
            }
        } else {
            $baseCoursesQuery->where(function ($query) {
                $query->where('statut', 'termine')
                    ->orWhereHas('attendanceSheets', fn ($sheets) => $sheets->where('statut', 'cloturee'))
                    ->orWhereHas('academicSession', fn ($session) => $session->where('status', 'terminee'));
            });
        }

        if ($selectedSessionId) {
            $baseCoursesQuery->where('academic_session_id', $selectedSessionId);
        }
        if ($selectedFormationId) {
            $baseCoursesQuery->where('formation_id', $selectedFormationId);
        }
        if ($selectedModuleId) {
            $baseCoursesQuery->where('module_id', $selectedModuleId);
        }

        $courses = $baseCoursesQuery
            ->orderByDesc('date')
            ->orderBy('heure_debut')
            ->get();

        $formations = collect();
        if ($selectedSessionId) {
            $formations =
                Formation::whereIn('id', Course::where(function ($q) use ($teacherProfile) {
                    $q->where('enseignant_id', $teacherProfile->id)
                        ->orWhere('teacher_profile_id', $teacherProfile->id);
                })
                    ->notHiddenFromTeacher($teacherProfile->id)
                    ->when($selectedSessionId, fn ($query) => $query->where('academic_session_id', $selectedSessionId))
                    ->pluck('formation_id'))
                    ->orderBy('name')
                    ->get();
        }

        $modules = collect();
        if ($selectedFormationId) {
            $modules =
                Module::whereIn('id', Course::where(function ($q) use ($teacherProfile) {
                    $q->where('enseignant_id', $teacherProfile->id)
                        ->orWhere('teacher_profile_id', $teacherProfile->id);
                })
                    ->notHiddenFromTeacher($teacherProfile->id)
                    ->when($selectedSessionId, fn ($query) => $query->where('academic_session_id', $selectedSessionId))
                    ->when($selectedFormationId, fn ($query) => $query->where('formation_id', $selectedFormationId))
                    ->pluck('module_id'))
                    ->orderBy('name')
                    ->get();
        }

        $course = null;
        $students = collect();
        $attendances = collect();
        $attendanceSheet = null;
        $isAttendanceReadOnly = false;

        $courseId = $request->get('course_id');
        if ($courseId) {
            $course = $baseCoursesQuery
                ->where('id', $courseId)
                ->firstOrFail();

            $students = $course->etudiantsConcernes(includeCompletedEnrollments: $history);
            $attendances = Attendance::where('course_id', $courseId)
                ->whereDate('date', $selectedDate)
                ->get()
                ->keyBy('student_profile_id');
            $attendanceSheet = AttendanceSheet::where('course_id', $courseId)
                ->whereDate('date', $selectedDate)
                ->first();
            $isAttendanceReadOnly = $history || $course->statut === 'termine' || $attendanceSheet?->statut === 'cloturee';
        }

        return view('teacher.attendance.index', compact(
            'courses', 'course', 'students', 'attendances', 'attendanceSheet', 'isAttendanceReadOnly',
            'selectedDate', 'history', 'sessions', 'formations', 'modules', 'selectedSessionId', 'selectedFormationId', 'selectedModuleId'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'attendances' => 'required|array',
            'attendances.*.status' => 'required|in:present,absent,late',
            'attendances.*.remarks' => 'nullable|string|max:1000',
            'date' => 'nullable|date_format:Y-m-d',
        ]);

        $teacherProfile = auth()->user()->teacherProfile;
        abort_unless($teacherProfile, 403, 'Profil enseignant introuvable.');

        // Vérification que le cours appartient à cet enseignant
        $course = Course::where(function ($q) use ($teacherProfile) {
            $q->where('enseignant_id', $teacherProfile->id)
                ->orWhere('teacher_profile_id', $teacherProfile->id);
        })->notHiddenFromTeacher($teacherProfile->id)->findOrFail($request->course_id);
        $course->loadMissing('academicSession');
        abort_unless($course->statut === 'en_cours' && $course->academicSession?->status !== 'terminee', 403, 'Ce cours est terminé.');

        $attendanceDate = $validated['date'] ?? now()->toDateString();
        $attendanceSheet = AttendanceSheet::where('course_id', $course->id)
            ->whereDate('date', $attendanceDate)
            ->first();
        if (! $attendanceSheet) {
            $attendanceSheet = AttendanceSheet::create([
                'course_id' => $course->id,
                'date' => $attendanceDate,
                'statut' => 'ouverte',
            ]);
        }
        abort_unless($attendanceSheet->statut === 'ouverte', 403, 'Cette feuille de présence est clôturée.');

        $studentIds = $course->etudiantsConcernes()->pluck('id');
        if (collect(array_keys($validated['attendances']))->map(fn ($id) => (int) $id)->diff($studentIds)->isNotEmpty()) {
            abort(403, 'Un ou plusieurs étudiants ne sont pas inscrits à ce cours.');
        }

        foreach ($validated['attendances'] as $studentProfileId => $data) {
            $attendance = Attendance::updateOrCreate(
                [
                    'course_id' => $course->id,
                    'student_profile_id' => $studentProfileId,
                    'date' => $attendanceDate,
                ],
                [
                    'formation_id' => $course->formation_id,
                    'assigned_teacher_profile_id' => $teacherProfile->id,
                    'status' => $data['status'],
                    'remarks' => $data['remarks'] ?? null,
                ]
            );

            // Notifier l'étudiant et ses parents en cas d'absence ou de retard
            if (in_array($data['status'], ['absent', 'late'], true)) {
                $attendance->load(['course.module', 'studentProfile.user']);
                $this->notificationService->absenceEnregistree($attendance);
            }
        }

        $redirectParameters = ['course_id' => $course->id];
        if ($request->filled('date')) {
            $redirectParameters['date'] = $attendanceDate;
        }

        return redirect()->route('teacher.attendance.index', $redirectParameters)
            ->with('success', 'Présences enregistrées avec succès !');
    }

    public function update(Request $request, Attendance $attendance)
    {
        $teacherProfile = auth()->user()->teacherProfile;
        abort_unless($teacherProfile, 403, 'Profil enseignant introuvable.');
        // Vérification ownership via le cours
        $course = Course::where(function ($q) use ($teacherProfile) {
            $q->where('enseignant_id', $teacherProfile->id)
                ->orWhere('teacher_profile_id', $teacherProfile->id);
        })->notHiddenFromTeacher($teacherProfile->id)->findOrFail($attendance->course_id);
        $course->loadMissing('academicSession');
        abort_unless($course->statut === 'en_cours' && $course->academicSession?->status !== 'terminee', 403, 'Ce cours est terminé.');
        $attendanceSheet = AttendanceSheet::where('course_id', $course->id)
            ->whereDate('date', $attendance->date)
            ->first();
        abort_unless($attendanceSheet?->statut !== 'cloturee', 403, 'Cette feuille de présence est clôturée.');

        abort_unless(
            (int) ($attendance->assigned_teacher_profile_id ?? $course->enseignant_id ?? $course->teacher_profile_id ?? 0) === (int) $teacherProfile->id,
            403,
            'Vous n’êtes pas l’enseignant assigné à cette présence.'
        );

        $attendance->update([
            'formation_id' => $attendance->formation_id ?? $course->formation_id,
            'assigned_teacher_profile_id' => $teacherProfile->id,
            'status' => $request->status ?? $attendance->status,
            'remarks' => $request->remarks ?? $attendance->remarks,
        ]);

        return redirect()->back()->with('success', 'Présence mise à jour.');
    }
}
