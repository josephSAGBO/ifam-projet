@extends('layouts.admin')

@section('title', 'Modifier le rôle : ' . $role->name)

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
            Configurer le rôle
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            {{ $role->name }} @if($isSystemRole) <span style="color:var(--color-primary-container); font-weight:600;">(Rôle système protégé)</span> @endif
        </p>
    </div>
</div>

<form action="{{ route('admin.roles.update', $role) }}" method="POST"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:2rem; max-width:850px;">
    @csrf
    @method('PUT')

    <div style="margin-bottom:2rem;">
        <label for="role-name" style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
            Nom du rôle
        </label>
        <input type="text" id="role-name" name="name" value="{{ old('name', $role->name) }}"
               {{ $isSystemRole ? 'readonly' : 'required' }}
               style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                      border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                      font-size:0.9375rem; color:var(--color-on-surface); {{ $isSystemRole ? 'opacity:0.7; cursor:not-allowed;' : '' }}"
               onfocus="this.style.borderColor='var(--color-primary-container)'"
               onblur="this.style.borderColor='var(--color-outline-variant)'">
        @if($isSystemRole)
        <p style="margin:0.25rem 0 0; font-size:0.75rem; color:var(--color-on-surface-variant);">
            Le nom des rôles système essentiels ne peut pas être modifié pour préserver l'intégrité de l'application.
        </p>
        @endif
        @error('name') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
    </div>

    <div style="margin-bottom:2rem;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.5rem;">
            <div style="font-size:0.875rem; font-weight:700; color:var(--color-on-surface);">
                Permissions associées ({{ count($rolePermissionNames) }} actives)
            </div>
        </div>
        <p style="font-size:0.75rem; color:var(--color-on-surface-variant); margin:0 0 1rem;">
            Cochez ou décochez les permissions pour mettre à jour les droits d'accès de ce rôle
        </p>

        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(250px, 1fr)); gap:0.75rem; max-height:420px; overflow-y:auto; padding:0.75rem; border:1px solid var(--color-outline-variant); border-radius:0.75rem; background:var(--color-surface-container-low);">
            @foreach($allPermissions as $perm)
            @php
                $checked = in_array($perm->name, old('permissions', $rolePermissionNames), true);
            @endphp
            <label style="display:flex; align-items:center; gap:0.5rem; padding:0.5rem 0.625rem; background:var(--color-surface-container-lowest); border-radius:0.5rem; cursor:pointer;">
                <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                       {{ $checked ? 'checked' : '' }}
                       style="accent-color:var(--color-primary-container);">
                <span style="font-size:0.8125rem; color:var(--color-on-surface);">{{ $perm->name }}</span>
            </label>
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
            Enregistrer les modifications
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
