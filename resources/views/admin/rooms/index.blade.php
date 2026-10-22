@extends('layouts.admin')

@section('title', 'Salles de classe')

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Salles de classe
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Gérez les salles disponibles pour les cours et les examens
        </p>
    </div>
    <a href="{{ route('admin.rooms.create') }}"
       style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem;
              background:var(--color-primary-container); color:var(--color-on-primary); border-radius:0.75rem;
              text-decoration:none; font-size:0.875rem; font-weight:700;
              box-shadow:0 3px 10px rgba(228,0,102,0.3); transition:all 0.2s;"
       onmouseover="this.style.transform='translateY(-1px)'"
       onmouseout="this.style.transform=''">
        <span class="material-symbols-outlined" style="font-size:20px;">add_circle</span>
        Ajouter une salle
    </a>
</div>

@if($isSuperAdmin && $sites->isNotEmpty())
<form method="GET" action="{{ route('admin.rooms.index') }}"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; margin-bottom:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    <div style="display:flex; align-items:end; gap:1rem;">
        <div>
            <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem; text-transform:uppercase; letter-spacing:0.06em;">
                Filtrer par site
            </label>
            <select name="site_id"
                    style="height:40px; padding:0 2rem 0 0.75rem; border-radius:0.625rem; border:1.5px solid var(--color-outline-variant);
                           background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface); cursor:pointer;">
                <option value="">Tous les sites</option>
                @foreach($sites as $site)
                <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                    {{ $site->nom }}
                </option>
                @endforeach
            </select>
        </div>
        <button type="submit"
                style="height:40px; padding:0 1.25rem; border-radius:0.625rem; border:none; cursor:pointer;
                       background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.875rem; font-weight:700;">
            Filtrer
        </button>
        @if(request('site_id'))
        <a href="{{ route('admin.rooms.index') }}"
           style="height:40px; padding:0 1rem; border-radius:0.625rem; display:inline-flex; align-items:center;
                  background:var(--color-surface-container-high); color:var(--color-on-surface); font-size:0.875rem; text-decoration:none;">
            Effacer
        </a>
        @endif
    </div>
</form>
@endif

<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden;">
    @if($rooms->isEmpty())
    <div style="padding:4rem 2rem; text-align:center; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined" style="font-size:48px; opacity:0.25; display:block; margin-bottom:1rem;">meeting_room</span>
        <p style="font-size:0.9375rem; margin:0;">Aucune salle trouvée.</p>
    </div>
    @else
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Nom de la salle</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Site</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Type</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Capacité</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Statut</th>
                    <th style="padding:0.875rem 1rem;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($rooms as $room)
                <tr style="border-bottom:1px solid var(--color-surface-container); transition:background 0.15s;"
                    onmouseover="this.style.background='var(--color-surface-container-low)'"
                    onmouseout="this.style.background=''">
                    
                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface); font-weight:600;">
                        {{ $room->name }}
                    </td>

                    <td style="padding:0.875rem 1rem;">
                        <span style="padding:0.25rem 0.5rem; border-radius:0.375rem; font-size:0.75rem; font-weight:600; background:#f0fdf4; color:#166534; border:1px solid #bbf7d0;">
                            {{ $room->site?->nom ?? '—' }}
                        </span>
                    </td>
                    
                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">
                        {{ $room->type ?? 'Standard' }}
                    </td>

                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">
                        {{ $room->capacity ? $room->capacity . ' places' : '—' }}
                    </td>

                    <td style="padding:0.875rem 1rem;">
                        @if($room->is_active)
                        <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#ecfdf5; color:#065f46; display:inline-flex; align-items:center; gap:0.25rem;">
                            <span style="width:6px;height:6px;border-radius:9999px;background:#059669;"></span>Active
                        </span>
                        @else
                        <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#f3f4f6; color:#6b7280; display:inline-flex; align-items:center; gap:0.25rem;">
                            <span style="width:6px;height:6px;border-radius:9999px;background:#9ca3af;"></span>Inactive
                        </span>
                        @endif
                    </td>

                    <td style="padding:0.875rem 1rem;">
                        <div style="display:flex; align-items:center; gap:0.5rem; justify-content:flex-end;">
                            <a href="{{ route('admin.rooms.edit', $room) }}"
                               title="Modifier"
                               style="width:34px; height:34px; border-radius:0.5rem; display:flex; align-items:center; justify-content:center;
                                      background:var(--color-surface-container); color:var(--color-on-surface-variant); text-decoration:none; transition:all 0.15s;"
                               onmouseover="this.style.background='var(--color-surface-container-high)'"
                               onmouseout="this.style.background='var(--color-surface-container)'">
                                <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                            </a>
                            <form method="POST" action="{{ route('admin.rooms.destroy', $room) }}"
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette salle ?')">
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
    
    @if($rooms->hasPages())
    <div style="padding:1rem 1.25rem; border-top:1px solid var(--color-surface-container); display:flex; align-items:center; justify-content:space-between;">
        <span style="font-size:0.8125rem; color:var(--color-on-surface-variant);">
            Affichage {{ $rooms->firstItem() }}–{{ $rooms->lastItem() }} sur {{ $rooms->total() }}
        </span>
        <div style="display:flex; gap:0.375rem;">
            @if($rooms->onFirstPage())
            <span style="padding:0.375rem 0.75rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-on-surface-variant); font-size:0.8125rem; opacity:0.5;">← Préc.</span>
            @else
            <a href="{{ $rooms->previousPageUrl() }}"
               style="padding:0.375rem 0.75rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-on-surface); font-size:0.8125rem; text-decoration:none;">← Préc.</a>
            @endif
            @if($rooms->hasMorePages())
            <a href="{{ $rooms->nextPageUrl() }}"
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
