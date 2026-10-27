@extends('layouts.role')
@section('title', $report->titre . ' — Espace Service')
@section('role_label', 'Espace Service')
@section('home_route', route('service.reports.index'))

@section('nav_links')
<a href="{{ route('service.reports.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">assignment</span> Mes Rapports
</a>
<a href="{{ route('service.reports.create') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; color:var(--color-on-surface-variant); text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">add_circle</span> Nouveau Rapport
</a>
@if(auth()->user()->hasRole('responsable_commercial'))
<a href="{{ route('admin.enrollments.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; color:var(--color-on-surface-variant); text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span> Inscriptions
</a>
@elseif(auth()->user()->hasAnyRole(['administrateur', 'direction_daf', 'direction_daa', 'responsable_site', 'responsable_administratif_financier', 'responsable_daa']))
<a href="{{ route('admin.dashboard') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; color:var(--color-on-surface-variant); text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span> Administration
</a>
@endif
<a href="{{ route('contact.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; color:var(--color-on-surface-variant); text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">mail</span> Contact
</a>
@endsection

@section('content')
<div style="max-width:1050px; margin:0 auto; padding:1.5rem;">
    {{-- Retour & Actions --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
        <a href="{{ route('service.reports.index') }}" style="display:inline-flex; align-items:center; gap:0.5rem; color:var(--color-primary-container); text-decoration:none; font-weight:700; font-size:0.875rem;">
            <span class="material-symbols-outlined" style="font-size:20px;">arrow_back</span>
            Retour à la liste de mes rapports
        </a>

        <div style="display:flex; gap:0.75rem; align-items:center;">
            @can('update', $report)
            <a href="{{ route('service.reports.edit', $report) }}" style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-primary-container); text-decoration:none; font-size:0.8125rem; font-weight:700;">
                <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                Modifier
            </a>
            @endcan

            @can('send', $report)
            <form method="POST" action="{{ route('service.reports.send', $report) }}" style="display:inline; margin:0;">
                @csrf
                <button type="submit" onclick="return confirm('Confirmez-vous la transmission de ce rapport à la Direction Générale ? Une fois envoyé, il ne pourra plus être modifié sauf retour de la Direction.');" style="padding:0.625rem 1.25rem; border-radius:0.5rem; background:#16a34a; color:#ffffff; border:none; font-weight:700; font-size:0.8125rem; cursor:pointer; display:inline-flex; align-items:center; gap:0.5rem;">
                    <span class="material-symbols-outlined" style="font-size:18px;">send</span>
                    Envoyer à la Direction
                </button>
            </form>
            @endcan
        </div>
    </div>

    @if(session('success'))
    <div style="padding:1rem; border-radius:0.5rem; background:#ecfdf5; color:#065f46; font-size:0.875rem; margin-bottom:1.5rem; border:1px solid #a7f3d0; display:flex; align-items:center; gap:0.5rem;">
        <span class="material-symbols-outlined">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    @if(session('warning'))
    <div style="padding:1rem; border-radius:0.5rem; background:#fffbeb; color:#92400e; font-size:0.875rem; margin-bottom:1.5rem; border:1px solid #fde68a; display:flex; align-items:center; gap:0.5rem;">
        <span class="material-symbols-outlined">warning</span>
        {{ session('warning') }}
    </div>
    @endif

    {{-- Si rapport retourné --}}
    @if($report->statut === 'retourne')
    <div style="padding:1.25rem; border-radius:0.75rem; background:#fff7ed; border:1px solid #fed7aa; margin-bottom:1.5rem;">
        <div style="display:flex; align-items:center; gap:0.5rem; color:#c2410c; font-weight:700; margin-bottom:0.5rem;">
            <span class="material-symbols-outlined">feedback</span>
            Rapport retourné pour correction par la Direction
        </div>
        <p style="margin:0 0 0.75rem; font-size:0.875rem; color:#9a3412;">
            {{ $report->commentaire_direction }}
        </p>
        @can('update', $report)
        <a href="{{ route('service.reports.edit', $report) }}" style="display:inline-flex; align-items:center; gap:0.375rem; font-size:0.8125rem; font-weight:700; color:#c2410c; text-decoration:underline;">
            Apporter les corrections demandées &rarr;
        </a>
        @endcan
    </div>
    @endif

    {{-- Carte d'en-tête --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:0.75rem; padding:1.75rem; margin-bottom:1.5rem; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1rem; margin-bottom:1.25rem;">
            <div>
                <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem;">
                    <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:800; background:var(--color-primary-container); color:var(--color-on-primary); text-transform:uppercase;">
                        {{ $report->type }}
                    </span>
                    <span style="font-size:0.8125rem; font-weight:600; color:var(--color-on-surface-variant);">
                        Période : {{ $report->periode_debut->format('d/m/Y') }} → {{ $report->periode_fin->format('d/m/Y') }}
                    </span>
                </div>
                <h1 style="font-size:1.5rem; font-weight:800; margin:0 0 0.5rem; color:var(--color-on-surface);">{{ $report->titre }}</h1>
                <div style="font-size:0.875rem; color:var(--color-on-surface-variant);">
                    <strong>Service :</strong> {{ $report->serviceProfile->name }}
                    • <strong>Site :</strong> {{ $report->site->nom ?? 'Tous sites (Siège)' }}
                </div>
            </div>

            <div style="text-align:right;">
                @if($report->statut === 'brouillon')
                    <span style="padding:0.375rem 0.875rem; border-radius:9999px; font-size:0.75rem; font-weight:700; background:#f1f5f9; color:#475569;">
                        Brouillon (Non transmis)
                    </span>
                @elseif($report->statut === 'envoye')
                    <span style="padding:0.375rem 0.875rem; border-radius:9999px; font-size:0.75rem; font-weight:700; background:#eff6ff; color:#1e40af;">
                        Transmis à la Direction
                    </span>
                @elseif($report->statut === 'lu')
                    <span style="padding:0.375rem 0.875rem; border-radius:9999px; font-size:0.75rem; font-weight:700; background:#f0fdf4; color:#166534;">
                        Validé / Consulté par la Direction
                    </span>
                @elseif($report->statut === 'retourne')
                    <span style="padding:0.375rem 0.875rem; border-radius:9999px; font-size:0.75rem; font-weight:700; background:#fef2f2; color:#991b1b;">
                        Retourné pour correction
                    </span>
                @endif
                <div style="font-size:0.75rem; color:var(--color-on-surface-variant); margin-top:0.375rem;">
                    @if($report->envoye_le)
                        Transmis le {{ $report->envoye_le->format('d/m/Y à H:i') }}
                    @else
                        Créé le {{ $report->created_at->format('d/m/Y') }}
                    @endif
                </div>
            </div>
        </div>

        <div style="border-top:1px solid var(--color-surface-container); padding-top:1rem; font-size:0.8125rem; color:var(--color-on-surface-variant);">
            <strong>Rédigé par :</strong> {{ $report->auteur->name }} ({{ $report->auteur->email }})
        </div>
    </div>

    {{-- Contenu --}}
    <div style="display:flex; flex-direction:column; gap:1.5rem;">
        <div style="background:var(--color-surface-container-lowest); border-radius:0.75rem; padding:1.5rem; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <h2 style="font-size:1.125rem; font-weight:800; color:var(--color-on-surface); margin:0 0 0.75rem;">Résumé exécutif</h2>
            <div style="font-size:0.9375rem; line-height:1.6; white-space:pre-line;">{{ $report->resume }}</div>
        </div>

        <div style="background:var(--color-surface-container-lowest); border-radius:0.75rem; padding:1.5rem; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <h2 style="font-size:1.125rem; font-weight:800; color:var(--color-on-surface); margin:0 0 0.75rem;">Activités & Réalisations</h2>
            <div style="font-size:0.9375rem; line-height:1.6; white-space:pre-line;">{{ $report->activites }}</div>
        </div>

        @if(!empty($report->indicateurs) && count($report->indicateurs) > 0)
        <div style="background:var(--color-surface-container-lowest); border-radius:0.75rem; padding:1.5rem; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <h2 style="font-size:1.125rem; font-weight:800; color:var(--color-on-surface); margin:0 0 1rem;">Indicateurs clés</h2>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
                @foreach($report->indicateurs as $indicator)
                <div style="padding:1rem; border-radius:0.5rem; background:var(--color-surface-container-low); border:1px solid var(--color-surface-container);">
                    <div style="font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase;">
                        {{ $indicator['libelle'] ?? 'Indicateur' }}
                    </div>
                    <div style="font-size:1.375rem; font-weight:800; color:var(--color-primary-container); margin-top:0.25rem;">
                        {{ $indicator['valeur'] ?? '—' }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:1.5rem;">
            <div style="background:var(--color-surface-container-lowest); border-radius:0.75rem; padding:1.5rem; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <h2 style="font-size:1.125rem; font-weight:800; color:#b91c1c; margin:0 0 0.75rem;">Difficultés rencontrées</h2>
                <div style="font-size:0.9375rem; line-height:1.6; white-space:pre-line;">{{ $report->difficultes ?: 'Néant.' }}</div>
            </div>

            <div style="background:var(--color-surface-container-lowest); border-radius:0.75rem; padding:1.5rem; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <h2 style="font-size:1.125rem; font-weight:800; color:var(--color-primary-container); margin:0 0 0.75rem;">Besoins & Appuis sollicités</h2>
                <div style="font-size:0.9375rem; line-height:1.6; white-space:pre-line;">{{ $report->besoins ?: 'Néant.' }}</div>
            </div>
        </div>

        @if($report->documents->count() > 0)
        <div style="background:var(--color-surface-container-lowest); border-radius:0.75rem; padding:1.5rem; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <h2 style="font-size:1.125rem; font-weight:800; color:var(--color-on-surface); margin:0 0 1rem;">Pièces jointes annexées</h2>
            <div style="display:flex; flex-direction:column; gap:0.75rem;">
                @foreach($report->documents as $doc)
                <div style="display:flex; justify-content:space-between; align-items:center; padding:0.75rem 1rem; border-radius:0.5rem; background:var(--color-surface-container-low); border:1px solid var(--color-surface-container);">
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <span class="material-symbols-outlined" style="font-size:24px; color:var(--color-primary-container);">description</span>
                        <div>
                            <div style="font-weight:700; font-size:0.875rem;">{{ $doc->original_name ?? $doc->title }}</div>
                            <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">{{ $doc->file_size_human }}</div>
                        </div>
                    </div>
                    <a href="{{ $doc->file_url }}" target="_blank" download style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.375rem 0.75rem; border-radius:0.375rem; background:var(--color-surface-container); color:var(--color-primary-container); text-decoration:none; font-size:0.8125rem; font-weight:700;">
                        <span class="material-symbols-outlined" style="font-size:16px;">download</span>
                        Télécharger
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
