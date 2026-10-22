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

    // Format monétaire unique pour toute la page
    $fcfa = fn ($amount) => number_format((float) $amount, 0, ',', ' ') . ' FCFA';

    // Seuil d'assiduité : point encore ouvert dans la roadmap, à ajuster ici une fois validé.
    $attendanceThreshold = 75;
    $hasAttendance = $totalAttendances > 0;
    $attendanceLow = $hasAttendance && $attendanceRate < $attendanceThreshold;

    $paymentProgress = ($totalOwed + $totalPaid) > 0
        ? min(100, (int) round(($totalPaid / ($totalOwed + $totalPaid)) * 100))
        : 0;

    $paymentStatuses = [
        'solde'   => ['label' => 'Soldé',   'class' => 'sd-badge--success'],
        'partiel' => ['label' => 'Partiel', 'class' => 'sd-badge--warning'],
        'impaye'  => ['label' => 'Impayé',  'class' => 'sd-badge--danger'],
    ];

    $shortcuts = [
        ['route' => 'student.schedule',        'icon' => 'calendar_month', 'label' => 'Mon emploi du temps', 'sub' => 'Cours, modules et créneaux',        'warn' => false],
        ['route' => 'student.grades',          'icon' => 'grade',          'label' => 'Mes notes',           'sub' => 'Résultats par évaluation',          'warn' => false],
        ['route' => 'student.bulletins.index', 'icon' => 'description',    'label' => 'Mes bulletins',       'sub' => 'Bulletins officiels publiés',       'warn' => false],
        ['route' => 'student.attendance',      'icon' => 'how_to_reg',     'label' => 'Mes présences',       'sub' => $hasAttendance ? 'Assiduité : ' . $attendanceRate . ' %' : 'Aucune séance enregistrée', 'warn' => $attendanceLow],
        ['route' => 'student.fees',            'icon' => 'receipt_long',   'label' => 'Ma scolarité',        'sub' => $totalOwed > 0 ? $fcfa($totalOwed) . ' restants' : 'Scolarité à jour', 'warn' => $totalOwed > 0],
        ['route' => 'student.documents.index', 'icon' => 'folder_open',    'label' => 'Mes documents',       'sub' => 'Attestations et pièces jointes',    'warn' => false],
    ];
@endphp

<div class="sd-page">

    {{-- En-tête : identité de l'étudiant --}}
    <header class="sd-header" aria-labelledby="sd-title">
        <div class="sd-identity">
            @if($studentUser->photo_path)
                <img class="sd-avatar" src="{{ Storage::url($studentUser->photo_path) }}" alt="Photo de {{ $studentUser->full_name }}">
            @else
                <div class="sd-avatar sd-avatar--fallback" role="img" aria-label="Initiales de {{ $studentUser->full_name }}">
                    {{ $studentUser->initials }}
                </div>
            @endif

            <div class="sd-identity-text">
                <h1 id="sd-title">Bonjour, {{ $studentUser->first_name ?? 'Étudiant' }}</h1>
                <p class="sd-fullname">{{ $studentUser->full_name }}</p>
                <dl class="sd-meta">
                    @if($studentUser->matricule)
                        <div>
                            <dt>Matricule</dt>
                            <dd>{{ $studentUser->matricule }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt>Session</dt>
                        <dd>{{ $activeSession->name ?? 'Aucune session active' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <p class="sd-date">
            <span class="material-symbols-outlined" aria-hidden="true">calendar_today</span>
            <time datetime="{{ now()->toDateString() }}">{{ ucfirst(now()->locale('fr')->translatedFormat('l j F Y')) }}</time>
        </p>
    </header>

    {{-- Alerte assiduité : visible uniquement sous le seuil --}}
    @if($attendanceLow)
        <div class="sd-notice" role="status">
            <span class="material-symbols-outlined" aria-hidden="true">info</span>
            <p>
                Votre assiduité est de {{ $attendanceRate }} %, en dessous du seuil de {{ $attendanceThreshold }} %.
                <a href="{{ route('student.attendance') }}">Consulter mes présences</a>
            </p>
        </div>
    @endif

    {{-- Indicateurs clés --}}
    <section class="sd-kpis" aria-label="Indicateurs clés">
        <div class="sd-kpi">
            <h2 class="sd-kpi-label">Solde restant</h2>
            <p class="sd-kpi-value">{{ $fcfa($totalOwed) }}</p>
            <div class="sd-progress" role="progressbar" aria-label="Part de la scolarité réglée"
                 aria-valuenow="{{ $paymentProgress }}" aria-valuemin="0" aria-valuemax="100">
                <span style="width: {{ $paymentProgress }}%;"></span>
            </div>
            <p class="sd-kpi-caption">{{ $fcfa($totalPaid) }} déjà réglés ({{ $paymentProgress }} %)</p>
        </div>

        <div class="sd-kpi">
            <h2 class="sd-kpi-label">Assiduité</h2>
            <p class="sd-kpi-value {{ $attendanceLow ? 'sd-kpi-value--warn' : '' }}">
                {{ $hasAttendance ? $attendanceRate . ' %' : '—' }}
            </p>
            <p class="sd-kpi-caption">
                {{ $hasAttendance ? $presentCount . ' présences sur ' . $totalAttendances . ' séances' : 'Aucune séance enregistrée' }}
            </p>
        </div>

        <div class="sd-kpi">
            <h2 class="sd-kpi-label">Notes récentes</h2>
            <p class="sd-kpi-value">{{ $recentGrades->count() }}</p>
            <p class="sd-kpi-caption">
                {{ $recentGrades->isNotEmpty() ? 'Dernières notes publiées' : 'Aucune note publiée' }}
            </p>
        </div>
    </section>

    <x-alumni-showcase :alumnis="$alumniShowcase" title="Ils ont commencé comme toi" :featured="true" />

    {{-- Scolarité par inscription --}}
    <section class="sd-section" aria-labelledby="sd-fees-title">
        <div class="sd-section-head">
            <div>
                <h2 id="sd-fees-title">Ma scolarité</h2>
                <p>Montants calculés à partir de vos inscriptions et des paiements enregistrés.</p>
            </div>
            <a class="sd-link" href="{{ route('student.fees') }}">Voir le suivi des paiements</a>
        </div>

        @if($myEnrollments->isEmpty())
            <p class="sd-empty">Aucune inscription financière n'est associée à votre dossier.</p>
        @else
            <div class="sd-table-wrap">
                <table class="sd-table">
                    <caption class="sd-sr-only">Situation financière par inscription</caption>
                    <thead>
                        <tr>
                            <th scope="col">Formation</th>
                            <th scope="col" class="sd-num">Prix</th>
                            <th scope="col" class="sd-num">Bourse / réduction</th>
                            <th scope="col" class="sd-num">Montant dû</th>
                            <th scope="col" class="sd-num">Déjà payé</th>
                            <th scope="col" class="sd-num">Solde restant</th>
                            <th scope="col">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($myEnrollments as $enrollment)
                            @php
                                $prixFormation = (float) $enrollment->prix_formation;
                                $montantDu     = (float) $enrollment->montant_du;
                                $bourse        = max(0, $prixFormation - $montantDu);
                                $statut        = $paymentStatuses[$enrollment->statut_paiement]
                                                 ?? ['label' => $enrollment->statut_paiement, 'class' => 'sd-badge--neutral'];
                            @endphp
                            <tr>
                                <th scope="row">
                                    <span class="sd-cell-title">{{ $enrollment->formation->name ?? 'Formation' }}</span>
                                    <span class="sd-cell-sub">{{ $enrollment->academicSession->name ?? 'Session non précisée' }}</span>
                                </th>
                                <td class="sd-num" data-label="Prix">{{ $fcfa($prixFormation) }}</td>
                                <td class="sd-num" data-label="Bourse / réduction">{{ $fcfa($bourse) }}</td>
                                <td class="sd-num" data-label="Montant dû">{{ $fcfa($montantDu) }}</td>
                                <td class="sd-num" data-label="Déjà payé">{{ $fcfa($enrollment->total_paye) }}</td>
                                <td class="sd-num {{ $enrollment->solde > 0 ? 'sd-amount--due' : 'sd-amount--ok' }}" data-label="Solde restant">
                                    {{ $fcfa($enrollment->solde) }}
                                </td>
                                <td data-label="Statut">
                                    <span class="sd-badge {{ $statut['class'] }}">{{ $statut['label'] }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- Notes récentes + accès rapides --}}
    <div class="sd-columns">

        <section class="sd-section" aria-labelledby="sd-grades-title">
            <div class="sd-section-head">
                <h2 id="sd-grades-title">Dernières notes</h2>
                @if($recentGrades->isNotEmpty())
                    <a class="sd-link" href="{{ route('student.grades') }}">Voir toutes mes notes</a>
                @endif
            </div>

            @if($recentGrades->isEmpty())
                <div class="sd-empty sd-empty--center">
                    <span class="material-symbols-outlined" aria-hidden="true">grading</span>
                    <p>Aucune note publiée pour le moment.</p>
                </div>
            @else
                <ul class="sd-grades">
                    @foreach($recentGrades as $g)
                        @php
                            $eval = $g->evaluation;
                            $max  = $eval?->max_score ?: 20;
                            $s20  = $g->is_absent ? 0 : ($max > 0 ? round(($g->score / $max) * 20, 1) : 0);
                            $passed = !$g->is_absent && $s20 >= 10;
                        @endphp
                        <li class="sd-grade">
                            <div class="sd-grade-info">
                                <span class="sd-grade-title">{{ $eval?->title ?? 'Évaluation' }}</span>
                                <span class="sd-grade-meta">
                                    @if($eval?->course?->module?->code)
                                        <span>{{ $eval->course->module->code }}</span>
                                    @endif
                                    @if($eval?->evaluation_date)
                                        <time datetime="{{ $eval->evaluation_date->toDateString() }}">{{ $eval->evaluation_date->format('d/m/Y') }}</time>
                                    @endif
                                </span>
                            </div>
                            @if($g->is_absent)
                                <span class="sd-score sd-score--fail" title="Absent à l'évaluation">ABS</span>
                            @else
                                <span class="sd-score {{ $passed ? 'sd-score--pass' : 'sd-score--fail' }}">
                                    {{ number_format($s20, 1, ',', "\u{00A0}") }}<small>/20</small>
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="sd-section" aria-labelledby="sd-shortcuts-title">
            <div class="sd-section-head">
                <h2 id="sd-shortcuts-title">Accès rapides</h2>
            </div>
            <nav aria-label="Accès rapides">
                <ul class="sd-shortcuts">
                    @foreach($shortcuts as $item)
                        <li>
                            <a class="sd-shortcut" href="{{ route($item['route']) }}">
                                <span class="sd-shortcut-icon">
                                    <span class="material-symbols-outlined" aria-hidden="true">{{ $item['icon'] }}</span>
                                </span>
                                <span class="sd-shortcut-text">
                                    <span class="sd-shortcut-label">{{ $item['label'] }}</span>
                                    <span class="sd-shortcut-sub {{ $item['warn'] ? 'sd-shortcut-sub--warn' : '' }}">{{ $item['sub'] }}</span>
                                </span>
                                <span class="material-symbols-outlined sd-shortcut-chevron" aria-hidden="true">chevron_right</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </section>

    </div>

</div>
@endsection