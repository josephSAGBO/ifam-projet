@extends('layouts.admin')

@section('title', 'Saisie des notes - ' . $evaluation->titre)

@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.grades.index') }}"
       style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
              display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
              transition:all 0.15s;"
       onmouseover="this.style.background='var(--color-surface-container-high)'"
       onmouseout="this.style.background='var(--color-surface-container)'">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            {{ $evaluation->titre }}
            <span style="display:inline-flex; align-items:center; padding:0.125rem 0.5rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; vertical-align:middle;
                {{ $evaluation->type === 'devoir' ? 'background:#eff6ff; color:#1d4ed8;' : 'background:#faf5ff; color:#7c3aed;' }}">
                {{ $evaluation->type === 'devoir' ? 'Devoir' : 'Contrôle continu' }}
            </span>
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            {{ $evaluation->module->name ?? ($evaluation->course->display_name ?? 'Module') }} | Barème : /{{ $bareme }}
        </p>
    </div>
</div>

<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden;">
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Étudiant</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant); width:120px;">Note (/{{ $bareme }})</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant); width:100px;">Absent</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Commentaire</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                @php
                    $currentGrade = $grades->get($student->id);
                    $displayScore = $currentGrade ? $currentGrade->score : '—';
                    $displayAbsent = $currentGrade && $currentGrade->is_absent ? 'Oui' : 'Non';
                    $displayComment = $currentGrade && filled($currentGrade->comments) ? $currentGrade->comments : '—';
                @endphp
                <tr style="border-bottom:1px solid var(--color-surface-container); transition:background 0.15s;"
                    onmouseover="this.style.background='var(--color-surface-container-low)'"
                    onmouseout="this.style.background=''">

                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface); font-weight:600;">
                        {{ $student->user->last_name ?? '' }} {{ $student->user->first_name ?? '' }}
                    </td>

                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface); font-weight:700;">
                        {{ $displayScore }}
                    </td>

                    <td style="padding:0.875rem 1rem;">
                        <span style="font-size:0.75rem; padding:0.125rem 0.5rem; border-radius:9999px; background:{{ $displayAbsent === 'Oui' ? '#fef2f2' : '#ecfdf5' }}; color:{{ $displayAbsent === 'Oui' ? '#991b1b' : '#065f46' }}; font-weight:700;">
                            {{ $displayAbsent }}
                        </span>
                    </td>

                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">
                        {{ $displayComment }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="padding:2rem; text-align:center; color:var(--color-on-surface-variant);">
                        Aucun étudiant trouvé pour cette formation.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
