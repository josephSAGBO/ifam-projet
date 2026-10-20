@extends('layouts.admin')

@section('title', 'Parents')

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Dossiers Parents
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            {{ $parents->total() }} parent{{ $parents->total() > 1 ? 's' : '' }} enregistré{{ $parents->total() > 1 ? 's' : '' }}
        </p>
    </div>
</div>

<form method="GET" action="{{ route('admin.parents.index') }}"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; margin-bottom:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    <div style="display:grid; grid-template-columns:1fr auto; gap:0.75rem; align-items:end;">

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
                       placeholder="Nom, Prénom, Email..."
                       style="width:100%; box-sizing:border-box; height:40px; padding:0 0.875rem 0 2.5rem; border-radius:0.625rem;
                              border:1.5px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.875rem; color:var(--color-on-surface);"
                       onfocus="this.style.borderColor='var(--color-primary-container)'"
                       onblur="this.style.borderColor='var(--color-outline-variant)'">
            </div>
        </div>

        <div style="display:flex; gap:0.5rem;">
            <button type="submit"
                    style="height:40px; padding:0 1.25rem; border-radius:0.625rem; border:none; cursor:pointer;
                           background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.875rem; font-weight:700;">
                Rechercher
            </button>
            @if(request()->has('search'))
            <a href="{{ route('admin.parents.index') }}"
               style="height:40px; padding:0 1rem; border-radius:0.625rem; display:inline-flex; align-items:center;
                      background:var(--color-surface-container-high); color:var(--color-on-surface); font-size:0.875rem; text-decoration:none;">
                Effacer
            </a>
            @endif
        </div>
    </div>
</form>

<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden;">
    @if($parents->isEmpty())
    <div style="padding:4rem 2rem; text-align:center; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined" style="font-size:48px; opacity:0.25; display:block; margin-bottom:1rem;">family_restroom</span>
        <p style="font-size:0.9375rem; margin:0;">Aucun parent trouvé.</p>
    </div>
    @else
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Parent</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Contact</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Profession</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Enfants rattachés</th>
                    <th style="padding:0.875rem 1rem;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($parents as $parent)
                <tr style="border-bottom:1px solid var(--color-surface-container); transition:background 0.15s;"
                    onmouseover="this.style.background='var(--color-surface-container-low)'"
                    onmouseout="this.style.background=''">
                    <td style="padding:0.875rem 1rem;">
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            <div style="width:38px; height:38px; border-radius:9999px; flex-shrink:0;
                                        background:#eef2ff; color:#4338ca;
                                        display:flex; align-items:center; justify-content:center;">
                                <span style="font-size:0.8125rem; font-weight:700;">{{ $parent->initials }}</span>
                            </div>
                            <div style="font-weight:700; color:var(--color-on-surface);">{{ $parent->full_name }}</div>
                        </div>
                    </td>
                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">
                        <div>{{ $parent->email ?? '—' }}</div>
                        @if($parent->phone)
                        <div style="font-size:0.8125rem;">{{ $parent->phone }}</div>
                        @endif
                    </td>
                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">
                        {{ $parent->parentProfile?->profession ?? '—' }}
                    </td>
                    <td style="padding:0.875rem 1rem;">
                        <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                            @if($parent->children->count() > 0)
                                @foreach($parent->children as $child)
                                    <span style="display:inline-flex; align-items:center; gap:0.25rem; background:var(--color-surface-container); padding:0.25rem 0.5rem; border-radius:0.5rem; font-size:0.75rem;">
                                        <span class="material-symbols-outlined" style="font-size:14px; color:var(--color-on-surface-variant);">face</span>
                                        {{ $child->full_name }}
                                    </span>
                                @endforeach
                            @else
                                <span style="color:var(--color-on-surface-variant); font-size:0.8125rem;">Aucun enfant rattaché</span>
                            @endif
                        </div>
                    </td>
                    <td style="padding:0.875rem 1rem;">
                        <div style="display:flex; align-items:center; gap:0.5rem; justify-content:flex-end;">
                            <a href="{{ route('admin.parents.show', $parent) }}"
                               title="Dossier parent"
                               style="height:34px; padding:0 0.75rem; border-radius:0.5rem; display:inline-flex; align-items:center; gap:0.375rem;
                                      background:var(--color-surface-container); color:var(--color-on-surface); text-decoration:none; font-weight:600; font-size:0.8125rem; transition:all 0.15s;"
                               onmouseover="this.style.background='var(--color-surface-container-high)'"
                               onmouseout="this.style.background='var(--color-surface-container)'">
                                <span class="material-symbols-outlined" style="font-size:18px;">folder_shared</span>
                                Dossier
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($parents->hasPages())
    <div style="padding:1rem 1.25rem; border-top:1px solid var(--color-surface-container); display:flex; align-items:center; justify-content:space-between;">
        <span style="font-size:0.8125rem; color:var(--color-on-surface-variant);">
            Affichage {{ $parents->firstItem() }}–{{ $parents->lastItem() }} sur {{ $parents->total() }}
        </span>
        <div style="display:flex; gap:0.375rem;">
            @if($parents->onFirstPage())
            <span style="padding:0.375rem 0.75rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-on-surface-variant); font-size:0.8125rem; opacity:0.5;">← Préc.</span>
            @else
            <a href="{{ $parents->previousPageUrl() }}"
               style="padding:0.375rem 0.75rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-on-surface); font-size:0.8125rem; text-decoration:none;">← Préc.</a>
            @endif
            @if($parents->hasMorePages())
            <a href="{{ $parents->nextPageUrl() }}"
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
