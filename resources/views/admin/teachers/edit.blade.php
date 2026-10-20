@extends('layouts.admin')

@section('title', 'Mise à jour du dossier enseignant')

@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.teachers.show', $teacher) }}"
       style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
              display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
              transition:all 0.15s;"
       onmouseover="this.style.background='var(--color-surface-container-high)'"
       onmouseout="this.style.background='var(--color-surface-container)'">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Dossier : {{ $teacher->full_name }}
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Complétez ou mettez à jour les informations spécifiques au profil enseignant.
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.teachers.update', $teacher) }}"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:2rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    @csrf
    @method('PUT')

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:2rem;">

        <div style="grid-column:1 / -1; padding-bottom:1rem; border-bottom:1px solid var(--color-surface-container); margin-bottom:0.5rem;">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Qualifications &amp; IFAM</h2>
        </div>

        {{-- Modules enseignés (multi-sélection) --}}
        <div style="grid-column:1 / -1;">
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Modules enseignés
                <span style="font-weight:400; color:var(--color-on-surface-variant); margin-left:0.25rem;">(Ctrl+clic pour plusieurs)</span>
            </label>
            <select name="modules[]" multiple
                    style="width:100%; box-sizing:border-box; min-height:140px; padding:0.5rem; border-radius:0.75rem;
                           border:1.5px solid {{ $errors->has('modules') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                           background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
                @foreach($modules as $module)
                    <option value="{{ $module->id }}"
                        {{ in_array($module->id, old('modules', $selectedModuleIds)) ? 'selected' : '' }}>
                        {{ $module->code }} — {{ $module->name }}
                    </option>
                @endforeach
            </select>
            @error('modules') <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p> @enderror
            @if($modules->isEmpty())
                <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-on-surface-variant);">
                    Aucun module disponible. <a href="{{ route('admin.modules.create') }}" style="color:var(--color-primary);">Créer un module</a>.
                </p>
            @endif
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Dernier diplôme / Qualification
            </label>
            <input type="text" name="qualification" value="{{ old('qualification', $profile->qualification) }}"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('qualification') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
            @error('qualification') <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Type d'enseignant
            </label>
            <select name="teacher_type"
                    style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                           border:1.5px solid {{ $errors->has('teacher_type') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                           background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface); cursor:pointer;">
                <option value="">Sélectionner un type</option>
                <option value="permanent"  {{ old('teacher_type', $profile->teacher_type) === 'permanent'  ? 'selected' : '' }}>Permanent</option>
                <option value="vacataire"  {{ old('teacher_type', $profile->teacher_type) === 'vacataire'  ? 'selected' : '' }}>Vacataire</option>
            </select>
            @error('teacher_type') <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Date d'embauche (Début de collaboration)
            </label>
            <input type="date" name="hired_at" value="{{ old('hired_at', $profile->hired_at?->format('Y-m-d')) }}"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('hired_at') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
            @error('hired_at') <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p> @enderror
        </div>

        <div style="grid-column:1 / -1;">
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Numéro d'identification IFU/NPI (interne)
            </label>
            <input type="text" name="id_number" value="{{ old('id_number', $profile->id_number) }}"
                   style="width:100%; max-width:400px; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('id_number') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface); font-family:monospace;">
            @error('id_number') <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p> @enderror
        </div>

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

    <div style="display:flex; justify-content:flex-end; gap:1rem; padding-top:1.5rem; border-top:1px solid var(--color-surface-container);">
        <a href="{{ route('admin.teachers.show', $teacher) }}"
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
