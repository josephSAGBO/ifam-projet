@extends('layouts.role')
@section('title', 'Tableau de bord - Espace Parent')
@section('role_label', 'Parent')
@section('home_route', route('parent.dashboard'))

@section('nav_links')
    @include('parent.partials.nav')
@endsection

@section('content')
<div class="student-dashboard-page parent-dashboard-page">
    <section class="student-dashboard-hero" aria-labelledby="parent-dashboard-title">
        <div class="student-dashboard-identity">
            <div class="student-dashboard-avatar student-dashboard-avatar-fallback" aria-hidden="true">
                {{ Auth::user()->initials }}
            </div>
            <div class="student-dashboard-welcome">
                <span class="student-dashboard-eyebrow">Espace parent</span>
                <h1 id="parent-dashboard-title">Bonjour, {{ Auth::user()->first_name ?? 'Parent' }}</h1>
                <p>Suivez les résultats, les présences et la scolarité de vos enfants.</p>
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
        </div>
    </section>

@if($childrenData->isEmpty())
    <div class="parent-dashboard-empty">
        <span class="material-symbols-outlined" style="font-size:48px; opacity:0.3; color:var(--color-primary); margin-bottom:1rem;">family_restroom</span>
        <h3 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem;">Aucun enfant associé à votre compte</h3>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); max-width:480px; margin:0 auto;">
            Votre compte parent n'est actuellement rattaché à aucun dossier étudiant. Veuillez contacter l'administration de l'IFAM pour lier vos enfants.
        </p>
    </div>
@else
    <div class="parent-dashboard-children">
        @foreach($childrenData as $child)
            @php
                $u = $child['user'];
                $financialEnrollments = $child['financialEnrollments'];
            @endphp
            <article class="parent-dashboard-child" aria-labelledby="parent-child-{{ $u->id }}">
                <header class="parent-dashboard-child-header">
                    <div class="student-dashboard-identity">
                        @if($u->photo_path)
                            <img class="student-dashboard-avatar" src="{{ Storage::url($u->photo_path) }}" alt="Photo de {{ $u->full_name }}">
                        @else
                            <div class="student-dashboard-avatar student-dashboard-avatar-fallback" aria-label="Initiales de {{ $u->full_name }}">
                                {{ $u->initials ?? 'ET' }}
                            </div>
                        @endif
                        <div class="student-dashboard-welcome">
                            <span class="student-dashboard-eyebrow">Dossier étudiant</span>
                            <h2 id="parent-child-{{ $u->id }}">{{ $u->full_name }}</h2>
                            <p>
                                @if($u->matricule) Matricule {{ $u->matricule }} <span aria-hidden="true">·</span> @endif
                                {{ $child['enrollment']?->formation?->name ?? ($financialEnrollments->first()?->formation?->name ?? 'Formation non précisée') }}
                            </p>
                            <div class="student-dashboard-session">
                                <span class="material-symbols-outlined" aria-hidden="true">event</span>
                                <span>{{ $child['enrollment']?->academicSession?->name ?? ($financialEnrollments->first()?->academicSession?->name ?? 'Session non précisée') }}</span>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="student-dashboard-stats parent-dashboard-stats">
                    <div class="student-dashboard-stat student-dashboard-stat-grades">
                        <div class="student-dashboard-stat-heading">
                            <span class="student-dashboard-stat-icon"><span class="material-symbols-outlined" aria-hidden="true">grade</span></span>
                            <span>Notes récentes</span>
                        </div>
                        <div class="student-dashboard-stat-value">{{ $child['recentGrades']->count() }}</div>
                        <div class="student-dashboard-stat-caption">Dernières notes enregistrées</div>
                    </div>
                    <div class="student-dashboard-stat student-dashboard-stat-attendance">
                        <div class="student-dashboard-stat-heading">
                            <span class="student-dashboard-stat-icon"><span class="material-symbols-outlined" aria-hidden="true">how_to_reg</span></span>
                            <span>Assiduité</span>
                        </div>
                        <div class="student-dashboard-stat-value">{{ $child['attendanceRate'] }} <span>%</span></div>
                        <div class="student-dashboard-stat-caption">{{ $child['presentCount'] }} présences / {{ $child['totalAttendances'] }} séances</div>
                    </div>
                    <div class="student-dashboard-stat student-dashboard-stat-finance">
                        <div class="student-dashboard-stat-heading">
                            <span class="student-dashboard-stat-icon"><span class="material-symbols-outlined" aria-hidden="true">account_balance_wallet</span></span>
                            <span>Solde restant</span>
                        </div>
                        <div class="student-dashboard-stat-value">{{ number_format($child['financialBalance'], 0, ',', ' ') }} <span>FCFA</span></div>
                        <div class="student-dashboard-stat-caption">{{ number_format($child['financialTotalPaid'], 0, ',', ' ') }} FCFA déjà réglés</div>
                    </div>
                </div>

                <div class="parent-dashboard-panels">
                    
                    <section class="parent-dashboard-panel">
                        <div class="parent-dashboard-panel-heading">
                            <h3><span class="material-symbols-outlined" aria-hidden="true">grade</span>Notes récentes</h3>
                            <span>Sur 20</span>
                        </div>

                        @if($child['recentGrades']->isEmpty())
                            <p class="parent-dashboard-empty-text">Aucune note enregistrée pour le moment.</p>
                        @else
                            <div class="parent-dashboard-records">
                                @foreach($child['recentGrades'] as $grade)
                                    <div class="parent-dashboard-record">
                                        <div class="parent-dashboard-record-detail">
                                            <strong>{{ $grade->evaluation?->titre ?? ($grade->evaluation?->module?->name ?? 'Évaluation') }}</strong>
                                            <span>{{ $grade->evaluation?->date?->format('d/m/Y') ?? $grade->created_at?->format('d/m/Y') }}</span>
                                        </div>
                                        <strong class="parent-dashboard-grade {{ $grade->score >= 10 ? 'is-passing' : 'is-failing' }}">
                                            {{ number_format($grade->score, 2, ',', ' ') }} / 20
                                        </strong>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    {{-- 2. Bulletins publiés --}}
                    <section class="parent-dashboard-panel">
                        <div class="parent-dashboard-panel-heading">
                            <h3><span class="material-symbols-outlined" aria-hidden="true">description</span>Bulletins officiels</h3>
                        </div>

                        @if($child['publishedBulletins']->isEmpty())
                            <p class="parent-dashboard-empty-text">
                                Aucun bulletin publié pour le moment. Les bulletins ne sont visibles qu'après publication par la direction.
                            </p>
                        @else
                            <div class="parent-dashboard-records">
                                @foreach($child['publishedBulletins'] as $pub)
                                    <div class="parent-dashboard-record">
                                        <div class="parent-dashboard-record-detail">
                                            <strong>{{ $pub->formation->name ?? 'Formation' }}</strong>
                                            <span>{{ $pub->academicSession->name ?? 'Session' }}</span>
                                        </div>
                                        <a href="{{ route('parent.bulletins.show', ['child' => $child['user']->id, 'publication' => $pub->id]) }}"
                                           class="parent-dashboard-action">
                                            Consulter
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    <section class="parent-dashboard-panel">
                        <div class="parent-dashboard-panel-heading">
                            <h3><span class="material-symbols-outlined" aria-hidden="true">co_present</span>Présences récentes</h3>
                        </div>

                        @if($child['recentAttendances']->isEmpty())
                            <p class="parent-dashboard-empty-text">Aucune présence enregistrée pour le moment.</p>
                        @else
                            <div class="parent-dashboard-records">
                                @foreach($child['recentAttendances'] as $attendance)
                                    @php
                                        $attendanceStatus = [
                                            'present' => 'Présent',
                                            'absent' => 'Absent',
                                            'late' => 'En retard',
                                        ][$attendance->status] ?? $attendance->status;
                                    @endphp
                                    <div class="parent-dashboard-record">
                                        <div class="parent-dashboard-record-detail">
                                            <strong>{{ $attendance->course?->display_name ?? 'Séance de cours' }}</strong>
                                            <span>{{ $attendance->date?->format('d/m/Y') }}</span>
                                        </div>
                                        <span class="parent-dashboard-attendance-status status-{{ $attendance->status }}">{{ $attendanceStatus }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    {{-- 4. Situation de paiement (Lecture seule) --}}
                    <section class="parent-dashboard-panel">
                        <div class="parent-dashboard-panel-heading">
                            <h3><span class="material-symbols-outlined" aria-hidden="true">account_balance_wallet</span>Situation financière</h3>
                        </div>
                        <div style="display:flex; flex-wrap:wrap; gap:0.5rem 1rem; margin:-0.25rem 0 1rem; font-size:0.75rem; color:var(--color-on-surface-variant);">
                            <span>Total payé : <strong style="color:#16a34a;">{{ number_format($child['financialTotalPaid'], 0, ',', ' ') }} FCFA</strong></span>
                            <span>Solde dû : <strong style="color:{{ $child['financialBalance'] > 0 ? '#dc2626' : '#16a34a' }};">{{ number_format($child['financialBalance'], 0, ',', ' ') }} FCFA</strong></span>
                        </div>

                        @if($financialEnrollments->isEmpty())
                            <p style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin:0;">Aucune inscription financière trouvée pour cet enfant.</p>
                        @else
                            <div style="display:flex; flex-direction:column; gap:0.875rem;">
                                @foreach($financialEnrollments as $financialEnrollment)
                                    @php
                                        $prixFormation = (float) $financialEnrollment->prix_formation;
                                        $montantDu = (float) $financialEnrollment->montant_du;
                                        $bourse = max(0, $prixFormation - $montantDu);
                                        $statutPaiement = $financialEnrollment->statut_paiement;
                                        $statutStyles = [
                                            'solde' => ['label' => 'Soldé', 'background' => '#ecfdf5', 'color' => '#065f46'],
                                            'partiel' => ['label' => 'Partiel', 'background' => '#fef3c7', 'color' => '#92400e'],
                                            'impaye' => ['label' => 'Impayé', 'background' => '#fef2f2', 'color' => '#991b1b'],
                                        ];
                                        $statut = $statutStyles[$statutPaiement] ?? ['label' => $statutPaiement, 'background' => '#f3f4f6', 'color' => '#374151'];
                                    @endphp
                                    <div style="padding:0.875rem; border:1px solid var(--color-outline-variant); border-radius:0.625rem;">
                                        <div style="display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:0.5rem; margin-bottom:0.75rem;">
                                            <div>
                                                <div style="font-size:0.8125rem; font-weight:700; color:var(--color-on-surface);">
                                                    {{ $financialEnrollment->formation->name ?? 'Formation' }}
                                                </div>
                                                <div style="font-size:0.6875rem; color:var(--color-on-surface-variant); margin-top:0.2rem;">
                                                    {{ $financialEnrollment->academicSession->name ?? 'Session non précisée' }}
                                                </div>
                                            </div>
                                            <span style="padding:0.2rem 0.5rem; border-radius:9999px; background:{{ $statut['background'] }}; color:{{ $statut['color'] }}; font-size:0.625rem; font-weight:700;">
                                                {{ $statut['label'] }}
                                            </span>
                                        </div>
                                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(110px, 1fr)); gap:0.625rem; font-size:0.75rem;">
                                            <div>
                                                <div style="color:var(--color-on-surface-variant);">Prix formation</div>
                                                <strong style="color:var(--color-on-surface);">{{ number_format($prixFormation, 0, ',', ' ') }} FCFA</strong>
                                            </div>
                                            <div>
                                                <div style="color:var(--color-on-surface-variant);">Bourse / réduction</div>
                                                <strong style="color:var(--color-on-surface);">{{ number_format($bourse, 0, ',', ' ') }} FCFA</strong>
                                            </div>
                                            <div>
                                                <div style="color:var(--color-on-surface-variant);">Montant dû</div>
                                                <strong style="color:var(--color-on-surface);">{{ number_format($montantDu, 0, ',', ' ') }} FCFA</strong>
                                            </div>
                                            <div>
                                                <div style="color:var(--color-on-surface-variant);">Déjà payé</div>
                                                <strong style="color:#16a34a;">{{ number_format($financialEnrollment->total_paye, 0, ',', ' ') }} FCFA</strong>
                                            </div>
                                            <div>
                                                <div style="color:var(--color-on-surface-variant);">Solde restant</div>
                                                <strong style="color:{{ $financialEnrollment->solde > 0 ? '#dc2626' : '#16a34a' }};">
                                                    {{ number_format($financialEnrollment->solde, 0, ',', ' ') }} FCFA
                                                </strong>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <div style="font-size:0.6875rem; color:var(--color-on-surface-variant); font-style:italic;">
                                    Consultation uniquement. Tout règlement s'effectue auprès de l'administration.
                                </div>
                            </div>
                        @endif
                    </section>

                </div>
            </article>
        @endforeach
    </div>
@endif

<x-alumni-showcase :alumnis="$alumniShowcase" title="Ils ont réussi à l'IFAM" />

</div>
@endsection
