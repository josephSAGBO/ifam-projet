@extends('layouts.admin')

@section('title', 'Rôles & Permissions')

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Rôles & Permissions
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Configuration des droits d'accès et des profils système
        </p>
    </div>
    <a href="{{ route('admin.roles.create') }}"
       style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem;
              background:var(--color-primary-container); color:var(--color-on-primary); border-radius:0.75rem;
              text-decoration:none; font-size:0.875rem; font-weight:700;
              box-shadow:0 3px 10px rgba(228,0,102,0.3); transition:all 0.2s;"
       onmouseover="this.style.transform='translateY(-1px)'"
       onmouseout="this.style.transform=''">
        <span class="material-symbols-outlined" style="font-size:20px;">add_circle</span>
        Nouveau rôle
    </a>
</div>

@if(session('success'))
<div style="margin-bottom:1rem; padding:0.875rem 1rem; border-radius:0.75rem; background:#ecfdf5; color:#065f46; font-size:0.875rem; font-weight:500; display:flex; align-items:center; gap:0.5rem;">
    <span class="material-symbols-outlined" style="font-size:18px;">check_circle</span>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div style="margin-bottom:1rem; padding:0.875rem 1rem; border-radius:0.75rem; background:#fef2f2; color:#991b1b; font-size:0.875rem; font-weight:500; display:flex; align-items:center; gap:0.5rem;">
    <span class="material-symbols-outlined" style="font-size:18px;">warning</span>
    {{ session('error') }}
</div>
@endif

<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden;">
    @if($roles->isEmpty())
    <div style="padding:4rem 2rem; text-align:center; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined" style="font-size:48px; opacity:0.25; display:block; margin-bottom:1rem;">security</span>
        <p style="font-size:0.9375rem; margin:0;">Aucun rôle trouvé.</p>
    </div>
    @else
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Nom du rôle</th>
                    <th style="text-align:center; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Permissions</th>
                    <th style="text-align:center; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Utilisateurs</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Type</th>
                    <th style="padding:0.875rem 1rem; text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $role)
                @php
                    $isSystem = in_array($role->name, $systemRoles, true);
                @endphp
                <tr style="border-bottom:1px solid var(--color-surface-container); transition:background 0.15s;"
                    onmouseover="this.style.background='var(--color-surface-container-low)'"
                    onmouseout="this.style.background=''">

                    <td style="padding:0.875rem 1rem;">
                        <a href="{{ route('admin.roles.show', $role) }}"
                           style="font-weight:600; color:var(--color-on-surface); text-decoration:none;"
                           onmouseover="this.style.textDecoration='underline'"
                           onmouseout="this.style.textDecoration='none'">{{ $role->name }}</a>
                        <div style="font-size:0.75rem; color:var(--color-on-surface-variant); margin-top:2px;">Guard : {{ $role->guard_name }}</div>
                    </td>

                    <td style="padding:0.875rem 1rem; text-align:center;">
                        <span style="display:inline-block; padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.75rem; font-weight:700; background:var(--color-surface-container-high); color:var(--color-on-surface);">
                            {{ $role->permissions_count }} permission(s)
                        </span>
                    </td>

                    <td style="padding:0.875rem 1rem; text-align:center;">
                        <span style="display:inline-block; padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.75rem; font-weight:700; background:var(--color-surface-container-high); color:var(--color-on-surface);">
                            {{ $role->users_count }}
                        </span>
                    </td>

                    <td style="padding:0.875rem 1rem;">
                        @if($isSystem)
                        <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:var(--color-secondary-container); color:var(--color-on-secondary-fixed);">
                            Système
                        </span>
                        @else
                        <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#f3f4f6; color:#4b5563;">
                            Personnalisé
                        </span>
                        @endif
                    </td>

                    <td style="padding:0.875rem 1rem; text-align:right;">
                        <div style="display:inline-flex; align-items:center; gap:0.5rem;">
                            <a href="{{ route('admin.roles.edit', $role) }}"
                               title="Configurer permissions"
                               style="display:inline-flex; align-items:center; padding:0.25rem; border-radius:0.375rem; color:var(--color-on-surface-variant); text-decoration:none;"
                               onmouseover="this.style.color='var(--color-primary)'"
                               onmouseout="this.style.color='var(--color-on-surface-variant)'">
                                <span class="material-symbols-outlined" style="font-size:20px;">tune</span>
                            </a>

                            <a href="{{ route('admin.roles.show', $role) }}"
                               title="Voir utilisateurs"
                               style="display:inline-flex; align-items:center; padding:0.25rem; border-radius:0.375rem; color:var(--color-on-surface-variant); text-decoration:none;"
                               onmouseover="this.style.color='var(--color-primary)'"
                               onmouseout="this.style.color='var(--color-on-surface-variant)'">
                                <span class="material-symbols-outlined" style="font-size:20px;">group</span>
                            </a>

                            @if(! $isSystem && $role->users_count === 0)
                            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                                  onsubmit="return confirm('Supprimer ce rôle ?');"
                                  style="display:inline; margin:0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Supprimer"
                                        style="background:none; border:none; cursor:pointer; padding:0.25rem; color:#dc2626; display:flex; align-items:center;">
                                    <span class="material-symbols-outlined" style="font-size:20px;">delete</span>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($roles->hasPages())
    <div style="padding:1rem 1.25rem; border-top:1px solid var(--color-outline-variant);">
        {{ $roles->links() }}
    </div>
    @endif
    @endif
</div>

@endsection
