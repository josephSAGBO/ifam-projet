@extends('layouts.admin')
@section('title', 'Paramètres')
@section('content')

<div style="margin-bottom:1.75rem;">
    <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Paramètres</h1>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">Gérez votre profil et vos préférences de sécurité.</p>
</div>

<div style="display:grid; grid-template-columns:240px 1fr; gap:1.5rem; max-width:1050px;">

    {{-- Sidebar nav --}}
    <div style="display:flex; flex-direction:column; gap:0.375rem;">
        <a href="#profile" onclick="showTab('profile', this)" data-tab="profile"
           style="display:flex; align-items:center; gap:0.75rem; padding:0.75rem 1rem; border-radius:0.75rem; text-decoration:none; font-size:0.875rem; font-weight:600; background:var(--color-primary-container); color:var(--color-on-primary);">
            <span class="material-symbols-outlined" style="font-size:20px;">person</span> Mon profil
        </a>
        <a href="#security" onclick="showTab('security', this)" data-tab="security"
           style="display:flex; align-items:center; gap:0.75rem; padding:0.75rem 1rem; border-radius:0.75rem; text-decoration:none; font-size:0.875rem; font-weight:600; color:var(--color-on-surface-variant); transition:all 0.15s;"
           onmouseover="this.style.background='var(--color-surface-container)'"
           onmouseout="this.style.background='transparent'">
            <span class="material-symbols-outlined" style="font-size:20px;">lock</span> Sécurité
        </a>
        <a href="#appearance" onclick="showTab('appearance', this)" data-tab="appearance"
           style="display:flex; align-items:center; gap:0.75rem; padding:0.75rem 1rem; border-radius:0.75rem; text-decoration:none; font-size:0.875rem; font-weight:600; color:var(--color-on-surface-variant); transition:all 0.15s;"
           onmouseover="this.style.background='var(--color-surface-container)'"
           onmouseout="this.style.background='transparent'">
            <span class="material-symbols-outlined" style="font-size:20px;">palette</span> Apparence
        </a>
    </div>

    {{-- Content panels --}}
    <div>

        {{-- ── PROFILE ── --}}
        <div id="tab-profile">
            @if(session('success_profile'))
            <div style="padding:0.875rem 1rem; border-radius:0.75rem; background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; font-weight:600; font-size:0.875rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="font-size:18px;">check_circle</span> {{ session('success_profile') }}
            </div>
            @endif
            <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:2rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
                <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1.5rem; display:flex; align-items:center; gap:0.625rem;">
                    <span class="material-symbols-outlined" style="font-size:20px; color:var(--color-primary);">person</span> Informations personnelles
                </h2>

                {{-- Avatar placeholder --}}
                <div style="display:flex; align-items:center; gap:1.25rem; margin-bottom:1.75rem; padding-bottom:1.75rem; border-bottom:1px solid var(--color-surface-container);">
                    @if($user->photo_path)
                    <img src="{{ Storage::url($user->photo_path) }}" alt="{{ $user->full_name }}"
                         style="width:72px; height:72px; border-radius:50%; object-fit:cover; border:3px solid var(--color-primary-container); flex-shrink:0;">
                    @else
                    <div style="width:72px; height:72px; border-radius:50%; background:var(--color-primary-container); display:flex; align-items:center; justify-content:center; font-size:1.75rem; font-weight:800; color:var(--color-on-primary); flex-shrink:0;">
                        {{ strtoupper(substr($user->first_name ?? 'A', 0, 1)) }}{{ strtoupper(substr($user->last_name ?? 'D', 0, 1)) }}
                    </div>
                    @endif
                    <div>
                        <div style="font-weight:700; font-size:1.0625rem; color:var(--color-on-surface);">{{ $user->first_name }} {{ $user->last_name }}</div>
                        <div style="font-size:0.875rem; color:var(--color-on-surface-variant); margin-top:0.125rem;">{{ $user->email }}</div>
                        <div style="margin-top:0.5rem;">
                            @foreach($user->getRoleNames() as $role)
                            <span style="padding:0.2rem 0.5rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:var(--color-surface-container); color:var(--color-on-surface-variant);">{{ $role }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.settings.updateProfile') }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PATCH')
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
                        <div>
                            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Prénom <span style="color:#dc2626;">*</span></label>
                            <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required
                                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
                            @error('first_name') <span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Nom <span style="color:#dc2626;">*</span></label>
                            <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required
                                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
                            @error('last_name') <span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Email <span style="color:#dc2626;">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
                            @error('email') <span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Téléphone</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
                        </div>
                        <div style="grid-column:1 / -1;">
                            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">
                                Photo de profil
                            </label>
                            <input type="file" name="photo" accept="image/*"
                                   style="width:100%; box-sizing:border-box; padding:0.5rem; border-radius:0.625rem;
                                          border:1.5px solid {{ $errors->has('photo') ? '#dc2626' : 'var(--color-outline-variant)' }};
                                          background:var(--color-surface-container-low); font-size:0.875rem;">
                            <p style="font-size:0.75rem; color:var(--color-on-surface-variant); margin:0.375rem 0 0;">JPG, PNG — max 2 Mo. Laissez vide pour conserver la photo actuelle.</p>
                            @error('photo') <span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div style="margin-top:1.5rem; display:flex; justify-content:flex-end;">
                        <button type="submit" style="height:44px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.5rem; border-radius:0.75rem; border:none; cursor:pointer; background:var(--color-primary-container); color:var(--color-on-primary); font-weight:700; box-shadow:0 3px 10px rgba(228,0,102,0.3);">
                            <span class="material-symbols-outlined" style="font-size:20px;">save</span> Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── SECURITY ── --}}
        <div id="tab-security" style="display:none;">
            @if(session('success_password'))
            <div style="padding:0.875rem 1rem; border-radius:0.75rem; background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; font-weight:600; font-size:0.875rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="font-size:18px;">check_circle</span> {{ session('success_password') }}
            </div>
            @endif
            <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:2rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
                <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1.5rem; display:flex; align-items:center; gap:0.625rem;">
                    <span class="material-symbols-outlined" style="font-size:20px; color:var(--color-primary);">lock</span> Changer le mot de passe
                </h2>
                <form action="{{ route('admin.settings.updatePassword') }}" method="POST">
                    @csrf @method('PATCH')
                    <div style="display:grid; gap:1.25rem; max-width:480px;">
                        <div>
                            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Mot de passe actuel <span style="color:#dc2626;">*</span></label>
                            <input type="password" name="current_password" required
                                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
                            @error('current_password') <span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Nouveau mot de passe <span style="color:#dc2626;">*</span></label>
                            <input type="password" name="password" required
                                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
                            <p style="font-size:0.75rem; color:var(--color-on-surface-variant); margin:0.375rem 0 0;">Minimum 8 caractères, avec lettres et chiffres.</p>
                            @error('password') <span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Confirmer le nouveau mot de passe <span style="color:#dc2626;">*</span></label>
                            <input type="password" name="password_confirmation" required
                                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
                        </div>
                    </div>
                    <div style="margin-top:1.5rem; display:flex; justify-content:flex-end;">
                        <button type="submit" style="height:44px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.5rem; border-radius:0.75rem; border:none; cursor:pointer; background:#dc2626; color:#fff; font-weight:700; box-shadow:0 3px 10px rgba(220,38,38,0.3);">
                            <span class="material-symbols-outlined" style="font-size:20px;">lock_reset</span> Mettre à jour le mot de passe
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── APPEARANCE ── --}}
        <div id="tab-appearance" style="display:none;">
            <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:2rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
                <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1.5rem; display:flex; align-items:center; gap:0.625rem;">
                    <span class="material-symbols-outlined" style="font-size:20px; color:var(--color-primary);">palette</span> Thème de l'interface
                </h2>
                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:1rem; max-width:560px;">
                    @foreach(['dark'=>['label'=>'Sombre','bg'=>'#0b1c30','accent'=>'#e40066'],'light'=>['label'=>'Clair','bg'=>'#f8f9fb','accent'=>'#e40066'],'auto'=>['label'=>'Automatique','bg'=>'linear-gradient(135deg,#0b1c30 50%,#f8f9fb 50%)','accent'=>'#e40066']] as $themeKey => $theme)
                    <button onclick="setTheme('{{ $themeKey }}')" id="theme-{{ $themeKey }}"
                            style="border:2px solid var(--color-outline-variant); border-radius:0.875rem; padding:1rem; cursor:pointer; text-align:center; transition:all 0.2s; background:transparent;">
                        <div style="width:100%; height:56px; border-radius:0.5rem; background:{{ $theme['bg'] }}; margin-bottom:0.625rem; position:relative; overflow:hidden;">
                            <div style="position:absolute; bottom:8px; left:8px; width:24px; height:24px; border-radius:50%; background:{{ $theme['accent'] }};"></div>
                        </div>
                        <span style="font-size:0.8125rem; font-weight:600; color:var(--color-on-surface);">{{ $theme['label'] }}</span>
                    </button>
                    @endforeach
                </div>
                <p style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin:1rem 0 0;">Le thème est sauvegardé localement dans votre navigateur.</p>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName, clickedLink) {
    // Hide all tabs
    ['profile','security','appearance'].forEach(t => {
        document.getElementById('tab-' + t).style.display = 'none';
    });
    // Show selected
    document.getElementById('tab-' + tabName).style.display = '';

    // Update nav styles
    document.querySelectorAll('[data-tab]').forEach(link => {
        const active = link.dataset.tab === tabName;
        link.style.background = active ? 'var(--color-primary-container)' : 'transparent';
        link.style.color      = active ? 'var(--color-on-primary)' : 'var(--color-on-surface-variant)';
    });
    event.preventDefault();
}

function setTheme(theme) {
    localStorage.setItem('ifam_theme', theme);
    if (theme === 'light') {
        document.documentElement.setAttribute('data-theme', 'light');
    } else if (theme === 'dark') {
        document.documentElement.removeAttribute('data-theme');
    } else {
        if (window.matchMedia('(prefers-color-scheme: light)').matches) {
            document.documentElement.setAttribute('data-theme', 'light');
        } else {
            document.documentElement.removeAttribute('data-theme');
        }
    }
    // Highlight active theme button
    ['dark','light','auto'].forEach(t => {
        const btn = document.getElementById('theme-' + t);
        btn.style.borderColor = t === theme ? 'var(--color-primary-container)' : 'var(--color-outline-variant)';
    });
}

// Restore active theme button on load
const savedTheme = localStorage.getItem('ifam_theme') || 'dark';
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('theme-' + savedTheme);
    if (btn) btn.style.borderColor = 'var(--color-primary-container)';
});

// Show correct tab if password error
@if($errors->has('current_password') || $errors->has('password') || session('success_password'))
showTab('security', document.querySelector('[data-tab="security"]'));
@endif
</script>
@endsection
