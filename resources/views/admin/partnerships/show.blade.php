@extends('layouts.admin')

@section('title', 'Détails Partenariat - ' . $partnership->nom)

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div style="display:flex; align-items:center; gap:1rem;">
        <a href="{{ route('admin.partnerships.index') }}"
           style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
                  display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
                {{ $partnership->nom }}
            </h1>
            <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
                Fiche détaillée du partenariat
            </p>
        </div>
    </div>

    <div style="display:flex; gap:0.75rem;">
        <a href="{{ route('admin.partnerships.edit', $partnership) }}"
           style="height:40px; padding:0 1.25rem; border-radius:0.625rem; background:var(--color-surface-container-high);
                  color:var(--color-on-surface); font-size:0.875rem; font-weight:600; text-decoration:none;
                  display:inline-flex; align-items:center; gap:0.5rem;">
            <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
            Modifier
        </a>
        <form action="{{ route('admin.partnerships.destroy', $partnership) }}" method="POST"
              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce partenariat ?');">
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
    </div>
</div>

<div style="display:grid; grid-template-columns: 2fr 1fr; gap:1.5rem;">

    {{-- Main Column --}}
    <div style="display:flex; flex-direction:column; gap:1.5rem;">
        
        {{-- Card Offers --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1.25rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="color:var(--color-primary);">handshake</span>
                Offres mutuelles & Contreparties
            </h2>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem;">
                <div style="background:var(--color-surface-container-low); border-radius:0.75rem; padding:1.25rem;">
                    <div style="font-size:0.8125rem; font-weight:700; text-transform:uppercase; color:var(--color-primary); margin-bottom:0.5rem;">
                        Ce qu'ils nous offrent
                    </div>
                    <div style="font-size:0.9375rem; color:var(--color-on-surface); line-height:1.6; whitespace:pre-line;">
                        {{ $partnership->ce_qu_ils_offrent ?? 'Non spécifié.' }}
                    </div>
                </div>

                <div style="background:var(--color-surface-container-low); border-radius:0.75rem; padding:1.25rem;">
                    <div style="font-size:0.8125rem; font-weight:700; text-transform:uppercase; color:var(--color-on-surface-variant); margin-bottom:0.5rem;">
                        Ce qu'on leur offre
                    </div>
                    <div style="font-size:0.9375rem; color:var(--color-on-surface); line-height:1.6; whitespace:pre-line;">
                        {{ $partnership->ce_qu_on_offre ?? 'Non spécifié.' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Suivi & Notes --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="color:var(--color-primary);">history</span>
                Suivi & Derniers échanges
            </h2>
            <div style="background:var(--color-surface-container-low); border-radius:0.75rem; padding:1.25rem; font-size:0.9375rem; color:var(--color-on-surface); line-height:1.6;">
                {{ $partnership->suivi ?? 'Aucune note de suivi enregistrée pour le moment.' }}
            </div>
        </div>

    </div>

    {{-- Sidebar Column --}}
    <div style="display:flex; flex-direction:column; gap:1.5rem;">
        
        {{-- General Info Card --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
            <h3 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; border-bottom:1px solid var(--color-outline-variant); padding-bottom:0.75rem;">
                Informations clés
            </h3>

            <div style="display:flex; flex-direction:column; gap:1rem;">
                <div>
                    <span style="font-size:0.75rem; text-transform:uppercase; font-weight:600; color:var(--color-on-surface-variant); display:block;">Statut</span>
                    <span style="margin-top:0.25rem; display:inline-block; padding:0.25rem 0.75rem; border-radius:1rem; font-size:0.8125rem; font-weight:700;
                                 @if($partnership->statut === 'Actif') background:#dcfce7; color:#15803d;
                                 @elseif($partnership->statut === 'En discussion') background:#fef9c3; color:#a16207;
                                 @elseif($partnership->statut === 'Suspendu') background:#fee2e2; color:#b91c1c;
                                 @else background:#f3f4f6; color:#4b5563; @endif">
                        {{ $partnership->statut }}
                    </span>
                </div>

                <div>
                    <span style="font-size:0.75rem; text-transform:uppercase; font-weight:600; color:var(--color-on-surface-variant); display:block;">Type</span>
                    <span style="font-size:0.9375rem; font-weight:600; color:var(--color-on-surface);">{{ $partnership->type_partenariat }}</span>
                </div>

                <div>
                    <span style="font-size:0.75rem; text-transform:uppercase; font-weight:600; color:var(--color-on-surface-variant); display:block;">Durée</span>
                    <span style="font-size:0.9375rem; color:var(--color-on-surface);">{{ $partnership->duree ?? 'Non spécifiée' }}</span>
                </div>

                <div>
                    <span style="font-size:0.75rem; text-transform:uppercase; font-weight:600; color:var(--color-on-surface-variant); display:block;">Secteur</span>
                    <span style="font-size:0.9375rem; color:var(--color-on-surface);">{{ $partnership->secteur_intervention ?? 'Non renseigné' }}</span>
                </div>

                <div>
                    <span style="font-size:0.75rem; text-transform:uppercase; font-weight:600; color:var(--color-on-surface-variant); display:block;">Dernier contact / appel</span>
                    <span style="font-size:0.9375rem; font-weight:600; color:var(--color-on-surface);">
                        {{ $partnership->date_dernier_contact ? $partnership->date_dernier_contact->format('d/m/Y') : 'Aucun contact enregistré' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Contact Persons Card --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
            <h3 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; border-bottom:1px solid var(--color-outline-variant); padding-bottom:0.75rem;">
                Contacts & Référents
            </h3>

            <div style="display:flex; flex-direction:column; gap:1.25rem;">
                <div>
                    <span style="font-size:0.75rem; text-transform:uppercase; font-weight:600; color:var(--color-on-surface-variant); display:block;">Contact Partenaire</span>
                    <div style="font-size:0.9375rem; font-weight:700; color:var(--color-on-surface); margin-top:0.25rem;">
                        {{ $partnership->full_contact_name }}
                    </div>
                    @if($partnership->telephone)
                        <div style="font-size:0.875rem; color:var(--color-on-surface-variant); display:flex; align-items:center; gap:0.25rem; margin-top:0.25rem;">
                            <span class="material-symbols-outlined" style="font-size:16px;">call</span>
                            {{ $partnership->telephone }}
                        </div>
                    @endif
                    @if($partnership->email)
                        <div style="font-size:0.875rem; color:var(--color-on-surface-variant); display:flex; align-items:center; gap:0.25rem; margin-top:0.25rem;">
                            <span class="material-symbols-outlined" style="font-size:16px;">mail</span>
                            {{ $partnership->email }}
                        </div>
                    @endif
                </div>

                <div style="border-top:1px dashed var(--color-outline-variant); padding-top:1rem;">
                    <span style="font-size:0.75rem; text-transform:uppercase; font-weight:600; color:var(--color-on-surface-variant); display:block;">Référent IFAM</span>
                    <div style="font-size:0.9375rem; font-weight:600; color:var(--color-on-surface); margin-top:0.25rem;">
                        {{ $partnership->personne_ressource_interne ?? 'Non assigné' }}
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
