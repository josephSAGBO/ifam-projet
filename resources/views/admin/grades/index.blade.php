@extends('layouts.admin')

@section('title', 'Saisie des notes')

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Saisie des notes
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Parcourez les notes par session, formation et module.
        </p>
    </div>
</div>

<form method="GET" action="{{ route('admin.grades.index') }}" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(210px, 1fr)); gap:1rem; align-items:end; margin-bottom:1.5rem; padding:1.25rem; background:var(--color-surface-container-lowest); border-radius:0.75rem; border:1px solid var(--color-outline-variant);">
    <div>
        <label for="session-select" style="display:block; margin-bottom:0.375rem; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant);">Session</label>
        <select id="session-select" name="session_id" onchange="this.form.submit()" style="width:100%; height:42px; padding:0 0.75rem; border:1px solid var(--color-outline-variant); border-radius:0.5rem; background:var(--color-surface); color:var(--color-on-surface);">
            <option value="">Sélectionner une session…</option>
            @foreach($sessions as $session)
                <option value="{{ $session->id }}" {{ $selectedSessionId == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="formation-select" style="display:block; margin-bottom:0.375rem; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant);">Formation</label>
        <select id="formation-select" name="formation_id" onchange="this.form.submit()" {{ $selectedSessionId ? '' : 'disabled' }} style="width:100%; height:42px; padding:0 0.75rem; border:1px solid var(--color-outline-variant); border-radius:0.5rem; background:var(--color-surface); color:var(--color-on-surface);">
            <option value="">Sélectionner une formation…</option>
            @foreach($formations as $formation)
                <option value="{{ $formation->id }}" {{ $selectedFormationId == $formation->id ? 'selected' : '' }}>{{ $formation->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="module-select" style="display:block; margin-bottom:0.375rem; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant);">Module</label>
        <select id="module-select" name="module_id" onchange="this.form.submit()" {{ $selectedFormationId ? '' : 'disabled' }} style="width:100%; height:42px; padding:0 0.75rem; border:1px solid var(--color-outline-variant); border-radius:0.5rem; background:var(--color-surface); color:var(--color-on-surface);">
            <option value="">Sélectionner un module…</option>
            @foreach($modules as $module)
                <option value="{{ $module->id }}" {{ $selectedModuleId == $module->id ? 'selected' : '' }}>{{ $module->code }} — {{ $module->name }}</option>
            @endforeach
        </select>
    </div>
    @if($selectedSessionId || $selectedFormationId || $selectedModuleId)
        <a href="{{ route('admin.grades.index') }}" style="height:42px; display:inline-flex; align-items:center; justify-content:center; gap:0.375rem; padding:0 0.875rem; border-radius:0.5rem; background:var(--color-surface-container-high); color:var(--color-on-surface); text-decoration:none; font-size:0.8125rem; font-weight:600;">
            <span class="material-symbols-outlined" style="font-size:17px;">restart_alt</span> Réinitialiser
        </a>
    @endif
</form>

@if(! $selectedSessionId)
    <div style="padding:3rem 1.5rem; border-radius:0.75rem; background:var(--color-surface-container-lowest); text-align:center; color:var(--color-on-surface-variant);">
        Sélectionnez une session pour commencer.
    </div>
@elseif(! $selectedFormationId)
    <div style="padding:3rem 1.5rem; border-radius:0.75rem; background:var(--color-surface-container-lowest); text-align:center; color:var(--color-on-surface-variant);">
        Sélectionnez une formation pour cette session.
    </div>
@elseif(! $selectedModuleId)
    <div style="padding:3rem 1.5rem; border-radius:0.75rem; background:var(--color-surface-container-lowest); text-align:center; color:var(--color-on-surface-variant);">
        Sélectionnez un module pour voir ses devoirs et contrôles continus.
    </div>
@elseif($evaluations->isEmpty())
    <div style="padding:3rem 1.5rem; border-radius:0.75rem; background:var(--color-surface-container-lowest); text-align:center; color:var(--color-on-surface-variant);">
        Aucune évaluation n’est enregistrée pour ce module dans cette session.
    </div>
@else
    <div style="background:var(--color-surface-container-lowest); border:1px solid var(--color-outline-variant); border-radius:0.75rem; overflow:hidden;">
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
                <thead><tr style="background:var(--color-surface-container-low); text-align:left;">
                    <th style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">Évaluation</th>
                    <th style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">Type</th>
                    <th style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">Enseignant</th>
                    <th style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">Date</th>
                    <th style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">Notes</th>
                    <th style="padding:0.875rem 1rem;"></th>
                </tr></thead>
                <tbody>
                    @foreach($evaluations as $evaluation)
                        <tr style="border-top:1px solid var(--color-surface-container);">
                            <td style="padding:0.875rem 1rem; font-weight:700; color:var(--color-on-surface);">{{ $evaluation->titre }}</td>
                            <td style="padding:0.875rem 1rem;">{{ $evaluation->type === 'devoir' ? 'Devoir' : ucfirst(str_replace('_', ' ', $evaluation->type)) }}</td>
                            <td style="padding:0.875rem 1rem;">{{ $evaluation->assignedTeacher->user->full_name ?? $evaluation->course->enseignant->user->full_name ?? '—' }}</td>
                            <td style="padding:0.875rem 1rem;">{{ $evaluation->date?->format('d/m/Y') ?? '—' }}</td>
                            <td style="padding:0.875rem 1rem;">{{ $evaluation->grades_count }}</td>
                            <td style="padding:0.875rem 1rem; text-align:right;">
                                <a href="{{ route('admin.grades.show', $evaluation) }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none; font-size:0.8125rem; font-weight:700;">
                                    <span class="material-symbols-outlined" style="font-size:17px;">table_view</span> Voir les notes
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
