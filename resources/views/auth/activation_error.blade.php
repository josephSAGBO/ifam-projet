<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Lien expiré ou déjà utilisé' }} — IFAM Connect</title>
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
            padding: 40px 32px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--color-border);
            text-align: center;
        }
        .icon-circle {
            width: 64px;
            height: 64px;
            border-radius: 9999px;
            background: #FEF2F2;
            color: #DC2626;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .title {
            font-size: 22px;
            font-weight: 800;
            color: var(--color-text);
            letter-spacing: -0.02em;
            margin-bottom: 12px;
        }
        .message-box {
            background: #F9FAFB;
            border: 1px solid var(--color-border);
            border-radius: 12px;
            padding: 16px;
            font-size: 14px;
            color: #374151;
            line-height: 1.55;
            margin-bottom: 20px;
        }
        .instruction {
            font-size: 13.5px;
            color: var(--color-text-muted);
            line-height: 1.55;
            margin-bottom: 28px;
        }
        .btn-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            height: 48px;
            background: var(--color-pink);
            color: #FFFFFF;
            text-decoration: none;
            border-radius: 9999px; /* Pill shape */
            font-size: 14.5px;
            font-weight: 700;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(230, 36, 122, 0.3);
        }
        .btn-pill:hover {
            background: var(--color-pink-hover);
            transform: translateY(-1px);
        }
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: 12px;
            font-size: 13px;
            font-weight: 600;
            color: var(--color-text-muted);
            text-decoration: none;
            transition: color 0.15s;
        }
        .btn-secondary:hover {
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
            <div class="icon-circle">
                <span class="material-symbols-outlined" style="font-size:34px;">link_off</span>
            </div>

            <h1 class="title">
                {{ $title ?? 'Lien expiré ou déjà utilisé' }}
            </h1>

            <div class="message-box">
                {{ $message ?? 'Ce lien n\'est plus valide ou a déjà été utilisé pour définir un mot de passe.' }}
            </div>

            <p class="instruction">
                Pour des raisons de sécurité, les liens d'accès sont à usage unique et valables 48 heures.<br><br>
                <strong>Veuillez contacter l'administration de l'IFAM</strong> ou faire une demande de réinitialisation pour obtenir un nouveau lien sécurisé.
            </p>

            <a href="{{ route('login') }}" class="btn-pill">
                <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
                <span>Retour à la page de connexion</span>
            </a>

            @if(!empty($isReset))
            <a href="{{ route('password.request') }}" class="btn-secondary">
                Faire une nouvelle demande de mot de passe
            </a>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer-note">
            IFAM Connect · © {{ date('Y') }} Institut de Formation et d'Administration Moderne
        </div>
    </div>
</body>
</html>
