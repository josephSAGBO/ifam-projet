@extends('layouts.role')

@section('title', $post->title)
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
@endsection

@section('content')

<div style="max-width:960px; margin:0 auto; padding:2rem 1.5rem;">

    <div style="margin-bottom:1.75rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <a href="{{ route('alumni.posts.index') }}"
               style="width:40px; height:40px; border-radius:0.5rem; background:var(--color-surface-container); display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
                    Aperçu de la publication
                </h1>
                <p style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin:0;">
                    Créée le {{ $post->created_at->format('d/m/Y à H:i') }}
                </p>
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:0.75rem;">
            @if(! $post->isPublished())
                <a href="{{ route('alumni.posts.edit', $post->id) }}"
                   style="height:40px; display:inline-flex; align-items:center; gap:0.375rem; padding:0 1rem; border-radius:0.5rem; background:var(--color-surface-container-high); color:var(--color-on-surface); font-size:0.8125rem; font-weight:600; text-decoration:none;">
                    <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                    Modifier
                </a>
            @else
                <a href="{{ route('alumni.public.posts.show', $post->slug) }}" target="_blank"
                   style="height:40px; display:inline-flex; align-items:center; gap:0.375rem; padding:0 1rem; border-radius:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.8125rem; font-weight:700; text-decoration:none;">
                    <span class="material-symbols-outlined" style="font-size:18px;">open_in_new</span>
                    Voir sur le site public
                </a>
            @endif
        </div>
    </div>

    {{-- Carte d'état du workflow --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); border:1px solid var(--color-outline-variant); margin-bottom:1.5rem;">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <span style="font-size:0.875rem; font-weight:700; color:var(--color-on-surface);">Statut de modération :</span>
                @if($post->isPublished())
                    <span style="background:#dcfce7; color:#15803d; padding:0.25rem 0.75rem; border-radius:999px; font-size:0.8125rem; font-weight:700; display:inline-flex; align-items:center; gap:0.25rem;">
                        <span class="material-symbols-outlined" style="font-size:16px;">check_circle</span>
                        En ligne / Validé
                    </span>
                @elseif($post->isPending())
                    <span style="background:#fef3c7; color:#b45309; padding:0.25rem 0.75rem; border-radius:999px; font-size:0.8125rem; font-weight:700; display:inline-flex; align-items:center; gap:0.25rem;">
                        <span class="material-symbols-outlined" style="font-size:16px;">hourglass_empty</span>
                        En cours d'examen par la modération
                    </span>
                @elseif($post->isRejected())
                    <span style="background:#fee2e2; color:#b91c1c; padding:0.25rem 0.75rem; border-radius:999px; font-size:0.8125rem; font-weight:700; display:inline-flex; align-items:center; gap:0.25rem;">
                        <span class="material-symbols-outlined" style="font-size:16px;">cancel</span>
                        Refusé — Corrections demandées
                    </span>
                @else
                    <span style="background:#f3f4f6; color:#4b5563; padding:0.25rem 0.75rem; border-radius:999px; font-size:0.8125rem; font-weight:700;">
                        Brouillon non soumis
                    </span>
                @endif
            </div>

            @if($post->isDraft())
                <form action="{{ route('alumni.posts.submit', $post->id) }}" method="POST">
                    @csrf
                    <button type="submit"
                            style="height:36px; padding:0 1rem; border-radius:0.5rem; border:none; background:#059669; color:#fff; font-size:0.8125rem; font-weight:700; cursor:pointer;">
                        Soumettre pour modération
                    </button>
                </form>
            @endif
        </div>

        @if($post->isRejected() && $post->rejection_reason)
            <div style="margin-top:1rem; background:#fff1f2; border-left:4px solid #e11d48; padding:0.875rem 1rem; border-radius:0.375rem; font-size:0.875rem; color:#9f1239;">
                <strong>Motif communiqué par la modération :</strong><br>
                {{ $post->rejection_reason }}
            </div>
        @endif
    </div>

    {{-- Contenu et photographies --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:2rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); border:1px solid var(--color-outline-variant);">
        <h2 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem;">
            {{ $post->title }}
        </h2>

        @if($post->media->isNotEmpty())
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
                @foreach($post->media as $media)
                    <div style="border-radius:0.75rem; overflow:hidden; border:1px solid var(--color-outline-variant); background:#eee; position:relative;">
                        <img src="{{ $media->url }}" alt="{{ $media->caption ?? '' }}"
                             style="width:100%; height:180px; object-fit:cover; display:block;">
                        @if($media->is_cover)
                            <span style="position:absolute; top:0.5rem; left:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.6875rem; font-weight:700; padding:0.125rem 0.5rem; border-radius:0.25rem;">
                                Couverture
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <div style="font-size:0.9375rem; line-height:1.75; color:var(--color-on-surface); white-space:pre-line;">
            {{ $post->content }}
        </div>
    </div>

</div>

@endsection
