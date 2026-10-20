@extends('layouts.admin')

@section('title', 'Reçu de paiement')

@section('content')
@php
    $student = $payment->enrollment?->studentProfile?->user ?? $payment->studentProfile?->user;
    $enrollment = $payment->enrollment;
    $amount = (float) ($payment->montant ?? $payment->amount_paid ?? 0);
    $paymentDate = $payment->date_paiement ?? $payment->payment_date;
    $receiptReference = $payment->reference ?? 'REC-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT);
    $formationName = $enrollment?->formation?->name ?? 'Scolarité';
    $sessionName = $enrollment?->academicSession?->name ?? '—';
    $paymentMode = $payment->mode ?? $payment->method ?? '—';
@endphp

<style>
    .receipt-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        max-width: 760px;
        margin: 0 auto 1.25rem;
    }
    .receipt-actions a {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        color: var(--color-primary);
        text-decoration: none;
        font-weight: 700;
        font-size: 0.875rem;
    }
    .receipt-actions button {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.625rem 1.125rem;
        border: 0;
        border-radius: 0.625rem;
        background: var(--color-primary-container);
        color: var(--color-on-primary);
        font-weight: 700;
        font-size: 0.875rem;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: opacity 0.15s ease;
    }
    .receipt-actions button:hover {
        opacity: 0.92;
    }

    /* Cadre du reçu calqué exactement sur 901 x 886 */
    .receipt-paper {
        position: relative;
        width: min(100%, 760px);
        aspect-ratio: 901 / 886;
        margin: 0 auto;
        overflow: hidden;
        background: #fff;
        color: #0b1329;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.12);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
    }

    .receipt-paper__template {
        position: absolute;
        inset: 0;
        display: block;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 1;
    }

    /* Masque blanc propre pour le titre avec le N° de référence exact */
    .receipt-paper__title-mask {
        position: absolute;
        z-index: 2;
        top: 21.0%;
        left: 2.8%;
        width: 92%;
        height: 5.6%;
        background: #fff;
    }
    .receipt-paper__title {
        position: absolute;
        z-index: 3;
        top: 21.2%;
        left: 3.0%;
        margin: 0;
        font-size: clamp(12px, 2.6vw, 19.5px);
        font-weight: 900;
        letter-spacing: 0.03em;
        line-height: 1.25;
        text-decoration: underline;
        text-transform: uppercase;
        color: #000;
    }

    /* Valeurs de champs superposées avec alignement parfait sur les pointillés */
    .receipt-paper__field {
        position: absolute;
        z-index: 3;
        overflow: hidden;
        color: #0f172a;
        font-size: clamp(8.5px, 1.5vw, 12px);
        line-height: 1.2;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .receipt-paper__student {
        top: 32.2%;
        left: 34.5%;
        width: 59%;
        font-weight: 800;
        text-transform: uppercase;
    }
    .receipt-paper__amount {
        top: 36.6%;
        left: 28.5%;
        width: 65%;
        font-weight: 800;
        color: #047857;
    }
    .receipt-paper__reason {
        top: 41.0%;
        left: 15.0%;
        width: 78%;
        font-weight: 600;
    }

    /* Encadrés gris clair pour les détails complémentaires (45.7% et 50.8%) */
    .receipt-paper__detail-one,
    .receipt-paper__detail-two {
        left: 6.0%;
        width: 88%;
        height: 4.2%;
        display: flex;
        align-items: center;
        padding: 0 1.2%;
        box-sizing: border-box;
        font-size: clamp(7.5px, 1.3vw, 10.5px);
        font-weight: 600;
        color: #1e293b;
    }
    .receipt-paper__detail-one {
        top: 45.8%;
    }
    .receipt-paper__detail-two {
        top: 50.9%;
    }

    /* Somme en lettres sous les boîtes */
    .receipt-paper__amount-words {
        top: 56.4%;
        left: 5.5%;
        width: 88%;
        font-size: clamp(7.5px, 1.3vw, 10.5px);
        font-weight: 700;
        font-style: italic;
        color: #334155;
    }

    /* Renseignements payeur et caissier */
    .receipt-paper__payer {
        top: 59.5%;
        left: 27.5%;
        width: 24%;
        font-weight: 600;
    }
    .receipt-paper__relationship {
        top: 59.5%;
        left: 59.5%;
        width: 32%;
        font-weight: 600;
    }

    .receipt-paper__cashier {
        top: 63.9%;
        left: 28.0%;
        width: 27%;
        font-weight: 600;
    }
    .receipt-paper__phone {
        top: 63.9%;
        left: 64.0%;
        width: 27%;
        font-weight: 600;
    }

    /* Reste à payer */
    .receipt-paper__balance {
        top: 68.3%;
        left: 27.5%;
        width: 45%;
        font-weight: 800;
        color: #b91c1c;
    }

    /* Date en bas à gauche */
    .receipt-paper__date {
        top: 76.6%;
        left: 11.5%;
        width: 22%;
        font-weight: 700;
    }

    /* Cachet comptable IFAM superposé en bas à droite */
    .receipt-paper__accounting-stamp {
        position: absolute;
        z-index: 4;
        top: 75.5%;
        left: 66%;
        width: 30%;
        height: 21%;
        object-fit: contain;
        mix-blend-mode: multiply;
        pointer-events: none;
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 0;
        }
        body {
            background: #fff !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        #sidebar,
        #admin-sidebar-backdrop,
        #admin-main-header,
        #admin-sidebar-toggle,
        .receipt-actions,
        #flash-success,
        #flash-error {
            display: none !important;
        }
        #admin-main-shell {
            padding-left: 0 !important;
        }
        #admin-main-content {
            padding: 0 !important;
            min-height: 297mm !important;
        }
        .receipt-paper {
            width: 195mm;
            max-width: 195mm;
            margin: 35mm auto 0;
            box-shadow: none;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

<div class="receipt-actions">
    <a href="{{ route('admin.payments.index') }}">
        <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
        Retour aux paiements
    </a>
    <button type="button" onclick="window.print()">
        <span class="material-symbols-outlined" style="font-size:18px;">print</span>
        Imprimer le reçu
    </button>
</div>

<article class="receipt-paper" aria-label="Reçu de paiement de scolarité">
    {{-- Canevas officiel du reçu IFAM --}}
    <img class="receipt-paper__template" src="{{ asset('img/reçu.png') }}" alt="Canevas officiel du reçu IFAM">

    {{-- Masque et titre dynamique avec le numéro officiel --}}
    <div class="receipt-paper__title-mask" aria-hidden="true"></div>
    <h1 class="receipt-paper__title">REÇU DE PAIEMENT DE SCOLARITÉ N° {{ $receiptReference }}</h1>

    {{-- Données superposées --}}
    <div class="receipt-paper__field receipt-paper__student">{{ $student?->full_name ?? 'Étudiant non renseigné' }}@if($student?->matricule) ({{ $student->matricule }})@endif</div>
    <div class="receipt-paper__field receipt-paper__amount">{{ number_format($amount, 0, ',', ' ') }} FCFA</div>
    <div class="receipt-paper__field receipt-paper__reason">Frais de scolarité — {{ $formationName }}</div>

    <div class="receipt-paper__field receipt-paper__detail-one">Session : {{ $sessionName }}</div>
    <div class="receipt-paper__field receipt-paper__detail-two">
        Mode : {{ $paymentMode }}@if($payment->notes) · {{ $payment->notes }}@endif
    </div>

    <div class="receipt-paper__field receipt-paper__amount-words">{{ $amountInWords }}</div>
    <div class="receipt-paper__field receipt-paper__payer">Payeur non renseigné</div>
    <div class="receipt-paper__field receipt-paper__relationship">—</div>

    <div class="receipt-paper__field receipt-paper__cashier">{{ $payment->enregistrePar?->full_name ?? 'Administration' }}</div>
    <div class="receipt-paper__field receipt-paper__phone">{{ $payment->enregistrePar?->phone ?? '—' }}</div>

    <div class="receipt-paper__field receipt-paper__balance">
        {{ $remainingBalance !== null ? number_format($remainingBalance, 0, ',', ' ') . ' FCFA' : '—' }}
    </div>

    <div class="receipt-paper__field receipt-paper__date">{{ optional($paymentDate)->format('d/m/Y') ?? '—' }}</div>

    {{-- Cachet officiel de la comptabilité --}}
    <img class="receipt-paper__accounting-stamp" src="{{ asset('img/catchetComptable.jpg') }}" alt="Cachet du service comptable">
</article>
@endsection
