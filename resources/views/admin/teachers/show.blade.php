@extends('layouts.admin')

@section('title', 'Dossier Enseignant')

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div style="display:flex; align-items:center; gap:1rem;">
        <a href="{{ route('admin.teachers.index') }}"
           style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
                  display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
                  transition:all 0.15s;"
           onmouseover="this.style.background='var(--color-surface-container-high)'"
           onmouseout="this.style.background='var(--color-surface-container)'">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
                Dossier : {{ $teacher->full_name }}
            </h1>
            <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0; display:flex; align-items:center; gap:0.5rem;">
                <span style="text-transform:capitalize;">• Enseignant {{ $profile->teacher_type ?? 'Non défini' }}</span>
                @if($profile->modules->isNotEmpty())
                <span>• {{ $profile->modules->count() }} module(s) enseigné(s)</span>
                @endif
            </p>
        </div>
    </div>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('admin.teachers.edit', $teacher) }}"
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
        
        {{-- Modules enseignés --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
            <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.25rem; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.75rem;">
                <span class="material-symbols-outlined" style="color:var(--color-primary); font-size:24px;">psychology</span>
                <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0;">
                    Modules enseignés & Qualification
                </h2>
            </div>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
                <div style="grid-column:1 / -1;">
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.5rem;">Modules enseignés</div>
                    @if($profile->modules->isNotEmpty())
                        <div style="display:flex; flex-wrap:wrap; gap:0.375rem;">
                            @foreach($profile->modules as $mod)
                                <span style="display:inline-flex; align-items:center; padding:0.25rem 0.625rem; border-radius:0.5rem;
                                             background:var(--color-surface-container); font-size:0.8125rem; font-weight:600; color:var(--color-on-surface);">
                                    {{ $mod->code }} — {{ $mod->name }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <span style="color:var(--color-on-surface-variant); font-style:italic;">Aucun module assigné</span>
                    @endif
                </div>
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Dernier diplôme / Qualification</div>
                    <div style="color:var(--color-on-surface);">{{ $profile->qualification ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Numéro d'identification IFU/NPI (interne)</div>
                    <div style="color:var(--color-on-surface); font-family:monospace;">{{ $profile->id_number ?? '—' }}</div>
                </div>
            </div>
        </div>

        {{-- Contact --}}
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
                        @if($teacher->email)
                            <a href="mailto:{{ $teacher->email }}" style="color:var(--color-primary); text-decoration:none;">{{ $teacher->email }}</a>
                        @else
                            <span style="color:var(--color-on-surface-variant); font-style:italic;">Non spécifié</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Téléphone</div>
                    <div style="color:var(--color-on-surface);">
                        @if($teacher->phone)
                            <a href="tel:{{ $teacher->phone }}" style="color:var(--color-primary); text-decoration:none;">{{ $teacher->phone }}</a>
                        @else
                            <span style="color:var(--color-on-surface-variant); font-style:italic;">Non spécifié</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div style="display:flex; flex-direction:column; gap:1.5rem;">
        
        {{-- Contrat / Embauche --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); border-top:4px solid var(--color-secondary);">
            <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.25rem;">
                <span class="material-symbols-outlined" style="color:var(--color-secondary); font-size:24px;">work</span>
                <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0;">
                    Contrat
                </h2>
            </div>

            <div style="display:flex; flex-direction:column; gap:1rem;">
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Type de contrat</div>
                    <div style="color:var(--color-on-surface); text-transform:capitalize; font-weight:600;">{{ $profile->teacher_type ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Date d'embauche (début)</div>
                    <div style="color:var(--color-on-surface);">
                        {{ $profile->hired_at ? $profile->hired_at->format('d/m/Y') : '—' }}
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
