<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'IFAM Connect — Réseau Alumni')</title>
    <meta name="description" content="Découvrez le parcours et les créations des diplômés de l'Institut de formation aux métiers de la mode, du stylisme et du textile (IFAM).">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    @stack('styles')
</head>
<body style="background-color:var(--color-background, #f8f9ff); color:var(--color-on-surface, #0b1c30); font-family:var(--font-sans, sans-serif); margin:0; min-height:100vh; display:flex; flex-direction:column;">

    {{-- Topbar publique --}}
    <header style="position:sticky; top:0; z-index:50; background:rgba(255,255,255,0.95); backdrop-filter:blur(8px); border-bottom:1px solid rgba(11,28,48,0.06); box-shadow:0 1px 4px rgba(11,28,48,0.03);">
        <div style="max-width:1300px; margin:0 auto; padding:0.75rem 1.5rem; display:flex; align-items:center; justify-content:space-between; gap:1rem;">
            
            {{-- Logo et identité --}}
            <a href="{{ route('alumni.public.index') }}" style="display:flex; align-items:center; gap:0.875rem; text-decoration:none;">
                <img src="{{ asset('img/logo.jpg') }}" alt="IFAM Logo" style="height:44px; width:auto; object-fit:contain; border-radius:0.375rem;">
                <div>
                    <div style="font-family:var(--font-display); font-weight:800; font-size:1.125rem; color:var(--color-primary, #b60050); line-height:1.1; letter-spacing:-0.01em;">
                        IFAM <span style="color:var(--color-on-surface, #0b1c30); font-weight:600;">Connect</span>
                    </div>
                    <div style="font-size:0.6875rem; font-weight:600; color:var(--color-on-surface-variant, #5b3f45); letter-spacing:0.04em; text-transform:uppercase;">
                        Réseau & Réalisations Alumni
                    </div>
                </div>
            </a>

            {{-- Liens de navigation publique --}}
            <nav style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                <a href="{{ route('alumni.public.index') }}" 
                   style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; text-decoration:none; font-size:0.875rem; font-weight:700; transition:all 0.15s; {{ request()->routeIs('alumni.public.index') || request()->routeIs('alumni.public.show') ? 'background:var(--color-primary-container, #e40066); color:var(--color-on-primary, #fff);' : 'color:var(--color-on-surface, #0b1c30);' }}">
                    <span class="material-symbols-outlined" style="font-size:18px;">people</span>
                    Annuaire des Diplômés
                </a>

                <a href="{{ route('alumni.public.posts.index') }}" 
                   style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; text-decoration:none; font-size:0.875rem; font-weight:700; transition:all 0.15s; {{ request()->routeIs('alumni.public.posts.*') ? 'background:var(--color-primary-container, #e40066); color:var(--color-on-primary, #fff);' : 'color:var(--color-on-surface, #0b1c30);' }}">
                    <span class="material-symbols-outlined" style="font-size:18px;">auto_awesome</span>
                    Créations & Actualités
                </a>
            </nav>

            {{-- Espace Utilisateur ou Connexion --}}
            <div style="display:flex; align-items:center; gap:0.75rem;">
                @auth
                    @php
                        $user = auth()->user();
                        $dashboardRoute = match(true) {
                            $user->hasRole('alumni') => route('alumni.dashboard'),
                            $user->hasRole('admin') => route('admin.dashboard'),
                            $user->hasRole('direction') => route('direction.dashboard'),
                            $user->hasRole('enseignant') => route('teacher.dashboard'),
                            $user->hasRole('etudiant') => route('student.dashboard'),
                            $user->hasRole('parent') => route('parent.dashboard'),
                            default => url('/dashboard'),
                        };
                    @endphp
                    <a href="{{ $dashboardRoute }}" 
                       style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.45rem 0.875rem; border-radius:0.625rem; background:var(--color-surface-container, #e5eeff); color:var(--color-on-surface, #0b1c30); text-decoration:none; font-size:0.8125rem; font-weight:700;">
                        <span class="material-symbols-outlined" style="font-size:18px; color:var(--color-primary, #b60050);">space_dashboard</span>
                        Mon Espace ({{ $user->getRoleNames()->first() ?? 'Compte' }})
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.45rem 1rem; border-radius:0.625rem; background:var(--color-surface-container, #e5eeff); color:var(--color-primary, #b60050); text-decoration:none; font-size:0.8125rem; font-weight:700; border:1px solid var(--color-outline-variant, #e4bdc3);">
                        <span class="material-symbols-outlined" style="font-size:18px;">login</span>
                        Se connecter
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main style="flex:1; max-width:1300px; width:100%; margin:0 auto; padding:2rem 1.5rem 4rem; box-sizing:border-box;">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer style="background:var(--color-surface-container-lowest, #ffffff); border-top:1px solid rgba(11,28,48,0.06); padding:2.5rem 1.5rem; margin-top:auto;">
        <div style="max-width:1300px; margin:0 auto; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; gap:1rem;">
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <img src="{{ asset('img/logo.jpg') }}" alt="IFAM" style="height:36px; width:auto; object-fit:contain;">
                <span style="font-family:var(--font-display); font-weight:800; font-size:1rem; color:var(--color-primary, #b60050);">
                    IFAM Bénin
                </span>
            </div>
            <p style="font-size:0.875rem; color:var(--color-on-surface-variant, #5b3f45); margin:0; max-width:600px;">
                Institut de formation aux métiers de la mode, du stylisme et du textile — Cotonou, Bénin.
            </p>
            <div style="font-size:0.8125rem; font-weight:700; color:var(--color-secondary, #835500); font-style:italic;">
                « L'excellence au cœur de la formation »
            </div>
            <div style="font-size:0.75rem; color:var(--color-tertiary, #5c5b5b); margin-top:0.5rem;">
                &copy; {{ date('Y') }} IFAM Connect — Tous droits réservés.
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
