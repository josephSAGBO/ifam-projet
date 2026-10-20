<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IFAM Connect — Connexion</title>
    <meta name="description" content="Connexion à la plateforme IFAM Connect">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
</head>
<body style="min-height:100vh; background:var(--color-background); display:flex; align-items:center; justify-content:center; padding:1.5rem; font-family:var(--font-sans);">

    <div style="width:100%; max-width:420px;">

        {{-- Logo --}}
        <div style="text-align:center; margin-bottom:2.5rem;">
            <div style="display:inline-flex; align-items:center; gap:0.75rem;">
                <img src="{{ asset('img/logo.jpg') }}"
                     alt="Logo IFAM"
                     style="height:52px; width:auto; object-fit:contain;">
                <div>
                    {{-- <div style="font-family:var(--font-display); font-weight:700; font-size:1.5rem; color:var(--color-on-surface); letter-spacing:-0.02em; line-height:1;">
                        IFAM<span style="color:var(--color-primary-container);">.</span>
                    </div>
                    <div style="font-size:0.6875rem; font-weight:700; letter-spacing:0.15em; color:var(--color-primary-container); text-transform:uppercase;">
                        CONNECT
                    </div> --}}
                </div>
            </div>
            <p style="margin-top:1rem; font-size:0.875rem; color:var(--color-on-surface-variant);">
                Plateforme de gestion de l'Institut IFAM
            </p>
        </div>

        {{-- Carte de connexion --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1.25rem; padding:2rem; box-shadow:0 4px 24px rgba(11,28,48,0.08);">

            <h1 style="font-family:var(--font-display); font-size:1.25rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.375rem;">
                Connexion
            </h1>
            <p style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin:0 0 1.75rem;">
                Entrez votre identifiant et votre mot de passe
            </p>

            {{-- Flash success ou status --}}
            @if(session('success') || session('status'))
            <div style="padding:0.75rem 1rem; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:0.75rem; color:#065f46; font-size:0.8125rem; font-weight:500; margin-bottom:1.25rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="font-size:18px; color:#059669; flex-shrink:0;">check_circle</span>
                {{ session('success') ?? session('status') }}
            </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" novalidate>
                @csrf

                {{-- Identifiant --}}
                <div style="margin-bottom:1.25rem;">
                    <label for="email" style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                        Identifiant
                        <span style="font-size:0.6875rem; font-weight:400; color:var(--color-on-surface-variant);"> — email ou matricule</span>
                    </label>
                    <div style="position:relative;">
                        <span class="material-symbols-outlined"
                              style="position:absolute; left:0.875rem; top:50%; transform:translateY(-50%); color:var(--color-on-surface-variant); font-size:20px; pointer-events:none;">
                            person
                        </span>
                        <input type="text"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               autocomplete="username"
                               required
                               placeholder="votre email valide"
                               style="width:100%; box-sizing:border-box; height:48px; padding:0 1rem 0 2.75rem; border-radius:0.75rem;
                                      border:1.5px solid {{ $errors->has('email') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                                      background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);
                                      transition:border-color 0.15s;"
                               onfocus="this.style.borderColor='var(--color-primary-container)'"
                               onblur="this.style.borderColor='{{ $errors->has('email') ? 'var(--color-error)' : 'var(--color-outline-variant)' }}'">
                    </div>
                    @error('email')
                    <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error); display:flex; align-items:center; gap:0.25rem;">
                        <span class="material-symbols-outlined" style="font-size:14px;">error</span>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                {{-- Mot de passe --}}
                <div style="margin-bottom:1.5rem;">
                    <label for="password" style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                        Mot de passe
                    </label>
                    <div style="position:relative;">
                        <span class="material-symbols-outlined"
                              style="position:absolute; left:0.875rem; top:50%; transform:translateY(-50%); color:var(--color-on-surface-variant); font-size:20px; pointer-events:none;">
                            lock
                        </span>
                        <input type="password"
                               id="password"
                               name="password"
                               autocomplete="current-password"
                               required
                               placeholder="Votre mot de passe"
                               style="width:100%; box-sizing:border-box; height:48px; padding:0 2.75rem 0 2.75rem; border-radius:0.75rem;
                                      border:1.5px solid {{ $errors->has('password') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                                      background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);
                                      transition:border-color 0.15s;"
                               onfocus="this.style.borderColor='var(--color-primary-container)'"
                               onblur="this.style.borderColor='{{ $errors->has('password') ? 'var(--color-error)' : 'var(--color-outline-variant)' }}'">
                        <button type="button"
                                id="toggle-password"
                                onclick="togglePassword()"
                                style="position:absolute; right:0.875rem; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:var(--color-on-surface-variant); display:flex; align-items:center; padding:0;">
                            <span class="material-symbols-outlined" id="pw-icon" style="font-size:20px;">visibility</span>
                        </button>
                    </div>
                    @error('password')
                    <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error); display:flex; align-items:center; gap:0.25rem;">
                        <span class="material-symbols-outlined" style="font-size:14px;">error</span>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                {{-- Se souvenir de moi --}}
                <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:1.5rem;">
                    <input type="checkbox" id="remember" name="remember"
                           style="width:16px; height:16px; accent-color:var(--color-primary-container); cursor:pointer;">
                    <label for="remember" style="font-size:0.8125rem; color:var(--color-on-surface-variant); cursor:pointer;">
                        Rester connecté
                    </label>
                </div>

                {{-- Bouton de connexion --}}
                <button type="submit"
                        style="width:100%; height:48px; border-radius:0.75rem; border:none; cursor:pointer;
                               background:var(--color-primary-container); color:var(--color-on-primary);
                               font-family:var(--font-sans); font-size:0.9375rem; font-weight:700; letter-spacing:0.01em;
                               box-shadow:0 4px 12px rgba(228,0,102,0.35); transition:all 0.2s;"
                        onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 16px rgba(228,0,102,0.45)';"
                        onmouseout="this.style.transform=''; this.style.boxShadow='0 4px 12px rgba(228,0,102,0.35)';">
                    Se connecter
                </button>

                {{-- Lien Mot de passe oublié --}}
                <div style="text-align:center; margin-top:1.25rem;">
                    <a href="{{ route('password.request') }}"
                       style="font-size:0.8125rem; font-weight:600; color:var(--color-primary-container); text-decoration:none; transition:all 0.15s;"
                       onmouseover="this.style.textDecoration='underline'"
                       onmouseout="this.style.textDecoration='none'">
                        Mot de passe oublié ?
                    </a>
                </div>
            </form>
        </div>

        {{-- Footer --}}
        <p style="text-align:center; margin-top:2rem; font-size:0.75rem; color:var(--color-on-surface-variant);">
            IFAM Connect v1.0 · © {{ date('Y') }} Institut IFAM
        </p>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon  = document.getElementById('pw-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility';
            }
        }
    </script>
</body>
</html>
