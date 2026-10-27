@extends('layouts.role')
@section('title', 'Mes Rapports de Service')
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
<div style="max-width:1300px; margin:0 auto; padding:1.5rem;">
    {{-- En-tête --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
        <div>
            <h1 style="font-size:1.5rem; font-weight:800; color:var(--color-on-surface); margin:0;">Rapports d'activité de mon service</h1>
            <p style="margin:0.25rem 0 0; color:var(--color-on-surface-variant); font-size:0.875rem;">
                Préparez, rédigez et transmettez vos comptes-rendus d'activité à la Direction Générale.
            </p>
        </div>
        <div>
            <a href="{{ route('service.reports.create') }}" style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem; border-radius:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none; font-size:0.875rem; font-weight:700; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <span class="material-symbols-outlined" style="font-size:20px;">add</span>
                Rédiger un rapport
            </a>
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

    {{-- Tableau des rapports du service --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:0.75rem; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="border-bottom:1px solid var(--color-surface-container); background:var(--color-surface-container-low); text-align:left;">
                    <th style="padding:0.875rem 1rem; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase;">Titre / Objet</th>
                    <th style="padding:0.875rem 1rem; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase;">Service & Site</th>
                    <th style="padding:0.875rem 1rem; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase;">Période</th>
                    <th style="padding:0.875rem 1rem; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase;">Rédacteur</th>
                    <th style="padding:0.875rem 1rem; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase;">Statut</th>
                    <th style="padding:0.875rem 1rem; text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                <tr style="border-bottom:1px solid var(--color-surface-container); transition:background 0.15s;" onmouseover="this.style.background='var(--color-surface-container-low)'" onmouseout="this.style.background=''">
                    <td style="padding:0.875rem 1rem;">
                        <a href="{{ route('service.reports.show', $report) }}" style="font-weight:700; color:var(--color-primary-container); text-decoration:none;">
                            {{ $report->titre }}
                        </a>
                        <div style="font-size:0.75rem; color:var(--color-on-surface-variant); margin-top:2px;">
                            Type : <strong>{{ ucfirst($report->type) }}</strong>
                        </div>
                    </td>
                    <td style="padding:0.875rem 1rem;">
                        <div style="font-weight:600;">{{ $report->serviceProfile->name }}</div>
                        <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                            {{ $report->site->nom ?? 'Tous sites' }}
                        </div>
                    </td>
                    <td style="padding:0.875rem 1rem; font-size:0.8125rem;">
                        {{ $report->periode_debut->format('d/m/Y') }} → {{ $report->periode_fin->format('d/m/Y') }}
                    </td>
                    <td style="padding:0.875rem 1rem; font-size:0.8125rem;">
                        {{ $report->auteur->name }}
                        @if($report->auteur_id === auth()->id())
                            <span style="font-size:0.6875rem; font-weight:700; color:var(--color-primary-container);">(Moi)</span>
                        @endif
                    </td>
                    <td style="padding:0.875rem 1rem;">
                        @if($report->statut === 'brouillon')
                            <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#f1f5f9; color:#475569;">
                                Brouillon
                            </span>
                        @elseif($report->statut === 'envoye')
                            <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#eff6ff; color:#1e40af;">
                                Envoyé à la Direction
                            </span>
                        @elseif($report->statut === 'lu')
                            <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#f0fdf4; color:#166534;">
                                Lu par la Direction
                            </span>
                        @elseif($report->statut === 'retourne')
                            <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#fef2f2; color:#991b1b;">
                                À corriger
                            </span>
                        @endif
                    </td>
                    <td style="padding:0.875rem 1rem; text-align:right;">
                        <div style="display:inline-flex; gap:0.5rem; align-items:center;">
                            <a href="{{ route('service.reports.show', $report) }}" title="Consulter" style="display:inline-flex; align-items:center; padding:0.375rem; border-radius:0.375rem; background:var(--color-surface-container); color:var(--color-on-surface); text-decoration:none;">
                                <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                            </a>

                            @can('update', $report)
                            <a href="{{ route('service.reports.edit', $report) }}" title="Modifier" style="display:inline-flex; align-items:center; padding:0.375rem; border-radius:0.375rem; background:var(--color-surface-container); color:var(--color-primary-container); text-decoration:none;">
                                <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                            </a>
                            @endcan

                            @can('send', $report)
                            <form method="POST" action="{{ route('service.reports.send', $report) }}" style="display:inline; margin:0;">
                                @csrf
                                <button type="submit" onclick="return confirm('Êtes-vous certain de vouloir transmettre ce rapport à la Direction Générale ? Une fois envoyé, il ne sera plus modifiable.');" title="Envoyer à la Direction" style="display:inline-flex; align-items:center; padding:0.375rem 0.625rem; border-radius:0.375rem; background:#16a34a; color:#ffffff; border:none; font-size:0.75rem; font-weight:700; cursor:pointer; gap:0.25rem;">
                                    <span class="material-symbols-outlined" style="font-size:16px;">send</span>
                                    Envoyer
                                </button>
                            </form>
                            @endcan

                            @can('delete', $report)
                            <form method="POST" action="{{ route('service.reports.destroy', $report) }}" style="display:inline; margin:0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Confirmez-vous la suppression de ce brouillon ?');" title="Supprimer" style="display:inline-flex; align-items:center; padding:0.375rem; border-radius:0.375rem; background:#fee2e2; color:#b91c1c; border:none; cursor:pointer;">
                                    <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding:2.5rem; text-align:center; color:var(--color-on-surface-variant);">
                        <span class="material-symbols-outlined" style="font-size:36px; display:block; margin-bottom:0.5rem; opacity:0.5;">assignment</span>
                        Aucun rapport rédigé pour le moment.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:1.25rem;">
        {{ $reports->links() }}
    </div>
</div>
@endsection
