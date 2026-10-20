@extends('layouts.public')

@section('title', $post->title . ' — Réalisations Alumni IFAM')

@section('content')
<div style="display:flex; flex-direction:column; gap:2rem; max-width:960px; margin:0 auto;">

    {{-- Fil d'Ariane --}}
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
        <a href="{{ route('alumni.public.posts.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; color:var(--color-on-surface-variant); text-decoration:none; font-size:0.875rem; font-weight:700;">
            <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
            Toutes les créations & actualités
        </a>

        <a href="{{ route('alumni.public.show', $post->alumni) }}" style="display:inline-flex; align-items:center; gap:0.375rem; color:var(--color-primary, #b60050); text-decoration:none; font-size:0.875rem; font-weight:700;">
            Voir le profil de l'auteur
            <span class="material-symbols-outlined" style="font-size:18px;">person</span>
        </a>
    </div>

    {{-- Article complet --}}
    <article style="background:var(--color-surface-container-lowest, #ffffff); border-radius:1.25rem; border:1px solid rgba(11,28,48,0.06); box-shadow:0 4px 20px rgba(11,28,48,0.03); overflow:hidden;">
        
        {{-- En-tête de l'article --}}
        <div style="padding:2.5rem 2rem 1.5rem;">
            
            {{-- Auteur & Date --}}
            <div style="display:flex; align-items:center; gap:0.875rem; margin-bottom:1.25rem;">
                @if($post->alumni->photo_url)
                    <img src="{{ $post->alumni->photo_url }}" alt="{{ $post->alumni->full_name }}" 
                         style="width:48px; height:48px; border-radius:50%; object-fit:cover; border:2px solid var(--color-surface-container, #e5eeff);">
                @else
                    <div style="width:48px; height:48px; border-radius:50%; background:linear-gradient(135deg, var(--color-primary, #b60050), var(--color-primary-container, #e40066)); color:#fff; display:flex; align-items:center; justify-content:center; font-size:1rem; font-weight:800;">
                        {{ $post->alumni->initials }}
                    </div>
                @endif
                <div>
                    <a href="{{ route('alumni.public.show', $post->alumni) }}" style="text-decoration:none; color:var(--color-on-surface); font-size:1rem; font-weight:800;">
                        {{ $post->alumni->full_name }}
                    </a>
                    <div style="font-size:0.8125rem; color:var(--color-on-surface-variant); display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                        <span>{{ $post->alumni->current_position ?? 'Alumni IFAM' }}</span>
                        <span>•</span>
                        <span>Publié le {{ $post->published_at?->translatedFormat('d F Y') ?? $post->created_at->translatedFormat('d F Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Titre principal --}}
            <h1 style="font-family:var(--font-display); font-size:2rem; font-weight:800; color:var(--color-on-surface, #0b1c30); margin:0 0 1rem; line-height:1.25;">
                {{ $post->title }}
            </h1>

            <div style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.25rem 0.625rem; border-radius:0.375rem; background:var(--color-surface-container, #e5eeff); color:var(--color-on-surface); font-size:0.75rem; font-weight:700;">
                <span class="material-symbols-outlined" style="font-size:16px; color:var(--color-primary);">school</span>
                {{ $post->alumni->formation?->titre ?? 'IFAM Bénin' }}
                @if($post->alumni->promotion_year)
                    — Promo {{ $post->alumni->promotion_year }}
                @endif
            </div>

        </div>

        {{-- Image de couverture si présente --}}
        @if($post->cover_media)
            <div style="max-height:500px; overflow:hidden; background:#000; display:flex; align-items:center; justify-content:center;">
                <img src="{{ $post->cover_media->url }}" alt="{{ $post->title }}" style="width:100%; max-height:500px; object-fit:cover;">
            </div>
            @if($post->cover_media->caption)
                <div style="padding:0.5rem 2rem; font-size:0.75rem; color:var(--color-on-surface-variant); font-style:italic; background:var(--color-surface-container-low, #eff4ff); border-bottom:1px solid rgba(11,28,48,0.04);">
                    {{ $post->cover_media->caption }}
                </div>
            @endif
        @endif

        {{-- Corps de texte --}}
        <div style="padding:2rem; font-size:1rem; line-height:1.75; color:var(--color-on-surface); white-space:pre-line; border-bottom:1px solid rgba(11,28,48,0.06);">
            {{ $post->content }}
        </div>

        {{-- Galerie d'images --}}
        @if($post->media->count() > 0)
            <div style="padding:2rem;">
                <h3 style="font-family:var(--font-display); font-size:1.125rem; font-weight:800; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
                    <span class="material-symbols-outlined" style="color:var(--color-primary);">photo_library</span>
                    Galerie photos ({{ $post->media->count() }})
                </h3>

                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:1rem;">
                    @foreach($post->media as $media)
                        <div style="background:var(--color-surface-container, #e5eeff); border-radius:0.75rem; overflow:hidden; border:1px solid rgba(11,28,48,0.06); display:flex; flex-direction:column;">
                            <a href="{{ $media->url }}" target="_blank" rel="noopener noreferrer" style="height:180px; overflow:hidden; display:block; background:#000;">
                                <img src="{{ $media->url }}" alt="{{ $media->caption ?? 'Création' }}" 
                                     style="width:100%; height:100%; object-fit:cover; transition:transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                            </a>
                            @if($media->caption)
                                <div style="padding:0.5rem 0.75rem; font-size:0.75rem; color:var(--color-on-surface-variant); line-height:1.3;">
                                    {{ $media->caption }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Carte Signature Auteur en bas --}}
        <div style="padding:2rem; background:var(--color-surface-container-low, #eff4ff); border-top:1px solid rgba(11,28,48,0.06); display:flex; gap:1.25rem; align-items:center; flex-wrap:wrap;">
            @if($post->alumni->photo_url)
                <img src="{{ $post->alumni->photo_url }}" alt="{{ $post->alumni->full_name }}" 
                     style="width:64px; height:64px; border-radius:50%; object-fit:cover; border:2px solid #fff;">
            @else
                <div style="width:64px; height:64px; border-radius:50%; background:var(--color-primary-container); color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.25rem; font-weight:800;">
                    {{ $post->alumni->initials }}
                </div>
            @endif

            <div style="flex:1; min-width:200px;">
                <div style="font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase;">À propos de l'auteur</div>
                <div style="font-size:1.125rem; font-weight:800; color:var(--color-on-surface);">{{ $post->alumni->full_name }}</div>
                <div style="font-size:0.8125rem; color:var(--color-primary, #b60050); font-weight:600;">
                    {{ $post->alumni->current_position ?? 'Diplômé(e) IFAM' }}
                    @if($post->alumni->company)
                        — {{ $post->alumni->company }}
                    @endif
                </div>
            </div>

            <div>
                <a href="{{ route('alumni.public.show', $post->alumni) }}" 
                   style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 1rem; border-radius:0.625rem; background:var(--color-surface-container-lowest, #fff); color:var(--color-on-surface); text-decoration:none; font-size:0.8125rem; font-weight:700; border:1px solid rgba(11,28,48,0.1);">
                    Voir le profil complet
                    <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
                </a>
            </div>
        </div>

    </article>

</div>
@endsection
