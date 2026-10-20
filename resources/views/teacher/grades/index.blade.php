@extends('layouts.role')
@section('title', 'Évaluations — Espace Enseignant')
@section('role_label', 'Enseignant')
@section('home_route', route('teacher.dashboard'))

@section('nav_links')
    @include('teacher.partials.nav')
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            {{ $history ? 'Historique des Notes' : 'Saisie des Notes' }}
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            {{ $history ? 'Consultation en lecture seule des cours terminés et évaluations clôturées' : 'Sélectionnez un cours puis saisissez les notes ou créez un contrôle continu' }}
        </p>
    </div>
    <a href="{{ route('teacher.grades.index', $history ? [] : ['history' => 1]) }}"
       class="interactive-hover"
       style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.625rem 0.875rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); color:var(--color-on-surface); text-decoration:none; font-size:0.8125rem; font-weight:700;">
        <span class="material-symbols-outlined" style="font-size:18px;">{{ $history ? 'edit_note' : 'history' }}</span>
        {{ $history ? 'Retour à la saisie' : 'Historique' }}
    </a>
    @if($activeSession)
    <span style="font-size:0.8125rem; font-weight:700; padding:0.375rem 0.875rem; border-radius:9999px; background:var(--color-primary-container); color:var(--color-on-primary);">
        {{ $activeSession->name }}
    </span>
    @endif
</div>

@if(session('success'))
<div style="background:#ecfdf5; border:1px solid #6ee7b7; border-radius:0.75rem; padding:0.875rem 1.25rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.75rem; color:#065f46; font-size:0.875rem; font-weight:600;">
    <span class="material-symbols-outlined" style="font-size:20px;">check_circle</span>
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div style="background:#fef2f2; border:1px solid #fca5a5; border-radius:0.75rem; padding:0.875rem 1.25rem; margin-bottom:1.5rem; color:#991b1b; font-size:0.875rem;">
    <ul style="margin:0; padding-left:1.25rem;">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if($assignedEvaluations->isNotEmpty())
<section style="margin-bottom:1.5rem; background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    <h2 style="display:flex; align-items:center; gap:0.5rem; font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem;">
        <span class="material-symbols-outlined" style="color:var(--color-primary-container);">assignment</span>
        Devoirs qui vous sont attribués
    </h2>
    <div style="display:flex; flex-direction:column; gap:0.625rem;">
        @foreach($assignedEvaluations as $assignedEvaluation)
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem; padding:0.75rem 1rem; border:1px solid var(--color-outline-variant); border-radius:0.625rem;">
                <div>
                    <div style="font-size:0.875rem; font-weight:700; color:var(--color-on-surface);">{{ $assignedEvaluation->titre }}</div>
                    <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                        {{ $assignedEvaluation->module->name ?? 'Module' }} · {{ $assignedEvaluation->formation->name ?? 'Formation' }} · {{ $assignedEvaluation->academicSession->name ?? 'Session' }}
                        @if($assignedEvaluation->date) · {{ $assignedEvaluation->date->format('d/m/Y') }} @endif
                    </div>
                </div>
                <a href="{{ route('teacher.grades.assigned.show', $assignedEvaluation) }}" class="interactive-hover" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none; font-size:0.8125rem; font-weight:700;">
                    <span class="material-symbols-outlined" style="font-size:17px;">{{ $history ? 'visibility' : 'edit_document' }}</span>
                    {{ $history ? 'Consulter' : 'Saisir les notes' }} ({{ $assignedEvaluation->grades->whereNotNull('score')->count() }})
                </a>
            </div>
        @endforeach
    </div>
</section>
@endif

{{-- Sélecteur de cours --}}
<form method="GET" action="{{ route('teacher.grades.index') }}"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem 1.5rem; margin-bottom:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end;">
    @if($history)
        <input type="hidden" name="history" value="1">
    @endif
    <div style="flex:1;">
        <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); margin-bottom:0.375rem; text-transform:uppercase; letter-spacing:0.06em;">Cours</label>
        <select name="course_id"
                style="width:100%; height:42px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">
            <option value="">— Sélectionnez un cours —</option>
            @foreach($courses as $c)
                <option value="{{ $c->id }}" {{ (isset($course) && $course->id == $c->id) ? 'selected' : '' }}>
                    {{ $c->display_name }}
                    @if($c->date) · {{ \Carbon\Carbon::parse($c->date)->format('d/m/Y') }} @endif
                </option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="interactive-hover"
            style="height:42px; padding:0 1.5rem; border-radius:0.5rem; border:none; background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.875rem; font-weight:700; cursor:pointer;">
        Charger
    </button>
</form>

@if(!isset($course))
{{-- État vide --}}
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:4rem 1.5rem; text-align:center; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    <span class="material-symbols-outlined" style="font-size:56px; color:var(--color-on-surface-variant); opacity:0.3; display:block; margin-bottom:1rem;">grading</span>
    <h3 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem;">Aucun cours sélectionné</h3>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0; max-width:380px; margin-inline:auto;">
        {{ $history ? 'Sélectionnez un cours terminé pour consulter son historique de notes.' : 'Choisissez un cours pour afficher les évaluations ouvertes et saisir les notes des étudiants.' }}
    </p>
</div>

@else
{{-- En-tête cours sélectionné + bouton créer CC --}}
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem 1.5rem; margin-bottom:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
    <div>
        <div style="font-size:1.0625rem; font-weight:800; color:var(--color-on-surface);">{{ $course->display_name }}</div>
        <div style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin-top:0.25rem;">
            Module : {{ $course->module->name ?? '—' }}
            · {{ $students->count() }} étudiant(s)
        </div>
    </div>

    @if(! $history)
    {{-- Bouton créer un contrôle continu --}}
    <button type="button" onclick="document.getElementById('modal-create-cc').style.display='flex'"
            class="interactive-hover"
            style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem; border-radius:0.75rem; border:none; background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.875rem; font-weight:700; cursor:pointer;">
        <span class="material-symbols-outlined" style="font-size:20px;">add_circle</span>
        Créer un contrôle continu
    </button>
    @else
    <span style="padding:0.5rem 0.75rem; border-radius:9999px; background:#f3f4f6; color:#4b5563; font-size:0.75rem; font-weight:700;">
        Lecture seule — historique
    </span>
    @endif
</div>

@if(! $history)
{{-- Modal création CC --}}
<div id="modal-create-cc"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--color-surface); border-radius:1.25rem; padding:2rem; width:100%; max-width:460px; box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem;">
            <h2 style="font-size:1.125rem; font-weight:800; color:var(--color-on-surface); margin:0;">Nouveau contrôle continu</h2>
            <button type="button" onclick="document.getElementById('modal-create-cc').style.display='none'"
                    style="background:none; border:none; cursor:pointer; color:var(--color-on-surface-variant);">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" action="{{ route('teacher.grades.createEvaluation') }}">
            @csrf
            <input type="hidden" name="course_id" value="{{ $course->id }}">

            <div style="margin-bottom:1rem;">
                <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); margin-bottom:0.375rem; text-transform:uppercase; letter-spacing:0.06em;">Titre *</label>
                <input type="text" name="titre" required
                       placeholder="Ex: Interrogation n°1, CC Chapitre 3…"
                       style="width:100%; height:42px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); font-size:0.875rem; color:var(--color-on-surface); box-sizing:border-box;">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                <div>
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); margin-bottom:0.375rem; text-transform:uppercase; letter-spacing:0.06em;">Type *</label>
                    <select name="type"
                            style="width:100%; height:42px; padding:0 0.75rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); font-size:0.875rem; color:var(--color-on-surface);">
                        <option value="controle_continu">Contrôle Continu</option>
                        <option value="interrogation">Interrogation</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); margin-bottom:0.375rem; text-transform:uppercase; letter-spacing:0.06em;">Date *</label>
                    <input type="date" name="date" required
                           style="width:100%; height:42px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); font-size:0.875rem; color:var(--color-on-surface); box-sizing:border-box;">
                </div>
            </div>

            <div style="background:var(--color-surface-container-low); border-radius:0.625rem; padding:0.75rem 1rem; margin-bottom:1.5rem; font-size:0.8125rem; color:var(--color-on-surface-variant);">
                <span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle; margin-right:0.25rem;">info</span>
                Les notes sont toujours sur <strong>20</strong>. Les devoirs administratifs sont gérés par l'administration.
            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
                <button type="button" onclick="document.getElementById('modal-create-cc').style.display='none'"
                        style="padding:0.625rem 1.25rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); color:var(--color-on-surface); font-size:0.875rem; font-weight:600; cursor:pointer;">
                    Annuler
                </button>
                <button type="submit"
                        style="padding:0.625rem 1.5rem; border-radius:0.5rem; border:none; background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.875rem; font-weight:700; cursor:pointer;">
                    Créer le contrôle
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@if($evaluations->isEmpty())
{{-- Aucune évaluation --}}
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:3.5rem 1.5rem; text-align:center; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    <span class="material-symbols-outlined" style="font-size:52px; color:var(--color-on-surface-variant); opacity:0.3; display:block; margin-bottom:1rem;">quiz</span>
    <h3 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem;">{{ $history ? 'Aucune évaluation dans l’historique de ce cours' : 'Aucune évaluation ouverte pour ce cours' }}</h3>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0; max-width:400px; margin-inline:auto;">
        {{ $history ? 'Cet historique ne contient aucune note enregistrée.' : 'Les évaluations clôturées sont consultables depuis l’historique.' }}
    </p>
</div>

@else
{{-- Onglets par évaluation --}}
<div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom:1.5rem;" id="eval-tabs">
    @foreach($evaluations as $i => $eval)
    <button type="button"
            onclick="showEval({{ $eval->id }})"
            id="tab-{{ $eval->id }}"
            style="padding:0.5rem 1.125rem; border-radius:0.625rem; border:2px solid {{ $i === 0 ? 'var(--color-primary-container)' : 'var(--color-outline-variant)' }}; background:{{ $i === 0 ? 'var(--color-primary-container)' : 'var(--color-surface)' }}; color:{{ $i === 0 ? 'var(--color-on-primary)' : 'var(--color-on-surface-variant)' }}; font-size:0.8125rem; font-weight:700; cursor:pointer; transition:all 0.15s;">
        {{ $eval->titre }}
    </button>
    @endforeach
</div>

@foreach($evaluations as $i => $eval)
@php
    $gradesByStudent = $eval->grades->keyBy('student_profile_id');
    $isCC = in_array($eval->type, ['controle_continu', 'interrogation']);
    $isReadOnly = $history || $eval->statut !== 'ouverte' || $course->statut === 'termine';
@endphp
<div id="eval-panel-{{ $eval->id }}" style="{{ $i !== 0 ? 'display:none;' : '' }}">

    {{-- Info évaluation --}}
    <div style="display:flex; gap:1rem; margin-bottom:1.25rem; flex-wrap:wrap; align-items:center;">
        <div style="padding:0.625rem 1rem; border-radius:0.625rem; background:var(--color-surface-container-low); font-size:0.8125rem; color:var(--color-on-surface-variant);">
            <span style="font-weight:700; color:var(--color-on-surface);">Type :</span>
            {{ match($eval->type) {
                'controle_continu' => 'Contrôle Continu',
                'interrogation'    => 'Interrogation',
                'devoir'           => 'Devoir',
                'examen'           => 'Examen',
                default            => ucfirst($eval->type ?? '—')
            } }}
        </div>
        @if($eval->date)
        <div style="padding:0.625rem 1rem; border-radius:0.625rem; background:var(--color-surface-container-low); font-size:0.8125rem; color:var(--color-on-surface-variant);">
            <span style="font-weight:700; color:var(--color-on-surface);">Date :</span>
            {{ \Carbon\Carbon::parse($eval->date)->format('d/m/Y') }}
        </div>
        @endif
        <div style="padding:0.625rem 1rem; border-radius:0.625rem; background:var(--color-surface-container-low); font-size:0.8125rem; color:var(--color-on-surface-variant);">
            <span style="font-weight:700; color:var(--color-on-surface);">Note max :</span> 20
        </div>
        @if($isReadOnly)
        <div style="padding:0.625rem 1rem; border-radius:0.625rem; background:#f3f4f6; font-size:0.8125rem; font-weight:700; color:#4b5563;">
            Lecture seule
        </div>
        @elseif($eval->statut === 'ouverte')
        <div style="padding:0.625rem 1rem; border-radius:0.625rem; background:#fffbeb; font-size:0.8125rem; font-weight:700; color:#92400e;">
            En cours
        </div>
        @elseif($eval->statut === 'cloturee')
        <div style="padding:0.625rem 1rem; border-radius:0.625rem; background:#ecfdf5; font-size:0.8125rem; font-weight:700; color:#065f46;">
            ✓ Clôturée
        </div>
        @endif

        @if($isCC)
        <span style="padding:0.375rem 0.75rem; border-radius:9999px; font-size:0.6875rem; font-weight:800; text-transform:uppercase; background:var(--color-primary-container); color:var(--color-on-primary); letter-spacing:0.04em;">
            CC — Créé par vous
        </span>
        @endif
    </div>

    @if($students->isEmpty())
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:2.5rem; text-align:center; color:var(--color-on-surface-variant); font-size:0.875rem;">
        Aucun étudiant inscrit trouvé pour ce cours.
    </div>
    @else
    <form method="POST" action="{{ route('teacher.grades.store') }}">
        @csrf
        <input type="hidden" name="course_id" value="{{ $course->id }}">
        <input type="hidden" name="evaluation_id" value="{{ $eval->id }}">

        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden; border:1px solid var(--color-surface-container);">
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
                    <thead>
                        <tr style="background:var(--color-surface-container-low); border-bottom:2px solid var(--color-outline-variant);">
                            <th style="text-align:left; padding:0.875rem 1.5rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">#</th>
                            <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Étudiant</th>
                            <th style="text-align:center; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Note / 20</th>
                            <th style="text-align:center; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Absent</th>
                            <th style="text-align:left; padding:0.875rem 1.5rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Commentaire</th>
                            @if($isCC)
                            <th style="text-align:center; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $j => $student)
                        @php
                            $grade = $gradesByStudent->get($student->id);
                        @endphp
                        <tr style="border-bottom:1px solid var(--color-surface-container);">
                            <td style="padding:0.75rem 1.5rem; font-size:0.8125rem; color:var(--color-on-surface-variant); font-weight:600;">{{ $j + 1 }}</td>
                            <td style="padding:0.75rem 1rem;">
                                <div style="display:flex; align-items:center; gap:0.75rem;">
                                    <div style="width:34px; height:34px; border-radius:50%; background:var(--color-primary-container); color:var(--color-on-primary); display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:800; flex-shrink:0;">
                                        {{ $student->user->initials ?? '??' }}
                                    </div>
                                    <div>
                                        <div style="font-weight:700; color:var(--color-on-surface);">{{ $student->user->full_name ?? '—' }}</div>
                                        <div style="font-size:0.75rem; font-family:monospace; color:var(--color-on-surface-variant);">{{ $student->student_id ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:0.75rem 1rem; text-align:center;">
                                <input type="number"
                                       name="grades[{{ $student->id }}][score]"
                                       value="{{ $grade && !$grade->is_absent ? $grade->score : '' }}"
                                       min="0" max="20" step="0.25"
                                       placeholder="—"
                                       {{ $isReadOnly ? 'disabled' : '' }}
                                       style="width:80px; height:36px; text-align:center; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; font-weight:700; color:var(--color-on-surface);">
                            </td>
                            <td style="padding:0.75rem 1rem; text-align:center;">
                                <input type="checkbox"
                                       name="grades[{{ $student->id }}][is_absent]"
                                       {{ $grade?->is_absent ? 'checked' : '' }}
                                       {{ $isReadOnly ? 'disabled' : '' }}
                                       style="width:18px; height:18px; accent-color:#e11d48; cursor:pointer;">
                            </td>
                            <td style="padding:0.75rem 1.5rem;">
                                <input type="text"
                                       name="grades[{{ $student->id }}][comments]"
                                       value="{{ $grade?->comments ?? '' }}"
                                       placeholder="Commentaire…"
                                       {{ $isReadOnly ? 'disabled' : '' }}
                                       style="width:100%; height:36px; padding:0 0.75rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.8125rem; color:var(--color-on-surface);">
                            </td>
                            @if($isCC && ! $isReadOnly)
                            <td style="padding:0.75rem 1rem; text-align:center;">
                                @if($grade)
                                <button type="submit"
                                        form="delete-grade-{{ $grade->id }}"
                                        title="Supprimer cette note"
                                        style="padding:0.45rem 0.65rem; border:1px solid #fecaca; border-radius:0.5rem; background:#fef2f2; color:#b91c1c; font-size:0.8125rem; font-weight:700; cursor:pointer;">
                                    <span class="material-symbols-outlined" style="font-size:17px; vertical-align:middle;">delete</span>
                                    Supprimer
                                </button>
                                @endif
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @php
            $gradedCount = $eval->grades->whereNotNull('score')->count();
            $absentCount = $eval->grades->where('is_absent', true)->count();
            $scores      = $eval->grades->where('is_absent', false)->whereNotNull('score')->pluck('score');
            $avg         = $scores->count() > 0 ? round($scores->avg(), 2) : null;
        @endphp

        @if($eval->grades->count() > 0)
        <div style="display:flex; gap:1rem; margin-top:1rem; flex-wrap:wrap;">
            <div style="padding:0.625rem 1rem; border-radius:0.625rem; background:var(--color-surface-container-low); font-size:0.8125rem;">
                <span style="color:var(--color-on-surface-variant);">Notés :</span>
                <strong style="color:var(--color-on-surface);">{{ $gradedCount }}/{{ $students->count() }}</strong>
            </div>
            <div style="padding:0.625rem 1rem; border-radius:0.625rem; background:var(--color-surface-container-low); font-size:0.8125rem;">
                <span style="color:var(--color-on-surface-variant);">Absents :</span>
                <strong style="color:#e11d48;">{{ $absentCount }}</strong>
            </div>
            @if($avg !== null)
            <div style="padding:0.625rem 1rem; border-radius:0.625rem; background:var(--color-surface-container-low); font-size:0.8125rem;">
                <span style="color:var(--color-on-surface-variant);">Moyenne :</span>
                <strong style="color:{{ $avg >= 10 ? '#059669' : '#e11d48' }};">
                    {{ number_format($avg, 2) }} / 20
                </strong>
            </div>
            @endif
        </div>
        @endif

        @if(! $isReadOnly)
        <div style="margin-top:1.5rem; display:flex; flex-wrap:wrap; justify-content:flex-end; gap:1rem;">
            <a href="{{ route('teacher.grades.index', ['course_id' => $course->id]) }}"
               style="padding:0.625rem 1.5rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); color:var(--color-on-surface); text-decoration:none; font-size:0.875rem; font-weight:600;">
                Réinitialiser
            </a>
            <button type="submit" class="interactive-hover"
                    style="padding:0.625rem 2rem; border-radius:0.5rem; border:none; background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.875rem; font-weight:700; cursor:pointer;">
                <span class="material-symbols-outlined" style="font-size:18px; vertical-align:middle;">save</span>
                Enregistrer les notes
            </button>
        </div>
        @endif
    </form>
    @if($isCC && ! $isReadOnly)
        @foreach($eval->grades as $gradeToDelete)
        <form id="delete-grade-{{ $gradeToDelete->id }}"
              method="POST"
              action="{{ route('teacher.grades.destroy', $gradeToDelete) }}"
              onsubmit="return confirm('Supprimer cette note de contrôle continu ?')"
              style="display:none;">
            @csrf
            @method('DELETE')
        </form>
        @endforeach
    @endif
    @endif
</div>
@endforeach

<script>
const evalIds = @json($evaluations->pluck('id'));

function showEval(evalId) {
    evalIds.forEach(id => {
        const panel = document.getElementById('eval-panel-' + id);
        const tab   = document.getElementById('tab-' + id);
        if (!panel || !tab) return;
        if (id === evalId) {
            panel.style.display = '';
            tab.style.background = 'var(--color-primary-container)';
            tab.style.color = 'var(--color-on-primary)';
            tab.style.borderColor = 'var(--color-primary-container)';
        } else {
            panel.style.display = 'none';
            tab.style.background = 'var(--color-surface)';
            tab.style.color = 'var(--color-on-surface-variant)';
            tab.style.borderColor = 'var(--color-outline-variant)';
        }
    });
}
</script>
@endif
@endif

@endsection
