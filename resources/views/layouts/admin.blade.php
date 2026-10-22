<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'IFAM Connect') — Espace Administrateur</title>
    <meta name="description" content="IFAM Connect — Plateforme de gestion de l'Institut IFAM">

    {{-- Design System : Tailwind v4 + tokens IFAM --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Material Symbols (icônes identiques aux maquettes Stitch) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    @stack('styles')
</head>
@php
    $authUser = auth()->user();
    $isSuperAdmin = $authUser?->hasRole('administrateur');
    $isDirectionDaf = $authUser?->hasRole('direction_daf') && ! $isSuperAdmin;
    $isRaf = $authUser?->hasRole('responsable_administratif_financier') && ! $isSuperAdmin && ! $isDirectionDaf;
    $isServiceAdmin = $authUser?->hasAnyRole(['responsable_administratif', 'secretaire_administratif', 'charge_scolarite', 'agent_administratif']) && ! $isSuperAdmin && ! $isDirectionDaf && ! $isRaf;
    $isServiceFinancier = $authUser?->hasAnyRole(['responsable_financier', 'comptable', 'caissier', 'agent_recouvrement']) && ! $isSuperAdmin && ! $isDirectionDaf && ! $isRaf && ! $isServiceAdmin;
    // DAA — Département des Affaires Académiques
    $isDirectionDaa = $authUser?->hasRole('direction_daa') && ! $isSuperAdmin;
    $isRespDaa = $authUser?->hasRole('responsable_daa') && ! $isSuperAdmin && ! $isDirectionDaa;
    $isRespEtudes = $authUser?->hasRole('responsable_etudes') && ! $isSuperAdmin && ! $isDirectionDaa && ! $isRespDaa;
    $isRespInsertion = $authUser?->hasRole('responsable_insertion') && ! $isSuperAdmin && ! $isDirectionDaa && ! $isRespDaa;
    $isSiteAdmin = $authUser?->hasRole('responsable_site') && ! $isSuperAdmin && ! $isDirectionDaf && ! $isRaf && ! $isServiceAdmin && ! $isServiceFinancier && ! $isDirectionDaa && ! $isRespDaa && ! $isRespEtudes && ! $isRespInsertion;
    $userSiteName = $authUser?->sites->pluck('nom')->first();

    $roleBadge = 'Admin';
    if ($isSuperAdmin) $roleBadge = 'SuperAdmin';
    elseif ($isDirectionDaf) $roleBadge = 'DG DAF';
    elseif ($isRaf) $roleBadge = 'RAF';
    elseif ($isServiceAdmin) $roleBadge = 'Svc Admin';
    elseif ($isServiceFinancier) $roleBadge = 'Svc Finance';
    elseif ($isDirectionDaa) $roleBadge = 'DG DAA';
    elseif ($isRespDaa) $roleBadge = 'Resp. DAA';
    elseif ($isRespEtudes) $roleBadge = 'Svc Études';
    elseif ($isRespInsertion) $roleBadge = 'Svc Insertion';
    elseif ($isSiteAdmin) $roleBadge = 'Admin Site';

    $sectionLabel = 'Administration Globale';
    if ($isDirectionDaf) $sectionLabel = 'Supervision DAF Globale';
    elseif ($isRaf) $sectionLabel = 'DAF Site (' . ($userSiteName ?? 'Local') . ')';
    elseif ($isServiceAdmin) $sectionLabel = 'Admin Scolarité (' . ($userSiteName ?? 'Local') . ')';
    elseif ($isServiceFinancier) $sectionLabel = 'Finance & Caisse (' . ($userSiteName ?? 'Local') . ')';
    elseif ($isDirectionDaa) $sectionLabel = 'Affaires Académiques — Global';
    elseif ($isRespDaa) $sectionLabel = 'DAA (' . ($userSiteName ?? 'Local') . ')';
    elseif ($isRespEtudes) $sectionLabel = 'Pédagogie (' . ($userSiteName ?? 'Local') . ')';
    elseif ($isRespInsertion) $sectionLabel = 'Insertion (' . ($userSiteName ?? 'Local') . ')';
    elseif ($isSiteAdmin) $sectionLabel = 'Périmètre Local (' . ($userSiteName ?? 'Site') . ')';
@endphp
<body style="background-color: var(--color-background); color: var(--color-on-surface);">

    {{-- ══════════════════════════════════════════════════
         SIDEBAR — 288px fixe, identique maquette Stitch
         ══════════════════════════════════════════════════ --}}
    <aside id="sidebar"
           style="position:fixed; left:0; top:0; height:100%; width:288px; background:var(--color-surface-container-lowest);
                  box-shadow: 0 1px 8px rgba(0,0,0,0.04); z-index:50; display:flex; flex-direction:column; justify-content:space-between; overflow-y:auto;">

        {{-- Logo IFAM --}}
        <div style="display:flex; flex-direction:column;">
            <div style="height:80px; padding:0 1.5rem; display:flex; align-items:center; gap:0.75rem; background:var(--color-surface-container-lowest);">
                <img src="{{ asset('img/logo.jpg') }}"
                     alt="Logo IFAM"
                     style="height:48px; width:auto; object-fit:contain;">
                <div style="display:flex; flex-direction:column;">
                    <div style="display:flex; align-items:center; gap:0.375rem;">
                        <span style="font-size:0.6875rem; font-weight:700; padding:0.125rem 0.375rem; border-radius:9999px;
                                     background:var(--color-secondary-container); color:var(--color-on-secondary-fixed); text-transform:uppercase; line-height:1;">
                            {{ $roleBadge }}
                        </span>
                    </div>
                    <span style="font-size:0.6875rem; font-weight:700; letter-spacing:0.15em; color:var(--color-primary-container); text-transform:uppercase; margin-top:2px;">CONNECT</span>
                </div>
            </div>

            {{-- Label section --}}
            <div style="padding:0.5rem 1rem;">
                <span style="padding:0 0.75rem; font-size:0.6875rem; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">
                    {{ $sectionLabel }}
                </span>
            </div>

            {{-- Navigation --}}
            <nav style="padding:0 0.75rem; display:flex; flex-direction:column; gap:0.25rem;">

                {{-- Tableau de bord --}}
                <x-admin.nav-link route="admin.dashboard" icon="grid_view" label="Tableau de bord" />

                @if($isDirectionDaf)
                    {{-- ── 1. DG DAF : Supervision globale ── --}}
                    <x-admin.nav-group label="Supervision Financière" icon="payments">
                        <x-admin.nav-sub-link route="admin.fees.index"         label="Frais de scolarité" />
                        <x-admin.nav-sub-link route="admin.payments.index"     label="Paiements & Validation" />
                        <x-admin.nav-sub-link route="admin.discounts.index"    label="Bourses / Réductions" />
                        <x-admin.nav-sub-link route="admin.expenses.index"     label="Dépenses & Achats" />
                    </x-admin.nav-group>

                    <x-admin.nav-group label="Supervision Administrative" icon="school">
                        <x-admin.nav-sub-link route="admin.sessions.index"     label="Rentrées académiques" />
                        <x-admin.nav-sub-link route="admin.enrollments.index"  label="Inscriptions" />
                        <x-admin.nav-sub-link route="admin.students.index"     label="Dossiers étudiants" />
                        <x-admin.nav-sub-link route="admin.documents.index"    label="Documents & Pièces" />
                    </x-admin.nav-group>

                    <x-admin.nav-group label="Rapports & Clôture" icon="analytics">
                        <x-admin.nav-sub-link route="admin.payments.print"     label="Journal des versements" />
                    </x-admin.nav-group>

                    <a href="{{ route('admin.notifications.index') }}"
                       style="display:flex; align-items:center; justify-content:space-between; padding:0.625rem 0.75rem; border-radius:0.75rem;
                              color:var(--color-on-surface-variant); text-decoration:none; transition:background 0.15s;"
                       onmouseover="this.style.background='var(--color-surface-container-high)'; this.style.color='var(--color-on-surface)';"
                       onmouseout="this.style.background=''; this.style.color='var(--color-on-surface-variant)';">
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            <span class="material-symbols-outlined" style="font-size:20px;">notifications</span>
                            <span style="font-size:0.875rem; font-weight:500;">Notifications</span>
                        </div>
                        @if(($unreadNotifications ?? 0) > 0)
                        <span style="padding:0.125rem 0.5rem; font-size:0.6875rem; font-weight:700; border-radius:9999px;
                                     background:var(--color-primary-container); color:var(--color-on-primary);">
                            {{ $unreadNotifications }}
                        </span>
                        @endif
                    </a>

                    <x-admin.nav-link route="admin.settings.index" icon="manage_accounts" label="Mon profil" />

                @elseif($isRaf)
                    {{-- ── 2. RAF : Supervision Administrative & Financière du site ── --}}
                    <x-admin.nav-group label="Service Administratif" icon="school">
                        <x-admin.nav-sub-link route="admin.sessions.index"     label="Rentrées du site" />
                        <x-admin.nav-sub-link route="admin.enrollments.index"  label="Inscriptions du site" />
                        <x-admin.nav-sub-link route="admin.students.index"     label="Dossiers étudiants" />
                        <x-admin.nav-sub-link route="admin.documents.index"    label="Documents du site" />
                        <x-admin.nav-sub-link route="admin.rooms.index"        label="Salles du site" />
                    </x-admin.nav-group>

                    <x-admin.nav-group label="Service Financier" icon="payments">
                        <x-admin.nav-sub-link route="admin.fees.index"         label="Frais de scolarité" />
                        <x-admin.nav-sub-link route="admin.payments.index"     label="Paiements & Caisse" />
                        <x-admin.nav-sub-link route="admin.expenses.index"     label="Dépenses du site" />
                        <x-admin.nav-sub-link route="admin.discounts.index"    label="Bourses / Réductions" />
                    </x-admin.nav-group>

                    <a href="{{ route('admin.notifications.index') }}"
                       style="display:flex; align-items:center; justify-content:space-between; padding:0.625rem 0.75rem; border-radius:0.75rem;
                              color:var(--color-on-surface-variant); text-decoration:none; transition:background 0.15s;"
                       onmouseover="this.style.background='var(--color-surface-container-high)'; this.style.color='var(--color-on-surface)';"
                       onmouseout="this.style.background=''; this.style.color='var(--color-on-surface-variant)';">
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            <span class="material-symbols-outlined" style="font-size:20px;">notifications</span>
                            <span style="font-size:0.875rem; font-weight:500;">Notifications</span>
                        </div>
                        @if(($unreadNotifications ?? 0) > 0)
                        <span style="padding:0.125rem 0.5rem; font-size:0.6875rem; font-weight:700; border-radius:9999px;
                                     background:var(--color-primary-container); color:var(--color-on-primary);">
                            {{ $unreadNotifications }}
                        </span>
                        @endif
                    </a>

                    <x-admin.nav-link route="admin.settings.index" icon="manage_accounts" label="Mon profil" />

                @elseif($isServiceAdmin)
                    {{-- ── 3. Service administratif : Inscriptions, Dossiers, Documents ── --}}
                    <x-admin.nav-group label="Scolarité & Dossiers" icon="school">
                        <x-admin.nav-sub-link route="admin.sessions.index"     label="Rentrées" />
                        <x-admin.nav-sub-link route="admin.enrollments.index"  label="Inscriptions" />
                        <x-admin.nav-sub-link route="admin.students.index"     label="Dossiers étudiants" />
                        <x-admin.nav-sub-link route="admin.documents.index"    label="Documents & Attestations" />
                        <x-admin.nav-sub-link route="admin.rooms.index"        label="Salles" />
                    </x-admin.nav-group>

                    <a href="{{ route('admin.notifications.index') }}"
                       style="display:flex; align-items:center; justify-content:space-between; padding:0.625rem 0.75rem; border-radius:0.75rem;
                              color:var(--color-on-surface-variant); text-decoration:none; transition:background 0.15s;"
                       onmouseover="this.style.background='var(--color-surface-container-high)'; this.style.color='var(--color-on-surface)';"
                       onmouseout="this.style.background=''; this.style.color='var(--color-on-surface-variant)';">
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            <span class="material-symbols-outlined" style="font-size:20px;">notifications</span>
                            <span style="font-size:0.875rem; font-weight:500;">Notifications</span>
                        </div>
                        @if(($unreadNotifications ?? 0) > 0)
                        <span style="padding:0.125rem 0.5rem; font-size:0.6875rem; font-weight:700; border-radius:9999px;
                                     background:var(--color-primary-container); color:var(--color-on-primary);">
                            {{ $unreadNotifications }}
                        </span>
                        @endif
                    </a>

                    <x-admin.nav-link route="admin.settings.index" icon="manage_accounts" label="Mon profil" />

                @elseif($isServiceFinancier)
                    {{-- ── 4. Service financier : Frais, Caisse, Dépenses ── --}}
                    <x-admin.nav-group label="Caisse & Recouvrement" icon="payments">
                        <x-admin.nav-sub-link route="admin.fees.index"         label="Frais de scolarité" />
                        <x-admin.nav-sub-link route="admin.payments.index"     label="Paiements & Encaissements" />
                        <x-admin.nav-sub-link route="admin.expenses.index"     label="Dépenses" />
                        <x-admin.nav-sub-link route="admin.discounts.index"    label="Bourses & Réductions" />
                    </x-admin.nav-group>

                    <a href="{{ route('admin.notifications.index') }}"
                       style="display:flex; align-items:center; justify-content:space-between; padding:0.625rem 0.75rem; border-radius:0.75rem;
                              color:var(--color-on-surface-variant); text-decoration:none; transition:background 0.15s;"
                       onmouseover="this.style.background='var(--color-surface-container-high)'; this.style.color='var(--color-on-surface)';"
                       onmouseout="this.style.background=''; this.style.color='var(--color-on-surface-variant)';">
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            <span class="material-symbols-outlined" style="font-size:20px;">notifications</span>
                            <span style="font-size:0.875rem; font-weight:500;">Notifications</span>
                        </div>
                        @if(($unreadNotifications ?? 0) > 0)
                        <span style="padding:0.125rem 0.5rem; font-size:0.6875rem; font-weight:700; border-radius:9999px;
                                     background:var(--color-primary-container); color:var(--color-on-primary);">
                            {{ $unreadNotifications }}
                        </span>
                        @endif
                    </a>

                    <x-admin.nav-link route="admin.settings.index" icon="manage_accounts" label="Mon profil" />

                @elseif($isDirectionDaa)
                    {{-- ── 5. DG DAA : Supervision académique globale + catalogue ── --}}
                    <x-admin.nav-group label="Catalogue Commun" icon="library_books">
                        <x-admin.nav-sub-link route="admin.formations.index"  label="Formations" />
                        <x-admin.nav-sub-link route="admin.modules.index"     label="Modules" />
                    </x-admin.nav-group>

                    <x-admin.nav-group label="Supervision Pédagogique" icon="school">
                        <x-admin.nav-sub-link route="admin.sessions.index"    label="Rentrées académiques" />
                        <x-admin.nav-sub-link route="admin.courses.index"     label="Cours" />
                        <x-admin.nav-sub-link route="admin.evaluations.index" label="Évaluations" />
                        <x-admin.nav-sub-link route="admin.grades.index"      label="Notes" />
                        <x-admin.nav-sub-link route="admin.attendance.index"  label="Présences" />
                        <x-admin.nav-sub-link route="admin.results.index"     label="Résultats & Moyennes" />
                        <x-admin.nav-sub-link route="admin.bulletins.index"   label="Bulletins" />
                    </x-admin.nav-group>

                    <x-admin.nav-group label="Insertion & Alumni" icon="work">
                        <x-admin.nav-sub-link route="admin.alumnis.index"     label="Alumni & Diplômés" />
                        <x-admin.nav-sub-link route="admin.partnerships.index" label="Partenariats" />
                    </x-admin.nav-group>

                    <a href="{{ route('admin.notifications.index') }}"
                       style="display:flex; align-items:center; justify-content:space-between; padding:0.625rem 0.75rem; border-radius:0.75rem;
                              color:var(--color-on-surface-variant); text-decoration:none; transition:background 0.15s;"
                       onmouseover="this.style.background='var(--color-surface-container-high)'; this.style.color='var(--color-on-surface)';"
                       onmouseout="this.style.background=''; this.style.color='var(--color-on-surface-variant)';">
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            <span class="material-symbols-outlined" style="font-size:20px;">notifications</span>
                            <span style="font-size:0.875rem; font-weight:500;">Notifications</span>
                        </div>
                        @if(($unreadNotifications ?? 0) > 0)
                        <span style="padding:0.125rem 0.5rem; font-size:0.6875rem; font-weight:700; border-radius:9999px;
                                     background:var(--color-primary-container); color:var(--color-on-primary);">
                            {{ $unreadNotifications }}
                        </span>
                        @endif
                    </a>

                    <x-admin.nav-link route="admin.settings.index" icon="manage_accounts" label="Mon profil" />

                @elseif($isRespDaa)
                    {{-- ── 6. Responsable DAA : supervision pédagogie + insertion sur son site ── --}}
                    <x-admin.nav-group label="Catalogue (lecture)" icon="library_books">
                        <x-admin.nav-sub-link route="admin.formations.index"  label="Formations" />
                        <x-admin.nav-sub-link route="admin.modules.index"     label="Modules" />
                    </x-admin.nav-group>

                    <x-admin.nav-group label="Pédagogie du site" icon="school">
                        <x-admin.nav-sub-link route="admin.sessions.index"    label="Rentrées du site" />
                        <x-admin.nav-sub-link route="admin.courses.index"     label="Cours" />
                        <x-admin.nav-sub-link route="admin.evaluations.index" label="Évaluations" />
                        <x-admin.nav-sub-link route="admin.grades.index"      label="Notes" />
                        <x-admin.nav-sub-link route="admin.attendance.index"  label="Présences" />
                        <x-admin.nav-sub-link route="admin.results.index"     label="Résultats" />
                        <x-admin.nav-sub-link route="admin.bulletins.index"   label="Bulletins" />
                    </x-admin.nav-group>

                    <x-admin.nav-group label="Insertion du site" icon="work">
                        <x-admin.nav-sub-link route="admin.alumnis.index"     label="Alumni" />
                        <x-admin.nav-sub-link route="admin.partnerships.index" label="Partenariats" />
                    </x-admin.nav-group>

                    <a href="{{ route('admin.notifications.index') }}"
                       style="display:flex; align-items:center; justify-content:space-between; padding:0.625rem 0.75rem; border-radius:0.75rem;
                              color:var(--color-on-surface-variant); text-decoration:none; transition:background 0.15s;"
                       onmouseover="this.style.background='var(--color-surface-container-high)'; this.style.color='var(--color-on-surface)';"
                       onmouseout="this.style.background=''; this.style.color='var(--color-on-surface-variant)';">
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            <span class="material-symbols-outlined" style="font-size:20px;">notifications</span>
                            <span style="font-size:0.875rem; font-weight:500;">Notifications</span>
                        </div>
                        @if(($unreadNotifications ?? 0) > 0)
                        <span style="padding:0.125rem 0.5rem; font-size:0.6875rem; font-weight:700; border-radius:9999px;
                                     background:var(--color-primary-container); color:var(--color-on-primary);">
                            {{ $unreadNotifications }}
                        </span>
                        @endif
                    </a>

                    <x-admin.nav-link route="admin.settings.index" icon="manage_accounts" label="Mon profil" />

                @elseif($isRespEtudes)
                    {{-- ── 7. Responsable Études : pédagogie site (sans insertion) ── --}}
                    <x-admin.nav-group label="Catalogue (lecture)" icon="library_books">
                        <x-admin.nav-sub-link route="admin.formations.index"  label="Formations" />
                        <x-admin.nav-sub-link route="admin.modules.index"     label="Modules" />
                    </x-admin.nav-group>

                    <x-admin.nav-group label="Gestion pédagogique" icon="school">
                        <x-admin.nav-sub-link route="admin.sessions.index"    label="Rentrées" />
                        <x-admin.nav-sub-link route="admin.courses.index"     label="Cours" />
                        <x-admin.nav-sub-link route="admin.evaluations.index" label="Évaluations" />
                        <x-admin.nav-sub-link route="admin.grades.index"      label="Notes" />
                        <x-admin.nav-sub-link route="admin.attendance.index"  label="Présences" />
                        <x-admin.nav-sub-link route="admin.results.index"     label="Résultats" />
                        <x-admin.nav-sub-link route="admin.bulletins.index"   label="Bulletins" />
                    </x-admin.nav-group>

                    <a href="{{ route('admin.notifications.index') }}"
                       style="display:flex; align-items:center; justify-content:space-between; padding:0.625rem 0.75rem; border-radius:0.75rem;
                              color:var(--color-on-surface-variant); text-decoration:none; transition:background 0.15s;"
                       onmouseover="this.style.background='var(--color-surface-container-high)'; this.style.color='var(--color-on-surface)';"
                       onmouseout="this.style.background=''; this.style.color='var(--color-on-surface-variant)';">
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            <span class="material-symbols-outlined" style="font-size:20px;">notifications</span>
                            <span style="font-size:0.875rem; font-weight:500;">Notifications</span>
                        </div>
                        @if(($unreadNotifications ?? 0) > 0)
                        <span style="padding:0.125rem 0.5rem; font-size:0.6875rem; font-weight:700; border-radius:9999px;
                                     background:var(--color-primary-container); color:var(--color-on-primary);">
                            {{ $unreadNotifications }}
                        </span>
                        @endif
                    </a>

                    <x-admin.nav-link route="admin.settings.index" icon="manage_accounts" label="Mon profil" />

                @elseif($isRespInsertion)
                    {{-- ── 8. Responsable Insertion : alumni + partenariats site ── --}}
                    <x-admin.nav-group label="Catalogue (lecture)" icon="library_books">
                        <x-admin.nav-sub-link route="admin.formations.index"  label="Formations" />
                        <x-admin.nav-sub-link route="admin.modules.index"     label="Modules" />
                    </x-admin.nav-group>

                    <x-admin.nav-group label="Insertion Professionnelle" icon="work">
                        <x-admin.nav-sub-link route="admin.alumnis.index"     label="Alumni & Diplômés" />
                        <x-admin.nav-sub-link route="admin.partnerships.index" label="Partenariats" />
                    </x-admin.nav-group>

                    <a href="{{ route('admin.notifications.index') }}"
                       style="display:flex; align-items:center; justify-content:space-between; padding:0.625rem 0.75rem; border-radius:0.75rem;
                              color:var(--color-on-surface-variant); text-decoration:none; transition:background 0.15s;"
                       onmouseover="this.style.background='var(--color-surface-container-high)'; this.style.color='var(--color-on-surface)';"
                       onmouseout="this.style.background=''; this.style.color='var(--color-on-surface-variant)';">
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            <span class="material-symbols-outlined" style="font-size:20px;">notifications</span>
                            <span style="font-size:0.875rem; font-weight:500;">Notifications</span>
                        </div>
                        @if(($unreadNotifications ?? 0) > 0)
                        <span style="padding:0.125rem 0.5rem; font-size:0.6875rem; font-weight:700; border-radius:9999px;
                                     background:var(--color-primary-container); color:var(--color-on-primary);">
                            {{ $unreadNotifications }}
                        </span>
                        @endif
                    </a>

                    <x-admin.nav-link route="admin.settings.index" icon="manage_accounts" label="Mon profil" />

                @elseif($isSiteAdmin)
                    {{-- ── Menu exclusif Administrateur de site (Section 9) ── --}}
                    <x-admin.nav-link route="admin.users.index" icon="group" label="Utilisateurs de mon site" />
                    <x-admin.nav-link route="admin.rooms.index" icon="meeting_room" label="Salles du site" />

                    {{-- Notifications --}}
                    <a href="{{ route('admin.notifications.index') }}"
                       style="display:flex; align-items:center; justify-content:space-between; padding:0.625rem 0.75rem; border-radius:0.75rem;
                              color:var(--color-on-surface-variant); text-decoration:none; transition:background 0.15s;"
                       onmouseover="this.style.background='var(--color-surface-container-high)'; this.style.color='var(--color-on-surface)';"
                       onmouseout="this.style.background=''; this.style.color='var(--color-on-surface-variant)';">
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            <span class="material-symbols-outlined" style="font-size:20px;">notifications</span>
                            <span style="font-size:0.875rem; font-weight:500;">Notifications</span>
                        </div>
                        @if(($unreadNotifications ?? 0) > 0)
                        <span style="padding:0.125rem 0.5rem; font-size:0.6875rem; font-weight:700; border-radius:9999px;
                                     background:var(--color-primary-container); color:var(--color-on-primary);">
                            {{ $unreadNotifications }}
                        </span>
                        @endif
                    </a>

                    {{-- Paramètres locaux --}}
                    <x-admin.nav-link route="admin.settings.index" icon="manage_accounts" label="Mon profil" />
                @else
                    {{-- ── Menu SuperAdmin (Section 9) ── --}}
                    <x-admin.nav-group label="Plateforme" icon="admin_panel_settings">
                        <x-admin.nav-sub-link route="admin.users.index"       label="Utilisateurs" />
                        <x-admin.nav-sub-link route="admin.sites.index"       label="Sites IFAM" />
                        <x-admin.nav-sub-link route="admin.roles.index"       label="Rôles & Permissions" />
                        <x-admin.nav-sub-link route="admin.departments.index" label="Départements" />
                        <x-admin.nav-sub-link route="admin.services.index"    label="Services" />
                        <x-admin.nav-sub-link route="admin.rooms.index"       label="Salles" />
                    </x-admin.nav-group>

                    {{-- Dossiers Métier --}}
                    <x-admin.nav-group label="Dossiers Métier" icon="folder_shared">
                        <x-admin.nav-sub-link route="admin.students.index" label="Étudiants" />
                        <x-admin.nav-sub-link route="admin.parents.index"  label="Parents" />
                        <x-admin.nav-sub-link route="admin.teachers.index" label="Enseignants" />
                    </x-admin.nav-group>

                    {{-- Académique --}}
                    <x-admin.nav-group label="Académique" icon="school">
                        <x-admin.nav-sub-link route="admin.formations.index"   label="Formations" />
                        <x-admin.nav-sub-link route="admin.sessions.index"     label="Rentrées" />
                        <x-admin.nav-sub-link route="admin.enrollments.index"  label="Inscriptions" />
                        <x-admin.nav-sub-link route="admin.modules.index"      label="Modules" />
                        <x-admin.nav-sub-link route="admin.courses.index"      label="Cours" />
                        <x-admin.nav-sub-link route="admin.evaluations.index"  label="Évaluations" />
                        <x-admin.nav-sub-link route="admin.grades.index"       label="Notes" />
                        <x-admin.nav-sub-link route="admin.attendance.index"   label="Présences" />
                    </x-admin.nav-group>

                    {{-- Résultats --}}
                    <x-admin.nav-group label="Résultats" icon="analytics">
                        <x-admin.nav-sub-link route="admin.results.index" label="Moyennes" />
                        <x-admin.nav-sub-link route="admin.bulletins.index" label="Bulletins" />
                    </x-admin.nav-group>

                    {{-- Documents --}}
                    <x-admin.nav-link route="admin.documents.index" icon="folder" label="Documents" />

                    {{-- Finance --}}
                    <x-admin.nav-group label="Finance" icon="payments">
                        <x-admin.nav-sub-link route="admin.fees.index"         label="Frais de scolarité" />
                        <x-admin.nav-sub-link route="admin.payments.index"     label="Paiements" />
                        <x-admin.nav-sub-link route="admin.discounts.index"    label="Bourses / Réductions" />
                        <x-admin.nav-sub-link route="admin.expenses.index"     label="Dépenses" />
                    </x-admin.nav-group>

                    {{-- Organisation --}}
                    <x-admin.nav-group label="Organisation" icon="corporate_fare">
                        <x-admin.nav-sub-link route="admin.change-requests.index"  label="Demandes de modification" />
                        <x-admin.nav-sub-link route="admin.partnerships.index"   label="Partenariats" />
                        <x-admin.nav-sub-link route="admin.alumnis.index"        label="Alumni / Diplômés" />
                    </x-admin.nav-group>

                    {{-- Notifications --}}
                    <a href="{{ route('admin.notifications.index') }}"
                       style="display:flex; align-items:center; justify-content:space-between; padding:0.625rem 0.75rem; border-radius:0.75rem;
                              color:var(--color-on-surface-variant); text-decoration:none; transition:background 0.15s;"
                       onmouseover="this.style.background='var(--color-surface-container-high)'; this.style.color='var(--color-on-surface)';"
                       onmouseout="this.style.background=''; this.style.color='var(--color-on-surface-variant)';">
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            <span class="material-symbols-outlined" style="font-size:20px;">notifications</span>
                            <span style="font-size:0.875rem; font-weight:500;">Notifications</span>
                        </div>
                        @if(($unreadNotifications ?? 0) > 0)
                        <span style="padding:0.125rem 0.5rem; font-size:0.6875rem; font-weight:700; border-radius:9999px;
                                     background:var(--color-primary-container); color:var(--color-on-primary);">
                            {{ $unreadNotifications }}
                        </span>
                        @endif
                    </a>

                    {{-- Contact --}}
                    <x-admin.nav-link route="admin.contact.index" icon="mail" label="Messages de contact" />

                    {{-- Paramètres globaux --}}
                    <x-admin.nav-group label="Paramétrage" icon="settings">
                        <x-admin.nav-sub-link route="admin.sites.index" label="Sites IFAM" />
                        <x-admin.nav-sub-link route="admin.settings.index" label="Mon profil" />
                    </x-admin.nav-group>
                @endif

                @if(auth()->user()?->canAccessServiceReports())
                    <x-admin.nav-link route="service.reports.index" icon="assignment" label="Rapports au Directeur" />
                @endif
            </nav>
        </div>

        {{-- Session active (bas de la sidebar) --}}
        <div style="padding:1rem 0.75rem 1rem;">
            <div style="padding:1rem; background:var(--color-surface-container-low); border-radius:0.75rem;
                        display:flex; align-items:center; gap:0.75rem; box-shadow:0 1px 4px rgba(0,0,0,0.02);">
                <span class="material-symbols-outlined" style="color:var(--color-primary-container); font-size:24px;">verified</span>
                <div>
                    <div style="font-size:0.75rem; font-weight:700; color:var(--color-on-surface);">
                        {{ $activeSession->name ?? 'Aucune rentrée active' }}
                    </div>
                    <div style="font-size:0.6875rem; color:var(--color-on-surface-variant);">
                        {{ $userSiteName ? 'IFAM • ' . $userSiteName : 'IFAM Général' }} • {{ $activeSession ? 'Actif' : 'Inactif' }}
                    </div>
                </div>
            </div>
        </div>
    </aside>
    <button id="admin-sidebar-backdrop" type="button" aria-label="Fermer le menu" style="display:none;"></button>

    {{-- ══════════════════════════════════════════════════
         ZONE PRINCIPALE (à droite de la sidebar)
         ══════════════════════════════════════════════════ --}}
    <div id="admin-main-shell" style="padding-left:288px; min-height:100vh;">

        {{-- ─── HEADER fixe ─────────────────────────────────── --}}
        <header id="admin-main-header" style="position:fixed; top:0; left:288px; right:0; height:80px;
                       background:rgba(248,249,255,0.85); backdrop-filter:blur(20px);
                       box-shadow:0 1px 8px rgba(0,0,0,0.04); z-index:40;
                       display:flex; align-items:center; justify-content:space-between; padding:0 2rem;">

            {{-- Barre de recherche --}}
            <button id="admin-sidebar-toggle" type="button" aria-label="Ouvrir le menu" aria-expanded="false"
                    style="display:none; width:40px; height:40px; border:0; border-radius:0.625rem; background:var(--color-surface-container); color:var(--color-on-surface); cursor:pointer;">
                <span class="material-symbols-outlined">menu</span>
            </button>

            <div class="admin-header-search" style="flex:1; max-width:36rem;">
                <div style="position:relative; width:100%; display:flex; align-items:center;">
                    <span class="material-symbols-outlined"
                          style="position:absolute; left:0.875rem; color:var(--color-on-surface-variant); font-size:20px; pointer-events:none;">search</span>
                    <input type="text"
                           placeholder="Rechercher un étudiant, enseignant, cours, paiement…"
                           style="width:100%; height:44px; padding:0 3.5rem 0 2.75rem;
                                  background:var(--color-surface-container-lowest); border-radius:0.75rem;
                                  border:none; font-size:0.75rem; color:var(--color-on-surface);
                                  box-shadow:0 1px 3px rgba(0,0,0,0.02);"
                           onfocus="this.style.boxShadow='0 0 0 2px var(--color-primary-container)'"
                           onblur="this.style.boxShadow='0 1px 3px rgba(0,0,0,0.02)'">
                    <div style="position:absolute; right:0.75rem; padding:0.125rem 0.375rem; border-radius:0.25rem;
                                background:var(--color-surface-container-high); color:var(--color-on-surface-variant);
                                font-size:0.6875rem; font-family:monospace;">⌘K</div>
                </div>
            </div>

            {{-- Actions header --}}
            <div style="display:flex; align-items:center; gap:1rem;">

                {{-- Cloche notification --}}
                <a href="{{ route('admin.notifications.index') }}"
                   style="position:relative; width:40px; height:40px; border-radius:0.75rem;
                          background:var(--color-surface-container-lowest); display:flex; align-items:center; justify-content:center;
                          color:var(--color-on-surface-variant); text-decoration:none;
                          box-shadow:0 1px 4px rgba(0,0,0,0.02); transition:background 0.15s;"
                   onmouseover="this.style.background='var(--color-surface-container)'"
                   onmouseout="this.style.background='var(--color-surface-container-lowest)'">
                    <span class="material-symbols-outlined" style="font-size:22px;">notifications</span>
                    @if(($unreadNotifications ?? 0) > 0)
                    <span style="position:absolute; top:8px; right:8px; width:8px; height:8px; border-radius:9999px;
                                 background:var(--color-primary-container); border:2px solid var(--color-surface);"></span>
                    @endif
                </a>

                {{-- Séparateur --}}
                <div style="width:1px; height:32px; background:var(--color-outline-variant); opacity:0.3;"></div>

                {{-- Profil utilisateur --}}
                <div class="admin-header-profile" style="display:flex; align-items:center; gap:0.75rem; padding-left:0.5rem;">
                    <div class="admin-profile-info" style="text-align:right;">
                        <div style="font-size:0.875rem; font-weight:700; color:var(--color-on-surface); line-height:1.2;">
                            {{ auth()->user()->first_name ?? '' }} {{ auth()->user()->last_name ?? 'Administrateur' }}
                        </div>
                        <div style="font-size:0.6875rem; color:var(--color-on-surface-variant);">
                            {{ $isSuperAdmin ? 'Administrateur Général' : ($isSiteAdmin ? 'Administrateur de Site (' . ($userSiteName ?? 'Local') . ')' : 'Administration') }}
                        </div>
                    </div>
                    @php $authUser = auth()->user(); @endphp
                    @if($authUser->photo_path)
                    <img src="{{ Storage::url($authUser->photo_path) }}" alt="{{ $authUser->full_name }}"
                         style="width:40px; height:40px; border-radius:9999px; object-fit:cover;
                                border:2px solid var(--color-primary-container);
                                box-shadow:0 2px 6px rgba(182,0,80,0.25); flex-shrink:0;">
                    @else
                    <div style="width:40px; height:40px; border-radius:9999px; background:var(--color-primary-container);
                                display:flex; align-items:center; justify-content:center;
                                box-shadow:0 2px 6px rgba(182,0,80,0.25); flex-shrink:0;">
                        <span style="font-size:0.875rem; font-weight:700; color:var(--color-on-primary);">{{ $authUser->initials }}</span>
                    </div>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                        @csrf
                        <button type="submit" title="Déconnexion"
                                style="background:none; border:none; cursor:pointer; color:var(--color-on-surface-variant); display:flex; align-items:center;"
                                onmouseover="this.style.color='var(--color-error)'"
                                onmouseout="this.style.color='var(--color-on-surface-variant)'">
                            <span class="material-symbols-outlined" style="font-size:20px;">logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        {{-- ─── CONTENU PRINCIPAL ────────────────────────────── --}}
        <main id="admin-main-content" style="padding-top:80px; padding-left:2rem; padding-right:2rem; padding-bottom:3rem; min-height:100vh; background:var(--color-surface);">
            @yield('content')
        </main>

    </div>{{-- /pl-sidebar --}}

    {{-- Flash messages --}}
    @if(session('success'))
    <div id="flash-success"
         style="position:fixed; bottom:1.5rem; right:1.5rem; z-index:9999;
                padding:0.875rem 1.25rem; border-radius:0.75rem;
                background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46;
                display:flex; align-items:center; gap:0.75rem;
                box-shadow:0 4px 16px rgba(5,150,105,0.15); font-size:0.875rem; font-weight:600;
                animation: slideUp 0.3s ease-out;">
        <span class="material-symbols-outlined" style="font-size:20px; color:#059669;">check_circle</span>
        {{ session('success') }}
        <button onclick="document.getElementById('flash-success').remove()"
                style="margin-left:0.5rem; background:none; border:none; cursor:pointer; color:#059669; display:flex;">
            <span class="material-symbols-outlined" style="font-size:18px;">close</span>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div id="flash-error"
         style="position:fixed; bottom:1.5rem; right:1.5rem; z-index:9999;
                padding:0.875rem 1.25rem; border-radius:0.75rem;
                background:#fef2f2; border:1px solid #fecaca; color:#991b1b;
                display:flex; align-items:center; gap:0.75rem;
                box-shadow:0 4px 16px rgba(185,28,28,0.12); font-size:0.875rem; font-weight:600;">
        <span class="material-symbols-outlined" style="font-size:20px; color:#dc2626;">error</span>
        {{ session('error') }}
        <button onclick="document.getElementById('flash-error').remove()"
                style="margin-left:0.5rem; background:none; border:none; cursor:pointer; color:#dc2626; display:flex;">
            <span class="material-symbols-outlined" style="font-size:18px;">close</span>
        </button>
    </div>
    @endif

    <style>
        @keyframes slideUp {
            from { opacity:0; transform:translateY(16px); }
            to   { opacity:1; transform:translateY(0); }
        }
    </style>

    <script>
        const adminSidebarToggle = document.getElementById('admin-sidebar-toggle');
        const adminSidebarBackdrop = document.getElementById('admin-sidebar-backdrop');

        function setAdminSidebarOpen(isOpen) {
            document.body.classList.toggle('admin-sidebar-open', isOpen);
            adminSidebarToggle.setAttribute('aria-expanded', String(isOpen));
        }

        adminSidebarToggle.addEventListener('click', function () {
            setAdminSidebarOpen(!document.body.classList.contains('admin-sidebar-open'));
        });
        adminSidebarBackdrop.addEventListener('click', function () {
            setAdminSidebarOpen(false);
        });
        document.querySelectorAll('#sidebar nav a').forEach(function (link) {
            link.addEventListener('click', function () {
                setAdminSidebarOpen(false);
            });
        });
    </script>

    @livewireScripts
    @stack('scripts')
</body>
</html>
