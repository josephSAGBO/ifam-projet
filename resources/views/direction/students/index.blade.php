@extends('layouts.role')
@section('title', 'Étudiants — Direction')
@section('role_label', 'Direction')
@section('home_route', route('direction.dashboard'))

@section('nav_links')
<a href="{{ route('direction.dashboard') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; {{ request()->routeIs('direction.dashboard') ? 'background:var(--color-primary-container); color:var(--color-on-primary);' : 'color:var(--color-on-surface-variant);' }} text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">dashboard</span> Dashboard
</a>
<a href="{{ route('direction.students.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; {{ request()->routeIs('direction.students*') ? 'background:var(--color-primary-container); color:var(--color-on-primary);' : 'color:var(--color-on-surface-variant);' }} text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">school</span> Étudiants
</a>
<a href="{{ route('direction.finance.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; {{ request()->routeIs('direction.finance*') ? 'background:var(--color-primary-container); color:var(--color-on-primary);' : 'color:var(--color-on-surface-variant);' }} text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">account_balance_wallet</span> Finances
</a>
<a href="{{ route('direction.alumni-posts.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; {{ request()->routeIs('direction.alumni-posts*') ? 'background:var(--color-primary-container); color:var(--color-on-primary);' : 'color:var(--color-on-surface-variant);' }} text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">auto_awesome</span> Alumni disponibles
</a>
<a href="{{ route('contact.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; {{ request()->routeIs('contact.*') ? 'background:var(--color-primary-container); color:var(--color-on-primary);' : 'color:var(--color-on-surface-variant);' }} text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">mail</span> Contact
</a>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Liste des Étudiants
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Consultation uniquement · {{ $students->total() }} étudiant(s)
        </p>
    </div>
</div>

{{-- Filtres --}}
<form method="GET" action="{{ route('direction.students.index') }}"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; margin-bottom:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);
             display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)) 100px; gap:1rem; align-items:end;">
    <div>
        <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Rechercher</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, matricule…"
               style="width:100%; height:40px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">
    </div>
    <div>
        <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Formation</label>
        <select name="formation_id" style="width:100%; height:40px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">
            <option value="">Toutes</option>
            @foreach($formations as $f)
                <option value="{{ $f->id }}" {{ request('formation_id') == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Session</label>
        <select name="session_id" style="width:100%; height:40px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">
            <option value="">Toutes</option>
            @foreach($sessions as $s)
                <option value="{{ $s->id }}" {{ request('session_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" style="height:40px; padding:0 1.25rem; border-radius:0.5rem; border:none; background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.875rem; font-weight:700; cursor:pointer;">
        Filtrer
    </button>
</form>

@if($students->isEmpty())
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:3.5rem 1.5rem; text-align:center; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    <span class="material-symbols-outlined" style="font-size:52px; color:var(--color-on-surface-variant); opacity:0.35; margin-bottom:1rem; display:block;">person_search</span>
    <h3 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem;">Aucun étudiant trouvé</h3>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">Modifiez vos filtres de recherche.</p>
</div>
@else
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden; border:1px solid var(--color-surface-container);">
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="background:var(--color-surface-container-low); border-bottom:2px solid var(--color-outline-variant);">
                    <th style="text-align:left; padding:0.875rem 1.5rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Matricule</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Nom complet</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Formation(s)</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Session active</th>
                    <th style="text-align:center; padding:0.875rem 1.5rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                @php
                    $activeEnroll = $student->enrollments->firstWhere('status', 'active');
                @endphp
                <tr style="border-bottom:1px solid var(--color-surface-container);">
                    <td style="padding:0.875rem 1.5rem; font-family:monospace; font-size:0.8125rem; font-weight:700; color:var(--color-on-surface);">
                        {{ $student->user->matricule ?? '—' }}
                    </td>
                    <td style="padding:0.875rem 1rem;">
                        <div style="font-weight:700; color:var(--color-on-surface);">
                            {{ $student->user->full_name ?? '—' }}
                        </div>
                        <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">{{ $student->user->email ?? '' }}</div>
                    </td>
                    <td style="padding:0.875rem 1rem; font-size:0.8125rem; color:var(--color-on-surface);">
                        {{ $student->enrollments->pluck('formation.name')->filter()->unique()->implode(', ') ?: '—' }}
                    </td>
                    <td style="padding:0.875rem 1rem;">
                        @if($activeEnroll)
                        <span style="font-size:0.75rem; font-weight:700; padding:0.25rem 0.625rem; border-radius:9999px; background:#ecfdf5; color:#065f46;">
                            {{ $activeEnroll->academicSession->name ?? '—' }}
                        </span>
                        @else
                        <span style="font-size:0.75rem; color:var(--color-on-surface-variant);">Aucune</span>
                        @endif
                    </td>
                    <td style="padding:0.875rem 1.5rem; text-align:center;">
                        <a href="{{ route('direction.students.show', $student) }}"
                           style="display:inline-flex; align-items:center; gap:0.25rem; padding:0.375rem 0.75rem; border-radius:0.5rem; background:var(--color-surface-container-high); color:var(--color-on-surface); text-decoration:none; font-size:0.75rem; font-weight:600;">
                            <span class="material-symbols-outlined" style="font-size:16px;">visibility</span>
                            Voir
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Pagination --}}
@if($students->hasPages())
<div style="margin-top:1.5rem; display:flex; justify-content:center;">
    {{ $students->links() }}
</div>
@endif
@endif

@endsection
