<?php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Alumni;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Expense;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Services\PaiementService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(PaiementService $paiementService): View
    {
        $totalStudents = StudentProfile::count();
        $totalTeachers = TeacherProfile::count();
        $totalRevenue = (float) Payment::query()
            ->whereNotNull('inscription_id')
            ->sum(DB::raw('COALESCE(montant, amount_paid, 0)'));
        $totalExpenses = Expense::sum('amount');
        $activeSession = AcademicSession::where('is_current', true)->latest()->first();
        $unreadNotifications = Notification::where('user_id', auth()->id())->whereNull('read_at')->count();

        $todayPresent = Attendance::whereDate('date', today())->where('status', 'present')->count();
        $todayAbsent = Attendance::whereDate('date', today())->where('status', 'absent')->count();
        $todayTotal = $todayPresent + $todayAbsent;

        $enrollments = Enrollment::with('payments')->get();
        $pendingFees = $enrollments->filter(fn (Enrollment $enrollment) => $paiementService->solde($enrollment) > 0)->count();
        $totalAlumni = Alumni::count();
        $visibleAlumni = Alumni::publiclyVisible()->count();
        $recentAlumni = Alumni::query()
            ->showcaseData()
            ->publiclyVisible()
            ->latest('created_at')
            ->limit(6)
            ->get();

        return view('direction.dashboard', compact(
            'totalStudents', 'totalTeachers', 'totalRevenue', 'totalExpenses',
            'activeSession', 'unreadNotifications', 'todayPresent', 'todayAbsent',
            'todayTotal', 'pendingFees', 'totalAlumni', 'visibleAlumni', 'recentAlumni'
        ));
    }
}
