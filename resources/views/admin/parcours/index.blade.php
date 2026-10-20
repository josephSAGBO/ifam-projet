@extends('layouts.admin')

@section('title', 'Parcours de formation')

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Parcours de formation
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Gérez les parcours (Licence 1, Licence 2, Master…) regroupant plusieurs formations.
        </p>
    </div>
    <a href="{{ route('admin.parcours.create') }}"
       style="height:44px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.25rem; border-radius:0.75rem;
              background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none;
              font-size:0.9375rem; font-weight:700; box-shadow:0 3px 10px rgba(228,0,102,0.25);">
        <span class="material-symbols-outlined" style="font-size:20px;">add</span>
        Nouveau parcours
    </a>
</div>

@if(session('success'))
    <div style="padding:0.875rem 1.25rem; border-radius:0.75rem; background:var(--color-success-container,#d1fae5); color:var(--color-on-success-container,#065f46); margin-bottom:1.5rem; font-size:0.875rem; font-weight:500;">
        {{ session('success') }}
    </div>
@endif

<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:var(--color-surface-container-low);">
                <th style="padding:0.875rem 1.25rem; text-align:left; font-size:0.8125rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.04em;">Nom</th>
                <th style="padding:0.875rem 1.25rem; text-align:left; font-size:0.8125rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.04em;">Description</th>
                <th style="padding:0.875rem 1.25rem; text-align:center; font-size:0.8125rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.04em;">Formations</th>
                <th style="padding:0.875rem 1.25rem; text-align:right; font-size:0.8125rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.04em;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($parcours as $item)
                <tr style="border-top:1px solid var(--color-surface-container);">
                    <td style="padding:1rem 1.25rem; font-size:0.9375rem; font-weight:600; color:var(--color-on-surface);">
                        {{ $item->nom }}
                    </td>
                    <td style="padding:1rem 1.25rem; font-size:0.875rem; color:var(--color-on-surface-variant); max-width:300px;">
                        {{ Str::limit($item->description, 80, '…') ?: '—' }}
                    </td>
                    <td style="padding:1rem 1.25rem; text-align:center;">
                        <span style="display:inline-flex; align-items:center; justify-content:center; min-width:28px; height:28px; padding:0 0.5rem;
                                     border-radius:999px; background:var(--color-surface-container); font-size:0.8125rem; font-weight:700; color:var(--color-on-surface);">
                            {{ $item->formations_count }}
                        </span>
                    </td>
                    <td style="padding:1rem 1.25rem; text-align:right;">
                        <div style="display:inline-flex; gap:0.5rem;">
                            <a href="{{ route('admin.parcours.edit', $item) }}"
                               style="height:34px; display:inline-flex; align-items:center; gap:0.375rem; padding:0 0.875rem; border-radius:0.625rem;
                                      background:var(--color-surface-container-high); color:var(--color-on-surface); text-decoration:none;
                                      font-size:0.8125rem; font-weight:600;">
                                <span class="material-symbols-outlined" style="font-size:16px;">edit</span>
                                Modifier
                            </a>
                            <form method="POST" action="{{ route('admin.parcours.destroy', $item) }}"
                                  onsubmit="return confirm('Supprimer ce parcours ? Les formations associées perdront leur parcours.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        style="height:34px; display:inline-flex; align-items:center; gap:0.375rem; padding:0 0.875rem; border-radius:0.625rem;
                                               background:transparent; border:1.5px solid var(--color-error); color:var(--color-error);
                                               font-size:0.8125rem; font-weight:600; cursor:pointer;">
                                    <span class="material-symbols-outlined" style="font-size:16px;">delete</span>
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="padding:3rem; text-align:center; color:var(--color-on-surface-variant); font-size:0.9375rem;">
                        Aucun parcours créé.
                        <a href="{{ route('admin.parcours.create') }}" style="color:var(--color-primary); font-weight:600; text-decoration:none;">Créer le premier</a>.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($parcours->hasPages())
    <div style="margin-top:1.5rem;">
        {{ $parcours->links() }}
    </div>
@endif

@endsection
