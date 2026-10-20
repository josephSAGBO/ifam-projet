@extends('layouts.role')
@section('title', 'Mes Notes & Moyennes - Espace Étudiant')
@section('role_label', 'Étudiant')
@section('home_route', route('student.dashboard'))

@section('nav_links')
    @include('student.partials.nav')
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Mes Notes & Relevé de Notes
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Consultez vos évaluations et moyennes pour vos inscriptions actives ou terminées
        </p>
    </div>

    @if(!empty($modulesData))
    <button onclick="window.print()"
            style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem; border-radius:0.75rem;
                   background:var(--color-surface-container-high); color:var(--color-on-surface); border:none; cursor:pointer;
                   font-size:0.875rem; font-weight:600; box-shadow:0 1px 3px rgba(0,0,0,0.05); transition:all 0.15s;">
        <span class="material-symbols-outlined" style="font-size:18px;">print</span>
        Imprimer le relevé
    </button>
    @endif
</div>

{{-- KPI Résumé --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:1.25rem; margin-bottom:2rem;">
    {{-- Moyenne Générale --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); border-left:4px solid var(--color-primary-container);">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
            <span style="font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-on-surface-variant);">
                Moyenne des inscriptions affichées
            </span>
            <span class="material-symbols-outlined" style="color:var(--color-primary-container); font-size:24px;">grade</span>
        </div>
        <div style="display:flex; align-items:baseline; gap:0.5rem; margin-bottom:0.5rem;">
            <span style="font-size:2rem; font-weight:800; color:var(--color-on-surface); font-family:var(--font-display);">
                {{ $generalAverage !== null ? number_format($generalAverage, 2, ',', ' ') : '—' }}
            </span>
            <span style="font-size:1rem; color:var(--color-on-surface-variant); font-weight:600;">/ 20</span>
        </div>
        <div>
            @if($generalAverage !== null)
                @if($isValidated)
                    <span style="font-size:0.6875rem; font-weight:700; padding:0.25rem 0.625rem; border-radius:9999px; background:#ecfdf5; color:#065f46; display:inline-flex; align-items:center; gap:0.25rem;">
                        <span style="width:6px; height:6px; border-radius:50%; background:#059669;"></span>
                        Seuil validé (≥ 10/20)
                    </span>
                @else
                    <span style="font-size:0.6875rem; font-weight:700; padding:0.25rem 0.625rem; border-radius:9999px; background:#fff1f2; color:#9f1239; display:inline-flex; align-items:center; gap:0.25rem;">
                        <span style="width:6px; height:6px; border-radius:50%; background:#e11d48;"></span>
                        Non validé (< 10/20)
                    </span>
                @endif
            @else
                <span style="font-size:0.75rem; color:var(--color-on-surface-variant);">Aucune note enregistrée</span>
            @endif
        </div>
    </div>

    {{-- Évaluations --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
            <span style="font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-on-surface-variant);">
                Évaluations Réalisées
            </span>
            <span class="material-symbols-outlined" style="color:var(--color-on-surface-variant); font-size:24px;">assignment_turned_in</span>
        </div>
        <div style="font-size:2rem; font-weight:800; color:var(--color-on-surface); font-family:var(--font-display); margin-bottom:0.5rem;">
            {{ $totalEvaluations }}
        </div>
        <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
            Devoirs, interrogations et examens
        </div>
    </div>

    {{-- Modules évalués --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
            <span style="font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-on-surface-variant);">
                Modules Suivis
            </span>
            <span class="material-symbols-outlined" style="color:var(--color-on-surface-variant); font-size:24px;">collections_bookmark</span>
        </div>
        <div style="font-size:2rem; font-weight:800; color:var(--color-on-surface); font-family:var(--font-display); margin-bottom:0.5rem;">
            {{ count($modulesData) }}
        </div>
        <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
            Modules, formation et session
        </div>
    </div>
</div>

@if(empty($modulesData))
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:3.5rem 1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); text-align:center;">
    <span class="material-symbols-outlined" style="font-size:52px; color:var(--color-on-surface-variant); opacity:0.35; margin-bottom:1rem; display:block;">grading</span>
    <h3 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem;">Aucune note publiée</h3>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0 auto; max-width:400px;">
        Vos professeurs n'ont pas encore publié de notes pour vos inscriptions actives ou terminées.
    </p>
</div>
@else
<div style="display:flex; flex-direction:column; gap:1.5rem;">
    @foreach($modulesData as $item)
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden; border:1px solid var(--color-surface-container);">
        {{-- En-tête du module --}}
        <div style="padding:1.25rem 1.5rem; background:var(--color-surface-container-low); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem; border-bottom:1px solid var(--color-outline-variant);">
            <div>
                <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem;">
                    <span style="font-size:0.6875rem; font-weight:800; padding:0.1875rem 0.5rem; border-radius:0.375rem; background:var(--color-surface-container-high); color:var(--color-on-surface-variant);">
                        {{ $item['module']->code ?? 'MOD' }}
                    </span>
                    <h3 style="font-size:1.0625rem; font-weight:700; color:var(--color-on-surface); margin:0;">
                        {{ $item['module']->name }}
                    </h3>
                </div>
                <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                    Coefficient du module : <strong>{{ $item['coefficient'] }}</strong>
                </div>
                <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-top:0.5rem;">
                    <span style="font-size:0.6875rem; font-weight:600; padding:0.2rem 0.5rem; border-radius:9999px; background:var(--color-surface-container-high); color:var(--color-on-surface-variant);">
                        {{ $item['formation']->name ?? 'Formation' }}
                    </span>
                    <span style="font-size:0.6875rem; font-weight:700; padding:0.2rem 0.5rem; border-radius:9999px; background:var(--color-primary-container); color:var(--color-on-primary);">
                        {{ $item['academicSession']->name ?? 'Session' }}
                    </span>
                </div>
            </div>

            <div style="display:flex; align-items:center; gap:0.75rem;">
                <span style="font-size:0.8125rem; color:var(--color-on-surface-variant); font-weight:500;">Moyenne module :</span>
                <span style="font-size:1.125rem; font-weight:800; padding:0.25rem 0.75rem; border-radius:0.5rem;
                             background:{{ ($item['average'] !== null && $item['average'] >= 10) ? '#ecfdf5' : '#fff1f2' }};
                             color:{{ ($item['average'] !== null && $item['average'] >= 10) ? '#065f46' : '#9f1239' }};">
                    {{ $item['average'] !== null ? number_format($item['average'], 2, ',', ' ') . ' / 20' : '—' }}
                </span>
            </div>
        </div>

        {{-- Tableau des évaluations --}}
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
                <thead>
                    <tr style="background:var(--color-surface-container-lowest); border-bottom:1px solid var(--color-surface-container);">
                        <th style="text-align:left; padding:0.75rem 1.5rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Évaluation</th>
                        <th style="text-align:left; padding:0.75rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Date</th>
                        <th style="text-align:right; padding:0.75rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Note Brute</th>
                        <th style="text-align:right; padding:0.75rem 1.5rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Note / 20</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($item['grades'] as $g)
                    @php
                        $eval = $g->evaluation;
                        $max = 20;
                        $score20 = $g->is_absent ? 0 : ($max > 0 ? ($g->score / $max) * 20 : 0);
                    @endphp
                    <tr style="border-bottom:1px solid var(--color-surface-container);">
                        <td style="padding:0.75rem 1.5rem;">
                            <div style="font-weight:600; color:var(--color-on-surface);">{{ $eval->titre }}</div>
                            <div style="font-size:0.6875rem; color:var(--color-on-surface-variant); text-transform:capitalize;">{{ $eval->type ?? 'Devoir' }}</div>
                        </td>
                        <td style="padding:0.75rem 1rem; color:var(--color-on-surface-variant); font-size:0.8125rem;">
                            {{ $eval->date ? $eval->date->format('d/m/Y') : '—' }}
                        </td>
                        <td style="padding:0.75rem 1rem; text-align:right; font-weight:500;">
                            @if($g->is_absent)
                                <span style="font-size:0.75rem; font-weight:700; color:#dc2626;">Absent</span>
                            @else
                                <span style="color:var(--color-on-surface);">{{ number_format($g->score, 2, ',', ' ') }}</span>
                                <span style="font-size:0.75rem; color:var(--color-on-surface-variant);">/ {{ $max }}</span>
                            @endif
                        </td>
                        <td style="padding:0.75rem 1.5rem; text-align:right;">
                            @if($g->is_absent)
                                <span style="font-weight:700; color:#dc2626;">0,00</span>
                            @else
                                <span style="font-weight:700; font-size:0.9375rem; color:{{ $score20 >= 10 ? '#059669' : '#e11d48' }};">
                                    {{ number_format($score20, 2, ',', ' ') }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection
