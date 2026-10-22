@extends('layouts.role')
@section('title', 'Finances — Direction')
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
<a href="{{ route('contact.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; {{ request()->routeIs('contact.*') ? 'background:var(--color-primary-container); color:var(--color-on-primary);' : 'color:var(--color-on-surface-variant);' }} text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">mail</span> Contact
</a>
@endsection

@section('content')

<style>
    @media print {
        .role-topbar,
        .finance-report-filters,
        .finance-report-actions {
            display: none !important;
        }

        .role-main-content {
            max-width: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .finance-report-card {
            box-shadow: none !important;
            break-inside: avoid;
        }
    }
</style>

<div style="margin-bottom:2rem;">
    <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
        Tableau de bord Financier
    </h1>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
        Consultation uniquement · Données en temps réel
    </p>
</div>

<div class="finance-report-filters" style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; margin-bottom:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
<form method="GET" action="{{ route('direction.finance.index') }}"
      style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 200px), 1fr)); gap:0.75rem; align-items:end;">
    <div>
        <label for="session_id" style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Session académique</label>
        <select id="session_id" name="session_id" style="width:100%; min-height:42px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); color:var(--color-on-surface);">
            <option value="">Toutes les sessions</option>
            @foreach($sessions as $session)
                <option value="{{ $session->id }}" {{ (string) $sessionId === (string) $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="formation_id" style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Formation</label>
        <select id="formation_id" name="formation_id" style="width:100%; min-height:42px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); color:var(--color-on-surface);">
            <option value="">Toutes les formations</option>
            @foreach($formations as $formation)
                <option value="{{ $formation->id }}" {{ (string) $formationId === (string) $formation->id ? 'selected' : '' }}>{{ $formation->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="date_from" style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Du</label>
        <input id="date_from" type="date" name="date_from" value="{{ $dateFrom }}" style="width:100%; box-sizing:border-box; min-height:42px; padding:0 0.75rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); color:var(--color-on-surface);">
    </div>
    <div>
        <label for="date_to" style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Au</label>
        <input id="date_to" type="date" name="date_to" value="{{ $dateTo }}" style="width:100%; box-sizing:border-box; min-height:42px; padding:0 0.75rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); color:var(--color-on-surface);">
    </div>
    <div class="finance-report-actions" style="display:flex; align-items:center; gap:0.75rem;">
        <button type="submit" style="min-height:42px; padding:0 1.25rem; border-radius:0.5rem; border:none; background:var(--color-primary-container); color:var(--color-on-primary); font-weight:700; cursor:pointer;">Générer le point</button>
    </div>
    @if($sessionId || $formationId || request()->hasAny(['date_from', 'date_to']))
        <a class="finance-report-actions" href="{{ route('direction.finance.index') }}" style="min-height:42px; display:inline-flex; align-items:center; color:var(--color-on-surface-variant); font-size:0.8125rem; font-weight:600;">Effacer</a>
    @endif
</form>
</div>

<div class="finance-report-card" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem; margin-bottom:1rem; padding:1rem 1.25rem; border:1px solid var(--color-outline-variant); border-radius:0.75rem; background:var(--color-surface-container-lowest);">
    <div>
        <div style="font-size:0.9375rem; font-weight:700; color:var(--color-on-surface);">Point financier</div>
        <div style="font-size:0.8125rem; color:var(--color-on-surface-variant);">
            Session : {{ $selectedSession?->name ?? 'Toutes' }} · Formation : {{ $selectedFormation?->name ?? 'Toutes' }} ·
            Période : {{ $dateFrom ? \Illuminate\Support\Carbon::parse($dateFrom)->format('d/m/Y') : 'Début non précisé' }}
            au {{ $dateTo ? \Illuminate\Support\Carbon::parse($dateTo)->format('d/m/Y') : 'Aujourd’hui' }}
        </div>
        <div style="font-size:0.75rem; color:var(--color-on-surface-variant); margin-top:0.25rem;">
            Les dépenses sont filtrées par période ; elles ne sont pas rattachées à une session ou à une formation.
        </div>
    </div>
    <button class="finance-report-actions" type="button" onclick="window.print()" style="min-height:40px; padding:0 1rem; border:0; border-radius:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); font-weight:700; cursor:pointer;">
        Imprimer le point financier
    </button>
</div>

{{-- KPIs financiers --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1.25rem; margin-bottom:2rem;">
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); border-left:4px solid #059669;">
        <div style="font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-on-surface-variant); margin-bottom:0.5rem;">Revenus encaissés</div>
        <div style="font-size:1.75rem; font-weight:800; font-family:var(--font-display); color:#059669; margin-bottom:0.25rem;">
            {{ number_format($totalRevenue, 0, ',', ' ') }} <span style="font-size:1rem;">FCFA</span>
        </div>
        <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">Paiements liés aux inscriptions</div>
    </div>

    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); border-left:4px solid #e11d48;">
        <div style="font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-on-surface-variant); margin-bottom:0.5rem;">Dépenses totales</div>
        <div style="font-size:1.75rem; font-weight:800; font-family:var(--font-display); color:#e11d48; margin-bottom:0.25rem;">
            {{ number_format($totalExpenses, 0, ',', ' ') }} <span style="font-size:1rem;">FCFA</span>
        </div>
        <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
            Dépenses de la période sélectionnée
        </div>
    </div>

    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); border-left:4px solid {{ $balance >= 0 ? '#059669' : '#e11d48' }};">
        <div style="font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-on-surface-variant); margin-bottom:0.5rem;">Solde net</div>
        <div style="font-size:1.75rem; font-weight:800; font-family:var(--font-display); color:{{ $balance >= 0 ? '#059669' : '#e11d48' }}; margin-bottom:0.25rem;">
            {{ number_format($balance, 0, ',', ' ') }} <span style="font-size:1rem;">FCFA</span>
        </div>
        <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">Revenus − Dépenses</div>
    </div>

    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); border-left:4px solid #d97706;">
        <div style="font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-on-surface-variant); margin-bottom:0.5rem;">Inscriptions avec solde</div>
        <div style="font-size:1.75rem; font-weight:800; font-family:var(--font-display); color:#d97706; margin-bottom:0.25rem;">{{ $pendingFees }}</div>
        <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
            Solde à recouvrer : {{ number_format($overdueSum, 0, ',', ' ') }} FCFA
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:2rem;">

    {{-- Revenus par formation --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1.25rem;">Revenus par formation</h2>
        @if($revenueByFormation->isEmpty())
            <p style="font-size:0.875rem; color:var(--color-on-surface-variant);">Aucune donnée.</p>
        @else
            @php $maxRevenue = $revenueByFormation->max('total') ?: 1; @endphp
            <div style="display:flex; flex-direction:column; gap:0.875rem;">
                @foreach($revenueByFormation as $row)
                <div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:0.25rem;">
                        <span style="font-size:0.8125rem; font-weight:600; color:var(--color-on-surface);">{{ $row->formation_name }}</span>
                        <span style="font-size:0.8125rem; font-weight:700; color:var(--color-on-surface);">{{ number_format($row->total, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div style="height:8px; background:var(--color-surface-container-high); border-radius:9999px; overflow:hidden;">
                        <div style="height:100%; width:{{ round(($row->total / $maxRevenue) * 100) }}%; background:var(--color-primary-container); border-radius:9999px;"></div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Paiements récents --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1.25rem;">Paiements récents</h2>
        @if($recentPayments->isEmpty())
            <p style="font-size:0.875rem; color:var(--color-on-surface-variant);">Aucun paiement enregistré.</p>
        @else
        <div style="display:flex; flex-direction:column; gap:0.75rem;">
            @foreach($recentPayments as $p)
            <div style="display:flex; align-items:center; justify-content:space-between; padding:0.625rem 0; border-bottom:1px solid var(--color-surface-container);">
                <div>
                    <div style="font-size:0.8125rem; font-weight:600; color:var(--color-on-surface);">
                        {{ $p->studentProfile->user->full_name ?? 'Étudiant inconnu' }}
                    </div>
                    <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                        {{ optional($p->date_paiement ?? $p->payment_date)->format('d/m/Y') ?? '—' }} · {{ $p->mode ?? $p->method ?? '' }}
                    </div>
                </div>
                <span style="font-size:0.875rem; font-weight:800; color:#059669;">
                    +{{ number_format((float) ($p->montant ?? $p->amount_paid ?? 0), 0, ',', ' ') }} FCFA
                </span>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- Dépenses récentes --}}
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1.25rem;">Dépenses récentes</h2>
    @if($recentExpenses->isEmpty())
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant);">Aucune dépense enregistrée.</p>
    @else
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="border-bottom:2px solid var(--color-outline-variant);">
                    <th style="text-align:left; padding:0.75rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Date</th>
                    <th style="text-align:left; padding:0.75rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Description</th>
                    <th style="text-align:left; padding:0.75rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Catégorie</th>
                    <th style="text-align:right; padding:0.75rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Montant</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentExpenses as $e)
                <tr style="border-bottom:1px solid var(--color-surface-container);">
                    <td style="padding:0.75rem 1rem; color:var(--color-on-surface-variant); font-size:0.8125rem;">{{ \Carbon\Carbon::parse($e->expense_date)->format('d/m/Y') }}</td>
                    <td style="padding:0.75rem 1rem; font-weight:600; color:var(--color-on-surface);">{{ $e->title }}</td>
                    <td style="padding:0.75rem 1rem; font-size:0.8125rem; color:var(--color-on-surface-variant);">{{ $e->category->name ?? '—' }}</td>
                    <td style="padding:0.75rem 1rem; text-align:right; font-weight:700; color:#e11d48;">
                        −{{ number_format($e->amount, 0, ',', ' ') }} FCFA
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endsection
