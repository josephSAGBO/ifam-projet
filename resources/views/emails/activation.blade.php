<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue à l'IFAM — Activez votre compte</title>
</head>
<body style="margin:0; padding:30px 15px; background-color:#F4F5F7; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing:antialiased; color:#111111;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px; margin:0 auto; background-color:#FFFFFF; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.06); border:1px solid #EDEDED;">
        <!-- Header -->
        <tr>
            <td style="padding:32px 40px 20px; text-align:center;">
                <img src="{{ rtrim(config('app.url'), '/') }}/img/logo.jpg"
                     alt="Logo IFAM"
                     width="120"
                     style="height:auto; max-height:60px; object-fit:contain; display:inline-block;">
                <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.18em; color:#E6247A; margin-top:10px;">
                    L'excellence au cœur de la formation
                </div>
            </td>
        </tr>

        <!-- Divider line -->
        <tr>
            <td style="padding:0 40px;">
                <div style="height:1px; background:#F0F0F0; width:100%;"></div>
            </td>
        </tr>

        <!-- Content -->
        <tr>
            <td style="padding:32px 40px 20px; text-align:center;">
                <h1 style="font-size:24px; font-weight:800; color:#111111; margin:0 0 16px; letter-spacing:-0.01em;">
                    Bienvenue à l'IFAM
                </h1>

                <p style="font-size:15px; line-height:1.65; color:#333333; margin:0 0 28px;">
                    Bonjour <strong>{{ $user->full_name }}</strong>,<br>
                    Vous avez été enregistré en tant que <strong style="color:#E6247A;">{{ $roleLabel ?? 'utilisateur' }}</strong> à l'école IFAM. Pour accéder à votre espace, veuillez définir votre mot de passe.
                </p>

                <!-- Button -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin:0 auto 28px;">
                    <tr>
                        <td align="center" style="border-radius:9999px; background-color:#E6247A;">
                            <a href="{{ $activationUrl }}" target="_blank" style="display:inline-block; padding:14px 36px; font-size:15px; font-weight:700; color:#FFFFFF; text-decoration:none; border-radius:9999px; letter-spacing:0.01em;">
                                Définir mon mot de passe
                            </a>
                        </td>
                    </tr>
                </table>

                <!-- Note -->
                <p style="font-size:13px; line-height:1.5; color:#777777; margin:0; padding:16px 20px; background:#F9FAFB; border-radius:10px; border:1px solid #EEEEEE;">
                    Ce lien est personnel et valable <strong>48 heures</strong>. Si vous n'êtes pas concerné par ce message, ignorez-le en toute tranquillité.
                </p>
            </td>
        </tr>

        <!-- Direct link fallback -->
        <tr>
            <td style="padding:0 40px 30px; text-align:center;">
                <p style="font-size:11px; color:#999999; word-break:break-all; line-height:1.4; margin:15px 0 0;">
                    Si le bouton ne fonctionne pas, copiez et collez l'adresse suivante dans votre navigateur :<br>
                    <a href="{{ $activationUrl }}" style="color:#E6247A; text-decoration:none;">{{ $activationUrl }}</a>
                </p>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="padding:24px 40px; background:#FAFAFA; border-top:1px solid #F0F0F0; text-align:center;">
                <p style="font-size:12px; color:#888888; margin:0 0 4px; font-weight:600;">
                    Institut de Formation et d'Administration Moderne (IFAM)
                </p>
                <p style="font-size:11px; color:#AAAAAA; margin:0;">
                    IFAM Connect · Plateforme numérique académique · © {{ date('Y') }} IFAM
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
