<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Evaluation;
use App\Models\Notification;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $teacher = auth()->user()->teacherProfile;
        $teacherId = optional($teacher)->id;

        $myCourses = Course::where(function ($q) use ($teacherId) {
            $q->where('enseignant_id', $teacherId)
                ->orWhere('teacher_profile_id', $teacherId);
        })
            ->with('module', 'formation')
            ->limit(10)
            ->get();

        $myEvaluations = Evaluation::whereHas('course', function ($q) use ($teacherId) {
            $q->where('enseignant_id', $teacherId)
                ->orWhere('teacher_profile_id', $teacherId);
        })
            ->orWhere(function ($q) {
                // Évaluations créées par l'enseignant
                $q->where('created_by', auth()->id());
            })
            ->with(['course.module', 'module'])
            ->orderByDesc('date')
            ->limit(5)
            ->get();

        $todayAttendance = Attendance::whereDate('date', today())
            ->whereHas('course', function ($q) use ($teacherId) {
                $q->where('enseignant_id', $teacherId)
                    ->orWhere('teacher_profile_id', $teacherId);
            })
            ->count();

        $unreadNotifications = Notification::where('user_id', auth()->id())->whereNull('read_at')->count();
        $activeSession = AcademicSession::where('is_current', true)->latest()->first();

        return view('teacher.dashboard', compact(
            'teacher', 'myCourses', 'myEvaluations',
            'todayAttendance', 'unreadNotifications', 'activeSession'
        ));
    }
}
