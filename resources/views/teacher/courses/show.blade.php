@extends('layouts.role')
@section('title', 'Détails du Cours - Espace Enseignant')
@section('role_label', 'Enseignant')
@section('home_route', route('teacher.dashboard'))

@section('nav_links')
    @include('teacher.partials.nav')
@endsection

@section('content')
<div style="margin-bottom:2rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
    <div>
        <a href="{{ route('teacher.courses.index') }}" style="display:inline-flex; align-items:center; gap:0.25rem; font-size:0.875rem; color:var(--color-primary-container); text-decoration:none; font-weight:600; margin-bottom:0.5rem;">
            <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span> Retour aux cours
        </a>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            {{ $course->display_name }}
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Formation : <strong>{{ $course->formation->name ?? '—' }}</strong> • Session : <strong>{{ $course->academicSession->name ?? '—' }}</strong>
        </p>
    </div>
    <div style="display:flex; gap:0.75rem;">
        <a href="{{ route('teacher.attendance.index', ['course_id' => $course->id, 'history' => $course->statut === 'termine' ? 1 : null]) }}" style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem; background:var(--color-surface-container-high); color:var(--color-on-surface); border-radius:0.75rem; text-decoration:none; font-size:0.875rem; font-weight:700;">
            <span class="material-symbols-outlined" style="font-size:20px;">{{ $course->statut === 'termine' ? 'history' : 'fact_check' }}</span>
            {{ $course->statut === 'termine' ? 'Historique des présences' : "Faire l'appel" }}
        </a>
        <a href="{{ route('teacher.grades.index', ['course_id' => $course->id, 'history' => $course->statut === 'termine' ? 1 : null]) }}" style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem; background:var(--color-primary-container); color:var(--color-on-primary); border-radius:0.75rem; text-decoration:none; font-size:0.875rem; font-weight:700;">
            <span class="material-symbols-outlined" style="font-size:20px;">{{ $course->statut === 'termine' ? 'history' : 'grading' }}</span>
            {{ $course->statut === 'termine' ? 'Historique des notes' : 'Saisir les notes' }}
        </a>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 2fr; gap:1.5rem; align-items:start;">
    {{-- Informations du cours --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1.25rem;">Informations Générales</h2>
        
        <div style="display:flex; flex-direction:column; gap:1rem;">
            <div>
                <div style="font-size:0.75rem; color:var(--color-on-surface-variant); font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.25rem;">Formation</div>
                <div style="font-weight:600; color:var(--color-on-surface);">{{ $course->formation->name ?? 'Non spécifiée' }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem; color:var(--color-on-surface-variant); font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.25rem;">Module associé</div>
                <div style="font-weight:600; color:var(--color-on-surface);">
                    {{ $course->module->name ?? 'Non spécifié' }}
                    <span style="font-size:0.75rem; color:var(--color-on-surface-variant); font-weight:normal;">({{ $course->module->code ?? 'MOD' }})</span>
                </div>
            </div>
            <div>
                <div style="font-size:0.75rem; color:var(--color-on-surface-variant); font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.25rem;">Crédits / Coefficient</div>
                <div style="font-weight:700; color:var(--color-on-surface);">
                    {{ $course->module->credits ?? 1 }}
                </div>
            </div>
            <div>
                <div style="font-size:0.75rem; color:var(--color-on-surface-variant); font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.25rem;">Session Académique</div>
                <div style="font-weight:500; color:var(--color-on-surface);">{{ $course->academicSession->name ?? '—' }}</div>
            </div>
        </div>
    </div>

    {{-- Liste réelle des étudiants inscrits (Feuille d'émargement / promotion) --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1.25rem; display:flex; align-items:center; justify-content:space-between;">
            <span>Étudiants Inscrits au Cours</span>
            <span style="font-size:0.75rem; font-weight:700; padding:0.25rem 0.625rem; background:var(--color-surface-container-high); border-radius:9999px; color:var(--color-on-surface);">
                {{ $enrolledStudents->count() }} étudiant(s)
            </span>
        </h2>
        
        @if($enrolledStudents->isEmpty())
        <div style="padding:2.5rem 1.5rem; text-align:center; background:var(--color-surface-container-low); border-radius:0.75rem;">
            <span class="material-symbols-outlined" style="font-size:48px; color:var(--color-on-surface-variant); opacity:0.35; margin-bottom:0.75rem; display:block;">groups</span>
            <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
                Aucun étudiant inscrit trouvé pour la formation <strong>{{ $course->formation->name ?? '' }}</strong> sur cette session.
            </p>
            @if($hasEnrollmentsInOtherSessions)
                <p role="status" style="font-size:0.8125rem; color:#92400e; margin:0.75rem 0 0;">
                    Des inscriptions existent pour cette formation dans une autre session. Demandez à l’administration de vérifier la session du cours et des inscriptions.
                </p>
            @endif
        </div>
        @else
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
                <thead>
                    <tr style="border-bottom:1px solid var(--color-surface-container);">
                        <th style="text-align:left; padding:0.75rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Étudiant</th>
                        <th style="text-align:left; padding:0.75rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Matricule</th>
                        <th style="text-align:left; padding:0.75rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Email</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($enrolledStudents as $sp)
                    <tr style="border-bottom:1px solid var(--color-surface-container);">
                        <td style="padding:0.75rem 1rem;">
                            <div style="display:flex; align-items:center; gap:0.625rem;">
                                <div style="width:32px; height:32px; border-radius:50%; background:var(--color-surface-container-high); color:var(--color-primary-container); font-size:0.75rem; font-weight:800; display:flex; align-items:center; justify-content:center;">
                                    {{ $sp->user->initials ?? 'ET' }}
                                </div>
                                <span style="font-weight:600; color:var(--color-on-surface);">{{ $sp->user->full_name }}</span>
                            </div>
                        </td>
                        <td style="padding:0.75rem 1rem; color:var(--color-on-surface-variant); font-size:0.8125rem;">
                            {{ $sp->user->matricule ?? '—' }}
                        </td>
                        <td style="padding:0.75rem 1rem; color:var(--color-on-surface-variant); font-size:0.8125rem;">
                            {{ $sp->user->email }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<section style="margin-top:1.5rem; background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
        <span class="material-symbols-outlined" style="color:var(--color-primary-container);">folder_open</span>
        Documents du cours ({{ $course->documents->count() }})
    </h2>

    @if(session('success'))
        <div role="status" style="margin-bottom:1rem; padding:0.75rem 1rem; border-radius:0.5rem; background:#ecfdf5; color:#047857; font-size:0.875rem;">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('teacher.courses.documents.store', $course) }}" method="POST" enctype="multipart/form-data"
          style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1rem; align-items:end; padding-bottom:1.25rem; border-bottom:1px solid var(--color-surface-container);">
        @csrf
        <div>
            <label for="document-title" style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.375rem;">Titre du document</label>
            <input id="document-title" type="text" name="title" value="{{ old('title') }}" maxlength="255" required
                   placeholder="Ex. Support du chapitre 1"
                   style="width:100%; box-sizing:border-box; height:42px; padding:0 0.875rem; border:1px solid var(--color-outline-variant); border-radius:0.5rem; background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
            @error('title') <span style="display:block; margin-top:0.25rem; color:#dc2626; font-size:0.75rem;">{{ $message }}</span> @enderror
        </div>
        <div>
            <label for="course-document" style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.375rem;">Fichier (20 Mo maximum)</label>
            <input id="course-document" type="file" name="document" required accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.png,.zip"
                   style="width:100%; box-sizing:border-box; min-height:42px; padding:0.5rem; border:1px solid var(--color-outline-variant); border-radius:0.5rem; background:var(--color-surface-container-low); font-size:0.8125rem; color:var(--color-on-surface);">
            @error('document') <span style="display:block; margin-top:0.25rem; color:#dc2626; font-size:0.75rem;">{{ $message }}</span> @enderror
        </div>
        <button type="submit" style="height:42px; display:inline-flex; align-items:center; justify-content:center; gap:0.5rem; padding:0 1.25rem; border:0; border-radius:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.875rem; font-weight:700; cursor:pointer;">
            <span class="material-symbols-outlined" style="font-size:19px;">upload_file</span>
            Ajouter le document
        </button>
    </form>

    @if($course->documents->isEmpty())
        <p style="margin:1rem 0 0; color:var(--color-on-surface-variant); font-size:0.875rem;">Aucun document n’a encore été ajouté à ce cours.</p>
    @else
        <div style="display:flex; flex-direction:column; gap:0.625rem; margin-top:1rem;">
            @foreach($course->documents as $document)
                <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem; padding:0.75rem 1rem; border:1px solid var(--color-outline-variant); border-radius:0.625rem; background:var(--color-surface-container-low);">
                    <div style="display:flex; align-items:center; gap:0.75rem; min-width:0;">
                        <span class="material-symbols-outlined" style="color:var(--color-primary-container);">description</span>
                        <div style="min-width:0;">
                            <div style="font-size:0.875rem; font-weight:600; color:var(--color-on-surface);">{{ $document->title }}</div>
                            <div style="font-size:0.75rem; color:var(--color-on-surface-variant); overflow-wrap:anywhere;">{{ $document->original_name }} · {{ round(($document->file_size ?? 0) / 1024, 1) }} Ko</div>
                        </div>
                    </div>
                    <a href="{{ Storage::url($document->file_path) }}" target="_blank" rel="noopener" download
                       style="height:34px; display:inline-flex; align-items:center; gap:0.375rem; padding:0 0.875rem; border-radius:0.375rem; background:var(--color-surface-container-high); color:var(--color-on-surface); text-decoration:none; font-size:0.8125rem; font-weight:600;">
                        <span class="material-symbols-outlined" style="font-size:17px;">download</span>
                        Télécharger
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</section>
@endsection
