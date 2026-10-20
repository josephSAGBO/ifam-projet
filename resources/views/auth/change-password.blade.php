<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IFAM Connect — Changement de mot de passe</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
</head>
<body style="min-height:100vh; background:var(--color-background); display:flex; align-items:center; justify-content:center; padding:1.5rem; font-family:var(--font-sans);">

    <div style="width:100%; max-width:440px;">

        {{-- Logo --}}
        <div style="text-align:center; margin-bottom:2.5rem;">
            <div style="display:inline-flex; align-items:center; gap:0.75rem;">
                <div>
                    <div style="font-family:var(--font-display); font-weight:700; font-size:1.5rem; color:var(--color-on-surface); letter-spacing:-0.02em; line-height:1;">
                        IFAM<span style="color:var(--color-primary-container);">.</span>
                    </div>
                    <div style="font-size:0.6875rem; font-weight:700; letter-spacing:0.15em; color:var(--color-primary-container); text-transform:uppercase;">
                        CONNECT
                    </div>
                </div>
            </div>
        </div>

        <div style="background:var(--color-surface-container-lowest); border-radius:1.25rem; padding:2rem; box-shadow:0 4px 24px rgba(11,28,48,0.08);">

            {{-- Icône alerte --}}
            <div style="display:flex; align-items:center; gap:0.875rem; padding:1rem; background:var(--color-secondary-container)1a; border:1px solid var(--color-secondary-container); border-radius:0.75rem; margin-bottom:1.75rem;">
                <span class="material-symbols-outlined" style="font-size:24px; color:var(--color-secondary); flex-shrink:0;">lock_reset</span>
                <div>
                    <div style="font-size:0.875rem; font-weight:700; color:var(--color-on-surface);">Changement de mot de passe requis</div>
                    <div style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin-top:0.125rem;">
                        Pour des raisons de sécurité, veuillez définir un nouveau mot de passe personnel.
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('password.change.update') }}" novalidate>
                @csrf

                {{-- Mot de passe actuel --}}
                <div style="margin-bottom:1.25rem;">
                    <label for="current_password" style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                        Mot de passe actuel (temporaire)
                    </label>
                    <input type="password" id="current_password" name="current_password" required
                           style="width:100%; box-sizing:border-box; height:48px; padding:0 1rem; border-radius:0.75rem;
                                  border:1.5px solid {{ $errors->has('current_password') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                                  background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);"
                           onfocus="this.style.borderColor='var(--color-primary-container)'"
                           onblur="this.style.borderColor='var(--color-outline-variant)'">
                    @error('current_password')
                    <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nouveau mot de passe --}}
                <div style="margin-bottom:1.25rem;">
                    <label for="password" style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                        Nouveau mot de passe
                        <span style="font-size:0.6875rem; font-weight:400; color:var(--color-on-surface-variant);">— 8 caractères minimum</span>
                    </label>
                    <input type="password" id="password" name="password" required minlength="8"
                           style="width:100%; box-sizing:border-box; height:48px; padding:0 1rem; border-radius:0.75rem;
                                  border:1.5px solid {{ $errors->has('password') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                                  background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);"
                           onfocus="this.style.borderColor='var(--color-primary-container)'"
                           onblur="this.style.borderColor='var(--color-outline-variant)'">
                    @error('password')
                    <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirmation --}}
                <div style="margin-bottom:1.75rem;">
                    <label for="password_confirmation" style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                        Confirmer le nouveau mot de passe
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           style="width:100%; box-sizing:border-box; height:48px; padding:0 1rem; border-radius:0.75rem;
                                  border:1.5px solid var(--color-outline-variant);
                                  background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);"
                           onfocus="this.style.borderColor='var(--color-primary-container)'"
                           onblur="this.style.borderColor='var(--color-outline-variant)'">
                </div>

                <button type="submit"
                        style="width:100%; height:48px; border-radius:0.75rem; border:none; cursor:pointer;
                               background:var(--color-primary-container); color:var(--color-on-primary);
                               font-family:var(--font-sans); font-size:0.9375rem; font-weight:700;
                               box-shadow:0 4px 12px rgba(228,0,102,0.35);">
                    Définir mon mot de passe
                </button>
            </form>
        </div>
    </div>
</body>
</html>
