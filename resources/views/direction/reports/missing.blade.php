@extends('layouts.role')
@section('title', 'Rapports manquants — Direction')
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
<div style="max-width:1300px; margin:0 auto; padding:1.5rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
        <div>
            <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem;">
                <a href="{{ route('direction.reports.index') }}" style="color:var(--color-on-surface-variant); text-decoration:none; font-size:0.8125rem; font-weight:600;">
                    Rapports
                </a>
                <span style="color:var(--color-on-surface-variant);">/</span>
                <span style="font-size:0.8125rem; font-weight:700; color:var(--color-primary-container);">Rapports manquants</span>
            </div>
            <h1 style="font-size:1.5rem; font-weight:800; color:var(--color-on-surface); margin:0;">Services en défaut de rapport</h1>
            <p style="margin:0.25rem 0 0; color:var(--color-on-surface-variant); font-size:0.875rem;">
                Liste des services n'ayant transmis aucun rapport mensuel pour la période sélectionnée.
            </p>
        </div>

        {{-- Sélecteur de période / mois --}}
        <form method="GET" action="{{ route('direction.reports.missing') }}" style="display:flex; align-items:center; gap:0.5rem; background:var(--color-surface-container-lowest); padding:0.5rem 0.75rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant);">
            <label style="font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant);">Mois analysé :</label>
            <input type="month" name="month" value="{{ $month }}" onchange="this.form.submit()" style="border:1px solid var(--color-outline-variant); border-radius:0.375rem; padding:0.375rem 0.5rem; font-size:0.8125rem; font-weight:600;">
            <noscript><button type="submit" style="padding:0.375rem 0.75rem; border-radius:0.375rem; background:var(--color-primary-container); color:var(--color-on-primary); border:none; font-weight:700; font-size:0.75rem;">OK</button></noscript>
        </form>
    </div>

    {{-- Synthèse --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
        <div style="background:var(--color-surface-container-lowest); border-radius:0.75rem; padding:1.25rem; border-left:4px solid #ef4444; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:var(--color-on-surface-variant);">Services sans rapport</div>
            <div style="font-size:1.75rem; font-weight:800; color:#dc2626; margin-top:0.25rem;">{{ count($missingServices) }}</div>
            <div style="font-size:0.75rem; color:var(--color-on-surface-variant); margin-top:0.25rem;">
                Période : {{ \Carbon\Carbon::parse($periodeDebut)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($periodeFin)->format('d/m/Y') }}
            </div>
        </div>
        <div style="background:var(--color-surface-container-lowest); border-radius:0.75rem; padding:1.25rem; border-left:4px solid var(--color-primary-container); box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:var(--color-on-surface-variant);">Échéance mensuelle légale</div>
            <div style="font-size:1.25rem; font-weight:800; color:var(--color-on-surface); margin-top:0.25rem;">Le 5 du mois suivant</div>
            <div style="font-size:0.75rem; color:var(--color-on-surface-variant); margin-top:0.25rem;">
                Fixée dans <code>config/reports.php</code>
            </div>
        </div>
    </div>

    {{-- Tableau des services manquants --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:0.75rem; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="border-bottom:1px solid var(--color-surface-container); background:var(--color-surface-container-low); text-align:left;">
                    <th style="padding:0.875rem 1rem; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase;">Département</th>
                    <th style="padding:0.875rem 1rem; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase;">Service en retard</th>
                    <th style="padding:0.875rem 1rem; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase;">Site</th>
                    <th style="padding:0.875rem 1rem; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase;">Responsable(s) identifié(s)</th>
                    <th style="padding:0.875rem 1rem; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase;">Statut</th>
                </tr>
            </thead>
            <tbody>
                @forelse($missingServices as $item)
                <tr style="border-bottom:1px solid var(--color-surface-container); transition:background 0.15s;" onmouseover="this.style.background='var(--color-surface-container-low)'" onmouseout="this.style.background=''">
                    <td style="padding:0.875rem 1rem; font-weight:600; color:var(--color-on-surface);">
                        {{ $item['department']->name }}
                    </td>
                    <td style="padding:0.875rem 1rem;">
                        <span style="font-weight:700; color:#b91c1c;">{{ $item['service']->name }}</span>
                        <div style="font-size:0.75rem; color:var(--color-on-surface-variant); font-family:monospace;">{{ $item['service']->code }}</div>
                    </td>
                    <td style="padding:0.875rem 1rem;">
                        <span style="font-size:0.8125rem; font-weight:600;">
                            {{ $item['site'] ? $item['site']->nom : 'Tous sites (Siège)' }}
                        </span>
                    </td>
                    <td style="padding:0.875rem 1rem;">
                        @if($item['responsables']->isNotEmpty())
                            @foreach($item['responsables'] as $resp)
                                <div style="font-size:0.8125rem; font-weight:600;">{{ $resp->name }}</div>
                                <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">{{ $resp->email }}</div>
                            @endforeach
                        @else
                            <span style="font-style:italic; font-size:0.75rem; color:var(--color-on-surface-variant);">Aucun responsable assigné</span>
                        @endif
                    </td>
                    <td style="padding:0.875rem 1rem;">
                        <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:800; background:#fef2f2; color:#dc2626; border:1px solid #fecaca; display:inline-flex; align-items:center; gap:0.25rem;">
                            <span style="width:6px; height:6px; border-radius:50%; background:#dc2626;"></span>
                            Non transmis
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding:2.5rem; text-align:center; color:#166534; background:#f0fdf4;">
                        <span class="material-symbols-outlined" style="font-size:36px; display:block; margin-bottom:0.5rem;">check_circle</span>
                        <strong>Tous les services ont transmis leur rapport pour cette période ! Félicitations.</strong>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
