<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Services\PaiementService;
use Illuminate\Contracts\View\View;

class ChildController extends Controller
{
    public function index(PaiementService $paiementService): View
    {
        $parent = auth()->user();

        $children = $parent->children()
            ->with([
                'studentProfile.enrollments' => fn ($query) => $query
                    ->with(['formation', 'academicSession', 'payments'])
                    ->orderByDesc('enrollment_date')
                    ->orderByDesc('id'),
            ])
            ->get();

        $childrenData = $children->map(function ($child) use ($paiementService) {
            $profile = $child->studentProfile;
            $profileId = $profile ? $profile->id : null;

            $totalAttendances = $profileId ? Attendance::where('student_profile_id', $profileId)->count() : 0;
            $presentCount = $profileId ? Attendance::where('student_profile_id', $profileId)->where('status', 'present')->count() : 0;
            $attendanceRate = $totalAttendances > 0 ? round(($presentCount / $totalAttendances) * 100) : null;

            // Notes
            $grades = $profileId ? Grade::where('student_profile_id', $profileId)->with('evaluation')->get() : collect();
            $avgSum = 0;
            $coefSum = 0;
            foreach ($grades as $g) {
                if ($g->evaluation && $g->evaluation->statut === 'cloturee') {
                    $score20 = $g->is_absent ? 0 : (float) $g->score;
                    $avgSum += $score20;
                    $coefSum += 1;
                }
            }
            $generalAverage = $coefSum > 0 ? round($avgSum / $coefSum, 2) : null;

            $enrollments = $profile?->enrollments ?? collect();
            $balance = (float) $enrollments->sum(
                fn (Enrollment $enrollment) => $paiementService->solde($enrollment)
            );
            $totalPaid = (float) $enrollments->sum(
                fn (Enrollment $enrollment) => $paiementService->totalPaye($enrollment)
            );

            $currentEnrollment = $profile ? $profile->enrollments->where('status', 'active')->first() : null;

            return [
                'user' => $child,
                'relationship' => $child->pivot->relationship ?? 'Parent',
                'profile' => $profile,
                'currentEnrollment' => $currentEnrollment,
                'attendanceRate' => $attendanceRate,
                'generalAverage' => $generalAverage,
                'totalPaid' => $totalPaid,
                'balance' => $balance,
                'gradesCount' => $grades->count(),
            ];
        });

        return view('parent.children.index', compact('childrenData'));
    }
}
