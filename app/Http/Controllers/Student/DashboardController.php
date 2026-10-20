<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Alumni;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Notification;
use App\Services\PaiementService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(PaiementService $paiementService): View
    {
        $student = auth()->user()->studentProfile;

        $activeSession = AcademicSession::where('is_current', true)->latest()->first();

        $myEnrollments = $student
            ? $student->enrollments()
                ->with(['formation', 'academicSession', 'payments'])
                ->orderByDesc('enrollment_date')
                ->orderByDesc('id')
                ->get()
            : collect();
        $totalOwed = $myEnrollments->sum(fn (Enrollment $enrollment) => $paiementService->solde($enrollment));
        $totalPaid = $myEnrollments->sum(fn (Enrollment $enrollment) => $paiementService->totalPaye($enrollment));

        // Présences
        $totalAttendances = Attendance::where('student_profile_id', optional($student)->id)->count();
        $presentCount = Attendance::where('student_profile_id', optional($student)->id)->where('status', 'present')->count();
        $attendanceRate = $totalAttendances > 0 ? round(($presentCount / $totalAttendances) * 100) : 0;

        // Notes récentes
        $recentGrades = Grade::where('student_profile_id', optional($student)->id)
            ->with('evaluation.course.module')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $unreadNotifications = Notification::where('user_id', auth()->id())->whereNull('read_at')->count();

        $preferredFormationId = $myEnrollments->first()?->formation_id;
        $alumniQuery = Alumni::query()
            ->showcaseData()
            ->publiclyVisible();

        if ($preferredFormationId) {
            $alumniQuery->orderByRaw('CASE WHEN formation_id = ? THEN 0 ELSE 1 END', [$preferredFormationId]);
        }

        $alumniShowcase = $alumniQuery
            ->orderByDesc('date_diplome')
            ->orderByDesc('promotion_year')
            ->limit(6)
            ->get();

        return view('student.dashboard', compact(
            'student', 'myEnrollments', 'totalOwed', 'totalPaid',
            'attendanceRate', 'presentCount', 'totalAttendances',
            'recentGrades', 'unreadNotifications', 'activeSession', 'alumniShowcase'
        ));
    }
}
