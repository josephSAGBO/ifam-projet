<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'inscription_id',
        'montant',
        'date_paiement',
        'mode',
        'reference',
        'enregistre_par',
        'notes',
        'status',
        'validated_by',
        'validated_at',
        'validation_notes',
        'receipt_published_at',
        'receipt_published_by',
        // Colonnes legacy maintenues pour compatibilité
        'fee_id',
        'student_profile_id',
        'amount_paid',
        'payment_date',
        'method',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'date_paiement' => 'date',
        'payment_date' => 'date',
        'validated_at' => 'datetime',
        'receipt_published_at' => 'datetime',
    ];

    public function estValide(): bool
    {
        return $this->status === 'valide';
    }

    public function estEnAttente(): bool
    {
        return $this->status === 'en_attente';
    }

    public function estRejete(): bool
    {
        return $this->status === 'rejete';
    }

    public function isReceiptPublished(): bool
    {
        return $this->estValide() && $this->receipt_published_at !== null;
    }

    protected static function booted()
    {
        static::saving(function (Payment $payment) {
            // Synchronisation automatique montant <-> amount_paid
            if ($payment->montant !== null && $payment->amount_paid === null) {
                $payment->amount_paid = $payment->montant;
            } elseif ($payment->amount_paid !== null && $payment->montant === null) {
                $payment->montant = $payment->amount_paid;
            }

            // Synchronisation date_paiement <-> payment_date
            if ($payment->date_paiement !== null && $payment->payment_date === null) {
                $payment->payment_date = $payment->date_paiement;
            } elseif ($payment->payment_date !== null && $payment->date_paiement === null) {
                $payment->date_paiement = $payment->payment_date;
            }

            // Synchronisation mode <-> method
            if ($payment->mode !== null && $payment->method === null) {
                $payment->method = match ($payment->mode) {
                    'especes' => 'cash',
                    'virement' => 'bank_transfer',
                    'carte' => 'card',
                    'cheque' => 'check',
                    default => 'other',
                };
            } elseif ($payment->method !== null && $payment->mode === null) {
                $payment->mode = match ($payment->method) {
                    'cash' => 'especes',
                    'bank_transfer' => 'virement',
                    'card' => 'carte',
                    'check' => 'cheque',
                    default => 'autre',
                };
            }

            // Si inscription_id est fourni, synchroniser student_profile_id
            if ($payment->inscription_id && ! $payment->student_profile_id) {
                $enrollment = Enrollment::find($payment->inscription_id);
                if ($enrollment) {
                    $payment->student_profile_id = $enrollment->student_profile_id;
                }
            }
        });
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'inscription_id');
    }

    public function inscription(): BelongsTo
    {
        return $this->enrollment();
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class);
    }

    public function enregistrePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enregistre_par');
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function receiptPublisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receipt_published_by');
    }
}
