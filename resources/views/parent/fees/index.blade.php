@extends('layouts.role')
@section('title', 'Paiements & Scolarité - Espace Parent')
@section('role_label', 'Parent')
@section('home_route', route('parent.dashboard'))

@section('nav_links')
    @include('parent.partials.nav')
@endsection

@section('content')
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Paiements & Scolarité des enfants</h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">Montants issus des inscriptions et paiements de vos enfants rattachés.</p>
    </div>
    @if($payments->isNotEmpty())
        <button onclick="window.print()" style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem; border-radius:0.75rem; background:var(--color-surface-container-high); color:var(--color-on-surface); border:none; cursor:pointer; font-size:0.875rem; font-weight:600;">
            <span class="material-symbols-outlined" style="font-size:18px;">receipt_long</span>
            Imprimer l'état financier
        </button>
    @endif
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1.25rem; margin-bottom:2rem;">
    @foreach([
        ['label' => 'Montant total dû', 'value' => $totalDue, 'color' => 'var(--color-on-surface)'],
        ['label' => 'Total payé', 'value' => $totalPaid, 'color' => '#059669'],
        ['label' => 'Solde restant', 'value' => $totalBalance, 'color' => $totalBalance > 0 ? '#e40066' : '#059669'],
        ['label' => 'Bourses / réductions', 'value' => $totalDiscounts, 'color' => 'var(--color-on-surface)'],
    ] as $item)
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
            <div style="font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-on-surface-variant); margin-bottom:0.5rem;">{{ $item['label'] }}</div>
            <strong style="font-size:1.375rem; color:{{ $item['color'] }};">{{ number_format($item['value'], 0, ',', ' ') }} FCFA</strong>
        </div>
    @endforeach
</div>

<div style="display:flex; flex-direction:column; gap:1.5rem;">
    @forelse($childrenData as $child)
        <section style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden;">
            <div style="padding:1.25rem 1.5rem; background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant); display:flex; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
                <div>
                    <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0;">{{ $child['user']->full_name }}</h2>
                    <span style="font-size:0.75rem; color:var(--color-on-surface-variant);">Montant dû : {{ number_format($child['totalDue'], 0, ',', ' ') }} FCFA · Payé : {{ number_format($child['totalPaid'], 0, ',', ' ') }} FCFA · Solde dû : {{ number_format($child['balance'], 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
            @if($child['enrollments']->isEmpty())
                <p style="padding:1.5rem; font-size:0.875rem; color:var(--color-on-surface-variant);">Aucune inscription financière trouvée pour cet enfant.</p>
            @else
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
                        <thead>
                            <tr style="border-bottom:1px solid var(--color-surface-container);">
                                <th style="text-align:left; padding:0.875rem 1rem;">Formation / session</th>
                                <th style="text-align:right; padding:0.875rem 1rem;">Prix</th>
                                <th style="text-align:right; padding:0.875rem 1rem;">Montant dû</th>
                                <th style="text-align:right; padding:0.875rem 1rem;">Payé</th>
                                <th style="text-align:right; padding:0.875rem 1rem;">Solde restant</th>
                                <th style="text-align:center; padding:0.875rem 1rem;">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($child['enrollments'] as $enrollment)
                                @php($statusLabels = ['solde' => 'Soldé', 'partiel' => 'Partiel', 'impaye' => 'Impayé'])
                                <tr style="border-bottom:1px solid var(--color-surface-container);">
                                    <td style="padding:0.875rem 1rem; font-weight:600; color:var(--color-on-surface);">
                                        {{ $enrollment->formation->name ?? 'Formation' }}
                                        <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">{{ $enrollment->academicSession->name ?? 'Session non précisée' }}</div>
                                    </td>
                                    <td style="padding:0.875rem 1rem; text-align:right;">{{ number_format((float) $enrollment->prix_formation, 0, ',', ' ') }} FCFA</td>
                                    <td style="padding:0.875rem 1rem; text-align:right;">{{ number_format((float) $enrollment->montant_du, 0, ',', ' ') }} FCFA</td>
                                    <td style="padding:0.875rem 1rem; text-align:right; color:#059669;">{{ number_format($enrollment->total_paye, 0, ',', ' ') }} FCFA</td>
                                    <td style="padding:0.875rem 1rem; text-align:right; font-weight:700; color:{{ $enrollment->solde > 0 ? '#dc2626' : '#059669' }};">{{ number_format($enrollment->solde, 0, ',', ' ') }} FCFA</td>
                                    <td style="padding:0.875rem 1rem; text-align:center;">{{ $statusLabels[$enrollment->statut_paiement] ?? $enrollment->statut_paiement }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @empty
        <p style="padding:1.5rem; background:var(--color-surface-container-lowest); border-radius:1rem; color:var(--color-on-surface-variant);">Aucun enfant n'est rattaché à votre compte.</p>
    @endforelse

    <section style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden;">
        <div style="padding:1.25rem 1.5rem; background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant); display:flex; justify-content:space-between;">
            <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0;">Historique des paiements</h2>
            <span style="font-size:0.75rem; color:var(--color-on-surface-variant);">{{ $payments->count() }} versement(s)</span>
        </div>
        @if($payments->isEmpty())
            <p style="padding:1.5rem; font-size:0.875rem; color:var(--color-on-surface-variant);">Aucun paiement enregistré pour le moment.</p>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--color-surface-container);">
                            <th style="text-align:left; padding:0.875rem 1rem;">Enfant</th>
                            <th style="text-align:left; padding:0.875rem 1rem;">Formation</th>
                            <th style="text-align:left; padding:0.875rem 1rem;">Référence / date</th>
                            <th style="text-align:left; padding:0.875rem 1rem;">Mode</th>
                            <th style="text-align:right; padding:0.875rem 1rem;">Montant versé</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            <tr style="border-bottom:1px solid var(--color-surface-container);">
                                <td style="padding:0.875rem 1rem;">{{ $payment->studentProfile->user->full_name ?? '—' }}</td>
                                <td style="padding:0.875rem 1rem;">{{ $payment->enrollment->formation->name ?? 'Formation' }}</td>
                                <td style="padding:0.875rem 1rem;">{{ $payment->reference ?? 'REC-' . str_pad($payment->id, 5, '0', STR_PAD_LEFT) }} · {{ optional($payment->date_paiement ?? $payment->payment_date)->format('d/m/Y') ?? '—' }}</td>
                                <td style="padding:0.875rem 1rem;">{{ $payment->mode ?? $payment->method ?? 'Espèces' }}</td>
                                <td style="padding:0.875rem 1rem; text-align:right; font-weight:700; color:#059669;">{{ number_format((float) ($payment->montant ?? $payment->amount_paid ?? 0), 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection
