@extends('layouts.role')

@section('title', 'Mes bulletins - Espace Étudiant')
@section('role_label', 'Étudiant')
@section('home_route', route('student.dashboard'))

@section('nav_links')
    @include('student.partials.nav')
@endsection

@section('content')
<div style="margin-bottom:1.5rem;">
    <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
        Mes bulletins officiels
    </h1>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
        Consultez les bulletins publiés par l’administration pour vos formations.
    </p>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:1rem;">
    @forelse($publications as $publication)
        <article style="background:var(--color-surface-container-lowest); border:1px solid var(--color-outline-variant); border-radius:1rem; padding:1.25rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem;">
                <div>
                    <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.375rem;">
                        {{ $publication->formation->name }}
                    </h2>
                    <p style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin:0;">
                        {{ $publication->academicSession->name }}
                    </p>
                </div>
                <span style="padding:0.25rem 0.5rem; border-radius:9999px; background:#ecfdf5; color:#065f46; font-size:0.6875rem; font-weight:700;">
                    Publié
                </span>
            </div>
            <p style="font-size:0.75rem; color:var(--color-on-surface-variant); margin:1rem 0;">
                Publié le {{ $publication->published_at?->format('d/m/Y') ?? '—' }}
            </p>
            <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                <a href="{{ route('student.bulletins.show', $publication) }}"
                   style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.625rem 0.875rem; border-radius:0.625rem; background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none; font-size:0.8125rem; font-weight:700;">
                    Voir le bulletin
                    <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                </a>
                <a href="{{ route('student.bulletins.pdf', $publication) }}"
                   target="_blank"
                   title="Télécharger le bulletin en PDF"
                   style="display:inline-flex; align-items:center; gap:0.25rem; padding:0.625rem 0.75rem; border-radius:0.625rem; background:var(--color-surface-container-high); color:var(--color-on-surface); text-decoration:none; font-size:0.8125rem; font-weight:700; border:1px solid var(--color-outline-variant);">
                    <span class="material-symbols-outlined" style="font-size:18px;">download</span>
                    PDF
                </a>
            </div>
        </article>
    @empty
        <div style="grid-column:1/-1; padding:3rem 1.5rem; border:1px solid var(--color-outline-variant); border-radius:1rem; background:var(--color-surface-container-lowest); text-align:center; color:var(--color-on-surface-variant);">
            <span class="material-symbols-outlined" style="font-size:42px; opacity:0.35; display:block; margin-bottom:0.75rem;">description</span>
            Aucun bulletin n’a encore été publié pour votre compte.
        </div>
    @endforelse
</div>
@endsection
