<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Enrollment;

class AttendanceController extends Controller
{
    public function index()
    {
        $student = auth()->user()->studentProfile;

        if (! $student) {
            return view('student.attendance.index', [
                'enrollments' => collect(),
                'attendances' => collect(),
                'totalCount' => 0,
                'presentCount' => 0,
                'absentCount' => 0,
                'justifiedCount' => 0,
                'lateCount' => 0,
                'attendanceRate' => 0,
                'byModule' => collect(),
            ]);
        }

        $enrollments = Enrollment::where('student_profile_id', $student->id)
            ->whereIn('status', Enrollment::STUDENT_ACCESSIBLE_STATUSES)
            ->with(['formation', 'academicSession'])
            ->latest('enrollment_date')
            ->get();

        $attendances = collect();
        $byModule = collect();

        if ($enrollments->isNotEmpty()) {
            $courseIds = Course::where(function ($query) use ($enrollments) {
                foreach ($enrollments as $enrollment) {
                    $query->orWhere(function ($pairQuery) use ($enrollment) {
                        $pairQuery->where('formation_id', $enrollment->formation_id)
                            ->where('academic_session_id', $enrollment->academic_session_id);
                    });
                }
            })->pluck('id');

            $attendances = Attendance::whereIn('course_id', $courseIds)
                ->where('student_profile_id', $student->id)
                ->with(['course.module', 'course.formation', 'course.academicSession'])
                ->orderByDesc('date')
                ->get();

            $byModule = $attendances->groupBy(fn ($attendance) => implode('-', [
                $attendance->course->formation_id,
                $attendance->course->academic_session_id,
                $attendance->course->module_id,
            ]))
                ->map(function ($items, $moduleName) {
                    $course = $items->first()->course;
                    $total = $items->count();
                    $present = $items->where('status', 'present')->count();
                    $absent = $items->where('status', 'absent')->count();
                    $late = $items->where('status', 'late')->count();

                    return [
                        'module' => $course->module->name ?? 'Module inconnu',
                        'formation' => $course->formation->name ?? 'Formation inconnue',
                        'academicSession' => $course->academicSession->name ?? 'Session inconnue',
                        'total' => $total,
                        'present' => $present,
                        'absent' => $absent,
                        'justified' => 0,
                        'late' => $late,
                        'rate' => $total > 0 ? round(($present / $total) * 100) : 0,
                    ];
                })->values();
        }

        $totalCount = $attendances->count();
        $presentCount = $attendances->where('status', 'present')->count();
        $absentCount = $attendances->where('status', 'absent')->count();
        $justifiedCount = 0;
        $lateCount = $attendances->where('status', 'late')->count();
        $attendanceRate = $totalCount > 0 ? round(($presentCount / $totalCount) * 100) : 0;

        return view('student.attendance.index', compact(
            'enrollments', 'attendances',
            'totalCount', 'presentCount', 'absentCount', 'justifiedCount', 'lateCount',
            'attendanceRate', 'byModule'
        ));
    }
}
