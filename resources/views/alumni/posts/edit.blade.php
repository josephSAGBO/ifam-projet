@extends('layouts.role')

@section('title', 'Modifier la Publication — ' . $post->title)
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

    <div style="margin-bottom:1.75rem; display:flex; align-items:center; gap:1rem;">
        <a href="{{ route('alumni.posts.index') }}"
           style="width:40px; height:40px; border-radius:0.5rem; background:var(--color-surface-container); display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 style="font-family:var(--font-display); font-size:1.625rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
                Modifier la Publication
            </h1>
            <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
                Mettez à jour le texte et les photographies de votre actualité.
            </p>
        </div>
    </div>

    {{-- Si la publication a été rejetée, afficher le motif de refus bien en évidence --}}
    @if($post->isRejected() && $post->rejection_reason)
        <div style="background:#fff1f2; border:1px solid #fecdd3; border-left:5px solid #e11d48; border-radius:0.75rem; padding:1.25rem; margin-bottom:1.75rem;">
            <div style="display:flex; align-items:center; gap:0.5rem; color:#be123c; font-weight:700; font-size:0.9375rem; margin-bottom:0.375rem;">
                <span class="material-symbols-outlined" style="font-size:20px;">cancel</span>
                Remarques de la modération — Corrections nécessaires
            </div>
            <div style="font-size:0.875rem; color:#9f1239; line-height:1.5;">
                {{ $post->rejection_reason }}
            </div>
        </div>
    @endif

    @if($errors->any())
        <div style="background:#fee2e2; color:#b91c1c; padding:1rem 1.25rem; border-radius:0.75rem; font-size:0.9375rem; font-weight:600; margin-bottom:1.5rem;">
            <ul style="margin:0; padding-left:1.25rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('alumni.posts.update', $post->id) }}" method="POST" enctype="multipart/form-data"
          style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:2rem; border:1px solid var(--color-outline-variant);">
        @csrf
        @method('PUT')

        {{-- Titre --}}
        <div style="margin-bottom:1.5rem;">
            <label style="display:block; font-size:0.875rem; font-weight:700; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Titre de la publication <span style="color:#dc2626;">*</span>
            </label>
            <input type="text" name="title" value="{{ old('title', $post->title) }}" required
                   style="width:100%; box-sizing:border-box; height:46px; padding:0 1rem; border-radius:0.625rem;
                          border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                          font-size:1rem; font-weight:600; color:var(--color-on-surface);">
        </div>

        {{-- Contenu textuel --}}
        <div style="margin-bottom:1.75rem;">
            <label style="display:block; font-size:0.875rem; font-weight:700; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Description & Contenu de l'actualité <span style="color:#dc2626;">*</span>
            </label>
            <textarea name="content" rows="7" required
                      style="width:100%; box-sizing:border-box; padding:0.875rem 1rem; border-radius:0.625rem;
                             border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                             font-size:0.9375rem; color:var(--color-on-surface); resize:vertical; line-height:1.6;">{{ old('content', $post->content) }}</textarea>
        </div>

        {{-- Photos déjà associées --}}
        @if($post->media->isNotEmpty())
            <div style="margin-bottom:1.75rem;">
                <label style="display:block; font-size:0.875rem; font-weight:700; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Photos existantes ({{ $post->media->count() }}) — Cochez pour supprimer ou choisissez la couverture :
                </label>

                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(160px, 1fr)); gap:1rem;">
                    @foreach($post->media as $media)
                        <div style="border-radius:0.5rem; overflow:hidden; border:1px solid var(--color-outline-variant); background:#fff; position:relative;">
                            <div style="height:120px; overflow:hidden; background:#eee;">
                                <img src="{{ $media->url }}" style="width:100%; height:100%; object-fit:cover;">
                            </div>
                            <div style="padding:0.5rem; font-size:0.75rem;">
                                <label style="display:flex; align-items:center; gap:0.375rem; cursor:pointer; color:var(--color-on-surface);">
                                    <input type="radio" name="cover_media_id" value="{{ $media->id }}" {{ $media->is_cover ? 'checked' : '' }}>
                                    Couverture
                                </label>
                                <label style="display:flex; align-items:center; gap:0.375rem; color:#dc2626; cursor:pointer; margin-top:0.375rem;">
                                    <input type="checkbox" name="delete_media[]" value="{{ $media->id }}">
                                    Supprimer
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Ajouter de nouvelles photos --}}
        @php
            $remainingSlots = max(0, 8 - $post->media->count());
        @endphp
        @if($remainingSlots > 0)
            <div style="margin-bottom:2rem; background:var(--color-surface-container-low); padding:1.5rem; border-radius:0.875rem; border:1px solid var(--color-outline-variant);">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                    <label style="font-size:0.875rem; font-weight:700; color:var(--color-on-surface);">
                        Ajouter des photos supplémentaires (encore {{ $remainingSlots }} place(s) disponible(s))
                    </label>
                    <span style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                        Max 5 Mo par photo • JPEG, PNG, WebP
                    </span>
                </div>

                <input type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp"
                       style="font-size:0.8125rem; color:var(--color-on-surface-variant);">
            </div>
        @endif

        {{-- Actions --}}
        <div style="display:flex; justify-content:flex-end; gap:1rem; flex-wrap:wrap;">
            <a href="{{ route('alumni.posts.index') }}"
               style="height:44px; display:inline-flex; align-items:center; padding:0 1.25rem; border-radius:0.625rem;
                      background:transparent; color:var(--color-on-surface-variant); font-size:0.875rem; font-weight:600; text-decoration:none;">
                Annuler
            </a>

            <button type="submit" name="action" value="draft"
                    style="height:44px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.25rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); cursor:pointer;
                           background:var(--color-surface-container-high); color:var(--color-on-surface); font-size:0.875rem; font-weight:700;">
                <span class="material-symbols-outlined" style="font-size:20px;">save</span>
                Enregistrer les modifications
            </button>

            <button type="submit" name="action" value="submit"
                    onclick="return confirm('Soumettre cette publication mise à jour pour validation par la modération ?');"
                    style="height:44px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.5rem; border-radius:0.625rem; border:none; cursor:pointer;
                           background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.875rem; font-weight:700;
                           box-shadow:0 3px 10px rgba(228,0,102,0.3);">
                <span class="material-symbols-outlined" style="font-size:20px;">send</span>
                Enregistrer et Soumettre à nouveau
            </button>
        </div>
    </form>

</div>

@endsection
