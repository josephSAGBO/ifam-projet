<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Services\PaiementService;
use Illuminate\Contracts\View\View;

class FeeController extends Controller
{
    public function index(PaiementService $paiementService): View
    {
        $student = auth()->user()->studentProfile;

        $enrollments = $student
            ? $student->enrollments()
                ->with(['formation', 'academicSession', 'payments'])
                ->orderByDesc('enrollment_date')
                ->orderByDesc('id')
                ->get()
            : collect();

        $payments = $enrollments
            ->flatMap(fn (Enrollment $enrollment) => $enrollment->payments->each(
                fn ($payment) => $payment->setRelation('enrollment', $enrollment)
            ))
            ->sortByDesc(fn ($payment) => $payment->date_paiement ?? $payment->payment_date)
            ->values();
        $totalFees = (float) $enrollments->sum(fn (Enrollment $enrollment) => (float) $enrollment->prix_formation);
        $netDue = (float) $enrollments->sum(fn (Enrollment $enrollment) => (float) $enrollment->montant_du);
        $totalDiscounts = max(0, $totalFees - $netDue);
        $totalPaid = (float) $enrollments->sum(fn (Enrollment $enrollment) => $paiementService->totalPaye($enrollment));
        $balance = (float) $enrollments->sum(fn (Enrollment $enrollment) => $paiementService->solde($enrollment));

        return view('student.fees.index', compact(
            'enrollments', 'payments', 'totalFees', 'totalDiscounts', 'netDue', 'totalPaid', 'balance'
        ));
    }
}
