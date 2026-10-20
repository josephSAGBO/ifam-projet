@extends('layouts.admin')

@section('title', 'Détails de l\'utilisateur')

@section('content')

{{-- En-tête --}}
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div style="display:flex; align-items:center; gap:1rem;">
        <a href="{{ route('admin.users.index') }}"
           style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
                  display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
                  transition:all 0.15s;"
           onmouseover="this.style.background='var(--color-surface-container-high)'"
           onmouseout="this.style.background='var(--color-surface-container)'">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        {{-- Avatar --}}
        @if($user->photo_path)
        <img src="{{ Storage::url($user->photo_path) }}" alt="{{ $user->full_name }}"
             style="width:60px; height:60px; border-radius:50%; object-fit:cover; border:3px solid var(--color-outline-variant); flex-shrink:0;">
        @else
        <div style="width:60px; height:60px; border-radius:50%; flex-shrink:0;
                    background:var(--color-primary-container); display:flex; align-items:center; justify-content:center;">
            <span style="font-size:1.25rem; font-weight:700; color:var(--color-on-primary);">{{ $user->initials }}</span>
        </div>
        @endif
        <div>
            <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
                {{ $user->full_name }}
            </h1>
            <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0; display:flex; align-items:center; gap:0.5rem;">
                @if($user->matricule)
                    <span style="font-family:monospace; background:var(--color-surface-container-high); padding:0.125rem 0.375rem; border-radius:0.25rem;">{{ $user->matricule }}</span>
                @endif
                @php $roleName = $user->getRoleNames()->first(); @endphp
                @if($roleName)
                    <span style="text-transform:capitalize;">• {{ $roleName }}</span>
                @endif
            </p>
        </div>
    </div>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('admin.users.edit', $user) }}"
           style="height:40px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1rem; border-radius:0.75rem;
                  background:var(--color-surface-container-high); color:var(--color-on-surface); text-decoration:none;
                  font-size:0.875rem; font-weight:600; transition:background 0.15s;"
           onmouseover="this.style.background='var(--color-surface-container-highest)'"
           onmouseout="this.style.background='var(--color-surface-container-high)'">
            <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
            Modifier
        </a>
    </div>
</div>

<div style="display:grid; grid-template-columns:2fr 1fr; gap:1.5rem;">

    {{-- Colonne principale --}}
    <div style="display:flex; flex-direction:column; gap:1.5rem;">
        
        {{-- Informations générales --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1.25rem; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.75rem;">
                Informations Personnelles
            </h2>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Prénom & Nom</div>
                    <div style="color:var(--color-on-surface); font-weight:500;">{{ $user->full_name }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Genre</div>
                    <div style="color:var(--color-on-surface);">{{ $user->gender ?? 'Non spécifié' }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Date de naissance</div>
                    <div style="color:var(--color-on-surface);">
                        {{ $user->birth_date ? $user->birth_date->format('d/m/Y') : 'Non spécifiée' }}
                        @if($user->birth_date)
                            <span style="color:var(--color-on-surface-variant); font-size:0.8125rem;">({{ $user->birth_date->age }} ans)</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1.25rem; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.75rem;">
                Contact
            </h2>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Email</div>
                    <div style="color:var(--color-on-surface);">
                        @if($user->email)
                            <a href="mailto:{{ $user->email }}" style="color:var(--color-primary); text-decoration:none;">{{ $user->email }}</a>
                        @else
                            <span style="color:var(--color-on-surface-variant); font-style:italic;">Non spécifié</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Téléphone</div>
                    <div style="color:var(--color-on-surface);">
                        @if($user->phone)
                            <a href="tel:{{ $user->phone }}" style="color:var(--color-primary); text-decoration:none;">{{ $user->phone }}</a>
                        @else
                            <span style="color:var(--color-on-surface-variant); font-style:italic;">Non spécifié</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Colonne latérale (Statut & Sécurité) --}}
    <div style="display:flex; flex-direction:column; gap:1.5rem;">
        
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1.25rem; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.75rem;">
                Statut
            </h2>

            <div style="display:flex; flex-direction:column; gap:1rem;">
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.375rem;">État du compte</div>
                    @if($user->is_active)
                        <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.75rem; font-weight:700; background:#ecfdf5; color:#065f46; display:inline-flex; align-items:center; gap:0.375rem;">
                            <span style="width:6px;height:6px;border-radius:9999px;background:#059669;"></span>Actif
                        </span>
                    @else
                        <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.75rem; font-weight:700; background:#fef2f2; color:#b91c1c; display:inline-flex; align-items:center; gap:0.375rem;">
                            <span style="width:6px;height:6px;border-radius:9999px;background:#dc2626;"></span>Inactif
                        </span>
                    @endif
                </div>

                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Dernière connexion</div>
                    <div style="color:var(--color-on-surface); font-size:0.875rem;">
                        {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Jamais connecté' }}
                    </div>
                </div>

                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Créé le</div>
                    <div style="color:var(--color-on-surface); font-size:0.875rem;">
                        {{ $user->created_at?->format('d/m/Y H:i') }}
                        @if($user->creator)
                            <div style="font-size:0.75rem; color:var(--color-on-surface-variant); margin-top:0.125rem;">
                                par {{ $user->creator->full_name }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

@endsection
