@extends('layouts.admin')
@section('title', 'Tableau de bord')
@section('content')

@if($isDirectionDaf)
<div style="margin-bottom:2rem;">
    <h1 style="font-family:var(--font-display); font-size:1.625rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
        Tableau de bord — Supervision DAF Globale
    </h1>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
        Bienvenue, <strong>{{ auth()->user()->first_name }}</strong> — {{ now()->isoFormat('dddd D MMMM YYYY') }}
    </p>
</div>

<section style="margin-bottom:1.5rem;">
    <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
        <span class="material-symbols-outlined" style="font-size:22px; color:#10b981;">account_balance</span>
        Supervision Financière
    </h2>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1rem;">
        @foreach([
            ['label' => 'Revenus validés', 'value' => number_format($financialSupervision['totalRevenue'], 0, '.', ' ').' FCFA', 'icon' => 'payments', 'color' => '#10b981', 'route' => 'admin.payments.index'],
            ['label' => 'Revenus ce mois', 'value' => number_format($financialSupervision['revenueThisMonth'], 0, '.', ' ').' FCFA', 'icon' => 'trending_up', 'color' => '#0ea5e9', 'route' => 'admin.payments.index'],
            ['label' => 'Paiements à valider', 'value' => $financialSupervision['paymentsToValidate'], 'icon' => 'pending_actions', 'color' => '#f59e0b', 'route' => 'admin.payments.index'],
            ['label' => 'Dépenses & achats', 'value' => number_format($financialSupervision['totalExpenses'], 0, '.', ' ').' FCFA', 'icon' => 'receipt_long', 'color' => '#ef4444', 'route' => 'admin.expenses.index'],
            ['label' => 'Frais à recouvrer', 'value' => $financialSupervision['feesToRecover'], 'icon' => 'account_balance_wallet', 'color' => '#8b5cf6', 'route' => 'admin.fees.index'],
            ['label' => 'Bourses / réductions', 'value' => $financialSupervision['discounts'], 'icon' => 'school', 'color' => '#ec4899', 'route' => 'admin.discounts.index'],
        ] as $metric)
        <a href="{{ route($metric['route']) }}" style="text-decoration:none; background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); transition:transform 0.2s, box-shadow 0.2s;"
           onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 16px rgba(11,28,48,0.10)'"
           onmouseout="this.style.transform=''; this.style.boxShadow='0 1px 6px rgba(11,28,48,0.04)'">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:0.75rem;">
                <div style="font-size:0.7rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.07em;">{{ $metric['label'] }}</div>
                <span class="material-symbols-outlined" style="display:grid; place-items:center; flex:0 0 38px; width:38px; height:38px; border-radius:0.75rem; background:{{ $metric['color'] }}18; color:{{ $metric['color'] }}; font-size:21px;">{{ $metric['icon'] }}</span>
            </div>
            <div style="margin-top:0.85rem; font-family:var(--font-display); font-size:1.5rem; font-weight:800; color:var(--color-on-surface);">{{ $metric['value'] }}</div>
        </a>
        @endforeach
    </div>
</section>

<section>
    <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
        <span class="material-symbols-outlined" style="font-size:22px; color:#6366f1;">school</span>
        Supervision Administrative
    </h2>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1rem;">
        @foreach([
            ['label' => 'Rentrées académiques', 'value' => $administrativeSupervision['sessions'], 'detail' => $administrativeSupervision['currentSessions'].' en cours', 'icon' => 'event', 'color' => '#6366f1', 'route' => 'admin.sessions.index'],
            ['label' => 'Inscriptions', 'value' => $administrativeSupervision['enrollments'], 'detail' => $administrativeSupervision['activeEnrollments'].' actives', 'icon' => 'how_to_reg', 'color' => '#0ea5e9', 'route' => 'admin.enrollments.index'],
            ['label' => 'Dossiers étudiants', 'value' => $administrativeSupervision['students'], 'detail' => 'Étudiants enregistrés', 'icon' => 'groups', 'color' => '#10b981', 'route' => 'admin.students.index'],
            ['label' => 'Documents & pièces', 'value' => $administrativeSupervision['documents'], 'detail' => 'Documents enregistrés', 'icon' => 'folder_open', 'color' => '#f59e0b', 'route' => 'admin.documents.index'],
        ] as $metric)
        <a href="{{ route($metric['route']) }}" style="text-decoration:none; background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); transition:transform 0.2s, box-shadow 0.2s;"
           onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 16px rgba(11,28,48,0.10)'"
           onmouseout="this.style.transform=''; this.style.boxShadow='0 1px 6px rgba(11,28,48,0.04)'">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:0.75rem;">
                <div style="font-size:0.7rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.07em;">{{ $metric['label'] }}</div>
                <span class="material-symbols-outlined" style="display:grid; place-items:center; flex:0 0 38px; width:38px; height:38px; border-radius:0.75rem; background:{{ $metric['color'] }}18; color:{{ $metric['color'] }}; font-size:21px;">{{ $metric['icon'] }}</span>
            </div>
            <div style="margin-top:0.75rem; font-family:var(--font-display); font-size:1.5rem; font-weight:800; color:var(--color-on-surface);">{{ $metric['value'] }}</div>
            <div style="margin-top:0.25rem; font-size:0.75rem; color:var(--color-on-surface-variant);">{{ $metric['detail'] }}</div>
        </a>
        @endforeach
    </div>
</section>
@elseif($isDirectionDaa)
<div style="margin-bottom:2rem;">
    <h1 style="font-family:var(--font-display); font-size:1.625rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
        Tableau de bord — Affaires Académiques
    </h1>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
        Bienvenue, <strong>{{ auth()->user()->first_name }}</strong> — {{ now()->isoFormat('dddd D MMMM YYYY') }}
    </p>
</div>

<section style="margin-bottom:1.5rem;">
    <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
        <span class="material-symbols-outlined" style="font-size:22px; color:#8b5cf6;">library_books</span>
        Catalogue Commun
    </h2>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
        @foreach([
            ['label' => 'Formations', 'value' => $catalogueSupervision['formations'], 'icon' => 'school', 'color' => '#6366f1', 'route' => 'admin.formations.index'],
            ['label' => 'Modules', 'value' => $catalogueSupervision['modules'], 'icon' => 'menu_book', 'color' => '#8b5cf6', 'route' => 'admin.modules.index'],
        ] as $metric)
        <a href="{{ route($metric['route']) }}" style="text-decoration:none; background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); transition:transform 0.2s, box-shadow 0.2s;"
           onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 16px rgba(11,28,48,0.10)'"
           onmouseout="this.style.transform=''; this.style.boxShadow='0 1px 6px rgba(11,28,48,0.04)'">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:0.75rem;">
                <div style="font-size:0.7rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.07em;">{{ $metric['label'] }}</div>
                <span class="material-symbols-outlined" style="display:grid; place-items:center; flex:0 0 38px; width:38px; height:38px; border-radius:0.75rem; background:{{ $metric['color'] }}18; color:{{ $metric['color'] }}; font-size:21px;">{{ $metric['icon'] }}</span>
            </div>
            <div style="margin-top:0.85rem; font-family:var(--font-display); font-size:1.5rem; font-weight:800; color:var(--color-on-surface);">{{ $metric['value'] }}</div>
        </a>
        @endforeach
    </div>
</section>

<section style="margin-bottom:1.5rem;">
    <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
        <span class="material-symbols-outlined" style="font-size:22px; color:#0ea5e9;">school</span>
        Supervision Pédagogique
    </h2>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
        @foreach([
            ['label' => 'Sessions académiques', 'value' => $pedagogicalSupervision['sessions'], 'detail' => $pedagogicalSupervision['currentSessions'].' en cours', 'icon' => 'event', 'color' => '#6366f1', 'route' => 'admin.sessions.index'],
            ['label' => 'Cours', 'value' => $pedagogicalSupervision['courses'], 'detail' => 'Cours planifiés', 'icon' => 'co_present', 'color' => '#0ea5e9', 'route' => 'admin.courses.index'],
            ['label' => 'Évaluations', 'value' => $pedagogicalSupervision['evaluations'], 'detail' => 'Évaluations enregistrées', 'icon' => 'quiz', 'color' => '#f59e0b', 'route' => 'admin.evaluations.index'],
            ['label' => 'Notes', 'value' => $pedagogicalSupervision['grades'], 'detail' => 'Notes saisies', 'icon' => 'grade', 'color' => '#10b981', 'route' => 'admin.grades.index'],
            ['label' => 'Présences', 'value' => $pedagogicalSupervision['attendances'], 'detail' => 'Pointages enregistrés', 'icon' => 'how_to_reg', 'color' => '#14b8a6', 'route' => 'admin.attendance.index'],
            ['label' => 'Bulletins', 'value' => $pedagogicalSupervision['bulletins'], 'detail' => $pedagogicalSupervision['publishedBulletins'].' publiés', 'icon' => 'description', 'color' => '#ec4899', 'route' => 'admin.bulletins.index'],
        ] as $metric)
        <a href="{{ route($metric['route']) }}" style="text-decoration:none; background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); transition:transform 0.2s, box-shadow 0.2s;"
           onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 16px rgba(11,28,48,0.10)'"
           onmouseout="this.style.transform=''; this.style.boxShadow='0 1px 6px rgba(11,28,48,0.04)'">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:0.75rem;">
                <div style="font-size:0.7rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.07em;">{{ $metric['label'] }}</div>
                <span class="material-symbols-outlined" style="display:grid; place-items:center; flex:0 0 38px; width:38px; height:38px; border-radius:0.75rem; background:{{ $metric['color'] }}18; color:{{ $metric['color'] }}; font-size:21px;">{{ $metric['icon'] }}</span>
            </div>
            <div style="margin-top:0.85rem; font-family:var(--font-display); font-size:1.5rem; font-weight:800; color:var(--color-on-surface);">{{ $metric['value'] }}</div>
            <div style="margin-top:0.25rem; font-size:0.75rem; color:var(--color-on-surface-variant);">{{ $metric['detail'] }}</div>
        </a>
        @endforeach
    </div>
</section>

<section>
    <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
        <span class="material-symbols-outlined" style="font-size:22px; color:#f59e0b;">work</span>
        Insertion & Alumni
    </h2>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
        @foreach([
            ['label' => 'Alumni & diplômés', 'value' => $insertionSupervision['alumni'], 'icon' => 'school', 'color' => '#f59e0b', 'route' => 'admin.alumnis.index'],
            ['label' => 'Partenariats', 'value' => $insertionSupervision['partnerships'], 'icon' => 'handshake', 'color' => '#ec4899', 'route' => 'admin.partnerships.index'],
        ] as $metric)
        <a href="{{ route($metric['route']) }}" style="text-decoration:none; background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); transition:transform 0.2s, box-shadow 0.2s;"
           onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 16px rgba(11,28,48,0.10)'"
           onmouseout="this.style.transform=''; this.style.boxShadow='0 1px 6px rgba(11,28,48,0.04)'">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:0.75rem;">
                <div style="font-size:0.7rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.07em;">{{ $metric['label'] }}</div>
                <span class="material-symbols-outlined" style="display:grid; place-items:center; flex:0 0 38px; width:38px; height:38px; border-radius:0.75rem; background:{{ $metric['color'] }}18; color:{{ $metric['color'] }}; font-size:21px;">{{ $metric['icon'] }}</span>
            </div>
            <div style="margin-top:0.85rem; font-family:var(--font-display); font-size:1.5rem; font-weight:800; color:var(--color-on-surface);">{{ $metric['value'] }}</div>
        </a>
        @endforeach
    </div>
</section>
@elseif($isServiceFinancier ?? false)
<div style="margin-bottom:2rem;">
    <h1 style="font-family:var(--font-display); font-size:1.625rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
        Tableau de bord — Caisse & Recouvrement
    </h1>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
        Bienvenue, <strong>{{ auth()->user()->first_name }}</strong> — {{ now()->isoFormat('dddd D MMMM YYYY') }}
    </p>
</div>

<section>
    <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
        <span class="material-symbols-outlined" style="font-size:22px; color:#10b981;">payments</span>
        Caisse & Recouvrement
    </h2>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
        @foreach([
            ['label' => 'Encaissements validés', 'value' => number_format($financialSupervision['totalRevenue'], 0, '.', ' ').' FCFA', 'icon' => 'payments', 'color' => '#10b981', 'route' => 'admin.payments.index'],
            ['label' => 'Encaissements ce mois', 'value' => number_format($financialSupervision['revenueThisMonth'], 0, '.', ' ').' FCFA', 'icon' => 'trending_up', 'color' => '#0ea5e9', 'route' => 'admin.payments.index'],
            ['label' => 'Paiements à valider', 'value' => $financialSupervision['paymentsToValidate'], 'icon' => 'pending_actions', 'color' => '#f59e0b', 'route' => 'admin.payments.index'],
            ['label' => 'Dépenses', 'value' => number_format($financialSupervision['totalExpenses'], 0, '.', ' ').' FCFA', 'icon' => 'receipt_long', 'color' => '#ef4444', 'route' => 'admin.expenses.index'],
            ['label' => 'Frais à recouvrer', 'value' => $financialSupervision['feesToRecover'], 'icon' => 'account_balance_wallet', 'color' => '#8b5cf6', 'route' => 'admin.fees.index'],
            ['label' => 'Bourses & réductions', 'value' => $financialSupervision['discounts'], 'icon' => 'school', 'color' => '#ec4899', 'route' => 'admin.discounts.index'],
        ] as $metric)
        <a href="{{ route($metric['route']) }}" style="text-decoration:none; background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); transition:transform 0.2s, box-shadow 0.2s;"
           onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 16px rgba(11,28,48,0.10)'"
           onmouseout="this.style.transform=''; this.style.boxShadow='0 1px 6px rgba(11,28,48,0.04)'">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:0.75rem;">
                <div style="font-size:0.7rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.07em;">{{ $metric['label'] }}</div>
                <span class="material-symbols-outlined" style="display:grid; place-items:center; flex:0 0 38px; width:38px; height:38px; border-radius:0.75rem; background:{{ $metric['color'] }}18; color:{{ $metric['color'] }}; font-size:21px;">{{ $metric['icon'] }}</span>
            </div>
            <div style="margin-top:0.85rem; font-family:var(--font-display); font-size:1.5rem; font-weight:800; color:var(--color-on-surface);">{{ $metric['value'] }}</div>
        </a>
        @endforeach
    </div>
</section>
@elseif($isServiceEtudes ?? false)
<div style="margin-bottom:2rem;">
    <h1 style="font-family:var(--font-display); font-size:1.625rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
        Tableau de bord — Études ({{ $dashboardSites->pluck('nom')->join(', ') ?: 'Aucun site associé' }})
    </h1>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
        Bienvenue, <strong>{{ auth()->user()->first_name }}</strong> — {{ now()->isoFormat('dddd D MMMM YYYY') }}
    </p>
</div>

<section>
    <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
        <span class="material-symbols-outlined" style="font-size:22px; color:#0ea5e9;">school</span>
        Gestion pédagogique
    </h2>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
        @foreach([
            ['label' => 'Sessions académiques', 'value' => $pedagogicalSupervision['sessions'], 'detail' => $pedagogicalSupervision['currentSessions'].' en cours', 'icon' => 'event', 'color' => '#6366f1', 'route' => 'admin.sessions.index'],
            ['label' => 'Cours', 'value' => $pedagogicalSupervision['courses'], 'detail' => 'Cours planifiés', 'icon' => 'co_present', 'color' => '#0ea5e9', 'route' => 'admin.courses.index'],
            ['label' => 'Évaluations', 'value' => $pedagogicalSupervision['evaluations'], 'detail' => 'Évaluations enregistrées', 'icon' => 'quiz', 'color' => '#f59e0b', 'route' => 'admin.evaluations.index'],
            ['label' => 'Notes', 'value' => $pedagogicalSupervision['grades'], 'detail' => 'Notes saisies', 'icon' => 'grade', 'color' => '#10b981', 'route' => 'admin.grades.index'],
            ['label' => 'Présences', 'value' => $pedagogicalSupervision['attendances'], 'detail' => 'Pointages enregistrés', 'icon' => 'how_to_reg', 'color' => '#14b8a6', 'route' => 'admin.attendance.index'],
            ['label' => 'Bulletins', 'value' => $pedagogicalSupervision['bulletins'], 'detail' => $pedagogicalSupervision['publishedBulletins'].' publiés', 'icon' => 'description', 'color' => '#ec4899', 'route' => 'admin.bulletins.index'],
        ] as $metric)
        <a href="{{ route($metric['route']) }}" style="text-decoration:none; background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); transition:transform 0.2s, box-shadow 0.2s;"
           onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 16px rgba(11,28,48,0.10)'"
           onmouseout="this.style.transform=''; this.style.boxShadow='0 1px 6px rgba(11,28,48,0.04)'">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:0.75rem;">
                <div style="font-size:0.7rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.07em;">{{ $metric['label'] }}</div>
                <span class="material-symbols-outlined" style="display:grid; place-items:center; flex:0 0 38px; width:38px; height:38px; border-radius:0.75rem; background:{{ $metric['color'] }}18; color:{{ $metric['color'] }}; font-size:21px;">{{ $metric['icon'] }}</span>
            </div>
            <div style="margin-top:0.85rem; font-family:var(--font-display); font-size:1.5rem; font-weight:800; color:var(--color-on-surface);">{{ $metric['value'] }}</div>
            <div style="margin-top:0.25rem; font-size:0.75rem; color:var(--color-on-surface-variant);">{{ $metric['detail'] }}</div>
        </a>
        @endforeach
    </div>
</section>
@elseif($isServiceInsertion ?? false)
<div style="margin-bottom:2rem;">
    <h1 style="font-family:var(--font-display); font-size:1.625rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
        Tableau de bord — Insertion ({{ $dashboardSites->pluck('nom')->join(', ') ?: 'Aucun site associé' }})
    </h1>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
        Bienvenue, <strong>{{ auth()->user()->first_name }}</strong> — {{ now()->isoFormat('dddd D MMMM YYYY') }}
    </p>
</div>

<section>
    <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
        <span class="material-symbols-outlined" style="font-size:22px; color:#f59e0b;">work</span>
        Insertion professionnelle
    </h2>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
        @foreach([
            ['label' => 'Alumni & diplômés', 'value' => $insertionDashboard['alumni'], 'detail' => $insertionDashboard['approvedAlumni'].' approuvés', 'icon' => 'school', 'color' => '#f59e0b', 'route' => 'admin.alumnis.index'],
            ['label' => 'À approuver', 'value' => $insertionDashboard['pendingAlumni'], 'detail' => 'Profils alumni en attente', 'icon' => 'pending_actions', 'color' => '#ef4444', 'route' => 'admin.alumnis.index'],
            ['label' => 'Partenariats', 'value' => $insertionDashboard['partnerships'], 'detail' => $insertionDashboard['activePartnerships'].' actifs', 'icon' => 'handshake', 'color' => '#8b5cf6', 'route' => 'admin.partnerships.index'],
            ['label' => 'Partenariats actifs', 'value' => $insertionDashboard['activePartnerships'], 'detail' => 'Tous types confondus', 'icon' => 'verified', 'color' => '#10b981', 'route' => 'admin.partnerships.index'],
        ] as $metric)
        <a href="{{ route($metric['route']) }}" style="text-decoration:none; background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); transition:transform 0.2s, box-shadow 0.2s;"
           onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 16px rgba(11,28,48,0.10)'"
           onmouseout="this.style.transform=''; this.style.boxShadow='0 1px 6px rgba(11,28,48,0.04)'">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:0.75rem;">
                <div style="font-size:0.7rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.07em;">{{ $metric['label'] }}</div>
                <span class="material-symbols-outlined" style="display:grid; place-items:center; flex:0 0 38px; width:38px; height:38px; border-radius:0.75rem; background:{{ $metric['color'] }}18; color:{{ $metric['color'] }}; font-size:21px;">{{ $metric['icon'] }}</span>
            </div>
            <div style="margin-top:0.85rem; font-family:var(--font-display); font-size:1.5rem; font-weight:800; color:var(--color-on-surface);">{{ $metric['value'] }}</div>
            <div style="margin-top:0.25rem; font-size:0.75rem; color:var(--color-on-surface-variant);">{{ $metric['detail'] }}</div>
        </a>
        @endforeach
    </div>
</section>
@elseif($isServiceDaa ?? false)
<div style="margin-bottom:2rem;">
    <h1 style="font-family:var(--font-display); font-size:1.625rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
        Tableau de bord — DAA ({{ $dashboardSites->pluck('nom')->join(', ') ?: 'Aucun site associé' }})
    </h1>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
        Bienvenue, <strong>{{ auth()->user()->first_name }}</strong> — {{ now()->isoFormat('dddd D MMMM YYYY') }}
    </p>
</div>

<section style="margin-bottom:1.5rem;">
    <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
        <span class="material-symbols-outlined" style="font-size:22px; color:#0ea5e9;">school</span>
        Pédagogie du site
    </h2>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
        @foreach([
            ['label' => 'Sessions académiques', 'value' => $pedagogicalSupervision['sessions'], 'detail' => $pedagogicalSupervision['currentSessions'].' en cours', 'icon' => 'event', 'color' => '#6366f1', 'route' => 'admin.sessions.index'],
            ['label' => 'Cours', 'value' => $pedagogicalSupervision['courses'], 'detail' => 'Cours planifiés', 'icon' => 'co_present', 'color' => '#0ea5e9', 'route' => 'admin.courses.index'],
            ['label' => 'Évaluations', 'value' => $pedagogicalSupervision['evaluations'], 'detail' => 'Évaluations enregistrées', 'icon' => 'quiz', 'color' => '#f59e0b', 'route' => 'admin.evaluations.index'],
            ['label' => 'Notes', 'value' => $pedagogicalSupervision['grades'], 'detail' => 'Notes saisies', 'icon' => 'grade', 'color' => '#10b981', 'route' => 'admin.grades.index'],
            ['label' => 'Présences', 'value' => $pedagogicalSupervision['attendances'], 'detail' => 'Pointages enregistrés', 'icon' => 'how_to_reg', 'color' => '#14b8a6', 'route' => 'admin.attendance.index'],
            ['label' => 'Bulletins', 'value' => $pedagogicalSupervision['bulletins'], 'detail' => $pedagogicalSupervision['publishedBulletins'].' publiés', 'icon' => 'description', 'color' => '#ec4899', 'route' => 'admin.bulletins.index'],
        ] as $metric)
        <a href="{{ route($metric['route']) }}" style="text-decoration:none; background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); transition:transform 0.2s, box-shadow 0.2s;"
           onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 16px rgba(11,28,48,0.10)'"
           onmouseout="this.style.transform=''; this.style.boxShadow='0 1px 6px rgba(11,28,48,0.04)'">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:0.75rem;">
                <div style="font-size:0.7rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.07em;">{{ $metric['label'] }}</div>
                <span class="material-symbols-outlined" style="display:grid; place-items:center; flex:0 0 38px; width:38px; height:38px; border-radius:0.75rem; background:{{ $metric['color'] }}18; color:{{ $metric['color'] }}; font-size:21px;">{{ $metric['icon'] }}</span>
            </div>
            <div style="margin-top:0.85rem; font-family:var(--font-display); font-size:1.5rem; font-weight:800; color:var(--color-on-surface);">{{ $metric['value'] }}</div>
            <div style="margin-top:0.25rem; font-size:0.75rem; color:var(--color-on-surface-variant);">{{ $metric['detail'] }}</div>
        </a>
        @endforeach
    </div>
</section>

<section>
    <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
        <span class="material-symbols-outlined" style="font-size:22px; color:#f59e0b;">work</span>
        Insertion du site
    </h2>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
        @foreach([
            ['label' => 'Alumni & diplômés', 'value' => $insertionSupervision['alumni'], 'icon' => 'school', 'color' => '#f59e0b', 'route' => 'admin.alumnis.index'],
            ['label' => 'Partenariats', 'value' => $insertionSupervision['partnerships'], 'icon' => 'handshake', 'color' => '#ec4899', 'route' => 'admin.partnerships.index'],
        ] as $metric)
        <a href="{{ route($metric['route']) }}" style="text-decoration:none; background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); transition:transform 0.2s, box-shadow 0.2s;"
           onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 16px rgba(11,28,48,0.10)'"
           onmouseout="this.style.transform=''; this.style.boxShadow='0 1px 6px rgba(11,28,48,0.04)'">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:0.75rem;">
                <div style="font-size:0.7rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.07em;">{{ $metric['label'] }}</div>
                <span class="material-symbols-outlined" style="display:grid; place-items:center; flex:0 0 38px; width:38px; height:38px; border-radius:0.75rem; background:{{ $metric['color'] }}18; color:{{ $metric['color'] }}; font-size:21px;">{{ $metric['icon'] }}</span>
            </div>
            <div style="margin-top:0.85rem; font-family:var(--font-display); font-size:1.5rem; font-weight:800; color:var(--color-on-surface);">{{ $metric['value'] }}</div>
        </a>
        @endforeach
    </div>
</section>
@elseif($isServiceAdmin)
<div style="margin-bottom:2rem;">
    <h1 style="font-family:var(--font-display); font-size:1.625rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
        Tableau de bord — Scolarité & Dossiers
    </h1>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
        Bienvenue, <strong>{{ auth()->user()->first_name }}</strong> — {{ now()->isoFormat('dddd D MMMM YYYY') }}
    </p>
</div>

<section>
    <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
        <span class="material-symbols-outlined" style="font-size:22px; color:#6366f1;">school</span>
        Scolarité & Dossiers
    </h2>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
        @foreach([
            ['label' => 'Rentrées académiques', 'value' => $administrativeSupervision['sessions'], 'detail' => $administrativeSupervision['currentSessions'].' en cours', 'icon' => 'event', 'color' => '#6366f1', 'route' => 'admin.sessions.index'],
            ['label' => 'Inscriptions', 'value' => $administrativeSupervision['enrollments'], 'detail' => $administrativeSupervision['activeEnrollments'].' actives', 'icon' => 'how_to_reg', 'color' => '#0ea5e9', 'route' => 'admin.enrollments.index'],
            ['label' => 'Dossiers étudiants', 'value' => $administrativeSupervision['students'], 'detail' => 'Étudiants du périmètre', 'icon' => 'groups', 'color' => '#10b981', 'route' => 'admin.students.index'],
            ['label' => 'Documents & attestations', 'value' => $administrativeSupervision['documents'], 'detail' => 'Documents du périmètre', 'icon' => 'folder_open', 'color' => '#f59e0b', 'route' => 'admin.documents.index'],
            ['label' => 'Salles', 'value' => $administrativeSupervision['rooms'], 'detail' => $administrativeSupervision['activeRooms'].' actives', 'icon' => 'meeting_room', 'color' => '#8b5cf6', 'route' => 'admin.rooms.index'],
        ] as $metric)
        <a href="{{ route($metric['route']) }}" style="text-decoration:none; background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); transition:transform 0.2s, box-shadow 0.2s;"
           onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 16px rgba(11,28,48,0.10)'"
           onmouseout="this.style.transform=''; this.style.boxShadow='0 1px 6px rgba(11,28,48,0.04)'">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:0.75rem;">
                <div style="font-size:0.7rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.07em;">{{ $metric['label'] }}</div>
                <span class="material-symbols-outlined" style="display:grid; place-items:center; flex:0 0 38px; width:38px; height:38px; border-radius:0.75rem; background:{{ $metric['color'] }}18; color:{{ $metric['color'] }}; font-size:21px;">{{ $metric['icon'] }}</span>
            </div>
            <div style="margin-top:0.85rem; font-family:var(--font-display); font-size:1.5rem; font-weight:800; color:var(--color-on-surface);">{{ $metric['value'] }}</div>
            <div style="margin-top:0.25rem; font-size:0.75rem; color:var(--color-on-surface-variant);">{{ $metric['detail'] }}</div>
        </a>
        @endforeach
    </div>
</section>
@elseif($isSiteManager)
<div style="margin-bottom:2rem;">
    <h1 style="font-family:var(--font-display); font-size:1.625rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
        Tableau de bord — {{ $dashboardSites->pluck('nom')->join(', ') ?: 'Aucun site associé' }}
    </h1>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
        Bienvenue, <strong>{{ auth()->user()->first_name }}</strong> — {{ now()->isoFormat('dddd D MMMM YYYY') }}
    </p>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
    @foreach([
        ['label' => 'Utilisateurs', 'value' => $totalUsers, 'icon' => 'group', 'color' => 'var(--color-primary)'],
        ['label' => 'Comptes actifs', 'value' => $activeUsers, 'icon' => 'verified', 'color' => '#0ea5e9'],
        ['label' => 'Salles', 'value' => $totalRooms, 'icon' => 'meeting_room', 'color' => '#6366f1'],
        ['label' => 'Salles actives', 'value' => $activeRooms, 'icon' => 'domain', 'color' => '#10b981'],
    ] as $kpi)
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); display:flex; flex-direction:column; gap:0.75rem;">
        <div style="display:flex; align-items:center; justify-content:space-between;">
            <span style="font-size:0.7rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.07em;">{{ $kpi['label'] }}</span>
            <span class="material-symbols-outlined" style="font-size:20px; color:{{ $kpi['color'] }};">{{ $kpi['icon'] }}</span>
        </div>
        <div style="font-family:var(--font-display); font-size:2rem; font-weight:800; color:var(--color-on-surface); line-height:1;">{{ $kpi['value'] }}</div>
    </div>
    @endforeach
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:1.5rem;">
    <section style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
            <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0;">Utilisateurs du site</h2>
            <a href="{{ route('admin.users.index') }}" style="font-size:0.8125rem; font-weight:600; color:var(--color-primary); text-decoration:none;">Voir la liste →</a>
        </div>
        @forelse($siteUsers as $siteUser)
        <div style="display:flex; justify-content:space-between; gap:1rem; padding:0.75rem 0; border-bottom:1px solid var(--color-surface-container);">
            <div>
                <div style="font-weight:600; color:var(--color-on-surface);">{{ $siteUser->full_name }}</div>
                <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">{{ $siteUser->email }}</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">{{ $siteUser->getRoleNames()->join(', ') ?: '—' }}</div>
                <div style="font-size:0.75rem; font-weight:600; color:{{ $siteUser->is_active ? '#059669' : '#dc2626' }};">{{ $siteUser->is_active ? 'Actif' : 'Inactif' }}</div>
            </div>
        </div>
        @empty
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">Aucun utilisateur associé à ce site.</p>
        @endforelse
    </section>

    <section style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
            <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0;">Salles du site</h2>
            <a href="{{ route('admin.rooms.index') }}" style="font-size:0.8125rem; font-weight:600; color:var(--color-primary); text-decoration:none;">Voir la liste →</a>
        </div>
        @forelse($rooms as $room)
        <div style="display:flex; justify-content:space-between; gap:1rem; padding:0.75rem 0; border-bottom:1px solid var(--color-surface-container);">
            <div>
                <div style="font-weight:600; color:var(--color-on-surface);">{{ $room->name }}</div>
                <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">{{ $room->type ?: 'Standard' }} · {{ $room->capacity ?: '—' }} places</div>
            </div>
            <div style="font-size:0.75rem; font-weight:600; color:{{ $room->is_active ? '#059669' : '#6b7280' }};">{{ $room->is_active ? 'Active' : 'Inactive' }}</div>
        </div>
        @empty
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">Aucune salle associée à ce site.</p>
        @endforelse
    </section>
</div>
@else

{{-- ══ En-tête ══ --}}
<div style="margin-bottom:2rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.625rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Tableau de bord
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Bienvenue, <strong>{{ auth()->user()->first_name }}</strong> — {{ now()->isoFormat('dddd D MMMM YYYY') }}
        </p>
    </div>
    <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
        @if($unreadNotifications > 0)
        <a href="{{ route('admin.notifications.index') }}"
           style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.5rem 1rem; border-radius:0.75rem;
                  background:#fef2f2; color:#dc2626; font-size:0.8125rem; font-weight:700; text-decoration:none;">
            <span class="material-symbols-outlined" style="font-size:18px;">notifications_active</span>
            {{ $unreadNotifications }} non lue{{ $unreadNotifications > 1 ? 's' : '' }}
        </a>
        @endif
        <a href="{{ route('admin.users.create') }}"
           style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem;
                  background:var(--color-primary-container); color:var(--color-on-primary); border-radius:0.75rem;
                  text-decoration:none; font-size:0.875rem; font-weight:700;
                  box-shadow:0 3px 10px rgba(228,0,102,0.3);">
            <span class="material-symbols-outlined" style="font-size:20px;">person_add</span> Créer un utilisateur
        </a>
    </div>
</div>

{{-- ══ KPIs Row 1 — Personnes ══ --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1rem; margin-bottom:1rem;">
    @php
    $kpis = [
        ['label'=>'Étudiants',        'value'=>$totalStudents,  'icon'=>'school',      'color'=>'#6366f1', 'route'=>'admin.students.index'],
        ['label'=>'Enseignants',       'value'=>$totalTeachers,  'icon'=>'co_present',  'color'=>'#10b981', 'route'=>'admin.teachers.index'],
        ['label'=>'Utilisateurs total','value'=>$totalUsers,     'icon'=>'group',       'color'=>'var(--color-primary)', 'route'=>'admin.users.index'],
        ['label'=>'Comptes actifs',    'value'=>$activeUsers,    'icon'=>'verified',    'color'=>'#0ea5e9', 'route'=>'admin.users.index'],
    ];
    @endphp
    @foreach($kpis as $kpi)
    <a href="{{ route($kpi['route']) }}" style="text-decoration:none;">
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);
                    display:flex; flex-direction:column; gap:0.75rem; transition:all 0.2s; cursor:pointer;"
             onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 16px rgba(11,28,48,0.1)'"
             onmouseout="this.style.transform=''; this.style.boxShadow='0 1px 6px rgba(11,28,48,0.04)'">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <span style="font-size:0.7rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.07em;">{{ $kpi['label'] }}</span>
                <div style="width:34px; height:34px; border-radius:0.625rem; background:{{ $kpi['color'] }}20; display:flex; align-items:center; justify-content:center;">
                    <span class="material-symbols-outlined" style="font-size:18px; color:{{ $kpi['color'] }};">{{ $kpi['icon'] }}</span>
                </div>
            </div>
            <div style="font-family:var(--font-display); font-size:2rem; font-weight:800; color:var(--color-on-surface); line-height:1;">{{ $kpi['value'] }}</div>
        </div>
    </a>
    @endforeach
</div>

{{-- ══ KPIs Row 2 — Finance ══ --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1rem; margin-bottom:1.75rem;">
    @php
    $finKpis = [
        ['label'=>'Revenus totaux',     'value'=>number_format($totalRevenue,0,'.',' ').' FCFA',    'icon'=>'payments',     'color'=>'#10b981', 'route'=>'admin.payments.index'],
        ['label'=>'Ce mois',            'value'=>number_format($revenueThisMonth,0,'.',' ').' FCFA','icon'=>'trending_up',  'color'=>'#6366f1', 'route'=>'admin.payments.index'],
        ['label'=>'Dépenses totales',   'value'=>number_format($totalExpenses,0,'.',' ').' FCFA',   'icon'=>'receipt_long', 'color'=>'#f59e0b', 'route'=>'admin.expenses.index'],
        ['label'=>'Inscriptions avec solde', 'value'=>$pendingFees + $partialFees,                 'icon'=>'warning',      'color'=>'#dc2626', 'route'=>'admin.fees.index'],
    ];
    @endphp
    @foreach($finKpis as $kpi)
    <a href="{{ route($kpi['route']) }}" style="text-decoration:none;">
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);
                    display:flex; flex-direction:column; gap:0.75rem; transition:all 0.2s;"
             onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 16px rgba(11,28,48,0.1)'"
             onmouseout="this.style.transform=''; this.style.boxShadow='0 1px 6px rgba(11,28,48,0.04)'">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <span style="font-size:0.7rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.07em;">{{ $kpi['label'] }}</span>
                <div style="width:34px; height:34px; border-radius:0.625rem; background:{{ $kpi['color'] }}20; display:flex; align-items:center; justify-content:center;">
                    <span class="material-symbols-outlined" style="font-size:18px; color:{{ $kpi['color'] }};">{{ $kpi['icon'] }}</span>
                </div>
            </div>
            <div style="font-family:var(--font-display); font-size:1.5rem; font-weight:800; color:var(--color-on-surface); line-height:1;">{{ $kpi['value'] }}</div>
        </div>
    </a>
    @endforeach
</div>

{{-- ══ Grille principale ══ --}}
<div style="display:grid; grid-template-columns:2fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">

    {{-- Paiements récents --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
            <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="font-size:20px; color:#10b981;">payments</span> Paiements récents
            </h2>
            <a href="{{ route('admin.payments.index') }}" style="font-size:0.8125rem; font-weight:600; color:var(--color-primary); text-decoration:none;">Voir tout →</a>
        </div>
        @if($recentPayments->isEmpty())
        <div style="text-align:center; padding:2rem; color:var(--color-on-surface-variant); font-size:0.875rem;">Aucun paiement enregistré.</div>
        @else
        <div style="display:flex; flex-direction:column; gap:0.625rem;">
            @foreach($recentPayments as $payment)
            <div style="display:flex; align-items:center; gap:0.875rem; padding:0.75rem; border-radius:0.75rem; background:var(--color-surface-container-low);">
                <div style="width:36px; height:36px; border-radius:50%; background:#10b98120; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <span class="material-symbols-outlined" style="font-size:18px; color:#10b981;">payments</span>
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="font-weight:600; font-size:0.875rem; color:var(--color-on-surface); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        {{ $payment->studentProfile->user->last_name ?? '—' }} {{ $payment->studentProfile->user->first_name ?? '' }}
                    </div>
                    <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">{{ $payment->payment_date?->format('d/m/Y') ?? '—' }}</div>
                </div>
                <div style="font-weight:800; color:#10b981; font-size:0.9375rem; white-space:nowrap;">
                    +{{ number_format($payment->amount_paid, 0, '.', ' ') }} FCFA
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Sidebar droite --}}
    <div style="display:flex; flex-direction:column; gap:1.25rem;">

        {{-- Session active --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
            <h2 style="font-size:0.875rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="font-size:18px; color:var(--color-primary);">event</span> Session en cours
            </h2>
            @if($activeSession)
            <div style="padding:1rem; background:var(--color-surface-container-low); border-radius:0.75rem; border-left:3px solid var(--color-primary);">
                <div style="font-weight:700; color:var(--color-on-surface); font-size:0.9375rem;">{{ $activeSession->name }}</div>
                <div style="font-size:0.75rem; color:var(--color-on-surface-variant); margin-top:0.375rem;">
                    {{ $activeSession->start_date?->format('d/m/Y') }} → {{ $activeSession->end_date?->format('d/m/Y') }}
                </div>
            </div>
            @else
            <div style="text-align:center; padding:1rem; color:var(--color-on-surface-variant);">
                <span class="material-symbols-outlined" style="font-size:32px; opacity:0.25; display:block; margin-bottom:0.5rem;">event_busy</span>
                <p style="font-size:0.8125rem; margin:0 0 0.75rem;">Aucune session active</p>
                <a href="{{ route('admin.sessions.create') }}" style="font-size:0.8125rem; font-weight:600; color:var(--color-primary); text-decoration:none;">Créer →</a>
            </div>
            @endif
        </div>

        {{-- Présences du jour --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
            <h2 style="font-size:0.875rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="font-size:18px; color:#6366f1;">how_to_reg</span> Présences aujourd'hui
            </h2>
            @if($todayAttendanceCount === 0)
            <p style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin:0; text-align:center;">Aucun pointage aujourd'hui.</p>
            @else
            @php
                $presentPct = $todayAttendanceCount > 0 ? round(($todayPresentCount / $todayAttendanceCount) * 100) : 0;
            @endphp
            <div style="display:flex; flex-direction:column; gap:0.625rem;">
                <div style="display:flex; justify-content:space-between; font-size:0.8125rem;">
                    <span style="color:#10b981; font-weight:700;">✓ Présents : {{ $todayPresentCount }}</span>
                    <span style="color:#dc2626; font-weight:700;">✗ Absents : {{ $todayAbsentCount }}</span>
                </div>
                <div style="height:8px; border-radius:9999px; background:var(--color-surface-container); overflow:hidden;">
                    <div style="height:100%; width:{{ $presentPct }}%; background:linear-gradient(90deg,#10b981,#34d399); border-radius:9999px; transition:width 0.5s;"></div>
                </div>
                <div style="font-size:0.75rem; color:var(--color-on-surface-variant); text-align:center;">{{ $presentPct }}% de taux de présence</div>
            </div>
            @endif
        </div>

    </div>
</div>

{{-- ══ Accès rapides + Derniers utilisateurs ══ --}}
<div style="display:grid; grid-template-columns:1fr 2fr; gap:1.5rem;">

    {{-- Accès rapides --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1.25rem; display:flex; align-items:center; gap:0.5rem;">
            <span class="material-symbols-outlined" style="font-size:20px; color:var(--color-primary);">bolt</span> Accès rapides
        </h2>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
            @php
            $shortcuts = [
                ['route'=>'admin.users.create',            'icon'=>'school',          'label'=>'Étudiant',       'color'=>'#6366f1'],
                ['route'=>'admin.teachers.create',         'icon'=>'co_present',      'label'=>'Enseignant',     'color'=>'#10b981'],
                ['route'=>'admin.fees.index',              'icon'=>'receipt',         'label'=>'Suivi frais',    'color'=>'#f59e0b'],
                ['route'=>'admin.payments.create',         'icon'=>'payments',        'label'=>'Paiement',       'color'=>'#0ea5e9'],
                ['route'=>'admin.documents.create',        'icon'=>'upload_file',     'label'=>'Document',       'color'=>'#8b5cf6'],
                ['route'=>'admin.notifications.create',    'icon'=>'send',            'label'=>'Notification',   'color'=>'#ec4899'],
            ];
            @endphp
            @foreach($shortcuts as $s)
            <a href="{{ route($s['route']) }}"
               style="display:flex; flex-direction:column; align-items:center; justify-content:center; gap:0.4rem;
                      padding:0.875rem 0.5rem; border-radius:0.75rem; background:var(--color-surface-container-low);
                      text-decoration:none; text-align:center; transition:all 0.2s;"
               onmouseover="this.style.background='var(--color-surface-container)'; this.style.transform='translateY(-2px)'"
               onmouseout="this.style.background='var(--color-surface-container-low)'; this.style.transform=''">
                <span class="material-symbols-outlined" style="font-size:22px; color:{{ $s['color'] }};">{{ $s['icon'] }}</span>
                <span style="font-size:0.7rem; font-weight:700; color:var(--color-on-surface);">{{ $s['label'] }}</span>
            </a>
            @endforeach
        </div>
    </div>

    {{-- Derniers utilisateurs --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
            <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="font-size:20px; color:var(--color-primary);">manage_accounts</span> Derniers inscrits
            </h2>
            <a href="{{ route('admin.users.index') }}" style="font-size:0.8125rem; font-weight:600; color:var(--color-primary); text-decoration:none;">Voir tout →</a>
        </div>
        @if($latestUsers->isEmpty())
        <div style="text-align:center; padding:2rem; color:var(--color-on-surface-variant); font-size:0.875rem;">Aucun utilisateur pour l'instant.</div>
        @else
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
                <thead>
                    <tr style="border-bottom:1px solid var(--color-outline-variant);">
                        <th style="text-align:left; padding:0.5rem 0.75rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Nom</th>
                        <th style="text-align:left; padding:0.5rem 0.75rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Rôle</th>
                        <th style="text-align:left; padding:0.5rem 0.75rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Statut</th>
                        <th style="text-align:left; padding:0.5rem 0.75rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Créé le</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($latestUsers as $user)
                    <tr style="border-bottom:1px solid var(--color-surface-container); transition:background 0.15s;"
                        onmouseover="this.style.background='var(--color-surface-container-low)'"
                        onmouseout="this.style.background=''">
                        <td style="padding:0.75rem;">
                            <div style="display:flex; align-items:center; gap:0.625rem;">
                                <div style="width:32px; height:32px; border-radius:50%; background:var(--color-primary-container); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                    <span style="font-size:0.7rem; font-weight:800; color:var(--color-on-primary);">{{ $user->initials }}</span>
                                </div>
                                <span style="font-weight:600; color:var(--color-on-surface);">{{ $user->full_name }}</span>
                            </div>
                        </td>
                        <td style="padding:0.75rem;">
                            <span style="padding:0.2rem 0.5rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:var(--color-surface-container); color:var(--color-on-surface-variant);">
                                {{ $user->getRoleNames()->first() ?? '—' }}
                            </span>
                        </td>
                        <td style="padding:0.75rem;">
                            @if($user->is_active)
                            <span style="padding:0.2rem 0.5rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#ecfdf5; color:#065f46;">Actif</span>
                            @else
                            <span style="padding:0.2rem 0.5rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#fef2f2; color:#991b1b;">Inactif</span>
                            @endif
                        </td>
                        <td style="padding:0.75rem; color:var(--color-on-surface-variant);">{{ $user->created_at?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

@endif

@endsection
