<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\PaiementService;
use Illuminate\Contracts\View\View;

class FeeController extends Controller
{
    public function index(PaiementService $paiementService): View
    {
        $parent = auth()->user();
        $children = $parent->children()
            ->with([
                'studentProfile.enrollments' => fn ($query) => $query
                    ->with(['formation', 'academicSession', 'payments.studentProfile.user'])
                    ->orderByDesc('enrollment_date')
                    ->orderByDesc('id'),
            ])
            ->get();

        $childrenData = $children->map(function (User $child) use ($paiementService): array {
            $enrollments = $child->studentProfile?->enrollments ?? collect();

            return [
                'user' => $child,
                'enrollments' => $enrollments,
                'totalDue' => (float) $enrollments->sum(fn (Enrollment $enrollment) => (float) $enrollment->montant_du),
                'totalPaid' => (float) $enrollments->sum(fn (Enrollment $enrollment) => $paiementService->totalPaye($enrollment)),
                'balance' => (float) $enrollments->sum(fn (Enrollment $enrollment) => $paiementService->solde($enrollment)),
            ];
        });

        $payments = $childrenData
            ->flatMap(fn (array $child) => $child['enrollments']->flatMap(
                fn (Enrollment $enrollment) => $enrollment->payments->each(
                    fn ($payment) => $payment->setRelation('enrollment', $enrollment)
                )
            ))
            ->sortByDesc(fn ($payment) => $payment->date_paiement ?? $payment->payment_date)
            ->values();
        $totalFees = (float) $childrenData->sum(
            fn (array $child) => $child['enrollments']->sum(fn (Enrollment $enrollment) => (float) $enrollment->prix_formation)
        );
        $totalDue = (float) $childrenData->sum('totalDue');
        $totalDiscounts = max(0, $totalFees - $totalDue);
        $totalPaid = (float) $childrenData->sum('totalPaid');
        $totalBalance = (float) $childrenData->sum('balance');

        return view('parent.fees.index', compact(
            'childrenData', 'payments', 'totalFees', 'totalDiscounts', 'totalDue', 'totalPaid', 'totalBalance'
        ));
    }
}
