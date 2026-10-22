@extends('layouts.role')
@section('title', $report->titre . ' — Direction')
@section('role_label', 'Direction')
@section('home_route', route('direction.dashboard'))

@section('nav_links')
<a href="{{ route('direction.dashboard') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; color:var(--color-on-surface-variant); text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">dashboard</span> Dashboard
</a>
<a href="{{ route('direction.students.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; color:var(--color-on-surface-variant); text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">school</span> Étudiants
</a>
<a href="{{ route('direction.finance.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; color:var(--color-on-surface-variant); text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">account_balance_wallet</span> Finances
</a>
<a href="{{ route('direction.alumni-posts.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; color:var(--color-on-surface-variant); text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">auto_awesome</span> Alumni disponibles
</a>
<a href="{{ route('direction.reports.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">assignment</span> Rapports
</a>
<a href="{{ route('contact.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; color:var(--color-on-surface-variant); text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">mail</span> Contact
</a>
@endsection

@section('content')
<div style="max-width:1100px; margin:0 auto; padding:1.5rem;">
    {{-- Retour & Actions --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
        <a href="{{ route('direction.reports.index') }}" style="display:inline-flex; align-items:center; gap:0.5rem; color:var(--color-primary-container); text-decoration:none; font-weight:700; font-size:0.875rem;">
            <span class="material-symbols-outlined" style="font-size:20px;">arrow_back</span>
            Retour à la liste des rapports
        </a>
        <div style="display:flex; gap:0.75rem;">
            <a href="{{ route('direction.reports.pdf', $report) }}" style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-primary-container); text-decoration:none; font-size:0.8125rem; font-weight:700;">
                <span class="material-symbols-outlined" style="font-size:18px;">picture_as_pdf</span>
                Télécharger PDF
            </a>
        </div>
    </div>

    {{-- Messages de feedback --}}
    @if(session('success'))
    <div style="padding:1rem; border-radius:0.5rem; background:#ecfdf5; color:#065f46; font-size:0.875rem; margin-bottom:1.5rem; border:1px solid #a7f3d0; display:flex; align-items:center; gap:0.5rem;">
        <span class="material-symbols-outlined">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    {{-- Alerte si rapport retourné --}}
    @if($report->statut === 'retourne')
    <div style="padding:1.25rem; border-radius:0.75rem; background:#fef2f2; border:1px solid #fecaca; margin-bottom:1.5rem;">
        <div style="display:flex; align-items:center; gap:0.5rem; color:#991b1b; font-weight:700; margin-bottom:0.5rem;">
            <span class="material-symbols-outlined">warning</span>
            Rapport retourné pour correction au responsable
        </div>
        <p style="margin:0; font-size:0.875rem; color:#7f1d1d;">
            <strong>Commentaire transmis :</strong> {{ $report->commentaire_direction }}
        </p>
    </div>
    @endif

    {{-- Carte d'en-tête du rapport --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:0.75rem; padding:1.75rem; margin-bottom:1.5rem; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1rem; margin-bottom:1.25rem;">
            <div>
                <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem;">
                    <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:800; background:var(--color-primary-container); color:var(--color-on-primary); text-transform:uppercase;">
                        {{ $report->type }}
                    </span>
                    <span style="font-size:0.8125rem; font-weight:600; color:var(--color-on-surface-variant);">
                        Période du {{ $report->periode_debut->format('d/m/Y') }} au {{ $report->periode_fin->format('d/m/Y') }}
                    </span>
                </div>
                <h1 style="font-size:1.5rem; font-weight:800; margin:0 0 0.5rem; color:var(--color-on-surface);">{{ $report->titre }}</h1>
                <div style="font-size:0.875rem; color:var(--color-on-surface-variant);">
                    <strong>Service :</strong> {{ $report->serviceProfile->name }} ({{ $report->serviceProfile->department->name ?? 'Département' }})
                    • <strong>Site :</strong> {{ $report->site->nom ?? 'Tous sites (Siège)' }}
                </div>
            </div>

            <div style="text-align:right;">
                @if($report->statut === 'envoye')
                    <span style="padding:0.375rem 0.875rem; border-radius:9999px; font-size:0.75rem; font-weight:700; background:#eff6ff; color:#1e40af;">
                        Nouveau (Non lu)
                    </span>
                @elseif($report->statut === 'lu')
                    <span style="padding:0.375rem 0.875rem; border-radius:9999px; font-size:0.75rem; font-weight:700; background:#f0fdf4; color:#166534;">
                        Lu par la Direction
                    </span>
                @elseif($report->statut === 'retourne')
                    <span style="padding:0.375rem 0.875rem; border-radius:9999px; font-size:0.75rem; font-weight:700; background:#fef2f2; color:#991b1b;">
                        Retourné pour correction
                    </span>
                @endif
                <div style="font-size:0.75rem; color:var(--color-on-surface-variant); margin-top:0.375rem;">
                    Transmis le {{ $report->envoye_le ? $report->envoye_le->format('d/m/Y à H:i') : '—' }}
                </div>
                @if($report->lu_le)
                <div style="font-size:0.75rem; color:#166534; margin-top:0.25rem;">
                    Lu le {{ $report->lu_le->format('d/m/Y à H:i') }} par {{ $report->luParUser->name ?? 'Direction' }}
                </div>
                @endif
            </div>
        </div>

        <div style="border-top:1px solid var(--color-surface-container); padding-top:1rem; font-size:0.8125rem; color:var(--color-on-surface-variant);">
            <strong>Rédigé par :</strong> {{ $report->auteur->name }} ({{ $report->auteur->email }})
        </div>
    </div>

    {{-- Contenu du rapport --}}
    <div style="display:flex; flex-direction:column; gap:1.5rem; margin-bottom:2rem;">
        {{-- Résumé exécutif --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:0.75rem; padding:1.5rem; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <h2 style="font-size:1.125rem; font-weight:800; color:var(--color-on-surface); margin:0 0 0.75rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="color:var(--color-primary-container);">summarize</span>
                Résumé synthétique
            </h2>
            <div style="font-size:0.9375rem; line-height:1.6; color:var(--color-on-surface); white-space:pre-line;">
                {{ $report->resume }}
            </div>
        </div>

        {{-- Activités réalisées --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:0.75rem; padding:1.5rem; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <h2 style="font-size:1.125rem; font-weight:800; color:var(--color-on-surface); margin:0 0 0.75rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="color:var(--color-primary-container);">task_alt</span>
                Activités & Réalisations de la période
            </h2>
            <div style="font-size:0.9375rem; line-height:1.6; color:var(--color-on-surface); white-space:pre-line;">
                {{ $report->activites }}
            </div>
        </div>

        {{-- Indicateurs clés --}}
        @if(!empty($report->indicateurs) && count($report->indicateurs) > 0)
        <div style="background:var(--color-surface-container-lowest); border-radius:0.75rem; padding:1.5rem; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <h2 style="font-size:1.125rem; font-weight:800; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="color:var(--color-primary-container);">analytics</span>
                Indicateurs clés de performance
            </h2>
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

        {{-- Difficultés & Besoins (2 colonnes) --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:1.5rem;">
            <div style="background:var(--color-surface-container-lowest); border-radius:0.75rem; padding:1.5rem; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <h2 style="font-size:1.125rem; font-weight:800; color:#b91c1c; margin:0 0 0.75rem; display:flex; align-items:center; gap:0.5rem;">
                    <span class="material-symbols-outlined">report_problem</span>
                    Difficultés rencontrées
                </h2>
                <div style="font-size:0.9375rem; line-height:1.6; color:var(--color-on-surface); white-space:pre-line;">
                    {{ $report->difficultes ?: 'Aucune difficulté majeure signalée pour cette période.' }}
                </div>
            </div>

            <div style="background:var(--color-surface-container-lowest); border-radius:0.75rem; padding:1.5rem; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <h2 style="font-size:1.125rem; font-weight:800; color:var(--color-primary-container); margin:0 0 0.75rem; display:flex; align-items:center; gap:0.5rem;">
                    <span class="material-symbols-outlined">help_outline</span>
                    Besoins & Appuis sollicités
                </h2>
                <div style="font-size:0.9375rem; line-height:1.6; color:var(--color-on-surface); white-space:pre-line;">
                    {{ $report->besoins ?: 'Aucun besoin spécifique exprimé pour cette période.' }}
                </div>
            </div>
        </div>

        {{-- Pièces jointes --}}
        @if($report->documents->count() > 0)
        <div style="background:var(--color-surface-container-lowest); border-radius:0.75rem; padding:1.5rem; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <h2 style="font-size:1.125rem; font-weight:800; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="color:var(--color-primary-container);">attachment</span>
                Pièces jointes & Documents justificatifs ({{ $report->documents->count() }})
            </h2>
            <div style="display:flex; flex-direction:column; gap:0.75rem;">
                @foreach($report->documents as $doc)
                <div style="display:flex; justify-content:space-between; align-items:center; padding:0.75rem 1rem; border-radius:0.5rem; background:var(--color-surface-container-low); border:1px solid var(--color-surface-container);">
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <span class="material-symbols-outlined" style="font-size:24px; color:var(--color-primary-container);">description</span>
                        <div>
                            <div style="font-weight:700; font-size:0.875rem;">{{ $doc->original_name ?? $doc->title }}</div>
                            <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                                {{ $doc->file_size_human }} • Ajouté par {{ $doc->uploadedBy->name ?? 'Agent' }}
                            </div>
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

    {{-- Formulaire de retour pour correction si pas déjà retourné --}}
    @can('returnForCorrection', $report)
    <div style="background:#fff7ed; border-radius:0.75rem; padding:1.5rem; border:1px solid #ffedd5; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <h3 style="font-size:1.125rem; font-weight:800; color:#c2410c; margin:0 0 0.5rem; display:flex; align-items:center; gap:0.5rem;">
            <span class="material-symbols-outlined">reply</span>
            Retourner le rapport pour correction
        </h3>
        <p style="margin:0 0 1rem; font-size:0.875rem; color:#9a3412;">
            Si des informations sont incomplètes ou nécessitent des précisions, vous pouvez retourner ce rapport au responsable du service avec vos instructions.
        </p>

        <form method="POST" action="{{ route('direction.reports.return', $report) }}">
            @csrf
            <div style="margin-bottom:1rem;">
                <label style="display:block; font-size:0.8125rem; font-weight:700; margin-bottom:0.375rem; color:#9a3412;">
                    Commentaire / Précisions demandées <span style="color:#ef4444;">*</span>
                </label>
                <textarea name="commentaire" rows="4" required style="width:100%; padding:0.75rem; border-radius:0.5rem; border:1px solid #fed7aa; font-size:0.875rem; resize:vertical;" placeholder="Indiquez clairement au service les corrections et compléments attendus..."></textarea>
                @error('commentaire')
                    <div style="color:#ef4444; font-size:0.75rem; margin-top:0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" onclick="return confirm('Confirmez-vous le retour de ce rapport pour correction ?');" style="padding:0.625rem 1.25rem; border-radius:0.5rem; background:#c2410c; color:#ffffff; border:none; font-weight:700; font-size:0.875rem; cursor:pointer; display:inline-flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="font-size:18px;">send</span>
                Retourner pour correction
            </button>
        </form>
    </div>
    @endcan
</div>
@endsection
