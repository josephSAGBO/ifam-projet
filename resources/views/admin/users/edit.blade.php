@extends('layouts.admin')

@section('title', 'Modifier un utilisateur')

@section('content')

{{-- En-tête --}}
<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.users.index') }}"
       style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
              display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
              transition:all 0.15s;"
       onmouseover="this.style.background='var(--color-surface-container-high)'"
       onmouseout="this.style.background='var(--color-surface-container)'">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Modifier {{ $user->full_name }}
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Mise à jour des informations du compte.
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:2rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    @csrf
    @method('PUT')

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:2rem;">

        {{-- Section Identité --}}
        <div style="grid-column:1 / -1; padding-bottom:1rem; border-bottom:1px solid var(--color-surface-container); margin-bottom:0.5rem;">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Identité & Contact</h2>
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Prénom <span style="color:var(--color-error);">*</span>
            </label>
            <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('first_name') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
            @error('first_name')
            <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Nom de famille <span style="color:var(--color-error);">*</span>
            </label>
            <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('last_name') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
            @error('last_name')
            <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Genre
            </label>
            <select name="gender"
                    style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                           border:1.5px solid {{ $errors->has('gender') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                           background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
                <option value="">Sélectionner</option>
                <option value="M" {{ old('gender', $user->gender) === 'M' ? 'selected' : '' }}>Masculin</option>
                <option value="F" {{ old('gender', $user->gender) === 'F' ? 'selected' : '' }}>Féminin</option>
                <option value="autre" {{ old('gender', $user->gender) === 'autre' ? 'selected' : '' }}>Autre</option>
            </select>
            @error('gender')
            <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Date de naissance
            </label>
            <input type="date" name="birth_date" value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('birth_date') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
            @error('birth_date')
            <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Email
            </label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('email') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
            @error('email')
            <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Téléphone
            </label>
            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('phone') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
            @error('phone')
            <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p>
            @enderror
        </div>

        <div style="grid-column:1 / -1;">
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.75rem;">
                Photo de profil
            </label>
            <div style="display:flex; align-items:center; gap:1.5rem; flex-wrap:wrap;">
                {{-- Aperçu actuel --}}
                @if($user->photo_path)
                <div style="position:relative;">
                    <img src="{{ Storage::url($user->photo_path) }}" alt="Photo de {{ $user->full_name }}"
                         style="width:80px; height:80px; border-radius:50%; object-fit:cover; border:3px solid var(--color-outline-variant);">
                    <span style="position:absolute; bottom:-6px; left:50%; transform:translateX(-50%); font-size:0.65rem; color:var(--color-on-surface-variant); white-space:nowrap;">Photo actuelle</span>
                </div>
                @else
                <div style="width:80px; height:80px; border-radius:50%; background:var(--color-primary-container); display:flex; align-items:center; justify-content:center;">
                    <span style="font-size:1.75rem; font-weight:700; color:var(--color-on-primary);">{{ $user->initials }}</span>
                </div>
                @endif
                {{-- Input --}}
                <div style="flex:1; min-width:200px;">
                    <input type="file" name="photo" accept="image/*"
                           style="width:100%; box-sizing:border-box; padding:0.5rem; border-radius:0.75rem;
                                  border:1.5px solid {{ $errors->has('photo') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                                  background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
                    <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-on-surface-variant);">Formats acceptés : JPG, PNG, GIF. Taille max : 2 Mo.</p>
                    @error('photo')
                    <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Section Compte & Sécurité --}}
        <div style="grid-column:1 / -1; padding-bottom:1rem; border-bottom:1px solid var(--color-surface-container); margin-top:1rem; margin-bottom:0.5rem;">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Compte & Sécurité</h2>
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Statut du compte
            </label>
            <select name="is_active"
                    style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                           border:1.5px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
                <option value="1" {{ old('is_active', $user->is_active) == 1 ? 'selected' : '' }}>Actif</option>
                <option value="0" {{ old('is_active', $user->is_active) == 0 ? 'selected' : '' }}>Inactif (Désactivé)</option>
            </select>
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Nouveau mot de passe <span style="font-weight:400; color:var(--color-on-surface-variant);">(Optionnel)</span>
            </label>
            <input type="text" name="new_password" placeholder="Laisser vide pour ne pas changer"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('new_password') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
            <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-on-surface-variant);">
                Si renseigné, l'utilisateur devra le changer à sa prochaine connexion.
            </p>
            @error('new_password')
            <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p>
            @enderror
        </div>

        {{-- Site de rattachement --}}
        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Site de rattachement
            </label>
            @if($isSuperAdmin)
                @php $currentSiteId = $user->sites->first()?->id; @endphp
                <select name="site_id" required
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                               border:1.5px solid {{ $errors->has('site_id') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                               background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface); cursor:pointer;">
                    <option value="">Sélectionner un site</option>
                    @foreach($sites as $site)
                    <option value="{{ $site->id }}" {{ (string) old('site_id', $currentSiteId) === (string) $site->id ? 'selected' : '' }}>
                        {{ $site->nom }} ({{ $site->ville }})
                    </option>
                    @endforeach
                </select>
                @error('site_id')
                <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p>
                @enderror
            @else
                <div style="height:44px; padding:0 1rem; border-radius:0.75rem; border:1.5px solid var(--color-outline-variant); background:var(--color-surface-container-high); display:flex; align-items:center; font-size:0.875rem; color:var(--color-on-surface); font-weight:600;">
                    <span class="material-symbols-outlined" style="font-size:18px; color:var(--color-primary); margin-right:0.5rem;">apartment</span>
                    {{ $user->sites->pluck('nom')->join(', ') ?: 'Site local' }}
                </div>
                <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-on-surface-variant);">
                    Seul le SuperAdmin peut modifier le site de rattachement.
                </p>
            @endif
        </div>

    </div>

    {{-- Actions --}}
    <div style="display:flex; justify-content:flex-end; gap:1rem; padding-top:1.5rem; border-top:1px solid var(--color-surface-container);">
        <a href="{{ route('admin.users.index') }}"
           style="height:44px; display:inline-flex; align-items:center; padding:0 1.5rem; border-radius:0.75rem;
                  background:var(--color-surface-container-high); color:var(--color-on-surface); text-decoration:none;
                  font-size:0.875rem; font-weight:600;">
            Annuler
        </a>
        <button type="submit"
                style="height:44px; padding:0 1.5rem; border-radius:0.75rem; border:none; cursor:pointer;
                       background:var(--color-primary-container); color:var(--color-on-primary);
                       font-size:0.875rem; font-weight:700; box-shadow:0 3px 10px rgba(228,0,102,0.3);">
            Mettre à jour
        </button>
    </div>
</form>

@endsection
