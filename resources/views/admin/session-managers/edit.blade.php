@extends('layouts.admin')

@section('title', 'Mise à jour du dossier gestionnaire')

@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.session-managers.show', $session_manager) }}"
       style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
              display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
              transition:all 0.15s;"
       onmouseover="this.style.background='var(--color-surface-container-high)'"
       onmouseout="this.style.background='var(--color-surface-container)'">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Dossier : {{ $session_manager->full_name }}
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Complétez ou mettez à jour les informations professionnelles.
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.session-managers.update', $session_manager) }}"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:2rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    @csrf
    @method('PUT')

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:2rem;">

        <div style="grid-column:1 / -1; padding-bottom:1rem; border-bottom:1px solid var(--color-surface-container); margin-bottom:0.5rem;">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Fonction & Département</h2>
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Département d'affectation
            </label>
            <input type="text" name="department" value="{{ old('department', $profile->department) }}"
                   placeholder="Ex: Scolarité, Pédagogie..."
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('department') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
            @error('department') <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Rôle interne détaillé
            </label>
            <input type="text" name="internal_role" value="{{ old('internal_role', $profile->internal_role) }}"
                   placeholder="Ex: Responsable Master 1"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('internal_role') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
            @error('internal_role') <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p> @enderror
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
        <a href="{{ route('admin.session-managers.show', $session_manager) }}"
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
