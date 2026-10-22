@extends('layouts.admin')

@section('title', 'Modifier le service : ' . $service->name)

@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.services.index') }}"
       style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
              display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
              transition:all 0.15s;"
       onmouseover="this.style.background='var(--color-surface-container-high)'"
       onmouseout="this.style.background='var(--color-surface-container)'">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Modifier le service
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            {{ $service->name }} ({{ $service->code }})
        </p>
    </div>
</div>

<form action="{{ route('admin.services.update', $service) }}" method="POST"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:2rem; max-width:640px;">
    @csrf
    @method('PUT')

    <div style="display:grid; grid-template-columns:1fr; gap:1.5rem;">

        <div>
            <label for="service-department" style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Département de rattachement <span style="color:#dc2626;">*</span>
            </label>
            <select id="service-department" name="department_id" required
                    style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                           border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                           font-size:0.9375rem; color:var(--color-on-surface);"
                    onfocus="this.style.borderColor='var(--color-primary-container)'"
                    onblur="this.style.borderColor='var(--color-outline-variant)'">
                <option value="">Sélectionner un département...</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ (old('department_id', $service->department_id) == $dept->id) ? 'selected' : '' }}>
                    {{ $dept->name }}
                </option>
                @endforeach
            </select>
            @error('department_id') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="service-name" style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Nom du service <span style="color:#dc2626;">*</span>
            </label>
            <input type="text" id="service-name" name="name" value="{{ old('name', $service->name) }}" required
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                          border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                          font-size:0.9375rem; color:var(--color-on-surface);"
                   onfocus="this.style.borderColor='var(--color-primary-container)'"
                   onblur="this.style.borderColor='var(--color-outline-variant)'">
            @error('name') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="service-code" style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Code technique du service
            </label>
            <input type="text" id="service-code" name="code" value="{{ old('code', $service->code) }}"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                          border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                          font-size:0.9375rem; color:var(--color-on-surface); font-family:monospace;"
                   onfocus="this.style.borderColor='var(--color-primary-container)'"
                   onblur="this.style.borderColor='var(--color-outline-variant)'">
            @error('code') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="service-description" style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Description / Fonctions
            </label>
            <textarea id="service-description" name="description" rows="3"
                      style="width:100%; box-sizing:border-box; padding:0.75rem 1rem; border-radius:0.625rem;
                             border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                             font-size:0.9375rem; color:var(--color-on-surface); resize:vertical;"
                      onfocus="this.style.borderColor='var(--color-primary-container)'"
                      onblur="this.style.borderColor='var(--color-outline-variant)'">{{ old('description', $service->description) }}</textarea>
            @error('description') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
        </div>

        <div>
            <label style="display:flex; align-items:center; gap:0.625rem; cursor:pointer;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $service->is_active) ? 'checked' : '' }}
                       style="width:18px; height:18px; accent-color:var(--color-primary-container);">
                <span style="font-size:0.875rem; font-weight:600; color:var(--color-on-surface);">Service actif</span>
            </label>
        </div>

    </div>

    <div style="display:flex; gap:1rem; margin-top:2rem;">
        <button type="submit"
                style="padding:0.625rem 1.5rem; background:var(--color-primary-container); color:var(--color-on-primary);
                       border-radius:0.625rem; border:none; font-size:0.875rem; font-weight:700; cursor:pointer;
                       box-shadow:0 2px 8px rgba(228,0,102,0.25); transition:all 0.15s;"
                onmouseover="this.style.opacity='0.9'"
                onmouseout="this.style.opacity='1'">
            Enregistrer les modifications
        </button>
        <a href="{{ route('admin.services.index') }}"
           style="padding:0.625rem 1.25rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant);
                  background:var(--color-surface-container); color:var(--color-on-surface); text-decoration:none;
                  font-size:0.875rem; font-weight:600; display:inline-flex; align-items:center;">
            Annuler
        </a>
    </div>

</form>

@endsection
