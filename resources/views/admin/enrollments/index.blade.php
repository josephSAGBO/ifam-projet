@extends('layouts.admin')

@section('title', 'Inscriptions des étudiants')

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Inscriptions
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Gérez les inscriptions des étudiants aux formations et sessions académiques
        </p>
    </div>
    <a href="{{ route('admin.enrollments.create') }}"
       style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem;
              background:var(--color-primary-container); color:var(--color-on-primary); border-radius:0.75rem;
              text-decoration:none; font-size:0.875rem; font-weight:700;
              box-shadow:0 3px 10px rgba(228,0,102,0.3); transition:all 0.2s;"
       onmouseover="this.style.transform='translateY(-1px)'"
       onmouseout="this.style.transform=''">
        <span class="material-symbols-outlined" style="font-size:20px;">add_circle</span>
        Nouvelle inscription
    </a>
</div>

{{-- Filtres et Recherche --}}
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; margin-bottom:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    <form method="GET" action="{{ route('admin.enrollments.index') }}" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) 120px; gap:1rem; align-items:end;">
        <div>
            <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Rechercher un étudiant</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, prénom, matricule…"
                   style="width:100%; height:40px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">
        </div>
        <div>
            <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Formation</label>
            <select name="formation_id" style="width:100%; height:40px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">
                <option value="">Toutes les formations</option>
                @foreach($formations as $formation)
                    <option value="{{ $formation->id }}" {{ request('formation_id') == $formation->id ? 'selected' : '' }}>
                        {{ $formation->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Session académique</label>
            <select name="academic_session_id" style="width:100%; height:40px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">
                <option value="">Toutes les sessions</option>
                @foreach($sessions as $session)
                    <option value="{{ $session->id }}" {{ request('academic_session_id') == $session->id ? 'selected' : '' }}>
                        {{ $session->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Statut</label>
            <select name="status" style="width:100%; height:40px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">
                <option value="">Tous les statuts</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspendue</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Terminée</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulée</option>
            </select>
        </div>
        <div style="display:flex; gap:0.5rem;">
            <button type="submit"
                    style="flex:1; height:40px; border:none; border-radius:0.5rem; background:var(--color-surface-container-high); color:var(--color-on-surface); font-weight:600; cursor:pointer; font-size:0.8125rem;">
                Filtrer
            </button>
            @if(request()->hasAny(['search', 'formation_id', 'academic_session_id', 'status']))
                <a href="{{ route('admin.enrollments.index') }}"
                   style="height:40px; width:40px; display:flex; align-items:center; justify-content:center; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-on-surface-variant); text-decoration:none;">
                    <span class="material-symbols-outlined" style="font-size:18px;">close</span>
                </a>
            @endif
        </div>
    </form>
</div>

<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden;">
    @if($enrollments->isEmpty())
    <div style="padding:4rem 2rem; text-align:center; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined" style="font-size:48px; opacity:0.25; display:block; margin-bottom:1rem;">how_to_reg</span>
        <p style="font-size:0.9375rem; margin:0;">Aucune inscription enregistrée avec ces critères.</p>
    </div>
    @else
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Étudiant</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Formation</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Session</th>
                    <th style="text-align:right; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Montant dû / Solde</th>
                    <th style="text-align:center; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Paiement</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Statut</th>
                    <th style="padding:0.875rem 1rem;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($enrollments as $enrollment)
                <tr style="border-bottom:1px solid var(--color-surface-container); transition:background 0.15s;"
                    onmouseover="this.style.background='var(--color-surface-container-low)'"
                    onmouseout="this.style.background=''">
                    
                    <td style="padding:0.875rem 1rem;">
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            <div style="width:36px; height:36px; border-radius:9999px; background:var(--color-surface-container-high); color:var(--color-primary-container); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.8125rem;">
                                {{ $enrollment->studentProfile->user->initials ?? 'ET' }}
                            </div>
                            <div>
                                <a href="{{ route('admin.enrollments.show', $enrollment) }}" style="font-weight:600; color:var(--color-on-surface); text-decoration:none;">
                                    {{ $enrollment->studentProfile->user->full_name ?? '—' }}
                                </a>
                                <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                                    Matricule : {{ $enrollment->studentProfile->user->matricule ?? '—' }}
                                </div>
                            </div>
                        </div>
                    </td>
                    
                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface); font-weight:500;">
                        {{ $enrollment->formation->name ?? '—' }}
                    </td>

                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">
                        {{ $enrollment->academicSession->name ?? '—' }}
                    </td>

                    <td style="padding:0.875rem 1rem; text-align:right;">
                        <div style="font-weight:700; color:var(--color-on-surface);">{{ number_format($enrollment->montant_du, 2, ',', ' ') }} FCFA</div>
                        <div style="font-size:0.75rem; color:{{ $enrollment->solde > 0 ? '#dc2626' : '#16a34a' }};">
                            Solde : {{ number_format($enrollment->solde, 2, ',', ' ') }} FCFA
                        </div>
                    </td>

                    <td style="padding:0.875rem 1rem; text-align:center;">
                        @php
                            $pStatut = $enrollment->statut_paiement;
                            $pBadges = [
                                'solde'   => ['bg' => '#ecfdf5', 'color' => '#065f46', 'label' => 'Soldé'],
                                'partiel' => ['bg' => '#fef3c7', 'color' => '#92400e', 'label' => 'Partiel'],
                                'impaye'  => ['bg' => '#fef2f2', 'color' => '#991b1b', 'label' => 'Impayé'],
                            ];
                            $pB = $pBadges[$pStatut] ?? ['bg' => '#f3f4f6', 'color' => '#374151', 'label' => $pStatut];
                        @endphp
                        <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:{{ $pB['bg'] }}; color:{{ $pB['color'] }}; display:inline-flex; align-items:center;">
                            {{ $pB['label'] }}
                        </span>
                    </td>

                    <td style="padding:0.875rem 1rem;">
                        @php
                            $statusStyles = [
                                'active' => ['bg' => '#ecfdf5', 'color' => '#065f46', 'label' => 'Active'],
                                'suspended' => ['bg' => '#fef3c7', 'color' => '#92400e', 'label' => 'Suspendue'],
                                'completed' => ['bg' => '#eff6ff', 'color' => '#1e40af', 'label' => 'Terminée'],
                                'cancelled' => ['bg' => '#fef2f2', 'color' => '#991b1b', 'label' => 'Annulée'],
                            ];
                            $style = $statusStyles[$enrollment->status] ?? ['bg' => '#f3f4f6', 'color' => '#374151', 'label' => ucfirst($enrollment->status)];
                        @endphp
                        <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:{{ $style['bg'] }}; color:{{ $style['color'] }}; display:inline-flex; align-items:center;">
                            {{ $style['label'] }}
                        </span>
                    </td>

                    <td style="padding:0.875rem 1rem;">
                        <div style="display:flex; align-items:center; gap:0.5rem; justify-content:flex-end;">
                            <a href="{{ route('admin.enrollments.show', $enrollment) }}"
                               title="Consulter"
                               style="width:34px; height:34px; border-radius:0.5rem; display:flex; align-items:center; justify-content:center;
                                      background:var(--color-surface-container); color:var(--color-on-surface-variant); text-decoration:none; transition:all 0.15s;">
                                <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                            </a>
                            <a href="{{ route('admin.enrollments.edit', $enrollment) }}"
                               title="Modifier"
                               style="width:34px; height:34px; border-radius:0.5rem; display:flex; align-items:center; justify-content:center;
                                      background:var(--color-surface-container); color:var(--color-on-surface-variant); text-decoration:none; transition:all 0.15s;"
                               onmouseover="this.style.background='var(--color-surface-container-high)'"
                               onmouseout="this.style.background='var(--color-surface-container)'">
                                <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                            </a>
                            <form method="POST" action="{{ route('admin.enrollments.destroy', $enrollment) }}"
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette inscription ?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Supprimer"
                                        style="width:34px; height:34px; border-radius:0.5rem; border:none; cursor:pointer;
                                               background:var(--color-surface-container); color:#dc2626; transition:all 0.15s;"
                                        onmouseover="this.style.background='#fef2f2'"
                                        onmouseout="this.style.background='var(--color-surface-container)'">
                                    <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    @if($enrollments->hasPages())
    <div style="padding:1rem; border-top:1px solid var(--color-surface-container);">
        {{ $enrollments->links() }}
    </div>
    @endif
    @endif
</div>

@endsection
