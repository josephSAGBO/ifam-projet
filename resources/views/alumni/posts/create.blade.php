@extends('layouts.role')

@section('title', 'Nouvelle Publication Alumni')
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
                Nouvelle Publication
            </h1>
            <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
                Partagez une création, un événement de mode, un défilé ou une nouvelle avec l'Institut IFAM.
            </p>
        </div>
    </div>

    @if($errors->any())
        <div style="background:#fee2e2; color:#b91c1c; padding:1rem 1.25rem; border-radius:0.75rem; font-size:0.9375rem; font-weight:600; margin-bottom:1.5rem;">
            <ul style="margin:0; padding-left:1.25rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('alumni.posts.store') }}" method="POST" enctype="multipart/form-data" id="postForm"
          style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:2rem; border:1px solid var(--color-outline-variant);">
        @csrf

        {{-- Titre --}}
        <div style="margin-bottom:1.5rem;">
            <label style="display:block; font-size:0.875rem; font-weight:700; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Titre de la publication <span style="color:#dc2626;">*</span>
            </label>
            <input type="text" name="title" value="{{ old('title') }}" required
                   placeholder="Ex: Lancement de ma collection Haute Couture Printemps 2026..."
                   style="width:100%; box-sizing:border-box; height:46px; padding:0 1rem; border-radius:0.625rem;
                          border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                          font-size:1rem; font-weight:600; color:var(--color-on-surface);">
        </div>

        {{-- Contenu textuel --}}
        <div style="margin-bottom:1.75rem;">
            <label style="display:block; font-size:0.875rem; font-weight:700; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Description & Récit de votre réalisation <span style="color:#dc2626;">*</span>
            </label>
            <textarea name="content" rows="7" required
                      placeholder="Détaillez votre projet : sources d'inspiration, matières textiles utilisées, techniques de modélisme, lieu de l'événement, retour d'expérience..."
                      style="width:100%; box-sizing:border-box; padding:0.875rem 1rem; border-radius:0.625rem;
                             border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                             font-size:0.9375rem; color:var(--color-on-surface); resize:vertical; line-height:1.6;">{{ old('content') }}</textarea>
        </div>

        {{-- Zone de téléversement des photographies --}}
        <div style="margin-bottom:2rem; background:var(--color-surface-container-low); padding:1.5rem; border-radius:0.875rem; border:1px solid var(--color-outline-variant);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem; flex-wrap:wrap; gap:0.5rem;">
                <label style="font-size:0.9375rem; font-weight:700; color:var(--color-on-surface); display:flex; align-items:center; gap:0.5rem;">
                    <span class="material-symbols-outlined" style="color:var(--color-primary); font-size:22px;">add_photo_alternate</span>
                    Photographies & Créations (Jusqu'à 8 photos)
                </label>
                <span style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                    Formats : JPEG, JPG, PNG, WebP • Max 5 Mo par photo
                </span>
            </div>

            <div style="border:2px dashed var(--color-outline-variant); border-radius:0.75rem; padding:1.5rem; text-align:center; background:var(--color-surface-container-lowest); cursor:pointer;"
                 onclick="document.getElementById('photosInput').click();">
                <span class="material-symbols-outlined" style="font-size:40px; color:var(--color-primary); margin-bottom:0.5rem; display:block;">
                    cloud_upload
                </span>
                <div style="font-size:0.875rem; font-weight:700; color:var(--color-on-surface); margin-bottom:0.25rem;">
                    Cliquez pour sélectionner vos photos
                </div>
                <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                    Vous pouvez sélectionner plusieurs photos en même temps (maximum 8 au total).
                </div>
                <input type="file" name="photos[]" id="photosInput" multiple accept="image/jpeg,image/png,image/webp"
                       style="display:none;" onchange="previewImages(this)">
            </div>

            <div id="imagePreviewsContainer" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(160px, 1fr)); gap:1rem; margin-top:1.25rem;"></div>
        </div>

        {{-- Boutons d'action : Brouillon ou Soumission --}}
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
                Enregistrer en brouillon
            </button>

            <button type="submit" name="action" value="submit"
                    onclick="return confirm('Soumettre cette publication pour modération par la direction ?');"
                    style="height:44px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.5rem; border-radius:0.625rem; border:none; cursor:pointer;
                           background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.875rem; font-weight:700;
                           box-shadow:0 3px 10px rgba(228,0,102,0.3);">
                <span class="material-symbols-outlined" style="font-size:20px;">send</span>
                Enregistrer et Soumettre
            </button>
        </div>
    </form>

</div>

<script>
    function previewImages(input) {
        const container = document.getElementById('imagePreviewsContainer');
        container.innerHTML = '';

        if (!input.files || input.files.length === 0) return;

        if (input.files.length > 8) {
            alert('Vous ne pouvez sélectionner que 8 photos au maximum.');
            input.value = '';
            return;
        }

        Array.from(input.files).forEach((file, index) => {
            if (file.size > 5 * 1024 * 1024) {
                alert(`Le fichier ${file.name} dépasse la limite autorisée de 5 Mo.`);
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const card = document.createElement('div');
                card.style.cssText = 'border-radius:0.5rem; overflow:hidden; border:1px solid var(--color-outline-variant); background:#fff; position:relative;';

                card.innerHTML = `
                    <div style="height:120px; overflow:hidden; background:#eee;">
                        <img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div style="padding:0.5rem;">
                        <input type="text" name="captions[${index}]" placeholder="Légende (facultatif)"
                               style="width:100%; box-sizing:border-box; padding:0.25rem 0.5rem; font-size:0.6875rem; border-radius:0.25rem; border:1px solid #ddd;">
                        <label style="display:flex; align-items:center; gap:0.25rem; font-size:0.6875rem; margin-top:0.375rem; cursor:pointer;">
                            <input type="radio" name="cover_index" value="${index}" ${index === 0 ? 'checked' : ''}>
                            Couverture
                        </label>
                    </div>
                `;
                container.appendChild(card);
            };
            reader.readAsDataURL(file);
        });
    }
</script>

@endsection
