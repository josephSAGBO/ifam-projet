@extends('layouts.role')

@section('title', 'Tableau de bord Alumni')
@section('role_label', 'Espace Alumni')
@section('home_route', route('alumni.dashboard'))

@section('nav_links')
    <a href="{{ route('alumni.dashboard') }}"
       style="height:36px; display:inline-flex; align-items:center; gap:0.375rem; padding:0 0.875rem; border-radius:0.5rem; font-size:0.875rem; font-weight:600; text-decoration:none;
              {{ request()->routeIs('alumni.dashboard') ? 'background:var(--color-primary-container); color:var(--color-on-primary);' : 'color:var(--color-on-surface-variant);' }}">
        <span class="material-symbols-outlined" style="font-size:18px;">dashboard</span>
        Accueil
    </a>
    <a href="{{ route('alumni.posts.index') }}"
       style="height:36px; display:inline-flex; align-items:center; gap:0.375rem; padding:0 0.875rem; border-radius:0.5rem; font-size:0.875rem; font-weight:600; text-decoration:none;
              {{ request()->routeIs('alumni.posts.*') ? 'background:var(--color-primary-container); color:var(--color-on-primary);' : 'color:var(--color-on-surface-variant);' }}">
        <span class="material-symbols-outlined" style="font-size:18px;">photo_library</span>
        Mes publications
    </a>
    <a href="{{ route('alumni.profile.edit') }}"
       style="height:36px; display:inline-flex; align-items:center; gap:0.375rem; padding:0 0.875rem; border-radius:0.5rem; font-size:0.875rem; font-weight:600; text-decoration:none;
              {{ request()->routeIs('alumni.profile.*') ? 'background:var(--color-primary-container); color:var(--color-on-primary);' : 'color:var(--color-on-surface-variant);' }}">
        <span class="material-symbols-outlined" style="font-size:18px;">account_circle</span>
        Mon profil
    </a>
    <a href="{{ route('alumni.public.index') }}" target="_blank"
       style="height:36px; display:inline-flex; align-items:center; gap:0.375rem; padding:0 0.875rem; border-radius:0.5rem; font-size:0.875rem; font-weight:600; text-decoration:none; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined" style="font-size:18px;">public</span>
        Annuaire public
    </a>
    @if(auth()->user()->hasRole('etudiant'))
        <a href="{{ route('student.dashboard') }}"
           style="height:36px; display:inline-flex; align-items:center; gap:0.375rem; padding:0 0.875rem; border-radius:0.5rem; font-size:0.875rem; font-weight:600; text-decoration:none; color:var(--color-primary); background:rgba(228,0,102,0.08);">
            <span class="material-symbols-outlined" style="font-size:18px;">history_edu</span>
            Mon dossier scolaire étudiant
        </a>
    @endif
@endsection

@section('content')

<div style="max-width:1100px; margin:0 auto; padding:2rem 1.5rem;">

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

    {{-- Carte d'accueil & Présentation Alumni --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:2rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); border:1px solid var(--color-outline-variant); margin-bottom:1.75rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1.5rem;">
        <div style="display:flex; align-items:center; gap:1.25rem;">
            @if($alumni?->photo_url)
                <img src="{{ $alumni->photo_url }}" alt="{{ $user->full_name }}"
                     style="width:72px; height:72px; border-radius:50%; object-fit:cover; border:3px solid var(--color-primary-container);">
            @else
                <div style="width:72px; height:72px; border-radius:50%; background:var(--color-primary-container); color:var(--color-on-primary); font-size:1.75rem; font-weight:700; display:flex; align-items:center; justify-content:center;">
                    {{ $user->initials }}
                </div>
            @endif
            <div>
                <div style="font-size:0.8125rem; color:var(--color-primary); font-weight:700; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.25rem;">
                    Bienvenue dans votre réseau des diplômés
                </div>
                <h1 style="font-family:var(--font-display); font-size:1.625rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
                    {{ $user->full_name }}
                </h1>
                <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
                    {{ $alumni?->formation?->name ?? 'Diplômé(e) de l\'IFAM' }}
                    @if($alumni?->date_diplome)
                        • Promotion {{ $alumni->date_diplome->format('Y') }}
                    @endif
                    @if($alumni?->statut_actuel)
                        • <span style="font-weight:600; color:var(--color-on-surface);">{{ $alumni->poste_actuel ? $alumni->poste_actuel . ' chez ' . $alumni->entreprise_actuelle : $alumni->statut_actuel }}</span>
                    @endif
                </p>
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:0.75rem;">
            <a href="{{ route('alumni.posts.create') }}"
               style="height:44px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.25rem; border-radius:0.625rem;
                      background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.875rem; font-weight:700;
                      text-decoration:none; box-shadow:0 3px 10px rgba(228,0,102,0.3);">
                <span class="material-symbols-outlined" style="font-size:20px;">add_photo_alternate</span>
                Publier une création
            </a>
            <a href="{{ route('alumni.profile.edit') }}"
               style="height:44px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.25rem; border-radius:0.625rem;
                      background:var(--color-surface-container-high); color:var(--color-on-surface); font-size:0.875rem; font-weight:600;
                      text-decoration:none; border:1px solid var(--color-outline-variant);">
                <span class="material-symbols-outlined" style="font-size:20px;">edit</span>
                Modifier mon profil
            </a>
        </div>
    </div>

    {{-- Alertes de publications refusées --}}
    @if($rejectedPosts->isNotEmpty())
        <div style="background:#fff1f2; border:1px solid #fecdd3; border-radius:0.875rem; padding:1.25rem; margin-bottom:1.75rem;">
            <div style="display:flex; align-items:center; gap:0.5rem; color:#be123c; font-weight:700; font-size:0.9375rem; margin-bottom:0.75rem;">
                <span class="material-symbols-outlined" style="font-size:20px;">error</span>
                Publications nécessitant une correction ({{ $rejectedPosts->count() }})
            </div>
            <div style="display:flex; flex-direction:column; gap:0.75rem;">
                @foreach($rejectedPosts as $rejPost)
                    <div style="background:#fff; border-radius:0.5rem; padding:0.875rem 1rem; border-left:4px solid #e11d48; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem;">
                        <div>
                            <div style="font-weight:700; color:var(--color-on-surface); font-size:0.875rem;">
                                {{ $rejPost->title }}
                            </div>
                            <div style="font-size:0.8125rem; color:#9f1239; margin-top:0.25rem;">
                                <strong>Motif du refus :</strong> {{ $rejPost->rejection_reason ?? 'Non précisé' }}
                            </div>
                        </div>
                        <a href="{{ route('alumni.posts.edit', $rejPost->id) }}"
                           style="height:32px; padding:0 0.875rem; border-radius:0.375rem; background:#be123c; color:#fff; font-size:0.75rem; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:0.25rem;">
                            <span class="material-symbols-outlined" style="font-size:16px;">edit</span>
                            Corriger et resoumettre
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Statistiques --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1.25rem; margin-bottom:2rem;">
        <div style="background:var(--color-surface-container-lowest); border-radius:0.875rem; padding:1.25rem; border:1px solid var(--color-outline-variant);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.5rem;">
                <span style="font-size:0.8125rem; font-weight:600; color:var(--color-on-surface-variant);">Total publications</span>
                <span class="material-symbols-outlined" style="color:var(--color-primary); font-size:22px;">photo_library</span>
            </div>
            <div style="font-size:1.75rem; font-weight:700; color:var(--color-on-surface);">{{ $postsCount }}</div>
            <div style="font-size:0.75rem; color:var(--color-on-surface-variant); margin-top:0.25rem;">Articles, défilés & créations</div>
        </div>

        <div style="background:var(--color-surface-container-lowest); border-radius:0.875rem; padding:1.25rem; border:1px solid var(--color-outline-variant);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.5rem;">
                <span style="font-size:0.8125rem; font-weight:600; color:var(--color-on-surface-variant);">En ligne / Publiées</span>
                <span class="material-symbols-outlined" style="color:#059669; font-size:22px;">check_circle</span>
            </div>
            <div style="font-size:1.75rem; font-weight:700; color:#059669;">{{ $publishedCount }}</div>
            <div style="font-size:0.75rem; color:var(--color-on-surface-variant); margin-top:0.25rem;">Visibles dans le flux public</div>
        </div>

        <div style="background:var(--color-surface-container-lowest); border-radius:0.875rem; padding:1.25rem; border:1px solid var(--color-outline-variant);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.5rem;">
                <span style="font-size:0.8125rem; font-weight:600; color:var(--color-on-surface-variant);">En cours de modération</span>
                <span class="material-symbols-outlined" style="color:#b45309; font-size:22px;">hourglass_empty</span>
            </div>
            <div style="font-size:1.75rem; font-weight:700; color:#b45309;">{{ $pendingCount }}</div>
            <div style="font-size:0.75rem; color:var(--color-on-surface-variant); margin-top:0.25rem;">En attente de validation</div>
        </div>

        <div style="background:var(--color-surface-container-lowest); border-radius:0.875rem; padding:1.25rem; border:1px solid var(--color-outline-variant);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.5rem;">
                <span style="font-size:0.8125rem; font-weight:600; color:var(--color-on-surface-variant);">Statut du profil</span>
                <span class="material-symbols-outlined" style="color:var(--color-on-surface); font-size:22px;">verified_user</span>
            </div>
            <div style="font-size:1.125rem; font-weight:700; margin-top:0.375rem;
                        {{ $alumni?->is_approved ? 'color:#059669;' : 'color:#b45309;' }}">
                {{ $alumni?->is_approved ? 'Profil validé' : 'En cours de validation' }}
            </div>
            <div style="font-size:0.75rem; color:var(--color-on-surface-variant); margin-top:0.25rem;">
                {{ $alumni?->is_visible_directory ? 'Visible dans l\'annuaire' : 'Masqué de l\'annuaire' }}
            </div>
        </div>
    </div>

    {{-- Vos dernières publications --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.75rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); border:1px solid var(--color-outline-variant);">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0;">
                Vos publications récentes
            </h2>
            <a href="{{ route('alumni.posts.index') }}" style="color:var(--color-primary); font-size:0.8125rem; font-weight:600; text-decoration:none;">
                Toutes mes publications →
            </a>
        </div>

        @if($recentPosts->isEmpty())
            <div style="text-align:center; padding:3rem 1rem; color:var(--color-on-surface-variant);">
                <span class="material-symbols-outlined" style="font-size:48px; color:var(--color-outline); margin-bottom:0.5rem;">post_add</span>
                <p style="font-size:0.9375rem; font-weight:600; margin:0 0 0.5rem;">Vous n'avez pas encore publié de contenu</p>
                <p style="font-size:0.8125rem; margin:0 0 1.25rem;">Partagez vos créations de stylisme, votre atelier, vos défilés ou vos réalisations avec la communauté IFAM.</p>
                <a href="{{ route('alumni.posts.create') }}"
                   style="height:38px; display:inline-flex; align-items:center; gap:0.375rem; padding:0 1rem; border-radius:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.8125rem; font-weight:700; text-decoration:none;">
                    <span class="material-symbols-outlined" style="font-size:18px;">add</span>
                    Créer ma première publication
                </a>
            </div>
        @else
            <div style="display:flex; flex-direction:column; gap:0.875rem;">
                @foreach($recentPosts as $post)
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:0.875rem 1rem; background:var(--color-surface-container-low); border-radius:0.625rem; gap:1rem;">
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            @if($post->cover_url)
                                <img src="{{ $post->cover_url }}" alt="" style="width:44px; height:44px; border-radius:0.5rem; object-fit:cover;">
                            @else
                                <div style="width:44px; height:44px; border-radius:0.5rem; background:var(--color-surface-container-high); display:flex; align-items:center; justify-content:center;">
                                    <span class="material-symbols-outlined" style="font-size:20px; color:var(--color-outline);">image</span>
                                </div>
                            @endif
                            <div>
                                <div style="font-weight:700; color:var(--color-on-surface); font-size:0.9375rem;">
                                    {{ $post->title }}
                                </div>
                                <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                                    {{ $post->media->count() }} photo(s) • Créé le {{ $post->created_at->format('d/m/Y') }}
                                </div>
                            </div>
                        </div>

                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            @if($post->isPublished())
                                <span style="background:#dcfce7; color:#15803d; padding:0.25rem 0.625rem; border-radius:999px; font-size:0.75rem; font-weight:700;">Publié</span>
                            @elseif($post->isPending())
                                <span style="background:#fef3c7; color:#b45309; padding:0.25rem 0.625rem; border-radius:999px; font-size:0.75rem; font-weight:700;">En modération</span>
                            @elseif($post->isRejected())
                                <span style="background:#fee2e2; color:#b91c1c; padding:0.25rem 0.625rem; border-radius:999px; font-size:0.75rem; font-weight:700;">Refusé</span>
                            @else
                                <span style="background:#f3f4f6; color:#4b5563; padding:0.25rem 0.625rem; border-radius:999px; font-size:0.75rem; font-weight:700;">Brouillon</span>
                            @endif

                            <a href="{{ route('alumni.posts.show', $post->id) }}"
                               style="height:32px; padding:0 0.75rem; border-radius:0.375rem; background:var(--color-surface-container-high); color:var(--color-on-surface); font-size:0.75rem; font-weight:600; text-decoration:none; display:inline-flex; align-items:center;">
                                Voir
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

@endsection
