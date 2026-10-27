<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Alumni;
use App\Models\AlumniPost;
use App\Models\Attendance;
use App\Models\BulletinPublication;
use App\Models\Course;
use App\Models\Discount;
use App\Models\Document;
use App\Models\Enrollment;
use App\Models\Evaluation;
use App\Models\Expense;
use App\Models\Fee;
use App\Models\Formation;
use App\Models\Grade;
use App\Models\Module;
use App\Models\Notification;
use App\Models\Partnership;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Site;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Services\PaiementService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request, PaiementService $paiementService)
    {
        $user = $request->user();

        if ($user->hasRole('responsable_commercial')) {
            $enrollments = Enrollment::query();
            $recentEnrollments = (clone $enrollments)
                ->with(['studentProfile.user', 'formation', 'academicSession.site'])
                ->latest('enrollment_date')
                ->limit(8)
                ->get();
            $enrollmentsBySite = Site::query()
                ->whereHas('enrollments')
                ->withCount('enrollments')
                ->orderByDesc('enrollments_count')
                ->orderBy('nom')
                ->get();

            return view('admin.dashboard', [
                'isDirectionDaf' => false,
                'isDirectionDaa' => false,
                'isServiceAdmin' => false,
                'isServiceFinancier' => false,
                'isRespCommercial' => true,
                'commercialSupervision' => [
                    'totalEnrollments' => (clone $enrollments)->count(),
                    'enrollmentsThisMonth' => (clone $enrollments)
                        ->whereMonth('enrollment_date', now()->month)
                        ->whereYear('enrollment_date', now()->year)
                        ->count(),
                    'activeEnrollments' => (clone $enrollments)->where('status', 'active')->count(),
                    'uniqueStudents' => (clone $enrollments)->distinct('student_profile_id')->count('student_profile_id'),
                    'completedEnrollments' => (clone $enrollments)->where('status', 'completed')->count(),
                    'suspendedEnrollments' => (clone $enrollments)->where('status', 'suspended')->count(),
                ],
                'enrollmentsBySite' => $enrollmentsBySite,
                'recentEnrollments' => $recentEnrollments,
            ]);
        }

        if ($user->hasRole('direction_daf')) {
            $validPayments = Payment::query()->where('status', 'valide');

            return view('admin.dashboard', [
                'isDirectionDaf' => true,
                'financialSupervision' => [
                    'totalRevenue' => (clone $validPayments)->sum('amount_paid'),
                    'revenueThisMonth' => (clone $validPayments)
                        ->whereMonth('payment_date', now()->month)
                        ->whereYear('payment_date', now()->year)
                        ->sum('amount_paid'),
                    'paymentsToValidate' => Payment::where('status', 'en_attente')->count(),
                    'totalExpenses' => Expense::sum('amount'),
                    'feesToRecover' => Fee::whereIn('status', ['pending', 'partial'])->count(),
                    'discounts' => Discount::count(),
                ],
                'administrativeSupervision' => [
                    'sessions' => AcademicSession::count(),
                    'currentSessions' => AcademicSession::where('is_current', true)->count(),
                    'enrollments' => Enrollment::count(),
                    'activeEnrollments' => Enrollment::where('status', 'active')->count(),
                    'students' => StudentProfile::count(),
                    'documents' => Document::count(),
                ],
            ]);
        }

        if ($user->hasRole('direction_daa')) {
            return view('admin.dashboard', [
                'isDirectionDaf' => false,
                'isDirectionDaa' => true,
                'catalogueSupervision' => [
                    'formations' => Formation::count(),
                    'modules' => Module::count(),
                ],
                'pedagogicalSupervision' => [
                    'sessions' => AcademicSession::count(),
                    'currentSessions' => AcademicSession::where('is_current', true)->count(),
                    'courses' => Course::count(),
                    'evaluations' => Evaluation::count(),
                    'grades' => Grade::count(),
                    'attendances' => Attendance::count(),
                    'bulletins' => BulletinPublication::count(),
                    'publishedBulletins' => BulletinPublication::where('is_published', true)->count(),
                ],
                'insertionSupervision' => [
                    'alumni' => Alumni::count(),
                    'partnerships' => Partnership::count(),
                ],
            ]);
        }

        if ($user->hasRole('responsable_daa')) {
            $dashboardSites = $user->sites()->orderBy('nom')->get(['sites.id', 'sites.nom']);
            $siteIds = $dashboardSites->modelKeys();
            $siteSessions = AcademicSession::query()->whereIn('site_id', $siteIds);
            $siteSessionIds = (clone $siteSessions)->select('id');
            $siteCourses = Course::query()->whereIn('academic_session_id', $siteSessionIds);
            $siteEvaluations = Evaluation::query()->where(function (Builder $query) use ($siteSessionIds): void {
                $query->whereIn('session_id', $siteSessionIds)
                    ->orWhereHas('course', fn (Builder $courseQuery) => $courseQuery->whereIn('academic_session_id', $siteSessionIds));
            });
            $siteGrades = Grade::query()->whereHas('evaluation', function (Builder $query) use ($siteSessionIds): void {
                $query->whereIn('session_id', $siteSessionIds)
                    ->orWhereHas('course', fn (Builder $courseQuery) => $courseQuery->whereIn('academic_session_id', $siteSessionIds));
            });
            $siteAttendances = Attendance::query()
                ->whereHas('course', fn (Builder $query) => $query->whereIn('academic_session_id', $siteSessionIds));
            $siteBulletins = BulletinPublication::query()->whereIn('academic_session_id', $siteSessionIds);
            $sitePartnerships = Partnership::query()->where(function (Builder $query) use ($siteIds): void {
                if ($siteIds === []) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->whereIn('site_id', $siteIds)->orWhereNull('site_id');
            });

            return view('admin.dashboard', [
                'isDirectionDaf' => false,
                'isDirectionDaa' => false,
                'isServiceDaa' => true,
                'dashboardSites' => $dashboardSites,
                'pedagogicalSupervision' => [
                    'sessions' => (clone $siteSessions)->count(),
                    'currentSessions' => (clone $siteSessions)->where('is_current', true)->count(),
                    'courses' => (clone $siteCourses)->count(),
                    'evaluations' => (clone $siteEvaluations)->count(),
                    'grades' => (clone $siteGrades)->count(),
                    'attendances' => (clone $siteAttendances)->count(),
                    'bulletins' => (clone $siteBulletins)->count(),
                    'publishedBulletins' => (clone $siteBulletins)->where('is_published', true)->count(),
                ],
                'insertionSupervision' => [
                    'alumni' => Alumni::query()->whereIn('site_id', $siteIds)->count(),
                    'partnerships' => (clone $sitePartnerships)->count(),
                ],
            ]);
        }

        if ($user->hasRole('responsable_etudes')) {
            $dashboardSites = $user->sites()->orderBy('nom')->get(['sites.id', 'sites.nom']);
            $siteIds = $dashboardSites->modelKeys();
            $siteSessions = AcademicSession::query()->whereIn('site_id', $siteIds);
            $siteSessionIds = (clone $siteSessions)->select('id');
            $siteCourses = Course::query()->whereIn('academic_session_id', $siteSessionIds);
            $siteEvaluations = Evaluation::query()->where(function (Builder $query) use ($siteSessionIds): void {
                $query->whereIn('session_id', $siteSessionIds)
                    ->orWhereHas('course', fn (Builder $courseQuery) => $courseQuery->whereIn('academic_session_id', $siteSessionIds));
            });
            $siteGrades = Grade::query()->whereHas('evaluation', function (Builder $query) use ($siteSessionIds): void {
                $query->whereIn('session_id', $siteSessionIds)
                    ->orWhereHas('course', fn (Builder $courseQuery) => $courseQuery->whereIn('academic_session_id', $siteSessionIds));
            });
            $siteAttendances = Attendance::query()
                ->whereHas('course', fn (Builder $query) => $query->whereIn('academic_session_id', $siteSessionIds));
            $siteBulletins = BulletinPublication::query()->whereIn('academic_session_id', $siteSessionIds);

            return view('admin.dashboard', [
                'isDirectionDaf' => false,
                'isDirectionDaa' => false,
                'isServiceEtudes' => true,
                'dashboardSites' => $dashboardSites,
                'pedagogicalSupervision' => [
                    'sessions' => (clone $siteSessions)->count(),
                    'currentSessions' => (clone $siteSessions)->where('is_current', true)->count(),
                    'courses' => (clone $siteCourses)->count(),
                    'evaluations' => (clone $siteEvaluations)->count(),
                    'grades' => (clone $siteGrades)->count(),
                    'attendances' => (clone $siteAttendances)->count(),
                    'bulletins' => (clone $siteBulletins)->count(),
                    'publishedBulletins' => (clone $siteBulletins)->where('is_published', true)->count(),
                ],
            ]);
        }

        if ($user->hasRole('responsable_insertion')) {
            $dashboardSites = $user->sites()->orderBy('nom')->get(['sites.id', 'sites.nom']);
            $siteIds = $dashboardSites->modelKeys();
            $siteAlumni = Alumni::query()->whereIn('site_id', $siteIds);
            $sitePartnerships = Partnership::query()->whereIn('site_id', $siteIds);

            return view('admin.dashboard', [
                'isDirectionDaf' => false,
                'isDirectionDaa' => false,
                'isServiceInsertion' => true,
                'dashboardSites' => $dashboardSites,
                'insertionDashboard' => [
                    'alumni' => (clone $siteAlumni)->count(),
                    'approvedAlumni' => (clone $siteAlumni)->where('is_approved', true)->count(),
                    'pendingAlumni' => (clone $siteAlumni)->where('is_approved', false)->count(),
                    'partnerships' => (clone $sitePartnerships)->count(),
                    'activePartnerships' => (clone $sitePartnerships)->where('statut', 'Actif')->count(),
                ],
            ]);
        }

        if ($user->hasAnyRole(['responsable_administratif', 'secretaire_administratif', 'charge_scolarite', 'agent_administratif'])) {
            $siteIds = $user->accessibleSiteIds();
            $siteSessions = AcademicSession::query()->whereIn('site_id', $siteIds);
            $siteEnrollments = Enrollment::query()->whereIn('site_id', $siteIds);
            $siteStudentProfiles = StudentProfile::query()
                ->whereHas('enrollments', fn (Builder $query) => $query->whereIn('site_id', $siteIds));
            $siteTeacherProfiles = TeacherProfile::query()
                ->whereHas('user.sites', fn (Builder $query) => $query->whereIn('sites.id', $siteIds));
            $documents = Document::query()->where(function (Builder $query) use ($siteStudentProfiles, $siteTeacherProfiles): void {
                $query->where(function (Builder $studentDocuments) use ($siteStudentProfiles): void {
                    $studentDocuments->where('documentable_type', StudentProfile::class)
                        ->whereIn('documentable_id', (clone $siteStudentProfiles)->select('id'));
                })->orWhere(function (Builder $teacherDocuments) use ($siteTeacherProfiles): void {
                    $teacherDocuments->where('documentable_type', TeacherProfile::class)
                        ->whereIn('documentable_id', (clone $siteTeacherProfiles)->select('id'));
                });
            });

            return view('admin.dashboard', [
                'isDirectionDaf' => false,
                'isDirectionDaa' => false,
                'isServiceAdmin' => true,
                'isServiceFinancier' => false,
                'administrativeSupervision' => [
                    'sessions' => (clone $siteSessions)->count(),
                    'currentSessions' => (clone $siteSessions)->where('is_current', true)->count(),
                    'enrollments' => (clone $siteEnrollments)->count(),
                    'activeEnrollments' => (clone $siteEnrollments)->where('status', 'active')->count(),
                    'students' => (clone $siteStudentProfiles)->count(),
                    'documents' => (clone $documents)->count(),
                    'rooms' => Room::whereIn('site_id', $siteIds)->count(),
                    'activeRooms' => Room::whereIn('site_id', $siteIds)->where('is_active', true)->count(),
                ],
            ]);
        }

        if ($user->hasAnyRole(['responsable_financier', 'comptable', 'caissier', 'agent_recouvrement'])) {
            $siteIds = $user->accessibleSiteIds();
            $siteEnrollments = Enrollment::query()->whereIn('site_id', $siteIds);
            $siteFees = Fee::query()->whereIn(
                'academic_session_id',
                AcademicSession::query()->whereIn('site_id', $siteIds)->select('id')
            );
            $siteStudentProfiles = StudentProfile::query()
                ->whereHas('enrollments', fn (Builder $query) => $query->whereIn('site_id', $siteIds));
            $validPayments = Payment::query()
                ->where('status', 'valide')
                ->whereHas('enrollment', fn (Builder $query) => $query->whereIn('site_id', $siteIds));
            $siteDiscounts = Discount::query()->where(function (Builder $query) use ($siteFees, $siteStudentProfiles): void {
                $query->whereIn('fee_id', (clone $siteFees)->select('id'))
                    ->orWhereIn('student_profile_id', (clone $siteStudentProfiles)->select('id'));
            });

            return view('admin.dashboard', [
                'isDirectionDaf' => false,
                'isDirectionDaa' => false,
                'isServiceAdmin' => false,
                'isServiceFinancier' => true,
                'financialSupervision' => [
                    'totalRevenue' => (clone $validPayments)->sum('amount_paid'),
                    'revenueThisMonth' => (clone $validPayments)
                        ->whereMonth('payment_date', now()->month)
                        ->whereYear('payment_date', now()->year)
                        ->sum('amount_paid'),
                    'paymentsToValidate' => Payment::query()
                        ->where('status', 'en_attente')
                        ->whereHas('enrollment', fn (Builder $query) => $query->whereIn('site_id', $siteIds))
                        ->count(),
                    'totalExpenses' => Expense::whereIn('site_id', $siteIds)->sum('amount'),
                    'feesToRecover' => (clone $siteFees)->whereIn('status', ['pending', 'partial'])->count(),
                    'discounts' => (clone $siteDiscounts)->count(),
                ],
            ]);
        }

        if ($user->hasRole('responsable_site')) {
            $dashboardSites = $user->sites()->orderBy('nom')->get(['sites.id', 'sites.nom']);
            $siteIds = $dashboardSites->modelKeys();
            $siteSessions = AcademicSession::query()->whereIn('site_id', $siteIds);
            $siteSessionIds = (clone $siteSessions)->select('id');
            $siteEnrollments = Enrollment::query()->whereIn('site_id', $siteIds);
            $siteCourses = Course::query()->whereIn('academic_session_id', $siteSessionIds);
            $siteEvaluations = Evaluation::query()->where(function (Builder $query) use ($siteSessionIds): void {
                $query->whereIn('session_id', $siteSessionIds)
                    ->orWhereHas('course', fn (Builder $courseQuery) => $courseQuery->whereIn('academic_session_id', $siteSessionIds));
            });
            $siteAttendances = Attendance::query()
                ->whereHas('course', fn (Builder $query) => $query->whereIn('academic_session_id', $siteSessionIds));
            $siteValidPayments = Payment::query()
                ->where('status', 'valide')
                ->whereHas('enrollment', fn (Builder $query) => $query->whereIn('site_id', $siteIds));
            $sitePendingPayments = Payment::query()
                ->where('status', 'en_attente')
                ->whereHas('enrollment', fn (Builder $query) => $query->whereIn('site_id', $siteIds));
            $siteExpenses = Expense::query()->whereIn('site_id', $siteIds);
            $siteAlumnis = Alumni::query()->whereIn('site_id', $siteIds);
            $sitePartnerships = Partnership::query()->where(function (Builder $query) use ($siteIds): void {
                if ($siteIds === []) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->whereIn('site_id', $siteIds)->orWhereNull('site_id');
            });
            $siteUsers = User::query()
                ->whereHas('sites', fn (Builder $query) => $query->whereIn('sites.id', $siteIds))
                ->with('roles')
                ->orderByDesc('created_at')
                ->limit(8)
                ->get();
            $totalUsers = User::query()
                ->whereHas('sites', fn (Builder $query) => $query->whereIn('sites.id', $siteIds))
                ->count();
            $activeUsers = User::query()
                ->where('is_active', true)
                ->whereHas('sites', fn (Builder $query) => $query->whereIn('sites.id', $siteIds))
                ->count();
            $rooms = Room::query()
                ->whereIn('site_id', $siteIds)
                ->orderBy('name')
                ->get();

            return view('admin.dashboard', [
                'isDirectionDaf' => false,
                'isDirectionDaa' => false,
                'isServiceAdmin' => false,
                'isServiceFinancier' => false,
                'isSiteManager' => true,
                'dashboardSites' => $dashboardSites,
                'siteUsers' => $siteUsers,
                'totalUsers' => $totalUsers,
                'activeUsers' => $activeUsers,
                'rooms' => $rooms,
                'totalRooms' => $rooms->count(),
                'activeRooms' => $rooms->where('is_active', true)->count(),
                'peopleSupervision' => [
                    'students' => (clone $siteEnrollments)->distinct('student_profile_id')->count('student_profile_id'),
                    'teachers' => TeacherProfile::query()
                        ->whereHas('user.sites', fn (Builder $query) => $query->whereIn('sites.id', $siteIds))
                        ->count(),
                ],
                'academicSupervision' => [
                    'sessions' => (clone $siteSessions)->count(),
                    'currentSessions' => (clone $siteSessions)->where('is_current', true)->count(),
                    'enrollments' => (clone $siteEnrollments)->count(),
                    'activeEnrollments' => (clone $siteEnrollments)->where('status', 'active')->count(),
                    'courses' => (clone $siteCourses)->count(),
                    'evaluations' => (clone $siteEvaluations)->count(),
                    'attendances' => (clone $siteAttendances)->count(),
                ],
                'financialSupervision' => [
                    'totalRevenue' => (clone $siteValidPayments)->sum('amount_paid'),
                    'revenueThisMonth' => (clone $siteValidPayments)
                        ->whereMonth('payment_date', now()->month)
                        ->whereYear('payment_date', now()->year)
                        ->sum('amount_paid'),
                    'paymentsToValidate' => (clone $sitePendingPayments)->count(),
                    'totalExpenses' => (clone $siteExpenses)->sum('amount'),
                ],
                'insertionSupervision' => [
                    'alumni' => (clone $siteAlumnis)->count(),
                    'visibleAlumni' => (clone $siteAlumnis)->publiclyVisible()->count(),
                    'publishedPosts' => AlumniPost::query()
                        ->where('status', 'published')
                        ->whereHas('alumni', fn (Builder $query) => $query->whereIn('site_id', $siteIds)->publiclyVisible())
                        ->count(),
                    'partnerships' => (clone $sitePartnerships)->count(),
                    'activePartnerships' => (clone $sitePartnerships)->whereIn('statut', ['Actif', 'actif'])->count(),
                ],
            ]);
        }

        $isSiteManager = false;
        $isDirectionDaf = false;
        $isDirectionDaa = false;
        $isServiceAdmin = false;
        $isServiceFinancier = false;

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
        $attendanceForToday = Attendance::query()
            ->whereDate('date', today());
        $todayAttendanceCount = (clone $attendanceForToday)->count();
        $todayAbsentCount = (clone $attendanceForToday)->where('status', 'absent')->count();
        $todayPresentCount = (clone $attendanceForToday)->where('status', 'present')->count();

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
        $monthExpr = DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', payment_date)"
            : "DATE_FORMAT(payment_date, '%Y-%m')";

        $paymentsChart = Payment::selectRaw("{$monthExpr} as month, SUM(amount_paid) as total")
            ->where('payment_date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.dashboard', compact(
            'isDirectionDaf',
            'isDirectionDaa',
            'isServiceAdmin',
            'isServiceFinancier',
            'isSiteManager',
            'totalStudents', 'totalTeachers', 'totalUsers', 'activeUsers',
            'totalRevenue', 'totalExpenses', 'pendingFees', 'partialFees',
            'revenueThisMonth', 'activeSession',
            'todayAttendanceCount', 'todayAbsentCount', 'todayPresentCount',
            'unreadNotifications', 'recentPayments',
            'latestUsers', 'paymentsChart',
        ));
    }
}
