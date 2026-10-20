@extends('layouts.public')

@section('title', 'Créations & Actualités des Alumni — IFAM Connect')

@section('content')
<div style="display:flex; flex-direction:column; gap:2rem;">

    {{-- Hero Section --}}
    <div style="background:linear-gradient(135deg, var(--color-surface-container-lowest, #fff) 0%, var(--color-surface-container, #e5eeff) 100%); border-radius:1.25rem; padding:2.5rem 2rem; border:1px solid var(--color-outline-variant, #e4bdc3); box-shadow:0 4px 20px rgba(182,0,80,0.04); display:flex; flex-direction:column; gap:1rem;">
        <div style="display:inline-flex; align-items:center; gap:0.5rem; align-self:flex-start; padding:0.35rem 0.75rem; border-radius:9999px; background:var(--color-primary-fixed, #ffd9df); color:var(--color-on-primary-fixed-variant, #90003e); font-size:0.75rem; font-weight:800; text-transform:uppercase; letter-spacing:0.06em;">
            <span class="material-symbols-outlined" style="font-size:16px;">auto_awesome</span>
            Vitrine des Créations
        </div>
        <h1 style="font-family:var(--font-display); font-size:2.25rem; font-weight:800; color:var(--color-on-surface, #0b1c30); margin:0; line-height:1.2;">
            Créations, Témoignages & <span style="color:var(--color-primary, #b60050);">Parcours</span>
        </h1>
        <p style="font-size:1rem; color:var(--color-on-surface-variant, #5b3f45); margin:0; max-width:750px; line-height:1.5;">
            Plongez au cœur du savoir-faire IFAM : défilés, collections textiles, ateliers de stylisme et retours d'expériences de nos anciens diplômés.
        </p>
    </div>

    {{-- Barre de recherche --}}
    <div style="background:var(--color-surface-container-lowest, #ffffff); border-radius:1rem; padding:1.25rem 1.5rem; border:1px solid rgba(11,28,48,0.06); box-shadow:0 2px 8px rgba(11,28,48,0.03);">
        <form method="GET" action="{{ route('alumni.public.posts.index') }}" style="display:flex; gap:1rem; align-items:center;">
            <div style="position:relative; flex:1;">
                <span class="material-symbols-outlined" style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); font-size:18px; color:var(--color-on-surface-variant);">search</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher une création, un article, une technique textile..." 
                       style="width:100%; box-sizing:border-box; padding:0.65rem 0.75rem 0.65rem 2.25rem; border-radius:0.625rem; border:1px solid rgba(11,28,48,0.15); font-size:0.875rem;">
            </div>
            <button type="submit" style="padding:0.65rem 1.25rem; border-radius:0.625rem; background:var(--color-primary-container, #e40066); color:#fff; border:none; font-weight:700; font-size:0.875rem; cursor:pointer; display:flex; align-items:center; gap:0.375rem;">
                <span class="material-symbols-outlined" style="font-size:18px;">search</span> Rechercher
            </button>
            @if(request('search'))
                <a href="{{ route('alumni.public.posts.index') }}" style="padding:0.65rem 0.875rem; border-radius:0.625rem; background:var(--color-surface-container, #e5eeff); color:var(--color-on-surface); text-decoration:none; font-weight:700; font-size:0.875rem; display:flex; align-items:center;">
                    <span class="material-symbols-outlined" style="font-size:18px;">close</span>
                </a>
            @endif
        </form>
    </div>

    {{-- Grille des publications --}}
    @if($posts->count() > 0)
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(340px, 1fr)); gap:1.75rem;">
            @foreach($posts as $post)
                <article style="background:var(--color-surface-container-lowest, #ffffff); border-radius:1rem; border:1px solid rgba(11,28,48,0.06); box-shadow:0 4px 16px rgba(11,28,48,0.03); overflow:hidden; display:flex; flex-direction:column; transition:transform 0.2s, box-shadow 0.2s;">
                    
                    {{-- Photo de couverture --}}
                    @if($post->cover_media)
                        <div style="height:220px; overflow:hidden; position:relative; background:#000;">
                            <img src="{{ $post->cover_media->url }}" alt="{{ $post->title }}" 
                                 style="width:100%; height:100%; object-fit:cover;">
                            @if($post->media->count() > 1)
                                <div style="position:absolute; bottom:0.75rem; right:0.75rem; background:rgba(0,0,0,0.65); backdrop-filter:blur(4px); color:#fff; border-radius:0.375rem; padding:0.25rem 0.6rem; font-size:0.75rem; font-weight:700; display:flex; align-items:center; gap:0.25rem;">
                                    <span class="material-symbols-outlined" style="font-size:14px;">photo_library</span>
                                    {{ $post->media->count() }} photos
                                </div>
                            @endif
                        </div>
                    @else
                        <div style="height:120px; background:linear-gradient(135deg, var(--color-surface-container, #e5eeff) 0%, var(--color-surface-container-high, #dce9ff) 100%); display:flex; align-items:center; justify-content:center;">
                            <span class="material-symbols-outlined" style="font-size:48px; color:var(--color-primary-fixed-dim, #ffb1c1);">brush</span>
                        </div>
                    @endif

                    <div style="padding:1.5rem; flex:1; display:flex; flex-direction:column; gap:0.75rem;">
                        
                        {{-- Auteur --}}
                        <div style="display:flex; align-items:center; gap:0.625rem;">
                            @if($post->alumni->photo_url)
                                <img src="{{ $post->alumni->photo_url }}" alt="{{ $post->alumni->full_name }}" 
                                     style="width:32px; height:32px; border-radius:50%; object-fit:cover;">
                            @else
                                <div style="width:32px; height:32px; border-radius:50%; background:var(--color-primary-container); color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:800;">
                                    {{ $post->alumni->initials }}
                                </div>
                            @endif
                            <div style="line-height:1.2;">
                                <a href="{{ route('alumni.public.show', $post->alumni) }}" style="text-decoration:none; color:var(--color-on-surface); font-size:0.8125rem; font-weight:700;">
                                    {{ $post->alumni->full_name }}
                                </a>
                                <div style="font-size:0.6875rem; color:var(--color-on-surface-variant);">
                                    {{ $post->published_at?->translatedFormat('d M Y') ?? $post->created_at->translatedFormat('d M Y') }}
                                </div>
                            </div>
                        </div>

                        {{-- Titre --}}
                        <h2 style="font-family:var(--font-display); font-size:1.25rem; font-weight:800; color:var(--color-on-surface); margin:0; line-height:1.3;">
                            <a href="{{ route('alumni.public.posts.show', $post->slug) }}" style="text-decoration:none; color:inherit;">
                                {{ $post->title }}
                            </a>
                        </h2>

                        {{-- Extrait --}}
                        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0; line-height:1.5; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;">
                            {{ Str::limit(strip_tags($post->content), 160) }}
                        </p>

                        {{-- Action --}}
                        <div style="margin-top:auto; padding-top:1rem; border-top:1px solid rgba(11,28,48,0.04); display:flex; align-items:center; justify-content:space-between;">
                            <span style="font-size:0.75rem; color:var(--color-secondary, #835500); font-weight:700;">
                                {{ $post->alumni->formation?->titre ?? 'IFAM' }}
                            </span>
                            <a href="{{ route('alumni.public.posts.show', $post->slug) }}" 
                               style="display:inline-flex; align-items:center; gap:0.25rem; font-size:0.8125rem; font-weight:800; color:var(--color-primary, #b60050); text-decoration:none;">
                                Découvrir
                                <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
                            </a>
                        </div>

                    </div>

                </article>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div style="margin-top:1.5rem;">
            {{ $posts->links() }}
        </div>
    @else
        <div style="background:var(--color-surface-container-lowest, #ffffff); border-radius:1rem; padding:3rem 2rem; text-align:center; border:1px solid rgba(11,28,48,0.06);">
            <div style="width:60px; height:60px; border-radius:50%; background:var(--color-surface-container, #e5eeff); color:var(--color-on-surface-variant); display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;">
                <span class="material-symbols-outlined" style="font-size:30px;">auto_awesome</span>
            </div>
            <h3 style="font-family:var(--font-display); font-size:1.25rem; font-weight:800; color:var(--color-on-surface, #0b1c30); margin:0 0 0.5rem;">
                Aucune création publiée pour l'instant
            </h3>
            <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
                Les réalisations et témoignages de nos diplômés apparaîtront ici dès leur approbation.
            </p>
        </div>
    @endif

</div>
@endsection
