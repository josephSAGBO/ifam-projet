@extends('layouts.admin')

@section('title', 'Nouveau Partenariat')

@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.partnerships.index') }}"
       style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
              display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
              transition:all 0.15s;"
       onmouseover="this.style.background='var(--color-surface-container-high)'"
       onmouseout="this.style.background='var(--color-surface-container)'">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Nouveau partenariat
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Enregistrez un nouvel organisme ou entreprise partenaire.
        </p>
    </div>
</div>

<form action="{{ route('admin.partnerships.store') }}" method="POST"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:2rem; max-width:900px;">
    @csrf

    <div style="display:grid; grid-template-columns:1fr; gap:1.5rem;">

        {{-- General Info: Structure, Contact Person --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Nom de l'entreprise / Organisme <span style="color:#dc2626;">*</span>
                </label>
                <input type="text" name="nom" value="{{ old('nom') }}" required
                       placeholder="Ex: Orange Côte d'Ivoire, Bank of Africa..."
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('nom') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Nom & Prénom du contact (Partenaire) <span style="color:#dc2626;">*</span>
                </label>
                <input type="text" name="prenom" value="{{ old('prenom') }}" required
                       placeholder="Ex: Jean Dupont, Directrice RH..."
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('prenom') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Contact Phone, Email --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Numéro de téléphone
                </label>
                <input type="text" name="telephone" value="{{ old('telephone') }}"
                       placeholder="Ex: +225 07 00 00 00 00"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('telephone') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Adresse e-mail
                </label>
                <input type="email" name="email" value="{{ old('email') }}"
                       placeholder="contact@partenaire.com"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('email') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Type, Duration, Sector, Status --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Type de partenariat <span style="color:#dc2626;">*</span>
                </label>
                <select name="type_partenariat" required
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                               border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                               font-size:0.9375rem; color:var(--color-on-surface);">
                    <option value="Stage" {{ old('type_partenariat') == 'Stage' ? 'selected' : '' }}>Stage</option>
                    <option value="Emploi" {{ old('type_partenariat') == 'Emploi' ? 'selected' : '' }}>Emploi</option>
                    <option value="Matériel" {{ old('type_partenariat') == 'Matériel' ? 'selected' : '' }}>Matériel</option>
                    <option value="Financier" {{ old('type_partenariat') == 'Financier' ? 'selected' : '' }}>Financier</option>
                    <option value="Parrainage" {{ old('type_partenariat') == 'Parrainage' ? 'selected' : '' }}>Parrainage</option>
                    <option value="Formations" {{ old('type_partenariat') == 'Formations' ? 'selected' : '' }}>Formations</option>
                    <option value="Autre" {{ old('type_partenariat') == 'Autre' ? 'selected' : '' }}>Autre</option>
                </select>
                @error('type_partenariat') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Durée
                </label>
                <input type="text" name="duree" value="{{ old('duree') }}"
                       placeholder="Ex: 1 an renouvelable, 3 ans, Indéterminée"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('duree') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Secteur d'intervention
                </label>
                <input type="text" name="secteur_intervention" value="{{ old('secteur_intervention') }}"
                       placeholder="Ex: Télécoms, Banque, Informatique, BTP..."
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('secteur_intervention') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Statut du partenariat <span style="color:#dc2626;">*</span>
                </label>
                <select name="statut" required
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                               border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                               font-size:0.9375rem; color:var(--color-on-surface);">
                    <option value="Actif" {{ old('statut') == 'Actif' ? 'selected' : '' }}>Actif</option>
                    <option value="En discussion" {{ old('statut') == 'En discussion' ? 'selected' : '' }}>En discussion</option>
                    <option value="Inactif" {{ old('statut') == 'Inactif' ? 'selected' : '' }}>Inactif</option>
                    <option value="Suspendu" {{ old('statut') == 'Suspendu' ? 'selected' : '' }}>Suspendu</option>
                </select>
                @error('statut') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Offers Mutual exchange --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Ce qu'ils nous offrent
                </label>
                <textarea name="ce_qu_ils_offrent" rows="3"
                          placeholder="Ex: Stages de 6 mois pour nos étudiants, bourses de mémoire, matériel informatique..."
                          style="width:100%; box-sizing:border-box; padding:0.75rem 1rem; border-radius:0.625rem;
                                 border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                 font-size:0.9375rem; color:var(--color-on-surface); resize:vertical;">{{ old('ce_qu_ils_offrent') }}</textarea>
                @error('ce_qu_ils_offrent') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Ce qu'on leur offre
                </label>
                <textarea name="ce_qu_on_offre" rows="3"
                          placeholder="Ex: Visibilité lors des événements IFAM, recrutement prioritaire de diplômés, formations sur-mesure..."
                          style="width:100%; box-sizing:border-box; padding:0.75rem 1rem; border-radius:0.625rem;
                                 border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                 font-size:0.9375rem; color:var(--color-on-surface); resize:vertical;">{{ old('ce_qu_on_offre') }}</textarea>
                @error('ce_qu_on_offre') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Resource Person, Last Contact, Follow-up notes --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Personne ressource interne (Référent IFAM)
                </label>
                <input type="text" name="personne_ressource_interne" value="{{ old('personne_ressource_interne') }}"
                       placeholder="Ex: Dr. Koffi (Chef de Département IT), M. Touré..."
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('personne_ressource_interne') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Date du dernier contact / appel
                </label>
                <input type="date" name="date_dernier_contact" value="{{ old('date_dernier_contact') }}"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('date_dernier_contact') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>

        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Suivi / Notes de relance
            </label>
            <textarea name="suivi" rows="3"
                      placeholder="Résumé des derniers échanges, prochaines échéances ou actions à mener..."
                      style="width:100%; box-sizing:border-box; padding:0.75rem 1rem; border-radius:0.625rem;
                             border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                             font-size:0.9375rem; color:var(--color-on-surface); resize:vertical;">{{ old('suivi') }}</textarea>
            @error('suivi') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
        </div>

    </div>

    <div style="margin-top:2rem; display:flex; justify-content:flex-end; gap:1rem;">
        <a href="{{ route('admin.partnerships.index') }}"
           style="height:44px; display:inline-flex; align-items:center; padding:0 1.25rem; border-radius:0.75rem;
                  background:transparent; color:var(--color-on-surface-variant); font-size:0.9375rem; font-weight:600; text-decoration:none;">
            Annuler
        </a>
        <button type="submit"
                style="height:44px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.5rem; border-radius:0.75rem; border:none; cursor:pointer;
                       background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.9375rem; font-weight:700;
                       box-shadow:0 3px 10px rgba(228,0,102,0.3);">
            <span class="material-symbols-outlined" style="font-size:20px;">save</span>
            Enregistrer le partenariat
        </button>
    </div>
</form>

@endsection
