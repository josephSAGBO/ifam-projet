@extends('layouts.admin')

@section('title', 'Cours')

@section('content')

<div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1.75rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Planification des Cours
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Gérez les cours (rattachement formation, session, module, enseignant, salle, horaires et documents).
        </p>
    </div>
    <a href="{{ route('admin.courses.create') }}" class="interactive-hover"
       style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem;
              background:var(--color-primary-container); color:var(--color-on-primary); border-radius:0.75rem;
              text-decoration:none; font-size:0.875rem; font-weight:700;
              box-shadow:0 3px 10px rgba(228,0,102,0.3); transition:all 0.2s;"
       onmouseover="this.style.transform='translateY(-1px)'"
       onmouseout="this.style.transform=''">
        <span class="material-symbols-outlined" style="font-size:20px;">add_circle</span>
        Créer un cours
    </a>
</div>

@if($isResponsableEtudes)
<form method="GET" action="{{ route('admin.courses.index') }}" style="display:flex; align-items:end; gap:0.75rem; flex-wrap:wrap; margin-bottom:1rem;">
    <label style="font-size:0.8rem; color:var(--color-on-surface-variant);">
        Formation
        <select name="formation_id" style="display:block; min-width:220px; margin-top:0.25rem; padding:0.55rem 0.75rem; border:1px solid var(--color-outline-variant); border-radius:0.5rem; background:var(--color-surface-container-lowest); color:var(--color-on-surface);">
            <option value="">Toutes les formations</option>
            @foreach($availableFormations as $formation)
                <option value="{{ $formation->id }}" @selected($selectedFormationId === $formation->id)>{{ $formation->name }}</option>
            @endforeach
        </select>
    </label>
    <label style="font-size:0.8rem; color:var(--color-on-surface-variant);">
        Module
        <select name="module_id" style="display:block; min-width:220px; margin-top:0.25rem; padding:0.55rem 0.75rem; border:1px solid var(--color-outline-variant); border-radius:0.5rem; background:var(--color-surface-container-lowest); color:var(--color-on-surface);">
            <option value="">Tous les modules</option>
            @foreach($availableModules as $module)
                <option value="{{ $module->id }}" @selected($selectedModuleId === $module->id)>{{ $module->name }}</option>
            @endforeach
        </select>
    </label>
    <button type="submit" style="height:40px; padding:0 1rem; border:0; border-radius:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); font-weight:700; cursor:pointer;">
        Filtrer
    </button>
    @if($selectedFormationId || $selectedModuleId)
        <a href="{{ route('admin.courses.index') }}" style="height:40px; display:inline-flex; align-items:center; color:var(--color-primary); text-decoration:none; font-size:0.875rem; font-weight:600;">Réinitialiser</a>
    @endif
</form>
@else
<form method="GET" action="{{ route('admin.courses.index') }}" style="display:flex; align-items:end; gap:0.75rem; flex-wrap:wrap; margin-bottom:1rem;">
    <label style="font-size:0.8rem; color:var(--color-on-surface-variant);">
        Filtrer les cours par site
        <select name="site_id" onchange="this.form.submit()" style="display:block; min-width:220px; margin-top:0.25rem; padding:0.55rem 0.75rem; border:1px solid var(--color-outline-variant); border-radius:0.5rem; background:var(--color-surface-container-lowest); color:var(--color-on-surface);">
            <option value="">Tous les sites accessibles</option>
            @foreach($availableSites as $site)
                <option value="{{ $site->id }}" @selected($selectedSiteId === $site->id)>{{ $site->nom }}</option>
            @endforeach
        </select>
    </label>
</form>
@endif

<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden;">
    @if($courses->isEmpty())
    <div style="padding:4rem 2rem; text-align:center; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined" style="font-size:48px; opacity:0.25; display:block; margin-bottom:1rem;">class</span>
        <p style="font-size:0.9375rem; margin:0;">Aucun cours trouvé.</p>
    </div>
    @else
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Cours / Module</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Formation & Session</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Planning & Salle</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Enseignant</th>
                    <th style="text-align:center; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Statut</th>
                    <th style="text-align:center; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Docs</th>
                    <th style="padding:0.875rem 1rem;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($courses as $course)
                <tr style="border-bottom:1px solid var(--color-surface-container); transition:background 0.15s;"
                    onmouseover="this.style.background='var(--color-surface-container-low)'"
                    onmouseout="this.style.background=''">
                    
                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface);">
                        <div style="display:flex; align-items:center; gap:0.5rem;">
                            <div style="width:12px; height:12px; border-radius:3px; background:{{ $course->color ?? '#e40066' }};"></div>
                            <div>
                                <a href="{{ route('admin.courses.show', $course) }}" style="font-weight:600; color:var(--color-on-surface); text-decoration:none;">
                                    {{ $course->display_name }}
                                </a>
                                <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                                    Code: {{ $course->module->code ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </td>
                    
                    <td style="padding:0.875rem 1rem;">
                        <div style="font-weight:500; color:var(--color-on-surface);">
                            {{ $course->formation->name ?? 'Formation inconnue' }}
                        </div>
                        <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                            Session: {{ $course->academicSession->name ?? 'N/A' }}
                        </div>
                    </td>

                    <td style="padding:0.875rem 1rem;">
                        @if($course->date)
                            <div style="font-weight:600; color:var(--color-on-surface);">
                                {{ $course->date->format('d/m/Y') }}
                                @if($course->heure_debut && $course->heure_fin)
                                    <span style="font-size:0.75rem; font-weight:normal; color:var(--color-on-surface-variant);">({{ $course->heure_debut }} - {{ $course->heure_fin }})</span>
                                @endif
                            </div>
                        @else
                            <div style="color:var(--color-on-surface-variant); font-style:italic;">Non planifié</div>
                        @endif
                        <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                            Salle: {{ $course->salle->name ?? '—' }}
                        </div>
                    </td>

                    <td style="padding:0.875rem 1rem;">
                        @if(($course->enseignant && $course->enseignant->user) || ($course->teacherProfile && $course->teacherProfile->user))
                            <div style="font-weight:500; color:var(--color-on-surface);">
                                {{ $course->enseignant->user->full_name ?? $course->teacherProfile->user->full_name }}
                            </div>
                        @else
                            <span style="color:var(--color-on-surface-variant); font-style:italic;">Non assigné</span>
                        @endif
                    </td>

                    <td style="padding:0.875rem 1rem; text-align:center;">
                        <form method="POST" action="{{ route('admin.courses.status', $course) }}" style="display:flex; align-items:center; justify-content:center; gap:0.375rem;">
                            @csrf
                            @method('PATCH')
                            <select name="statut" aria-label="Statut du cours"
                                    style="height:36px; max-width:130px; padding:0 0.5rem; border:1px solid var(--color-outline-variant); border-radius:0.5rem; background:var(--color-surface); color:var(--color-on-surface);">
                                <option value="en_cours" {{ $course->statut === 'en_cours' ? 'selected' : '' }}>En cours</option>
                                <option value="termine" {{ $course->statut === 'termine' ? 'selected' : '' }}>Terminé</option>
                            </select>
                            <button type="submit" title="Enregistrer le statut"
                                    style="width:34px; height:34px; border:0; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-primary); cursor:pointer; transition:background 0.15s;"
                                    onmouseover="this.style.background='var(--color-surface-container-high)'"
                                    onmouseout="this.style.background='var(--color-surface-container)'">
                                <span class="material-symbols-outlined" style="font-size:18px;">save</span>
                            </button>
                        </form>
                    </td>

                    <td style="padding:0.875rem 1rem; text-align:center;">
                        <span style="padding:0.25rem 0.5rem; border-radius:9999px; font-size:0.75rem; font-weight:700; background:var(--color-surface-container-high); color:var(--color-on-surface);">
                            {{ $course->documents->count() }}
                        </span>
                    </td>

                    <td style="padding:0.875rem 1rem;">
                        <div style="display:flex; align-items:center; gap:0.375rem; justify-content:flex-end;">
                            <a href="{{ route('admin.courses.show', $course) }}"
                               title="Voir détails"
                               style="width:34px; height:34px; border-radius:0.5rem; display:flex; align-items:center; justify-content:center;
                                      background:var(--color-surface-container); color:var(--color-on-surface); text-decoration:none; transition:all 0.15s;">
                                <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                            </a>
                            <a href="{{ route('admin.courses.edit', $course) }}"
                               title="Modifier"
                               style="width:34px; height:34px; border-radius:0.5rem; display:flex; align-items:center; justify-content:center;
                                      background:var(--color-surface-container); color:var(--color-on-surface-variant); text-decoration:none; transition:all 0.15s;">
                                <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                            </a>
                            <form method="POST" action="{{ route('admin.courses.destroy', $course) }}"
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce cours ?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Supprimer"
                                        style="width:34px; height:34px; border-radius:0.5rem; border:none; cursor:pointer;
                                               background:var(--color-surface-container); color:#dc2626; transition:all 0.15s;">
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
    
    @if($courses->hasPages())
    <div style="padding:1rem 1.25rem; border-top:1px solid var(--color-surface-container); display:flex; align-items:center; justify-content:space-between;">
        <span style="font-size:0.8125rem; color:var(--color-on-surface-variant);">
            Affichage {{ $courses->firstItem() }}–{{ $courses->lastItem() }} sur {{ $courses->total() }}
        </span>
        <div style="display:flex; gap:0.375rem;">
            @if($courses->onFirstPage())
            <span style="padding:0.375rem 0.75rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-on-surface-variant); font-size:0.8125rem; opacity:0.5;">← Préc.</span>
            @else
            <a href="{{ $courses->previousPageUrl() }}"
               style="padding:0.375rem 0.75rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-on-surface); font-size:0.8125rem; text-decoration:none;">← Préc.</a>
            @endif
            @if($courses->hasMorePages())
            <a href="{{ $courses->nextPageUrl() }}"
               style="padding:0.375rem 0.75rem; border-radius:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.8125rem; font-weight:600; text-decoration:none;">Suiv. →</a>
            @else
            <span style="padding:0.375rem 0.75rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-on-surface-variant); font-size:0.8125rem; opacity:0.5;">Suiv. →</span>
            @endif
        </div>
    </div>
    @endif
    
    @endif
</div>
@endsection
