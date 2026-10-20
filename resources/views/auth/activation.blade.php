<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isReset ? 'Réinitialiser mon mot de passe' : 'Définir mon mot de passe' }} — IFAM Connect</title>
    <meta name="description" content="Définition de mot de passe IFAM Connect">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <style>
        :root {
            --font-sans: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            --color-bg: #FAFAFA;
            --color-card: #FFFFFF;
            --color-text: #111111;
            --color-text-muted: #666666;
            --color-pink: #E6247A;
            --color-pink-hover: #C01B63;
            --color-pink-subtle: #FDF2F8;
            --color-border: #E5E7EB;
            --color-border-focus: #E6247A;
            --color-error: #DC2626;
            --color-error-bg: #FEF2F2;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--font-sans);
            background-color: var(--color-bg);
            color: var(--color-text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            width: 100%;
            max-width: 440px;
        }
        .header {
            text-align: center;
            margin-bottom: 28px;
        }
        .logo {
            font-size: 34px;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--color-text);
            line-height: 1;
        }
        .logo span {
            color: var(--color-pink);
        }
        .tagline {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            color: var(--color-pink);
            margin-top: 8px;
        }
        .card {
            background: var(--color-card);
            border-radius: 20px;
            padding: 36px 32px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--color-border);
        }
        .title {
            font-size: 22px;
            font-weight: 800;
            color: var(--color-text);
            letter-spacing: -0.02em;
            margin-bottom: 8px;
            text-align: center;
        }
        .subtitle {
            font-size: 14px;
            color: var(--color-text-muted);
            line-height: 1.5;
            text-align: center;
            margin-bottom: 28px;
        }
        .user-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: var(--color-pink-subtle);
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
            color: var(--color-pink);
            margin-bottom: 16px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 8px;
        }
        .input-wrapper {
            position: relative;
        }
        .form-input {
            width: 100%;
            height: 48px;
            padding: 0 44px 0 14px;
            border-radius: 12px;
            border: 1.5px solid var(--color-border);
            font-family: var(--font-sans);
            font-size: 14px;
            color: var(--color-text);
            background: #FFFFFF;
            transition: all 0.2s ease;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--color-border-focus);
            box-shadow: 0 0 0 3px rgba(230, 36, 122, 0.12);
        }
        .form-input.is-error {
            border-color: var(--color-error);
            background-color: #FFFDFD;
        }
        .toggle-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #888888;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
        }
        .toggle-btn:hover {
            color: var(--color-text);
        }
        .error-message {
            font-size: 12px;
            color: var(--color-error);
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .btn-submit {
            width: 100%;
            height: 50px;
            background: var(--color-pink);
            color: #FFFFFF;
            border: none;
            border-radius: 9999px; /* Pill shape */
            font-family: var(--font-sans);
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(230, 36, 122, 0.3);
            margin-top: 10px;
        }
        .btn-submit:hover {
            background: var(--color-pink-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(230, 36, 122, 0.4);
        }
        .btn-submit:active {
            transform: translateY(0);
        }
        .password-hints {
            font-size: 12px;
            color: #777777;
            margin-top: 6px;
            line-height: 1.4;
        }
        .footer-note {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: var(--color-text-muted);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <img src="{{ asset('img/logo.jpg') }}" alt="Logo IFAM" style="height: 64px; width: auto; margin: 0 auto; display: block; border-radius: 8px;">
            <div class="tagline">L'excellence au cœur de la formation</div>
        </div>

        <!-- Card -->
        <div class="card">
            <div style="text-align:center;">
                <div class="user-badge">
                    <span class="material-symbols-outlined" style="font-size:16px;">account_circle</span>
                    {{ $user->email }}
                </div>
            </div>

            <h1 class="title">
                {{ $isReset ? 'Réinitialiser mon mot de passe' : 'Définissez votre mot de passe' }}
            </h1>

            <p class="subtitle">
                {{ $isReset 
                    ? 'Choisissez un nouveau mot de passe sécurisé pour réaccéder à votre compte IFAM Connect.' 
                    : 'Pour finaliser l\'activation de votre compte IFAM, veuillez choisir un mot de passe sécurisé.' }}
            </p>

            <form method="POST" action="{{ $submitRoute }}" novalidate>
                @csrf

                <!-- Nouveau mot de passe -->
                <div class="form-group">
                    <label class="form-label" for="password">Nouveau mot de passe</label>
                    <div class="input-wrapper">
                        <input type="password"
                               id="password"
                               name="password"
                               required
                               minlength="8"
                               placeholder="Minimum 8 caractères"
                               class="form-input {{ $errors->has('password') ? 'is-error' : '' }}">
                        <button type="button" class="toggle-btn" onclick="toggleVisibility('password', 'icon-pwd')">
                            <span class="material-symbols-outlined" id="icon-pwd" style="font-size:20px;">visibility</span>
                        </button>
                    </div>
                    @error('password')
                    <div class="error-message">
                        <span class="material-symbols-outlined" style="font-size:14px;">error</span>
                        {{ $message }}
                    </div>
                    @else
                    <div class="password-hints">
                        Le mot de passe doit comporter au moins 8 caractères.
                    </div>
                    @enderror
                </div>

                <!-- Confirmer mot de passe -->
                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Confirmer le mot de passe</label>
                    <div class="input-wrapper">
                        <input type="password"
                               id="password_confirmation"
                               name="password_confirmation"
                               required
                               placeholder="Retapez votre mot de passe"
                               class="form-input">
                        <button type="button" class="toggle-btn" onclick="toggleVisibility('password_confirmation', 'icon-pwd-confirm')">
                            <span class="material-symbols-outlined" id="icon-pwd-confirm" style="font-size:20px;">visibility</span>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit">
                    {{ $isReset ? 'Réinitialiser mon mot de passe' : 'Définir mon mot de passe' }}
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="footer-note">
            IFAM Connect · © {{ date('Y') }} Institut de Formation et d'Administration Moderne
        </div>
    </div>

    <script>
        function toggleVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
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
