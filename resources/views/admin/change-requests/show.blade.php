@extends('layouts.admin')

@section('title', 'Traitement de la demande')

@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.change-requests.index') }}"
       style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
              display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
              transition:all 0.15s;"
       onmouseover="this.style.background='var(--color-surface-container-high)'"
       onmouseout="this.style.background='var(--color-surface-container)'">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Détail de la demande ({{ ucfirst($changeRequest->type) }})
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Soumise le {{ $changeRequest->created_at->format('d/m/Y à H:i') }} par {{ $changeRequest->requester->full_name ?? 'Inconnu' }}
        </p>
    </div>
</div>

@if(session('success'))
<div style="background:#dcfce7; color:#15803d; padding:1rem; border-radius:0.75rem; margin-bottom:1.5rem; font-size:0.875rem; font-weight:600; display:flex; align-items:center; gap:0.5rem;">
    <span class="material-symbols-outlined">check_circle</span>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div style="background:#fee2e2; color:#b91c1c; padding:1rem; border-radius:0.75rem; margin-bottom:1.5rem; font-size:0.875rem; font-weight:600; display:flex; align-items:center; gap:0.5rem;">
    <span class="material-symbols-outlined">error</span>
    {{ session('error') }}
</div>
@endif

<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden; max-width:800px;">
    
    <div style="padding:1.5rem; border-bottom:1px solid var(--color-surface-container); display:flex; align-items:center; justify-content:space-between; gap:1rem;">
        <div style="display:flex; align-items:center; gap:1rem;">
            <div style="width:48px; height:48px; border-radius:50%; background:var(--color-primary-container); color:var(--color-on-primary); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:1.125rem;">
                {{ $changeRequest->requester?->initials ?? '?' }}
            </div>
            <div>
                <div style="font-weight:700; color:var(--color-on-surface); font-size:1rem;">
                    {{ $changeRequest->requester?->full_name ?? 'Utilisateur inconnu' }}
                </div>
                <div style="font-size:0.8125rem; color:var(--color-on-surface-variant);">
                    Rôle : {{ ucfirst($changeRequest->requester?->role ?? '') }}
                </div>
            </div>
        </div>
        
        <div style="text-align:right;">
            @if($changeRequest->status === 'pending')
                <span style="display:inline-flex; align-items:center; gap:0.25rem; padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#fef3c7; color:#b45309;">
                    <span class="material-symbols-outlined" style="font-size:14px;">pending</span>
                    En attente
                </span>
            @elseif($changeRequest->status === 'approved')
                <span style="display:inline-flex; align-items:center; gap:0.25rem; padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#dcfce7; color:#15803d;">
                    <span class="material-symbols-outlined" style="font-size:14px;">check_circle</span>
                    Approuvée
                </span>
            @else
                <span style="display:inline-flex; align-items:center; gap:0.25rem; padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#fee2e2; color:#b91c1c;">
                    <span class="material-symbols-outlined" style="font-size:14px;">cancel</span>
                    Rejetée
                </span>
            @endif
        </div>
    </div>

    <div style="padding:2rem 1.5rem;">
        
        @if($changeRequest->reason)
        <div style="margin-bottom:2rem;">
            <h3 style="font-size:0.875rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.05em; margin:0 0 0.5rem;">Motif de la demande</h3>
            <div style="background:var(--color-surface-container); padding:1rem; border-radius:0.5rem; font-size:0.9375rem; color:var(--color-on-surface);">
                {{ $changeRequest->reason }}
            </div>
        </div>
        @endif

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:2rem;">
            <div>
                <h3 style="font-size:0.875rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.05em; margin:0 0 0.5rem;">Ancienne Valeur</h3>
                <pre style="background:var(--color-surface-container); padding:1rem; border-radius:0.5rem; font-size:0.8125rem; overflow-x:auto; margin:0; border:1px solid #fee2e2;">{{ json_encode($changeRequest->old_value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
            <div>
                <h3 style="font-size:0.875rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.05em; margin:0 0 0.5rem;">Nouvelle Valeur Proposée</h3>
                <pre style="background:var(--color-surface-container); padding:1rem; border-radius:0.5rem; font-size:0.8125rem; overflow-x:auto; margin:0; border:1px solid #dcfce7;">{{ json_encode($changeRequest->new_value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
        
    </div>
    
    @if($changeRequest->status === 'pending')
    <div style="padding:1.5rem; background:var(--color-surface-container-low); border-top:1px solid var(--color-surface-container); display:flex; justify-content:flex-end; gap:1rem;">
        
        <form action="{{ route('admin.change-requests.update', $changeRequest) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="status" value="rejected">
            <button type="submit"
               style="height:40px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.25rem; border-radius:0.75rem;
                      background:transparent; color:#b91c1c; border:1px solid #fee2e2; cursor:pointer;
                      font-size:0.875rem; font-weight:700; transition:all 0.15s;"
               onmouseover="this.style.background='#fee2e2'"
               onmouseout="this.style.background='transparent'">
                <span class="material-symbols-outlined" style="font-size:18px;">close</span>
                Rejeter
            </button>
        </form>

        <form action="{{ route('admin.change-requests.update', $changeRequest) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="status" value="approved">
            <button type="submit"
               style="height:40px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.25rem; border-radius:0.75rem;
                      background:#15803d; color:white; border:none; cursor:pointer;
                      font-size:0.875rem; font-weight:700; transition:all 0.15s; box-shadow:0 3px 10px rgba(21,128,61,0.3);"
               onmouseover="this.style.transform='translateY(-1px)'"
               onmouseout="this.style.transform=''">
                <span class="material-symbols-outlined" style="font-size:18px;">check</span>
                Approuver
            </button>
        </form>
    </div>
    @else
    <div style="padding:1rem 1.5rem; background:var(--color-surface-container-low); border-top:1px solid var(--color-surface-container); font-size:0.8125rem; color:var(--color-on-surface-variant); text-align:center;">
        Demande traitée le {{ $changeRequest->processed_at->format('d/m/Y à H:i') }} par {{ $changeRequest->processor->full_name ?? 'Inconnu' }}.
    </div>
    @endif

</div>
@endsection
