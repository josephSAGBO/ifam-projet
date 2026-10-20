@extends('layouts.admin')

@section('title', 'Modifier la Formation')

@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.formations.index') }}"
       style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
              display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
              transition:all 0.15s;"
       onmouseover="this.style.background='var(--color-surface-container-high)'"
       onmouseout="this.style.background='var(--color-surface-container)'">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Modifier la formation
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            {{ $formation->name }}
        </p>
    </div>
</div>

<form action="{{ route('admin.formations.update', $formation) }}" method="POST"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:2rem; max-width:800px;">
    @csrf
    @method('PUT')

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">

        {{-- Name --}}
        <div style="grid-column:1 / -1;">
            <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Nom de la formation <span style="color:#dc2626;">*</span>
            </label>
            <input type="text" name="name" value="{{ old('name', $formation->name) }}" required
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                          border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                          font-size:0.9375rem; color:var(--color-on-surface);"
                   onfocus="this.style.borderColor='var(--color-primary-container)'"
                   onblur="this.style.borderColor='var(--color-outline-variant)'">
            @error('name') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
        </div>

        {{-- Parcours --}}
        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Parcours
            </label>
            <select name="parcours_id"
                    style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                           border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                           font-size:0.9375rem; color:var(--color-on-surface);"
                    onfocus="this.style.borderColor='var(--color-primary-container)'"
                    onblur="this.style.borderColor='var(--color-outline-variant)'">
                <option value="">— Aucun parcours —</option>
                @foreach($parcoursList as $parcours)
                    <option value="{{ $parcours->id }}"
                        {{ old('parcours_id', $formation->parcours_id) == $parcours->id ? 'selected' : '' }}>
                        {{ $parcours->nom }}
                    </option>
                @endforeach
            </select>
            @error('parcours_id') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
        </div>

        {{-- Ordre dans le parcours --}}
        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Ordre dans le parcours
            </label>
            <input type="number" name="ordre" value="{{ old('ordre', $formation->ordre) }}" min="0"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                          border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                          font-size:0.9375rem; color:var(--color-on-surface);"
                   onfocus="this.style.borderColor='var(--color-primary-container)'"
                   onblur="this.style.borderColor='var(--color-outline-variant)'">
            @error('ordre') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
        </div>

        {{-- Prix --}}
        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Frais de scolarité (FCFA)
            </label>
            <input type="number" name="prix" value="{{ old('prix', $formation->prix) }}" min="0" step="0.01"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                          border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                          font-size:0.9375rem; color:var(--color-on-surface);"
                   onfocus="this.style.borderColor='var(--color-primary-container)'"
                   onblur="this.style.borderColor='var(--color-outline-variant)'">
            @error('prix') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
        </div>

        {{-- Description --}}
        <div style="grid-column:1 / -1;">
            <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Description (Optionnel)
            </label>
            <textarea name="description" rows="4"
                      style="width:100%; box-sizing:border-box; padding:0.75rem 1rem; border-radius:0.625rem;
                             border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                             font-size:0.9375rem; color:var(--color-on-surface); resize:vertical;"
                      onfocus="this.style.borderColor='var(--color-primary-container)'"
                      onblur="this.style.borderColor='var(--color-outline-variant)'">{{ old('description', $formation->description) }}</textarea>
            @error('description') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
        </div>

        {{-- Status --}}
        <div style="grid-column:1 / -1; display:flex; align-items:center; gap:0.5rem;">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $formation->is_active) ? 'checked' : '' }}
                   style="width:18px; height:18px; accent-color:var(--color-primary-container);">
            <label for="is_active" style="font-size:0.875rem; font-weight:500; color:var(--color-on-surface); cursor:pointer;">
                Formation active (visible)
            </label>
            @error('is_active') <span style="color:#dc2626; font-size:0.75rem; margin-left:1rem;">{{ $message }}</span> @enderror
        </div>
    </div>

    <div style="margin-top:2rem; display:flex; justify-content:flex-end; gap:1rem;">
        <a href="{{ route('admin.formations.index') }}"
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
