@extends('layouts.admin')

@section('title', 'Lecture du message')

@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.contact.index') }}"
       style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
              display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
              transition:all 0.15s;"
       onmouseover="this.style.background='var(--color-surface-container-high)'"
       onmouseout="this.style.background='var(--color-surface-container)'">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Lecture du message
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Reçu le {{ $contact->created_at->format('d/m/Y à H:i') }}
        </p>
    </div>
</div>

<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden; max-width:800px;">
    
    <div style="padding:1.5rem; border-bottom:1px solid var(--color-surface-container); display:flex; align-items:center; justify-content:space-between; gap:1rem;">
        <div style="display:flex; align-items:center; gap:1rem;">
            <div style="width:48px; height:48px; border-radius:50%; background:var(--color-primary-container); color:var(--color-on-primary); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:1.125rem;">
                {{ $contact->user?->initials ?? '?' }}
            </div>
            <div>
                <div style="font-weight:700; color:var(--color-on-surface); font-size:1rem;">
                    {{ $contact->user?->full_name ?? 'Utilisateur inconnu' }}
                </div>
                <div style="font-size:0.8125rem; color:var(--color-on-surface-variant);">
                    <a href="mailto:{{ $contact->user?->email }}" style="color:inherit; text-decoration:none;">
                        {{ $contact->user?->email ?? 'Aucun email' }}
                    </a>
                </div>
            </div>
        </div>
        
        <div style="text-align:right;">
            <span style="display:inline-flex; align-items:center; gap:0.25rem; padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#e0f2fe; color:#0369a1;">
                <span class="material-symbols-outlined" style="font-size:14px;">done_all</span>
                Lu le {{ $contact->read_at?->format('d/m/Y H:i') }}
            </span>
        </div>
    </div>

    <div style="padding:2rem 1.5rem;">
        <h2 style="font-size:1.25rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1.5rem; padding-bottom:1rem; border-bottom:1px dashed var(--color-outline-variant);">
            {{ $contact->subject }}
        </h2>
        
        <div style="font-size:0.9375rem; line-height:1.6; color:var(--color-on-surface); white-space:pre-wrap;">{{ $contact->message }}</div>
    </div>
    
    <div style="padding:1rem 1.5rem; background:var(--color-surface-container-low); border-top:1px solid var(--color-surface-container); display:flex; justify-content:flex-end; gap:0.75rem;">
        <a href="mailto:{{ $contact->user?->email }}?subject=RE: {{ rawurlencode($contact->subject) }}"
           style="height:40px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.25rem; border-radius:0.75rem;
                  background:var(--color-primary); color:var(--color-on-primary); text-decoration:none;
                  font-size:0.875rem; font-weight:700; transition:all 0.15s; box-shadow:0 3px 10px rgba(228,0,102,0.3);"
           onmouseover="this.style.transform='translateY(-1px)'"
           onmouseout="this.style.transform=''">
            <span class="material-symbols-outlined" style="font-size:18px;">reply</span>
            Répondre par email
        </a>
    </div>

</div>
@endsection
