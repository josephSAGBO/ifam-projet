@extends('layouts.admin')

@section('title', 'Nouveau Lieu de Stage')

@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
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
            Nouveau lieu de stage
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Enregistrez une structure d'accueil pour les stages des étudiants.
        </p>
    </div>
</div>

<form action="{{ route('admin.internship-sites.store') }}" method="POST"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:2rem; max-width:960px; border:1px solid var(--color-surface-container);">
    @csrf

    {{-- SECTION 1 : Identification de la Structure --}}
    <div style="margin-bottom:2rem;">
        <h2 style="font-size:1rem; font-weight:700; color:var(--color-primary); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem; text-transform:uppercase; letter-spacing:0.05em;">
            <span class="material-symbols-outlined" style="font-size:20px;">domain</span>
            1. Identification de la Structure
        </h2>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:1.25rem; margin-bottom:1.25rem;">
            <div style="grid-column: 1 / -1;">
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Nom de la structure / Entreprise <span style="color:#dc2626;">*</span>
                </label>
                <input type="text" name="nom" value="{{ old('nom') }}" required
                       placeholder="Ex: Bénin Télécoms Services, Cabinet Fiduciaire ABC, CHU HKM..."
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('nom') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Type de structure <span style="color:#dc2626;">*</span>
                </label>
                <select name="type" required
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                               border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                               font-size:0.9375rem; color:var(--color-on-surface);">
                    <option value="">Sélectionnez un type...</option>
                    @php
                        $typesList = ['Entreprise privée', 'Cabinet d\'expertise', 'Hôpital / Clinique', 'ONG / Association', 'Administration publique', 'Banque / Microfinance / Assurance', 'Établissement scolaire / Universitaire', 'Autre'];
                    @endphp
                    @foreach($typesList as $t)
                        <option value="{{ $t }}" {{ old('type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
                @error('type') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Secteur d'activité <span style="color:#dc2626;">*</span>
                </label>
                <input type="text" name="secteur_activite" value="{{ old('secteur_activite') }}" required
                       placeholder="Ex: Informatique, Audit, Santé, BTP, Marketing..."
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('secteur_activite') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>

        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Description / Activités principales
            </label>
            <textarea name="description" rows="3"
                      placeholder="Présentation sommaire de l'activité de la structure, départements d'accueil..."
                      style="width:100%; box-sizing:border-box; padding:0.75rem 1rem; border-radius:0.625rem;
                             border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                             font-size:0.9375rem; color:var(--color-on-surface); resize:vertical;">{{ old('description') }}</textarea>
            @error('description') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
        </div>
    </div>

    <hr style="border:none; border-top:1px solid var(--color-surface-container); margin:2rem 0;">

    {{-- SECTION 2 : Localisation géographique --}}
    <div style="margin-bottom:2rem;">
        <h2 style="font-size:1rem; font-weight:700; color:var(--color-primary); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem; text-transform:uppercase; letter-spacing:0.05em;">
            <span class="material-symbols-outlined" style="font-size:20px;">pin_drop</span>
            2. Localisation géographique
        </h2>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1.25rem; margin-bottom:1.25rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Pays <span style="color:#dc2626;">*</span>
                </label>
                <input type="text" name="pays" value="{{ old('pays', 'Bénin') }}" required
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('pays') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Département <span style="color:#dc2626;">*</span>
                </label>
                <input type="text" name="departement" value="{{ old('departement', 'Littoral') }}" required
                       placeholder="Ex: Littoral, Atlantique, Ouémé..."
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('departement') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Commune <span style="color:#dc2626;">*</span>
                </label>
                <input type="text" name="commune" value="{{ old('commune') }}" required
                       placeholder="Ex: Cotonou, Abomey-Calavi, Porto-Novo..."
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('commune') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Quartier ou Arrondissement
                </label>
                <input type="text" name="quartier" value="{{ old('quartier') }}"
                       placeholder="Ex: Cadjèhoun, Haie Vive, Akpakpa..."
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('quartier') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:1.25rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Adresse précise <span style="color:#dc2626;">*</span>
                </label>
                <input type="text" name="adresse" value="{{ old('adresse') }}" required
                       placeholder="Ex: Boulevard de la Marina, Immeuble Horizon 3e étage..."
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('adresse') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Repère géographique
                </label>
                <input type="text" name="repere" value="{{ old('repere') }}"
                       placeholder="Ex: En face de la pharmacie du Camp, à 200m du carrefour..."
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('repere') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    <hr style="border:none; border-top:1px solid var(--color-surface-container); margin:2rem 0;">

    {{-- SECTION 3 : Contact & Référents --}}
    <div style="margin-bottom:2rem;">
        <h2 style="font-size:1rem; font-weight:700; color:var(--color-primary); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem; text-transform:uppercase; letter-spacing:0.05em;">
            <span class="material-symbols-outlined" style="font-size:20px;">contact_phone</span>
            3. Contact & Référents
        </h2>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:1.25rem; margin-bottom:1.25rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Nom & Prénom du contact principal <span style="color:#dc2626;">*</span>
                </label>
                <input type="text" name="contact_nom" value="{{ old('contact_nom') }}" required
                       placeholder="Ex: M. Jean HOUENOU, Mme Aïcha BIO..."
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('contact_nom') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Fonction du contact
                </label>
                <input type="text" name="contact_fonction" value="{{ old('contact_fonction') }}"
                       placeholder="Ex: Responsable RH, Directeur Technique, Tuteur..."
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('contact_fonction') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1.25rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Téléphone principal <span style="color:#dc2626;">*</span>
                </label>
                <input type="text" name="telephone_principal" value="{{ old('telephone_principal') }}" required
                       placeholder="Ex: +229 97 00 00 00"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('telephone_principal') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Téléphone secondaire
                </label>
                <input type="text" name="telephone_secondaire" value="{{ old('telephone_secondaire') }}"
                       placeholder="Ex: +229 21 30 00 00"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('telephone_secondaire') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Adresse e-mail
                </label>
                <input type="email" name="email" value="{{ old('email') }}"
                       placeholder="contact@structure.bj"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('email') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Site web
                </label>
                <input type="url" name="site_web" value="{{ old('site_web') }}"
                       placeholder="https://www.structure.bj"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('site_web') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    <hr style="border:none; border-top:1px solid var(--color-surface-container); margin:2rem 0;">

    {{-- SECTION 4 : Capacité, Statut & Rattachement --}}
    <div style="margin-bottom:2rem;">
        <h2 style="font-size:1rem; font-weight:700; color:var(--color-primary); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem; text-transform:uppercase; letter-spacing:0.05em;">
            <span class="material-symbols-outlined" style="font-size:20px;">tune</span>
            4. Capacité, Statut & Rattachement
        </h2>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1.25rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Capacité max de stagiaires
                </label>
                <input type="number" name="capacite_max" value="{{ old('capacite_max') }}" min="1" max="9999"
                       placeholder="Laisser vide si inconnue"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                <span style="font-size:0.75rem; color:var(--color-on-surface-variant); margin-top:0.25rem; display:block;">Affichera un tiret si non renseignée.</span>
                @error('capacite_max') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Statut de la fiche <span style="color:#dc2626;">*</span>
                </label>
                <select name="statut" required
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                               border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                               font-size:0.9375rem; color:var(--color-on-surface);">
                    <option value="actif" {{ old('statut', 'actif') === 'actif' ? 'selected' : '' }}>Actif (disponible pour stages)</option>
                    <option value="suspendu" {{ old('statut') === 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                    <option value="archive" {{ old('statut') === 'archive' ? 'selected' : '' }}>Archivé</option>
                </select>
                @error('statut') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            @if($isGlobal)
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Site IFAM de rattachement <span style="color:#dc2626;">*</span>
                </label>
                <select name="site_id" required
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                               border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                               font-size:0.9375rem; color:var(--color-on-surface);">
                    <option value="">Sélectionnez un site...</option>
                    @foreach($sites as $s)
                        <option value="{{ $s->id }}" {{ (string) old('site_id', $defaultSiteId) === (string) $s->id ? 'selected' : '' }}>
                            {{ $s->nom }}
                        </option>
                    @endforeach
                </select>
                @error('site_id') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
            @endif
        </div>
    </div>

    {{-- Actions --}}
    <div style="display:flex; justify-content:flex-end; gap:1rem; margin-top:2.5rem; padding-top:1.5rem; border-top:1px solid var(--color-surface-container);">
        <a href="{{ route('admin.internship-sites.index') }}"
           style="height:44px; padding:0 1.25rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant);
                  background:transparent; color:var(--color-on-surface-variant); font-size:0.875rem; font-weight:600;
                  display:inline-flex; align-items:center; text-decoration:none;">
            Annuler
        </a>
        <button type="submit"
                style="height:44px; padding:0 1.5rem; border-radius:0.625rem; border:none;
                       background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.875rem;
                       font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:0.5rem;
                       box-shadow:0 3px 10px rgba(182,0,80,0.25);">
            <span class="material-symbols-outlined" style="font-size:20px;">save</span>
            Enregistrer le lieu de stage
        </button>
    </div>
</form>

@endsection
