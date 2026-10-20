@extends('layouts.admin')

@section('title', 'Dossier Parent')

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div style="display:flex; align-items:center; gap:1rem;">
        <a href="{{ route('admin.parents.index') }}"
           style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
                  display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
                  transition:all 0.15s;"
           onmouseover="this.style.background='var(--color-surface-container-high)'"
           onmouseout="this.style.background='var(--color-surface-container)'">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
                Dossier Parent : {{ $parent->full_name }}
            </h1>
            <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
                {{ $parent->children->count() }} enfant(s) rattaché(s)
            </p>
        </div>
    </div>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('admin.parents.edit', $parent) }}"
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

    <div style="display:flex; flex-direction:column; gap:1.5rem;">
        
        {{-- Informations et Contact --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
            <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.25rem; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.75rem;">
                <span class="material-symbols-outlined" style="color:var(--color-primary); font-size:24px;">contact_mail</span>
                <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0;">
                    Informations de contact
                </h2>
            </div>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Email</div>
                    <div style="color:var(--color-on-surface);">
                        @if($parent->email)
                            <a href="mailto:{{ $parent->email }}" style="color:var(--color-primary); text-decoration:none;">{{ $parent->email }}</a>
                        @else
                            <span style="color:var(--color-on-surface-variant); font-style:italic;">Non spécifié</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Téléphone</div>
                    <div style="color:var(--color-on-surface);">
                        @if($parent->phone)
                            <a href="tel:{{ $parent->phone }}" style="color:var(--color-primary); text-decoration:none;">{{ $parent->phone }}</a>
                        @else
                            <span style="color:var(--color-on-surface-variant); font-style:italic;">Non spécifié</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Profession</div>
                    <div style="color:var(--color-on-surface);">{{ $profile->profession ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Employeur</div>
                    <div style="color:var(--color-on-surface);">{{ $profile->employer ?? '—' }}</div>
                </div>
            </div>
        </div>

        {{-- Enfants rattachés (Fratrie) --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); border-top:4px solid var(--color-secondary);">
            <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.25rem;">
                <span class="material-symbols-outlined" style="color:var(--color-secondary); font-size:24px;">escalator_warning</span>
                <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0;">
                    Enfants rattachés
                </h2>
            </div>

            @if($parent->children->count() > 0)
                <div style="display:flex; flex-direction:column; gap:0.75rem;">
                    @foreach($parent->children as $child)
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem; border-radius:0.75rem; background:var(--color-surface-container-low); border:1px solid var(--color-outline-variant);">
                            <div style="display:flex; align-items:center; gap:0.75rem;">
                                <div style="width:40px; height:40px; border-radius:9999px; background:#dbeafe; color:#1e40af; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.875rem;">
                                    {{ $child->initials }}
                                </div>
                                <div>
                                    <div style="font-weight:700; color:var(--color-on-surface);">{{ $child->full_name }}</div>
                                    <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                                        Lien : {{ $child->pivot->relationship ?? 'Non défini' }}
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('admin.students.show', $child) }}"
                               style="height:32px; padding:0 0.75rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-on-surface); display:inline-flex; align-items:center; text-decoration:none; font-size:0.8125rem; font-weight:600; transition:background 0.15s;"
                               onmouseover="this.style.background='var(--color-surface-container-high)'"
                               onmouseout="this.style.background='var(--color-surface-container)'">
                                Dossier de l'élève
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="padding:2rem; text-align:center; background:var(--color-surface-container-low); border-radius:0.75rem; border:1px dashed var(--color-outline-variant);">
                    <span class="material-symbols-outlined" style="font-size:32px; color:var(--color-on-surface-variant); opacity:0.5; margin-bottom:0.5rem;">child_care</span>
                    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
                        Aucun enfant n'est rattaché à ce parent.
                    </p>
                </div>
            @endif
        </div>

    </div>

    <div style="display:flex; flex-direction:column; gap:1.5rem;">

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
