@extends('layouts.admin')

@section('title', 'Modifier le Partenariat')

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
            Modifier : {{ $partnership->nom }}
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Mettez à jour les informations du partenariat.
        </p>
    </div>
</div>

<form action="{{ route('admin.partnerships.update', $partnership) }}" method="POST"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:2rem; max-width:900px;">
    @csrf
    @method('PUT')

    <div style="display:grid; grid-template-columns:1fr; gap:1.5rem;">

        {{-- General Info --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Nom de l'entreprise / Organisme <span style="color:#dc2626;">*</span>
                </label>
                <input type="text" name="nom" value="{{ old('nom', $partnership->nom) }}" required
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('nom') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Nom & Prénom du contact (Partenaire) <span style="color:#dc2626;">*</span>
                </label>
                <input type="text" name="prenom" value="{{ old('prenom', $partnership->prenom) }}" required
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('prenom') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Phone, Email --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Numéro de téléphone
                </label>
                <input type="text" name="telephone" value="{{ old('telephone', $partnership->telephone) }}"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('telephone') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Adresse e-mail
                </label>
                <input type="email" name="email" value="{{ old('email', $partnership->email) }}"
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
                    @foreach(['Stage', 'Emploi', 'Matériel', 'Financier', 'Parrainage', 'Formations', 'Autre'] as $type)
                        <option value="{{ $type }}" {{ old('type_partenariat', $partnership->type_partenariat) == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
                @error('type_partenariat') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Durée
                </label>
                <input type="text" name="duree" value="{{ old('duree', $partnership->duree) }}"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('duree') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Secteur d'intervention
                </label>
                <input type="text" name="secteur_intervention" value="{{ old('secteur_intervention', $partnership->secteur_intervention) }}"
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
                    @foreach(['Actif', 'En discussion', 'Inactif', 'Suspendu'] as $statut)
                        <option value="{{ $statut }}" {{ old('statut', $partnership->statut) == $statut ? 'selected' : '' }}>
                            {{ $statut }}
                        </option>
                    @endforeach
                </select>
                @error('statut') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Offers Mutual --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Ce qu'ils nous offrent
                </label>
                <textarea name="ce_qu_ils_offrent" rows="3"
                          style="width:100%; box-sizing:border-box; padding:0.75rem 1rem; border-radius:0.625rem;
                                 border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                 font-size:0.9375rem; color:var(--color-on-surface); resize:vertical;">{{ old('ce_qu_ils_offrent', $partnership->ce_qu_ils_offrent) }}</textarea>
                @error('ce_qu_ils_offrent') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Ce qu'on leur offre
                </label>
                <textarea name="ce_qu_on_offre" rows="3"
                          style="width:100%; box-sizing:border-box; padding:0.75rem 1rem; border-radius:0.625rem;
                                 border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                 font-size:0.9375rem; color:var(--color-on-surface); resize:vertical;">{{ old('ce_qu_on_offre', $partnership->ce_qu_on_offre) }}</textarea>
                @error('ce_qu_on_offre') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Resource Person, Last Contact, Follow-up notes --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Personne ressource interne (Référent IFAM)
                </label>
                <input type="text" name="personne_ressource_interne" value="{{ old('personne_ressource_interne', $partnership->personne_ressource_interne) }}"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('personne_ressource_interne') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Date du dernier contact / appel
                </label>
                <input type="date" name="date_dernier_contact"
                       value="{{ old('date_dernier_contact', $partnership->date_dernier_contact ? $partnership->date_dernier_contact->format('Y-m-d') : '') }}"
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
                      style="width:100%; box-sizing:border-box; padding:0.75rem 1rem; border-radius:0.625rem;
                             border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                             font-size:0.9375rem; color:var(--color-on-surface); resize:vertical;">{{ old('suivi', $partnership->suivi) }}</textarea>
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
            Mettre à jour
        </button>
    </div>
</form>

@endsection
