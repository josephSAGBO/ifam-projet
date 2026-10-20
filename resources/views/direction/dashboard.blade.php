@extends('layouts.role')
@section('title', 'Tableau de bord Direction')
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
    <span class="material-symbols-outlined" style="font-size:18px;">auto_awesome</span> Modération Alumni
</a>
<a href="{{ route('contact.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; {{ request()->routeIs('contact.*') ? 'background:var(--color-primary-container); color:var(--color-on-primary);' : 'color:var(--color-on-surface-variant);' }} text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">mail</span> Contact
</a>
@endsection


@section('content')

<div style="margin-bottom:2rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Tableau de bord — Direction
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Vue d'ensemble de l'établissement — {{ now()->isoFormat('dddd D MMMM YYYY') }}
        </p>
    </div>
</div>

{{-- KPIs --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
    @php
    $kpis = [
        ['label'=>'Étudiants',      'value'=>$totalStudents,                                      'icon'=>'school',      'color'=>'#6366f1'],
        ['label'=>'Enseignants',    'value'=>$totalTeachers,                                      'icon'=>'co_present',  'color'=>'#10b981'],
        ['label'=>'Revenus encaissés', 'value'=>number_format($totalRevenue,0,',',' ').' FCFA',  'icon'=>'payments',    'color'=>'#0ea5e9'],
        ['label'=>'Dépenses',          'value'=>number_format($totalExpenses,0,',',' ').' FCFA', 'icon'=>'receipt_long','color'=>'#f59e0b'],
        ['label'=>'Inscriptions avec solde', 'value'=>$pendingFees,                                'icon'=>'warning',     'color'=>'#dc2626'],
        ['label'=>'Alumni', 'value'=>$totalAlumni, 'icon'=>'school', 'color'=>'#8b5cf6'],
        ['label'=>'Profils alumni visibles', 'value'=>$visibleAlumni, 'icon'=>'visibility', 'color'=>'#0f766e'],
    ];
    @endphp
    @foreach($kpis as $kpi)
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); display:flex; flex-direction:column; gap:0.75rem;">
        <div style="display:flex; align-items:center; justify-content:space-between;">
            <span style="font-size:0.7rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.07em;">{{ $kpi['label'] }}</span>
            <div style="width:34px; height:34px; border-radius:0.625rem; background:{{ $kpi['color'] }}20; display:flex; align-items:center; justify-content:center;">
                <span class="material-symbols-outlined" style="font-size:18px; color:{{ $kpi['color'] }};">{{ $kpi['icon'] }}</span>
            </div>
        </div>
        <div style="font-family:var(--font-display); font-size:clamp(1.15rem, 2vw, 1.75rem); font-weight:800; color:var(--color-on-surface); line-height:1.2; overflow-wrap:anywhere;">{{ $kpi['value'] }}</div>
    </div>
    @endforeach
</div>

{{-- Présences + Session --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 350px), 1fr)); gap:1.5rem;">
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
            <span class="material-symbols-outlined" style="font-size:20px; color:#6366f1;">how_to_reg</span> Présences aujourd'hui
        </h2>
        @php $rate = $todayTotal > 0 ? round(($todayPresent/$todayTotal)*100) : 0; @endphp
        @if($todayTotal === 0)
        <p style="color:var(--color-on-surface-variant); font-size:0.875rem;">Aucun pointage pour aujourd'hui.</p>
        @else
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
            <div style="text-align:center; padding:1rem; border-radius:0.75rem; background:#ecfdf5;">
                <div style="font-size:1.75rem; font-weight:800; color:#10b981;">{{ $todayPresent }}</div>
                <div style="font-size:0.75rem; color:#065f46; font-weight:600;">Présents</div>
            </div>
            <div style="text-align:center; padding:1rem; border-radius:0.75rem; background:#fef2f2;">
                <div style="font-size:1.75rem; font-weight:800; color:#dc2626;">{{ $todayAbsent }}</div>
                <div style="font-size:0.75rem; color:#991b1b; font-weight:600;">Absents</div>
            </div>
        </div>
        <div style="height:10px; border-radius:9999px; background:var(--color-surface-container); overflow:hidden;">
            <div style="height:100%; width:{{ $rate }}%; background:linear-gradient(90deg,#10b981,#34d399); border-radius:9999px;"></div>
        </div>
        <p style="font-size:0.75rem; color:var(--color-on-surface-variant); margin:0.375rem 0 0; text-align:center;">{{ $rate }}% de présence</p>
        @endif
    </div>

    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
            <span class="material-symbols-outlined" style="font-size:20px; color:var(--color-primary);">event</span> Session en cours
        </h2>
        @if($activeSession)
        <div style="padding:1rem; background:var(--color-surface-container-low); border-radius:0.75rem; border-left:3px solid var(--color-primary);">
            <div style="font-weight:700; color:var(--color-on-surface);">{{ $activeSession->name }}</div>
            <div style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin-top:0.25rem;">
                {{ $activeSession->start_date?->format('d/m/Y') }} → {{ $activeSession->end_date?->format('d/m/Y') }}
            </div>
        </div>
        @else
        <p style="color:var(--color-on-surface-variant); font-size:0.875rem; text-align:center; margin:2rem 0;">Aucune session active.</p>
        @endif
    </div>
</div>

<x-alumni-showcase :alumnis="$recentAlumni" title="Derniers alumni" />

<div style="margin-top:1rem; text-align:right;">
    <a href="{{ route('admin.alumnis.index') }}"
       style="display:inline-flex; align-items:center; gap:0.375rem; color:var(--color-primary); font-size:0.8125rem; font-weight:700; text-decoration:none;">
        Gérer la publication des profils alumni
        <span class="material-symbols-outlined" aria-hidden="true" style="font-size:18px;">arrow_forward</span>
    </a>
</div>

@endsection
