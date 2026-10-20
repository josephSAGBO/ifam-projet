@extends('layouts.role')
@section('title', 'Mes Présences - Espace Étudiant')
@section('role_label', 'Étudiant')
@section('home_route', route('student.dashboard'))

@section('nav_links')
    @include('student.partials.nav')
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Mes Présences
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Consultez votre assiduité par module et par séance
        </p>
    </div>

    @if($enrollments->isNotEmpty())
    <div style="display:flex; align-items:center; flex-wrap:wrap; gap:0.5rem;">
        @foreach($enrollments as $enrollment)
            <span style="font-size:0.75rem; font-weight:600; padding:0.375rem 0.75rem; border-radius:9999px; background:var(--color-surface-container-high); color:var(--color-on-surface);">
                {{ $enrollment->formation->name ?? 'Formation' }}
            </span>
            <span style="font-size:0.75rem; font-weight:700; padding:0.375rem 0.75rem; border-radius:9999px; background:var(--color-primary-container); color:var(--color-on-primary);">
                {{ $enrollment->academicSession->name ?? 'Session' }}
            </span>
        @endforeach
    </div>
    @endif
</div>

@if($enrollments->isEmpty())
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:3.5rem 1.5rem; text-align:center; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    <span class="material-symbols-outlined" style="font-size:52px; color:var(--color-on-surface-variant); opacity:0.35; margin-bottom:1rem; display:block;">event_busy</span>
    <h3 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem;">Aucune inscription accessible</h3>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0 auto; max-width:400px;">
        Votre dossier n'est lié à aucune inscription active ou terminée. Contactez l'administration.
    </p>
    <a href="{{ route('contact.index') }}"
       style="display:inline-flex; align-items:center; gap:0.5rem; margin-top:1.5rem; padding:0.625rem 1.25rem; border-radius:0.5rem;
              background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none; font-weight:700; font-size:0.875rem;">
        <span class="material-symbols-outlined" style="font-size:18px;">mail</span>
        Contacter l'administration
    </a>
</div>
@else

{{-- KPI globaux --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1.25rem; margin-bottom:2rem;">

    {{-- Taux global --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); border-left:4px solid {{ $attendanceRate >= 75 ? '#059669' : ($attendanceRate >= 50 ? '#d97706' : '#e11d48') }};">
        <div style="font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-on-surface-variant); margin-bottom:0.5rem;">Taux de présence</div>
        <div style="font-size:2rem; font-weight:800; font-family:var(--font-display); color:var(--color-on-surface); margin-bottom:0.375rem;">{{ $attendanceRate }}%</div>
        <div style="font-size:0.75rem; font-weight:600; padding:0.25rem 0.625rem; border-radius:9999px; display:inline-flex; align-items:center; gap:0.25rem;
                    background:{{ $attendanceRate >= 75 ? '#ecfdf5' : ($attendanceRate >= 50 ? '#fffbeb' : '#fff1f2') }};
                    color:{{ $attendanceRate >= 75 ? '#065f46' : ($attendanceRate >= 50 ? '#92400e' : '#9f1239') }};">
            <span style="width:6px; height:6px; border-radius:50%; background:{{ $attendanceRate >= 75 ? '#059669' : ($attendanceRate >= 50 ? '#d97706' : '#e11d48') }};"></span>
            {{ $attendanceRate >= 75 ? 'Seuil atteint' : ($attendanceRate >= 50 ? 'En dessous du seuil' : 'Insuffisant') }}
        </div>
    </div>

    {{-- Présent --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <div style="font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-on-surface-variant); margin-bottom:0.5rem;">Présent</div>
        <div style="font-size:2rem; font-weight:800; font-family:var(--font-display); color:#059669; margin-bottom:0.25rem;">{{ $presentCount }}</div>
        <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">séance(s)</div>
    </div>

    {{-- Absent --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <div style="font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-on-surface-variant); margin-bottom:0.5rem;">Absent</div>
        <div style="font-size:2rem; font-weight:800; font-family:var(--font-display); color:#e11d48; margin-bottom:0.25rem;">{{ $absentCount }}</div>
        <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">séance(s)</div>
    </div>

    {{-- Justifié --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <div style="font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-on-surface-variant); margin-bottom:0.5rem;">Justifié</div>
        <div style="font-size:2rem; font-weight:800; font-family:var(--font-display); color:#d97706; margin-bottom:0.25rem;">{{ $justifiedCount }}</div>
        <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">séance(s)</div>
    </div>

    {{-- Retard --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <div style="font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-on-surface-variant); margin-bottom:0.5rem;">Retard</div>
        <div style="font-size:2rem; font-weight:800; font-family:var(--font-display); color:#7c3aed; margin-bottom:0.25rem;">{{ $lateCount }}</div>
        <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">séance(s)</div>
    </div>
</div>

@if($byModule->isNotEmpty())
{{-- Résumé par module --}}
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); margin-bottom:2rem;">
    <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1.25rem;">Assiduité par module</h2>
    <div style="display:flex; flex-direction:column; gap:0.875rem;">
        @foreach($byModule as $mod)
        <div style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
            <div style="min-width:180px; font-size:0.875rem; font-weight:600; color:var(--color-on-surface);">
                {{ $mod['module'] }}
                <div style="margin-top:0.2rem; font-size:0.6875rem; font-weight:500; color:var(--color-on-surface-variant);">
                    {{ $mod['formation'] }} · {{ $mod['academicSession'] }}
                </div>
            </div>
            <div style="flex:1; min-width:120px;">
                <div style="height:8px; background:var(--color-surface-container-high); border-radius:9999px; overflow:hidden;">
                    <div style="height:100%; width:{{ $mod['rate'] }}%; background:{{ $mod['rate'] >= 75 ? '#059669' : ($mod['rate'] >= 50 ? '#d97706' : '#e11d48') }}; border-radius:9999px; transition:width 0.4s;"></div>
                </div>
            </div>
            <div style="font-size:0.875rem; font-weight:700; color:var(--color-on-surface); min-width:50px; text-align:right;">{{ $mod['rate'] }}%</div>
            <div style="font-size:0.75rem; color:var(--color-on-surface-variant); min-width:80px;">
                {{ $mod['present'] }}P / {{ $mod['absent'] }}A / {{ $mod['justified'] }}J
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@if($attendances->isEmpty())
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:3.5rem 1.5rem; text-align:center; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    <span class="material-symbols-outlined" style="font-size:52px; color:var(--color-on-surface-variant); opacity:0.35; margin-bottom:1rem; display:block;">how_to_reg</span>
    <h3 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem;">Aucune présence enregistrée</h3>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0 auto; max-width:400px;">
        Les feuilles de présence n'ont pas encore été saisies pour votre session.
    </p>
</div>
@else
{{-- Tableau détaillé --}}
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden; border:1px solid var(--color-surface-container);">
    <div style="padding:1.25rem 1.5rem; border-bottom:1px solid var(--color-outline-variant);">
        <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0;">Détail des séances</h2>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="background:var(--color-surface-container-low); border-bottom:2px solid var(--color-outline-variant);">
                    <th style="text-align:left; padding:0.875rem 1.5rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Date</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Module / Cours</th>
                    <th style="text-align:center; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Statut</th>
                    <th style="text-align:left; padding:0.875rem 1.5rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Observation</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $att)
                @php
                    $statusColors = [
                        'present'          => ['bg' => '#ecfdf5', 'color' => '#065f46', 'dot' => '#059669', 'label' => 'Présent'],
                        'absent'           => ['bg' => '#fff1f2', 'color' => '#9f1239', 'dot' => '#e11d48', 'label' => 'Absent'],
                        'justified_absent' => ['bg' => '#fffbeb', 'color' => '#92400e', 'dot' => '#d97706', 'label' => 'Abs. justifié'],
                        'late'             => ['bg' => '#f5f3ff', 'color' => '#5b21b6', 'dot' => '#7c3aed', 'label' => 'Retard'],
                    ];
                    $sc = $statusColors[$att->status] ?? ['bg' => '#f3f4f6', 'color' => '#374151', 'dot' => '#9ca3af', 'label' => ucfirst($att->status)];
                @endphp
                <tr style="border-bottom:1px solid var(--color-surface-container);">
                    <td style="padding:0.75rem 1.5rem; color:var(--color-on-surface); font-weight:500;">
                        {{ $att->date ? $att->date->format('d/m/Y') : '—' }}
                    </td>
                    <td style="padding:0.75rem 1rem;">
                        <div style="font-weight:600; color:var(--color-on-surface);">{{ $att->course->module->name ?? '—' }}</div>
                        <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">{{ $att->course->display_name ?? '' }}</div>
                        <div style="font-size:0.6875rem; color:var(--color-on-surface-variant);">{{ $att->course->formation->name ?? 'Formation' }} · {{ $att->course->academicSession->name ?? 'Session' }}</div>
                    </td>
                    <td style="padding:0.75rem 1rem; text-align:center;">
                        <span style="font-size:0.75rem; font-weight:700; padding:0.25rem 0.75rem; border-radius:9999px; display:inline-flex; align-items:center; gap:0.35rem;
                                     background:{{ $sc['bg'] }}; color:{{ $sc['color'] }};">
                            <span style="width:6px; height:6px; border-radius:50%; background:{{ $sc['dot'] }};"></span>
                            {{ $sc['label'] }}
                        </span>
                    </td>
                    <td style="padding:0.75rem 1.5rem; font-size:0.8125rem; color:var(--color-on-surface-variant);">
                        {{ $att->remarks ?? '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endif

@endsection
