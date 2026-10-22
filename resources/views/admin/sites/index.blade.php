@extends('layouts.admin')

@section('title', 'Sites IFAM')

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Sites IFAM
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Gérez les sites et campus de l'institution
        </p>
    </div>
    <a href="{{ route('admin.sites.create') }}"
       style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem;
              background:var(--color-primary-container); color:var(--color-on-primary); border-radius:0.75rem;
              text-decoration:none; font-size:0.875rem; font-weight:700;
              box-shadow:0 3px 10px rgba(228,0,102,0.3); transition:all 0.2s;"
       onmouseover="this.style.transform='translateY(-1px)'"
       onmouseout="this.style.transform=''">
        <span class="material-symbols-outlined" style="font-size:20px;">add_circle</span>
        Ajouter un site
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
    <a href="{{ route('admin.sites.index') }}" style="margin-left:0.5rem; text-decoration:underline; color:#991b1b; font-weight:700;">Désactiver à la place</a>
</div>
@endif

<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden;">
    @if($sites->isEmpty())
    <div style="padding:4rem 2rem; text-align:center; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined" style="font-size:48px; opacity:0.25; display:block; margin-bottom:1rem;">location_city</span>
        <p style="font-size:0.9375rem; margin:0;">Aucun site trouvé.</p>
    </div>
    @else
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Nom / Ville</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Sessions</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Inscriptions</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Salles</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">État</th>
                    <th style="padding:0.875rem 1rem;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($sites as $site)
                <tr style="border-bottom:1px solid var(--color-surface-container); transition:background 0.15s;"
                    onmouseover="this.style.background='var(--color-surface-container-low)'"
                    onmouseout="this.style.background=''">

                    <td style="padding:0.875rem 1rem;">
                        <a href="{{ route('admin.sites.show', $site) }}"
                           style="font-weight:600; color:var(--color-on-surface); text-decoration:none;"
                           onmouseover="this.style.textDecoration='underline'"
                           onmouseout="this.style.textDecoration='none'">{{ $site->nom }}</a>
                        <div style="font-size:0.75rem; color:var(--color-on-surface-variant); margin-top:2px;">{{ $site->ville }}</div>
                    </td>

                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">
                        {{ $site->academic_sessions_count }}
                    </td>

                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">
                        {{ $site->enrollments_count }}
                    </td>

                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">
                        {{ $site->rooms_count }}
                    </td>

                    <td style="padding:0.875rem 1rem;">
                        @if($site->est_actif)
                        <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#ecfdf5; color:#065f46; display:inline-flex; align-items:center; gap:0.25rem;">
                            <span style="width:6px;height:6px;border-radius:9999px;background:#059669;"></span>Actif
                        </span>
                        @else
                        <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#f3f4f6; color:#6b7280; display:inline-flex; align-items:center; gap:0.25rem;">
                            <span style="width:6px;height:6px;border-radius:9999px;background:#9ca3af;"></span>Inactif
                        </span>
                        @endif
                    </td>

                    <td style="padding:0.875rem 1rem;">
                        <div style="display:flex; align-items:center; gap:0.5rem; justify-content:flex-end;">
                            <a href="{{ route('admin.sites.show', $site) }}"
                               title="Voir"
                               style="width:34px; height:34px; border-radius:0.5rem; display:flex; align-items:center; justify-content:center;
                                      background:var(--color-surface-container); color:var(--color-on-surface-variant); text-decoration:none; transition:all 0.15s;"
                               onmouseover="this.style.background='var(--color-surface-container-high)'"
                               onmouseout="this.style.background='var(--color-surface-container)'">
                                <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                            </a>
                            <a href="{{ route('admin.sites.edit', $site) }}"
                               title="Modifier"
                               style="width:34px; height:34px; border-radius:0.5rem; display:flex; align-items:center; justify-content:center;
                                      background:var(--color-surface-container); color:var(--color-on-surface-variant); text-decoration:none; transition:all 0.15s;"
                               onmouseover="this.style.background='var(--color-surface-container-high)'"
                               onmouseout="this.style.background='var(--color-surface-container)'">
                                <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                            </a>
                            {{-- Toggle actif/inactif --}}
                            <form method="POST" action="{{ route('admin.sites.toggle-active', $site) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        title="{{ $site->est_actif ? 'Désactiver' : 'Activer' }}"
                                        style="width:34px; height:34px; border-radius:0.5rem; border:none; cursor:pointer; transition:all 0.15s;
                                               background:var(--color-surface-container); color:{{ $site->est_actif ? '#d97706' : '#059669' }};"
                                        onmouseover="this.style.background='{{ $site->est_actif ? '#fffbeb' : '#ecfdf5' }}'"
                                        onmouseout="this.style.background='var(--color-surface-container)'">
                                    <span class="material-symbols-outlined" style="font-size:18px;">{{ $site->est_actif ? 'toggle_on' : 'toggle_off' }}</span>
                                </button>
                            </form>
                            {{-- Suppression --}}
                            <form method="POST" action="{{ route('admin.sites.destroy', $site) }}"
                                  onsubmit="return confirm('Supprimer le site « {{ $site->nom }} » ? Cette action est irréversible.')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Supprimer"
                                        style="width:34px; height:34px; border-radius:0.5rem; border:none; cursor:pointer;
                                               background:var(--color-surface-container); color:#dc2626; transition:all 0.15s;"
                                        onmouseover="this.style.background='#fef2f2'"
                                        onmouseout="this.style.background='var(--color-surface-container)'">
                                    <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($sites->hasPages())
    <div style="padding:1rem 1.25rem; border-top:1px solid var(--color-surface-container); display:flex; align-items:center; justify-content:space-between;">
        <span style="font-size:0.8125rem; color:var(--color-on-surface-variant);">
            Affichage {{ $sites->firstItem() }}–{{ $sites->lastItem() }} sur {{ $sites->total() }}
        </span>
        <div style="display:flex; gap:0.375rem;">
            @if($sites->onFirstPage())
            <span style="padding:0.375rem 0.75rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-on-surface-variant); font-size:0.8125rem; opacity:0.5;">← Préc.</span>
            @else
            <a href="{{ $sites->previousPageUrl() }}" style="padding:0.375rem 0.75rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-on-surface); font-size:0.8125rem; text-decoration:none;">← Préc.</a>
            @endif
            @if($sites->hasMorePages())
            <a href="{{ $sites->nextPageUrl() }}" style="padding:0.375rem 0.75rem; border-radius:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.8125rem; font-weight:600; text-decoration:none;">Suiv. →</a>
            @else
            <span style="padding:0.375rem 0.75rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-on-surface-variant); font-size:0.8125rem; opacity:0.5;">Suiv. →</span>
            @endif
        </div>
    </div>
    @endif

    @endif
</div>
@endsection
