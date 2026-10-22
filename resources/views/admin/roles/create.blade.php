@extends('layouts.admin')

@section('title', 'Nouveau rôle')

@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.roles.index') }}"
       style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
              display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
              transition:all 0.15s;"
       onmouseover="this.style.background='var(--color-surface-container-high)'"
       onmouseout="this.style.background='var(--color-surface-container)'">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Nouveau rôle
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Définir un rôle et configurer ses permissions associées
        </p>
    </div>
</div>

<form action="{{ route('admin.roles.store') }}" method="POST"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:2rem; max-width:800px;">
    @csrf

    <div style="margin-bottom:2rem;">
        <label for="role-name" style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
            Nom du rôle <span style="color:#dc2626;">*</span>
        </label>
        <input type="text" id="role-name" name="name" value="{{ old('name') }}" required
               placeholder="ex: superviseur_examen, auditeur_qualite…"
               style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                      border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                      font-size:0.9375rem; color:var(--color-on-surface);"
               onfocus="this.style.borderColor='var(--color-primary-container)'"
               onblur="this.style.borderColor='var(--color-outline-variant)'">
        @error('name') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
    </div>

    <div style="margin-bottom:2rem;">
        <div style="font-size:0.875rem; font-weight:700; color:var(--color-on-surface); margin-bottom:0.5rem;">
            Permissions attribuées
        </div>
        <p style="font-size:0.75rem; color:var(--color-on-surface-variant); margin:0 0 1rem;">
            Cochez les permissions autorisées pour ce rôle
        </p>

        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:0.75rem; max-height:400px; overflow-y:auto; padding:0.5rem; border:1px solid var(--color-outline-variant); border-radius:0.75rem; background:var(--color-surface-container-low);">
            @foreach($permissions as $group => $perms)
            <div style="grid-column: 1 / -1; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-primary-container); margin-top:0.5rem;">
                Thème : {{ $group }}
            </div>
            @foreach($perms as $perm)
            <label style="display:flex; align-items:center; gap:0.5rem; padding:0.375rem 0.5rem; background:var(--color-surface-container-lowest); border-radius:0.5rem; cursor:pointer;">
                <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                       {{ in_array($perm->name, old('permissions', []), true) ? 'checked' : '' }}
                       style="accent-color:var(--color-primary-container);">
                <span style="font-size:0.8125rem; color:var(--color-on-surface);">{{ $perm->name }}</span>
            </label>
            @endforeach
            @endforeach
        </div>
    </div>

    <div style="display:flex; gap:1rem;">
        <button type="submit"
                style="padding:0.625rem 1.5rem; background:var(--color-primary-container); color:var(--color-on-primary);
                       border-radius:0.625rem; border:none; font-size:0.875rem; font-weight:700; cursor:pointer;
                       box-shadow:0 2px 8px rgba(228,0,102,0.25); transition:all 0.15s;"
                onmouseover="this.style.opacity='0.9'"
                onmouseout="this.style.opacity='1'">
            Créer le rôle
        </button>
        <a href="{{ route('admin.roles.index') }}"
           style="padding:0.625rem 1.25rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant);
                  background:var(--color-surface-container); color:var(--color-on-surface); text-decoration:none;
                  font-size:0.875rem; font-weight:600; display:inline-flex; align-items:center;">
            Annuler
        </a>
    </div>

</form>

@endsection
