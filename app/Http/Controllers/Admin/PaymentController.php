<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\StudentProfile;
use App\Services\NotificationService;
use App\Services\PaiementService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(protected PaiementService $paiementService) {}

    public function index(Request $request): View
    {
        $filters = $this->validatedPaymentFilters($request);
        $query = $this->filteredPaymentsQuery($filters);
        $filteredTotal = (float) (clone $query)->reorder()->sum(DB::raw('COALESCE(montant, amount_paid, 0)'));
        $payments = $query->paginate(15)->withQueryString();
        $students = StudentProfile::with('user')->get()->sortBy(fn ($s) => $s->user->last_name ?? '');

        return view('admin.payments.index', compact('payments', 'students', 'filteredTotal'));
    }

    public function printReport(Request $request): View
    {
        $filters = $this->validatedPaymentFilters($request);
        $payments = $this->filteredPaymentsQuery($filters)->get();
        $filteredTotal = (float) $payments->sum(
            fn (Payment $payment) => (float) ($payment->montant ?? $payment->amount_paid ?? 0)
        );

        return view('admin.payments.print', compact('payments', 'filteredTotal', 'filters'));
    }

    public function create(Request $request)
    {
        $allEnrollments = Enrollment::with(['studentProfile.user', 'formation.parcours', 'academicSession', 'payments'])
            ->get();

        $allEnrollments
            ->groupBy(fn (Enrollment $enrollment) => $enrollment->formation?->parcours_id
                ? 'student-'.$enrollment->student_profile_id.'-parcours-'.$enrollment->formation->parcours_id
                : 'student-'.$enrollment->student_profile_id.'-formation-'.$enrollment->formation_id)
            ->each(function ($pathEnrollments): void {
                $pathBalance = $pathEnrollments->sum(fn (Enrollment $enrollment) => $this->paiementService->solde($enrollment));

                foreach ($pathEnrollments as $enrollment) {
                    $enrollment->setAttribute('solde_parcours', $pathBalance);
                }
            });

        $enrollments = $allEnrollments
            ->filter(fn ($e) => $this->paiementService->solde($e) > 0 || $e->id == $request->inscription_id)
            ->values();

        $selectedInscriptionId = $request->inscription_id;

        return view('admin.payments.create', compact('enrollments', 'selectedInscriptionId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'inscription_id' => 'required|exists:enrollments,id',
            'montant' => 'required|numeric|min:0.01',
            'date_paiement' => 'required|date',
            'mode' => 'required|string|max:50',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $enrollment = Enrollment::findOrFail($validated['inscription_id']);

        try {
            $payment = $this->paiementService->enregistrerPaiement($enrollment, $validated, auth()->id());
            try {
                app(NotificationService::class)->paiementEnregistre($payment);
            } catch (Throwable $e) {
                report($e);
            }
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors([
                'montant' => $e->getMessage(),
            ]);
        }

        return redirect()->route('admin.payments.receipt', $payment)
            ->with('success', 'Paiement enregistré avec succès.');
    }

    public function receipt(Payment $payment): View
    {
        $payment->load([
            'enrollment.studentProfile.user',
            'enrollment.formation',
            'enrollment.academicSession',
            'studentProfile.user',
            'enregistrePar',
        ]);
        $remainingBalance = $payment->enrollment
            ? $this->paiementService->solde($payment->enrollment)
            : null;
        $amount = (float) ($payment->montant ?? $payment->amount_paid ?? 0);
        $amountInWords = $this->amountInWords((int) round($amount));

        return view('admin.payments.receipt', compact('payment', 'remainingBalance', 'amountInWords'));
    }

    private function amountInWords(int $amount): string
    {
        if ($amount < 0) {
            return 'moins '.$this->positiveIntegerInFrench(abs($amount)).' francs CFA';
        }

        return ucfirst($this->positiveIntegerInFrench($amount)).' francs CFA';
    }

    private function positiveIntegerInFrench(int $number): string
    {
        $units = [
            'zéro', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit',
            'neuf', 'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize',
        ];

        if ($number < 17) {
            return $units[$number];
        }

        if ($number < 20) {
            return 'dix-'.$units[$number - 10];
        }

        if ($number < 70) {
            $tens = [2 => 'vingt', 3 => 'trente', 4 => 'quarante', 5 => 'cinquante', 6 => 'soixante'];
            $ten = intdiv($number, 10);
            $unit = $number % 10;

            return $tens[$ten].($unit === 0 ? '' : ($unit === 1 ? ' et un' : '-'.$units[$unit]));
        }

        if ($number < 80) {
            return $number === 71 ? 'soixante et onze' : 'soixante-'.$this->positiveIntegerInFrench($number - 60);
        }

        if ($number < 100) {
            return $number === 80 ? 'quatre-vingts' : 'quatre-vingt-'.$this->positiveIntegerInFrench($number - 80);
        }

        if ($number < 1000) {
            $hundreds = intdiv($number, 100);
            $remainder = $number % 100;
            $words = $hundreds === 1 ? 'cent' : $units[$hundreds].' cent';

            if ($remainder === 0 && $hundreds > 1) {
                return $words.'s';
            }

            return $words.($remainder > 0 ? ' '.$this->positiveIntegerInFrench($remainder) : '');
        }

        if ($number < 1_000_000) {
            $thousands = intdiv($number, 1000);
            $remainder = $number % 1000;
            $words = $thousands === 1 ? 'mille' : $this->positiveIntegerInFrench($thousands).' mille';

            return $words.($remainder > 0 ? ' '.$this->positiveIntegerInFrench($remainder) : '');
        }

        $millions = intdiv($number, 1_000_000);
        $remainder = $number % 1_000_000;
        $words = $this->positiveIntegerInFrench($millions).' million'.($millions > 1 ? 's' : '');

        return $words.($remainder > 0 ? ' '.$this->positiveIntegerInFrench($remainder) : '');
    }

    /**
     * @return array{student_id?: int|string|null, mode?: string|null, date_from?: string|null, date_to?: string|null}
     */
    private function validatedPaymentFilters(Request $request): array
    {
        return $request->validate([
            'student_id' => ['nullable', 'integer', Rule::exists('student_profiles', 'id')],
            'mode' => ['nullable', 'string', 'max:50'],
            'date_from' => ['nullable', 'date_format:Y-m-d', Rule::when($request->filled('date_to'), ['before_or_equal:date_to'])],
            'date_to' => ['nullable', 'date_format:Y-m-d', Rule::when($request->filled('date_from'), ['after_or_equal:date_from'])],
        ]);
    }

    /**
     * @param  array{student_id?: int|string|null, mode?: string|null, date_from?: string|null, date_to?: string|null}  $filters
     * @return Builder<Payment>
     */
    private function filteredPaymentsQuery(array $filters): Builder
    {
        $legacyModes = [
            'especes' => 'cash',
            'virement' => 'bank_transfer',
            'carte' => 'card',
            'cheque' => 'check',
            'autre' => 'other',
        ];

        return Payment::query()
            ->with([
                'enrollment.studentProfile.user',
                'enrollment.formation',
                'enrollment.academicSession',
                'studentProfile.user',
                'enregistrePar',
            ])
            ->when($filters['student_id'] ?? null, function (Builder $query, int|string $studentId): void {
                $query->where(function (Builder $query) use ($studentId): void {
                    $query->where('student_profile_id', $studentId)
                        ->orWhereHas('enrollment', fn (Builder $enrollmentQuery) => $enrollmentQuery->where('student_profile_id', $studentId));
                });
            })
            ->when($filters['mode'] ?? null, function (Builder $query, string $mode) use ($legacyModes): void {
                $query->where(function (Builder $query) use ($mode, $legacyModes): void {
                    $query->where('mode', $mode);

                    if (isset($legacyModes[$mode])) {
                        $query->orWhere(function (Builder $query) use ($mode, $legacyModes): void {
                            $query->whereNull('mode')->where('method', $legacyModes[$mode]);
                        });
                    }
                });
            })
            ->when($filters['date_from'] ?? null, function (Builder $query, string $dateFrom): void {
                $query->where(function (Builder $query) use ($dateFrom): void {
                    $query->whereDate('date_paiement', '>=', $dateFrom)
                        ->orWhere(function (Builder $query) use ($dateFrom): void {
                            $query->whereNull('date_paiement')->whereDate('payment_date', '>=', $dateFrom);
                        });
                });
            })
            ->when($filters['date_to'] ?? null, function (Builder $query, string $dateTo): void {
                $query->where(function (Builder $query) use ($dateTo): void {
                    $query->whereDate('date_paiement', '<=', $dateTo)
                        ->orWhere(function (Builder $query) use ($dateTo): void {
                            $query->whereNull('date_paiement')->whereDate('payment_date', '<=', $dateTo);
                        });
                });
            })
            ->orderByDesc(DB::raw('COALESCE(date_paiement, payment_date)'))
            ->orderByDesc('id');
    }

    public function edit(Payment $payment)
    {
        $payment->load(['enrollment.studentProfile.user', 'enrollment.formation']);
        $enrollments = Enrollment::with(['studentProfile.user', 'formation'])->get();

        return view('admin.payments.edit', compact('payment', 'enrollments'));
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'montant' => 'required|numeric|min:0.01',
            'date_paiement' => 'required|date',
            'mode' => 'required|string|max:50',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $enrollment = $payment->enrollment;
        if ($enrollment) {
            // Le solde sans ce versement
            $soldeSansCePaiement = $this->paiementService->solde($enrollment) + (float) ($payment->montant ?? $payment->amount_paid ?? 0.0);
            if ((float) $validated['montant'] > ($soldeSansCePaiement + 0.001)) {
                return back()->withInput()->withErrors([
                    'montant' => sprintf('Le montant (%.2f €) ne peut pas excéder le solde restant disponible (%.2f €).', $validated['montant'], $soldeSansCePaiement),
                ]);
            }
        }

        $payment->update([
            'montant' => $validated['montant'],
            'amount_paid' => $validated['montant'],
            'date_paiement' => $validated['date_paiement'],
            'payment_date' => $validated['date_paiement'],
            'mode' => $validated['mode'],
            'reference' => $validated['reference'],
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('admin.payments.index')
            ->with('success', 'Paiement mis à jour avec succès.');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()->route('admin.payments.index')
            ->with('success', 'Paiement supprimé avec succès.');
    }
}
