@extends('layouts.public')

@section('title', $alumni->full_name . ' — Profil Alumni IFAM')

@section('content')
<div style="display:flex; flex-direction:column; gap:2rem;">

    {{-- Fil d'Ariane --}}
    <div>
        <a href="{{ route('alumni.public.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; color:var(--color-on-surface-variant); text-decoration:none; font-size:0.875rem; font-weight:700;">
            <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
            Retour à l'annuaire des diplômés
        </a>
    </div>

    {{-- Carte d'identité Alumni --}}
    <div style="background:var(--color-surface-container-lowest, #ffffff); border-radius:1.25rem; border:1px solid rgba(11,28,48,0.06); box-shadow:0 4px 20px rgba(11,28,48,0.03); overflow:hidden;">
        
        {{-- Bannière d'en-tête --}}
        <div style="height:120px; background:linear-gradient(135deg, var(--color-primary, #b60050) 0%, var(--color-primary-container, #e40066) 100%);"></div>

        <div style="padding:0 2rem 2rem; position:relative; margin-top:-60px;">
            <div style="display:flex; flex-wrap:wrap; gap:1.5rem; justify-content:space-between; align-items:flex-end;">
                
                {{-- Photo + Nom --}}
                <div style="display:flex; flex-wrap:wrap; gap:1.25rem; align-items:flex-end;">
                    @if($alumni->photo_url)
                        <img src="{{ $alumni->photo_url }}" alt="{{ $alumni->full_name }}" 
                             style="width:120px; height:120px; border-radius:50%; object-fit:cover; border:4px solid #fff; box-shadow:0 4px 12px rgba(0,0,0,0.08); background:#fff;">
                    @else
                        <div style="width:120px; height:120px; border-radius:50%; background:linear-gradient(135deg, var(--color-primary, #b60050), var(--color-primary-container, #e40066)); color:#fff; display:flex; align-items:center; justify-content:center; font-size:2.25rem; font-weight:800; border:4px solid #fff; box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                            {{ $alumni->initials }}
                        </div>
                    @endif

                    <div style="margin-bottom:0.5rem;">
                        <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                            <h1 style="font-family:var(--font-display); font-size:1.75rem; font-weight:800; color:var(--color-on-surface, #0b1c30); margin:0;">
                                {{ $alumni->full_name }}
                            </h1>
                            <span style="display:inline-flex; align-items:center; gap:0.25rem; padding:0.2rem 0.6rem; border-radius:9999px; background:#ecfdf5; color:#065f46; font-size:0.6875rem; font-weight:800; text-transform:uppercase;">
                                <span class="material-symbols-outlined" style="font-size:14px; color:#059669;">verified</span>
                                Diplômé Certifié
                            </span>
                        </div>
                        <div style="font-size:1rem; font-weight:700; color:var(--color-primary, #b60050); margin-top:0.25rem;">
                            {{ $alumni->current_position ?? 'Professionnel(le) de la Mode' }}
                            @if($alumni->company)
                                <span style="font-weight:500; color:var(--color-on-surface-variant);">chez {{ $alumni->company }}</span>
                            @endif
                        </div>
                        @if($alumni->city)
                            <div style="font-size:0.8125rem; color:var(--color-on-surface-variant); display:flex; align-items:center; gap:0.25rem; margin-top:0.25rem;">
                                <span class="material-symbols-outlined" style="font-size:16px;">location_on</span>
                                {{ $alumni->city }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Liens Professionnels --}}
                <div style="display:flex; gap:0.75rem; flex-wrap:wrap; margin-bottom:0.5rem;">
                    @if($alumni->linkedin_url)
                        <a href="{{ $alumni->linkedin_url }}" target="_blank" rel="noopener noreferrer" 
                           style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 1rem; border-radius:0.625rem; background:var(--color-surface-container, #e5eeff); color:var(--color-on-surface); text-decoration:none; font-size:0.8125rem; font-weight:700;">
                            <span class="material-symbols-outlined" style="font-size:18px; color:#0077b5;">share</span>
                            LinkedIn
                        </a>
                    @endif
                    @if($alumni->portfolio_url)
                        <a href="{{ $alumni->portfolio_url }}" target="_blank" rel="noopener noreferrer" 
                           style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 1rem; border-radius:0.625rem; background:var(--color-surface-container, #e5eeff); color:var(--color-on-surface); text-decoration:none; font-size:0.8125rem; font-weight:700;">
                            <span class="material-symbols-outlined" style="font-size:18px; color:var(--color-secondary);">language</span>
                            Site / Portfolio
                        </a>
                    @endif
                    @if($alumni->email_professionnel)
                        <a href="mailto:{{ $alumni->email_professionnel }}" 
                           style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 1rem; border-radius:0.625rem; background:var(--color-primary-container, #e40066); color:#fff; text-decoration:none; font-size:0.8125rem; font-weight:700;">
                            <span class="material-symbols-outlined" style="font-size:18px;">mail</span>
                            Contacter
                        </a>
                    @endif
                </div>

            </div>

            {{-- Parcours & Biographie --}}
            <div style="margin-top:2rem; display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:1.5rem; border-top:1px solid rgba(11,28,48,0.06); padding-top:1.5rem;">
                
                {{-- Cursus IFAM --}}
                <div style="background:var(--color-surface-container-low, #eff4ff); border-radius:1rem; padding:1.25rem;">
                    <h3 style="font-size:0.8125rem; font-weight:800; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin:0 0 1rem; display:flex; align-items:center; gap:0.375rem;">
                        <span class="material-symbols-outlined" style="font-size:18px; color:var(--color-primary);">school</span>
                        Cursus Académique IFAM
                    </h3>
                    <div style="display:flex; flex-direction:column; gap:0.75rem;">
                        <div>
                            <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">Formation principale</div>
                            <div style="font-size:0.9375rem; font-weight:700; color:var(--color-on-surface);">{{ $alumni->formation?->titre ?? 'Formation IFAM' }}</div>
                        </div>
                        @if($alumni->promotion_year)
                            <div>
                                <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">Promotion de sortie</div>
                                <div style="font-size:0.9375rem; font-weight:700; color:var(--color-secondary, #835500);">Promotion {{ $alumni->promotion_year }}</div>
                            </div>
                        @endif
                        @if($alumni->graduation_date)
                            <div>
                                <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">Diplômé(e) le</div>
                                <div style="font-size:0.875rem; font-weight:600; color:var(--color-on-surface);">{{ $alumni->graduation_date->translatedFormat('d F Y') }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Présentation / Biographie --}}
                <div style="background:var(--color-surface-container-lowest, #ffffff); border-radius:1rem; padding:1.25rem; border:1px solid rgba(11,28,48,0.06);">
                    <h3 style="font-size:0.8125rem; font-weight:800; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin:0 0 1rem; display:flex; align-items:center; gap:0.375rem;">
                        <span class="material-symbols-outlined" style="font-size:18px; color:var(--color-primary);">badge</span>
                        Profil Professionnel
                    </h3>
                    @if($alumni->bio)
                        <p style="font-size:0.875rem; line-height:1.6; color:var(--color-on-surface); margin:0; white-space:pre-line;">
                            {{ $alumni->bio }}
                        </p>
                    @else
                        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); font-style:italic; margin:0;">
                            Ce diplômé n'a pas encore rédigé sa biographie professionnelle.
                        </p>
                    @endif
                </div>

            </div>

        </div>

    </div>

    {{-- Réalisations et Publications approuvées --}}
    <div>
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
            <div>
                <h2 style="font-family:var(--font-display); font-size:1.5rem; font-weight:800; color:var(--color-on-surface, #0b1c30); margin:0;">
                    Créations & Publications de {{ $alumni->prenom }}
                </h2>
                <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0.25rem 0 0;">
                    Découvrez les modèles, distinctions, articles et réalisations partagés par ce diplômé.
                </p>
            </div>
        </div>

        @if($posts->count() > 0)
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:1.5rem;">
                @foreach($posts as $post)
                    <article style="background:var(--color-surface-container-lowest, #ffffff); border-radius:1rem; border:1px solid rgba(11,28,48,0.06); box-shadow:0 2px 10px rgba(11,28,48,0.03); overflow:hidden; display:flex; flex-direction:column;">
                        
                        @if($post->cover_media)
                            <div style="height:200px; overflow:hidden; position:relative; background:#000;">
                                <img src="{{ $post->cover_media->url }}" alt="{{ $post->title }}" 
                                     style="width:100%; height:100%; object-fit:cover;">
                                @if($post->media->count() > 1)
                                    <div style="position:absolute; bottom:0.5rem; right:0.5rem; background:rgba(0,0,0,0.6); color:#fff; border-radius:0.375rem; padding:0.2rem 0.5rem; font-size:0.75rem; font-weight:700; display:flex; align-items:center; gap:0.25rem;">
                                        <span class="material-symbols-outlined" style="font-size:14px;">photo_library</span>
                                        {{ $post->media->count() }} photos
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div style="padding:1.25rem; flex:1; display:flex; flex-direction:column; gap:0.5rem;">
                            <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                                Publié le {{ $post->published_at?->translatedFormat('d F Y') ?? $post->created_at->translatedFormat('d F Y') }}
                            </div>

                            <h3 style="font-family:var(--font-display); font-size:1.125rem; font-weight:800; color:var(--color-on-surface); margin:0; line-height:1.3;">
                                <a href="{{ route('alumni.public.posts.show', $post->slug) }}" style="text-decoration:none; color:inherit;">
                                    {{ $post->title }}
                                </a>
                            </h3>

                            <p style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin:0; line-height:1.5; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;">
                                {{ Str::limit(strip_tags($post->content), 140) }}
                            </p>

                            <div style="margin-top:auto; padding-top:0.75rem;">
                                <a href="{{ route('alumni.public.posts.show', $post->slug) }}" 
                                   style="display:inline-flex; align-items:center; gap:0.25rem; font-size:0.8125rem; font-weight:800; color:var(--color-primary, #b60050); text-decoration:none;">
                                    Lire la publication
                                    <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
                                </a>
                            </div>
                        </div>

                    </article>
                @endforeach
            </div>

            <div style="margin-top:1.5rem;">
                {{ $posts->links() }}
            </div>
        @else
            <div style="background:var(--color-surface-container-lowest, #ffffff); border-radius:1rem; padding:2rem; text-align:center; border:1px solid rgba(11,28,48,0.06);">
                <span class="material-symbols-outlined" style="font-size:36px; color:var(--color-on-surface-variant);">collections_bookmark</span>
                <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0.5rem 0 0;">
                    Ce diplômé n'a pas encore de publications publiques approuvées.
                </p>
            </div>
        @endif
    </div>

</div>
@endsection
