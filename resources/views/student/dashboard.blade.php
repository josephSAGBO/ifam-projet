@extends('layouts.role')
@section('title', 'Tableau de bord - Espace Étudiant')
@section('role_label', 'Étudiant')
@section('home_route', route('student.dashboard'))

@section('nav_links')
    @include('student.partials.nav')
@endsection

@section('content')

@php
    $studentUser = auth()->user();
    $paymentProgress = ($totalOwed + $totalPaid) > 0
        ? min(100, (int) round(($totalPaid / ($totalOwed + $totalPaid)) * 100))
        : 0;
@endphp

<div class="student-dashboard-page">
    <section class="student-dashboard-hero" aria-labelledby="student-dashboard-title">
        <div class="student-dashboard-identity">
            @if($studentUser->photo_path)
                <img class="student-dashboard-avatar" src="{{ Storage::url($studentUser->photo_path) }}" alt="Photo de {{ $studentUser->full_name }}">
            @else
                <div class="student-dashboard-avatar student-dashboard-avatar-fallback" aria-label="Initiales de {{ $studentUser->full_name }}">
                    {{ $studentUser->initials }}
                </div>
            @endif
            <div class="student-dashboard-welcome">
                <span class="student-dashboard-eyebrow">Espace étudiant</span>
                <h1 id="student-dashboard-title">Bonjour, {{ $studentUser->first_name ?? 'Étudiant' }}</h1>
                <p>{{ $studentUser->full_name }}@if($studentUser->matricule) <span aria-hidden="true">·</span> Matricule {{ $studentUser->matricule }}@endif</p>
                <div class="student-dashboard-session">
                    <span class="material-symbols-outlined" aria-hidden="true">school</span>
                    <span>{{ $activeSession->name ?? 'Aucune session active' }}</span>
                </div>
            </div>
        </div>
        <div class="student-dashboard-hero-aside">
            <div class="student-dashboard-date">
                <span class="material-symbols-outlined" aria-hidden="true">calendar_today</span>
                <span>{{ now()->locale('fr')->translatedFormat('l d F Y') }}</span>
            </div>
            <div class="student-dashboard-hero-actions">
                <a href="{{ route('student.schedule') }}">
                    <span class="material-symbols-outlined" aria-hidden="true">calendar_month</span>
                    Mon emploi du temps
                </a>
                <a href="{{ route('student.grades') }}">
                    <span class="material-symbols-outlined" aria-hidden="true">grade</span>
                    Mes résultats
                </a>
            </div>
        </div>
    </section>

{{-- KPI rapides --}}
<div class="student-dashboard-stats">

    {{-- Scolarité restante --}}
    <div class="student-dashboard-stat student-dashboard-stat-finance">
        <div class="student-dashboard-stat-heading">
            <span class="student-dashboard-stat-icon"><span class="material-symbols-outlined" aria-hidden="true">payments</span></span>
            <span>Solde restant</span>
        </div>
        <div class="student-dashboard-stat-value">
            {{ number_format($totalOwed, 0, ',', ' ') }} <span>FCFA</span>
        </div>
        <div class="student-dashboard-stat-detail">
            <span>{{ number_format($totalPaid, 0, ',', ' ') }} FCFA déjà réglés</span>
            <div class="student-dashboard-progress" role="progressbar" aria-label="Progression des paiements" aria-valuenow="{{ $paymentProgress }}" aria-valuemin="0" aria-valuemax="100">
                <span style="width:{{ $paymentProgress }}%;"></span>
            </div>
        </div>
    </div>

    {{-- Assiduité --}}
    <div class="student-dashboard-stat student-dashboard-stat-attendance">
        <div class="student-dashboard-stat-heading">
            <span class="student-dashboard-stat-icon"><span class="material-symbols-outlined" aria-hidden="true">how_to_reg</span></span>
            <span>Assiduité</span>
        </div>
        <div class="student-dashboard-stat-value">
            {{ $attendanceRate }} <span>%</span>
        </div>
        <div class="student-dashboard-stat-caption">
            {{ $presentCount }} présences / {{ $totalAttendances }} séances
        </div>
    </div>

    {{-- Notes récentes --}}
    <div class="student-dashboard-stat student-dashboard-stat-grades">
        <div class="student-dashboard-stat-heading">
            <span class="student-dashboard-stat-icon"><span class="material-symbols-outlined" aria-hidden="true">grade</span></span>
            <span>Notes récentes</span>
        </div>
        <div class="student-dashboard-stat-value">
            {{ $recentGrades->count() }}
        </div>
        <div class="student-dashboard-stat-caption">
            {{ $recentGrades->isNotEmpty() ? 'Dernières notes publiées' : 'Aucune note publiée' }}
        </div>
    </div>

</div>

{{-- Situation financière par inscription --}}
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; margin-bottom:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem; margin-bottom:1.25rem;">
        <div>
            <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0;">Ma scolarité</h2>
            <p style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin:0.25rem 0 0;">
                Les montants sont calculés à partir de vos inscriptions et des paiements enregistrés.
            </p>
        </div>
        <a href="{{ route('student.fees') }}" style="font-size:0.8125rem; font-weight:600; color:var(--color-primary); text-decoration:none;">
            Voir le suivi des paiements
        </a>
    </div>

    @if($myEnrollments->isEmpty())
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">Aucune inscription financière n'est associée à votre dossier.</p>
    @else
        <div style="display:flex; flex-direction:column; gap:0.875rem;">
            @foreach($myEnrollments as $enrollment)
                @php
                    $prixFormation = (float) $enrollment->prix_formation;
                    $montantDu = (float) $enrollment->montant_du;
                    $bourse = max(0, $prixFormation - $montantDu);
                    $statutPaiement = $enrollment->statut_paiement;
                    $statutStyles = [
                        'solde' => ['label' => 'Soldé', 'background' => '#ecfdf5', 'color' => '#065f46'],
                        'partiel' => ['label' => 'Partiel', 'background' => '#fef3c7', 'color' => '#92400e'],
                        'impaye' => ['label' => 'Impayé', 'background' => '#fef2f2', 'color' => '#991b1b'],
                    ];
                    $statut = $statutStyles[$statutPaiement] ?? ['label' => $statutPaiement, 'background' => '#f3f4f6', 'color' => '#374151'];
                @endphp
                <div style="padding:1rem; border:1px solid var(--color-outline-variant); border-radius:0.75rem;">
                    <div style="display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:0.75rem; margin-bottom:0.875rem;">
                        <div>
                            <div style="font-size:0.875rem; font-weight:700; color:var(--color-on-surface);">
                                {{ $enrollment->formation->name ?? 'Formation' }}
                            </div>
                            <div style="font-size:0.75rem; color:var(--color-on-surface-variant); margin-top:0.2rem;">
                                {{ $enrollment->academicSession->name ?? 'Session non précisée' }}
                            </div>
                        </div>
                        <span style="padding:0.25rem 0.625rem; border-radius:9999px; background:{{ $statut['background'] }}; color:{{ $statut['color'] }}; font-size:0.6875rem; font-weight:700;">
                            {{ $statut['label'] }}
                        </span>
                    </div>
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(125px, 1fr)); gap:0.75rem;">
                        <div>
                            <div style="font-size:0.6875rem; color:var(--color-on-surface-variant);">Prix formation</div>
                            <strong style="font-size:0.8125rem; color:var(--color-on-surface);">{{ number_format($prixFormation, 0, ',', ' ') }} FCFA</strong>
                        </div>
                        <div>
                            <div style="font-size:0.6875rem; color:var(--color-on-surface-variant);">Bourse / réduction</div>
                            <strong style="font-size:0.8125rem; color:var(--color-on-surface);">{{ number_format($bourse, 0, ',', ' ') }} FCFA</strong>
                        </div>
                        <div>
                            <div style="font-size:0.6875rem; color:var(--color-on-surface-variant);">Montant dû</div>
                            <strong style="font-size:0.8125rem; color:var(--color-on-surface);">{{ number_format($montantDu, 0, ',', ' ') }} FCFA</strong>
                        </div>
                        <div>
                            <div style="font-size:0.6875rem; color:var(--color-on-surface-variant);">Déjà payé</div>
                            <strong style="font-size:0.8125rem; color:#059669;">{{ number_format($enrollment->total_paye, 0, ',', ' ') }} FCFA</strong>
                        </div>
                        <div>
                            <div style="font-size:0.6875rem; color:var(--color-on-surface-variant);">Solde restant</div>
                            <strong style="font-size:0.8125rem; color:{{ $enrollment->solde > 0 ? '#dc2626' : '#059669' }};">{{ number_format($enrollment->solde, 0, ',', ' ') }} FCFA</strong>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Corps principal : dernières notes + raccourcis --}}
<div class="student-dashboard-panels">

    {{-- Dernières notes --}}
    <div class="student-dashboard-panel">
        <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.25rem; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.75rem;">
            <span class="material-symbols-outlined" style="color:var(--color-primary); font-size:22px;">school</span>
            <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0;">Dernières Notes</h2>
        </div>
        @if($recentGrades->isEmpty())
            <div style="text-align:center; padding:2rem 0; color:var(--color-on-surface-variant);">
                <span class="material-symbols-outlined" style="font-size:36px; opacity:0.3; display:block; margin-bottom:0.5rem;">grading</span>
                <p style="font-size:0.875rem; margin:0;">Aucune note publiée pour le moment.</p>
            </div>
        @else
            <div style="display:flex; flex-direction:column; gap:0.625rem;">
                @foreach($recentGrades as $g)
                @php
                    $eval = $g->evaluation;
                    $max  = $eval?->max_score ?: 20;
                    $s20  = $g->is_absent ? 0 : ($max > 0 ? round(($g->score / $max) * 20, 1) : 0);
                @endphp
                <div style="display:flex; align-items:center; justify-content:space-between; padding:0.625rem 0.875rem;
                             background:var(--color-surface-container-low); border-radius:0.625rem;">
                    <div>
                        <div style="font-size:0.875rem; font-weight:600; color:var(--color-on-surface);">
                            {{ $eval?->title ?? 'Évaluation' }}
                        </div>
                        <div style="font-size:0.6875rem; color:var(--color-on-surface-variant);">
                            {{ $eval?->course?->module?->code ?? '' }}
                            @if($eval?->evaluation_date)
                                · {{ $eval->evaluation_date->format('d/m/Y') }}
                            @endif
                        </div>
                    </div>
                    <span style="font-size:0.9375rem; font-weight:800; padding:0.25rem 0.625rem; border-radius:0.5rem;
                                 background:{{ $s20 >= 10 ? '#ecfdf5' : '#fff1f2' }};
                                 color:{{ $s20 >= 10 ? '#065f46' : '#9f1239' }};">
                        {{ $g->is_absent ? 'ABS' : number_format($s20, 1, ',', ' ') }}
                    </span>
                </div>
                @endforeach
            </div>
            <a href="{{ route('student.grades') }}"
               style="display:inline-flex; align-items:center; gap:0.375rem; margin-top:1rem; font-size:0.8125rem;
                      font-weight:600; color:var(--color-primary); text-decoration:none;">
                Voir toutes mes notes
                <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
            </a>
        @endif
    </div>

    {{-- Raccourcis navigation --}}
    <div class="student-dashboard-shortcuts">

        @php
        $navCards = [
            ['route' => 'student.schedule',       'icon' => 'calendar_month', 'label' => 'Mon Emploi du Temps',  'sub' => 'Cours, modules et créneaux',     'bg' => 'var(--color-primary-container)', 'color' => 'var(--color-on-primary)'],
            ['route' => 'student.bulletins.index','icon' => 'description',    'label' => 'Mes Bulletins',         'sub' => 'Bulletins officiels publiés',    'bg' => '#ecfdf5', 'color' => '#065f46'],
            ['route' => 'student.attendance',     'icon' => 'how_to_reg',     'label' => 'Mes Présences',         'sub' => 'Assiduité : '.$attendanceRate.'%','bg' => $attendanceRate >= 75 ? '#ecfdf5' : '#fff7ed', 'color' => $attendanceRate >= 75 ? '#065f46' : '#9a3412'],
            ['route' => 'student.fees',           'icon' => 'receipt_long',   'label' => 'Ma Scolarité',          'sub' => $totalOwed > 0 ? number_format($totalOwed,0,',',' ').' FCFA restants' : 'Scolarité à jour', 'bg' => '#fff1f2', 'color' => '#9f1239'],
            ['route' => 'student.documents.index','icon' => 'folder_open',    'label' => 'Mes Documents',         'sub' => 'Attestations et pièces jointes', 'bg' => 'var(--color-surface-container-high)', 'color' => 'var(--color-on-surface)'],
        ];
        @endphp

        @foreach($navCards as $card)
        <a class="student-dashboard-shortcut" href="{{ route($card['route']) }}"
           style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1rem 1.25rem;
                  box-shadow:0 1px 6px rgba(11,28,48,0.04); display:flex; align-items:center; gap:1rem;
                  text-decoration:none; transition:transform 0.15s, box-shadow 0.15s;
                  border:1px solid var(--color-surface-container);"
           onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(11,28,48,0.08)';"
           onmouseout="this.style.transform=''; this.style.boxShadow='0 1px 6px rgba(11,28,48,0.04)';">
            <div style="width:40px; height:40px; border-radius:0.75rem; background:{{ $card['bg'] }};
                        color:{{ $card['color'] }}; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <span class="material-symbols-outlined" style="font-size:20px;">{{ $card['icon'] }}</span>
            </div>
            <div style="flex:1; min-width:0;">
                <div style="font-size:0.9375rem; font-weight:700; color:var(--color-on-surface);">{{ $card['label'] }}</div>
                <div style="font-size:0.75rem; color:var(--color-on-surface-variant); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $card['sub'] }}</div>
            </div>
            <span class="material-symbols-outlined" style="color:var(--color-on-surface-variant); font-size:18px; flex-shrink:0;">arrow_forward_ios</span>
        </a>
        @endforeach

    </div>
</div>

<x-alumni-showcase :alumnis="$alumniShowcase" title="Ils ont commencé comme toi" />

</div>
@endsection
