@extends('layouts.admin')

@section('title', 'Détails du Cours')

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div style="display:flex; align-items:center; gap:1rem;">
        <a href="{{ route('admin.courses.index') }}"
           style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
                  display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
                  transition:all 0.15s;"
           onmouseover="this.style.background='var(--color-surface-container-high)'"
           onmouseout="this.style.background='var(--color-surface-container)'">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem; display:flex; align-items:center; gap:0.5rem;">
                <span style="width:14px; height:14px; border-radius:4px; background:{{ $course->color ?? '#e40066' }};"></span>
                {{ $course->display_name }}
            </h1>
            <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
                {{ $course->formation->name ?? 'Formation inconnue' }} • Session : {{ $course->academicSession->name ?? 'N/A' }}
            </p>
        </div>
    </div>

    <div style="display:flex; gap:0.75rem;">
        <a href="{{ route('admin.courses.edit', $course) }}"
           style="height:40px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.25rem; border-radius:0.75rem;
                  background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none;
                  font-size:0.875rem; font-weight:700; box-shadow:0 3px 10px rgba(228,0,102,0.3);">
            <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
            Modifier
        </a>
    </div>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 22rem), 1fr)); gap:1.5rem;">

    {{-- Colonne 1 : Détails du cours --}}
    <div style="display:flex; flex-direction:column; gap:1.5rem;">
        
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1.25rem; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.75rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="color:var(--color-primary);">info</span>
                Fiche du Cours
            </h2>

            <div style="display:flex; flex-direction:column; gap:1rem; font-size:0.875rem;">
                <div>
                    <span style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em;">Module</span>
                    <strong style="color:var(--color-on-surface);">{{ $course->module->code ?? 'N/A' }} — {{ $course->module->name ?? 'N/A' }}</strong>
                </div>

                <div>
                    <span style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em;">Enseignant</span>
                    <strong style="color:var(--color-on-surface);">
                        @if(($course->enseignant && $course->enseignant->user) || ($course->teacherProfile && $course->teacherProfile->user))
                            {{ $course->enseignant->user->full_name ?? $course->teacherProfile->user->full_name }}
                        @else
                            <span style="color:var(--color-on-surface-variant); font-style:italic;">Non assigné</span>
                        @endif
                    </strong>
                </div>

                <div>
                    <span style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em;">Date & Horaires</span>
                    <strong style="color:var(--color-on-surface);">
                        @if($course->date)
                            {{ $course->date->format('d/m/Y') }}
                            @if($course->heure_debut && $course->heure_fin)
                                ({{ $course->heure_debut }} - {{ $course->heure_fin }})
                            @endif
                        @else
                            <span style="color:var(--color-on-surface-variant); font-style:italic;">Non planifié</span>
                        @endif
                    </strong>
                </div>

                <div>
                    <span style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em;">Salle</span>
                    <strong style="color:var(--color-on-surface);">
                        {{ $course->salle->name ?? 'Non spécifiée' }}
                    </strong>
                </div>

                <div>
                    <span style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em;">Effectif concerné</span>
                    <strong style="color:var(--color-primary);">{{ $students->count() }} étudiant(s) inscrit(s)</strong>
                </div>
            </div>
        </div>

        {{-- Formulaire Ajout de Document --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
            <h2 style="font-size:1.0625rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="color:var(--color-primary);">upload_file</span>
                Ajouter un document
            </h2>

            <form action="{{ route('admin.courses.documents.store', $course) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom:1rem;">
                    <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.375rem;">Titre du document</label>
                    <input type="text" name="title" required placeholder="Ex: Support de cours Chapitre 1"
                           style="width:100%; box-sizing:border-box; height:38px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.875rem;">
                </div>

                <div style="margin-bottom:1.25rem;">
                    <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.375rem;">Fichier (PDF, Doc, Image... max 20Mo)</label>
                    <input type="file" name="document" required
                           style="width:100%; box-sizing:border-box; font-size:0.8125rem; color:var(--color-on-surface);">
                </div>

                <button type="submit"
                        style="width:100%; height:38px; border-radius:0.5rem; border:none; cursor:pointer; background:var(--color-primary-container); color:var(--color-on-primary); font-weight:700; font-size:0.875rem;">
                    Uploader le document
                </button>
            </form>
        </div>

    </div>

    {{-- Colonne 2 : Étudiants concernés & Documents attachés --}}
    <div style="display:flex; flex-direction:column; gap:1.5rem;">

        {{-- Section Documents attachés --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.75rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="color:var(--color-primary);">folder</span>
                Documents du cours ({{ $course->documents->count() }})
            </h2>

            @if($course->documents->isEmpty())
                <p style="font-size:0.875rem; color:var(--color-on-surface-variant); font-style:italic; margin:0;">
                    Aucun document n'a été rattaché à ce cours pour le moment.
                </p>
            @else
                <div style="display:flex; flex-direction:column; gap:0.75rem;">
                    @foreach($course->documents as $doc)
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:0.75rem 1rem; border-radius:0.625rem; background:var(--color-surface-container-low); border:1px solid var(--color-outline-variant);">
                            <div style="display:flex; align-items:center; gap:0.75rem;">
                                <span class="material-symbols-outlined" style="color:var(--color-primary); font-size:24px;">description</span>
                                <div>
                                    <div style="font-weight:600; font-size:0.875rem; color:var(--color-on-surface);">{{ $doc->title }}</div>
                                    <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                                        {{ $doc->original_name }} • {{ round(($doc->file_size ?? 0) / 1024, 1) }} Ko
                                    </div>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:0.5rem;">
                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank" download
                                   style="height:32px; padding:0 0.75rem; border-radius:0.375rem; background:var(--color-surface-container-high); color:var(--color-on-surface); text-decoration:none; font-size:0.8125rem; font-weight:600; display:inline-flex; align-items:center; gap:0.25rem;">
                                    <span class="material-symbols-outlined" style="font-size:16px;">download</span>
                                    Télécharger
                                </a>
                                <form action="{{ route('admin.courses.documents.destroy', [$course, $doc]) }}" method="POST" onsubmit="return confirm('Supprimer ce document ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" style="width:32px; height:32px; border-radius:0.375rem; border:none; cursor:pointer; background:#fef2f2; color:#dc2626; display:flex; align-items:center; justify-content:center;">
                                        <span class="material-symbols-outlined" style="font-size:16px;">delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Section Étudiants concernés --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.75rem; display:flex; align-items:center; justify-content:space-between;">
                <span style="display:flex; align-items:center; gap:0.5rem;">
                    <span class="material-symbols-outlined" style="color:var(--color-primary);">groups</span>
                    Étudiants concernés (Calculés automatiquement)
                </span>
                <span style="font-size:0.8125rem; font-weight:600; color:var(--color-on-surface-variant);">
                    Total: {{ $students->count() }}
                </span>
            </h2>

            @if($students->isEmpty())
                <p style="font-size:0.875rem; color:var(--color-on-surface-variant); font-style:italic; margin:0;">
                    Aucun étudiant n'a d'inscription active dans la session et la formation de ce cours.
                </p>
            @else
                <div style="max-height:350px; overflow-y:auto; border-radius:0.5rem; border:1px solid var(--color-outline-variant);">
                    <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
                        <thead>
                            <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                                <th style="text-align:left; padding:0.625rem 0.875rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase;">Étudiant</th>
                                <th style="text-align:left; padding:0.625rem 0.875rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase;">Matricule</th>
                                <th style="text-align:left; padding:0.625rem 0.875rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase;">Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $sp)
                                <tr style="border-bottom:1px solid var(--color-surface-container);">
                                    <td style="padding:0.625rem 0.875rem; font-weight:600; color:var(--color-on-surface);">
                                        {{ $sp->user->full_name ?? '—' }}
                                    </td>
                                    <td style="padding:0.625rem 0.875rem; color:var(--color-on-surface-variant); font-family:monospace;">
                                        {{ $sp->user->matricule ?? '—' }}
                                    </td>
                                    <td style="padding:0.625rem 0.875rem; color:var(--color-on-surface-variant);">
                                        {{ $sp->user->email ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

</div>

@endsection
