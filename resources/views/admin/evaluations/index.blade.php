@extends('layouts.admin')

@section('title', 'Évaluations (Devoirs & CC)')

@section('content')

<div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1.75rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Devoirs et Contrôles Continus
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Gérez les devoirs (les contrôles continus sont gérés par les enseignants)
        </p>
    </div>
    <a href="{{ route('admin.evaluations.create') }}" class="interactive-hover"
       style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem;
              background:var(--color-primary-container); color:var(--color-on-primary); border-radius:0.75rem;
              text-decoration:none; font-size:0.875rem; font-weight:700;
              box-shadow:0 3px 10px rgba(228,0,102,0.3); transition:all 0.2s;"
       onmouseover="this.style.transform='translateY(-1px)'"
       onmouseout="this.style.transform=''">
        <span class="material-symbols-outlined" style="font-size:20px;">add_circle</span>
        Nouveau Devoir
    </a>
</div>

{{-- Filter Form --}}
<div style="background:var(--color-surface-container-low); padding:1rem 1.5rem; border-radius:1rem; margin-bottom:1.5rem; display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end;">
    <form action="{{ route('admin.evaluations.index') }}" method="GET" style="display:flex; gap:1rem; align-items:flex-end; flex-wrap:wrap;">
        @includeWhen($sites->isNotEmpty(), 'admin.partials.site-filter')
        @if($isResponsableEtudes)
        <div>
            <label for="formation_id" style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Formation</label>
            <select id="formation_id" name="formation_id"
                    style="height:38px; min-width:190px; padding:0 0.75rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); font-size:0.875rem;">
                <option value="">Toutes les formations</option>
                @foreach($formations as $formation)
                    <option value="{{ $formation->id }}" @selected($selectedFormationId === $formation->id)>{{ $formation->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div>
            <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Filtrer par Module</label>
            <select name="module_id" @if(!$isResponsableEtudes) onchange="this.form.submit()" @endif
                    style="height:38px; padding:0 1rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); font-size:0.875rem;">
                <option value="">Tous les modules</option>
                @foreach($modules as $module)
                    <option value="{{ $module->id }}" @selected($selectedModuleId === $module->id)>
                        {{ $module->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @if($isResponsableEtudes)
        <button type="submit" style="height:38px; padding:0 1rem; border:0; border-radius:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); font-weight:700; cursor:pointer;">
            Filtrer
        </button>
        @endif
        @if($selectedFormationId || $selectedModuleId || $selectedSiteId)
        <a href="{{ route('admin.evaluations.index') }}" style="height:38px; display:inline-flex; align-items:center; color:var(--color-primary); text-decoration:none; font-size:0.875rem; font-weight:600;">
            Réinitialiser
        </a>
        @endif
    </form>
</div>

<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden;">
    @if($evaluations->isEmpty())
    <div style="padding:4rem 2rem; text-align:center; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined" style="font-size:48px; opacity:0.25; display:block; margin-bottom:1rem;">quiz</span>
        <p style="font-size:0.9375rem; margin:0;">Aucune évaluation trouvée.</p>
    </div>
    @else
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Titre</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Module / Cours</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Type</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Enseignant assigné</th>
                    <!-- <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Créé par</th> -->
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Date</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Statut</th>
                    <th style="padding:0.875rem 1rem;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($evaluations as $eval)
                <tr style="border-bottom:1px solid var(--color-surface-container); transition:background 0.15s;"
                    onmouseover="this.style.background='var(--color-surface-container-low)'"
                    onmouseout="this.style.background=''">
                    
                    <td style="padding:0.875rem 1rem;">
                        <div style="color:var(--color-on-surface); font-weight:600;">
                            {{ $eval->titre }}
                        </div>
                    </td>
                    <td style="padding:0.875rem 1rem;">
                        <div style="font-size:0.875rem; color:var(--color-on-surface);">
                            @if($eval->type === 'devoir')
                                Module: {{ $eval->module->name ?? '-' }}
                            @else
                                Cours: {{ $eval->course->display_name ?? '-' }}
                            @endif
                        </div>
                    </td>
                    <td style="padding:0.875rem 1rem;">
                        <span style="padding:0.25rem 0.5rem; border-radius:0.375rem; font-size:0.75rem; font-weight:600; background:var(--color-surface-container-highest); color:var(--color-on-surface);">
                            {{ ucfirst(str_replace('_', ' ', $eval->type)) }}
                        </span>
                    </td>

                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface); font-weight:600;">
                        {{ $eval->assignedTeacher?->user?->full_name
                            ?? $eval->course?->enseignant?->user?->full_name
                            ?? $eval->course?->teacherProfile?->user?->full_name
                            ?? 'Non assigné' }}
                    </td>

                    <!-- <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">
                        {{ $eval->creator?->full_name ?? 'Non renseigné' }}
                    </td> -->

                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">
                        {{ $eval->date ? $eval->date->format('d/m/Y') : 'Non définie' }}
                    </td>

                    <td style="padding:0.875rem 1rem;">
                        <form method="POST" action="{{ route('admin.evaluations.status', $eval) }}" style="display:flex; align-items:center; gap:0.375rem;">
                            @csrf
                            @method('PATCH')
                            <select name="statut" aria-label="Statut de l’évaluation"
                                    style="height:36px; max-width:130px; padding:0 0.5rem; border:1px solid var(--color-outline-variant); border-radius:0.5rem; background:var(--color-surface); color:var(--color-on-surface);">
                                <option value="brouillon" {{ $eval->statut === 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                                <option value="ouverte" {{ $eval->statut === 'ouverte' ? 'selected' : '' }}>Ouverte</option>
                                <option value="cloturee" {{ $eval->statut === 'cloturee' ? 'selected' : '' }}>Clôturée</option>
                            </select>
                            <button type="submit" title="Enregistrer le statut"
                                    style="width:34px; height:34px; border:0; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-primary); cursor:pointer; transition:background 0.15s;"
                                    onmouseover="this.style.background='var(--color-surface-container-high)'"
                                    onmouseout="this.style.background='var(--color-surface-container)'">
                                <span class="material-symbols-outlined" style="font-size:18px;">save</span>
                            </button>
                        </form>
                    </td>

                    <td style="padding:0.875rem 1rem;">
                        @if($eval->type === 'devoir')
                        <div style="display:flex; align-items:center; gap:0.5rem; justify-content:flex-end;">
                            <a href="{{ route('admin.evaluations.edit', $eval) }}"
                               title="Modifier"
                               style="width:34px; height:34px; border-radius:0.5rem; display:flex; align-items:center; justify-content:center;
                                      background:var(--color-surface-container); color:var(--color-on-surface-variant); text-decoration:none; transition:all 0.15s;"
                               onmouseover="this.style.background='var(--color-surface-container-high)'"
                               onmouseout="this.style.background='var(--color-surface-container)'">
                                <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                            </a>
                            <form method="POST" action="{{ route('admin.evaluations.destroy', $eval) }}"
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce devoir ?')">
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
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    @if($evaluations->hasPages())
    <div style="padding:1rem 1.25rem; border-top:1px solid var(--color-surface-container); display:flex; align-items:center; justify-content:space-between;">
        <span style="font-size:0.8125rem; color:var(--color-on-surface-variant);">
            Affichage {{ $evaluations->firstItem() }}–{{ $evaluations->lastItem() }} sur {{ $evaluations->total() }}
        </span>
        <div style="display:flex; gap:0.375rem;">
            {{ $evaluations->links() }}
        </div>
    </div>
    @endif
    
    @endif
</div>
@endsection
