<?php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Expense;
use App\Models\Formation;
use App\Models\Payment;
use App\Services\PaiementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FinanceController extends Controller
{
    public function index(Request $request, PaiementService $paiementService): View
    {
        $filters = $request->validate([
            'session_id' => ['nullable', 'integer', Rule::exists('academic_sessions', 'id')],
            'formation_id' => ['nullable', 'integer', Rule::exists('formations', 'id')],
            'date_from' => ['nullable', 'date_format:Y-m-d', Rule::when($request->filled('date_to'), ['before_or_equal:date_to'])],
            'date_to' => ['nullable', 'date_format:Y-m-d', Rule::when($request->filled('date_from'), ['after_or_equal:date_from'])],
        ]);
        $sessionId = $filters['session_id'] ?? null;
        $selectedSession = $sessionId ? AcademicSession::findOrFail($sessionId) : null;
        $formationId = $filters['formation_id'] ?? null;
        $selectedFormation = $formationId ? Formation::findOrFail($formationId) : null;
        $dateFrom = $filters['date_from'] ?? $selectedSession?->start_date?->toDateString();
        $dateTo = $filters['date_to'] ?? $selectedSession?->end_date?->toDateString() ?? today()->toDateString();

        $paymentQuery = Payment::query()
            ->whereNotNull('inscription_id')
            ->whereHas('enrollment', fn ($query) => $query->when(
                $sessionId,
                fn ($enrollmentQuery) => $enrollmentQuery->where('academic_session_id', $sessionId)
            )->when(
                $formationId,
                fn ($enrollmentQuery) => $enrollmentQuery->where('formation_id', $formationId)
            ));
        $paymentQuery
            ->when($dateFrom, fn ($query) => $query->whereDate('date_paiement', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('date_paiement', '<=', $dateTo));
        $totalRevenue = (float) (clone $paymentQuery)
            ->sum(DB::raw('COALESCE(montant, amount_paid, 0)'));
        $expenseQuery = Expense::query();
        $expenseQuery
            ->when($dateFrom, fn ($query) => $query->whereDate('expense_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('expense_date', '<=', $dateTo));
        $totalExpenses = (clone $expenseQuery)->sum('amount');
        $balance = $totalRevenue - $totalExpenses;

        $enrollments = Enrollment::query()
            ->with('payments')
            ->when($sessionId, fn ($query) => $query->where('academic_session_id', $sessionId))
            ->when($formationId, fn ($query) => $query->where('formation_id', $formationId))
            ->get();
        $pendingFees = $enrollments->filter(fn (Enrollment $enrollment) => $paiementService->solde($enrollment) > 0)->count();
        $overdueSum = (float) $enrollments->sum(fn (Enrollment $enrollment) => $paiementService->solde($enrollment));

        $recentPayments = (clone $paymentQuery)
            ->with(['studentProfile.user', 'enrollment.formation', 'enrollment.academicSession'])
            ->orderByDesc('date_paiement')
            ->orderByDesc('payment_date')
            ->limit(10)
            ->get();

        $recentExpenses = (clone $expenseQuery)->with('category')
            ->orderByDesc('expense_date')
            ->limit(10)
            ->get();

        $revenueByFormation = (clone $paymentQuery)
            ->join('enrollments', 'payments.inscription_id', '=', 'enrollments.id')
            ->join('formations', 'enrollments.formation_id', '=', 'formations.id')
            ->selectRaw('formations.name as formation_name, SUM(COALESCE(payments.montant, payments.amount_paid, 0)) as total')
            ->groupBy('formations.id', 'formations.name')
            ->orderByDesc('total')
            ->get();
        $sessions = AcademicSession::orderByDesc('start_date')->get();
        $formations = Formation::orderBy('name')->get();

        return view('direction.finance.index', compact(
            'totalRevenue', 'totalExpenses', 'balance',
            'pendingFees', 'overdueSum',
            'recentPayments', 'recentExpenses', 'revenueByFormation', 'sessions', 'formations',
            'sessionId', 'selectedSession', 'formationId', 'selectedFormation', 'dateFrom', 'dateTo'
        ));
    }
}
