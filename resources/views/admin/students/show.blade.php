@extends('layouts.admin')

@section('title', 'Dossier Étudiant')

@section('content')

{{-- En-tête --}}
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div style="display:flex; align-items:center; gap:1rem;">
        <a href="{{ route('admin.students.index') }}"
           style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
                  display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
                  transition:all 0.15s;"
           onmouseover="this.style.background='var(--color-surface-container-high)'"
           onmouseout="this.style.background='var(--color-surface-container)'">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
                Dossier : {{ $student->full_name }}
            </h1>
            <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0; display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                <span style="font-family:monospace; background:var(--color-surface-container-high); padding:0.125rem 0.375rem; border-radius:0.25rem; color:var(--color-primary-container); font-weight:700;">{{ $student->matricule }}</span>
                <span>• Année d'entrée : {{ $profile->entry_year ?? 'Non définie' }}</span>
                @php
                    $studentSites = $student->sites->concat($student->studentProfile?->enrollments->pluck('site')->filter() ?? collect())->unique('id');
                @endphp
                @if($studentSites->isNotEmpty())
                    <span>• Site(s) :</span>
                    @foreach($studentSites as $sSite)
                        <span style="padding:0.15rem 0.45rem; border-radius:0.375rem; font-size:0.75rem; font-weight:600; background:#f0fdf4; color:#166534; border:1px solid #bbf7d0;">
                            {{ $sSite->nom }}
                        </span>
                    @endforeach
                @endif
            </p>
        </div>
    </div>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('admin.students.edit', $student) }}"
           style="height:40px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.25rem; border-radius:0.75rem;
                  background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none;
                  font-size:0.875rem; font-weight:700; transition:all 0.15s; box-shadow:0 3px 10px rgba(228,0,102,0.3);"
           onmouseover="this.style.transform='translateY(-1px)'"
           onmouseout="this.style.transform=''">
            <span class="material-symbols-outlined" style="font-size:18px;">edit_document</span>
            Mettre à jour le dossier
        </a>
    </div>
</div>

<div style="display:grid; grid-template-columns:2fr 1fr; gap:1.5rem;">

    {{-- Colonne principale --}}
    <div style="display:flex; flex-direction:column; gap:1.5rem;">
        
        {{-- État Civil & Naissance --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
            <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.25rem; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.75rem;">
                <span class="material-symbols-outlined" style="color:var(--color-primary); font-size:24px;">assignment_ind</span>
                <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0;">
                    État Civil & Naissance
                </h2>
            </div>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Lieu de naissance</div>
                    <div style="color:var(--color-on-surface);">{{ $profile->birth_place ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Nationalité</div>
                    <div style="color:var(--color-on-surface);">{{ $profile->nationality ?? '—' }}</div>
                </div>
                <div style="grid-column:1 / -1;">
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Numéro d'Identification Personnel(NPI)</div>
                    <div style="color:var(--color-on-surface); font-family:monospace;">{{ $profile->birth_certificate_number ?? '—' }}</div>
                </div>
            </div>
        </div>

        {{-- Contact & Adresse --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
            <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.25rem; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.75rem;">
                <span class="material-symbols-outlined" style="color:var(--color-primary); font-size:24px;">home_pin</span>
                <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0;">
                    Adresse
                </h2>
            </div>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
                <div style="grid-column:1 / -1;">
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Adresse physique</div>
                    <div style="color:var(--color-on-surface);">{{ $profile->address ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Ville / Quartier</div>
                    <div style="color:var(--color-on-surface);">{{ $profile->city ?? '—' }}</div>
                </div>
            </div>
        </div>

        {{-- Historique Académique --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
            <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.25rem; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.75rem;">
                <span class="material-symbols-outlined" style="color:var(--color-primary); font-size:24px;">history_edu</span>
                <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0;">
                    Parcours antérieur
                </h2>
            </div>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Établissement précédent</div>
                    <div style="color:var(--color-on-surface);">{{ $profile->previous_school ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Dernier diplôme obtenu</div>
                    <div style="color:var(--color-on-surface);">{{ $profile->previous_diploma ?? '—' }}</div>
                </div>
            </div>
        </div>

    </div>

    {{-- Colonne latérale --}}
    <div style="display:flex; flex-direction:column; gap:1.5rem;">
        
        {{-- Tuteur légal / Contact urgence --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); border-top:4px solid var(--color-secondary);">
            <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.25rem;">
                <span class="material-symbols-outlined" style="color:var(--color-secondary); font-size:24px;">medical_information</span>
                <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0;">
                    Contact d'urgence
                </h2>
            </div>

            <div style="display:flex; flex-direction:column; gap:1rem;">
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Nom complet</div>
                    <div style="color:var(--color-on-surface); font-weight:600;">{{ $profile->guardian_name ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Lien de parenté</div>
                    <div style="color:var(--color-on-surface);">{{ $profile->guardian_relation ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Téléphone</div>
                    <div style="color:var(--color-on-surface);">
                        @if($profile->guardian_phone)
                            <a href="tel:{{ $profile->guardian_phone }}" style="color:var(--color-primary); text-decoration:none; font-weight:600;">{{ $profile->guardian_phone }}</a>
                        @else
                            —
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Notes Admin --}}
        <div style="background:var(--color-surface-container-low); border-radius:1rem; padding:1.5rem; box-shadow:inset 0 2px 4px rgba(0,0,0,0.02);">
            <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:1rem;">
                <span class="material-symbols-outlined" style="color:var(--color-on-surface-variant); font-size:20px;">notes</span>
                <h3 style="font-size:0.9375rem; font-weight:700; color:var(--color-on-surface); margin:0;">Notes internes (Admin)</h3>
            </div>
            @if($profile->admin_notes)
                <div style="font-size:0.875rem; color:var(--color-on-surface); white-space:pre-wrap; background:var(--color-surface-container-lowest); padding:1rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant);">{{ $profile->admin_notes }}</div>
            @else
                <p style="font-size:0.875rem; color:var(--color-on-surface-variant); font-style:italic; margin:0;">Aucune note sur ce dossier.</p>
            @endif
        </div>

    </div>

</div>

@endsection
