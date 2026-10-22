@extends('layouts.admin')

@section('title', 'Services')

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Services de l'Institut
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Chaque service est obligatoirement rattaché à un département institutionnel
        </p>
    </div>
    <a href="{{ route('admin.services.create') }}"
       style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem;
              background:var(--color-primary-container); color:var(--color-on-primary); border-radius:0.75rem;
              text-decoration:none; font-size:0.875rem; font-weight:700;
              box-shadow:0 3px 10px rgba(228,0,102,0.3); transition:all 0.2s;"
       onmouseover="this.style.transform='translateY(-1px)'"
       onmouseout="this.style.transform=''">
        <span class="material-symbols-outlined" style="font-size:20px;">add_circle</span>
        Nouveau service
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

{{-- Filtre par département --}}
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; margin-bottom:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    <form method="GET" action="{{ route('admin.services.index') }}" style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
        <label for="filter-department" style="font-size:0.875rem; font-weight:600; color:var(--color-on-surface);">Filtrer par département :</label>
        <select id="filter-department" name="department_id" onchange="this.form.submit()"
                style="height:40px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant);
                       background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
            <option value="">Tous les départements</option>
            @foreach($departments as $dept)
            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                {{ $dept->name }}
            </option>
            @endforeach
        </select>
        @if(request()->filled('department_id'))
        <a href="{{ route('admin.services.index') }}" style="font-size:0.8125rem; color:var(--color-primary-container); text-decoration:none; font-weight:600;">Réinitialiser</a>
        @endif
    </form>
</div>

<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden;">
    @if($services->isEmpty())
    <div style="padding:4rem 2rem; text-align:center; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined" style="font-size:48px; opacity:0.25; display:block; margin-bottom:1rem;">hub</span>
        <p style="font-size:0.9375rem; margin:0;">Aucun service trouvé.</p>
    </div>
    @else
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Service / Code</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Département de rattachement</th>
                    <th style="text-align:center; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Utilisateurs</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Statut</th>
                    <th style="padding:0.875rem 1rem; text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($services as $service)
                <tr style="border-bottom:1px solid var(--color-surface-container); transition:background 0.15s;"
                    onmouseover="this.style.background='var(--color-surface-container-low)'"
                    onmouseout="this.style.background=''">

                    <td style="padding:0.875rem 1rem;">
                        <a href="{{ route('admin.services.show', $service) }}"
                           style="font-weight:600; color:var(--color-on-surface); text-decoration:none;"
                           onmouseover="this.style.textDecoration='underline'"
                           onmouseout="this.style.textDecoration='none'">{{ $service->name }}</a>
                        <div style="font-size:0.75rem; color:var(--color-on-surface-variant); font-family:monospace; margin-top:2px;">{{ $service->code }}</div>
                    </td>

                    <td style="padding:0.875rem 1rem;">
                        @if($service->department)
                        <a href="{{ route('admin.departments.show', $service->department) }}"
                           style="color:var(--color-primary-container); text-decoration:none; font-weight:600;">
                            {{ $service->department->name }}
                        </a>
                        @else
                        <span style="color:#b91c1c; font-style:italic;">Non rattaché</span>
                        @endif
                    </td>

                    <td style="padding:0.875rem 1rem; text-align:center;">
                        <span style="display:inline-block; padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.75rem; font-weight:700; background:var(--color-surface-container-high); color:var(--color-on-surface);">
                            {{ $service->users_count }}
                        </span>
                    </td>

                    <td style="padding:0.875rem 1rem;">
                        @if($service->is_active)
                        <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#ecfdf5; color:#065f46; display:inline-flex; align-items:center; gap:0.25rem;">
                            <span style="width:6px;height:6px;border-radius:9999px;background:#059669;"></span>Actif
                        </span>
                        @else
                        <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#f3f4f6; color:#6b7280; display:inline-flex; align-items:center; gap:0.25rem;">
                            <span style="width:6px;height:6px;border-radius:9999px;background:#9ca3af;"></span>Inactif
                        </span>
                        @endif
                    </td>

                    <td style="padding:0.875rem 1rem; text-align:right;">
                        <div style="display:inline-flex; align-items:center; gap:0.5rem;">
                            <form method="POST" action="{{ route('admin.services.toggle-active', $service) }}" style="display:inline; margin:0;">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        title="{{ $service->is_active ? 'Désactiver' : 'Activer' }}"
                                        style="background:none; border:none; cursor:pointer; padding:0.25rem; border-radius:0.375rem; color:var(--color-on-surface-variant); display:flex; align-items:center;"
                                        onmouseover="this.style.color='var(--color-primary)'"
                                        onmouseout="this.style.color='var(--color-on-surface-variant)'">
                                    <span class="material-symbols-outlined" style="font-size:20px;">
                                        {{ $service->is_active ? 'toggle_on' : 'toggle_off' }}
                                    </span>
                                </button>
                            </form>

                            <a href="{{ route('admin.services.edit', $service) }}"
                               title="Modifier"
                               style="display:inline-flex; align-items:center; padding:0.25rem; border-radius:0.375rem; color:var(--color-on-surface-variant); text-decoration:none;"
                               onmouseover="this.style.color='var(--color-primary)'"
                               onmouseout="this.style.color='var(--color-on-surface-variant)'">
                                <span class="material-symbols-outlined" style="font-size:20px;">edit</span>
                            </a>

                            <a href="{{ route('admin.services.show', $service) }}"
                               title="Consulter"
                               style="display:inline-flex; align-items:center; padding:0.25rem; border-radius:0.375rem; color:var(--color-on-surface-variant); text-decoration:none;"
                               onmouseover="this.style.color='var(--color-primary)'"
                               onmouseout="this.style.color='var(--color-on-surface-variant)'">
                                <span class="material-symbols-outlined" style="font-size:20px;">arrow_forward</span>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($services->hasPages())
    <div style="padding:1rem 1.25rem; border-top:1px solid var(--color-outline-variant);">
        {{ $services->links() }}
    </div>
    @endif
    @endif
</div>

@endsection
