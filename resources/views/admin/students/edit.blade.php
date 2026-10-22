@extends('layouts.admin')

@section('title', 'Mise à jour du dossier étudiant')

@section('content')

{{-- En-tête --}}
<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.students.show', $student) }}"
       style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
              display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
              transition:all 0.15s;"
       onmouseover="this.style.background='var(--color-surface-container-high)'"
       onmouseout="this.style.background='var(--color-surface-container)'">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Dossier : {{ $student->full_name }}
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0; display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
            <span>Complétez ou mettez à jour les informations spécifiques au profil étudiant.</span>
            @php
                $studentSites = $student->sites->concat($student->studentProfile?->enrollments->pluck('site')->filter() ?? collect())->unique('id');
            @endphp
            @if($studentSites->isNotEmpty())
                <span>• Site(s) :</span>
                @foreach($studentSites as $sSite)
                    <span style="padding:0.15rem 0.45rem; border-radius:0.375rem; font-size:0.75rem; font-weight:600; background:#f0fdf4; color:#166534; border:1px solid #bbf7d0;">
                        {{ $sSite->nom }}
                    </span>
                @endforeach
            @endif
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.students.update', $student) }}"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:2rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    @csrf
    @method('PUT')

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:2rem;">

        {{-- État civil --}}
        <div style="grid-column:1 / -1; padding-bottom:1rem; border-bottom:1px solid var(--color-surface-container); margin-bottom:0.5rem;">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">État Civil & Naissance</h2>
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Lieu de naissance
            </label>
            <input type="text" name="birth_place" value="{{ old('birth_place', $profile->birth_place) }}"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('birth_place') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
            @error('birth_place') <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Nationalité
            </label>
            <input type="text" name="nationality" value="{{ old('nationality', $profile->nationality ?? 'Béninoise') }}"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('nationality') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
            @error('nationality') <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p> @enderror
        </div>

        <div style="grid-column:1 / -1;">
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Numéro d'Identidication Personnel(NPI)
            </label>
            <input type="text" name="birth_certificate_number" value="{{ old('birth_certificate_number', $profile->birth_certificate_number) }}"
                   style="width:100%; max-width:400px; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('birth_certificate_number') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface); font-family:monospace;">
            @error('birth_certificate_number') <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p> @enderror
        </div>

        {{-- Adresse --}}
        <div style="grid-column:1 / -1; padding-bottom:1rem; border-bottom:1px solid var(--color-surface-container); margin-top:1rem; margin-bottom:0.5rem;">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Adresse et Domicile</h2>
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Adresse physique
            </label>
            <input type="text" name="address" value="{{ old('address', $profile->address) }}" placeholder="Quartier, rue, maison..."
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('address') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
            @error('address') <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Ville
            </label>
            <input type="text" name="city" value="{{ old('city', $profile->city) }}"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('city') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
            @error('city') <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p> @enderror
        </div>

        {{-- Contact Urgence --}}
        <div style="grid-column:1 / -1; padding-bottom:1rem; border-bottom:1px solid var(--color-surface-container); margin-top:1rem; margin-bottom:0.5rem;">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Contact d'Urgence / Tuteur Légal</h2>
        </div>

        <div style="grid-column:1 / -1; display:grid; grid-template-columns:1fr 1fr 1fr; gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Nom complet
                </label>
                <input type="text" name="guardian_name" value="{{ old('guardian_name', $profile->guardian_name) }}"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                              border:1.5px solid {{ $errors->has('guardian_name') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                              background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
                @error('guardian_name') <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Lien de parenté
                </label>
                <input type="text" name="guardian_relation" value="{{ old('guardian_relation', $profile->guardian_relation) }}" placeholder="Ex: Père, Mère, Oncle..."
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                              border:1.5px solid {{ $errors->has('guardian_relation') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                              background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
                @error('guardian_relation') <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Téléphone
                </label>
                <input type="text" name="guardian_phone" value="{{ old('guardian_phone', $profile->guardian_phone) }}"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                              border:1.5px solid {{ $errors->has('guardian_phone') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                              background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
                @error('guardian_phone') <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Parcours & IFAM --}}
        <div style="grid-column:1 / -1; padding-bottom:1rem; border-bottom:1px solid var(--color-surface-container); margin-top:1rem; margin-bottom:0.5rem;">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Parcours Académique</h2>
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Établissement précédent
            </label>
            <input type="text" name="previous_school" value="{{ old('previous_school', $profile->previous_school) }}"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('previous_school') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
            @error('previous_school') <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Dernier diplôme obtenu
            </label>
            <input type="text" name="previous_diploma" value="{{ old('previous_diploma', $profile->previous_diploma) }}" placeholder="Ex: BAC G2, Licence pro..."
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('previous_diploma') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
            @error('previous_diploma') <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p> @enderror
        </div>

        <div style="grid-column:1 / -1;">
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Année d'entrée à l'IFAM
            </label>
            <input type="number" name="entry_year" min="2000" max="{{ date('Y')+1 }}" value="{{ old('entry_year', $profile->entry_year ?? date('Y')) }}"
                   style="width:100%; max-width:200px; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('entry_year') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
            @error('entry_year') <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p> @enderror
        </div>

        {{-- Notes Admin --}}
        <div style="grid-column:1 / -1; padding-bottom:1rem; border-bottom:1px solid var(--color-surface-container); margin-top:1rem; margin-bottom:0.5rem;">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Notes Administratives</h2>
            <p style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin:0;">
                Ces notes sont confidentielles et visibles uniquement par l'administration.
            </p>
        </div>

        <div style="grid-column:1 / -1;">
            <textarea name="admin_notes" rows="4"
                      style="width:100%; box-sizing:border-box; padding:1rem; border-radius:0.75rem; resize:vertical;
                             border:1.5px solid {{ $errors->has('admin_notes') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                             background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface); font-family:inherit;">{{ old('admin_notes', $profile->admin_notes) }}</textarea>
            @error('admin_notes') <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p> @enderror
        </div>

    </div>

    {{-- Actions --}}
    <div style="display:flex; justify-content:flex-end; gap:1rem; padding-top:1.5rem; border-top:1px solid var(--color-surface-container);">
        <a href="{{ route('admin.students.show', $student) }}"
           style="height:44px; display:inline-flex; align-items:center; padding:0 1.5rem; border-radius:0.75rem;
                  background:var(--color-surface-container-high); color:var(--color-on-surface); text-decoration:none;
                  font-size:0.875rem; font-weight:600;">
            Annuler
        </a>
        <button type="submit"
                style="height:44px; padding:0 1.5rem; border-radius:0.75rem; border:none; cursor:pointer;
                       background:var(--color-primary-container); color:var(--color-on-primary);
                       font-size:0.875rem; font-weight:700; box-shadow:0 3px 10px rgba(228,0,102,0.3);">
            Enregistrer le dossier
        </button>
    </div>
</form>

@endsection
