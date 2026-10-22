@extends('layouts.admin')

@section('title', 'Frais de scolarité')

@section('content')
<div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Frais de scolarité
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Les frais proviennent des inscriptions. Les paiements enregistrés sur chaque inscription mettent automatiquement le solde à jour.
        </p>
    </div>
    <a href="{{ route('admin.enrollments.index') }}"
       style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1rem; border-radius:0.75rem; background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none; font-size:0.8125rem; font-weight:700;">
        <span class="material-symbols-outlined" style="font-size:18px;">school</span>
        Voir les inscriptions
    </a>
</div>

<div style="background:var(--color-surface-container-low); padding:1rem 1.25rem; border-radius:1rem; margin-bottom:1.25rem;">
    <form action="{{ route('admin.fees.index') }}" method="GET" style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end;">
        @includeWhen($sites->isNotEmpty(), 'admin.partials.site-filter')
        <div>
            <label for="session_id" style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">
                Session académique
            </label>
            <select id="session_id" name="session_id" onchange="this.form.submit()"
                    style="height:40px; min-width:190px; padding:0 0.75rem; border:1px solid var(--color-outline-variant); border-radius:0.5rem; background:var(--color-surface-container-lowest); font-size:0.875rem;">
                <option value="">Toutes les sessions</option>
                @foreach($sessions as $session)
                    <option value="{{ $session->id }}" {{ (string) request('session_id') === (string) $session->id ? 'selected' : '' }}>
                        {{ $session->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="student_id" style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">
                Étudiant
            </label>
            <select id="student_id" name="student_id" onchange="this.form.submit()"
                    style="height:40px; min-width:210px; padding:0 0.75rem; border:1px solid var(--color-outline-variant); border-radius:0.5rem; background:var(--color-surface-container-lowest); font-size:0.875rem;">
                <option value="">Tous les étudiants</option>
                @foreach($students as $student)
                    <option value="{{ $student->id }}" {{ (string) request('student_id') === (string) $student->id ? 'selected' : '' }}>
                        {{ $student->user?->last_name }} {{ $student->user?->first_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="status" style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">
                Situation du paiement
            </label>
            <select id="status" name="status" onchange="this.form.submit()"
                    style="height:40px; min-width:170px; padding:0 0.75rem; border:1px solid var(--color-outline-variant); border-radius:0.5rem; background:var(--color-surface-container-lowest); font-size:0.875rem;">
                <option value="">Tous les paiements</option>
                <option value="impaye" {{ request('status') === 'impaye' ? 'selected' : '' }}>Impayé</option>
                <option value="partiel" {{ request('status') === 'partiel' ? 'selected' : '' }}>Partiel</option>
                <option value="solde" {{ request('status') === 'solde' ? 'selected' : '' }}>Soldé</option>
            </select>
        </div>

        @if(request()->hasAny(['session_id', 'student_id', 'status']))
            <a href="{{ route('admin.fees.index') }}"
               style="height:40px; display:inline-flex; align-items:center; color:var(--color-primary); text-decoration:none; font-size:0.875rem; font-weight:600;">
                Réinitialiser
            </a>
        @endif
    </form>
</div>

<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden;">
    @if($enrollments->isEmpty())
        <div style="padding:3.5rem 1.5rem; text-align:center; color:var(--color-on-surface-variant);">
            <span class="material-symbols-outlined" style="font-size:44px; opacity:0.3; display:block; margin-bottom:0.75rem;">account_balance_wallet</span>
            <p style="font-size:0.9375rem; margin:0;">Aucune inscription ne correspond à ces filtres.</p>
        </div>
    @else
        <div style="overflow-x:auto;">
            <table style="width:100%; min-width:1000px; border-collapse:collapse; font-size:0.8125rem;">
                <thead>
                    <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                        <th style="text-align:left; padding:0.875rem 1rem;">Étudiant</th>
                        <th style="text-align:left; padding:0.875rem 1rem;">Formation</th>
                        <th style="text-align:left; padding:0.875rem 1rem;">Session</th>
                        <th style="text-align:right; padding:0.875rem 1rem;">Prix</th>
                        <th style="text-align:right; padding:0.875rem 1rem;">Bourse</th>
                        <th style="text-align:right; padding:0.875rem 1rem;">Montant dû</th>
                        <th style="text-align:right; padding:0.875rem 1rem;">Déjà payé</th>
                        <th style="text-align:right; padding:0.875rem 1rem;">Solde</th>
                        <th style="text-align:left; padding:0.875rem 1rem;">Paiement</th>
                        <th style="padding:0.875rem 1rem;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($enrollments as $enrollment)
                        @php
                            $totalPaye = $enrollment->total_paye;
                            $solde = $enrollment->solde;
                            $statutPaiement = $enrollment->statut_paiement;
                            $deduction = max(0, (float) $enrollment->prix_formation - (float) $enrollment->montant_du);
                        @endphp
                        <tr style="border-bottom:1px solid var(--color-surface-container);">
                            <td style="padding:0.875rem 1rem;">
                                <div style="font-weight:700; color:var(--color-on-surface);">
                                    {{ $enrollment->studentProfile->user->full_name ?? '—' }}
                                </div>
                                <div style="font-size:0.7rem; color:var(--color-on-surface-variant); margin-top:0.2rem;">
                                    {{ $enrollment->studentProfile->user->matricule ?? 'Matricule non défini' }}
                                </div>
                            </td>
                            <td style="padding:0.875rem 1rem; color:var(--color-on-surface);">
                                <div style="font-weight:600;">{{ $enrollment->formation->name ?? '—' }}</div>
                                @if($enrollment->formation?->parcours)
                                    <div style="font-size:0.7rem; color:var(--color-on-surface-variant); margin-top:0.2rem;">
                                        {{ $enrollment->formation->parcours->nom }}
                                    </div>
                                @endif
                            </td>
                            <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">
                                {{ $enrollment->academicSession->name ?? '—' }}
                            </td>
                            <td style="padding:0.875rem 1rem; text-align:right; white-space:nowrap;">
                                {{ number_format((float) $enrollment->prix_formation, 0, ',', ' ') }} FCFA
                            </td>
                            <td style="padding:0.875rem 1rem; text-align:right; white-space:nowrap; color:#059669;">
                                −{{ number_format($deduction, 0, ',', ' ') }} FCFA
                            </td>
                            <td style="padding:0.875rem 1rem; text-align:right; white-space:nowrap; font-weight:700;">
                                {{ number_format((float) $enrollment->montant_du, 0, ',', ' ') }} FCFA
                            </td>
                            <td style="padding:0.875rem 1rem; text-align:right; white-space:nowrap; color:#059669;">
                                {{ number_format($totalPaye, 0, ',', ' ') }} FCFA
                            </td>
                            <td style="padding:0.875rem 1rem; text-align:right; white-space:nowrap; font-weight:700; color:{{ $solde > 0 ? '#b91c1c' : '#047857' }};">
                                {{ number_format($solde, 0, ',', ' ') }} FCFA
                            </td>
                            <td style="padding:0.875rem 1rem;">
                                @if($statutPaiement === 'solde')
                                    <span style="padding:0.25rem 0.625rem; border-radius:9999px; background:#ecfdf5; color:#065f46; font-size:0.6875rem; font-weight:700;">Soldé</span>
                                @elseif($statutPaiement === 'partiel')
                                    <span style="padding:0.25rem 0.625rem; border-radius:9999px; background:#fffbeb; color:#92400e; font-size:0.6875rem; font-weight:700;">Partiel</span>
                                @else
                                    <span style="padding:0.25rem 0.625rem; border-radius:9999px; background:#fef2f2; color:#991b1b; font-size:0.6875rem; font-weight:700;">Impayé</span>
                                @endif
                                <div style="font-size:0.6875rem; color:var(--color-on-surface-variant); margin-top:0.375rem;">
                                    Inscription {{ ucfirst($enrollment->status) }}
                                </div>
                            </td>
                            <td style="padding:0.875rem 1rem; text-align:right;">
                                @if($solde > 0)
                                    <a href="{{ route('admin.payments.create', ['inscription_id' => $enrollment->id]) }}"
                                       style="display:inline-flex; align-items:center; gap:0.25rem; padding:0.5rem 0.625rem; border-radius:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none; font-size:0.75rem; font-weight:700; white-space:nowrap;">
                                        <span class="material-symbols-outlined" style="font-size:16px;">payments</span>
                                        Encaisser
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($enrollments->hasPages())
            <div style="padding:1rem 1.25rem; border-top:1px solid var(--color-surface-container); display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
                <span style="font-size:0.8125rem; color:var(--color-on-surface-variant);">
                    Affichage {{ $enrollments->firstItem() }}–{{ $enrollments->lastItem() }} sur {{ $enrollments->total() }}
                </span>
                {{ $enrollments->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
