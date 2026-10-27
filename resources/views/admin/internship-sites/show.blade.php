@extends('layouts.admin')

@section('title', 'Détails Lieu de Stage - ' . $internshipSite->nom)

@section('content')

{{-- En-tête --}}
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem; flex-wrap:wrap; gap:1rem;">
    <div style="display:flex; align-items:center; gap:1rem;">
        <a href="{{ route('admin.internship-sites.index') }}"
           style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
                  display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
                  transition:all 0.15s;"
           onmouseover="this.style.background='var(--color-surface-container-high)'"
           onmouseout="this.style.background='var(--color-surface-container)'">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
                {{ $internshipSite->nom }}
            </h1>
            <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
                {{ $internshipSite->type }} • {{ $internshipSite->secteur_activite }}
            </p>
        </div>
    </div>

    <div style="display:flex; gap:0.75rem;">
        @can('update', $internshipSite)
        <a href="{{ route('admin.internship-sites.edit', $internshipSite) }}"
           style="height:40px; padding:0 1.25rem; border-radius:0.625rem; background:var(--color-surface-container-high);
                  color:var(--color-on-surface); font-size:0.875rem; font-weight:600; text-decoration:none;
                  display:inline-flex; align-items:center; gap:0.5rem;">
            <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
            Modifier
        </a>
        @endcan

        @can('delete', $internshipSite)
        <form action="{{ route('admin.internship-sites.destroy', $internshipSite) }}" method="POST"
              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement ce lieu de stage ?');">
            @csrf
            @method('DELETE')
            <button type="submit"
                    style="height:40px; padding:0 1.25rem; border-radius:0.625rem; background:#fee2e2;
                           color:#dc2626; border:none; cursor:pointer; font-size:0.875rem; font-weight:600;
                           display:inline-flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                Supprimer
            </button>
        </form>
        @endcan
    </div>
</div>

{{-- Message flash --}}
@if(session('success'))
    <div style="background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; padding:0.875rem 1.25rem; border-radius:0.75rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.75rem; font-size:0.875rem;">
        <span class="material-symbols-outlined" style="color:#059669; font-size:20px;">check_circle</span>
        <span>{{ session('success') }}</span>
    </div>
@endif

<div style="display:grid; grid-template-columns:2fr 1fr; gap:1.5rem;">

    {{-- Colonne Principale --}}
    <div style="display:flex; flex-direction:column; gap:1.5rem;">

        {{-- Carte 1 : Présentation & Activités --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); border:1px solid var(--color-surface-container);">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1.25rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="color:var(--color-primary);">domain</span>
                Présentation de la Structure
            </h2>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                <div>
                    <span style="font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:0.25rem;">
                        Type d'organisation
                    </span>
                    <span style="font-size:0.9375rem; font-weight:600; color:var(--color-on-surface);">
                        {{ $internshipSite->type }}
                    </span>
                </div>
                <div>
                    <span style="font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:0.25rem;">
                        Secteur d'activité
                    </span>
                    <span style="font-size:0.9375rem; font-weight:600; color:var(--color-on-surface);">
                        {{ $internshipSite->secteur_activite }}
                    </span>
                </div>
            </div>

            <div>
                <span style="font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:0.375rem;">
                    Description des activités
                </span>
                <div style="font-size:0.875rem; color:var(--color-on-surface); line-height:1.6; background:var(--color-surface-container-low); padding:1rem; border-radius:0.625rem;">
                    {{ $internshipSite->description ?: 'Aucune description détaillée enregistrée pour cette structure.' }}
                </div>
            </div>
        </div>

        {{-- Carte 2 : Localisation géographique --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); border:1px solid var(--color-surface-container);">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1.25rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="color:var(--color-primary);">pin_drop</span>
                Localisation & Accès
            </h2>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1.25rem; margin-bottom:1.25rem;">
                <div>
                    <span style="font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:0.25rem;">
                        Pays
                    </span>
                    <span style="font-size:0.9375rem; color:var(--color-on-surface);">{{ $internshipSite->pays ?? 'Bénin' }}</span>
                </div>
                <div>
                    <span style="font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:0.25rem;">
                        Département
                    </span>
                    <span style="font-size:0.9375rem; color:var(--color-on-surface);">{{ $internshipSite->departement }}</span>
                </div>
                <div>
                    <span style="font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:0.25rem;">
                        Commune
                    </span>
                    <span style="font-size:0.9375rem; font-weight:600; color:var(--color-on-surface);">{{ $internshipSite->commune }}</span>
                </div>
                <div>
                    <span style="font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:0.25rem;">
                        Quartier
                    </span>
                    <span style="font-size:0.9375rem; color:var(--color-on-surface);">{{ $internshipSite->quartier ?: '—' }}</span>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
                <div>
                    <span style="font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:0.25rem;">
                        Adresse précise
                    </span>
                    <span style="font-size:0.9375rem; color:var(--color-on-surface);">{{ $internshipSite->adresse }}</span>
                </div>
                <div>
                    <span style="font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:0.25rem;">
                        Repère
                    </span>
                    <span style="font-size:0.9375rem; color:var(--color-on-surface);">{{ $internshipSite->repere ?: '—' }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- Colonne Latérale --}}
    <div style="display:flex; flex-direction:column; gap:1.5rem;">

        {{-- Statut & Capacité --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); border:1px solid var(--color-surface-container);">
            <h3 style="font-size:0.875rem; font-weight:700; color:var(--color-on-surface-variant); margin:0 0 1rem; text-transform:uppercase; letter-spacing:0.08em;">
                Statut & Capacité
            </h3>

            <div style="margin-bottom:1.25rem;">
                <span style="font-size:0.75rem; color:var(--color-on-surface-variant); display:block; margin-bottom:0.375rem;">Disponibilité</span>
                @if($internshipSite->statut === 'actif')
                    <span style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.375rem 0.75rem; border-radius:9999px; font-size:0.8125rem; font-weight:700; background:#ecfdf5; color:#065f46;">
                        <span style="width:8px; height:8px; border-radius:50%; background:#059669;"></span> Actif (disponible)
                    </span>
                @elseif($internshipSite->statut === 'suspendu')
                    <span style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.375rem 0.75rem; border-radius:9999px; font-size:0.8125rem; font-weight:700; background:#fef3c7; color:#92400e;">
                        <span style="width:8px; height:8px; border-radius:50%; background:#d97706;"></span> Suspendu
                    </span>
                @else
                    <span style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.375rem 0.75rem; border-radius:9999px; font-size:0.8125rem; font-weight:700; background:#f3f4f6; color:#4b5563;">
                        <span style="width:8px; height:8px; border-radius:50%; background:#9ca3af;"></span> Archivé
                    </span>
                @endif
            </div>

            <div style="margin-bottom:1.25rem;">
                <span style="font-size:0.75rem; color:var(--color-on-surface-variant); display:block; margin-bottom:0.25rem;">Capacité maximale d'accueil</span>
                <span style="font-size:1.25rem; font-weight:700; color:var(--color-primary);">
                    @if(!is_null($internshipSite->capacite_max))
                        {{ $internshipSite->capacite_max }} stagiaire{{ $internshipSite->capacite_max > 1 ? 's' : '' }}
                    @else
                        —
                    @endif
                </span>
            </div>

            <div style="margin-bottom:1.25rem; padding-top:1rem; border-top:1px solid var(--color-surface-container);">
                <span style="font-size:0.75rem; color:var(--color-on-surface-variant); display:block; margin-bottom:0.25rem;">Site IFAM de rattachement</span>
                <span style="font-size:0.9375rem; font-weight:600; color:var(--color-on-surface);">
                    {{ $internshipSite->site?->nom ?? '—' }}
                </span>
            </div>

            <div style="padding-top:1rem; border-top:1px solid var(--color-surface-container); font-size:0.75rem; color:var(--color-on-surface-variant);">
                <div>Créé le {{ $internshipSite->created_at?->format('d/m/Y à H:i') }}</div>
                <div>Mis à jour le {{ $internshipSite->updated_at?->format('d/m/Y à H:i') }}</div>
            </div>
        </div>

        {{-- Contact & Interlocuteur --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); border:1px solid var(--color-surface-container);">
            <h3 style="font-size:0.875rem; font-weight:700; color:var(--color-on-surface-variant); margin:0 0 1rem; text-transform:uppercase; letter-spacing:0.08em;">
                Contact Référent
            </h3>

            <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem;">
                <div style="width:44px; height:44px; border-radius:50%; background:var(--color-primary-container); color:var(--color-on-primary); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:1rem;">
                    {{ strtoupper(substr($internshipSite->contact_nom, 0, 1)) }}
                </div>
                <div>
                    <div style="font-weight:700; color:var(--color-on-surface); font-size:0.9375rem;">
                        {{ $internshipSite->contact_nom }}
                    </div>
                    @if($internshipSite->contact_fonction)
                        <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                            {{ $internshipSite->contact_fonction }}
                        </div>
                    @endif
                </div>
            </div>

            <div style="display:flex; flex-direction:column; gap:0.75rem; font-size:0.875rem;">
                <div style="display:flex; align-items:center; gap:0.625rem; color:var(--color-on-surface);">
                    <span class="material-symbols-outlined" style="font-size:18px; color:var(--color-primary);">call</span>
                    <a href="tel:{{ $internshipSite->telephone_principal }}" style="color:var(--color-primary); text-decoration:none; font-weight:600;">
                        {{ $internshipSite->telephone_principal }}
                    </a>
                </div>

                @if($internshipSite->telephone_secondaire)
                <div style="display:flex; align-items:center; gap:0.625rem; color:var(--color-on-surface);">
                    <span class="material-symbols-outlined" style="font-size:18px; color:var(--color-on-surface-variant);">phone_in_talk</span>
                    <span>{{ $internshipSite->telephone_secondaire }}</span>
                </div>
                @endif

                @if($internshipSite->email)
                <div style="display:flex; align-items:center; gap:0.625rem; color:var(--color-on-surface);">
                    <span class="material-symbols-outlined" style="font-size:18px; color:var(--color-primary);">mail</span>
                    <a href="mailto:{{ $internshipSite->email }}" style="color:var(--color-primary); text-decoration:none;">
                        {{ $internshipSite->email }}
                    </a>
                </div>
                @endif

                @if($internshipSite->site_web)
                <div style="display:flex; align-items:center; gap:0.625rem; color:var(--color-on-surface);">
                    <span class="material-symbols-outlined" style="font-size:18px; color:var(--color-primary);">public</span>
                    <a href="{{ $internshipSite->site_web }}" target="_blank" rel="noopener noreferrer" style="color:var(--color-primary); text-decoration:none; word-break:break-all;">
                        {{ $internshipSite->site_web }}
                    </a>
                </div>
                @endif
            </div>
        </div>

    </div>

</div>

@endsection
