@extends('layouts.role')
@section('title', 'Rapports des Services — Direction')
@section('role_label', 'Direction')
@section('home_route', route('direction.dashboard'))

@section('nav_links')
<a href="{{ route('direction.dashboard') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; {{ request()->routeIs('direction.dashboard') ? 'background:var(--color-primary-container); color:var(--color-on-primary);' : 'color:var(--color-on-surface-variant);' }} text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">dashboard</span> Dashboard
</a>
<a href="{{ route('direction.students.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; {{ request()->routeIs('direction.students*') ? 'background:var(--color-primary-container); color:var(--color-on-primary);' : 'color:var(--color-on-surface-variant);' }} text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">school</span> Étudiants
</a>
<a href="{{ route('direction.finance.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; {{ request()->routeIs('direction.finance*') ? 'background:var(--color-primary-container); color:var(--color-on-primary);' : 'color:var(--color-on-surface-variant);' }} text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">account_balance_wallet</span> Finances
</a>
<a href="{{ route('direction.alumni-posts.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; {{ request()->routeIs('direction.alumni-posts*') ? 'background:var(--color-primary-container); color:var(--color-on-primary);' : 'color:var(--color-on-surface-variant);' }} text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">auto_awesome</span> Alumni disponibles
</a>
<a href="{{ route('direction.reports.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; {{ request()->routeIs('direction.reports*') ? 'background:var(--color-primary-container); color:var(--color-on-primary);' : 'color:var(--color-on-surface-variant);' }} text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">assignment</span> Rapports
</a>
<a href="{{ route('contact.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; {{ request()->routeIs('contact.*') ? 'background:var(--color-primary-container); color:var(--color-on-primary);' : 'color:var(--color-on-surface-variant);' }} text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">mail</span> Contact
</a>
@endsection

@section('content')
<div style="max-width:1400px; margin:0 auto; padding:1.5rem;">
    {{-- En-tête --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
        <div>
            <h1 style="font-size:1.5rem; font-weight:800; color:var(--color-on-surface); margin:0;">Rapports d'activité des services</h1>
            <p style="margin:0.25rem 0 0; color:var(--color-on-surface-variant); font-size:0.875rem;">
                Suivi des comptes-rendus périodiques transmis par les départements et services IFAM.
            </p>
        </div>
        <div style="display:flex; gap:0.75rem;">
            <a href="{{ route('direction.reports.missing') }}" style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1rem; border-radius:0.5rem; background:#fef2f2; color:#991b1b; text-decoration:none; font-size:0.8125rem; font-weight:700; border:1px solid #fecaca;">
                <span class="material-symbols-outlined" style="font-size:18px;">notification_important</span>
                Rapports manquants
            </a>
        </div>
    </div>

    {{-- Filtres --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:0.75rem; padding:1.25rem; margin-bottom:1.5rem; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <form method="GET" action="{{ route('direction.reports.index') }}" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1rem; align-items:end;">
            <div>
                <label style="display:block; font-size:0.75rem; font-weight:700; margin-bottom:0.375rem; color:var(--color-on-surface-variant);">Département</label>
                <select name="department_id" style="width:100%; padding:0.5rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); font-size:0.8125rem;">
                    <option value="">Tous les départements</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display:block; font-size:0.75rem; font-weight:700; margin-bottom:0.375rem; color:var(--color-on-surface-variant);">Service</label>
                <select name="service_profile_id" style="width:100%; padding:0.5rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); font-size:0.8125rem;">
                    <option value="">Tous les services</option>
                    @foreach($services as $serv)
                        <option value="{{ $serv->id }}" {{ request('service_profile_id') == $serv->id ? 'selected' : '' }}>{{ $serv->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display:block; font-size:0.75rem; font-weight:700; margin-bottom:0.375rem; color:var(--color-on-surface-variant);">Site</label>
                <select name="site_id" style="width:100%; padding:0.5rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); font-size:0.8125rem;">
                    <option value="">Tous les sites</option>
                    @foreach($sites as $site)
                        <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>{{ $site->nom }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display:block; font-size:0.75rem; font-weight:700; margin-bottom:0.375rem; color:var(--color-on-surface-variant);">Type</label>
                <select name="type" style="width:100%; padding:0.5rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); font-size:0.8125rem;">
                    <option value="">Tous types</option>
                    <option value="mensuel" {{ request('type') == 'mensuel' ? 'selected' : '' }}>Mensuel</option>
                    <option value="hebdomadaire" {{ request('type') == 'hebdomadaire' ? 'selected' : '' }}>Hebdomadaire</option>
                    <option value="ponctuel" {{ request('type') == 'ponctuel' ? 'selected' : '' }}>Ponctuel</option>
                </select>
            </div>

            <div>
                <label style="display:block; font-size:0.75rem; font-weight:700; margin-bottom:0.375rem; color:var(--color-on-surface-variant);">Statut</label>
                <select name="statut" style="width:100%; padding:0.5rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); font-size:0.8125rem;">
                    <option value="">Tous reçus</option>
                    <option value="envoye" {{ request('statut') == 'envoye' ? 'selected' : '' }}>Nouveau (Non lu)</option>
                    <option value="lu" {{ request('statut') == 'lu' ? 'selected' : '' }}>Lu par la Direction</option>
                    <option value="retourne" {{ request('statut') == 'retourne' ? 'selected' : '' }}>Retourné pour correction</option>
                </select>
            </div>

            <div style="display:flex; gap:0.5rem;">
                <button type="submit" style="padding:0.5rem 1rem; border-radius:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); border:none; font-weight:700; font-size:0.8125rem; cursor:pointer;">
                    Filtrer
                </button>
                <a href="{{ route('direction.reports.index') }}" style="padding:0.5rem 0.75rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-on-surface-variant); text-decoration:none; font-weight:600; font-size:0.8125rem; display:flex; align-items:center;">
                    Effacer
                </a>
            </div>
        </form>
    </div>

    {{-- Tableau des rapports --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:0.75rem; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="border-bottom:1px solid var(--color-surface-container); background:var(--color-surface-container-low); text-align:left;">
                    <th style="padding:0.875rem 1rem; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase;">Titre / Objet</th>
                    <th style="padding:0.875rem 1rem; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase;">Service & Département</th>
                    <th style="padding:0.875rem 1rem; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase;">Site</th>
                    <th style="padding:0.875rem 1rem; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase;">Période</th>
                    <th style="padding:0.875rem 1rem; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase;">Transmis le</th>
                    <th style="padding:0.875rem 1rem; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase;">Statut</th>
                    <th style="padding:0.875rem 1rem; text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                <tr style="border-bottom:1px solid var(--color-surface-container); transition:background 0.15s;" onmouseover="this.style.background='var(--color-surface-container-low)'" onmouseout="this.style.background=''">
                    <td style="padding:0.875rem 1rem;">
                        <a href="{{ route('direction.reports.show', $report) }}" style="font-weight:700; color:var(--color-primary-container); text-decoration:none;">
                            {{ $report->titre }}
                        </a>
                        <div style="font-size:0.75rem; color:var(--color-on-surface-variant); margin-top:2px;">
                            Par {{ $report->auteur->name ?? 'Agent' }} • {{ ucfirst($report->type) }}
                        </div>
                    </td>
                    <td style="padding:0.875rem 1rem;">
                        <div style="font-weight:600;">{{ $report->serviceProfile->name }}</div>
                        <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">{{ $report->serviceProfile->department->name ?? 'N/A' }}</div>
                    </td>
                    <td style="padding:0.875rem 1rem;">
                        <span style="font-size:0.8125rem; font-weight:600;">{{ $report->site->nom ?? 'Tous sites (Siège)' }}</span>
                    </td>
                    <td style="padding:0.875rem 1rem; font-size:0.8125rem;">
                        {{ $report->periode_debut->format('d/m/Y') }} → {{ $report->periode_fin->format('d/m/Y') }}
                    </td>
                    <td style="padding:0.875rem 1rem; font-size:0.8125rem; color:var(--color-on-surface-variant);">
                        {{ $report->envoye_le ? $report->envoye_le->format('d/m/Y H:i') : '—' }}
                    </td>
                    <td style="padding:0.875rem 1rem;">
                        @if($report->statut === 'envoye')
                            <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#eff6ff; color:#1e40af;">
                                Non lu
                            </span>
                        @elseif($report->statut === 'lu')
                            <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#f0fdf4; color:#166534;">
                                Lu
                            </span>
                        @elseif($report->statut === 'retourne')
                            <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#fef2f2; color:#991b1b;">
                                Retourné
                            </span>
                        @endif
                    </td>
                    <td style="padding:0.875rem 1rem; text-align:right;">
                        <div style="display:inline-flex; gap:0.5rem;">
                            <a href="{{ route('direction.reports.show', $report) }}" title="Consulter" style="display:inline-flex; align-items:center; padding:0.375rem; border-radius:0.375rem; background:var(--color-surface-container); color:var(--color-on-surface); text-decoration:none;">
                                <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                            </a>
                            <a href="{{ route('direction.reports.pdf', $report) }}" title="Exporter PDF" style="display:inline-flex; align-items:center; padding:0.375rem; border-radius:0.375rem; background:var(--color-surface-container); color:var(--color-primary-container); text-decoration:none;">
                                <span class="material-symbols-outlined" style="font-size:18px;">picture_as_pdf</span>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding:2.5rem; text-align:center; color:var(--color-on-surface-variant);">
                        <span class="material-symbols-outlined" style="font-size:36px; display:block; margin-bottom:0.5rem; opacity:0.5;">assignment_late</span>
                        Aucun rapport transmis ne correspond aux critères sélectionnés.
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
