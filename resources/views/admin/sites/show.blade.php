@extends('layouts.admin')

@section('title', $site->nom)

@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.sites.index') }}"
       style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
              display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
              transition:all 0.15s;"
       onmouseover="this.style.background='var(--color-surface-container-high)'"
       onmouseout="this.style.background='var(--color-surface-container)'">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div style="flex:1;">
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0;">
                {{ $site->nom }}
            </h1>
            @if($site->est_actif)
            <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#ecfdf5; color:#065f46; display:inline-flex; align-items:center; gap:0.25rem;">
                <span style="width:6px;height:6px;border-radius:9999px;background:#059669;"></span>Actif
            </span>
            @else
            <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#f3f4f6; color:#6b7280; display:inline-flex; align-items:center; gap:0.25rem;">
                <span style="width:6px;height:6px;border-radius:9999px;background:#9ca3af;"></span>Inactif
            </span>
            @endif
        </div>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0.25rem 0 0;">{{ $site->ville }}</p>
    </div>
    <a href="{{ route('admin.sites.edit', $site) }}"
       style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem;
              background:var(--color-primary-container); color:var(--color-on-primary); border-radius:0.75rem;
              text-decoration:none; font-size:0.875rem; font-weight:700;
              box-shadow:0 3px 10px rgba(228,0,102,0.3); transition:all 0.2s;"
       onmouseover="this.style.transform='translateY(-1px)'"
       onmouseout="this.style.transform=''">
        <span class="material-symbols-outlined" style="font-size:20px;">edit</span>
        Modifier
    </a>
</div>

{{-- Statistiques --}}
<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:1.5rem;">
    @foreach([
        ['label' => 'Sessions', 'count' => $site->academic_sessions_count, 'icon' => 'calendar_month'],
        ['label' => 'Inscriptions', 'count' => $site->enrollments_count, 'icon' => 'how_to_reg'],
        ['label' => 'Salles', 'count' => $site->rooms_count, 'icon' => 'meeting_room'],
        ['label' => 'Dépenses', 'count' => $site->expenses_count, 'icon' => 'payments'],
        ['label' => 'Alumni', 'count' => $site->alumnis_count, 'icon' => 'school'],
        ['label' => 'Messages', 'count' => $site->contact_messages_count, 'icon' => 'mail'],
    ] as $stat)
    <div style="background:var(--color-surface-container-lowest); border-radius:0.875rem; padding:1.25rem; box-shadow:0 1px 4px rgba(11,28,48,0.04); display:flex; align-items:center; gap:1rem;">
        <span class="material-symbols-outlined" style="font-size:28px; color:var(--color-primary-container);">{{ $stat['icon'] }}</span>
        <div>
            <div style="font-size:1.5rem; font-weight:700; color:var(--color-on-surface);">{{ $stat['count'] }}</div>
            <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">{{ $stat['label'] }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- Infos --}}
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:2rem; max-width:600px;">
    <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1.5rem;">Informations</h2>
    <dl style="display:grid; grid-template-columns:1fr 2fr; gap:0.875rem 1rem; font-size:0.875rem;">
        <dt style="color:var(--color-on-surface-variant); font-weight:600;">Nom</dt>
        <dd style="color:var(--color-on-surface); margin:0;">{{ $site->nom }}</dd>

        <dt style="color:var(--color-on-surface-variant); font-weight:600;">Ville</dt>
        <dd style="color:var(--color-on-surface); margin:0;">{{ $site->ville }}</dd>

        <dt style="color:var(--color-on-surface-variant); font-weight:600;">Adresse</dt>
        <dd style="color:var(--color-on-surface); margin:0;">{{ $site->adresse ?: '—' }}</dd>

        <dt style="color:var(--color-on-surface-variant); font-weight:600;">Téléphone</dt>
        <dd style="color:var(--color-on-surface); margin:0;">{{ $site->telephone ?: '—' }}</dd>

        <dt style="color:var(--color-on-surface-variant); font-weight:600;">E-mail</dt>
        <dd style="color:var(--color-on-surface); margin:0;">{{ $site->email ?: '—' }}</dd>

        <dt style="color:var(--color-on-surface-variant); font-weight:600;">Créé le</dt>
        <dd style="color:var(--color-on-surface); margin:0;">{{ $site->created_at->format('d/m/Y') }}</dd>
    </dl>
</div>

@endsection
