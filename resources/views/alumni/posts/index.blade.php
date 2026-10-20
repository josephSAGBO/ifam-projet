@extends('layouts.role')

@section('title', 'Mes Publications Alumni')
@section('role_label', 'Espace Alumni')
@section('home_route', route('alumni.dashboard'))

@section('nav_links')
    <a href="{{ route('alumni.dashboard') }}"
       style="height:36px; display:inline-flex; align-items:center; gap:0.375rem; padding:0 0.875rem; border-radius:0.5rem; font-size:0.875rem; font-weight:600; text-decoration:none; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined" style="font-size:18px;">dashboard</span>
        Accueil
    </a>
    <a href="{{ route('alumni.posts.index') }}"
       style="height:36px; display:inline-flex; align-items:center; gap:0.375rem; padding:0 0.875rem; border-radius:0.5rem; font-size:0.875rem; font-weight:600; text-decoration:none; background:var(--color-primary-container); color:var(--color-on-primary);">
        <span class="material-symbols-outlined" style="font-size:18px;">photo_library</span>
        Mes publications
    </a>
    <a href="{{ route('alumni.profile.edit') }}"
       style="height:36px; display:inline-flex; align-items:center; gap:0.375rem; padding:0 0.875rem; border-radius:0.5rem; font-size:0.875rem; font-weight:600; text-decoration:none; color:var(--color-on-surface-variant);">
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
            Mon dossier scolaire
        </a>
    @endif
@endsection

@section('content')

<div style="max-width:1050px; margin:0 auto; padding:2rem 1.5rem;">

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem; flex-wrap:wrap; gap:1rem;">
        <div>
            <h1 style="font-family:var(--font-display); font-size:1.625rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
                Mes Publications & Créations
            </h1>
            <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
                Gérez vos articles, vos créations de mode et suivez leur état de validation par la direction.
            </p>
        </div>

        <a href="{{ route('alumni.posts.create') }}"
           style="height:44px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.25rem; border-radius:0.625rem;
                  background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.875rem; font-weight:700;
                  text-decoration:none; box-shadow:0 3px 10px rgba(228,0,102,0.3);">
            <span class="material-symbols-outlined" style="font-size:20px;">add_photo_alternate</span>
            Nouvelle publication
        </a>
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

    @if($posts->isEmpty())
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:4rem 1.5rem; text-align:center; color:var(--color-on-surface-variant); border:1px solid var(--color-outline-variant);">
            <span class="material-symbols-outlined" style="font-size:56px; color:var(--color-outline); margin-bottom:0.75rem;">photo_library</span>
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem;">
                Vous n'avez pas encore de publication
            </h2>
            <p style="font-size:0.875rem; max-width:500px; margin:0 auto 1.5rem;">
                Vous pouvez ajouter jusqu'à 8 photographies par publication pour illustrer vos créations textiles, vos tenues, vos défilés ou vos réussites professionnelles.
            </p>
            <a href="{{ route('alumni.posts.create') }}"
               style="height:42px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.25rem; border-radius:0.625rem;
                      background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.875rem; font-weight:700; text-decoration:none;">
                <span class="material-symbols-outlined" style="font-size:18px;">add</span>
                Créer une publication
            </a>
        </div>
    @else
        <div style="display:flex; flex-direction:column; gap:1.25rem;">
            @foreach($posts as $post)
                <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); border:1px solid var(--color-outline-variant); display:grid; grid-template-columns:140px 1fr auto; gap:1.5rem; align-items:center;">
                    {{-- Visuel de couverture --}}
                    <div style="width:140px; height:105px; border-radius:0.75rem; background:var(--color-surface-container-high); overflow:hidden; position:relative;">
                        @if($post->cover_url)
                            <img src="{{ $post->cover_url }}" alt="" style="width:100%; height:100%; object-fit:cover;">
                        @else
                            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--color-on-surface-variant);">
                                <span class="material-symbols-outlined" style="font-size:32px;">image</span>
                            </div>
                        @endif
                        <span style="position:absolute; bottom:0.375rem; right:0.375rem; background:rgba(0,0,0,0.65); color:#fff; font-size:0.625rem; font-weight:700; padding:0.125rem 0.375rem; border-radius:0.25rem; display:flex; align-items:center; gap:0.25rem;">
                            <span class="material-symbols-outlined" style="font-size:10px;">photo_library</span>
                            {{ $post->media->count() }}
                        </span>
                    </div>

                    {{-- Détails texte & statuts --}}
                    <div>
                        <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.375rem; flex-wrap:wrap;">
                            @if($post->isPublished())
                                <span style="background:#dcfce7; color:#15803d; padding:0.25rem 0.625rem; border-radius:999px; font-size:0.75rem; font-weight:700; display:inline-flex; align-items:center; gap:0.25rem;">
                                    <span class="material-symbols-outlined" style="font-size:14px;">check_circle</span>
                                    En ligne / Publié
                                </span>
                            @elseif($post->isPending())
                                <span style="background:#fef3c7; color:#b45309; padding:0.25rem 0.625rem; border-radius:999px; font-size:0.75rem; font-weight:700; display:inline-flex; align-items:center; gap:0.25rem;">
                                    <span class="material-symbols-outlined" style="font-size:14px;">hourglass_empty</span>
                                    En attente de modération
                                </span>
                            @elseif($post->isRejected())
                                <span style="background:#fee2e2; color:#b91c1c; padding:0.25rem 0.625rem; border-radius:999px; font-size:0.75rem; font-weight:700; display:inline-flex; align-items:center; gap:0.25rem;">
                                    <span class="material-symbols-outlined" style="font-size:14px;">cancel</span>
                                    Refusé par la modération
                                </span>
                            @else
                                <span style="background:#f3f4f6; color:#4b5563; padding:0.25rem 0.625rem; border-radius:999px; font-size:0.75rem; font-weight:700;">
                                    Brouillon
                                </span>
                            @endif

                            <span style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                                Créé le {{ $post->created_at->format('d/m/Y') }}
                                @if($post->published_at)
                                    • Mis en ligne le {{ $post->published_at->format('d/m/Y') }}
                                @endif
                            </span>
                        </div>

                        <h3 style="font-size:1.0625rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.375rem;">
                            {{ $post->title }}
                        </h3>

                        <p style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin:0; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; line-height:1.4;">
                            {{ Str::limit(strip_tags($post->content), 180) }}
                        </p>

                        @if($post->isRejected() && $post->rejection_reason)
                            <div style="margin-top:0.625rem; background:#fff1f2; border-left:3px solid #e11d48; padding:0.5rem 0.75rem; border-radius:0.25rem; font-size:0.75rem; color:#9f1239;">
                                <strong>Motif du refus :</strong> {{ $post->rejection_reason }}
                            </div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div style="display:flex; flex-direction:column; gap:0.5rem; min-width:130px; align-items:stretch;">
                        @if($post->isDraft())
                            <form action="{{ route('alumni.posts.submit', $post->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        style="width:100%; height:34px; border-radius:0.5rem; border:none; background:#059669; color:#fff; font-size:0.75rem; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; gap:0.25rem;">
                                    <span class="material-symbols-outlined" style="font-size:16px;">send</span>
                                    Soumettre
                                </button>
                            </form>
                            <a href="{{ route('alumni.posts.edit', $post->id) }}"
                               style="height:34px; border-radius:0.5rem; background:var(--color-surface-container-high); color:var(--color-on-surface); font-size:0.75rem; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; gap:0.25rem;">
                                <span class="material-symbols-outlined" style="font-size:16px;">edit</span>
                                Modifier
                            </a>
                            <form action="{{ route('alumni.posts.destroy', $post->id) }}" method="POST"
                                  onsubmit="return confirm('Supprimer définitivement ce brouillon ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        style="width:100%; height:34px; border-radius:0.5rem; border:none; background:#fee2e2; color:#dc2626; font-size:0.75rem; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;">
                                    Supprimer
                                </button>
                            </form>
                        @elseif($post->isPending())
                            <a href="{{ route('alumni.posts.show', $post->id) }}"
                               style="height:34px; border-radius:0.5rem; background:var(--color-surface-container-high); color:var(--color-on-surface); font-size:0.75rem; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; gap:0.25rem;">
                                Aperçu
                            </a>
                            <form action="{{ route('alumni.posts.cancel', $post->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        style="width:100%; height:34px; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:transparent; color:var(--color-on-surface-variant); font-size:0.75rem; font-weight:600; cursor:pointer;">
                                    Annuler soumission
                                </button>
                            </form>
                        @elseif($post->isRejected())
                            <a href="{{ route('alumni.posts.edit', $post->id) }}"
                               style="height:34px; border-radius:0.5rem; background:#be123c; color:#fff; font-size:0.75rem; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; gap:0.25rem;">
                                <span class="material-symbols-outlined" style="font-size:16px;">edit</span>
                                Corriger
                            </a>
                            <form action="{{ route('alumni.posts.destroy', $post->id) }}" method="POST"
                                  onsubmit="return confirm('Supprimer cette publication ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        style="width:100%; height:34px; border-radius:0.5rem; border:none; background:#fee2e2; color:#dc2626; font-size:0.75rem; font-weight:600; cursor:pointer;">
                                    Supprimer
                                </button>
                            </form>
                        @elseif($post->isPublished())
                            <a href="{{ route('alumni.public.posts.show', $post->slug) }}" target="_blank"
                               style="height:34px; border-radius:0.5rem; background:var(--color-surface-container-high); color:var(--color-on-surface); font-size:0.75rem; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; gap:0.25rem;">
                                <span class="material-symbols-outlined" style="font-size:16px;">open_in_new</span>
                                Voir en ligne
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($posts->hasPages())
            <div style="margin-top:1.5rem;">
                {{ $posts->links() }}
            </div>
        @endif
    @endif

</div>

@endsection
