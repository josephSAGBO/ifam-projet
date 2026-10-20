@extends('layouts.admin')

@section('title', 'Profil Alumni — ' . $alumni->full_name)

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem; flex-wrap:wrap; gap:1rem;">
    <div style="display:flex; align-items:center; gap:1rem;">
        <a href="{{ route('admin.alumnis.index') }}"
           style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
                  display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
                Fiche Alumni : {{ $alumni->full_name }}
            </h1>
            <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
                Détails du parcours, situation professionnelle et publications de l'Alumni.
            </p>
        </div>
    </div>

    <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
        <form action="{{ route('admin.alumnis.toggleApproval', $alumni->id) }}" method="POST">
            @csrf
            @method('PATCH')
            @if($alumni->is_approved)
                <button type="submit"
                        style="height:40px; padding:0 1rem; border-radius:0.625rem; background:#fee2e2; color:#dc2626; border:none; cursor:pointer; font-size:0.875rem; font-weight:600; display:inline-flex; align-items:center; gap:0.375rem;">
                    <span class="material-symbols-outlined" style="font-size:18px;">cancel</span>
                    Retirer l'approbation
                </button>
            @else
                <button type="submit"
                        style="height:40px; padding:0 1rem; border-radius:0.625rem; background:#dcfce7; color:#15803d; border:none; cursor:pointer; font-size:0.875rem; font-weight:700; display:inline-flex; align-items:center; gap:0.375rem;">
                    <span class="material-symbols-outlined" style="font-size:18px;">check_circle</span>
                    Approuver le profil
                </button>
            @endif
        </form>

        <a href="{{ route('admin.alumnis.edit', $alumni) }}"
           style="height:40px; padding:0 1.25rem; border-radius:0.625rem; background:var(--color-surface-container-high);
                  color:var(--color-on-surface); font-size:0.875rem; font-weight:600; text-decoration:none;
                  display:inline-flex; align-items:center; gap:0.5rem;">
            <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
            Modifier
        </a>

        @if($alumni->is_approved && $alumni->is_visible_directory)
            <a href="{{ route('alumni.public.show', $alumni->id) }}" target="_blank"
               style="height:40px; padding:0 1rem; border-radius:0.625rem; background:var(--color-surface-container-high);
                      color:var(--color-on-surface); font-size:0.875rem; font-weight:600; text-decoration:none;
                      display:inline-flex; align-items:center; gap:0.375rem; border:1px solid var(--color-outline-variant);">
                <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                Voir la fiche publique
            </a>
        @endif
    </div>
</div>

<div style="display:grid; grid-template-columns: 320px 1fr; gap:1.75rem; align-items:start;">

    {{-- Carte Identité & Statut --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.75rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); text-align:center; border:1px solid var(--color-outline-variant);">
        @if($alumni->photo_url)
            <img src="{{ $alumni->photo_url }}" alt="{{ $alumni->full_name }}"
                 style="width:120px; height:120px; border-radius:50%; object-fit:cover; margin:0 auto 1.25rem; border:4px solid var(--color-outline-variant);">
        @else
            <div style="width:120px; height:120px; border-radius:50%; background:var(--color-primary-container); color:var(--color-on-primary);
                        display:flex; align-items:center; justify-content:center; font-weight:700; font-size:2.5rem; margin:0 auto 1.25rem;">
                {{ $alumni->initials }}
            </div>
        @endif

        <h2 style="font-size:1.25rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem;">
            {{ $alumni->full_name }}
        </h2>

        <div style="display:flex; justify-content:center; gap:0.5rem; flex-wrap:wrap; margin-bottom:1.25rem;">
            @if($alumni->is_approved)
                <span style="background:#dcfce7; color:#15803d; padding:0.25rem 0.625rem; border-radius:999px; font-size:0.75rem; font-weight:700; display:inline-flex; align-items:center; gap:0.25rem;">
                    <span class="material-symbols-outlined" style="font-size:14px;">check_circle</span>
                    Profil approuvé
                </span>
            @else
                <span style="background:#fef3c7; color:#b45309; padding:0.25rem 0.625rem; border-radius:999px; font-size:0.75rem; font-weight:700; display:inline-flex; align-items:center; gap:0.25rem;">
                    <span class="material-symbols-outlined" style="font-size:14px;">hourglass_empty</span>
                    En attente d'approbation
                </span>
            @endif

            @if($alumni->is_visible_directory)
                <span style="background:#e0f2fe; color:#0369a1; padding:0.25rem 0.625rem; border-radius:999px; font-size:0.75rem; font-weight:700;">
                    Visible dans l'annuaire
                </span>
            @else
                <span style="background:#f3f4f6; color:#6b7280; padding:0.25rem 0.625rem; border-radius:999px; font-size:0.75rem; font-weight:700;">
                    Masqué de l'annuaire
                </span>
            @endif
        </div>

        <div style="border-top:1px solid var(--color-outline-variant); padding-top:1.25rem; text-align:left; display:flex; flex-direction:column; gap:0.875rem; font-size:0.875rem;">
            <div>
                <span style="font-size:0.75rem; font-weight:600; text-transform:uppercase; color:var(--color-on-surface-variant); display:block;">Email</span>
                <span style="color:var(--color-on-surface); font-weight:600;">{{ $alumni->email ?? 'Non renseigné' }}</span>
            </div>

            <div>
                <span style="font-size:0.75rem; font-weight:600; text-transform:uppercase; color:var(--color-on-surface-variant); display:block;">Téléphone</span>
                <span style="color:var(--color-on-surface);">{{ $alumni->telephone ?? 'Non renseigné' }}</span>
            </div>

            <div>
                <span style="font-size:0.75rem; font-weight:600; text-transform:uppercase; color:var(--color-on-surface-variant); display:block;">Résidence / Ville</span>
                <span style="color:var(--color-on-surface);">{{ $alumni->lieu_residence ?? 'Non renseigné' }}</span>
            </div>

            @if($alumni->linkedin_url)
                <div>
                    <span style="font-size:0.75rem; font-weight:600; text-transform:uppercase; color:var(--color-on-surface-variant); display:block;">LinkedIn</span>
                    <a href="{{ $alumni->linkedin_url }}" target="_blank" style="color:var(--color-primary); word-break:break-all;">
                        {{ $alumni->linkedin_url }}
                    </a>
                </div>
            @endif

            @if($alumni->portfolio_url)
                <div>
                    <span style="font-size:0.75rem; font-weight:600; text-transform:uppercase; color:var(--color-on-surface-variant); display:block;">Portfolio / Site</span>
                    <a href="{{ $alumni->portfolio_url }}" target="_blank" style="color:var(--color-primary); word-break:break-all;">
                        {{ $alumni->portfolio_url }}
                    </a>
                </div>
            @endif

            @if($alumni->studentProfile)
                <div style="background:var(--color-surface-container-low); padding:0.75rem; border-radius:0.5rem; margin-top:0.5rem;">
                    <span style="font-size:0.6875rem; font-weight:700; text-transform:uppercase; color:var(--color-primary); display:block;">
                        Dossier étudiant IFAM Connect
                    </span>
                    <div style="font-weight:700; color:var(--color-on-surface); font-size:0.8125rem;">
                        Matricule : {{ $alumni->studentProfile->user->matricule ?? '—' }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Détails Métier, Formations et Publications --}}
    <div style="display:flex; flex-direction:column; gap:1.75rem;">

        {{-- Situation Professionnelle --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); border:1px solid var(--color-outline-variant);">
            <h3 style="font-size:1.0625rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="color:var(--color-primary);">work</span>
                Situation professionnelle & Insertion
            </h3>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-bottom:1.25rem;">
                <div style="background:var(--color-surface-container-low); padding:1rem; border-radius:0.75rem;">
                    <div style="font-size:0.75rem; color:var(--color-on-surface-variant); font-weight:600; text-transform:uppercase;">Statut actuel</div>
                    <div style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin-top:0.25rem;">
                        {{ $alumni->statut_actuel ?? 'Non renseigné' }}
                    </div>
                </div>

                <div style="background:var(--color-surface-container-low); padding:1rem; border-radius:0.75rem;">
                    <div style="font-size:0.75rem; color:var(--color-on-surface-variant); font-weight:600; text-transform:uppercase;">Poste / Métier</div>
                    <div style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin-top:0.25rem;">
                        {{ $alumni->poste_actuel ?? '—' }}
                    </div>
                </div>

                <div style="background:var(--color-surface-container-low); padding:1rem; border-radius:0.75rem;">
                    <div style="font-size:0.75rem; color:var(--color-on-surface-variant); font-weight:600; text-transform:uppercase;">Entreprise / Atelier</div>
                    <div style="font-size:1rem; font-weight:700; color:var(--color-primary); margin-top:0.25rem;">
                        {{ $alumni->entreprise_actuelle ?? '—' }}
                    </div>
                </div>
            </div>

            @if($alumni->bio)
                <div>
                    <h4 style="font-size:0.875rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem;">
                        Biographie professionnelle
                    </h4>
                    <p style="font-size:0.875rem; color:var(--color-on-surface); line-height:1.6; margin:0; white-space:pre-line; background:var(--color-surface-container-low); padding:1rem; border-radius:0.5rem;">
                        {{ $alumni->bio }}
                    </p>
                </div>
            @endif
        </div>

        {{-- Formations Validées --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); border:1px solid var(--color-outline-variant);">
            <h3 style="font-size:1.0625rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="color:var(--color-primary);">school</span>
                Formations IFAM complétées
            </h3>

            @if($alumni->completed_formations->isNotEmpty())
                <div style="display:flex; flex-direction:column; gap:0.75rem;">
                    @foreach($alumni->completed_formations as $item)
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:0.875rem 1rem; background:var(--color-surface-container-low); border-radius:0.5rem; flex-wrap:wrap; gap:0.5rem;">
                            <div>
                                <div style="font-weight:700; color:var(--color-on-surface);">
                                    {{ $item['formation']->name ?? 'Formation' }}
                                </div>
                                @if(!empty($item['parcours']))
                                    <div style="font-size:0.75rem; color:var(--color-primary); font-weight:600;">
                                        Parcours : {{ $item['parcours']->nom }}
                                    </div>
                                @endif
                            </div>
                            <div style="font-size:0.8125rem; font-weight:600; color:var(--color-on-surface-variant);">
                                {{ $item['session'] ?? ($item['year'] ? 'Promotion ' . $item['year'] : '') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="font-size:0.875rem; color:var(--color-on-surface-variant);">
                    Formation principale : <strong>{{ $alumni->formation->name ?? 'Non spécifiée' }}</strong>
                    @if($alumni->date_diplome)
                        (Diplôme obtenu en {{ $alumni->date_diplome->format('Y') }})
                    @endif
                </div>
            @endif
        </div>

        {{-- Publications de l'Alumni --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); border:1px solid var(--color-outline-variant);">
            <h3 style="font-size:1.0625rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="color:var(--color-primary);">rate_review</span>
                Publications de cet Alumni ({{ $alumni->posts->count() }})
            </h3>

            @if($alumni->posts->isEmpty())
                <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
                    Cet alumni n'a pas encore rédigé d'actualité ou de réalisation.
                </p>
            @else
                <div style="display:flex; flex-direction:column; gap:0.75rem;">
                    @foreach($alumni->posts as $post)
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:0.875rem 1rem; background:var(--color-surface-container-low); border-radius:0.5rem; gap:1rem;">
                            <div style="display:flex; align-items:center; gap:0.75rem;">
                                @if($post->cover_url)
                                    <img src="{{ $post->cover_url }}" alt="" style="width:40px; height:40px; border-radius:0.375rem; object-fit:cover;">
                                @else
                                    <div style="width:40px; height:40px; border-radius:0.375rem; background:var(--color-surface-container-high); display:flex; align-items:center; justify-content:center;">
                                        <span class="material-symbols-outlined" style="font-size:20px; color:var(--color-outline);">image</span>
                                    </div>
                                @endif
                                <div>
                                    <div style="font-weight:700; color:var(--color-on-surface); font-size:0.875rem;">
                                        {{ $post->title }}
                                    </div>
                                    <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                                        {{ $post->media->count() }} photo(s) • {{ $post->created_at->format('d/m/Y') }}
                                    </div>
                                </div>
                            </div>

                            <div style="display:flex; align-items:center; gap:0.75rem;">
                                @if($post->isPublished())
                                    <span style="background:#dcfce7; color:#15803d; padding:0.25rem 0.5rem; border-radius:999px; font-size:0.6875rem; font-weight:700;">Publié</span>
                                @elseif($post->isPending())
                                    <span style="background:#fef3c7; color:#b45309; padding:0.25rem 0.5rem; border-radius:999px; font-size:0.6875rem; font-weight:700;">En attente</span>
                                @elseif($post->isRejected())
                                    <span style="background:#fee2e2; color:#b91c1c; padding:0.25rem 0.5rem; border-radius:999px; font-size:0.6875rem; font-weight:700;">Refusé</span>
                                @else
                                    <span style="background:#f3f4f6; color:#4b5563; padding:0.25rem 0.5rem; border-radius:999px; font-size:0.6875rem; font-weight:700;">Brouillon</span>
                                @endif

                                <a href="{{ route('admin.alumnis.posts.show', $post->id) }}"
                                   style="height:32px; padding:0 0.75rem; border-radius:0.375rem; background:var(--color-surface-container-high); color:var(--color-on-surface); font-size:0.75rem; font-weight:600; text-decoration:none; display:inline-flex; align-items:center;">
                                    Examiner
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

</div>

@endsection
