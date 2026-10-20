@extends('layouts.admin')

@section('title', 'Dossier Gestionnaire')

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div style="display:flex; align-items:center; gap:1rem;">
        <a href="{{ route('admin.session-managers.index') }}"
           style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
                  display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
                  transition:all 0.15s;"
           onmouseover="this.style.background='var(--color-surface-container-high)'"
           onmouseout="this.style.background='var(--color-surface-container)'">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
                Dossier : {{ $session_manager->full_name }}
            </h1>
            <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
                Gestionnaire de scolarité / Sessions
            </p>
        </div>
    </div>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('admin.session-managers.edit', $session_manager) }}"
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
                    Informations & Département
                </h2>
            </div>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Email</div>
                    <div style="color:var(--color-on-surface);">
                        @if($session_manager->email)
                            <a href="mailto:{{ $session_manager->email }}" style="color:var(--color-primary); text-decoration:none;">{{ $session_manager->email }}</a>
                        @else
                            <span style="color:var(--color-on-surface-variant); font-style:italic;">Non spécifié</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Téléphone</div>
                    <div style="color:var(--color-on-surface);">
                        @if($session_manager->phone)
                            <a href="tel:{{ $session_manager->phone }}" style="color:var(--color-primary); text-decoration:none;">{{ $session_manager->phone }}</a>
                        @else
                            <span style="color:var(--color-on-surface-variant); font-style:italic;">Non spécifié</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Département d'affectation</div>
                    <div style="color:var(--color-on-surface);">{{ $profile->department ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">Rôle interne détaillé</div>
                    <div style="color:var(--color-on-surface);">{{ $profile->internal_role ?? '—' }}</div>
                </div>
            </div>
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
