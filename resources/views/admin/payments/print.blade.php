@extends('layouts.admin')

@section('title', 'Impression des paiements')

@section('content')
<style>
    @media print {
        #sidebar,
        #admin-sidebar-backdrop,
        #admin-main-header,
        .payment-print-actions,
        #flash-success,
        #flash-error {
            display: none !important;
        }

        #admin-main-shell {
            padding-left: 0 !important;
        }

        #admin-main-content {
            min-height: 0 !important;
            padding: 0 !important;
        }

        .payment-print-report {
            max-width: none !important;
            margin: 0 !important;
            border: 0 !important;
            box-shadow: none !important;
        }

        .payment-print-report tr {
            break-inside: avoid;
        }
    }
</style>

<div class="payment-print-actions" style="max-width:1100px; margin:0 auto 1rem; display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap;">
    <a href="{{ route('admin.payments.index', $filters) }}" style="color:var(--color-primary); text-decoration:none; font-weight:600;">← Retour aux paiements</a>
    <button type="button" onclick="window.print()" style="min-height:40px; padding:0 1rem; border:0; border-radius:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); font-weight:700; cursor:pointer;">
        Imprimer
    </button>
</div>

<article class="payment-print-report" style="max-width:1100px; margin:0 auto; padding:1.5rem; background:#fff; color:#172033; border:1px solid #d1d5db; border-radius:0.75rem; box-shadow:0 2px 12px rgba(15,23,42,0.08);">
    <header style="display:flex; justify-content:space-between; align-items:center; gap:1.5rem; padding-bottom:1rem; border-bottom:2px solid #e40066;">
        <div style="display:flex; align-items:center; gap:1rem;">
            <img src="{{ asset('img/logo.jpg') }}" alt="Logo IFAM" style="width:64px; height:64px; object-fit:contain;">
            <div>
                <div style="font-size:1.125rem; font-weight:800;">IFAM Connect</div>
                <div style="font-size:0.8125rem; color:#64748b;">État des paiements enregistrés</div>
            </div>
        </div>
        <div style="text-align:right; font-size:0.8125rem; color:#475569;">
            Édité le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </header>

    <div style="display:flex; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin:1rem 0; padding:0.875rem 1rem; background:#f8fafc; border:1px solid #e2e8f0; border-radius:0.5rem;">
        <div>
            <div style="font-size:0.75rem; color:#64748b;">Période</div>
            <strong>{{ !empty($filters['date_from']) ? \Illuminate\Support\Carbon::parse($filters['date_from'])->format('d/m/Y') : 'Toutes dates' }}
                — {{ !empty($filters['date_to']) ? \Illuminate\Support\Carbon::parse($filters['date_to'])->format('d/m/Y') : 'Toutes dates' }}</strong>
        </div>
        <div>
            <div style="font-size:0.75rem; color:#64748b;">Nombre de paiements</div>
            <strong>{{ $payments->count() }}</strong>
        </div>
        <div style="text-align:right;">
            <div style="font-size:0.75rem; color:#64748b;">Montant total</div>
            <strong style="font-size:1.125rem; color:#be185d;">{{ number_format($filteredTotal, 0, ',', ' ') }} FCFA</strong>
        </div>
    </div>

    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.8125rem;">
            <thead>
                <tr style="background:#f1f5f9;">
                    <th style="padding:0.65rem; text-align:left; border:1px solid #cbd5e1;">Date</th>
                    <th style="padding:0.65rem; text-align:left; border:1px solid #cbd5e1;">Étudiant</th>
                    <th style="padding:0.65rem; text-align:left; border:1px solid #cbd5e1;">Formation / session</th>
                    <th style="padding:0.65rem; text-align:right; border:1px solid #cbd5e1;">Montant</th>
                    <th style="padding:0.65rem; text-align:left; border:1px solid #cbd5e1;">Mode</th>
                    <th style="padding:0.65rem; text-align:left; border:1px solid #cbd5e1;">Référence</th>
                    <th style="padding:0.65rem; text-align:left; border:1px solid #cbd5e1;">Enregistré par</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    @php
                        $studentUser = $payment->enrollment?->studentProfile?->user ?? $payment->studentProfile?->user;
                        $paymentDate = $payment->date_paiement ?? $payment->payment_date;
                    @endphp
                    <tr>
                        <td style="padding:0.65rem; border:1px solid #cbd5e1; white-space:nowrap;">{{ $paymentDate?->format('d/m/Y') ?? '—' }}</td>
                        <td style="padding:0.65rem; border:1px solid #cbd5e1;">{{ $studentUser?->full_name ?? '—' }}<br><span style="color:#64748b;">{{ $studentUser?->matricule ?? '' }}</span></td>
                        <td style="padding:0.65rem; border:1px solid #cbd5e1;">{{ $payment->enrollment?->formation?->name ?? '—' }}<br><span style="color:#64748b;">{{ $payment->enrollment?->academicSession?->name ?? '—' }}</span></td>
                        <td style="padding:0.65rem; text-align:right; border:1px solid #cbd5e1; white-space:nowrap;">{{ number_format((float) ($payment->montant ?? $payment->amount_paid ?? 0), 0, ',', ' ') }} FCFA</td>
                        <td style="padding:0.65rem; border:1px solid #cbd5e1;">{{ $payment->mode ?? $payment->method ?? '—' }}</td>
                        <td style="padding:0.65rem; border:1px solid #cbd5e1;">{{ $payment->reference ?? '—' }}</td>
                        <td style="padding:0.65rem; border:1px solid #cbd5e1;">{{ $payment->enregistrePar?->full_name ?? $payment->enregistrePar?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding:1.5rem; text-align:center; border:1px solid #cbd5e1; color:#64748b;">Aucun paiement pour ces filtres.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" style="padding:0.75rem; text-align:right; border:1px solid #cbd5e1;">Total</th>
                    <th style="padding:0.75rem; text-align:right; border:1px solid #cbd5e1; white-space:nowrap;">{{ number_format($filteredTotal, 0, ',', ' ') }} FCFA</th>
                    <th colspan="3" style="border:1px solid #cbd5e1;"></th>
                </tr>
            </tfoot>
        </table>
    </div>
</article>
@endsection
