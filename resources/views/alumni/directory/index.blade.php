@extends('layouts.role')

@section('title', 'Tous les alumni')
@section('role_label', 'Annuaire Alumni')
@section('home_route', route('dashboard'))

@section('content')
<main class="student-dashboard-page" style="max-width:1200px; margin:0 auto; padding:1.5rem;">
    <header style="margin-bottom:1.5rem;">
        <h1 style="margin:0; color:var(--color-on-surface); font-size:1.5rem; font-weight:800;">Tous les alumni</h1>
        <p style="margin:0.375rem 0 0; color:var(--color-on-surface-variant); font-size:0.875rem;">
            Découvrez les diplômés qui ont accepté de partager leur parcours.
        </p>
    </header>

    <form method="GET" action="{{ route('alumni.directory.index') }}"
          style="display:flex; align-items:flex-end; flex-wrap:wrap; gap:1rem; margin-bottom:1rem; padding:1rem; background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <label style="display:grid; gap:0.375rem; color:var(--color-on-surface); font-size:0.8125rem; font-weight:600;">
            Formation
            <select name="formation_id" style="min-width:220px; padding:0.625rem; border:1px solid var(--color-outline-variant); border-radius:0.5rem; background:var(--color-surface-container-low); color:var(--color-on-surface);">
                <option value="">Toutes les formations</option>
                @foreach($formations as $formation)
                    <option value="{{ $formation->id }}" @selected((string) $formationId === (string) $formation->id)>{{ $formation->name }}</option>
                @endforeach
            </select>
        </label>
        <label style="display:grid; gap:0.375rem; color:var(--color-on-surface); font-size:0.8125rem; font-weight:600;">
            Année de sortie
            <select name="year" style="min-width:160px; padding:0.625rem; border:1px solid var(--color-outline-variant); border-radius:0.5rem; background:var(--color-surface-container-low); color:var(--color-on-surface);">
                <option value="">Toutes les années</option>
                @foreach($years as $availableYear)
                    <option value="{{ $availableYear }}" @selected((string) $year === (string) $availableYear)>{{ $availableYear }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit" style="padding:0.625rem 1rem; border:0; border-radius:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.8125rem; font-weight:700; cursor:pointer;">
            Filtrer
        </button>
    </form>

    <x-alumni-showcase :alumnis="$alumnis" title="Alumni IFAM" :show-all-link="false" />

    <div style="margin-top:1rem;">{{ $alumnis->links() }}</div>
</main>
@endsection
