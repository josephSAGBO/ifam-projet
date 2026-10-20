@extends('layouts.admin')

@section('title', 'Modifier un Alumni — ' . $alumni->full_name)

@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.alumnis.index') }}"
       style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
              display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
              transition:all 0.15s;"
       onmouseover="this.style.background='var(--color-surface-container-high)'"
       onmouseout="this.style.background='var(--color-surface-container)'">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Modifier la fiche : {{ $alumni->full_name }}
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Mettez à jour les informations professionnelles, les liens et la visibilité de cet Alumni.
        </p>
    </div>
</div>

<form action="{{ route('admin.alumnis.update', $alumni->id) }}" method="POST" enctype="multipart/form-data"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:2rem; max-width:920px;">
    @csrf
    @method('PUT')

    <div style="display:grid; grid-template-columns:1fr; gap:1.5rem;">

        {{-- Identity & Photo --}}
        <div style="display:grid; grid-template-columns: 1fr 2fr; gap:1.5rem; align-items:start;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Photo de profil
                </label>
                <div style="text-align:center; margin-bottom:0.75rem;">
                    @if($alumni->photo_url)
                        <img src="{{ $alumni->photo_url }}" alt="" style="width:96px; height:96px; border-radius:50%; object-fit:cover; margin:0 auto 0.75rem; display:block; border:2px solid var(--color-outline-variant);">
                    @endif
                </div>
                <div style="border:2px dashed var(--color-outline-variant); border-radius:0.75rem; padding:1rem; text-align:center; background:var(--color-surface-container-low);">
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" style="font-size:0.8125rem; color:var(--color-on-surface-variant); width:100%;">
                </div>
                @error('photo') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                        Nom <span style="color:#dc2626;">*</span>
                    </label>
                    <input type="text" name="nom" value="{{ old('nom', $alumni->nom) }}" required
                           style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                                  border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                  font-size:0.9375rem; color:var(--color-on-surface);">
                    @error('nom') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                        Prénom <span style="color:#dc2626;">*</span>
                    </label>
                    <input type="text" name="prenom" value="{{ old('prenom', $alumni->prenom) }}" required
                           style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                                  border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                  font-size:0.9375rem; color:var(--color-on-surface);">
                    @error('prenom') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                        Adresse e-mail
                    </label>
                    <input type="email" name="email" value="{{ old('email', $alumni->email) }}"
                           style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                                  border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                  font-size:0.9375rem; color:var(--color-on-surface);">
                    @error('email') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                        Téléphone
                    </label>
                    <input type="text" name="telephone" value="{{ old('telephone', $alumni->telephone) }}"
                           style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                                  border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                  font-size:0.9375rem; color:var(--color-on-surface);">
                    @error('telephone') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Academic Path --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Formation principale
                </label>
                <select name="formation_id"
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                               border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                               font-size:0.9375rem; color:var(--color-on-surface);">
                    <option value="">— Sélectionner la formation —</option>
                    @foreach($formations as $formation)
                        <option value="{{ $formation->id }}" {{ old('formation_id', $alumni->formation_id) == $formation->id ? 'selected' : '' }}>
                            {{ $formation->name }}
                        </option>
                    @endforeach
                </select>
                @error('formation_id') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Date d'obtention du diplôme
                </label>
                <input type="date" name="date_diplome" value="{{ old('date_diplome', $alumni->date_diplome?->format('Y-m-d')) }}"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('date_diplome') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Lieu professionnel / Ville
                </label>
                <input type="text" name="lieu_residence" value="{{ old('lieu_residence', $alumni->lieu_residence) }}"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('lieu_residence') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Professional Situation --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Situation professionnelle actuelle
                </label>
                <select name="statut_actuel"
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                               border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                               font-size:0.9375rem; color:var(--color-on-surface);">
                    <option value="">— Sélectionner —</option>
                    @foreach($statutsPro as $val => $label)
                        <option value="{{ $val }}" {{ old('statut_actuel', $alumni->statut_actuel) == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('statut_actuel') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Entreprise / Atelier / Marque
                </label>
                <input type="text" name="entreprise_actuelle" value="{{ old('entreprise_actuelle', $alumni->entreprise_actuelle) }}"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('entreprise_actuelle') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Poste / Métier
                </label>
                <input type="text" name="poste_actuel" value="{{ old('poste_actuel', $alumni->poste_actuel) }}"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('poste_actuel') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Web & Social Links --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Lien LinkedIn
                </label>
                <input type="url" name="linkedin_url" value="{{ old('linkedin_url', $alumni->linkedin_url) }}" placeholder="https://linkedin.com/in/..."
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('linkedin_url') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Portfolio / Site web
                </label>
                <input type="url" name="portfolio_url" value="{{ old('portfolio_url', $alumni->portfolio_url) }}" placeholder="https://..."
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('portfolio_url') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Bio --}}
        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Biographie professionnelle & Présentation
            </label>
            <textarea name="bio" rows="4"
                      style="width:100%; box-sizing:border-box; padding:0.75rem 1rem; border-radius:0.625rem;
                             border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                             font-size:0.9375rem; color:var(--color-on-surface); resize:vertical;">{{ old('bio', $alumni->bio) }}</textarea>
            @error('bio') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
        </div>

        {{-- Validation & Visibilité --}}
        <div style="background:var(--color-surface-container-low); padding:1.25rem; border-radius:0.75rem; display:flex; flex-direction:column; gap:0.75rem;">
            <label style="display:flex; align-items:center; gap:0.75rem; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); cursor:pointer;">
                <input type="checkbox" name="is_approved" value="1" {{ old('is_approved', $alumni->is_approved) ? 'checked' : '' }} style="width:18px; height:18px;">
                Profil approuvé par l'administration
            </label>
            <label style="display:flex; align-items:center; gap:0.75rem; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); cursor:pointer;">
                <input type="checkbox" name="is_visible_directory" value="1" {{ old('is_visible_directory', $alumni->is_visible_directory) ? 'checked' : '' }} style="width:18px; height:18px;">
                Visible dans l'annuaire public des diplômés de l'IFAM
            </label>
            <label style="display:flex; align-items:flex-start; gap:0.75rem; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); cursor:pointer;">
                <input type="hidden" name="is_public" value="0">
                <input type="checkbox" name="is_public" value="1" {{ old('is_public', $alumni->is_public) ? 'checked' : '' }} style="width:18px; height:18px;">
                <span>Accord explicite de l'alumni enregistré (cocher uniquement si son consentement a été donné)</span>
            </label>
        </div>

    </div>

    <div style="margin-top:2rem; display:flex; justify-content:flex-end; gap:1rem;">
        <a href="{{ route('admin.alumnis.index') }}"
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
