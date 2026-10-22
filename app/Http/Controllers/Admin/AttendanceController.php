<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HasSiteFilter;
use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\AttendanceSheet;
use App\Models\Course;
use App\Models\Formation;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    use HasSiteFilter;

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

    /**
     * Display a listing of courses to take attendance for.
     */
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
        $selectedCourseId = $request->integer('course_id') ?: null;

        $coursesQuery = Course::with(['module', 'formation', 'academicSession', 'enseignant.user', 'teacherProfile.user'])
            ->when($managed !== null, fn ($query) => $query->whereHas('academicSession', fn ($sq) => $sq->whereIn('site_id', $managed)))
            ->when($selectedSiteId !== null, fn ($query) => $query->whereHas('academicSession', fn ($sq) => $sq->where('site_id', $selectedSiteId)))
            ->when($selectedSessionId, fn ($query) => $query->where('academic_session_id', $selectedSessionId))
            ->when($selectedFormationId, fn ($query) => $query->where('formation_id', $selectedFormationId));

        $courses = $coursesQuery->orderByDesc('date')->get();

        $formations = collect();
        if ($selectedSessionId) {
            $formationIds = Course::where('academic_session_id', $selectedSessionId)
                ->whereNotNull('formation_id')
                ->pluck('formation_id')
                ->unique();

            $formations = Formation::whereIn('id', $formationIds)
                ->orderBy('name')
                ->get();
        }

        $course = null;
        $students = collect();
        $attendances = collect();
        $attendanceSheet = null;
        $date = Carbon::today();

        if ($selectedCourseId) {
            $course = $courses->firstWhere('id', $selectedCourseId);

            if ($course) {
                $dateStr = $request->input('date', date('Y-m-d'));
                try {
                    $date = Carbon::parse($dateStr);
                } catch (\Exception $e) {
                    $date = Carbon::now();
                }

                $students = $course->etudiantsConcernes();
                $attendances = Attendance::where('course_id', $course->id)
                    ->whereDate('date', $date->format('Y-m-d'))
                    ->get()
                    ->keyBy('student_profile_id');
                $attendanceSheet = AttendanceSheet::where('course_id', $course->id)
                    ->whereDate('date', $date->format('Y-m-d'))
                    ->first();
            }
        }

        return view('admin.attendance.index', compact(
            'sessions',
            'formations',
            'courses',
            'selectedSessionId',
            'selectedFormationId',
            'selectedCourseId',
            'sites',
            'selectedSiteId',
            'course',
            'students',
            'attendances',
            'attendanceSheet',
            'date'
        ));
    }

    public function updateSheetStatus(Request $request, Course $course): RedirectResponse
    {
        $managed = $this->managedSiteIds();
        if ($managed !== null) {
            $course->loadMissing('academicSession');
            $siteId = $course->academicSession?->site_id;
            if ($siteId !== null && ! in_array($siteId, $managed, true)) {
                abort(403, 'Accès refusé : ce cours appartient à un autre site.');
            }
        }

        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'statut' => 'required|in:ouverte,cloturee',
        ]);

        $attendanceSheet = AttendanceSheet::where('course_id', $course->id)
            ->whereDate('date', $validated['date'])
            ->first();

        if ($attendanceSheet) {
            $attendanceSheet->update(['statut' => $validated['statut']]);
        } else {
            AttendanceSheet::create([
                'course_id' => $course->id,
                'date' => $validated['date'],
                'statut' => $validated['statut'],
            ]);
        }

        return redirect()->route('admin.attendance.index', [
            'session_id' => $course->academic_session_id,
            'formation_id' => $course->formation_id,
            'course_id' => $course->id,
            'date' => $validated['date'],
        ])->with('success', 'Statut de la feuille de présence mis à jour.');
    }

    /**
     * Show the form for creating or editing attendance for a specific course and date.
     */
    public function show(Request $request, Course $course)
    {
        $managed = $this->managedSiteIds();
        if ($managed !== null) {
            $course->loadMissing('academicSession');
            $siteId = $course->academicSession?->site_id;
            if ($siteId !== null && ! in_array($siteId, $managed, true)) {
                abort(403, 'Accès refusé : ce cours appartient à un autre site.');
            }
        }

        $dateStr = $request->input('date', date('Y-m-d'));
        try {
            $date = Carbon::parse($dateStr);
        } catch (\Exception $e) {
            $date = Carbon::now();
        }

        // Utilisation de la méthode unique du cours
        $students = $course->etudiantsConcernes();

        $attendances = Attendance::where('course_id', $course->id)
            ->whereDate('date', $date->format('Y-m-d'))
            ->get()
            ->keyBy('student_profile_id');

        return view('admin.attendance.show', compact('course', 'date', 'students', 'attendances'));
    }

    /**
     * The teacher is the only one allowed to record attendance.
     * The admin can consult the students and their current status, but cannot overwrite it.
     */
    public function store(Request $request, ?Course $course = null)
    {
        if (! $course && $request->filled('course_id')) {
            $course = Course::find($request->integer('course_id'));
        }

        abort_unless($course, 403, 'Aucun cours valide fourni pour la présence.');
        abort(403, 'La saisie des présences est réservée à l’enseignant assigné au cours.');
    }
}
