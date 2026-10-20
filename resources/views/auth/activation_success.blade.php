<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe enregistré — IFAM Connect</title>
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
            background: #FDF2F8;
            color: var(--color-pink);
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
        .subtitle {
            font-size: 14px;
            color: var(--color-text-muted);
            line-height: 1.6;
            margin-bottom: 32px;
        }
        .btn-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            height: 50px;
            background: var(--color-pink);
            color: #FFFFFF;
            text-decoration: none;
            border-radius: 9999px; /* Pill shape */
            font-size: 15px;
            font-weight: 700;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(230, 36, 122, 0.3);
        }
        .btn-pill:hover {
            background: var(--color-pink-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(230, 36, 122, 0.4);
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
                <span class="material-symbols-outlined" style="font-size:36px;">check_circle</span>
            </div>

            <h1 class="title">
                {{ $isReset ? 'Mot de passe réinitialisé !' : 'Mot de passe enregistré !' }}
            </h1>

            <p class="subtitle">
                Votre mot de passe a été défini avec succès. Vous pouvez dès à présent vous connecter pour accéder à votre espace personnalisé sur IFAM Connect.
            </p>

            <a href="{{ route('login') }}" class="btn-pill">
                <span>Accéder à la connexion</span>
                <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
            </a>
        </div>

        <!-- Footer -->
        <div class="footer-note">
            IFAM Connect · © {{ date('Y') }} Institut de Formation et d'Administration Moderne
        </div>
    </div>
</body>
</html>
