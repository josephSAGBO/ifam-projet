@extends('layouts.admin')

@section('title', 'Utilisateurs')

@section('content')

{{-- En-tête --}}
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Utilisateurs
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            {{ $users->total() }} compte{{ $users->total() > 1 ? 's' : '' }} dans le système
        </p>
    </div>
    <a href="{{ route('admin.users.create') }}"
       style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem;
              background:var(--color-primary-container); color:var(--color-on-primary); border-radius:0.75rem;
              text-decoration:none; font-size:0.875rem; font-weight:700;
              box-shadow:0 3px 10px rgba(228,0,102,0.3); transition:all 0.2s;"
       onmouseover="this.style.transform='translateY(-1px)'"
       onmouseout="this.style.transform=''">
        <span class="material-symbols-outlined" style="font-size:20px;">person_add</span>
        Créer un utilisateur
    </a>
</div>

{{-- Filtres --}}
<form method="GET" action="{{ route('admin.users.index') }}"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; margin-bottom:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    <div style="display:grid; grid-template-columns:1fr auto auto auto; gap:0.75rem; align-items:end;">

        {{-- Recherche --}}
        <div>
            <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem; text-transform:uppercase; letter-spacing:0.06em;">
                Recherche
            </label>
            <div style="position:relative;">
                <span class="material-symbols-outlined"
                      style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); font-size:18px; color:var(--color-on-surface-variant); pointer-events:none;">
                    search
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Nom, email, matricule…"
                       style="width:100%; box-sizing:border-box; height:40px; padding:0 0.875rem 0 2.5rem; border-radius:0.625rem;
                              border:1.5px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.875rem; color:var(--color-on-surface);"
                       onfocus="this.style.borderColor='var(--color-primary-container)'"
                       onblur="this.style.borderColor='var(--color-outline-variant)'">
            </div>
        </div>

        {{-- Filtre rôle --}}
        <div>
            <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem; text-transform:uppercase; letter-spacing:0.06em;">
                Rôle
            </label>
            <select name="role"
                    style="height:40px; padding:0 2rem 0 0.75rem; border-radius:0.625rem; border:1.5px solid var(--color-outline-variant);
                           background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface); cursor:pointer;">
                <option value="">Tous les rôles</option>
                @foreach($roles as $role)
                <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                    {{ ucfirst($role->name) }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Filtre statut --}}
        <div>
            <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem; text-transform:uppercase; letter-spacing:0.06em;">
                Statut
            </label>
            <select name="status"
                    style="height:40px; padding:0 2rem 0 0.75rem; border-radius:0.625rem; border:1.5px solid var(--color-outline-variant);
                           background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface); cursor:pointer;">
                <option value="">Tous les statuts</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Actifs</option>
                <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>En attente d'activation</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactifs</option>
            </select>
        </div>

        {{-- Bouton filtrer --}}
        <div style="display:flex; gap:0.5rem;">
            <button type="submit"
                    style="height:40px; padding:0 1.25rem; border-radius:0.625rem; border:none; cursor:pointer;
                           background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.875rem; font-weight:700;">
                Filtrer
            </button>
            @if(request()->hasAny(['search','role','status']))
            <a href="{{ route('admin.users.index') }}"
               style="height:40px; padding:0 1rem; border-radius:0.625rem; display:inline-flex; align-items:center;
                      background:var(--color-surface-container-high); color:var(--color-on-surface); font-size:0.875rem; text-decoration:none;">
                Effacer
            </a>
            @endif
        </div>
    </div>
</form>

{{-- Tableau --}}
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden;">
    @if($users->isEmpty())
    <div style="padding:4rem 2rem; text-align:center; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined" style="font-size:48px; opacity:0.25; display:block; margin-bottom:1rem;">group_off</span>
        <p style="font-size:0.9375rem; margin:0;">Aucun utilisateur trouvé.</p>
    </div>
    @else
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Utilisateur</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Contact</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Rôle</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Statut</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Créé le</th>
                    <th style="padding:0.875rem 1rem;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr style="border-bottom:1px solid var(--color-surface-container); transition:background 0.15s;"
                    onmouseover="this.style.background='var(--color-surface-container-low)'"
                    onmouseout="this.style.background=''">

                    {{-- Identité --}}
                    <td style="padding:0.875rem 1rem;">
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            @if($user->photo_path)
                            <img src="{{ Storage::url($user->photo_path) }}" alt="{{ $user->full_name }}"
                                 style="width:38px; height:38px; border-radius:9999px; flex-shrink:0; object-fit:cover; border:2px solid var(--color-outline-variant);">
                            @else
                            <div style="width:38px; height:38px; border-radius:9999px; flex-shrink:0;
                                        background:{{ $user->is_active ? 'var(--color-primary-container)' : 'var(--color-surface-container-high)' }};
                                        display:flex; align-items:center; justify-content:center;">
                                <span style="font-size:0.8125rem; font-weight:700; color:{{ $user->is_active ? 'var(--color-on-primary)' : 'var(--color-on-surface-variant)' }};">
                                    {{ $user->initials }}
                                </span>
                            </div>
                            @endif
                            <div>
                                <div style="font-weight:700; color:var(--color-on-surface);">{{ $user->full_name }}</div>
                                @if($user->matricule)
                                <div style="font-size:0.75rem; color:var(--color-on-surface-variant); font-family:monospace;">{{ $user->matricule }}</div>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- Contact --}}
                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">
                        <div>{{ $user->email ?? '—' }}</div>
                        @if($user->phone)
                        <div style="font-size:0.8125rem;">{{ $user->phone }}</div>
                        @endif
                    </td>

                    {{-- Rôle --}}
                    <td style="padding:0.875rem 1rem;">
                        @php $roleName = $user->getRoleNames()->first() ?? null; @endphp
                        @if($roleName)
                        @php
                            $roleColors = [
                                'administrateur'       => ['bg'=>'#fce7f3','color'=>'#9d174d'],
                                'direction'            => ['bg'=>'#ede9fe','color'=>'#5b21b6'],
                                'enseignant'           => ['bg'=>'#dbeafe','color'=>'#1e40af'],
                                'etudiant'             => ['bg'=>'#d1fae5','color'=>'#065f46'],
                                'parent'               => ['bg'=>'#fff7ed','color'=>'#92400e'],
                            ];
                            $rc = $roleColors[$roleName] ?? ['bg'=>'var(--color-surface-container-high)','color'=>'var(--color-on-surface)'];
                        @endphp
                        <span style="padding:0.25rem 0.75rem; border-radius:9999px; font-size:0.6875rem; font-weight:700;
                                     background:{{ $rc['bg'] }}; color:{{ $rc['color'] }};">
                            {{ ucfirst($roleName) }}
                        </span>
                        @else
                        <span style="color:var(--color-on-surface-variant); font-size:0.8125rem;">—</span>
                        @endif
                    </td>

                    {{-- Statut --}}
                    <td style="padding:0.875rem 1rem;">
                        @if($user->activation_token)
                        <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#fef3c7; color:#b45309; display:inline-flex; align-items:center; gap:0.25rem;">
                            <span style="width:6px;height:6px;border-radius:9999px;background:#d97706;"></span>En attente d'activation
                        </span>
                        @elseif($user->is_active)
                        <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#ecfdf5; color:#065f46; display:inline-flex; align-items:center; gap:0.25rem;">
                            <span style="width:6px;height:6px;border-radius:9999px;background:#059669;"></span>Actif
                        </span>
                        @else
                        <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#f3f4f6; color:#6b7280; display:inline-flex; align-items:center; gap:0.25rem;">
                            <span style="width:6px;height:6px;border-radius:9999px;background:#9ca3af;"></span>Inactif
                        </span>
                        @endif
                        @if(!$user->activation_token && $user->must_change_password)
                        <span style="margin-top:0.25rem; display:block; font-size:0.6875rem; color:var(--color-secondary);">
                            🔑 Mdp à changer
                        </span>
                        @endif
                    </td>

                    {{-- Date création --}}
                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant); font-size:0.8125rem;">
                        {{ $user->created_at?->format('d/m/Y') }}
                    </td>

                    {{-- Actions --}}
                    <td style="padding:0.875rem 1rem;">
                        <div style="display:flex; align-items:center; gap:0.5rem; justify-content:flex-end;">
                            <a href="{{ route('admin.users.show', $user) }}"
                               title="Voir la fiche"
                               style="width:34px; height:34px; border-radius:0.5rem; display:flex; align-items:center; justify-content:center;
                                      background:var(--color-surface-container); color:var(--color-on-surface-variant); text-decoration:none; transition:all 0.15s;"
                               onmouseover="this.style.background='var(--color-surface-container-high)'"
                               onmouseout="this.style.background='var(--color-surface-container)'">
                                <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                            </a>
                            <a href="{{ route('admin.users.edit', $user) }}"
                               title="Modifier"
                               style="width:34px; height:34px; border-radius:0.5rem; display:flex; align-items:center; justify-content:center;
                                      background:var(--color-surface-container); color:var(--color-on-surface-variant); text-decoration:none; transition:all 0.15s;"
                               onmouseover="this.style.background='var(--color-surface-container-high)'"
                               onmouseout="this.style.background='var(--color-surface-container)'">
                                <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                            </a>
                            @if($user->id !== auth()->id())
                                @if($user->activation_token)
                                <form method="POST" action="{{ route('admin.users.resendActivation', $user) }}">
                                    @csrf
                                    <button type="submit" title="Renvoyer le lien d'activation"
                                            style="width:34px; height:34px; border-radius:0.5rem; border:none; cursor:pointer;
                                                   background:var(--color-surface-container); color:#e6247a; transition:all 0.15s;"
                                            onmouseover="this.style.background='#fdf2f8'"
                                            onmouseout="this.style.background='var(--color-surface-container)'">
                                        <span class="material-symbols-outlined" style="font-size:18px;">mail</span>
                                    </button>
                                </form>
                                @endif

                                @if($user->is_active)
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                      onsubmit="return confirm('Désactiver le compte de {{ $user->full_name }} ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Désactiver"
                                            style="width:34px; height:34px; border-radius:0.5rem; border:none; cursor:pointer;
                                                   background:var(--color-surface-container); color:#dc2626; transition:all 0.15s;"
                                            onmouseover="this.style.background='#fef2f2'"
                                            onmouseout="this.style.background='var(--color-surface-container)'">
                                        <span class="material-symbols-outlined" style="font-size:18px;">block</span>
                                    </button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" title="Réactiver"
                                            style="width:34px; height:34px; border-radius:0.5rem; border:none; cursor:pointer;
                                                   background:var(--color-surface-container); color:#059669; transition:all 0.15s;"
                                            onmouseover="this.style.background='#ecfdf5'"
                                            onmouseout="this.style.background='var(--color-surface-container)'">
                                        <span class="material-symbols-outlined" style="font-size:18px;">check_circle</span>
                                    </button>
                                </form>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
    <div style="padding:1rem 1.25rem; border-top:1px solid var(--color-surface-container); display:flex; align-items:center; justify-content:space-between;">
        <span style="font-size:0.8125rem; color:var(--color-on-surface-variant);">
            Affichage {{ $users->firstItem() }}–{{ $users->lastItem() }} sur {{ $users->total() }}
        </span>
        <div style="display:flex; gap:0.375rem;">
            @if($users->onFirstPage())
            <span style="padding:0.375rem 0.75rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-on-surface-variant); font-size:0.8125rem; opacity:0.5;">← Préc.</span>
            @else
            <a href="{{ $users->previousPageUrl() }}"
               style="padding:0.375rem 0.75rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-on-surface); font-size:0.8125rem; text-decoration:none;">← Préc.</a>
            @endif
            @if($users->hasMorePages())
            <a href="{{ $users->nextPageUrl() }}"
               style="padding:0.375rem 0.75rem; border-radius:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.8125rem; font-weight:600; text-decoration:none;">Suiv. →</a>
            @else
            <span style="padding:0.375rem 0.75rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-on-surface-variant); font-size:0.8125rem; opacity:0.5;">Suiv. →</span>
            @endif
        </div>
    </div>
    @endif
    @endif
</div>

@endsection
