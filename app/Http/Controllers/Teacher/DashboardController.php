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
            ->when($teacherId, fn ($query) => $query->notHiddenFromTeacher($teacherId))
            ->whereHas('academicSession', fn ($query) => $query->where('status', '!=', 'terminee'))
            ->with('module', 'formation')
            ->limit(10)
            ->get();

        $myEvaluations = Evaluation::where(function ($query) use ($teacherId) {
            $query->whereHas('course', function ($courseQuery) use ($teacherId) {
                $courseQuery->where(function ($assignment) use ($teacherId) {
                    $assignment->where('enseignant_id', $teacherId)
                        ->orWhere('teacher_profile_id', $teacherId);
                })
                    ->when($teacherId, fn ($query) => $query->notHiddenFromTeacher($teacherId))
                    ->whereHas('academicSession', fn ($session) => $session->where('status', '!=', 'terminee'));
            })->orWhere(function ($createdEvaluations) {
                $createdEvaluations->where('created_by', auth()->id())
                    ->whereNull('cours_id');
            });
        })
            ->with(['course.module', 'module'])
            ->orderByDesc('date')
            ->limit(5)
            ->get();

        $todayAttendance = Attendance::whereDate('date', today())
            ->whereHas('course', function ($q) use ($teacherId) {
                $q->where(function ($assignment) use ($teacherId) {
                    $assignment->where('enseignant_id', $teacherId)
                        ->orWhere('teacher_profile_id', $teacherId);
                })->when($teacherId, fn ($courseQuery) => $courseQuery->notHiddenFromTeacher($teacherId))
                    ->whereHas('academicSession', fn ($session) => $session->where('status', '!=', 'terminee'));
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
