<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Alumni;
use App\Models\Attendance;
use App\Models\BulletinPublication;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\User;
use App\Services\PaiementService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DashboardController extends Controller
{
    public function __invoke(PaiementService $paiementService): View
    {
        $parentUser = auth()->user();
        $activeSession = AcademicSession::where('is_current', true)->first() ?? AcademicSession::latest('start_date')->first();

        // Récupérer tous les enfants liés au compte parent via parent_etudiant
        $childrenUsers = $parentUser->children()
            ->with([
                'studentProfile.enrollments' => fn (HasMany $query) => $query
                    ->with(['formation', 'academicSession', 'payments'])
                    ->orderByDesc('enrollment_date')
                    ->orderByDesc('id'),
            ])
            ->get();

        $childrenData = $childrenUsers->map(function (User $childUser) use ($activeSession, $paiementService): array {
            $studentProfile = $childUser->studentProfile;

            if (! $studentProfile) {
                return [
                    'user' => $childUser,
                    'profile' => null,
                    'enrollment' => null,
                    'recentGrades' => collect(),
                    'recentAttendances' => collect(),
                    'financialEnrollments' => collect(),
                    'financialTotalPaid' => 0,
                    'financialBalance' => 0,
                    'attendanceRate' => 0,
                    'presentCount' => 0,
                    'totalAttendances' => 0,
                    'publishedBulletins' => collect(),
                ];
            }

            $financialEnrollments = $studentProfile->enrollments;
            $enrollment = $financialEnrollments->first(
                fn (Enrollment $enrollment) => $enrollment->status === 'active'
                    && (! $activeSession || $enrollment->academic_session_id === $activeSession->id)
            );

            // Notes récentes
            $recentGrades = Grade::where('student_profile_id', $studentProfile->id)
                ->with(['evaluation.module', 'evaluation.course'])
                ->whereNotNull('score')
                ->latest()
                ->limit(5)
                ->get();

            // Présences
            $totalAttendances = Attendance::where('student_profile_id', $studentProfile->id)->count();
            $presentCount = Attendance::where('student_profile_id', $studentProfile->id)->where('status', 'present')->count();
            $attendanceRate = $totalAttendances > 0 ? round(($presentCount / $totalAttendances) * 100) : 100;
            $recentAttendances = Attendance::where('student_profile_id', $studentProfile->id)
                ->with('course.module')
                ->latest('date')
                ->limit(5)
                ->get();

            // Bulletins publiés
            $publishedBulletins = BulletinPublication::where('student_profile_id', $studentProfile->id)
                ->where('is_published', true)
                ->with(['formation', 'academicSession'])
                ->get();

            return [
                'user' => $childUser,
                'profile' => $studentProfile,
                'enrollment' => $enrollment,
                'recentGrades' => $recentGrades,
                'recentAttendances' => $recentAttendances,
                'financialEnrollments' => $financialEnrollments,
                'financialTotalPaid' => $financialEnrollments->sum(
                    fn (Enrollment $enrollment) => $paiementService->totalPaye($enrollment)
                ),
                'financialBalance' => $financialEnrollments->sum(
                    fn (Enrollment $enrollment) => $paiementService->solde($enrollment)
                ),
                'attendanceRate' => $attendanceRate,
                'presentCount' => $presentCount,
                'totalAttendances' => $totalAttendances,
                'publishedBulletins' => $publishedBulletins,
            ];
        });

        $alumniShowcase = Alumni::query()
            ->showcaseData()
            ->publiclyVisible()
            ->orderByDesc('date_diplome')
            ->orderByDesc('promotion_year')
            ->limit(6)
            ->get();

        return view('parent.dashboard', compact('parentUser', 'activeSession', 'childrenData', 'alumniShowcase'));
    }
}
