@extends('layouts.admin')

@section('title', 'Étudiants')

@section('content')

{{-- En-tête --}}
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Dossiers Étudiants
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            {{ $students->total() }} étudiant{{ $students->total() > 1 ? 's' : '' }} inscrit{{ $students->total() > 1 ? 's' : '' }}
        </p>
    </div>
</div>

{{-- Filtres --}}
<form method="GET" action="{{ route('admin.students.index') }}"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; margin-bottom:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    <div style="display:grid; grid-template-columns:1fr @if($sites->isNotEmpty()) auto @endif auto; gap:0.75rem; align-items:end;">

        {{-- Recherche --}}
        <div>
            <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem; text-transform:uppercase; letter-spacing:0.06em;">
                Recherche par nom ou matricule
            </label>
            <div style="position:relative;">
                <span class="material-symbols-outlined"
                      style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); font-size:18px; color:var(--color-on-surface-variant); pointer-events:none;">
                    search
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Ex: ETU-2026-000001, Doe..."
                       style="width:100%; box-sizing:border-box; height:40px; padding:0 0.875rem 0 2.5rem; border-radius:0.625rem;
                              border:1.5px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.875rem; color:var(--color-on-surface);"
                       onfocus="this.style.borderColor='var(--color-primary-container)'"
                       onblur="this.style.borderColor='var(--color-outline-variant)'">
            </div>
        </div>

        @if($sites->isNotEmpty())
        <div>
            <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem; text-transform:uppercase; letter-spacing:0.06em;">
                Site
            </label>
            <select name="site_id"
                    style="height:40px; padding:0 2rem 0 0.75rem; border-radius:0.625rem; border:1.5px solid var(--color-outline-variant);
                           background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface); cursor:pointer;">
                <option value="">Tous les sites</option>
                @foreach($sites as $site)
                <option value="{{ $site->id }}" {{ (string) $selectedSiteId === (string) $site->id ? 'selected' : '' }}>
                    {{ $site->nom }}
                </option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Bouton filtrer --}}
        <div style="display:flex; gap:0.5rem;">
            <button type="submit"
                    style="height:40px; padding:0 1.25rem; border-radius:0.625rem; border:none; cursor:pointer;
                           background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.875rem; font-weight:700;">
                Filtrer
            </button>
            @if(request()->hasAny(['search', 'site_id']))
            <a href="{{ route('admin.students.index') }}"
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
    @if($students->isEmpty())
    <div style="padding:4rem 2rem; text-align:center; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined" style="font-size:48px; opacity:0.25; display:block; margin-bottom:1rem;">school</span>
        <p style="font-size:0.9375rem; margin:0;">Aucun étudiant trouvé.</p>
    </div>
    @else
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Matricule</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Étudiant</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Site</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Contact</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Année d'entrée</th>
                    <th style="padding:0.875rem 1rem;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                <tr style="border-bottom:1px solid var(--color-surface-container); transition:background 0.15s;"
                    onmouseover="this.style.background='var(--color-surface-container-low)'"
                    onmouseout="this.style.background=''">

                    <td style="padding:0.875rem 1rem; font-family:monospace; font-weight:600; color:var(--color-primary-container);">
                        {{ $student->matricule }}
                    </td>

                    <td style="padding:0.875rem 1rem;">
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            <div style="width:38px; height:38px; border-radius:9999px; flex-shrink:0;
                                        background:var(--color-surface-container-high);
                                        display:flex; align-items:center; justify-content:center;">
                                <span style="font-size:0.8125rem; font-weight:700; color:var(--color-on-surface-variant);">
                                    {{ $student->initials }}
                                </span>
                            </div>
                            <div>
                                <div style="font-weight:700; color:var(--color-on-surface);">{{ $student->full_name }}</div>
                                <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">{{ $student->gender ?? '-' }} • {{ $student->birth_date ? $student->birth_date->age . ' ans' : '-' }}</div>
                            </div>
                        </div>
                    </td>

                    <td style="padding:0.875rem 1rem;">
                        @php
                            $studentSites = $student->sites->concat($student->studentProfile?->enrollments->pluck('site')->filter() ?? collect())->unique('id');
                        @endphp
                        @if($studentSites->isNotEmpty())
                            <div style="display:flex; flex-wrap:wrap; gap:0.25rem;">
                                @foreach($studentSites as $sSite)
                                    <span style="padding:0.25rem 0.5rem; border-radius:0.375rem; font-size:0.75rem; font-weight:600; background:#f0fdf4; color:#166534; border:1px solid #bbf7d0;">
                                        {{ $sSite->nom }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span style="color:var(--color-on-surface-variant); font-size:0.8125rem;">—</span>
                        @endif
                    </td>

                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">
                        <div>{{ $student->email ?? '—' }}</div>
                        @if($student->phone)
                        <div style="font-size:0.8125rem;">{{ $student->phone }}</div>
                        @endif
                    </td>

                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">
                        {{ $student->studentProfile?->entry_year ?? '—' }}
                    </td>

                    <td style="padding:0.875rem 1rem;">
                        <div style="display:flex; align-items:center; gap:0.5rem; justify-content:flex-end;">
                            <a href="{{ route('admin.students.show', $student) }}"
                               title="Dossier complet"
                               style="height:34px; padding:0 0.75rem; border-radius:0.5rem; display:inline-flex; align-items:center; gap:0.375rem;
                                      background:var(--color-surface-container); color:var(--color-on-surface); text-decoration:none; font-weight:600; font-size:0.8125rem; transition:all 0.15s;"
                               onmouseover="this.style.background='var(--color-surface-container-high)'"
                               onmouseout="this.style.background='var(--color-surface-container)'">
                                <span class="material-symbols-outlined" style="font-size:18px;">folder_open</span>
                                Dossier
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($students->hasPages())
    <div style="padding:1rem 1.25rem; border-top:1px solid var(--color-surface-container); display:flex; align-items:center; justify-content:space-between;">
        <span style="font-size:0.8125rem; color:var(--color-on-surface-variant);">
            Affichage {{ $students->firstItem() }}–{{ $students->lastItem() }} sur {{ $students->total() }}
        </span>
        <div style="display:flex; gap:0.375rem;">
            @if($students->onFirstPage())
            <span style="padding:0.375rem 0.75rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-on-surface-variant); font-size:0.8125rem; opacity:0.5;">← Préc.</span>
            @else
            <a href="{{ $students->previousPageUrl() }}"
               style="padding:0.375rem 0.75rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-on-surface); font-size:0.8125rem; text-decoration:none;">← Préc.</a>
            @endif
            @if($students->hasMorePages())
            <a href="{{ $students->nextPageUrl() }}"
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
