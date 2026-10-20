<?php

namespace App\Http\Controllers\Admin;

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
    public function __construct(private readonly PaiementService $paiementService) {}

    public function index(Request $request): View
    {
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
            ->when(
                $validated['session_id'] ?? null,
                fn (Builder $query, string $sessionId) => $query->where('academic_session_id', $sessionId)
            )
            ->when(
                $validated['student_id'] ?? null,
                fn (Builder $query, string $studentId) => $query->where('student_profile_id', $studentId)
            );

        if (! empty($validated['status'])) {
            $this->applyPaymentStatusFilter($query, $validated['status']);
        }

        $enrollments = $query
            ->orderByDesc('enrollment_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $sessions = AcademicSession::orderByDesc('start_date')->get();
        $students = StudentProfile::query()
            ->whereHas('enrollments')
            ->with('user')
            ->get()
            ->sortBy(fn (StudentProfile $student) => $student->user?->last_name ?? '')
            ->values();

        return view('admin.fees.index', compact('enrollments', 'sessions', 'students'));
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
