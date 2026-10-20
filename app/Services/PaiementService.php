<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Fee;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Service unique pour la gestion des montants dus, bourses, paiements et soldes.
 * Basé sur le modèle Enrollment (inscriptions) conformément au point 12.
 */
class PaiementService
{
    /**
     * Calcule le montant dû pour une inscription selon le prix et la bourse.
     * Formule : montant_du = max(0, prix_formation - bourse).
     */
    public function calculerMontantDu(float $prixFormation, string $bourseType = 'aucune', float $bourseValeur = 0.0): float
    {
        $prix = max(0.0, $prixFormation);
        $valeur = max(0.0, $bourseValeur);

        $deduction = match ($bourseType) {
            'pourcentage' => $prix * ($valeur / 100.0),
            'montant' => $valeur,
            default => 0.0,
        };

        return max(0.0, round($prix - $deduction, 2));
    }

    /**
     * Total payé pour une inscription (somme des versements).
     */
    public function totalPaye(Enrollment $enrollment): float
    {
        $payments = $enrollment->relationLoaded('payments')
            ? $enrollment->payments
            : $enrollment->payments()->get();

        return (float) $payments->sum(
            fn (Payment $payment) => (float) ($payment->montant ?? $payment->amount_paid ?? 0)
        );
    }

    /**
     * Solde restant dû pour une inscription.
     * = max(0, montant_du - totalPaye).
     */
    public function solde(Enrollment $enrollment): float
    {
        $montantDu = (float) $enrollment->montant_du;
        $totalPaye = $this->totalPaye($enrollment);

        return max(0.0, round($montantDu - $totalPaye, 2));
    }

    /**
     * Solde cumulé des inscriptions du même parcours pour l'étudiant.
     * Les créances restent attachées à leur inscription annuelle et ne sont pas recopiées.
     */
    public function soldeParcours(Enrollment $enrollment): float
    {
        $formation = $enrollment->loadMissing('formation.parcours')->formation;
        $enrollments = Enrollment::with(['formation.parcours', 'academicSession', 'payments'])
            ->where('student_profile_id', $enrollment->student_profile_id)
            ->when(
                $formation?->parcours_id,
                fn ($query, $parcoursId) => $query->whereHas('formation', fn ($formationQuery) => $formationQuery->where('parcours_id', $parcoursId)),
                fn ($query) => $query->where('formation_id', $enrollment->formation_id),
            )
            ->get();

        return round($enrollments->sum(fn (Enrollment $yearEnrollment) => $this->solde($yearEnrollment)), 2);
    }

    /**
     * Statut de paiement d'une inscription :
     * - 'impaye' : aucun paiement
     * - 'partiel' : payé partiellement
     * - 'solde'   : totalité réglée
     */
    public function statut(Enrollment $enrollment): string
    {
        $totalPaye = $this->totalPaye($enrollment);
        $montantDu = (float) $enrollment->montant_du;

        if ($montantDu <= 0 || $totalPaye >= $montantDu) {
            return 'solde';
        }

        if ($totalPaye > 0) {
            return 'partiel';
        }

        return 'impaye';
    }

    /**
     * Enregistre un paiement pour une inscription.
     * Interdit formellement un paiement supérieur au solde restant.
     *
     * @throws InvalidArgumentException
     */
    public function enregistrerPaiement(Enrollment $enrollment, array $data, ?int $enregistreParId = null): Payment
    {
        $montant = (float) ($data['montant'] ?? $data['amount_paid'] ?? 0.0);

        if ($montant <= 0) {
            throw new InvalidArgumentException('Le montant du paiement doit être supérieur à 0.');
        }

        $soldeRestant = $this->solde($enrollment);

        // Tolérance de 0.001 pour les arrondis en virgule flottante
        if ($montant > ($soldeRestant + 0.001)) {
            throw new InvalidArgumentException(
                sprintf('Le montant du paiement (%.2f) ne peut pas excéder le solde restant (%.2f).', $montant, $soldeRestant)
            );
        }

        $datePaiement = $data['date_paiement'] ?? $data['payment_date'] ?? now()->toDateString();
        $mode = $data['mode'] ?? 'especes';
        $userId = $enregistreParId ?? auth()->id();

        return DB::transaction(function () use ($enrollment, $montant, $datePaiement, $mode, $userId, $data): Payment {
            $payment = Payment::create([
                'inscription_id' => $enrollment->id,
                'student_profile_id' => $enrollment->student_profile_id,
                'montant' => $montant,
                'amount_paid' => $montant,
                'date_paiement' => $datePaiement,
                'payment_date' => $datePaiement,
                'mode' => $mode,
                'reference' => $data['reference'] ?? null,
                'enregistre_par' => $userId,
                'notes' => $data['notes'] ?? null,
            ]);

            if (blank($payment->reference)) {
                $payment->update([
                    'reference' => sprintf('IFAM-REC-%s-%06d', now()->format('Y'), $payment->id),
                ]);
            }

            return $payment;
        });
    }

    /**
     * Recalcule et corrige le montant dû d'une inscription selon sa formation et sa bourse.
     */
    public function recalculerInscription(Enrollment $enrollment): void
    {
        $prix = (float) ($enrollment->prix_formation ?: ($enrollment->formation->prix ?? 0.0));
        $montantDu = $this->calculerMontantDu($prix, $enrollment->bourse_type ?? 'aucune', (float) ($enrollment->bourse_valeur ?? 0.0));

        $enrollment->update([
            'prix_formation' => $prix,
            'montant_du' => $montantDu,
        ]);
    }

    /**
     * Méthode legacy maintenue pour la compatibilité avec l'ancien système de frais (Fee).
     */
    public function recalculateFeeStatus(Fee $fee): void
    {
        $totalPaid = $fee->payments()->sum('amount_paid');

        if ($totalPaid >= $fee->amount) {
            $fee->update(['status' => 'paid']);
        } elseif ($totalPaid > 0) {
            $fee->update(['status' => 'partial']);
        } else {
            $fee->update(['status' => 'pending']);
        }
    }
}
