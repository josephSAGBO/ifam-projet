@extends('layouts.admin')

@section('title', 'Gestion des Alumni')

@section('content')

{{-- Header section --}}
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.75rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Espace Alumni & Diplômés
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Suivi des anciens étudiants de l'IFAM, insertion professionnelle et réseau des diplômés.
        </p>
    </div>
    <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
        <a href="{{ route('alumni.public.index') }}" target="_blank"
           style="height:42px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1rem; border-radius:0.625rem;
                  background:var(--color-surface-container-high); color:var(--color-on-surface); font-size:0.875rem; font-weight:600;
                  text-decoration:none; border:1px solid var(--color-outline-variant);">
            <span class="material-symbols-outlined" style="font-size:18px;">open_in_new</span>
            Annuaire public
        </a>
        <a href="{{ route('admin.alumnis.posts.index') }}"
           style="height:42px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1rem; border-radius:0.625rem;
                  background:var(--color-surface-container-high); color:var(--color-on-surface); font-size:0.875rem; font-weight:600;
                  text-decoration:none; border:1px solid var(--color-outline-variant); position:relative;">
            <span class="material-symbols-outlined" style="font-size:18px;">rate_review</span>
            Publications Alumni
            @if($pendingPostsCount > 0)
            <span style="background:var(--color-error); color:#fff; border-radius:999px; font-size:0.6875rem; font-weight:700; padding:0.125rem 0.5rem; margin-left:0.25rem;">
                {{ $pendingPostsCount }}
            </span>
            @endif
        </a>
        <a href="{{ route('admin.alumnis.create') }}"
           style="height:42px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.25rem; border-radius:0.625rem;
                  background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.875rem; font-weight:700;
                  text-decoration:none; box-shadow:0 3px 10px rgba(228,0,102,0.3); transition:all 0.15s;">
            <span class="material-symbols-outlined" style="font-size:20px;">person_add</span>
            Ajouter un Alumni
        </a>
    </div>
</div>

@if(session('success'))
    <div style="background:#dcfce7; color:#15803d; padding:1rem 1.25rem; border-radius:0.75rem; font-size:0.9375rem; font-weight:600; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.5rem;">
        <span class="material-symbols-outlined">check_circle</span>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background:#fee2e2; color:#b91c1c; padding:1rem 1.25rem; border-radius:0.75rem; font-size:0.9375rem; font-weight:600; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.5rem;">
        <span class="material-symbols-outlined">error</span>
        {{ session('error') }}
    </div>
@endif

{{-- Navigation par Onglets --}}
<div style="display:flex; gap:0.5rem; margin-bottom:1.5rem; border-bottom:1px solid var(--color-outline-variant); padding-bottom:0.5rem;">
    <a href="{{ route('admin.alumnis.index', ['tab' => 'registered']) }}"
       style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.9375rem; font-weight:600; text-decoration:none;
              {{ $tab !== 'candidates' ? 'background:var(--color-primary-container); color:var(--color-on-primary);' : 'color:var(--color-on-surface-variant);' }}">
        <span class="material-symbols-outlined" style="font-size:20px;">school</span>
        Alumni enregistrés ({{ $alumnis->total() }})
    </a>
    <a href="{{ route('admin.alumnis.index', ['tab' => 'candidates']) }}"
       style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.9375rem; font-weight:600; text-decoration:none; position:relative;
              {{ $tab === 'candidates' ? 'background:var(--color-primary-container); color:var(--color-on-primary);' : 'color:var(--color-on-surface-variant);' }}">
        <span class="material-symbols-outlined" style="font-size:20px;">verified</span>
        Candidats éligibles à la diplomation ({{ $eligibleCandidates->count() }})
        @if($eligibleCandidates->count() > 0 && $tab !== 'candidates')
        <span style="background:var(--color-primary); color:#fff; border-radius:999px; font-size:0.6875rem; font-weight:700; padding:0.125rem 0.5rem;">
            {{ $eligibleCandidates->count() }}
        </span>
        @endif
    </a>
</div>

@if($tab === 'candidates')
    {{-- Onglet : Candidats éligibles détectés par le système --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:1.5rem;">
        <div style="margin-bottom:1.25rem;">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
                Étudiants ayant achevé leur parcours de formation
            </h2>
            <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
                Ces étudiants ont validé l'étape finale de leur parcours ou formation autonome, et ne possèdent plus d'inscriptions en cours. Vous pouvez leur accorder formellement le statut Alumni.
            </p>
        </div>

        @if($eligibleCandidates->isEmpty())
            <div style="text-align:center; padding:3rem 1rem; color:var(--color-on-surface-variant);">
                <span class="material-symbols-outlined" style="font-size:48px; color:var(--color-outline); margin-bottom:0.75rem;">school</span>
                <p style="font-size:0.9375rem; font-weight:600; margin:0 0 0.25rem;">Aucun étudiant actuellement en attente de diplomation</p>
                <p style="font-size:0.8125rem; margin:0;">Les étudiants termineront automatiquement dans cette liste dès validation de l'ultime étape de leur parcours.</p>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; text-align:left; font-size:0.875rem;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--color-outline-variant); color:var(--color-on-surface-variant);">
                            <th style="padding:0.75rem 1rem;">Étudiant</th>
                            <th style="padding:0.75rem 1rem;">Matricule</th>
                            <th style="padding:0.75rem 1rem;">Formations & Parcours achevés</th>
                            <th style="padding:0.75rem 1rem; text-align:right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($eligibleCandidates as $candidate)
                            @php
                                $candidateUser = $candidate->user;
                            @endphp
                            <tr style="border-bottom:1px solid var(--color-outline-variant);">
                                <td style="padding:1rem;">
                                    <div style="display:flex; align-items:center; gap:0.75rem;">
                                        @if($candidateUser?->photo_path)
                                            <img src="{{ Storage::url($candidateUser->photo_path) }}" alt=""
                                                 style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                                        @else
                                            <div style="width:40px; height:40px; border-radius:50%; background:var(--color-surface-container-high);
                                                        display:flex; align-items:center; justify-content:center; font-weight:700; color:var(--color-primary);">
                                                {{ $candidateUser?->initials ?? 'ET' }}
                                            </div>
                                        @endif
                                        <div>
                                            <div style="font-weight:700; color:var(--color-on-surface);">
                                                {{ $candidateUser?->full_name }}
                                            </div>
                                            <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                                                {{ $candidateUser?->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding:1rem; color:var(--color-on-surface-variant); font-family:monospace; font-weight:600;">
                                    {{ $candidateUser?->matricule ?? '—' }}
                                </td>
                                <td style="padding:1rem;">
                                    @foreach($candidate->enrollments->where('status', 'completed') as $enr)
                                        <div style="margin-bottom:0.25rem;">
                                            <span style="font-weight:600; color:var(--color-on-surface);">{{ $enr->formation->name }}</span>
                                            @if($enr->formation->parcours)
                                                <span style="font-size:0.75rem; color:var(--color-primary); background:rgba(228,0,102,0.08); padding:0.125rem 0.375rem; border-radius:0.25rem;">
                                                    Parcours : {{ $enr->formation->parcours->nom }}
                                                </span>
                                            @endif
                                            <span style="font-size:0.75rem; color:var(--color-on-surface-variant);">({{ $enr->academicSession?->name }})</span>
                                        </div>
                                    @endforeach
                                </td>
                                <td style="padding:1rem; text-align:right;">
                                    <form action="{{ route('admin.alumnis.convert', $candidate->id) }}" method="POST"
                                          onsubmit="return confirm('Confirmez-vous la diplomation et l\'attribution du statut Alumni pour {{ addslashes($candidateUser?->full_name) }} ?');"
                                          style="display:inline;">
                                        @csrf
                                        <button type="submit"
                                                style="height:36px; padding:0 1rem; border-radius:0.5rem; border:none;
                                                       background:#059669; color:#fff; font-size:0.8125rem; font-weight:700; cursor:pointer;
                                                       display:inline-flex; align-items:center; gap:0.375rem; box-shadow:0 2px 6px rgba(5,150,105,0.25);">
                                            <span class="material-symbols-outlined" style="font-size:18px;">workspace_premium</span>
                                            Diplômer & Convertir en Alumni
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@else
    {{-- Onglet : Alumni enregistrés --}}

    {{-- Search and Filter Bar --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:1.25rem; margin-bottom:1.5rem;">
        <form action="{{ route('admin.alumnis.index') }}" method="GET" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:center;">
            <input type="hidden" name="tab" value="registered">
            <div style="flex:1; min-width:240px; position:relative;">
                <span class="material-symbols-outlined" style="position:absolute; left:0.875rem; top:50%; transform:translateY(-50%); color:var(--color-on-surface-variant); font-size:20px;">
                    search
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Rechercher par nom, matricule, email, entreprise, poste ou ville..."
                       style="width:100%; box-sizing:border-box; height:42px; padding:0 1rem 0 2.5rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.875rem; color:var(--color-on-surface);">
            </div>

            <div style="width:200px;">
                <select name="formation_id"
                        style="width:100%; box-sizing:border-box; height:42px; padding:0 0.875rem; border-radius:0.625rem;
                               border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                               font-size:0.875rem; color:var(--color-on-surface);">
                    <option value="">Toutes les formations</option>
                    @foreach($formations as $f)
                        <option value="{{ $f->id }}" {{ request('formation_id') == $f->id ? 'selected' : '' }}>
                            {{ $f->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="width:180px;">
                <select name="statut_actuel"
                        style="width:100%; box-sizing:border-box; height:42px; padding:0 0.875rem; border-radius:0.625rem;
                               border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                               font-size:0.875rem; color:var(--color-on-surface);">
                    <option value="">Tous les statuts</option>
                    @foreach($statutsPro as $val => $label)
                        <option value="{{ $val }}" {{ request('statut_actuel') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div style="width:160px;">
                <select name="approval"
                        style="width:100%; box-sizing:border-box; height:42px; padding:0 0.875rem; border-radius:0.625rem;
                               border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                               font-size:0.875rem; color:var(--color-on-surface);">
                    <option value="">Tous les profils</option>
                    <option value="1" {{ request('approval') === '1' ? 'selected' : '' }}>Approuvés</option>
                    <option value="0" {{ request('approval') === '0' ? 'selected' : '' }}>Non approuvés</option>
                </select>
            </div>

            <button type="submit"
                    style="height:42px; padding:0 1.25rem; border-radius:0.625rem; border:none;
                           background:var(--color-surface-container-high); color:var(--color-on-surface);
                           font-size:0.875rem; font-weight:600; cursor:pointer;">
                Filtrer
            </button>

            @if(request()->anyFilled(['search', 'formation_id', 'statut_actuel', 'approval']))
                <a href="{{ route('admin.alumnis.index', ['tab' => 'registered']) }}"
                   style="height:42px; display:inline-flex; align-items:center; padding:0 0.75rem; color:var(--color-on-surface-variant);
                          font-size:0.8125rem; text-decoration:none;">
                    Réinitialiser
                </a>
            @endif
        </form>
    </div>

    {{-- Grille / Liste des Alumni --}}
    @if($alumnis->isEmpty())
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:3.5rem 1rem; text-align:center; color:var(--color-on-surface-variant);">
            <span class="material-symbols-outlined" style="font-size:56px; color:var(--color-outline); margin-bottom:0.75rem;">school</span>
            <p style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem;">Aucun profil Alumni trouvé</p>
            <p style="font-size:0.875rem; margin:0 0 1.5rem;">Aucun enregistrement ne correspond à vos critères de recherche.</p>
            <a href="{{ route('admin.alumnis.create') }}"
               style="height:40px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.25rem; border-radius:0.625rem;
                      background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.875rem; font-weight:700; text-decoration:none;">
                <span class="material-symbols-outlined" style="font-size:18px;">add</span>
                Ajouter un Alumni
            </a>
        </div>
    @else
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden;">
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; text-align:left; font-size:0.875rem;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--color-outline-variant); color:var(--color-on-surface-variant); background:var(--color-surface-container-low);">
                            <th style="padding:1rem;">Alumni</th>
                            <th style="padding:1rem;">Formation & Diplôme</th>
                            <th style="padding:1rem;">Situation pro</th>
                            <th style="padding:1rem;">Validation</th>
                            <th style="padding:1rem;">Annuaire public</th>
                            <th style="padding:1rem;">Publications</th>
                            <th style="padding:1rem; text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alumnis as $alumni)
                            <tr style="border-bottom:1px solid var(--color-outline-variant); transition:background 0.15s;"
                                onmouseover="this.style.background='var(--color-surface-container-lowest)'"
                                onmouseout="this.style.background=''">
                                <td style="padding:1rem;">
                                    <div style="display:flex; align-items:center; gap:0.75rem;">
                                        @if($alumni->photo_url)
                                            <img src="{{ $alumni->photo_url }}" alt=""
                                                 style="width:42px; height:42px; border-radius:50%; object-fit:cover;">
                                        @else
                                            <div style="width:42px; height:42px; border-radius:50%; background:var(--color-surface-container-high);
                                                        display:flex; align-items:center; justify-content:center; font-weight:700; color:var(--color-primary);">
                                                {{ $alumni->initials }}
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('admin.alumnis.show', $alumni->id) }}"
                                               style="font-weight:700; color:var(--color-on-surface); text-decoration:none;">
                                                {{ $alumni->full_name }}
                                            </a>
                                            <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                                                {{ $alumni->email ?? 'Sans email' }}
                                                @if($alumni->studentProfile?->user?->matricule)
                                                    • <span style="font-family:monospace;">{{ $alumni->studentProfile->user->matricule }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding:1rem;">
                                    <div style="font-weight:600; color:var(--color-on-surface);">
                                        {{ $alumni->formation->name ?? 'Formation non spécifiée' }}
                                    </div>
                                    <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                                        {{ $alumni->date_diplome ? 'Promo ' . $alumni->date_diplome->format('Y') : 'Année non renseignée' }}
                                    </div>
                                </td>
                                <td style="padding:1rem;">
                                    @if($alumni->statut_actuel)
                                        <div style="font-weight:600; color:var(--color-on-surface);">
                                            {{ $alumni->poste_actuel ?? $alumni->statut_actuel }}
                                        </div>
                                        @if($alumni->entreprise_actuelle)
                                            <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                                                {{ $alumni->entreprise_actuelle }}
                                            </div>
                                        @endif
                                    @else
                                        <span style="color:var(--color-on-surface-variant); font-size:0.8125rem;">Non renseignée</span>
                                    @endif
                                </td>
                                <td style="padding:1rem;">
                                    <form action="{{ route('admin.alumnis.toggleApproval', $alumni->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        @if($alumni->is_approved)
                                            <button type="submit" title="Cliquez pour retirer l'approbation"
                                                    style="background:#dcfce7; color:#15803d; border:none; padding:0.25rem 0.625rem; border-radius:999px; font-size:0.75rem; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:0.25rem;">
                                                <span class="material-symbols-outlined" style="font-size:14px;">check_circle</span>
                                                Approuvé
                                            </button>
                                        @else
                                            <button type="submit" title="Cliquez pour approuver"
                                                    style="background:#fef3c7; color:#b45309; border:none; padding:0.25rem 0.625rem; border-radius:999px; font-size:0.75rem; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:0.25rem;">
                                                <span class="material-symbols-outlined" style="font-size:14px;">hourglass_empty</span>
                                                En attente
                                            </button>
                                        @endif
                                    </form>
                                </td>
                                <td style="padding:1rem;">
                                    <form action="{{ route('admin.alumnis.toggleDirectory', $alumni->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        @if($alumni->is_visible_directory)
                                            <button type="submit" title="Cliquez pour masquer de l'annuaire"
                                                    style="background:none; border:none; color:#059669; cursor:pointer; display:inline-flex; align-items:center; gap:0.25rem; font-size:0.8125rem; font-weight:600;">
                                                <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                                                Visible
                                            </button>
                                        @else
                                            <button type="submit" title="Cliquez pour rendre visible dans l'annuaire"
                                                    style="background:none; border:none; color:#6b7280; cursor:pointer; display:inline-flex; align-items:center; gap:0.25rem; font-size:0.8125rem; font-weight:600;">
                                                <span class="material-symbols-outlined" style="font-size:18px;">visibility_off</span>
                                                Masqué
                                            </button>
                                        @endif
                                    </form>
                                </td>
                                <td style="padding:1rem;">
                                    <span style="font-weight:700; color:var(--color-on-surface);">{{ $alumni->posts->count() }}</span>
                                    <span style="font-size:0.75rem; color:var(--color-on-surface-variant);">actu(s)</span>
                                </td>
                                <td style="padding:1rem; text-align:right;">
                                    <div style="display:inline-flex; align-items:center; gap:0.375rem;">
                                        <a href="{{ route('admin.alumnis.show', $alumni->id) }}"
                                           style="width:32px; height:32px; border-radius:0.375rem; display:inline-flex; align-items:center; justify-content:center;
                                                  background:var(--color-surface-container-high); color:var(--color-on-surface); text-decoration:none;"
                                           title="Voir la fiche">
                                            <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                                        </a>
                                        <a href="{{ route('admin.alumnis.edit', $alumni->id) }}"
                                           style="width:32px; height:32px; border-radius:0.375rem; display:inline-flex; align-items:center; justify-content:center;
                                                  background:var(--color-surface-container-high); color:var(--color-on-surface); text-decoration:none;"
                                           title="Modifier">
                                            <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                                        </a>
                                        <form action="{{ route('admin.alumnis.destroy', $alumni->id) }}" method="POST"
                                              onsubmit="return confirm('Confirmez-vous la suppression de ce profil Alumni ? (Le dossier étudiant d\'origine restera intact)');"
                                              style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    style="width:32px; height:32px; border-radius:0.375rem; border:none; display:inline-flex; align-items:center; justify-content:center;
                                                           background:#fee2e2; color:#b91c1c; cursor:pointer;"
                                                    title="Supprimer">
                                                <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($alumnis->hasPages())
                <div style="padding:1.25rem; border-top:1px solid var(--color-outline-variant);">
                    {{ $alumnis->links() }}
                </div>
            @endif
        </div>
    @endif
@endif

@endsection
