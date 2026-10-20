<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié — IFAM Connect</title>
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
            line-height: 1.55;
            text-align: center;
            margin-bottom: 24px;
        }
        .status-box {
            background: #F0FDF4;
            border: 1px solid #BBF7D0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .status-text {
            font-size: 13.5px;
            line-height: 1.5;
            color: #166534;
            font-weight: 500;
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
        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            font-size: 20px;
            pointer-events: none;
        }
        .form-input {
            width: 100%;
            height: 48px;
            padding: 0 14px 0 44px;
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-submit:hover {
            background: var(--color-pink-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(230, 36, 122, 0.4);
        }
        .btn-submit:active {
            transform: translateY(0);
        }
        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 20px;
            font-size: 13.5px;
            font-weight: 600;
            color: var(--color-text-muted);
            text-decoration: none;
            transition: color 0.15s;
        }
        .back-link:hover {
            color: var(--color-pink);
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
            <h1 class="title">Mot de passe oublié ?</h1>
            <p class="subtitle">
                Saisissez l'adresse email associée à votre compte IFAM pour recevoir un lien personnel de réinitialisation.
            </p>

            @if(session('status'))
            <!-- Neutral success message -->
            <div class="status-box">
                <span class="material-symbols-outlined" style="font-size:20px; color:#16a34a; flex-shrink:0;">check_circle</span>
                <div class="status-text">
                    {{ session('status') }}
                </div>
            </div>

            <a href="{{ route('login') }}" class="btn-submit" style="text-decoration:none;">
                <span>Retour à la connexion</span>
                <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
            </a>
            @else
            <form method="POST" action="{{ route('password.email') }}" id="forgot-form" novalidate>
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">Adresse email</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">mail</span>
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               placeholder="votre.email@ifam.bj"
                               class="form-input {{ $errors->has('email') ? 'is-error' : '' }}">
                    </div>
                    @error('email')
                    <div class="error-message">
                        <span class="material-symbols-outlined" style="font-size:14px;">error</span>
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <button type="submit" class="btn-submit">
                    <span>Envoyer le lien de réinitialisation</span>
                    <span class="material-symbols-outlined" style="font-size:18px;">send</span>
                </button>

                <a href="{{ route('login') }}" class="back-link">
                    <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
                    <span>Retour à la connexion</span>
                </a>
            </form>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer-note">
            IFAM Connect · © {{ date('Y') }} Institut de Formation et d'Administration Moderne
        </div>
    </div>
</body>
</html>
