<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Expense;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Services\PaiementService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, PaiementService $paiementService)
    {
        // ── KPIs Utilisateurs ───────────────────────────────────────
        $totalStudents = StudentProfile::count();
        $totalTeachers = TeacherProfile::count();
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();

        // ── Finance ─────────────────────────────────────────────────
        $totalRevenue = Payment::sum('amount_paid');
        $enrollmentsWithPayments = Enrollment::with('payments')->get();
        $pendingFees = $enrollmentsWithPayments
            ->filter(fn (Enrollment $enrollment) => $paiementService->statut($enrollment) === 'impaye')
            ->count();
        $partialFees = $enrollmentsWithPayments
            ->filter(fn (Enrollment $enrollment) => $paiementService->statut($enrollment) === 'partiel')
            ->count();
        $totalExpenses = Expense::sum('amount');
        $revenueThisMonth = Payment::whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount_paid');

        // ── Session active ───────────────────────────────────────────
        $activeSession = AcademicSession::where('is_current', true)
            ->latest()
            ->first();

        // ── Présences du jour ────────────────────────────────────────
        $todayAttendanceCount = Attendance::whereDate('date', today())->count();
        $todayAbsentCount = Attendance::whereDate('date', today())->where('status', 'absent')->count();
        $todayPresentCount = Attendance::whereDate('date', today())->where('status', 'present')->count();

        // ── Notifications non lues ───────────────────────────────────
        $unreadNotifications = Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        // ── Paiements récents ────────────────────────────────────────
        $recentPayments = Payment::with('studentProfile.user', 'fee')
            ->orderByDesc('payment_date')
            ->limit(6)
            ->get();

        // ── Derniers utilisateurs ─────────────────────────────────────
        $latestUsers = User::orderByDesc('created_at')->limit(5)->get();

        // ── Évolution paiements 6 derniers mois (pour mini chart) ────
        $paymentsChart = Payment::selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(amount_paid) as total")
            ->where('payment_date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.dashboard', compact(
            'totalStudents', 'totalTeachers', 'totalUsers', 'activeUsers',
            'totalRevenue', 'totalExpenses', 'pendingFees', 'partialFees',
            'revenueThisMonth', 'activeSession',
            'todayAttendanceCount', 'todayAbsentCount', 'todayPresentCount',
            'unreadNotifications', 'recentPayments',
            'latestUsers', 'paymentsChart',
        ));
    }
}
