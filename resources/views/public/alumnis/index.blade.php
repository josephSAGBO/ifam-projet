@extends('layouts.public')

@section('title', 'Annuaire des Anciens Étudiants — IFAM Connect')

@section('content')
<div style="display:flex; flex-direction:column; gap:2rem;">

    {{-- Hero Section --}}
    <div style="background:linear-gradient(135deg, var(--color-surface-container-lowest, #fff) 0%, var(--color-surface-container, #e5eeff) 100%); border-radius:1.25rem; padding:2.5rem 2rem; border:1px solid var(--color-outline-variant, #e4bdc3); box-shadow:0 4px 20px rgba(182,0,80,0.04); display:flex; flex-direction:column; gap:1rem;">
        <div style="display:inline-flex; align-items:center; gap:0.5rem; align-self:flex-start; padding:0.35rem 0.75rem; border-radius:9999px; background:var(--color-primary-fixed, #ffd9df); color:var(--color-on-primary-fixed-variant, #90003e); font-size:0.75rem; font-weight:800; text-transform:uppercase; letter-spacing:0.06em;">
            <span class="material-symbols-outlined" style="font-size:16px;">school</span>
            Réseau Professionnel
        </div>
        <h1 style="font-family:var(--font-display); font-size:2.25rem; font-weight:800; color:var(--color-on-surface, #0b1c30); margin:0; line-height:1.2;">
            L'Excellence de nos <span style="color:var(--color-primary, #b60050);">Diplômés</span>
        </h1>
        <p style="font-size:1rem; color:var(--color-on-surface-variant, #5b3f45); margin:0; max-width:750px; line-height:1.5;">
            Découvrez les talents formés à l'IFAM : créateurs de mode, stylistes, modélistes et entrepreneurs textiles qui façonnent la mode béninoise et internationale.
        </p>
    </div>

    {{-- Filtres & Recherche --}}
    <div style="background:var(--color-surface-container-lowest, #ffffff); border-radius:1rem; padding:1.25rem 1.5rem; border:1px solid rgba(11,28,48,0.06); box-shadow:0 2px 8px rgba(11,28,48,0.03);">
        <form method="GET" action="{{ route('alumni.public.index') }}" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; align-items:end;">
            <div>
                <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant, #5b3f45); margin-bottom:0.375rem; text-transform:uppercase;">
                    Recherche par nom / mot-clé
                </label>
                <div style="position:relative;">
                    <span class="material-symbols-outlined" style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); font-size:18px; color:var(--color-on-surface-variant);">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Ex: Dossou, Styliste..." 
                           style="width:100%; box-sizing:border-box; padding:0.6rem 0.75rem 0.6rem 2.25rem; border-radius:0.625rem; border:1px solid rgba(11,28,48,0.15); font-size:0.875rem;">
                </div>
            </div>

            <div>
                <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant, #5b3f45); margin-bottom:0.375rem; text-transform:uppercase;">
                    Formation
                </label>
                <select name="formation_id" style="width:100%; box-sizing:border-box; padding:0.6rem 0.75rem; border-radius:0.625rem; border:1px solid rgba(11,28,48,0.15); font-size:0.875rem; background:#fff;">
                    <option value="">Toutes les formations</option>
                    @foreach($formations as $formation)
                        <option value="{{ $formation->id }}" {{ request('formation_id') == $formation->id ? 'selected' : '' }}>
                            {{ $formation->titre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant, #5b3f45); margin-bottom:0.375rem; text-transform:uppercase;">
                    Promotion
                </label>
                <select name="promotion" style="width:100%; box-sizing:border-box; padding:0.6rem 0.75rem; border-radius:0.625rem; border:1px solid rgba(11,28,48,0.15); font-size:0.875rem; background:#fff;">
                    <option value="">Toutes les promotions</option>
                    @foreach($promotions as $promo)
                        <option value="{{ $promo }}" {{ request('promotion') == $promo ? 'selected' : '' }}>
                            Promotion {{ $promo }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant, #5b3f45); margin-bottom:0.375rem; text-transform:uppercase;">
                    Situation
                </label>
                <select name="situation" style="width:100%; box-sizing:border-box; padding:0.6rem 0.75rem; border-radius:0.625rem; border:1px solid rgba(11,28,48,0.15); font-size:0.875rem; background:#fff;">
                    <option value="">Toutes les situations</option>
                    <option value="employed" {{ request('situation') === 'employed' ? 'selected' : '' }}>Salarié(e)</option>
                    <option value="freelance" {{ request('situation') === 'freelance' ? 'selected' : '' }}>Entrepreneur / Créateur</option>
                    <option value="seeking" {{ request('situation') === 'seeking' ? 'selected' : '' }}>En recherche</option>
                    <option value="studying" {{ request('situation') === 'studying' ? 'selected' : '' }}>Poursuite d'études</option>
                </select>
            </div>

            <div style="display:flex; gap:0.5rem;">
                <button type="submit" style="flex:1; padding:0.6rem 1rem; border-radius:0.625rem; background:var(--color-primary-container, #e40066); color:#fff; border:none; font-weight:700; font-size:0.875rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:0.375rem;">
                    <span class="material-symbols-outlined" style="font-size:18px;">filter_list</span> Filtrer
                </button>
                @if(request()->hasAny(['search', 'formation_id', 'promotion', 'situation']))
                    <a href="{{ route('alumni.public.index') }}" style="padding:0.6rem 0.75rem; border-radius:0.625rem; background:var(--color-surface-container, #e5eeff); color:var(--color-on-surface); text-decoration:none; font-weight:700; font-size:0.875rem; display:flex; align-items:center; justify-content:center;" title="Réinitialiser">
                        <span class="material-symbols-outlined" style="font-size:18px;">restart_alt</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Grille des Alumni --}}
    @if($alumnis->count() > 0)
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(290px, 1fr)); gap:1.5rem;">
            @foreach($alumnis as $alumni)
                <div style="background:var(--color-surface-container-lowest, #ffffff); border-radius:1rem; border:1px solid rgba(11,28,48,0.06); box-shadow:0 2px 10px rgba(11,28,48,0.03); overflow:hidden; display:flex; flex-direction:column; transition:transform 0.2s, box-shadow 0.2s;">
                    
                    {{-- Header de la carte --}}
                    <div style="padding:1.5rem 1.5rem 1rem; display:flex; gap:1rem; align-items:center;">
                        @if($alumni->photo_url)
                            <img src="{{ $alumni->photo_url }}" alt="{{ $alumni->full_name }}" 
                                 style="width:68px; height:68px; border-radius:50%; object-fit:cover; border:3px solid var(--color-surface-container, #e5eeff); flex-shrink:0;">
                        @else
                            <div style="width:68px; height:68px; border-radius:50%; background:linear-gradient(135deg, var(--color-primary, #b60050), var(--color-primary-container, #e40066)); color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.375rem; font-weight:800; flex-shrink:0;">
                                {{ $alumni->initials }}
                            </div>
                        @endif

                        <div style="flex:1; min-width:0;">
                            <h3 style="font-family:var(--font-display); font-size:1.125rem; font-weight:800; color:var(--color-on-surface, #0b1c30); margin:0 0 0.25rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                <a href="{{ route('alumni.public.show', $alumni) }}" style="text-decoration:none; color:inherit;">
                                    {{ $alumni->full_name }}
                                </a>
                            </h3>
                            <div style="font-size:0.8125rem; font-weight:700; color:var(--color-primary, #b60050); margin-bottom:0.25rem;">
                                {{ $alumni->current_position ?? 'Diplômé(e) IFAM' }}
                            </div>
                            @if($alumni->company)
                                <div style="font-size:0.75rem; color:var(--color-on-surface-variant); display:flex; align-items:center; gap:0.25rem;">
                                    <span class="material-symbols-outlined" style="font-size:14px;">storefront</span>
                                    {{ $alumni->company }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Corps de la carte --}}
                    <div style="padding:0 1.5rem 1rem; flex:1; display:flex; flex-direction:column; gap:0.75rem;">
                        <div style="display:flex; flex-wrap:wrap; gap:0.375rem;">
                            <span style="display:inline-flex; align-items:center; gap:0.25rem; padding:0.2rem 0.5rem; border-radius:0.375rem; background:var(--color-surface-container, #e5eeff); color:var(--color-on-surface); font-size:0.6875rem; font-weight:700;">
                                <span class="material-symbols-outlined" style="font-size:14px; color:var(--color-primary);">school</span>
                                {{ $alumni->formation?->titre ?? 'IFAM' }}
                            </span>
                            @if($alumni->promotion_year)
                                <span style="display:inline-flex; align-items:center; gap:0.25rem; padding:0.2rem 0.5rem; border-radius:0.375rem; background:var(--color-secondary-fixed, #ffddb4); color:var(--color-on-secondary-fixed, #291800); font-size:0.6875rem; font-weight:700;">
                                    Promo {{ $alumni->promotion_year }}
                                </span>
                            @endif
                        </div>

                        @if($alumni->bio)
                            <p style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin:0; line-height:1.4; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;">
                                {{ $alumni->bio }}
                            </p>
                        @endif
                    </div>

                    {{-- Footer de la carte --}}
                    <div style="padding:0.75rem 1.5rem; background:var(--color-surface-container-low, #eff4ff); border-top:1px solid rgba(11,28,48,0.04); display:flex; align-items:center; justify-content:space-between;">
                        <div style="display:flex; gap:0.5rem;">
                            @if($alumni->linkedin_url)
                                <a href="{{ $alumni->linkedin_url }}" target="_blank" rel="noopener noreferrer" style="color:var(--color-primary); display:flex;" title="Profil LinkedIn">
                                    <span class="material-symbols-outlined" style="font-size:18px;">share</span>
                                </a>
                            @endif
                            @if($alumni->portfolio_url)
                                <a href="{{ $alumni->portfolio_url }}" target="_blank" rel="noopener noreferrer" style="color:var(--color-secondary); display:flex;" title="Portfolio / Site">
                                    <span class="material-symbols-outlined" style="font-size:18px;">language</span>
                                </a>
                            @endif
                            @if($alumni->email_professionnel)
                                <a href="mailto:{{ $alumni->email_professionnel }}" style="color:var(--color-on-surface-variant); display:flex;" title="Email professionnel">
                                    <span class="material-symbols-outlined" style="font-size:18px;">mail</span>
                                </a>
                            @endif
                        </div>

                        <a href="{{ route('alumni.public.show', $alumni) }}" 
                           style="display:inline-flex; align-items:center; gap:0.25rem; font-size:0.8125rem; font-weight:800; color:var(--color-primary, #b60050); text-decoration:none;">
                            Voir le profil
                            <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
                        </a>
                    </div>

                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div style="margin-top:1rem;">
            {{ $alumnis->links() }}
        </div>
    @else
        <div style="background:var(--color-surface-container-lowest, #ffffff); border-radius:1rem; padding:3rem 2rem; text-align:center; border:1px solid rgba(11,28,48,0.06);">
            <div style="width:60px; height:60px; border-radius:50%; background:var(--color-surface-container, #e5eeff); color:var(--color-on-surface-variant); display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;">
                <span class="material-symbols-outlined" style="font-size:30px;">person_search</span>
            </div>
            <h3 style="font-family:var(--font-display); font-size:1.25rem; font-weight:800; color:var(--color-on-surface, #0b1c30); margin:0 0 0.5rem;">
                Aucun diplômé trouvé
            </h3>
            <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0 0 1.5rem;">
                Aucun profil d'alumni ne correspond actuellement à vos critères de recherche.
            </p>
            @if(request()->hasAny(['search', 'formation_id', 'promotion', 'situation']))
                <a href="{{ route('alumni.public.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 1rem; border-radius:0.625rem; background:var(--color-primary-container, #e40066); color:#fff; text-decoration:none; font-size:0.8125rem; font-weight:700;">
                    <span class="material-symbols-outlined" style="font-size:18px;">restart_alt</span>
                    Effacer les filtres
                </a>
            @endif
        </div>
    @endif

</div>
@endsection
