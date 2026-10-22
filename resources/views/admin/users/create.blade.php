@extends('layouts.admin')

@section('title', 'Créer un utilisateur')

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
            Nouvel utilisateur
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Créez un compte utilisateur. Un email d'activation sécurisé sera automatiquement envoyé avec un lien valable 48 heures.
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.users.store') }}" id="user-create-form" novalidate enctype="multipart/form-data"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:2rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    @csrf

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:2rem;">

        {{-- Section Identité & Contact --}}
        <div style="grid-column:1 / -1; padding-bottom:1rem; border-bottom:1px solid var(--color-surface-container); margin-bottom:0.5rem;">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Identité & Contact</h2>
            <p style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin:0;">Informations personnelles requises.</p>
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Prénom <span style="color:var(--color-error);">*</span>
            </label>
            <input type="text" name="first_name" value="{{ old('first_name') }}" required
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('first_name') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);"
                   onfocus="this.style.borderColor='var(--color-primary-container)'"
                   onblur="this.style.borderColor='var(--color-outline-variant)'">
            @error('first_name')
            <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Nom de famille <span style="color:var(--color-error);">*</span>
            </label>
            <input type="text" name="last_name" value="{{ old('last_name') }}" required
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('last_name') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);"
                   onfocus="this.style.borderColor='var(--color-primary-container)'"
                   onblur="this.style.borderColor='var(--color-outline-variant)'">
            @error('last_name')
            <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email avec validation stricte --}}
        <div>
            <label for="email_input" style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Adresse email <span style="color:var(--color-error);">*</span>
            </label>
            <input type="email" name="email" id="email_input" value="{{ old('email') }}" required
                   placeholder="prenom.nom@ifam.bj"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('email') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);"
                   onfocus="this.style.borderColor='var(--color-primary-container)'"
                   onblur="validateEmailField(this)">
            <p id="email-client-error" style="display:none; margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);"></p>
            @error('email')
            <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p>
            @enderror
            <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-on-surface-variant);">
                L'email d'activation y sera automatiquement envoyé.
            </p>
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Téléphone
            </label>
            <input type="text" name="phone" value="{{ old('phone') }}"
                   placeholder="+229 01 XX XX XX XX"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('phone') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);"
                   onfocus="this.style.borderColor='var(--color-primary-container)'"
                   onblur="this.style.borderColor='var(--color-outline-variant)'">
            @error('phone')
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
                           background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface); cursor:pointer;">
                <option value="">Sélectionner</option>
                <option value="M" {{ old('gender') === 'M' ? 'selected' : '' }}>Masculin</option>
                <option value="F" {{ old('gender') === 'F' ? 'selected' : '' }}>Féminin</option>
                <option value="autre" {{ old('gender') === 'autre' ? 'selected' : '' }}>Autre</option>
            </select>
            @error('gender')
            <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Date de naissance
            </label>
            <input type="date" name="birth_date" value="{{ old('birth_date') }}"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('birth_date') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);"
                   onfocus="this.style.borderColor='var(--color-primary-container)'"
                   onblur="this.style.borderColor='var(--color-outline-variant)'">
            @error('birth_date')
            <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Photo de profil (Optionnelle)
            </label>
            <input type="file" name="photo" accept="image/*"
                   style="width:100%; box-sizing:border-box; padding:0.5rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('photo') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);"
                   onfocus="this.style.borderColor='var(--color-primary-container)'"
                   onblur="this.style.borderColor='var(--color-outline-variant)'">
            @error('photo')
            <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p>
            @enderror
        </div>

        {{-- Section Rôle & Activation --}}
        <div style="grid-column:1 / -1; padding-bottom:1rem; border-bottom:1px solid var(--color-surface-container); margin-top:1rem; margin-bottom:0.5rem;">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Rôle & Activation</h2>
            <p style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin:0;">Affectation du rôle métier (Étudiant, Enseignant, Direction ou Parent).</p>
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Rôle à attribuer <span style="color:var(--color-error);">*</span>
            </label>
            <select name="role" id="role_select" required
                    style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                           border:1.5px solid {{ $errors->has('role') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                           background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface); cursor:pointer;">
                <option value="">Sélectionner un rôle</option>
                @php
                    $roleLabels = [
                        'etudiant'   => 'Étudiant',
                        'enseignant' => 'Enseignant',
                        'direction'  => 'Direction',
                        'parent'     => 'Parent',
                    ];
                @endphp
                @foreach($roles as $role)
                <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
                    {{ $roleLabels[$role->name] ?? ucfirst($role->name) }}
                </option>
                @endforeach
            </select>
            <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-on-surface-variant);">
                (Pour un étudiant, un matricule officiel sera généré automatiquement).
            </p>
            @error('role')
            <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p>
            @enderror
        </div>

        {{-- Site de rattachement --}}
        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Site de rattachement <span style="color:var(--color-error);">*</span>
            </label>
            @if($isSuperAdmin)
                <select name="site_id" id="site_select" required
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                               border:1.5px solid {{ $errors->has('site_id') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                               background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface); cursor:pointer;">
                    <option value="">Sélectionner un site</option>
                    @foreach($sites as $site)
                    <option value="{{ $site->id }}" {{ (string) old('site_id', $defaultSiteId) === (string) $site->id ? 'selected' : '' }}>
                        {{ $site->nom }} ({{ $site->ville }})
                    </option>
                    @endforeach
                </select>
                <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-on-surface-variant);">
                    Sélectionnez le site principal de l'utilisateur.
                </p>
            @else
                @php $currentSite = $sites->first(); @endphp
                <div style="height:44px; padding:0 1rem; border-radius:0.75rem; border:1.5px solid var(--color-outline-variant); background:var(--color-surface-container-high); display:flex; align-items:center; font-size:0.875rem; color:var(--color-on-surface); font-weight:600;">
                    <span class="material-symbols-outlined" style="font-size:18px; color:var(--color-primary); margin-right:0.5rem;">apartment</span>
                    {{ $currentSite?->nom ?? 'Site assigné' }} (Imposé automatiquement)
                </div>
                <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-on-surface-variant);">
                    En tant qu'administrateur de site, les comptes créés sont obligatoirement rattachés à votre site.
                </p>
            @endif
            @error('site_id')
            <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p>
            @enderror
        </div>

        {{-- Encadré explicatif de l'activation automatique (sans mot de passe) --}}
        <div>
            <div style="background:#FDF2F8; border:1.5px dashed #F472B6; border-radius:0.75rem; padding:1rem 1.25rem;">
                <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem;">
                    <span class="material-symbols-outlined" style="font-size:20px; color:#E6247A;">mail_lock</span>
                    <strong style="font-size:0.8125rem; color:#9D174D;">Activation par email (48 heures)</strong>
                </div>
                <p style="font-size:0.8125rem; color:#831843; line-height:1.5; margin:0;">
                    Aucun mot de passe n'est défini à la création. Le système envoie immédiatement un email d'activation contenant un lien personnel valable 48h. L'utilisateur définira lui-même son mot de passe.
                </p>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div style="display:flex; justify-content:flex-end; gap:1rem; padding-top:1.5rem; border-top:1px solid var(--color-surface-container);">
        <a href="{{ route('admin.users.index') }}"
           style="height:44px; display:inline-flex; align-items:center; padding:0 1.5rem; border-radius:9999px;
                  background:var(--color-surface-container-high); color:var(--color-on-surface); text-decoration:none;
                  font-size:0.875rem; font-weight:600;">
            Annuler
        </a>
        <button type="submit" id="submit_btn"
                style="height:44px; padding:0 1.75rem; border-radius:9999px; border:none; cursor:pointer;
                       background:#E6247A; color:#FFFFFF;
                       font-size:0.875rem; font-weight:700; box-shadow:0 3px 12px rgba(230,36,122,0.35);
                       display:inline-flex; align-items:center; gap:0.5rem; transition:all 0.2s;"
                onmouseover="this.style.background='#C01B63'"
                onmouseout="this.style.background='#E6247A'">
            <span class="material-symbols-outlined" style="font-size:18px;">send</span>
            Créer et envoyer l'invitation
        </button>
    </div>
</form>

<script>
function validateEmail(email) {
    // Regex RFC-compliant pour format email
    const re = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/;
    return re.test(String(email).trim());
}

function validateEmailField(input) {
    const errorEl = document.getElementById('email-client-error');
    const value = input.value.trim();

    if (!value) {
        input.style.borderColor = 'var(--color-error)';
        errorEl.textContent = "L'adresse email est obligatoire.";
        errorEl.style.display = 'block';
        return false;
    } else if (!validateEmail(value)) {
        input.style.borderColor = 'var(--color-error)';
        errorEl.textContent = "Veuillez saisir une adresse email valide (ex. nom@domaine.com).";
        errorEl.style.display = 'block';
        return false;
    } else {
        input.style.borderColor = 'var(--color-outline-variant)';
        errorEl.style.display = 'none';
        return true;
    }
}

document.getElementById('user-create-form').addEventListener('submit', function(e) {
    const emailInput = document.getElementById('email_input');
    const isEmailValid = validateEmailField(emailInput);
    const roleSelect = document.getElementById('role_select');

    if (!isEmailValid) {
        e.preventDefault();
        emailInput.focus();
        return false;
    }

    if (!roleSelect.value) {
        e.preventDefault();
        roleSelect.focus();
        return false;
    }
});
</script>

@endsection
