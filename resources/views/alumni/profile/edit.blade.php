@extends('layouts.role')

@section('title', 'Mon Profil Professionnel Alumni')
@section('role_label', 'Espace Alumni')
@section('home_route', route('alumni.dashboard'))

@section('nav_links')
    <a href="{{ route('alumni.dashboard') }}"
       style="height:36px; display:inline-flex; align-items:center; gap:0.375rem; padding:0 0.875rem; border-radius:0.5rem; font-size:0.875rem; font-weight:600; text-decoration:none; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined" style="font-size:18px;">dashboard</span>
        Accueil
    </a>
    <a href="{{ route('alumni.posts.index') }}"
       style="height:36px; display:inline-flex; align-items:center; gap:0.375rem; padding:0 0.875rem; border-radius:0.5rem; font-size:0.875rem; font-weight:600; text-decoration:none; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined" style="font-size:18px;">photo_library</span>
        Mes publications
    </a>
    <a href="{{ route('alumni.profile.edit') }}"
       style="height:36px; display:inline-flex; align-items:center; gap:0.375rem; padding:0 0.875rem; border-radius:0.5rem; font-size:0.875rem; font-weight:600; text-decoration:none; background:var(--color-primary-container); color:var(--color-on-primary);">
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

<div style="max-width:960px; margin:0 auto; padding:2rem 1.5rem;">

    <div style="margin-bottom:1.75rem;">
        <h1 style="font-family:var(--font-display); font-size:1.625rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Mon Profil Professionnel
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Ces informations permettent à l'IFAM et au réseau professionnel de suivre votre parcours et vos réalisations.
        </p>
    </div>

    @if(session('success'))
        <div style="background:#dcfce7; color:#15803d; padding:1rem 1.25rem; border-radius:0.75rem; font-size:0.9375rem; font-weight:600; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.5rem;">
            <span class="material-symbols-outlined">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    {{-- Encadré Parcours académique certifié (Lecture seule) --}}
    <div style="background:var(--color-surface-container-low); border-radius:0.875rem; padding:1.25rem 1.5rem; border:1px solid var(--color-outline-variant); margin-bottom:1.75rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
        <div>
            <div style="font-size:0.6875rem; font-weight:700; text-transform:uppercase; color:var(--color-primary); letter-spacing:0.06em;">
                Parcours académique certifié par l'IFAM
            </div>
            <div style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin-top:0.125rem;">
                {{ $alumni->formation->name ?? 'Diplôme IFAM' }}
                @if($alumni->date_diplome)
                    <span style="font-size:0.875rem; font-weight:600; color:var(--color-on-surface-variant);">
                        • Promotion {{ $alumni->date_diplome->format('Y') }}
                    </span>
                @endif
            </div>
        </div>

        <div>
            @if($alumni->is_approved)
                <span style="background:#dcfce7; color:#15803d; padding:0.375rem 0.875rem; border-radius:999px; font-size:0.8125rem; font-weight:700; display:inline-flex; align-items:center; gap:0.375rem;">
                    <span class="material-symbols-outlined" style="font-size:16px;">verified</span>
                    Statut Alumni validé
                </span>
            @else
                <span style="background:#fef3c7; color:#b45309; padding:0.375rem 0.875rem; border-radius:999px; font-size:0.8125rem; font-weight:700; display:inline-flex; align-items:center; gap:0.375rem;">
                    <span class="material-symbols-outlined" style="font-size:16px;">hourglass_empty</span>
                    Validation administrative en cours
                </span>
            @endif
        </div>
    </div>

    <form action="{{ route('alumni.profile.update') }}" method="POST" enctype="multipart/form-data"
          style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:2rem; border:1px solid var(--color-outline-variant);">
        @csrf
        @method('PUT')

        <div style="display:grid; grid-template-columns:1fr; gap:1.75rem;">

            {{-- Photo & Identité --}}
            <div style="display:grid; grid-template-columns:120px 1fr; gap:1.5rem; align-items:start;">
                <div>
                    @if($alumni->photo_url)
                        <img src="{{ $alumni->photo_url }}" alt="" style="width:110px; height:110px; border-radius:50%; object-fit:cover; border:3px solid var(--color-outline-variant); margin-bottom:0.5rem; display:block;">
                    @else
                        <div style="width:110px; height:110px; border-radius:50%; background:var(--color-primary-container); color:var(--color-on-primary); font-size:2rem; font-weight:700; display:flex; align-items:center; justify-content:center; margin-bottom:0.5rem;">
                            {{ $alumni->initials }}
                        </div>
                    @endif
                </div>

                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.375rem;">
                        Changer la photo de profil (JPEG, PNG ou WebP - Max 5 Mo)
                    </label>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                           style="font-size:0.875rem; color:var(--color-on-surface-variant); margin-bottom:0.5rem;">
                    <p style="font-size:0.75rem; color:var(--color-on-surface-variant); margin:0;">
                        Une photo professionnelle met en valeur votre profil auprès des recruteurs et partenaires.
                    </p>
                    @error('photo') <span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Situation Professionnelle --}}
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:1.25rem;">
                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.375rem;">
                        Situation actuelle
                    </label>
                    <select name="statut_actuel"
                            style="width:100%; box-sizing:border-box; height:44px; padding:0 0.875rem; border-radius:0.5rem;
                                   border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                   font-size:0.9375rem; color:var(--color-on-surface);">
                        <option value="">— Sélectionner —</option>
                        @foreach($statutsPro as $val => $label)
                            <option value="{{ $val }}" {{ old('statut_actuel', $alumni->statut_actuel) == $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.375rem;">
                        Atelier / Entreprise / Marque
                    </label>
                    <input type="text" name="entreprise_actuelle" value="{{ old('entreprise_actuelle', $alumni->entreprise_actuelle) }}"
                           placeholder="Ex: Maison de Mode IFAM, Indépendant..."
                           style="width:100%; box-sizing:border-box; height:44px; padding:0 0.875rem; border-radius:0.5rem;
                                  border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                  font-size:0.9375rem; color:var(--color-on-surface);">
                </div>

                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.375rem;">
                        Métier / Fonction exercée
                    </label>
                    <input type="text" name="poste_actuel" value="{{ old('poste_actuel', $alumni->poste_actuel) }}"
                           placeholder="Ex: Styliste Créatrice, Modéliste..."
                           style="width:100%; box-sizing:border-box; height:44px; padding:0 0.875rem; border-radius:0.5rem;
                                  border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                  font-size:0.9375rem; color:var(--color-on-surface);">
                </div>
            </div>

            {{-- Coordonnées & Ville --}}
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:1.25rem;">
                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.375rem;">
                        Ville / Pays de résidence professionnelle
                    </label>
                    <input type="text" name="lieu_residence" value="{{ old('lieu_residence', $alumni->lieu_residence) }}"
                           placeholder="Ex: Cotonou, Bénin"
                           style="width:100%; box-sizing:border-box; height:44px; padding:0 0.875rem; border-radius:0.5rem;
                                  border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                  font-size:0.9375rem; color:var(--color-on-surface);">
                </div>

                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.375rem;">
                        Email professionnel
                    </label>
                    <input type="email" name="email" value="{{ old('email', $alumni->email) }}"
                           placeholder="alumni@atelier.com"
                           style="width:100%; box-sizing:border-box; height:44px; padding:0 0.875rem; border-radius:0.5rem;
                                  border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                  font-size:0.9375rem; color:var(--color-on-surface);">
                </div>

                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.375rem;">
                        Téléphone professionnel (non public)
                    </label>
                    <input type="text" name="telephone" value="{{ old('telephone', $alumni->telephone) }}"
                           placeholder="+229 01 00 00 00"
                           style="width:100%; box-sizing:border-box; height:44px; padding:0 0.875rem; border-radius:0.5rem;
                                  border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                  font-size:0.9375rem; color:var(--color-on-surface);">
                </div>
            </div>

            {{-- Liens Professionnels --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.375rem;">
                        Profil LinkedIn
                    </label>
                    <input type="url" name="linkedin_url" value="{{ old('linkedin_url', $alumni->linkedin_url) }}"
                           placeholder="https://linkedin.com/in/mon-profil"
                           style="width:100%; box-sizing:border-box; height:44px; padding:0 0.875rem; border-radius:0.5rem;
                                  border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                  font-size:0.9375rem; color:var(--color-on-surface);">
                </div>

                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.375rem;">
                        Portfolio / Site web / Instagram de créations
                    </label>
                    <input type="url" name="portfolio_url" value="{{ old('portfolio_url', $alumni->portfolio_url) }}"
                           placeholder="https://mon-portfolio.com"
                           style="width:100%; box-sizing:border-box; height:44px; padding:0 0.875rem; border-radius:0.5rem;
                                  border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                  font-size:0.9375rem; color:var(--color-on-surface);">
                </div>
            </div>

            {{-- Bio --}}
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.375rem;">
                    Présentation du parcours & Témoignage
                </label>
                <textarea name="bio" rows="4"
                          placeholder="Décrivez votre univers créatif, vos projets de mode, les compétences acquises à l'IFAM et vos ambitions..."
                          style="width:100%; box-sizing:border-box; padding:0.75rem 1rem; border-radius:0.5rem;
                                 border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                 font-size:0.9375rem; color:var(--color-on-surface); resize:vertical;">{{ old('bio', $alumni->bio) }}</textarea>
            </div>

            {{-- Consentement & Visibilité --}}
            <div style="background:var(--color-surface-container-low); padding:1.25rem; border-radius:0.75rem; border:1px solid var(--color-outline-variant);">
                <label style="display:flex; align-items:flex-start; gap:0.75rem; cursor:pointer;">
                    <input type="checkbox" name="is_visible_directory" value="1"
                           {{ old('is_visible_directory', $alumni->is_visible_directory) ? 'checked' : '' }}
                           style="width:20px; height:20px; margin-top:0.125rem;">
                    <div>
                        <div style="font-size:0.9375rem; font-weight:700; color:var(--color-on-surface);">
                            Autoriser la visibilité de mon profil dans l'annuaire Alumni
                        </div>
                        <div style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin-top:0.25rem;">
                            Cette option ne publie pas votre profil sans votre accord explicite et l'approbation de l'administration.
                        </div>
                    </div>
                </label>
                <label style="display:flex; align-items:flex-start; gap:0.75rem; cursor:pointer; margin-top:1rem;">
                    <input type="hidden" name="is_public" value="0">
                    <input type="checkbox" name="is_public" value="1"
                           {{ old('is_public', $alumni->is_public) ? 'checked' : '' }}
                           style="width:20px; height:20px; margin-top:0.125rem;">
                    <div>
                        <div style="font-size:0.9375rem; font-weight:700; color:var(--color-on-surface);">
                            J'accepte l'affichage public de mon profil Alumni
                        </div>
                        <div style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin-top:0.25rem;">
                            Mon prénom, nom, photo, formation, année de sortie, activité, témoignage et créations publiées peuvent être affichés. Aucune coordonnée privée, note ou adresse ne sera publiée.
                        </div>
                    </div>
                </label>
            </div>

        </div>

        <div style="margin-top:2rem; display:flex; justify-content:flex-end; gap:1rem;">
            <a href="{{ route('alumni.dashboard') }}"
               style="height:44px; display:inline-flex; align-items:center; padding:0 1.25rem; border-radius:0.625rem;
                      background:transparent; color:var(--color-on-surface-variant); font-size:0.9375rem; font-weight:600; text-decoration:none;">
                Annuler
            </a>
            <button type="submit"
                    style="height:44px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.5rem; border-radius:0.625rem; border:none; cursor:pointer;
                           background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.9375rem; font-weight:700;
                           box-shadow:0 3px 10px rgba(228,0,102,0.3);">
                <span class="material-symbols-outlined" style="font-size:20px;">save</span>
                Enregistrer mon profil
            </button>
        </div>
    </form>

</div>

@endsection
