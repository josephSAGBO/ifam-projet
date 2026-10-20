@extends('layouts.admin')

@section('title', 'Détail de l\'inscription')

@section('content')
<div style="max-width:960px; margin:0 auto; padding-bottom:3rem;">
    {{-- Header --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem;">
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <a href="{{ route('admin.enrollments.index') }}"
               style="display:inline-flex; align-items:center; justify-content:center; width:40px; height:40px; border-radius:0.5rem;
                      background:var(--color-surface-container); color:var(--color-on-surface); text-decoration:none;">
                <span class="material-symbols-outlined" style="font-size:20px;">arrow_back</span>
            </a>
            <div>
                <h1 style="font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0;">
                    Détail de l'inscription
                </h1>
                <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0.25rem 0 0;">
                    {{ $enrollment->studentProfile->user->full_name ?? '—' }} — {{ $enrollment->formation->name ?? '—' }}
                </p>
            </div>
        </div>

        <div style="display:flex; gap:0.75rem;">
            <a href="{{ route('admin.enrollments.edit', $enrollment) }}"
               style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1rem; border-radius:0.5rem;
                      background:var(--color-surface-container); color:var(--color-on-surface); text-decoration:none; font-weight:600; font-size:0.875rem;">
                <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                Modifier
            </a>
            @if($solde > 0)
                <a href="{{ route('admin.payments.create', ['inscription_id' => $enrollment->id]) }}"
                   style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem; border-radius:0.5rem;
                          background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none; font-weight:700; font-size:0.875rem;
                          box-shadow:0 3px 10px rgba(228,0,102,0.3);">
                    <span class="material-symbols-outlined" style="font-size:18px;">add_card</span>
                    Enregistrer un paiement
                </a>
            @endif
        </div>
    </div>

    {{-- Grille Informations --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">
        {{-- Fiche Étudiant & Formation --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; border:1px solid var(--color-outline-variant); padding:1.5rem;">
            <h3 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="font-size:20px; color:var(--color-primary);">school</span>
                Étudiant & Formation
            </h3>

            <div style="display:flex; flex-direction:column; gap:0.75rem; font-size:0.875rem;">
                <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.5rem;">
                    <span style="color:var(--color-on-surface-variant);">Étudiant :</span>
                    <strong style="color:var(--color-on-surface);">{{ $enrollment->studentProfile->user->full_name ?? '—' }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.5rem;">
                    <span style="color:var(--color-on-surface-variant);">Matricule :</span>
                    <strong style="color:var(--color-on-surface);">{{ $enrollment->studentProfile->user->matricule ?? '—' }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.5rem;">
                    <span style="color:var(--color-on-surface-variant);">Formation :</span>
                    <strong style="color:var(--color-on-surface);">{{ $enrollment->formation->name ?? '—' }}</strong>
                </div>
                @if($enrollment->formation?->parcours)
                <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.5rem;">
                    <span style="color:var(--color-on-surface-variant);">Parcours / étape :</span>
                    <strong style="color:var(--color-on-surface);">
                        {{ $enrollment->formation->parcours->nom }} · {{ $enrollment->formation->ordre ?? '—' }} — {{ $enrollment->formation->name }}
                    </strong>
                </div>
                @endif
                <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.5rem;">
                    <span style="color:var(--color-on-surface-variant);">Session académique :</span>
                    <strong style="color:var(--color-on-surface);">{{ $enrollment->academicSession->name ?? '—' }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.5rem;">
                    <span style="color:var(--color-on-surface-variant);">Date d'inscription :</span>
                    <span style="color:var(--color-on-surface);">{{ $enrollment->enrollment_date ? $enrollment->enrollment_date->format('d/m/Y') : '—' }}</span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:var(--color-on-surface-variant);">Statut dossier :</span>
                    <span style="font-weight:700; text-transform:uppercase; font-size:0.75rem; color:var(--color-on-surface);">{{ $enrollment->status }}</span>
                </div>
            </div>
        </div>

        {{-- Situation Financière --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; border:1px solid var(--color-outline-variant); padding:1.5rem;">
            <h3 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="font-size:20px; color:var(--color-primary);">account_balance_wallet</span>
                Situation financière
            </h3>

            <div style="display:flex; flex-direction:column; gap:0.75rem; font-size:0.875rem;">
                <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.5rem;">
                    <span style="color:var(--color-on-surface-variant);">Prix formation :</span>
                    <strong style="color:var(--color-on-surface);">{{ number_format($enrollment->prix_formation, 2, ',', ' ') }} FCFA</strong>
                </div>
                <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.5rem;">
                    <span style="color:var(--color-on-surface-variant);">Bourse appliquée :</span>
                    <strong style="color:#16a34a;">
                        @if($enrollment->bourse_type === 'pourcentage')
                            {{ $enrollment->bourse_valeur }} %
                        @elseif($enrollment->bourse_type === 'montant')
                            {{ number_format($enrollment->bourse_valeur, 2, ',', ' ') }} FCFA
                        @else
                            Aucune
                        @endif
                    </strong>
                </div>
                <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.5rem;">
                    <span style="color:var(--color-on-surface-variant);">Montant net dû :</span>
                    <strong style="color:var(--color-on-surface); font-size:1rem;">{{ number_format($enrollment->montant_du, 2, ',', ' ') }} FCFA</strong>
                </div>
                <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.5rem;">
                    <span style="color:var(--color-on-surface-variant);">Total réglé :</span>
                    <strong style="color:#16a34a; font-size:1rem;">{{ number_format($totalPaye, 2, ',', ' ') }} FCFA</strong>
                </div>
                <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.5rem;">
                    <span style="color:var(--color-on-surface-variant);">Solde restant :</span>
                    <strong style="color:{{ $solde > 0 ? '#dc2626' : '#16a34a' }}; font-size:1.125rem;">
                        {{ number_format($solde, 2, ',', ' ') }} FCFA
                    </strong>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="color:var(--color-on-surface-variant);">Statut paiement :</span>
                    @php
                        $badges = [
                            'solde'   => ['bg' => '#ecfdf5', 'color' => '#065f46', 'label' => 'Soldé'],
                            'partiel' => ['bg' => '#fef3c7', 'color' => '#92400e', 'label' => 'Partiel'],
                            'impaye'  => ['bg' => '#fef2f2', 'color' => '#991b1b', 'label' => 'Impayé'],
                        ];
                        $badge = $badges[$statutPaiement] ?? ['bg' => '#f3f4f6', 'color' => '#374151', 'label' => $statutPaiement];
                    @endphp
                    <span style="padding:0.25rem 0.75rem; border-radius:9999px; font-weight:700; font-size:0.75rem; background:{{ $badge['bg'] }}; color:{{ $badge['color'] }};">
                        {{ $badge['label'] }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    @if($enrollment->formation?->parcours)
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; border:1px solid var(--color-outline-variant); padding:1.5rem; margin-bottom:1.5rem;">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1rem;">
            <div>
                <h3 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0;">Situation du parcours {{ $enrollment->formation->parcours->nom }}</h3>
                <p style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin:0.25rem 0 0;">Les soldes restent rattachés à leur inscription annuelle.</p>
            </div>
            <strong style="font-size:1.125rem; color:{{ $soldeParcours > 0 ? '#dc2626' : '#16a34a' }};">Solde total : {{ number_format($soldeParcours, 0, ',', ' ') }} FCFA</strong>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:0.8125rem;">
                <thead>
                    <tr style="background:var(--color-surface-container-low);">
                        <th style="padding:0.625rem 0.75rem; text-align:left; color:var(--color-on-surface-variant);">Formation</th>
                        <th style="padding:0.625rem 0.75rem; text-align:left; color:var(--color-on-surface-variant);">Session</th>
                        <th style="padding:0.625rem 0.75rem; text-align:left; color:var(--color-on-surface-variant);">Statut</th>
                        <th style="padding:0.625rem 0.75rem; text-align:right; color:var(--color-on-surface-variant);">Dû</th>
                        <th style="padding:0.625rem 0.75rem; text-align:right; color:var(--color-on-surface-variant);">Solde</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parcoursEnrollments->sortBy(fn ($item) => $item->formation->ordre ?? 0) as $yearEnrollment)
                        <tr style="border-top:1px solid var(--color-surface-container);">
                            <td style="padding:0.625rem 0.75rem;">{{ $yearEnrollment->formation->ordre ? 'Étape '.$yearEnrollment->formation->ordre.' · ' : '' }}{{ $yearEnrollment->formation->name }}</td>
                            <td style="padding:0.625rem 0.75rem;">{{ $yearEnrollment->academicSession->name ?? '—' }}</td>
                            <td style="padding:0.625rem 0.75rem;">{{ ucfirst($yearEnrollment->status) }}</td>
                            <td style="padding:0.625rem 0.75rem; text-align:right;">{{ number_format($yearEnrollment->montant_du, 0, ',', ' ') }} FCFA</td>
                            <td style="padding:0.625rem 0.75rem; text-align:right; font-weight:700; color:{{ $yearEnrollment->solde > 0 ? '#dc2626' : '#16a34a' }};">{{ number_format($yearEnrollment->solde, 0, ',', ' ') }} FCFA</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Historique des Paiements --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; border:1px solid var(--color-outline-variant); overflow:hidden;">
        <div style="padding:1.25rem 1.5rem; border-bottom:1px solid var(--color-outline-variant); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="font-size:20px; color:var(--color-primary);">receipt_long</span>
                Historique des versements
            </h3>
        </div>

        @if($enrollment->payments->isEmpty())
            <div style="padding:3rem 1.5rem; text-align:center; color:var(--color-on-surface-variant);">
                <span class="material-symbols-outlined" style="font-size:36px; opacity:0.3; display:block; margin-bottom:0.5rem;">credit_card_off</span>
                Aucun versement enregistré pour cette inscription.
            </div>
        @else
            <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
                <thead>
                    <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                        <th style="text-align:left; padding:0.75rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; color:var(--color-on-surface-variant);">Date</th>
                        <th style="text-align:left; padding:0.75rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; color:var(--color-on-surface-variant);">Mode</th>
                        <th style="text-align:left; padding:0.75rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; color:var(--color-on-surface-variant);">Référence</th>
                        <th style="text-align:left; padding:0.75rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; color:var(--color-on-surface-variant);">Enregistré par</th>
                        <th style="text-align:right; padding:0.75rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; color:var(--color-on-surface-variant);">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($enrollment->payments as $payment)
                        <tr style="border-bottom:1px solid var(--color-surface-container);">
                            <td style="padding:0.75rem 1rem; color:var(--color-on-surface);">
                                {{ $payment->date_paiement ? $payment->date_paiement->format('d/m/Y') : ($payment->payment_date ? $payment->payment_date->format('d/m/Y') : '—') }}
                            </td>
                            <td style="padding:0.75rem 1rem; text-transform:capitalize; color:var(--color-on-surface-variant);">
                                {{ $payment->mode ?? $payment->method ?? '—' }}
                            </td>
                            <td style="padding:0.75rem 1rem; font-family:monospace; color:var(--color-on-surface-variant);">
                                <a href="{{ route('admin.payments.receipt', $payment) }}"
                                   style="color:var(--color-primary); text-decoration:none; font-weight:600;">
                                    {{ $payment->reference ?? 'Voir le reçu' }}
                                </a>
                            </td>
                            <td style="padding:0.75rem 1rem; color:var(--color-on-surface-variant);">
                                {{ $payment->enregistrePar->full_name ?? ($payment->enregistrePar->name ?? 'Système') }}
                            </td>
                            <td style="padding:0.75rem 1rem; text-align:right; font-weight:700; color:#16a34a;">
                                {{ number_format($payment->montant ?? $payment->amount_paid ?? 0, 2, ',', ' ') }} FCFA
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
