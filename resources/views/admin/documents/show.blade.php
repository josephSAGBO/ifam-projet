@extends('layouts.admin')
@section('title', 'Document : ' . $document->title)
@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.documents.index') }}" style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container); display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">{{ $document->title }}</h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">{{ $document->type_label }}</p>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 340px; gap:1.5rem; max-width:1100px;">
    
    {{-- Prévisualisation --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden;">
        @php $isImage = str_starts_with($document->mime_type ?? '', 'image/'); @endphp
        @if($isImage)
        <img src="{{ $document->file_url }}" alt="{{ $document->title }}"
             style="width:100%; max-height:600px; object-fit:contain; background:#f9fafb; display:block;">
        @elseif($document->mime_type === 'application/pdf')
        <iframe src="{{ $document->file_url }}" style="width:100%; height:600px; border:none;"></iframe>
        @else
        <div style="padding:5rem 2rem; text-align:center; color:var(--color-on-surface-variant);">
            <span class="material-symbols-outlined" style="font-size:56px; display:block; margin-bottom:1rem; opacity:0.3;">description</span>
            <p style="margin:0 0 1.5rem; font-size:0.9375rem;">Aperçu non disponible pour ce type de fichier.</p>
            <a href="{{ $document->file_url }}" target="_blank"
               style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem; border-radius:0.75rem;
                      background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none; font-weight:700;">
                <span class="material-symbols-outlined" style="font-size:18px;">download</span> Télécharger
            </a>
        </div>
        @endif
    </div>

    {{-- Infos --}}
    <div style="display:flex; flex-direction:column; gap:1.25rem;">

        {{-- Metadata card --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:1.5rem;">
            <h2 style="font-size:0.875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant); margin:0 0 1.25rem;">Informations</h2>

            <div style="display:grid; gap:0.875rem;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; padding-bottom:0.875rem; border-bottom:1px solid var(--color-surface-container);">
                    <span style="font-size:0.8125rem; color:var(--color-on-surface-variant);">Nom du fichier</span>
                    <span style="font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); text-align:right; max-width:180px; word-break:break-all;">{{ $document->original_name }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:0.875rem; border-bottom:1px solid var(--color-surface-container);">
                    <span style="font-size:0.8125rem; color:var(--color-on-surface-variant);">Taille</span>
                    <span style="font-size:0.8125rem; font-weight:600; color:var(--color-on-surface);">{{ $document->file_size_human }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:0.875rem; border-bottom:1px solid var(--color-surface-container);">
                    <span style="font-size:0.8125rem; color:var(--color-on-surface-variant);">Propriétaire</span>
                    <span style="font-size:0.8125rem; font-weight:600; color:var(--color-on-surface);">
                        @if($document->documentable)
                            {{ $document->documentable->user->last_name ?? '' }} {{ $document->documentable->user->first_name ?? '' }}
                        @else —
                        @endif
                    </span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:0.875rem; border-bottom:1px solid var(--color-surface-container);">
                    <span style="font-size:0.8125rem; color:var(--color-on-surface-variant);">Uploadé par</span>
                    <span style="font-size:0.8125rem; font-weight:600; color:var(--color-on-surface);">{{ $document->uploadedBy->last_name ?? 'N/A' }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:0.8125rem; color:var(--color-on-surface-variant);">Date d'ajout</span>
                    <span style="font-size:0.8125rem; font-weight:600; color:var(--color-on-surface);">{{ $document->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        {{-- Verification card --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:1.5rem;">
            <div style="display:flex; align-items:center; gap:0.875rem; margin-bottom:1rem;">
                @if($document->is_verified)
                <div style="width:40px; height:40px; border-radius:0.75rem; background:#ecfdf5; display:flex; align-items:center; justify-content:center;">
                    <span class="material-symbols-outlined" style="color:#065f46; font-size:22px;">verified</span>
                </div>
                <div>
                    <div style="font-weight:700; color:#065f46; font-size:0.9375rem;">Document vérifié</div>
                    <div style="font-size:0.75rem; color:#6b7280;">Ce document a été validé par l'administration</div>
                </div>
                @else
                <div style="width:40px; height:40px; border-radius:0.75rem; background:#fef2f2; display:flex; align-items:center; justify-content:center;">
                    <span class="material-symbols-outlined" style="color:#dc2626; font-size:22px;">pending</span>
                </div>
                <div>
                    <div style="font-weight:700; color:#dc2626; font-size:0.9375rem;">En attente de vérification</div>
                    <div style="font-size:0.75rem; color:#6b7280;">Non encore validé</div>
                </div>
                @endif
            </div>
            <form method="POST" action="{{ route('admin.documents.toggleVerified', $document) }}">
                @csrf @method('PATCH')
                <button type="submit" style="width:100%; height:40px; border-radius:0.75rem; border:none; cursor:pointer; font-weight:700; font-size:0.875rem;
                    {{ $document->is_verified ? 'background:#fef2f2; color:#dc2626;' : 'background:#ecfdf5; color:#065f46;' }}">
                    {{ $document->is_verified ? 'Retirer la vérification' : '✓ Marquer comme vérifié' }}
                </button>
            </form>
        </div>

        {{-- Notes --}}
        @if($document->notes)
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:1.5rem;">
            <h2 style="font-size:0.875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant); margin:0 0 0.75rem;">Notes</h2>
            <p style="font-size:0.875rem; color:var(--color-on-surface); margin:0; line-height:1.6;">{{ $document->notes }}</p>
        </div>
        @endif

        {{-- Actions --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
            <a href="{{ $document->file_url }}" target="_blank"
               style="height:42px; display:inline-flex; align-items:center; justify-content:center; gap:0.5rem; border-radius:0.75rem;
                      background:var(--color-surface-container); color:var(--color-on-surface); text-decoration:none; font-weight:600; font-size:0.875rem;">
                <span class="material-symbols-outlined" style="font-size:18px;">download</span> Télécharger
            </a>
            <form method="POST" action="{{ route('admin.documents.destroy', $document) }}" onsubmit="return confirm('Supprimer définitivement ?')">
                @csrf @method('DELETE')
                <button type="submit" style="width:100%; height:42px; border-radius:0.75rem; border:none; cursor:pointer; font-weight:600; font-size:0.875rem; background:#fef2f2; color:#dc2626;">
                    <span class="material-symbols-outlined" style="font-size:18px; vertical-align:middle;">delete</span> Supprimer
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
