@extends('layouts.admin')

@section('title', 'Bulletins')

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Bulletins
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Génération et consultation des bulletins académiques par session et formation.
        </p>
    </div>

</div>

<form method="GET" action="{{ route('admin.bulletins.index') }}" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1rem; margin-bottom:1.5rem; padding:1.25rem; background:var(--color-surface-container-lowest); border:1px solid var(--color-outline-variant); border-radius:1rem;">
    @includeWhen($sites->isNotEmpty(), 'admin.partials.site-filter')
    <div>
        <label for="session_id" style="display:block; margin-bottom:0.375rem; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant);">Session</label>
        <select id="session_id" name="session_id" onchange="this.form.submit()" style="width:100%; height:42px; border:1px solid var(--color-outline-variant); border-radius:0.5rem; background:var(--color-surface); color:var(--color-on-surface); padding:0 0.75rem;">
            <option value="">Sélectionner une session</option>
            @foreach($sessions as $session)
                <option value="{{ $session->id }}" {{ (string) $selectedSessionId === (string) $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="formation_id" style="display:block; margin-bottom:0.375rem; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant);">Formation</label>
        <select id="formation_id" name="formation_id" onchange="this.form.submit()" {{ $selectedSessionId ? '' : 'disabled' }} style="width:100%; height:42px; border:1px solid var(--color-outline-variant); border-radius:0.5rem; background:var(--color-surface); color:var(--color-on-surface); padding:0 0.75rem;">
            <option value="">Sélectionner une formation</option>
            @foreach($formations as $formation)
                <option value="{{ $formation->id }}" {{ (string) $selectedFormationId === (string) $formation->id ? 'selected' : '' }}>{{ $formation->name }}</option>
            @endforeach
        </select>
    </div>
</form>

@if($selectedSessionId && $selectedFormationId)
    <div style="background:var(--color-surface-container-lowest); border:1px solid var(--color-outline-variant); border-radius:1rem; overflow:hidden;">
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
                <thead>
                    <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                        <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Étudiant</th>
                        <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Moyenne générale</th>
                        <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $row)
                        <tr style="border-bottom:1px solid var(--color-surface-container);">
                            <td style="padding:0.875rem 1rem; color:var(--color-on-surface); font-weight:600;">
                                {{ $row['student']->user->full_name ?? '—' }}
                            </td>
                            <td style="padding:0.875rem 1rem; color:var(--color-on-surface); font-weight:700;">
                                {{ $row['average'] !== null ? number_format($row['average'], 2, ',', ' ') : '—' }}
                            </td>
                            <td style="padding:0.875rem 1rem;">
                                <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                                    <a href="{{ route('admin.bulletins.show', ['student' => $row['student']->id, 'session_id' => $selectedSessionId, 'formation_id' => $selectedFormationId]) }}" style="display:inline-flex; align-items:center; justify-content:center; padding:0.5rem 0.875rem; border-radius:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none; font-size:0.8125rem; font-weight:700;">
                                        Voir bulletin
                                    </a>
                                    <a href="{{ route('admin.bulletins.pdf', ['student' => $row['student']->id, 'session_id' => $selectedSessionId, 'formation_id' => $selectedFormationId]) }}" target="_blank" title="Télécharger le bulletin en PDF" style="display:inline-flex; align-items:center; gap:0.25rem; padding:0.5rem 0.75rem; border-radius:0.5rem; background:var(--color-surface-container-high); color:var(--color-on-surface); text-decoration:none; font-size:0.8125rem; font-weight:700; border:1px solid var(--color-outline-variant);">
                                        <span class="material-symbols-outlined" style="font-size:18px;">download</span>
                                        PDF
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="padding:2rem; text-align:center; color:var(--color-on-surface-variant);">
                                Aucun étudiant actif pour cette formation dans cette session.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@else
    <div style="background:var(--color-surface-container-lowest); border:1px solid var(--color-outline-variant); border-radius:1rem; padding:3rem 2rem; text-align:center; color:var(--color-on-surface-variant);">
        Sélectionnez une session et une formation pour générer les bulletins.
    </div>
@endif

@endsection
