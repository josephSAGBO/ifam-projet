@extends('layouts.role')
@section('title', 'Mes Enfants - Espace Parent')
@section('role_label', 'Parent')
@section('home_route', route('parent.dashboard'))

@section('nav_links')
    @include('parent.partials.nav')
@endsection

@section('content')

<div style="margin-bottom:2rem;">
    <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
        Mes Enfants Inscrits à l'IFAM
    </h1>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
        Suivi pédagogique, assiduité et situation financière de vos enfants
    </p>
</div>

@if(empty($childrenData) || count($childrenData) === 0)
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:3.5rem 1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); text-align:center;">
    <span class="material-symbols-outlined" style="font-size:52px; color:var(--color-on-surface-variant); opacity:0.35; margin-bottom:1rem; display:block;">escalator_warning</span>
    <h3 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem;">Aucun enfant rattaché à votre compte</h3>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0 auto; max-width:420px;">
        Si votre enfant est déjà inscrit à l'IFAM, veuillez contacter le service scolarité afin qu'il procède au rattachement de votre compte parent.
    </p>
    <a href="{{ route('contact.index') }}"
       style="display:inline-flex; align-items:center; gap:0.5rem; margin-top:1.5rem; padding:0.625rem 1.25rem; border-radius:0.5rem;
              background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none; font-weight:700; font-size:0.875rem;">
        <span class="material-symbols-outlined" style="font-size:18px;">mail</span>
        Contacter l'administration
    </a>
</div>
@else
<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(360px, 1fr)); gap:1.5rem;">
    @foreach($childrenData as $child)
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); border:1px solid var(--color-surface-container); overflow:hidden;">
        {{-- En-tête profil enfant --}}
        <div style="padding:1.5rem; border-bottom:1px solid var(--color-surface-container); display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:1rem;">
                <div style="width:48px; height:48px; border-radius:50%; background:var(--color-primary-container); color:var(--color-on-primary); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1.125rem;">
                    {{ $child['user']->initials }}
                </div>
                <div>
                    <h3 style="font-size:1.0625rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
                        {{ $child['user']->full_name }}
                    </h3>
                    <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                        Matricule : <strong>{{ $child['user']->matricule ?? 'N/A' }}</strong>
                    </div>
                </div>
            </div>

            <span style="font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; padding:0.25rem 0.5rem; border-radius:9999px; background:var(--color-surface-container-high); color:var(--color-on-surface);">
                {{ $child['relationship'] }}
            </span>
        </div>

        {{-- Cursus actuel --}}
        <div style="padding:1.25rem 1.5rem; background:var(--color-surface-container-low); border-bottom:1px solid var(--color-surface-container);">
            <div style="font-size:0.75rem; color:var(--color-on-surface-variant); margin-bottom:0.25rem;">Formation & Session</div>
            @if($child['currentEnrollment'])
                <div style="font-weight:700; color:var(--color-on-surface); font-size:0.9375rem;">
                    {{ $child['currentEnrollment']->formation->name ?? 'Formation' }}
                </div>
                <div style="font-size:0.8125rem; color:var(--color-primary-container); font-weight:600;">
                    Session {{ $child['currentEnrollment']->academicSession->name ?? '' }}
                </div>
            @else
                <div style="font-size:0.875rem; color:var(--color-on-surface-variant); font-style:italic;">
                    Aucune inscription active cette année
                </div>
            @endif
        </div>

        {{-- Indicateurs Clés --}}
        <div style="padding:1.5rem; display:grid; grid-template-columns:repeat(auto-fit, minmax(115px, 1fr)); gap:1rem; text-align:center;">
            {{-- Moyenne --}}
            <div style="padding:0.75rem; border-radius:0.75rem; background:var(--color-surface-container-low);">
                <div style="font-size:0.6875rem; font-weight:700; text-transform:uppercase; color:var(--color-on-surface-variant); margin-bottom:0.25rem;">Moyenne</div>
                <div style="font-size:1.25rem; font-weight:800; color:{{ ($child['generalAverage'] !== null && $child['generalAverage'] >= 10) ? '#059669' : '#e40066' }};">
                    {{ $child['generalAverage'] !== null ? number_format($child['generalAverage'], 2, ',', ' ') : '—' }}
                </div>
                <div style="font-size:0.6875rem; color:var(--color-on-surface-variant);">/ 20</div>
            </div>

            {{-- Présence --}}
            <div style="padding:0.75rem; border-radius:0.75rem; background:var(--color-surface-container-low);">
                <div style="font-size:0.6875rem; font-weight:700; text-transform:uppercase; color:var(--color-on-surface-variant); margin-bottom:0.25rem;">Assiduité</div>
                <div style="font-size:1.25rem; font-weight:800; color:var(--color-on-surface);">
                    {{ $child['attendanceRate'] !== null ? $child['attendanceRate'] . '%' : '—' }}
                </div>
                <div style="font-size:0.6875rem; color:var(--color-on-surface-variant);">présence</div>
            </div>

            {{-- Paiements --}}
            <div style="padding:0.75rem; border-radius:0.75rem; background:var(--color-surface-container-low);">
                <div style="font-size:0.6875rem; font-weight:700; text-transform:uppercase; color:var(--color-on-surface-variant); margin-bottom:0.25rem;">Total payé</div>
                <div style="font-size:1.125rem; font-weight:800; color:#059669;">
                    {{ number_format($child['totalPaid'], 0, ',', ' ') }}
                </div>
                <div style="font-size:0.6875rem; color:var(--color-on-surface-variant);">FCFA</div>
            </div>

            {{-- Solde --}}
            <div style="padding:0.75rem; border-radius:0.75rem; background:var(--color-surface-container-low);">
                <div style="font-size:0.6875rem; font-weight:700; text-transform:uppercase; color:var(--color-on-surface-variant); margin-bottom:0.25rem;">Solde dû</div>
                <div style="font-size:1.125rem; font-weight:800; color:{{ $child['balance'] > 0 ? '#e40066' : '#059669' }};">
                    {{ number_format($child['balance'], 0, ',', ' ') }}
                </div>
                <div style="font-size:0.6875rem; color:var(--color-on-surface-variant);">FCFA</div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection
