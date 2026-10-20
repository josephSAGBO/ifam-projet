<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'IFAM Connect') — @yield('role_label', 'IFAM')</title>
    <meta name="description" content="IFAM Connect — Plateforme de gestion de l'Institut IFAM">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    @stack('styles')
</head>
<body class="{{ request()->routeIs('student.*') ? 'student-role' : '' }}" style="background-color:var(--color-background); color:var(--color-on-surface);">

    {{-- ══ Topbar ══ --}}
    <header class="role-topbar" style="position:fixed; top:0; left:0; right:0; height:64px; background:var(--color-surface-container-lowest);
                   box-shadow:0 1px 4px rgba(11,28,48,0.06); z-index:50; display:flex; align-items:center; padding:0 1.5rem;
                   justify-content:space-between;">
        {{-- Logo --}}
        <a href="@yield('home_route', '/')" style="display:flex; align-items:center; gap:0.75rem; text-decoration:none;">
            <img src="{{ asset('img/logo.jpg') }}"
                 alt="Logo IFAM"
                 style="height:40px; width:auto; object-fit:contain;">
            <div>
                <div style="font-size:0.6875rem; font-weight:600; color:var(--color-primary); text-transform:uppercase; letter-spacing:0.08em;">
                    @yield('role_label', 'Espace')
                </div>
            </div>
        </a>

        {{-- Navigation centrale --}}
        <nav class="role-navigation {{ request()->routeIs('student.*') ? 'student-navigation' : '' }}" style="display:flex; align-items:center; gap:0.375rem;">
            @yield('nav_links')
        </nav>

        {{-- User menu --}}
        <div style="display:flex; align-items:center; gap:0.875rem;">
            {{-- Notifications --}}
            <a href="{{ route('admin.notifications.index') }}"
               style="width:38px; height:38px; border-radius:0.625rem; display:flex; align-items:center; justify-content:center;
                      background:var(--color-surface-container); color:var(--color-on-surface-variant); text-decoration:none; position:relative;">
                <span class="material-symbols-outlined" style="font-size:20px;">notifications</span>
                @if(isset($unreadNotifications) && $unreadNotifications > 0)
                <span style="position:absolute; top:4px; right:4px; width:16px; height:16px; border-radius:50%;
                             background:var(--color-primary-container); color:var(--color-on-primary);
                             font-size:0.5625rem; font-weight:800; display:flex; align-items:center; justify-content:center;">
                    {{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}
                </span>
                @endif
            </a>

            {{-- Avatar --}}
            <div style="display:flex; align-items:center; gap:0.625rem;">
                @if(auth()->user()->photo_path)
                <img src="{{ Storage::url(auth()->user()->photo_path) }}" alt="{{ auth()->user()->full_name }}"
                     style="width:36px; height:36px; border-radius:50%; object-fit:cover; border:2px solid var(--color-primary-container);">
                @else
                <div style="width:36px; height:36px; border-radius:50%; background:var(--color-primary-container); display:flex; align-items:center; justify-content:center;">
                    <span style="font-size:0.75rem; font-weight:800; color:var(--color-on-primary);">{{ auth()->user()->initials }}</span>
                </div>
                @endif
                <div class="role-user-info" style="line-height:1.2;">
                    <div style="font-size:0.8125rem; font-weight:700; color:var(--color-on-surface);">{{ auth()->user()->full_name }}</div>
                    <div style="font-size:0.6875rem; color:var(--color-on-surface-variant);">{{ auth()->user()->getRoleNames()->first() ?? '' }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" title="Déconnexion"
                        style="background:none; border:none; cursor:pointer; color:var(--color-on-surface-variant); display:flex; align-items:center;"
                        onmouseover="this.style.color='#dc2626'" onmouseout="this.style.color='var(--color-on-surface-variant)'">
                    <span class="material-symbols-outlined" style="font-size:20px;">logout</span>
                </button>
            </form>
        </div>
    </header>

    {{-- ══ Contenu ══ --}}
    <main class="role-main-content {{ request()->routeIs('student.*') ? 'student-content' : '' }}" style="padding-top:80px; padding-left:2rem; padding-right:2rem; padding-bottom:3rem; min-height:100vh; background:var(--color-surface); max-width:1400px; margin:0 auto;">
        @yield('content')
    </main>

    {{-- Flash --}}
    @if(session('success'))
    <div id="flash-success" style="position:fixed; bottom:1.5rem; right:1.5rem; z-index:9999; padding:0.875rem 1.25rem; border-radius:0.75rem;
                background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; display:flex; align-items:center; gap:0.75rem;
                box-shadow:0 4px 16px rgba(5,150,105,0.15); font-size:0.875rem; font-weight:600; animation:slideUp 0.3s ease-out;">
        <span class="material-symbols-outlined" style="font-size:20px; color:#059669;">check_circle</span>
        {{ session('success') }}
        <button onclick="document.getElementById('flash-success').remove()" style="margin-left:0.5rem; background:none; border:none; cursor:pointer; color:#059669; display:flex;">
            <span class="material-symbols-outlined" style="font-size:18px;">close</span>
        </button>
    </div>
    @endif
    @if(session('error'))
    <div id="flash-error" style="position:fixed; bottom:1.5rem; right:1.5rem; z-index:9999; padding:0.875rem 1.25rem; border-radius:0.75rem;
                background:#fef2f2; border:1px solid #fecaca; color:#991b1b; display:flex; align-items:center; gap:0.75rem;
                box-shadow:0 4px 16px rgba(185,28,28,0.12); font-size:0.875rem; font-weight:600;">
        <span class="material-symbols-outlined" style="font-size:20px; color:#dc2626;">error</span>
        {{ session('error') }}
    </div>
    @endif

    <style>
        @keyframes slideUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
    </style>
    @stack('scripts')
</body>
</html>
