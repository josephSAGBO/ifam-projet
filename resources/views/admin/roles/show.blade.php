@extends('layouts.admin')

@section('title', 'Rôle : ' . $role->name)

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div style="display:flex; align-items:center; gap:1rem;">
        <a href="{{ route('admin.roles.index') }}"
           style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
              display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
              transition:all 0.15s;"
           onmouseover="this.style.background='var(--color-surface-container-high)'"
           onmouseout="this.style.background='var(--color-surface-container)'">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
                Rôle : {{ $role->name }}
            </h1>
            <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
                Guard : {{ $role->guard_name }} • {{ $role->permissions->count() }} permission(s) associée(s)
            </p>
        </div>
    </div>
    <div>
        <a href="{{ route('admin.roles.edit', $role) }}"
           style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem;
                  background:var(--color-primary-container); color:var(--color-on-primary); border-radius:0.75rem;
                  text-decoration:none; font-size:0.875rem; font-weight:700;">
            <span class="material-symbols-outlined" style="font-size:20px;">tune</span>
            Configurer les permissions
        </a>
    </div>
</div>

<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden;">
    <div style="padding:1.25rem 1.5rem; border-bottom:1px solid var(--color-surface-container);">
        <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0;">
            Utilisateurs possédant ce rôle ({{ $users->total() }})
        </h2>
    </div>

    @if($users->isEmpty())
    <div style="padding:3rem 2rem; text-align:center; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined" style="font-size:40px; opacity:0.25; display:block; margin-bottom:0.75rem;">person_off</span>
        <p style="font-size:0.9375rem; margin:0;">Aucun utilisateur n'a actuellement ce rôle.</p>
    </div>
    @else
    <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
        <thead>
            <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Nom complet</th>
                <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Email</th>
                <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Site principal</th>
                <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Statut</th>
                <th style="padding:0.875rem 1rem; text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr style="border-bottom:1px solid var(--color-surface-container);">
                <td style="padding:0.875rem 1rem; font-weight:600; color:var(--color-on-surface);">
                    {{ $user->full_name }}
                </td>
                <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">
                    {{ $user->email }}
                </td>
                <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">
                    {{ $user->sites->pluck('nom')->implode(', ') ?: '—' }}
                </td>
                <td style="padding:0.875rem 1rem;">
                    @if($user->is_active)
                    <span style="color:#065f46; font-size:0.75rem; font-weight:700;">Actif</span>
                    @else
                    <span style="color:#6b7280; font-size:0.75rem; font-weight:700;">Inactif</span>
                    @endif
                </td>
                <td style="padding:0.875rem 1rem; text-align:right;">
                    <a href="{{ route('admin.users.show', $user) }}"
                       style="display:inline-flex; align-items:center; padding:0.25rem; color:var(--color-on-surface-variant);"
                       title="Consulter le profil">
                        <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($users->hasPages())
    <div style="padding:1rem 1.25rem; border-top:1px solid var(--color-outline-variant);">
        {{ $users->links() }}
    </div>
    @endif
    @endif
</div>

@endsection
