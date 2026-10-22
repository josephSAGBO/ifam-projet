@extends('layouts.admin')
@section('title', 'Documents')
@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Documents</h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">Gestion centralisée des fichiers et pièces jointes.</p>
    </div>
    <a href="{{ route('admin.documents.create') }}"
       style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem;
              background:var(--color-primary-container); color:var(--color-on-primary); border-radius:0.75rem;
              text-decoration:none; font-size:0.875rem; font-weight:700; box-shadow:0 3px 10px rgba(228,0,102,0.3);">
        <span class="material-symbols-outlined" style="font-size:20px;">upload_file</span> Ajouter un document
    </a>
</div>

{{-- Filters --}}
<div style="background:var(--color-surface-container-low); padding:1rem 1.5rem; border-radius:1rem; margin-bottom:1.5rem;">
    <form action="{{ route('admin.documents.index') }}" method="GET" style="display:flex; gap:1rem; align-items:flex-end; flex-wrap:wrap;">
        @includeWhen($sites->isNotEmpty(), 'admin.partials.site-filter')
        <div>
            <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Type</label>
            <select name="type" onchange="this.form.submit()" style="height:38px; padding:0 1rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); font-size:0.875rem;">
                <option value="">Tous les types</option>
                @foreach($typeLabels as $key => $label)
                    <option value="{{ $key }}" {{ request('type')==$key?'selected':'' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Propriétaire</label>
            <select name="owner_type" onchange="this.form.submit()" style="height:38px; padding:0 1rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); font-size:0.875rem;">
                <option value="">Tous</option>
                <option value="App\Models\StudentProfile" {{ request('owner_type')=='App\Models\StudentProfile'?'selected':'' }}>Étudiants</option>
                <option value="App\Models\TeacherProfile" {{ request('owner_type')=='App\Models\TeacherProfile'?'selected':'' }}>Enseignants</option>
            </select>
        </div>
        <div>
            <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Vérification</label>
            <select name="verified" onchange="this.form.submit()" style="height:38px; padding:0 1rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); font-size:0.875rem;">
                <option value="">Tous</option>
                <option value="1" {{ request('verified')=='1'?'selected':'' }}>Vérifiés</option>
                <option value="0" {{ request('verified')=='0'?'selected':'' }}>Non vérifiés</option>
            </select>
        </div>
        @if(request()->hasAny(['type','owner_type','verified']))
        <a href="{{ route('admin.documents.index') }}" style="height:38px; display:inline-flex; align-items:center; color:var(--color-primary); text-decoration:none; font-size:0.875rem; font-weight:600;">Réinitialiser</a>
        @endif
    </form>
</div>

@php
$iconMap = [
    'identity'   => 'badge',
    'diploma'    => 'school',
    'transcript' => 'grading',
    'enrollment' => 'assignment_turned_in',
    'medical'    => 'local_hospital',
    'contract'   => 'description',
    'invoice'    => 'receipt',
    'other'      => 'attach_file',
];
@endphp

<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden;">
    @if($documents->isEmpty())
    <div style="padding:4rem 2rem; text-align:center; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined" style="font-size:48px; opacity:0.25; display:block; margin-bottom:1rem;">folder_open</span>
        <p style="margin:0;">Aucun document trouvé.</p>
    </div>
    @else
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Document</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Propriétaire</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Type</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Taille</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Statut</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Ajouté le</th>
                    <th style="padding:0.875rem 1rem;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($documents as $doc)
                <tr style="border-bottom:1px solid var(--color-surface-container); transition:background 0.15s;"
                    onmouseover="this.style.background='var(--color-surface-container-low)'"
                    onmouseout="this.style.background=''">
                    <td style="padding:0.875rem 1rem;">
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            <div style="width:36px; height:36px; border-radius:0.625rem; background:var(--color-surface-container); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <span class="material-symbols-outlined" style="font-size:18px; color:var(--color-primary);">{{ $iconMap[$doc->type] ?? 'attach_file' }}</span>
                            </div>
                            <div>
                                <div style="font-weight:600; color:var(--color-on-surface);">{{ $doc->title }}</div>
                                <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">{{ $doc->original_name }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">
                        @if($doc->documentable)
                            @if($doc->documentable instanceof \App\Models\StudentProfile)
                                <span class="material-symbols-outlined" style="font-size:14px; vertical-align:middle; color:#6366f1;">person</span>
                                {{ $doc->documentable->user->last_name ?? '' }} {{ $doc->documentable->user->first_name ?? '' }}
                            @elseif($doc->documentable instanceof \App\Models\TeacherProfile)
                                <span class="material-symbols-outlined" style="font-size:14px; vertical-align:middle; color:#10b981;">school</span>
                                {{ $doc->documentable->user->last_name ?? '' }} {{ $doc->documentable->user->first_name ?? '' }}
                            @else
                                N/A
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td style="padding:0.875rem 1rem;">
                        <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:var(--color-surface-container); color:var(--color-on-surface-variant);">
                            {{ $typeLabels[$doc->type] ?? $doc->type }}
                        </span>
                    </td>
                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">{{ $doc->file_size_human }}</td>
                    <td style="padding:0.875rem 1rem;">
                        @if($doc->is_verified)
                        <span style="display:inline-flex; align-items:center; gap:0.25rem; padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#ecfdf5; color:#065f46;">
                            <span class="material-symbols-outlined" style="font-size:13px;">verified</span> Vérifié
                        </span>
                        @else
                        <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#fef2f2; color:#991b1b;">
                            En attente
                        </span>
                        @endif
                    </td>
                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">{{ $doc->created_at->format('d/m/Y') }}</td>
                    <td style="padding:0.875rem 1rem;">
                        <div style="display:flex; align-items:center; gap:0.375rem; justify-content:flex-end;">
                            <a href="{{ route('admin.documents.show', $doc) }}"
                               style="width:34px; height:34px; border-radius:0.5rem; display:flex; align-items:center; justify-content:center; background:var(--color-surface-container); color:var(--color-on-surface-variant); text-decoration:none;" title="Voir">
                                <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                            </a>
                            <a href="{{ $doc->file_url }}" target="_blank"
                               style="width:34px; height:34px; border-radius:0.5rem; display:flex; align-items:center; justify-content:center; background:var(--color-surface-container); color:var(--color-on-surface-variant); text-decoration:none;" title="Télécharger">
                                <span class="material-symbols-outlined" style="font-size:18px;">download</span>
                            </a>
                            <form method="POST" action="{{ route('admin.documents.destroy', $doc) }}" onsubmit="return confirm('Supprimer ce document ?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="width:34px; height:34px; border-radius:0.5rem; border:none; cursor:pointer; background:var(--color-surface-container); color:#dc2626;" title="Supprimer">
                                    <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($documents->hasPages())
    <div style="padding:1rem 1.25rem; border-top:1px solid var(--color-surface-container); display:flex; align-items:center; justify-content:space-between;">
        <span style="font-size:0.8125rem; color:var(--color-on-surface-variant);">Affichage {{ $documents->firstItem() }}–{{ $documents->lastItem() }} sur {{ $documents->total() }}</span>
        {{ $documents->links('vendor.pagination.simple-default') }}
    </div>
    @endif
    @endif
</div>
@endsection
