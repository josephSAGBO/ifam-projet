@extends('layouts.admin')

@section('title', 'Lieux de Stage')

@section('content')

{{-- En-tête de la page --}}
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem; display:flex; align-items:center; gap:0.625rem;">
            <span class="material-symbols-outlined" style="color:var(--color-primary); font-size:28px;">location_on</span>
            Répertoire des Lieux de Stage
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Structures d'accueil potentielles pour les stages des étudiants de l'IFAM
        </p>
    </div>
    @can('create', App\Models\InternshipSite::class)
    <a href="{{ route('admin.internship-sites.create') }}"
       style="height:44px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.25rem; border-radius:0.75rem;
              background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none;
              font-size:0.875rem; font-weight:700; box-shadow:0 3px 10px rgba(182,0,80,0.25); transition:all 0.15s;"
       onmouseover="this.style.opacity='0.9'"
       onmouseout="this.style.opacity='1'">
        <span class="material-symbols-outlined" style="font-size:20px;">add_location_alt</span>
        Nouveau Lieu de Stage
    </a>
    @endcan
</div>

{{-- Messages flash --}}
@if(session('success'))
    <div style="background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; padding:0.875rem 1.25rem; border-radius:0.75rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.75rem; font-size:0.875rem;">
        <span class="material-symbols-outlined" style="color:#059669; font-size:20px;">check_circle</span>
        <span>{{ session('success') }}</span>
    </div>
@endif

{{-- Barre de filtres et recherche --}}
<form method="GET" action="{{ route('admin.internship-sites.index') }}"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem 1.5rem; margin-bottom:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)) auto; gap:1rem; align-items:end;">
    <div>
        <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); margin-bottom:0.375rem; text-transform:uppercase; letter-spacing:0.06em;">Recherche</label>
        <div style="position:relative;">
            <span class="material-symbols-outlined" style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); font-size:18px; color:var(--color-on-surface-variant);">search</span>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, secteur, commune, contact..."
                   style="width:100%; height:42px; padding:0 0.875rem 0 2.5rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface); box-sizing:border-box;">
        </div>
    </div>

    <div>
        <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); margin-bottom:0.375rem; text-transform:uppercase; letter-spacing:0.06em;">Statut</label>
        <select name="statut" style="width:100%; height:42px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface); box-sizing:border-box;">
            <option value="">Tous les statuts</option>
            <option value="actif" {{ request('statut') === 'actif' ? 'selected' : '' }}>Actif</option>
            <option value="suspendu" {{ request('statut') === 'suspendu' ? 'selected' : '' }}>Suspendu</option>
            <option value="archive" {{ request('statut') === 'archive' ? 'selected' : '' }}>Archivé</option>
        </select>
    </div>

    @if($isGlobal && $sites->isNotEmpty())
    <div>
        <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); margin-bottom:0.375rem; text-transform:uppercase; letter-spacing:0.06em;">Site IFAM</label>
        <select name="site_id" style="width:100%; height:42px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface); box-sizing:border-box;">
            <option value="">Tous les sites</option>
            @foreach($sites as $s)
                <option value="{{ $s->id }}" {{ (string) request('site_id') === (string) $s->id ? 'selected' : '' }}>{{ $s->nom }}</option>
            @endforeach
        </select>
    </div>
    @endif

    <div style="display:flex; gap:0.5rem;">
        <button type="submit" style="height:42px; padding:0 1.25rem; border-radius:0.5rem; border:none; background:var(--color-primary-container); color:var(--color-on-primary); font-weight:700; font-size:0.875rem; cursor:pointer;">
            Filtrer
        </button>
        @if(request()->hasAny(['search', 'statut', 'site_id']))
            <a href="{{ route('admin.internship-sites.index') }}" style="height:42px; display:inline-flex; align-items:center; padding:0 1rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); color:var(--color-on-surface-variant); font-size:0.8125rem; font-weight:600; text-decoration:none;">
                Effacer
            </a>
        @endif
    </div>
</form>

{{-- Table des Lieux de stage --}}
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden; border:1px solid var(--color-surface-container);">
    @if($internshipSites->isEmpty())
        <div style="padding:4rem 1.5rem; text-align:center;">
            <span class="material-symbols-outlined" style="font-size:56px; color:var(--color-on-surface-variant); opacity:0.3; display:block; margin-bottom:1rem;">location_off</span>
            <h3 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem;">Aucun lieu de stage trouvé</h3>
            <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0 0 1.5rem;">
                Commencez par ajouter un premier lieu d'accueil pour les stages ou adaptez vos filtres.
            </p>
            @can('create', App\Models\InternshipSite::class)
            <a href="{{ route('admin.internship-sites.create') }}" style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem; border-radius:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none; font-weight:700; font-size:0.875rem;">
                <span class="material-symbols-outlined" style="font-size:18px;">add</span> Ajouter un lieu
            </a>
            @endcan
        </div>
    @else
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
                <thead>
                    <tr style="background:var(--color-surface-container-low); border-bottom:2px solid var(--color-outline-variant);">
                        <th style="text-align:left; padding:0.875rem 1.25rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Structure</th>
                        <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Type & Secteur</th>
                        <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Localisation</th>
                        <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Contact</th>
                        <th style="text-align:center; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Capacité</th>
                        @if($isGlobal)
                        <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Site IFAM</th>
                        @endif
                        <th style="text-align:center; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Statut</th>
                        <th style="text-align:right; padding:0.875rem 1.25rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($internshipSites as $site)
                        <tr style="border-bottom:1px solid var(--color-surface-container); transition:background 0.15s;"
                            onmouseover="this.style.background='var(--color-surface-container-lowest)'"
                            onmouseout="this.style.background='transparent'">
                            <td style="padding:1rem 1.25rem;">
                                <div style="font-weight:700; color:var(--color-on-surface);">
                                    <a href="{{ route('admin.internship-sites.show', $site) }}" style="color:var(--color-on-surface); text-decoration:none;">
                                        {{ $site->nom }}
                                    </a>
                                </div>
                                @if($site->email)
                                    <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">{{ $site->email }}</div>
                                @endif
                            </td>
                            <td style="padding:1rem;">
                                <div style="font-weight:600; color:var(--color-on-surface);">{{ $site->type }}</div>
                                <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">{{ $site->secteur_activite }}</div>
                            </td>
                            <td style="padding:1rem;">
                                <div style="color:var(--color-on-surface);">{{ $site->commune }}</div>
                                <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">{{ $site->departement }}, {{ $site->pays ?? 'Bénin' }}</div>
                            </td>
                            <td style="padding:1rem;">
                                <div style="font-weight:600; color:var(--color-on-surface);">{{ $site->contact_nom }}</div>
                                <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                                    {{ $site->telephone_principal }}
                                    @if($site->contact_fonction)
                                        • {{ $site->contact_fonction }}
                                    @endif
                                </div>
                            </td>
                            <td style="padding:1rem; text-align:center;">
                                @if(!is_null($site->capacite_max))
                                    <span style="font-weight:700; color:var(--color-primary); background:var(--color-surface-container); padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.8125rem;">
                                        {{ $site->capacite_max }}
                                    </span>
                                @else
                                    <span style="color:var(--color-on-surface-variant); font-size:0.875rem;">—</span>
                                @endif
                            </td>
                            @if($isGlobal)
                            <td style="padding:1rem;">
                                <span style="font-size:0.8125rem; background:var(--color-surface-container-high); padding:0.25rem 0.5rem; border-radius:0.375rem; color:var(--color-on-surface);">
                                    {{ $site->site?->nom ?? '—' }}
                                </span>
                            </td>
                            @endif
                            <td style="padding:1rem; text-align:center;">
                                @if($site->statut === 'actif')
                                    <span style="display:inline-flex; align-items:center; gap:0.25rem; padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.75rem; font-weight:700; background:#ecfdf5; color:#065f46;">
                                        <span style="width:6px; height:6px; border-radius:50%; background:#059669;"></span> Actif
                                    </span>
                                @elseif($site->statut === 'suspendu')
                                    <span style="display:inline-flex; align-items:center; gap:0.25rem; padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.75rem; font-weight:700; background:#fef3c7; color:#92400e;">
                                        <span style="width:6px; height:6px; border-radius:50%; background:#d97706;"></span> Suspendu
                                    </span>
                                @else
                                    <span style="display:inline-flex; align-items:center; gap:0.25rem; padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.75rem; font-weight:700; background:#f3f4f6; color:#4b5563;">
                                        <span style="width:6px; height:6px; border-radius:50%; background:#9ca3af;"></span> Archivé
                                    </span>
                                @endif
                            </td>
                            <td style="padding:1rem 1.25rem; text-align:right;">
                                <div style="display:inline-flex; align-items:center; gap:0.375rem;">
                                    <a href="{{ route('admin.internship-sites.show', $site) }}"
                                       title="Consulter"
                                       style="width:32px; height:32px; border-radius:0.375rem; display:inline-flex; align-items:center; justify-content:center; color:var(--color-on-surface-variant); text-decoration:none; background:var(--color-surface-container);"
                                       onmouseover="this.style.background='var(--color-surface-container-high)'"
                                       onmouseout="this.style.background='var(--color-surface-container)'">
                                        <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                                    </a>
                                    @can('update', $site)
                                    <a href="{{ route('admin.internship-sites.edit', $site) }}"
                                       title="Modifier"
                                       style="width:32px; height:32px; border-radius:0.375rem; display:inline-flex; align-items:center; justify-content:center; color:var(--color-on-surface-variant); text-decoration:none; background:var(--color-surface-container);"
                                       onmouseover="this.style.background='var(--color-surface-container-high)'"
                                       onmouseout="this.style.background='var(--color-surface-container)'">
                                        <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                                    </a>
                                    @endcan
                                    @can('delete', $site)
                                    <form action="{{ route('admin.internship-sites.destroy', $site) }}" method="POST"
                                          onsubmit="return confirm('Confirmez-vous la suppression de ce lieu de stage ?');"
                                          style="display:inline-block; margin:0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                title="Supprimer"
                                                style="width:32px; height:32px; border-radius:0.375rem; display:inline-flex; align-items:center; justify-content:center; color:#dc2626; background:#fee2e2; border:none; cursor:pointer;"
                                                onmouseover="this.style.background='#fecaca'"
                                                onmouseout="this.style.background='#fee2e2'">
                                            <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($internshipSites->hasPages())
            <div style="padding:1rem 1.25rem; border-top:1px solid var(--color-surface-container);">
                {{ $internshipSites->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
