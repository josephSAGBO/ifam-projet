<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Grade;

class GradeController extends Controller
{
    /**
     * Barème fixe : toutes les évaluations sont sur 20.
     */
    private const BAREME = 20;

    public function index()
    {
        $student = auth()->user()->studentProfile;

        if (! $student) {
            return view('student.grades.index', [
                'enrollments' => collect(),
                'modulesData' => [],
                'generalAverage' => null,
                'isValidated' => false,
                'totalEvaluations' => 0,
            ]);
        }

        $enrollments = Enrollment::where('student_profile_id', $student->id)
            ->whereIn('status', Enrollment::STUDENT_ACCESSIBLE_STATUSES)
            ->with(['formation', 'academicSession'])
            ->latest('enrollment_date')
            ->get();

        $grades = collect();
        if ($enrollments->isNotEmpty()) {
            $grades = Grade::where('student_profile_id', $student->id)
                ->whereHas('evaluation', function ($query) use ($enrollments) {
                    $query->whereIn('statut', ['ouverte', 'cloturee'])
                        ->whereHas('course', function ($courseQuery) use ($enrollments) {
                            $courseQuery->where(function ($enrollmentQuery) use ($enrollments) {
                                foreach ($enrollments as $enrollment) {
                                    $enrollmentQuery->orWhere(function ($pairQuery) use ($enrollment) {
                                        $pairQuery->where('formation_id', $enrollment->formation_id)
                                            ->where('academic_session_id', $enrollment->academic_session_id);
                                    });
                                }
                            });
                        });
                })
                ->where(function ($query) {
                    $query->whereNotNull('score')
                        ->orWhere('is_absent', true);
                })
                ->with([
                    'evaluation.module',
                    'evaluation.course.module',
                    'evaluation.course.formation',
                    'evaluation.course.academicSession',
                ])
                ->get();
        }

        $groups = $grades->groupBy(function ($grade) {
            $course = $grade->evaluation?->course;
            $moduleId = $grade->evaluation?->module_id ?? $course?->module_id;

            return implode('-', [$course?->formation_id, $course?->academic_session_id, $moduleId]);
        });

        $modulesData = [];
        $totalPoints = 0;
        $totalModuleWeights = 0;

        foreach ($groups as $moduleGrades) {
            $firstGrade = $moduleGrades->first();
            $course = $firstGrade->evaluation?->course;
            $module = $firstGrade->evaluation?->module ?? $course?->module;

            if (! $module || ! $course) {
                continue;
            }

            $moduleSum = 0;
            $moduleCoefSum = 0;

            foreach ($moduleGrades as $grade) {
                if ($grade->is_absent) {
                    $scoreOn20 = 0;
                } else {
                    $scoreOn20 = $grade->score !== null
                        ? min((float) $grade->score, self::BAREME)
                        : null;
                }

                if ($scoreOn20 !== null) {
                    $moduleSum += $scoreOn20;
                    $moduleCoefSum++;
                }
            }

            $moduleAverage = $moduleCoefSum > 0 ? round($moduleSum / $moduleCoefSum, 2) : null;
            $moduleWeight = (float) ($module->coefficient ?: 1);

            if ($moduleAverage !== null) {
                $totalPoints += $moduleAverage * $moduleWeight;
                $totalModuleWeights += $moduleWeight;
            }

            $modulesData[] = [
                'module' => $module,
                'grades' => $moduleGrades,
                'average' => $moduleAverage,
                'coefficient' => $moduleWeight,
                'formation' => $course->formation,
                'academicSession' => $course->academicSession,
            ];
        }

        $generalAverage = $totalModuleWeights > 0
            ? round($totalPoints / $totalModuleWeights, 2)
            : null;

        $isValidated = ($generalAverage !== null && $generalAverage >= 10.0);

        return view('student.grades.index', [
            'enrollments' => $enrollments,
            'modulesData' => $modulesData,
            'generalAverage' => $generalAverage,
            'isValidated' => $isValidated,
            'totalEvaluations' => $grades->count(),
        ]);
    }
}
