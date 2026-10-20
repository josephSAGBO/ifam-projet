@extends('layouts.admin')

@section('title', 'Détail des résultats')

@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.results.index', ['session_id' => $selectedSessionId, 'formation_id' => $selectedFormationId]) }}" style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container); display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Résultats de {{ $student->user->full_name ?? 'Étudiant' }}
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            {{ $formation->name }} · Session {{ $selectedSessionId }}
        </p>
    </div>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
    <div style="background:var(--color-surface-container-lowest); border:1px solid var(--color-outline-variant); border-radius:1rem; padding:1.25rem;">
        <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant); margin-bottom:0.5rem;">Moyenne générale</div>
        <div style="font-size:2rem; font-weight:800; color:var(--color-on-surface);">{{ $average !== null ? number_format($average, 2, ',', ' ') : '—' }}</div>
    </div>
</div>

<div style="background:var(--color-surface-container-lowest); border:1px solid var(--color-outline-variant); border-radius:1rem; overflow:hidden;">
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Module</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Moyenne</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Moyenne pondérée</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detail as $row)
                    <tr style="border-bottom:1px solid var(--color-surface-container);">
                        <td style="padding:0.875rem 1rem; color:var(--color-on-surface); font-weight:600;">
                            {{ $row['module']->name }}
                        </td>
                        <td style="padding:0.875rem 1rem; color:var(--color-on-surface);">
                            {{ $row['moyenne_module'] !== null ? number_format($row['moyenne_module'], 2, ',', ' ') : '—' }}
                        </td>
                        <td style="padding:0.875rem 1rem; color:var(--color-on-surface);">
                            {{ $row['moyenne_ponderee'] !== null ? number_format($row['moyenne_ponderee'], 2, ',', ' ') : '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="padding:2rem; text-align:center; color:var(--color-on-surface-variant);">
                            Aucune moyenne calculée pour ce module.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
