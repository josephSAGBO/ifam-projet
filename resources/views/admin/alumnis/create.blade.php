@extends('layouts.admin')

@section('title', 'Ajouter un Alumni')

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
            Ajouter un diplômé (Alumni)
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Enregistrez manuellement un diplômé ou reliez-le à un étudiant éligible.
        </p>
    </div>
</div>

<form action="{{ route('admin.alumnis.store') }}" method="POST" enctype="multipart/form-data"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:2rem; max-width:920px;">
    @csrf

    <div style="display:grid; grid-template-columns:1fr; gap:1.5rem;">

        {{-- Sélection éventuelle d'un dossier étudiant éligible --}}
        @if($eligibleStudents->isNotEmpty())
        <div style="background:var(--color-surface-container-low); padding:1.25rem; border-radius:0.75rem; border:1px solid var(--color-outline-variant);">
            <label style="display:block; font-size:0.875rem; font-weight:700; color:var(--color-on-surface); margin-bottom:0.375rem;">
                Lier à un dossier étudiant existant (Optionnel)
            </label>
            <p style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin:0 0 0.75rem;">
                Seuls les étudiants ayant validé leur formation/parcours et n'ayant plus d'inscriptions en cours sont listés. Si vous créez un ancien diplômé historique (avant IFAM Connect), laissez ce champ vide.
            </p>
            <select name="student_profile_id" id="student_select"
                    style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                           border:1px solid var(--color-outline-variant); background:var(--color-surface-container-lowest);
                           font-size:0.9375rem; color:var(--color-on-surface);">
                <option value="">— Aucun (Diplômé externe ou promotion antérieure) —</option>
                @foreach($eligibleStudents as $student)
                    <option value="{{ $student->id }}"
                            data-nom="{{ $student->user?->last_name }}"
                            data-prenom="{{ $student->user?->first_name }}"
                            data-email="{{ $student->user?->email }}"
                            data-tel="{{ $student->user?->phone }}"
                            {{ old('student_profile_id') == $student->id ? 'selected' : '' }}>
                        {{ $student->user?->full_name }} ({{ $student->user?->matricule ?? 'Sans matricule' }})
                    </option>
                @endforeach
            </select>
            @error('student_profile_id') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
        </div>
        @endif

        {{-- Identity & Photo --}}
        <div style="display:grid; grid-template-columns: 1fr 2fr; gap:1.5rem; align-items:start;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Photo de profil
                </label>
                <div style="border:2px dashed var(--color-outline-variant); border-radius:0.75rem; padding:1.25rem; text-align:center; background:var(--color-surface-container-low);">
                    <span class="material-symbols-outlined" style="font-size:36px; color:var(--color-on-surface-variant); margin-bottom:0.5rem; display:block;">
                        add_a_photo
                    </span>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" style="font-size:0.8125rem; color:var(--color-on-surface-variant); width:100%;">
                </div>
                @error('photo') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                        Nom <span style="color:#dc2626;">*</span>
                    </label>
                    <input type="text" name="nom" id="field_nom" value="{{ old('nom') }}" required placeholder="Ex: KOUASSI"
                           style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                                  border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                  font-size:0.9375rem; color:var(--color-on-surface);">
                    @error('nom') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                        Prénom <span style="color:#dc2626;">*</span>
                    </label>
                    <input type="text" name="prenom" id="field_prenom" value="{{ old('prenom') }}" required placeholder="Ex: Amenan Marie"
                           style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                                  border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                  font-size:0.9375rem; color:var(--color-on-surface);">
                    @error('prenom') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                        Adresse e-mail
                    </label>
                    <input type="email" name="email" id="field_email" value="{{ old('email') }}" placeholder="alumni@email.com"
                           style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                                  border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                  font-size:0.9375rem; color:var(--color-on-surface);">
                    @error('email') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                        Téléphone professionnel (non public par défaut)
                    </label>
                    <input type="text" name="telephone" id="field_telephone" value="{{ old('telephone') }}" placeholder="+229 01 00 00 00"
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
                    Formation principale suivie à l'IFAM
                </label>
                <select name="formation_id"
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                               border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                               font-size:0.9375rem; color:var(--color-on-surface);">
                    <option value="">— Sélectionner la formation —</option>
                    @foreach($formations as $formation)
                        <option value="{{ $formation->id }}" {{ old('formation_id') == $formation->id ? 'selected' : '' }}>
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
                <input type="date" name="date_diplome" value="{{ old('date_diplome') }}"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('date_diplome') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Lieu professionnel / Ville de résidence
                </label>
                <input type="text" name="lieu_residence" value="{{ old('lieu_residence') }}" placeholder="Ex: Cotonou, Bénin / Paris, France"
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
                        <option value="{{ $val }}" {{ old('statut_actuel') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('statut_actuel') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Atelier / Marque / Entreprise actuelle
                </label>
                <input type="text" name="entreprise_actuelle" value="{{ old('entreprise_actuelle') }}" placeholder="Ex: Maison de Couture IFAM, Startup..."
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('entreprise_actuelle') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Métier / Poste exercé
                </label>
                <input type="text" name="poste_actuel" value="{{ old('poste_actuel') }}" placeholder="Ex: Styliste Créatrice, Modéliste, Chef d'atelier..."
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
                <input type="url" name="linkedin_url" value="{{ old('linkedin_url') }}" placeholder="https://linkedin.com/in/..."
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('linkedin_url') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Portfolio / Site web
                </label>
                <input type="url" name="portfolio_url" value="{{ old('portfolio_url') }}" placeholder="https://..."
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('portfolio_url') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Bio / Parcours --}}
        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Biographie professionnelle & Présentation du parcours
            </label>
            <textarea name="bio" rows="4"
                      placeholder="Présentation du parcours professionnel, créations marquantes, témoignages sur la formation IFAM..."
                      style="width:100%; box-sizing:border-box; padding:0.75rem 1rem; border-radius:0.625rem;
                             border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                             font-size:0.9375rem; color:var(--color-on-surface); resize:vertical;">{{ old('bio') }}</textarea>
            @error('bio') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
        </div>

        {{-- Visibilité & Validation --}}
        <div style="background:var(--color-surface-container-low); padding:1.25rem; border-radius:0.75rem; display:flex; flex-direction:column; gap:0.75rem;">
            <label style="display:flex; align-items:center; gap:0.75rem; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); cursor:pointer;">
                <input type="checkbox" name="is_approved" value="1" {{ old('is_approved', '1') ? 'checked' : '' }} style="width:18px; height:18px;">
                Approuver formellement ce profil Alumni (profil validé par l'administration)
            </label>
            <label style="display:flex; align-items:center; gap:0.75rem; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); cursor:pointer;">
                <input type="checkbox" name="is_visible_directory" value="1" {{ old('is_visible_directory', '1') ? 'checked' : '' }} style="width:18px; height:18px;">
                Rendre ce profil visible dans l'annuaire public des diplômés de l'IFAM
            </label>
            <label style="display:flex; align-items:flex-start; gap:0.75rem; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); cursor:pointer;">
                <input type="hidden" name="is_public" value="0">
                <input type="checkbox" name="is_public" value="1" {{ old('is_public') ? 'checked' : '' }} style="width:18px; height:18px;">
                <span>Enregistrer l'accord explicite de l'alumni (à cocher uniquement si son consentement a été donné)</span>
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
            Enregistrer l'Alumni
        </button>
    </div>
</form>

<script>
    document.getElementById('student_select')?.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option && option.value) {
            document.getElementById('field_nom').value = option.dataset.nom || '';
            document.getElementById('field_prenom').value = option.dataset.prenom || '';
            document.getElementById('field_email').value = option.dataset.email || '';
            document.getElementById('field_telephone').value = option.dataset.tel || '';
        }
    });
</script>

@endsection
