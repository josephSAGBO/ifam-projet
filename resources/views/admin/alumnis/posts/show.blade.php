@extends('layouts.admin')

@section('title', 'Examen de la publication — ' . $post->title)

@section('content')

{{-- Fil d'ariane --}}
<div style="margin-bottom:1.5rem; display:flex; align-items:center; gap:0.5rem; font-size:0.875rem;">
    <a href="{{ route('admin.alumnis.posts.index') }}" style="color:var(--color-primary); text-decoration:none; font-weight:600;">
        ← Retour aux publications
    </a>
    <span style="color:var(--color-outline);">/</span>
    <span style="color:var(--color-on-surface-variant);">Examen de publication</span>
</div>

@if(session('success'))
    <div style="background:#dcfce7; color:#15803d; padding:1rem 1.25rem; border-radius:0.75rem; font-size:0.9375rem; font-weight:600; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.5rem;">
        <span class="material-symbols-outlined">check_circle</span>
        {{ session('success') }}
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

<div style="display:grid; grid-template-columns:1fr 340px; gap:1.75rem; align-items:start;">
    {{-- Colonne Principale : Contenu & Photos --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:2rem; border:1px solid var(--color-outline-variant);">
        {{-- Titre et statut --}}
        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; margin-bottom:1.5rem; flex-wrap:wrap;">
            <div>
                <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem;">
                    {{ $post->title }}
                </h1>
                <div style="font-size:0.8125rem; color:var(--color-on-surface-variant);">
                    Soumis le {{ $post->created_at->format('d/m/Y à H:i') }}
                    @if($post->published_at)
                        • Publié le {{ $post->published_at->format('d/m/Y à H:i') }}
                    @endif
                </div>
            </div>

            <div>
                @if($post->isPending())
                    <span style="background:#fef3c7; color:#b45309; padding:0.375rem 0.875rem; border-radius:999px; font-size:0.8125rem; font-weight:700; display:inline-flex; align-items:center; gap:0.375rem;">
                        <span class="material-symbols-outlined" style="font-size:16px;">hourglass_empty</span>
                        En attente de validation
                    </span>
                @elseif($post->isPublished())
                    <span style="background:#dcfce7; color:#15803d; padding:0.375rem 0.875rem; border-radius:999px; font-size:0.8125rem; font-weight:700; display:inline-flex; align-items:center; gap:0.375rem;">
                        <span class="material-symbols-outlined" style="font-size:16px;">check_circle</span>
                        En ligne / Publié
                    </span>
                @elseif($post->isRejected())
                    <span style="background:#fee2e2; color:#b91c1c; padding:0.375rem 0.875rem; border-radius:999px; font-size:0.8125rem; font-weight:700; display:inline-flex; align-items:center; gap:0.375rem;">
                        <span class="material-symbols-outlined" style="font-size:16px;">cancel</span>
                        Refusé
                    </span>
                @endif
            </div>
        </div>

        @if($post->isRejected() && $post->rejection_reason)
            <div style="background:#fef2f2; border-left:4px solid #ef4444; padding:1rem; border-radius:0.5rem; margin-bottom:1.5rem; color:#991b1b;">
                <div style="font-weight:700; font-size:0.875rem; margin-bottom:0.25rem;">Motif du refus actuellement enregistré :</div>
                <div style="font-size:0.875rem;">{{ $post->rejection_reason }}</div>
                @if($post->validator)
                    <div style="font-size:0.75rem; color:#7f1d1d; margin-top:0.375rem;">
                        Par {{ $post->validator->full_name }} le {{ $post->rejected_at?->format('d/m/Y à H:i') }}
                    </div>
                @endif
            </div>
        @endif

        {{-- Galerie de photographies --}}
        <div style="margin-bottom:2rem;">
            <h3 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="color:var(--color-primary); font-size:20px;">photo_library</span>
                Photographies jointes ({{ $post->media->count() }}/8)
            </h3>

            @if($post->media->isEmpty())
                <div style="padding:1.5rem; background:var(--color-surface-container-low); border-radius:0.75rem; color:var(--color-on-surface-variant); font-size:0.875rem; text-align:center;">
                    Aucune photographie n'a été jointe à cette publication.
                </div>
            @else
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:1rem;">
                    @foreach($post->media as $media)
                        <div style="border-radius:0.75rem; overflow:hidden; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-high); position:relative;">
                            <img src="{{ $media->url }}" alt="{{ $media->caption ?? '' }}"
                                 style="width:100%; height:160px; object-fit:cover; display:block;">
                            @if($media->is_cover)
                                <span style="position:absolute; top:0.5rem; left:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.6875rem; font-weight:700; padding:0.125rem 0.5rem; border-radius:0.25rem;">
                                    Couverture
                                </span>
                            @endif
                            @if($media->caption)
                                <div style="padding:0.5rem 0.75rem; font-size:0.75rem; color:var(--color-on-surface); background:var(--color-surface-container-lowest);">
                                    {{ $media->caption }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Texte de la publication --}}
        <div>
            <h3 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.75rem;">
                Texte de la publication
            </h3>
            <div style="font-size:0.9375rem; line-height:1.7; color:var(--color-on-surface); white-space:pre-line; background:var(--color-surface-container-lowest); padding:1rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant);">
                {{ $post->content }}
            </div>
        </div>
    </div>

    {{-- Colonne Latérale : Décision & Auteur --}}
    <div style="display:flex; flex-direction:column; gap:1.5rem;">
        {{-- Panneau d'action de modération --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:1.5rem; border:1px solid var(--color-outline-variant);">
            <h3 style="font-size:1.0625rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem;">
                Décision de modération
            </h3>

            {{-- Bouton Approuver --}}
            @if(! $post->isPublished())
                <form action="{{ route('admin.alumnis.posts.approve', $post->id) }}" method="POST" style="margin-bottom:1rem;">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Confirmez-vous l\'approbation et la mise en ligne immédiate de cette publication ?');"
                            style="width:100%; height:44px; border-radius:0.625rem; border:none; background:#059669; color:#fff; font-size:0.9375rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:0.5rem; box-shadow:0 3px 8px rgba(5,150,105,0.3);">
                        <span class="material-symbols-outlined" style="font-size:20px;">check_circle</span>
                        Approuver et mettre en ligne
                    </button>
                </form>
            @endif

            {{-- Formulaire Refuser --}}
            <div style="border-top:1px solid var(--color-outline-variant); padding-top:1rem;">
                <h4 style="font-size:0.875rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem;">
                    Refuser la publication
                </h4>
                <p style="font-size:0.75rem; color:var(--color-on-surface-variant); margin:0 0 0.75rem;">
                    Un motif explicite est obligatoire et sera communiqué à l'alumni pour lui permettre de corriger son texte.
                </p>

                <form action="{{ route('admin.alumnis.posts.reject', $post->id) }}" method="POST">
                    @csrf
                    <textarea name="rejection_reason" rows="4" required minlength="10"
                              placeholder="Ex: Veuillez corriger la légende de la photo 2 ou préciser le cadre du défilé..."
                              style="width:100%; box-sizing:border-box; padding:0.75rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); font-size:0.8125rem; color:var(--color-on-surface); resize:vertical; margin-bottom:0.75rem;">{{ old('rejection_reason', $post->rejection_reason) }}</textarea>

                    <button type="submit"
                            onclick="return confirm('Confirmez-vous le refus de cette publication avec ce motif ?');"
                            style="width:100%; height:40px; border-radius:0.625rem; border:none; background:#dc2626; color:#fff; font-size:0.875rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:0.5rem;">
                        <span class="material-symbols-outlined" style="font-size:18px;">cancel</span>
                        Refuser avec ce motif
                    </button>
                </form>
            </div>

            {{-- Bouton Dépublier si actuellement en ligne --}}
            @if($post->isPublished())
                <div style="border-top:1px solid var(--color-outline-variant); padding-top:1rem; margin-top:1rem;">
                    <form action="{{ route('admin.alumnis.posts.unpublish', $post->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="reason" value="Retiré de la publication par la modération">
                        <button type="submit"
                                onclick="return confirm('Souhaitez-vous retirer cette publication de l\'espace public ?');"
                                style="width:100%; height:38px; border-radius:0.5rem; border:1px solid #dc2626; background:transparent; color:#dc2626; font-size:0.8125rem; font-weight:700; cursor:pointer;">
                            Retirer de l'espace public
                        </button>
                    </form>
                </div>
            @endif
        </div>

        {{-- Carte Auteur Alumni --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:1.5rem; border:1px solid var(--color-outline-variant);">
            <h3 style="font-size:0.9375rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem;">
                Auteur de la publication
            </h3>

            <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem;">
                @if($post->alumni->photo_url)
                    <img src="{{ $post->alumni->photo_url }}" alt="" style="width:48px; height:48px; border-radius:50%; object-fit:cover;">
                @else
                    <div style="width:48px; height:48px; border-radius:50%; background:var(--color-primary-container); color:var(--color-on-primary); display:flex; align-items:center; justify-content:center; font-size:1rem; font-weight:700;">
                        {{ $post->alumni->initials }}
                    </div>
                @endif
                <div>
                    <div style="font-weight:700; color:var(--color-on-surface);">
                        {{ $post->alumni->full_name }}
                    </div>
                    <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                        {{ $post->alumni->formation->name ?? 'Diplômé IFAM' }}
                    </div>
                </div>
            </div>

            <div style="font-size:0.8125rem; color:var(--color-on-surface-variant); display:flex; flex-direction:column; gap:0.5rem;">
                <div>
                    <strong>Statut :</strong> {{ $post->alumni->statut_actuel ?? 'Non renseigné' }}
                </div>
                <div>
                    <strong>Poste / Entreprise :</strong> {{ $post->alumni->poste_actuel ?? '—' }} ({{ $post->alumni->entreprise_actuelle ?? '—' }})
                </div>
                <div>
                    <strong>Email :</strong> {{ $post->alumni->email ?? '—' }}
                </div>
                <div>
                    <strong>Profil approuvé :</strong>
                    @if($post->alumni->is_approved)
                        <span style="color:#059669; font-weight:700;">Oui</span>
                    @else
                        <span style="color:#b45309; font-weight:700;">Non approuvé</span>
                    @endif
                </div>
            </div>

            <div style="margin-top:1rem; padding-top:0.75rem; border-top:1px solid var(--color-outline-variant);">
                <a href="{{ route('admin.alumnis.show', $post->alumni->id) }}"
                   style="color:var(--color-primary); font-size:0.8125rem; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:0.25rem;">
                    Voir la fiche complète de l'Alumni →
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
