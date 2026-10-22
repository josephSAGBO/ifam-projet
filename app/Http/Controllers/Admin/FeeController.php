<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HasSiteFilter;
use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\StudentProfile;
use App\Services\PaiementService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    use HasSiteFilter;

    public function __construct(private readonly PaiementService $paiementService) {}

    public function index(Request $request): View
    {
        ['sites' => $sites, 'selectedSiteId' => $selectedSiteId] = $this->siteFilter($request);
        if (auth()->check() && ! auth()->user()->can('view fees') && ! auth()->user()->hasAnyRole(['administrateur', 'direction', 'direction_daf'])) {
            abort(403, 'Accès interdit : vous n\'avez pas les permissions pour consulter les frais financiers.');
        }

        $validated = $request->validate([
            'session_id' => ['nullable', 'integer', 'exists:academic_sessions,id'],
            'student_id' => ['nullable', 'integer', 'exists:student_profiles,id'],
            'status' => ['nullable', 'in:impaye,partiel,solde'],
        ]);

        $query = Enrollment::with([
            'studentProfile.user',
            'formation.parcours',
            'academicSession',
            'payments',
        ])
            ->when(auth()->check() && ! auth()->user()->hasAnyRole(['administrateur', 'direction', 'direction_daf']), function (Builder $query): void {
                $query->whereIn('site_id', auth()->user()->accessibleSiteIds());
            })
            ->when(
                $validated['session_id'] ?? null,
                fn (Builder $query, string $sessionId) => $query->where('academic_session_id', $sessionId)
            )
            ->when(
                $validated['student_id'] ?? null,
                fn (Builder $query, string $studentId) => $query->where('student_profile_id', $studentId)
            );
        if ($selectedSiteId !== null) {
            $query->where('site_id', $selectedSiteId);
        }

        if (! empty($validated['status'])) {
            $this->applyPaymentStatusFilter($query, $validated['status']);
        }

        $enrollments = $query
            ->orderByDesc('enrollment_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $sessionsQuery = AcademicSession::orderByDesc('start_date');
        if ($selectedSiteId !== null) {
            $sessionsQuery->where('site_id', $selectedSiteId);
        }
        $sessions = $sessionsQuery->get();
        $studentsQuery = StudentProfile::query()->whereHas('enrollments');
        if ($selectedSiteId !== null) {
            $studentsQuery->whereHas('enrollments', fn ($q) => $q->where('site_id', $selectedSiteId));
        }
        if (auth()->check() && ! auth()->user()->hasAnyRole(['administrateur', 'direction', 'direction_daf'])) {
            $studentsQuery->whereHas('enrollments', fn ($q) => $q->whereIn('site_id', auth()->user()->accessibleSiteIds()));
        }
        $students = $studentsQuery
            ->with('user')
            ->get()
            ->sortBy(fn (StudentProfile $student) => $student->user?->last_name ?? '')
            ->values();

        return view('admin.fees.index', compact('enrollments', 'sessions', 'students', 'sites', 'selectedSiteId'));
    }

    private function applyPaymentStatusFilter(Builder $query, string $status): void
    {
        $totalPaidSql = '(SELECT COALESCE(NULLIF(SUM(payments.montant), 0), SUM(payments.amount_paid), 0) '
            .'FROM payments WHERE payments.inscription_id = enrollments.id)';

        match ($status) {
            'impaye' => $query->whereRaw("enrollments.montant_du > 0 AND {$totalPaidSql} <= 0"),
            'partiel' => $query->whereRaw("enrollments.montant_du > {$totalPaidSql} AND {$totalPaidSql} > 0"),
            'solde' => $query->whereRaw("(enrollments.montant_du <= 0 OR {$totalPaidSql} >= enrollments.montant_du)"),
        };
    }
}
