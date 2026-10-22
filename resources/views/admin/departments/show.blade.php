@extends('layouts.admin')

@section('title', 'Département : ' . $department->name)

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div style="display:flex; align-items:center; gap:1rem;">
        <a href="{{ route('admin.departments.index') }}"
           style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
                  display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
                  transition:all 0.15s;"
           onmouseover="this.style.background='var(--color-surface-container-high)'"
           onmouseout="this.style.background='var(--color-surface-container)'">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
                {{ $department->name }}
            </h1>
            <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
                Code : <span style="font-family:monospace; font-weight:600;">{{ $department->code }}</span>
            </p>
        </div>
    </div>
    <div style="display:flex; gap:0.75rem;">
        <a href="{{ route('admin.services.create', ['department_id' => $department->id]) }}"
           style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem;
                  background:var(--color-primary-container); color:var(--color-on-primary); border-radius:0.75rem;
                  text-decoration:none; font-size:0.875rem; font-weight:700;">
            <span class="material-symbols-outlined" style="font-size:20px;">add_circle</span>
            Rattacher un service
        </a>
        <a href="{{ route('admin.departments.edit', $department) }}"
           style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1rem;
                  background:var(--color-surface-container-high); color:var(--color-on-surface); border-radius:0.75rem;
                  text-decoration:none; font-size:0.875rem; font-weight:600;">
            <span class="material-symbols-outlined" style="font-size:20px;">edit</span>
            Modifier
        </a>
    </div>
</div>

<div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; margin-bottom:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1.5rem;">
        <div>
            <div style="font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant); margin-bottom:0.25rem;">Statut</div>
            <div>
                @if($department->is_active)
                <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.75rem; font-weight:700; background:#ecfdf5; color:#065f46;">Actif</span>
                @else
                <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.75rem; font-weight:700; background:#f3f4f6; color:#6b7280;">Inactif</span>
                @endif
            </div>
        </div>
        <div>
            <div style="font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant); margin-bottom:0.25rem;">Services rattachés</div>
            <div style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface);">{{ $department->services->count() }}</div>
        </div>
        <div>
            <div style="font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant); margin-bottom:0.25rem;">Description</div>
            <div style="font-size:0.875rem; color:var(--color-on-surface);">{{ $department->description ?? 'Aucune description' }}</div>
        </div>
    </div>
</div>

<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden;">
    <div style="padding:1.25rem 1.5rem; border-bottom:1px solid var(--color-surface-container); display:flex; align-items:center; justify-content:space-between;">
        <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0;">
            Services composant ce département
        </h2>
    </div>

    @if($department->services->isEmpty())
    <div style="padding:3rem 2rem; text-align:center; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined" style="font-size:40px; opacity:0.25; display:block; margin-bottom:0.75rem;">hub</span>
        <p style="font-size:0.9375rem; margin:0;">Aucun service rattaché à ce département pour le moment.</p>
    </div>
    @else
    <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
        <thead>
            <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Service</th>
                <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Code</th>
                <th style="text-align:center; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Membres</th>
                <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Statut</th>
                <th style="padding:0.875rem 1rem; text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($department->services as $service)
            <tr style="border-bottom:1px solid var(--color-surface-container);">
                <td style="padding:0.875rem 1rem; font-weight:600; color:var(--color-on-surface);">
                    {{ $service->name }}
                </td>
                <td style="padding:0.875rem 1rem; font-family:monospace; color:var(--color-on-surface-variant);">
                    {{ $service->code }}
                </td>
                <td style="padding:0.875rem 1rem; text-align:center;">
                    <span style="padding:0.25rem 0.5rem; border-radius:9999px; font-size:0.75rem; font-weight:700; background:var(--color-surface-container-high);">
                        {{ $service->users_count }} utilisateur(s)
                    </span>
                </td>
                <td style="padding:0.875rem 1rem;">
                    @if($service->is_active)
                    <span style="color:#065f46; font-size:0.75rem; font-weight:700;">Actif</span>
                    @else
                    <span style="color:#6b7280; font-size:0.75rem; font-weight:700;">Inactif</span>
                    @endif
                </td>
                <td style="padding:0.875rem 1rem; text-align:right;">
                    <a href="{{ route('admin.services.edit', $service) }}"
                       style="display:inline-flex; align-items:center; padding:0.25rem; color:var(--color-on-surface-variant);">
                        <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@endsection
