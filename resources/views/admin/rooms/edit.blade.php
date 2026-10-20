@extends('layouts.admin')

@section('title', 'Modifier une salle')

@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.rooms.index') }}"
       style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
              display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
              transition:all 0.15s;"
       onmouseover="this.style.background='var(--color-surface-container-high)'"
       onmouseout="this.style.background='var(--color-surface-container)'">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Modifier la salle
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            {{ $room->name }}
        </p>
    </div>
</div>

<form action="{{ route('admin.rooms.update', $room) }}" method="POST"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:2rem; max-width:600px;">
    @csrf
    @method('PUT')

    <div style="display:grid; grid-template-columns:1fr; gap:1.5rem;">
        
        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Nom de la salle <span style="color:#dc2626;">*</span>
            </label>
            <input type="text" name="name" value="{{ old('name', $room->name) }}" required
                   placeholder="ex: Salle 101, Amphi A..."
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                          border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                          font-size:0.9375rem; color:var(--color-on-surface);"
                   onfocus="this.style.borderColor='var(--color-primary-container)'"
                   onblur="this.style.borderColor='var(--color-outline-variant)'">
            @error('name') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Capacité (places) <span style="color:#dc2626;">*</span>
                </label>
                <input type="number" name="capacity" value="{{ old('capacity', $room->capacity) }}" required min="1"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);"
                       onfocus="this.style.borderColor='var(--color-primary-container)'"
                       onblur="this.style.borderColor='var(--color-outline-variant)'">
                @error('capacity') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Type de salle
                </label>
                <select name="type"
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                               border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                               font-size:0.9375rem; color:var(--color-on-surface);"
                        onfocus="this.style.borderColor='var(--color-primary-container)'"
                        onblur="this.style.borderColor='var(--color-outline-variant)'">
                    <option value="Théorie" {{ old('type', $room->type) == 'Théorie' ? 'selected' : '' }}>Théorie</option>
                    <option value="Informatique" {{ old('type', $room->type) == 'Informatique' ? 'selected' : '' }}>Informatique</option>
                    <option value="Laboratoire" {{ old('type', $room->type) == 'Laboratoire' ? 'selected' : '' }}>Laboratoire</option>
                    <option value="Atelier" {{ old('type', $room->type) == 'Atelier' ? 'selected' : '' }}>Atelier</option>
                </select>
                @error('type') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>

        <div>
            <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $room->is_active) ? 'checked' : '' }}
                       style="width:1.25rem; height:1.25rem; accent-color:var(--color-primary-container);">
                <span style="font-size:0.9375rem; color:var(--color-on-surface); font-weight:500;">
                    Salle active (disponible pour les emplois du temps)
                </span>
            </label>
        </div>

    </div>

    <div style="margin-top:2rem; display:flex; justify-content:flex-end; gap:1rem;">
        <a href="{{ route('admin.rooms.index') }}"
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
