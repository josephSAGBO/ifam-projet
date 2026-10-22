<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HasSiteFilter;
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
    use HasSiteFilter;

    public function __construct(protected PaiementService $paiementService) {}

    public function index(Request $request): View
    {
        ['sites' => $sites, 'selectedSiteId' => $selectedSiteId] = $this->siteFilter($request);
        if (auth()->check() && ! auth()->user()->can('view payments') && ! auth()->user()->hasAnyRole(['administrateur', 'direction', 'direction_daf'])) {
            abort(403, 'Accès interdit : vous n\'avez pas les permissions pour consulter les paiements financiers.');
        }

        $filters = $this->validatedPaymentFilters($request);
        $query = $this->filteredPaymentsQuery($filters);
        if ($selectedSiteId !== null) {
            $query->whereHas('enrollment', fn ($q) => $q->where('site_id', $selectedSiteId));
        }
        $filteredTotal = (float) (clone $query)->reorder()->sum(DB::raw('COALESCE(montant, amount_paid, 0)'));
        $payments = $query->paginate(15)->withQueryString();
        $studentsQuery = StudentProfile::query()->with('user');
        if ($selectedSiteId !== null) {
            $studentsQuery->whereHas('enrollments', fn ($q) => $q->where('site_id', $selectedSiteId));
        }
        $students = $studentsQuery->get()->sortBy(fn ($s) => $s->user->last_name ?? '');

        return view('admin.payments.index', compact('payments', 'students', 'filteredTotal', 'sites', 'selectedSiteId'));
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
        if (auth()->check() && ! auth()->user()->can('create payments') && ! auth()->user()->hasAnyRole(['administrateur', 'direction', 'direction_daf'])) {
            abort(403, "Vous n'avez pas la permission d'enregistrer des paiements.");
        }

        $query = Enrollment::with(['studentProfile.user', 'formation.parcours', 'academicSession', 'payments']);
        if (auth()->check() && ! auth()->user()->hasAnyRole(['administrateur', 'direction', 'direction_daf'])) {
            $query->whereIn('site_id', auth()->user()->accessibleSiteIds());
        }

        $allEnrollments = $query->get();

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
        if (! $request->user()->can('create payments') && ! $request->user()->hasAnyRole(['administrateur', 'direction', 'direction_daf'])) {
            abort(403, "Vous n'avez pas la permission d'enregistrer des paiements.");
        }

        $validated = $request->validate([
            'inscription_id' => 'required|exists:enrollments,id',
            'montant' => 'required|numeric|min:0.01',
            'date_paiement' => 'required|date',
            'mode' => 'required|string|max:50',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $enrollment = Enrollment::findOrFail($validated['inscription_id']);
        if (! $request->user()->hasSiteAccess($enrollment->site_id)) {
            abort(403, 'Accès interdit : vous ne pouvez enregistrer un paiement que pour votre site.');
        }

        if ($request->user()->can('validate financial operations') || $request->user()->hasRole('administrateur')) {
            $validated['status'] = 'valide';
            $validated['validated_by'] = auth()->id();
            $validated['validated_at'] = now();
        } else {
            $validated['status'] = 'en_attente';
            $validated['validated_by'] = null;
            $validated['validated_at'] = null;
        }

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
        if ($payment->enrollment && ! auth()->user()->hasSiteAccess($payment->enrollment->site_id)) {
            abort(403, 'Accès interdit à ce reçu de paiement.');
        }

        return $this->renderReceipt($payment, 'admin');
    }

    public function studentReceipt(Payment $payment): View
    {
        $studentProfile = auth()->user()->studentProfile;
        $payment->loadMissing('enrollment');

        abort_unless(
            $payment->isReceiptPublished()
                && $studentProfile
                && (int) $payment->enrollment?->student_profile_id === (int) $studentProfile->id,
            404
        );

        return $this->renderReceipt($payment, 'student');
    }

    public function parentReceipt(Payment $payment): View
    {
        $payment->loadMissing('enrollment');
        $studentProfileId = $payment->enrollment?->student_profile_id;
        $parentHasAccess = $studentProfileId !== null
            && auth()->user()->children()
                ->whereHas('studentProfile', fn ($query) => $query->whereKey($studentProfileId))
                ->exists();

        abort_unless($payment->isReceiptPublished() && $parentHasAccess, 404);

        return $this->renderReceipt($payment, 'parent');
    }

    public function publishReceipt(Payment $payment): RedirectResponse
    {
        $this->authorizeReceiptPublication($payment);
        abort_unless($payment->estValide(), 403, 'Seul un paiement validé peut être publié.');

        $payment->forceFill([
            'receipt_published_at' => now(),
            'receipt_published_by' => auth()->id(),
        ])->save();

        return back()->with('success', 'Le reçu est maintenant publié dans les espaces privés de l’étudiant et de ses parents.');
    }

    public function unpublishReceipt(Payment $payment): RedirectResponse
    {
        $this->authorizeReceiptPublication($payment);

        $payment->forceFill([
            'receipt_published_at' => null,
            'receipt_published_by' => null,
        ])->save();

        return back()->with('success', 'La publication du reçu a été retirée.');
    }

    private function authorizeReceiptPublication(Payment $payment): void
    {
        $user = auth()->user();
        abort_unless(
            $user->can('print receipts') || $user->hasRole('administrateur'),
            403,
            'Vous n’avez pas la permission de publier les reçus.'
        );
        abort_unless($payment->enrollment, 403, 'Ce paiement n’est lié à aucune inscription.');

        if (! $user->hasSiteAccess($payment->enrollment->site_id)) {
            abort(403, 'Accès interdit à ce reçu de paiement.');
        }
    }

    private function renderReceipt(Payment $payment, string $audience): View
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

        return view('admin.payments.receipt', compact('payment', 'remainingBalance', 'amountInWords', 'audience'));
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
                'validateur',
            ])
            ->when(auth()->check() && ! auth()->user()->hasAnyRole(['administrateur', 'direction', 'direction_daf']), function (Builder $query): void {
                $accessibleSiteIds = auth()->user()->accessibleSiteIds();
                $query->whereHas('enrollment', fn (Builder $eq) => $eq->whereIn('site_id', $accessibleSiteIds));
            })
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
        if ($payment->enrollment && ! auth()->user()->hasSiteAccess($payment->enrollment->site_id)) {
            abort(403, 'Accès interdit : ce paiement ne relève pas de votre site.');
        }

        if ($payment->estValide() && ! auth()->user()->can('validate financial operations') && ! auth()->user()->hasRole('administrateur')) {
            abort(403, 'Un paiement validé ne peut pas être modifié sans habilitation supérieure.');
        }

        $payment->load(['enrollment.studentProfile.user', 'enrollment.formation']);
        $enrollments = Enrollment::with(['studentProfile.user', 'formation'])->get();

        return view('admin.payments.edit', compact('payment', 'enrollments'));
    }

    public function update(Request $request, Payment $payment)
    {
        if ($payment->enrollment && ! auth()->user()->hasSiteAccess($payment->enrollment->site_id)) {
            abort(403, 'Accès interdit : ce paiement ne relève pas de votre site.');
        }

        if ($payment->estValide() && ! auth()->user()->can('validate financial operations') && ! auth()->user()->hasRole('administrateur')) {
            abort(403, 'Un paiement validé ne peut pas être modifié sans habilitation supérieure.');
        }

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
        if ($payment->enrollment && ! auth()->user()->hasSiteAccess($payment->enrollment->site_id)) {
            abort(403, 'Accès interdit : ce paiement ne relève pas de votre site.');
        }

        if ($payment->estValide() && ! auth()->user()->can('validate financial operations') && ! auth()->user()->hasRole('administrateur')) {
            abort(403, 'Un paiement validé ne peut pas être supprimé sans habilitation supérieure.');
        }

        $payment->delete();

        return redirect()->route('admin.payments.index')
            ->with('success', 'Paiement supprimé avec succès.');
    }

    public function validatePayment(Request $request, Payment $payment): RedirectResponse
    {
        if (! auth()->user()->can('validate financial operations') && ! auth()->user()->hasRole('administrateur')) {
            abort(403, 'Accès interdit : vous ne disposez pas des droits de validation financière.');
        }

        $payment->update([
            'status' => 'valide',
            'validated_by' => auth()->id(),
            'validated_at' => now(),
            'validation_notes' => $request->input('validation_notes'),
        ]);

        return back()->with('success', 'Paiement validé avec succès.');
    }

    public function rejectPayment(Request $request, Payment $payment): RedirectResponse
    {
        if (! auth()->user()->can('validate financial operations') && ! auth()->user()->hasRole('administrateur')) {
            abort(403, 'Accès interdit : vous ne disposez pas des droits de validation financière.');
        }

        $payment->update([
            'status' => 'rejete',
            'validated_by' => auth()->id(),
            'validated_at' => now(),
            'validation_notes' => $request->input('validation_notes'),
        ]);

        return back()->with('success', 'Paiement rejeté.');
    }
}
