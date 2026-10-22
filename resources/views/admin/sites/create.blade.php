@extends('layouts.admin')

@section('title', 'Nouveau site')

@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.sites.index') }}"
       style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
              display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
              transition:all 0.15s;"
       onmouseover="this.style.background='var(--color-surface-container-high)'"
       onmouseout="this.style.background='var(--color-surface-container)'">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Nouveau site
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Ajouter un campus ou antenne IFAM
        </p>
    </div>
</div>

<form action="{{ route('admin.sites.store') }}" method="POST"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:2rem; max-width:600px;">
    @csrf

    <div style="display:grid; grid-template-columns:1fr; gap:1.5rem;">

        <div>
            <label for="site-nom" style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Nom du site <span style="color:#dc2626;">*</span>
            </label>
            <input type="text" id="site-nom" name="nom" value="{{ old('nom') }}" required
                   placeholder="ex: IFAM Cotonou, IFAM Porto-Novo…"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                          border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                          font-size:0.9375rem; color:var(--color-on-surface);"
                   onfocus="this.style.borderColor='var(--color-primary-container)'"
                   onblur="this.style.borderColor='var(--color-outline-variant)'">
            @error('nom') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="site-ville" style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Ville <span style="color:#dc2626;">*</span>
            </label>
            <input type="text" id="site-ville" name="ville" value="{{ old('ville') }}" required
                   placeholder="ex: Cotonou"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                          border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                          font-size:0.9375rem; color:var(--color-on-surface);"
                   onfocus="this.style.borderColor='var(--color-primary-container)'"
                   onblur="this.style.borderColor='var(--color-outline-variant)'">
            @error('ville') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="site-adresse" style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Adresse
            </label>
            <input type="text" id="site-adresse" name="adresse" value="{{ old('adresse') }}"
                   placeholder="ex: Haie Vive, Avenue du Gouverneur…"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                          border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                          font-size:0.9375rem; color:var(--color-on-surface);"
                   onfocus="this.style.borderColor='var(--color-primary-container)'"
                   onblur="this.style.borderColor='var(--color-outline-variant)'">
            @error('adresse') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
            <div>
                <label for="site-telephone" style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Téléphone
                </label>
                <input type="text" id="site-telephone" name="telephone" value="{{ old('telephone') }}"
                       placeholder="+229 XX XX XX XX"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);"
                       onfocus="this.style.borderColor='var(--color-primary-container)'"
                       onblur="this.style.borderColor='var(--color-outline-variant)'">
                @error('telephone') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="site-email" style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    E-mail
                </label>
                <input type="email" id="site-email" name="email" value="{{ old('email') }}"
                       placeholder="site@ifam.bj"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);"
                       onfocus="this.style.borderColor='var(--color-primary-container)'"
                       onblur="this.style.borderColor='var(--color-outline-variant)'">
                @error('email') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>

        <div>
            <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                <input type="checkbox" name="est_actif" value="1" {{ old('est_actif', true) ? 'checked' : '' }}
                       style="width:1.25rem; height:1.25rem; accent-color:var(--color-primary-container);">
                <span style="font-size:0.9375rem; color:var(--color-on-surface); font-weight:500;">
                    Site actif (disponible dans les listes déroulantes)
                </span>
            </label>
        </div>

    </div>

    <div style="margin-top:2rem; display:flex; justify-content:flex-end; gap:1rem;">
        <a href="{{ route('admin.sites.index') }}"
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
            <span class="material-symbols-outlined" style="font-size:20px;">add_circle</span>
            Créer le site
        </button>
    </div>
</form>

@endsection
