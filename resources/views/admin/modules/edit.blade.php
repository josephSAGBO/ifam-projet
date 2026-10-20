@extends('layouts.admin')

@section('title', 'Modifier le Module')

@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.modules.index') }}"
       style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
              display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
              transition:all 0.15s;"
       onmouseover="this.style.background='var(--color-surface-container-high)'"
       onmouseout="this.style.background='var(--color-surface-container)'">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Modifier le module
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            {{ $module->code }} — {{ $module->name }}
        </p>
    </div>
</div>

<form action="{{ route('admin.modules.update', $module) }}" method="POST"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:2rem; max-width:800px;">
    @csrf
    @method('PUT')

    <div style="display:grid; grid-template-columns:1fr; gap:1.5rem;">

        {{-- Formation parente --}}
        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Formation <span style="color:#dc2626;">*</span>
            </label>
            <select name="formation_id" required
                    style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                           border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                           font-size:0.9375rem; color:var(--color-on-surface);"
                    onfocus="this.style.borderColor='var(--color-primary-container)'"
                    onblur="this.style.borderColor='var(--color-outline-variant)'">
                <option value="">— Aucune formation —</option>
                @foreach($formations as $formation)
                    <option value="{{ $formation->id }}"
                        {{ old('formation_id', $module->formations->first()?->id) == $formation->id ? 'selected' : '' }}>
                        {{ $formation->name }}
                    </option>
                @endforeach
            </select>
            @error('formation_id') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
        </div>

        {{-- Code and Name --}}
        <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Code du module <span style="color:#dc2626;">*</span>
                </label>
                <input type="text" name="code" value="{{ old('code', $module->code) }}" required
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface); text-transform:uppercase;"
                       onfocus="this.style.borderColor='var(--color-primary-container)'"
                       onblur="this.style.borderColor='var(--color-outline-variant)'">
                @error('code') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Nom du module <span style="color:#dc2626;">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name', $module->name) }}" required
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);"
                       onfocus="this.style.borderColor='var(--color-primary-container)'"
                       onblur="this.style.borderColor='var(--color-outline-variant)'">
                @error('name') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Coefficient and Durée --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Coefficient
                </label>
                <input type="number" name="coefficient" value="{{ old('coefficient', $module->coefficient) }}" min="0" step="0.01"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);"
                       onfocus="this.style.borderColor='var(--color-primary-container)'"
                       onblur="this.style.borderColor='var(--color-outline-variant)'">
                @error('coefficient') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Durée <small style="font-weight:400; color:var(--color-on-surface-variant);">(remplissez les champs voulus)</small>
                </label>
                <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:0.5rem;">
                    <div>
                        <input type="number" name="duree_mois" value="{{ old('duree_mois', $module->duree_mois) }}" min="0" placeholder="Mois"
                               style="width:100%; box-sizing:border-box; height:44px; padding:0 0.75rem; border-radius:0.625rem;
                                      border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                      font-size:0.9375rem; color:var(--color-on-surface);"
                               onfocus="this.style.borderColor='var(--color-primary-container)'"
                               onblur="this.style.borderColor='var(--color-outline-variant)'">
                        <small style="display:block; text-align:center; color:var(--color-on-surface-variant); margin-top:2px;">Mois</small>
                    </div>
                    <div>
                        <input type="number" name="duree_semaines" value="{{ old('duree_semaines', $module->duree_semaines) }}" min="0" placeholder="Semaines"
                               style="width:100%; box-sizing:border-box; height:44px; padding:0 0.75rem; border-radius:0.625rem;
                                      border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                      font-size:0.9375rem; color:var(--color-on-surface);"
                               onfocus="this.style.borderColor='var(--color-primary-container)'"
                               onblur="this.style.borderColor='var(--color-outline-variant)'">
                        <small style="display:block; text-align:center; color:var(--color-on-surface-variant); margin-top:2px;">Semaines</small>
                    </div>
                    <div>
                        <input type="number" name="duree_heures" value="{{ old('duree_heures', $module->duree_heures) }}" min="0" placeholder="Heures"
                               style="width:100%; box-sizing:border-box; height:44px; padding:0 0.75rem; border-radius:0.625rem;
                                      border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                      font-size:0.9375rem; color:var(--color-on-surface);"
                               onfocus="this.style.borderColor='var(--color-primary-container)'"
                               onblur="this.style.borderColor='var(--color-outline-variant)'">
                        <small style="display:block; text-align:center; color:var(--color-on-surface-variant); margin-top:2px;">Heures</small>
                    </div>
                    <div>
                        <input type="number" name="duree_minutes" value="{{ old('duree_minutes', $module->duree_minutes) }}" min="0" max="59" placeholder="Minutes"
                               style="width:100%; box-sizing:border-box; height:44px; padding:0 0.75rem; border-radius:0.625rem;
                                      border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                      font-size:0.9375rem; color:var(--color-on-surface);"
                               onfocus="this.style.borderColor='var(--color-primary-container)'"
                               onblur="this.style.borderColor='var(--color-outline-variant)'">
                        <small style="display:block; text-align:center; color:var(--color-on-surface-variant); margin-top:2px;">Minutes</small>
                    </div>
                </div>
                @error('duree_mois') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
                @error('duree_semaines') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
                @error('duree_heures') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
                @error('duree_minutes') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Description --}}
        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Description
            </label>
            <textarea name="description" rows="4"
                      style="width:100%; box-sizing:border-box; padding:0.75rem 1rem; border-radius:0.625rem;
                             border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                             font-size:0.9375rem; color:var(--color-on-surface); resize:vertical;"
                      onfocus="this.style.borderColor='var(--color-primary-container)'"
                      onblur="this.style.borderColor='var(--color-outline-variant)'">{{ old('description', $module->description) }}</textarea>
            @error('description') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
        </div>
    </div>

    <div style="margin-top:2rem; display:flex; justify-content:flex-end; gap:1rem;">
        <a href="{{ route('admin.modules.index') }}"
           style="height:44px; display:inline-flex; align-items:center; padding:0 1.25rem; border-radius:0.75rem;
                  background:transparent; color:var(--color-on-surface-variant); font-size:0.9375rem; font-weight:600; text-decoration:none;
                  transition:all 0.15s;"
           onmouseover="this.style.background='var(--color-surface-container)'"
           onmouseout="this.style.background='transparent'">
            Annuler
        </a>
        <button type="submit"
                style="height:44px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.5rem; border-radius:0.75rem; border:none; cursor:pointer;
                       background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.9375rem; font-weight:700;
                       box-shadow:0 3px 10px rgba(228,0,102,0.3); transition:all 0.15s;"
                onmouseover="this.style.transform='translateY(-1px)'"
                onmouseout="this.style.transform=''">
            <span class="material-symbols-outlined" style="font-size:20px;">save</span>
            Mettre à jour
        </button>
    </div>
</form>

@endsection
