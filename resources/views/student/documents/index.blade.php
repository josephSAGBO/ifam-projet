@extends('layouts.role')
@section('title', 'Mes Documents - Espace Étudiant')
@section('role_label', 'Étudiant')
@section('home_route', route('student.dashboard'))

@section('nav_links')
    @include('student.partials.nav')
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Mes Documents
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Gérez vos documents administratifs soumis à l'IFAM
        </p>
    </div>
    <button onclick="document.getElementById('uploadModal').style.display='flex'"
            style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem;
                   background:var(--color-primary-container); color:var(--color-on-primary); border-radius:0.75rem;
                   border:none; cursor:pointer; font-size:0.875rem; font-weight:700;
                   box-shadow:0 3px 10px rgba(228,0,102,0.3); transition:all 0.2s;"
            onmouseover="this.style.transform='translateY(-1px)'"
            onmouseout="this.style.transform=''">
        <span class="material-symbols-outlined" style="font-size:20px;">upload_file</span>
        Déposer un document
    </button>
</div>

{{-- Alertes --}}
@if(session('success'))
<div style="background:#ecfdf5; color:#065f46; border-radius:0.75rem; padding:1rem 1.25rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.75rem; font-size:0.875rem; font-weight:600;">
    <span class="material-symbols-outlined" style="font-size:20px;">check_circle</span>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="background:#fff1f2; color:#9f1239; border-radius:0.75rem; padding:1rem 1.25rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.75rem; font-size:0.875rem; font-weight:600;">
    <span class="material-symbols-outlined" style="font-size:20px;">error</span>
    {{ session('error') }}
</div>
@endif

<section aria-labelledby="course-documents-title" style="margin-bottom:2rem;">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:0.875rem;">
        <div>
            <h2 id="course-documents-title" style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Documents des cours</h2>
            <p style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin:0;">Supports partagés par vos enseignants pour vos cours.</p>
        </div>
    </div>

    @if($courseDocuments->isEmpty())
        <p style="padding:1rem 1.125rem; border:1px solid var(--color-outline-variant); border-radius:0.625rem; color:var(--color-on-surface-variant); font-size:0.875rem; margin:0;">
            Aucun document de cours n’est disponible pour vos inscriptions actives ou terminées.
        </p>
    @else
        <div style="display:flex; flex-direction:column; gap:0.625rem;">
            @foreach($courseDocuments as $courseDocument)
                <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem; padding:0.875rem 1rem; border:1px solid var(--color-outline-variant); border-radius:0.625rem; background:var(--color-surface-container-lowest);">
                    <div style="display:flex; align-items:center; gap:0.75rem; min-width:0;">
                        <span class="material-symbols-outlined" aria-hidden="true" style="color:var(--color-primary-container); font-size:22px;">description</span>
                        <div style="min-width:0;">
                            <div style="font-size:0.875rem; font-weight:700; color:var(--color-on-surface);">{{ $courseDocument->title }}</div>
                            <div style="font-size:0.75rem; color:var(--color-on-surface-variant); overflow-wrap:anywhere;">
                                {{ $courseDocument->course->display_name }}
                                · {{ $courseDocument->course->formation->name ?? 'Formation' }}
                                · {{ $courseDocument->course->academicSession->name ?? 'Session' }}
                                · {{ $courseDocument->original_name }}
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('student.course-documents.download', $courseDocument) }}"
                       style="height:36px; display:inline-flex; align-items:center; justify-content:center; gap:0.375rem; padding:0 0.875rem; border-radius:0.5rem; background:var(--color-surface-container-high); color:var(--color-on-surface); text-decoration:none; font-size:0.8125rem; font-weight:700; white-space:nowrap;">
                        <span class="material-symbols-outlined" aria-hidden="true" style="font-size:18px;">download</span>
                        Télécharger
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</section>

@if($documents->isEmpty())
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:3.5rem 1.5rem; text-align:center; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    <span class="material-symbols-outlined" style="font-size:52px; color:var(--color-on-surface-variant); opacity:0.35; margin-bottom:1rem; display:block;">folder_open</span>
    <h3 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem;">Aucun document administratif soumis</h3>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0 auto; max-width:400px;">
        Déposez vos pièces justificatives (pièce d'identité, diplôme, etc.) pour compléter votre dossier étudiant.
    </p>
</div>
@else
<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:1.25rem;">
    @foreach($documents as $doc)
    @php
        $typeLabels = \App\Models\Document::typeLabels();
        $icon = match($doc->type) {
            'identity'   => 'badge',
            'diploma'    => 'workspace_premium',
            'transcript' => 'grading',
            'enrollment' => 'school',
            'medical'    => 'medical_information',
            'contract'   => 'description',
            'invoice'    => 'receipt',
            default      => 'insert_drive_file',
        };
    @endphp
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); border:1px solid var(--color-surface-container); overflow:hidden; display:flex; flex-direction:column;">
        <div style="padding:1.25rem 1.5rem; display:flex; align-items:flex-start; gap:1rem; flex:1;">
            <div style="width:44px; height:44px; border-radius:0.75rem; background:var(--color-surface-container-high); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <span class="material-symbols-outlined" style="color:var(--color-on-surface-variant); font-size:22px;">{{ $icon }}</span>
            </div>
            <div style="flex:1; min-width:0;">
                <div style="font-weight:700; color:var(--color-on-surface); margin-bottom:0.25rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                    {{ $doc->title }}
                </div>
                <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                    {{ $typeLabels[$doc->type] ?? $doc->type }}
                    · {{ $doc->file_size_human }}
                    · {{ $doc->created_at->format('d/m/Y') }}
                </div>
            </div>
        </div>

        <div style="padding:0.75rem 1.5rem; border-top:1px solid var(--color-surface-container); display:flex; align-items:center; justify-content:space-between;">
            {{-- Statut vérification --}}
            @if($doc->is_verified)
            <span style="font-size:0.6875rem; font-weight:700; padding:0.25rem 0.625rem; border-radius:9999px; background:#ecfdf5; color:#065f46; display:inline-flex; align-items:center; gap:0.25rem;">
                <span style="width:6px; height:6px; border-radius:50%; background:#059669;"></span>
                Vérifié
            </span>
            @else
            <span style="font-size:0.6875rem; font-weight:700; padding:0.25rem 0.625rem; border-radius:9999px; background:#fffbeb; color:#92400e; display:inline-flex; align-items:center; gap:0.25rem;">
                <span style="width:6px; height:6px; border-radius:50%; background:#d97706;"></span>
                En attente de vérification
            </span>
            @endif

            <div style="display:flex; align-items:center; gap:0.5rem;">
                {{-- Télécharger --}}
                <a href="{{ Storage::url($doc->file_path) }}" target="_blank"
                   style="display:inline-flex; align-items:center; gap:0.25rem; padding:0.375rem 0.75rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-on-surface); text-decoration:none; font-size:0.75rem; font-weight:600;">
                    <span class="material-symbols-outlined" style="font-size:16px;">download</span>
                    Voir
                </a>
                {{-- Supprimer si non vérifié --}}
                @if(!$doc->is_verified)
                <form action="{{ route('student.documents.destroy', $doc) }}" method="POST"
                      onsubmit="return confirm('Supprimer ce document ?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            style="display:inline-flex; align-items:center; gap:0.25rem; padding:0.375rem 0.75rem; border-radius:0.5rem; background:#fff1f2; color:#9f1239; border:none; cursor:pointer; font-size:0.75rem; font-weight:600;">
                        <span class="material-symbols-outlined" style="font-size:16px;">delete</span>
                        Supprimer
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Modal upload --}}
<div id="uploadModal" style="display:none; position:fixed; inset:0; background:rgba(11,28,48,0.5); z-index:9999; align-items:center; justify-content:center; padding:1rem;"
     onclick="if(event.target===this) this.style.display='none'">
    <div style="background:var(--color-surface); border-radius:1.25rem; padding:2rem; width:100%; max-width:500px; box-shadow:0 20px 60px rgba(11,28,48,0.2);">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem;">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0;">Déposer un document</h2>
            <button onclick="document.getElementById('uploadModal').style.display='none'"
                    style="background:none; border:none; cursor:pointer; color:var(--color-on-surface-variant); display:flex;">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="{{ route('student.documents.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="display:flex; flex-direction:column; gap:1rem;">
                <div>
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Intitulé du document *</label>
                    <input type="text" name="title" required
                           style="width:100%; height:42px; padding:0 0.875rem; border-radius:0.625rem; border:1.5px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); font-size:0.875rem; color:var(--color-on-surface);"
                           placeholder="Ex : Carte nationale d'identité">
                </div>
                <div>
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Type de document *</label>
                    <select name="type" required
                            style="width:100%; height:42px; padding:0 0.875rem; border-radius:0.625rem; border:1.5px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); font-size:0.875rem; color:var(--color-on-surface);">
                        @foreach(\App\Models\Document::typeLabels() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Fichier (PDF, JPG, PNG — max 10 Mo) *</label>
                    <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png"
                           style="width:100%; padding:0.5rem; border-radius:0.625rem; border:1.5px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); font-size:0.875rem; color:var(--color-on-surface);">
                    @error('file')<p style="font-size:0.75rem; color:#e11d48; margin:0.375rem 0 0;">{{ $message }}</p>@enderror
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:0.5rem;">
                    <button type="button" onclick="document.getElementById('uploadModal').style.display='none'"
                            style="flex:1; padding:0.625rem; border-radius:0.625rem; border:1.5px solid var(--color-outline-variant); background:transparent; color:var(--color-on-surface); font-size:0.875rem; font-weight:600; cursor:pointer;">
                        Annuler
                    </button>
                    <button type="submit"
                            style="flex:1; padding:0.625rem; border-radius:0.625rem; border:none; background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.875rem; font-weight:700; cursor:pointer;">
                        Envoyer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
