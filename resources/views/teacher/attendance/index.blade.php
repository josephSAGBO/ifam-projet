@extends('layouts.role')
@section('title', 'Présences - Espace Enseignant')
@section('role_label', 'Enseignant')
@section('home_route', route('teacher.dashboard'))

@section('nav_links')
    @include('teacher.partials.nav')
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Gestion des Présences
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            {{ $history ? 'Consultez les feuilles de présence de vos cours terminés en lecture seule' : 'Sélectionnez un cours et une date pour faire l’appel des étudiants' }}
        </p>
    </div>
    <a href="{{ route('teacher.attendance.index', $history ? [] : ['history' => 1]) }}"
       class="interactive-hover"
       style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.625rem 0.875rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); color:var(--color-on-surface); text-decoration:none; font-size:0.8125rem; font-weight:700;">
        <span class="material-symbols-outlined" style="font-size:18px;">{{ $history ? 'edit_note' : 'history' }}</span>
        {{ $history ? 'Retour aux présences' : 'Historique' }}
    </a>
    @if($sessions->isNotEmpty())
    <div style="display:flex; align-items:center; flex-wrap:wrap; gap:0.5rem;">
        <span style="font-size:0.75rem; color:var(--color-on-surface-variant);">Sessions de vos cours :</span>
        @foreach($sessions as $session)
            <span style="font-size:0.75rem; font-weight:700; padding:0.375rem 0.75rem; border-radius:9999px; background:var(--color-primary-container); color:var(--color-on-primary);">
                {{ $session->name }}
            </span>
        @endforeach
    </div>
    @endif
</div>

@if(session('success'))
<div style="background:#ecfdf5; border:1px solid #6ee7b7; border-radius:0.75rem; padding:0.875rem 1.25rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.75rem; color:#065f46; font-size:0.875rem; font-weight:600;">
    <span class="material-symbols-outlined" style="font-size:20px;">check_circle</span>
    {{ session('success') }}
</div>
@endif

{{-- Sélecteurs de filtre --}}
<form method="GET" action="{{ route('teacher.attendance.index') }}"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem 1.5rem; margin-bottom:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1rem; align-items:end;">
    @if($history)
        <input type="hidden" name="history" value="1">
    @endif
    @if(request('course_id'))
        <input type="hidden" name="course_id" value="{{ request('course_id') }}">
    @endif
    <div>
        <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); margin-bottom:0.375rem; text-transform:uppercase; letter-spacing:0.06em;">Session</label>
        <select name="session_id" onchange="this.form.submit()" style="width:100%; height:42px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">
            <option value="">— Sélectionnez une session —</option>
            @foreach($sessions as $session)
                <option value="{{ $session->id }}" {{ (string) $selectedSessionId === (string) $session->id ? 'selected' : '' }}>
                    {{ $session->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="attendance-date" style="display:block; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); margin-bottom:0.375rem; text-transform:uppercase; letter-spacing:0.06em;">Date de la séance</label>
        <input id="attendance-date" type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()"
               style="width:100%; height:42px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">
    </div>

    <div>
        <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); margin-bottom:0.375rem; text-transform:uppercase; letter-spacing:0.06em;">Formation</label>
        <select name="formation_id" onchange="this.form.submit()" {{ $selectedSessionId ? '' : 'disabled' }} style="width:100%; height:42px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">
            <option value="">— Sélectionnez une formation —</option>
            @foreach($formations as $formation)
                <option value="{{ $formation->id }}" {{ (string) $selectedFormationId === (string) $formation->id ? 'selected' : '' }}>
                    {{ $formation->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); margin-bottom:0.375rem; text-transform:uppercase; letter-spacing:0.06em;">Module</label>
        <select name="module_id" onchange="this.form.submit()" {{ $selectedFormationId ? '' : 'disabled' }} style="width:100%; height:42px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">
            <option value="">— Sélectionnez un module —</option>
            @foreach($modules as $module)
                <option value="{{ $module->id }}" {{ (string) $selectedModuleId === (string) $module->id ? 'selected' : '' }}>
                    {{ $module->name }}
                </option>
            @endforeach
        </select>
    </div>

    @if($selectedSessionId || $selectedFormationId || $selectedModuleId)
        <div>
            <a href="{{ route('teacher.attendance.index') }}" style="display:inline-flex; align-items:center; justify-content:center; height:42px; padding:0 1rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); color:var(--color-on-surface); text-decoration:none; font-size:0.8125rem; font-weight:700;">
                Réinitialiser
            </a>
        </div>
    @endif
</form>

@if($courses->isNotEmpty())
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1rem 1.25rem; margin-bottom:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <div style="font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.75rem;">Cours filtrés</div>
        <div style="display:flex; flex-wrap:wrap; gap:0.75rem;">
            @foreach($courses as $c)
                <a href="{{ route('teacher.attendance.index', ['session_id' => $selectedSessionId, 'formation_id' => $selectedFormationId, 'module_id' => $selectedModuleId, 'course_id' => $c->id, 'date' => $selectedDate, 'history' => $history ? 1 : null]) }}"
                   class="interactive-hover"
                   style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 0.875rem; border-radius:0.625rem; background:{{ isset($course) && $course->id === $c->id ? 'var(--color-primary-container)' : 'var(--color-surface-container-high)' }}; color:{{ isset($course) && $course->id === $c->id ? 'var(--color-on-primary)' : 'var(--color-on-surface)' }}; text-decoration:none; font-size:0.8125rem; font-weight:700;">
                    {{ $c->display_name }}
                </a>
            @endforeach
        </div>
    </div>
@endif

@if(!isset($course))
{{-- État vide --}}
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:4rem 1.5rem; text-align:center; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    <span class="material-symbols-outlined" style="font-size:56px; color:var(--color-on-surface-variant); opacity:0.3; display:block; margin-bottom:1rem;">how_to_reg</span>
    <h3 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem;">Aucun cours sélectionné</h3>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0; max-width:380px; margin-inline:auto;">
        Choisissez un cours dans la liste ci-dessus pour afficher la liste des étudiants et saisir les présences.
    </p>
</div>

@elseif($students->isEmpty())
{{-- Aucun étudiant --}}
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:3.5rem 1.5rem; text-align:center; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    <span class="material-symbols-outlined" style="font-size:52px; color:var(--color-on-surface-variant); opacity:0.3; display:block; margin-bottom:1rem;">person_search</span>
    <h3 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem;">Aucun étudiant inscrit</h3>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0; max-width:380px; margin-inline:auto;">
        Aucun étudiant actif trouvé pour la formation liée à ce cours.
    </p>
</div>

@else
{{-- En-tête cours --}}
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem 1.5rem; margin-bottom:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
    <div>
        <div style="font-size:1.0625rem; font-weight:800; color:var(--color-on-surface);">{{ $course->display_name }}</div>
        <div style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin-top:0.25rem;">
                Module : {{ $course->module->name ?? '—' }} · Formation : {{ $course->formation->name ?? '—' }} · Session : {{ $course->academicSession->name ?? '—' }} · {{ $students->count() }} étudiant(s)
        </div>
    </div>
    @if(! $isAttendanceReadOnly)
    <div style="display:flex; flex-wrap:wrap; gap:0.5rem;">
        <button type="button" onclick="markAll('present')"
                style="padding:0.5rem 1rem; border-radius:0.5rem; border:1px solid #6ee7b7; background:#ecfdf5; color:#065f46; font-size:0.8125rem; font-weight:700; cursor:pointer;">
            ✓ Tous présents
        </button>
        <button type="button" onclick="markAll('absent')"
                style="padding:0.5rem 1rem; border-radius:0.5rem; border:1px solid #fca5a5; background:#fff1f2; color:#9f1239; font-size:0.8125rem; font-weight:700; cursor:pointer;">
            ✗ Tous absents
        </button>
    </div>
    @else
    <span style="padding:0.5rem 0.75rem; border-radius:9999px; background:#f3f4f6; color:#4b5563; font-size:0.75rem; font-weight:700;">
        {{ $history ? 'Historique — lecture seule' : ($course->statut === 'termine' ? 'Cours terminé — lecture seule' : 'Séance clôturée — lecture seule') }}
    </span>
    @endif
</div>

@if($isAttendanceReadOnly && $attendances->isEmpty())
<div role="status" style="padding:0.875rem 1rem; margin-bottom:1rem; border-radius:0.625rem; background:var(--color-surface-container-low); color:var(--color-on-surface-variant); font-size:0.875rem;">
    Aucun appel n’a été enregistré pour cette date.
</div>
@endif

{{-- Formulaire de présence --}}
<form method="POST" action="{{ route('teacher.attendance.store') }}">
    @csrf
    <input type="hidden" name="course_id" value="{{ $course->id }}">
    <input type="hidden" name="date" value="{{ $selectedDate }}">

    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden; border:1px solid var(--color-surface-container);">
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
                <thead>
                    <tr style="background:var(--color-surface-container-low); border-bottom:2px solid var(--color-outline-variant);">
                        <th style="text-align:left; padding:0.875rem 1.5rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">#</th>
                        <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Étudiant</th>
                        <th style="text-align:center; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Statut</th>
                        <th style="text-align:left; padding:0.875rem 1.5rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Observation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $i => $student)
                    @php
                        $existing = $attendances->get($student->id);
                        $currentStatus = $existing?->status ?? ($isAttendanceReadOnly ? null : 'present');
                    @endphp
                    <tr style="border-bottom:1px solid var(--color-surface-container);" class="att-row">
                        <td style="padding:0.875rem 1.5rem; font-size:0.8125rem; color:var(--color-on-surface-variant); font-weight:600;">
                            {{ $i + 1 }}
                        </td>
                        <td style="padding:0.875rem 1rem;">
                            <div style="display:flex; align-items:center; gap:0.75rem;">
                                <div style="width:36px; height:36px; border-radius:50%; background:var(--color-primary-container); color:var(--color-on-primary); display:flex; align-items:center; justify-content:center; font-size:0.8125rem; font-weight:800; flex-shrink:0;">
                                    {{ $student->user->initials ?? '??' }}
                                </div>
                                <div>
                                    <div style="font-weight:700; color:var(--color-on-surface);">{{ $student->user->full_name ?? '—' }}</div>
                                    <div style="font-size:0.75rem; font-family:monospace; color:var(--color-on-surface-variant);">{{ $student->student_id ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:0.875rem 1rem; text-align:center;">
                            <div style="display:flex; justify-content:center; gap:0.375rem;">
                                @foreach(['present' => ['label' => 'P', 'bg' => '#ecfdf5', 'color' => '#065f46', 'border' => '#6ee7b7'], 'absent' => ['label' => 'A', 'bg' => '#fff1f2', 'color' => '#9f1239', 'border' => '#fca5a5'], 'late' => ['label' => 'R', 'bg' => '#f5f3ff', 'color' => '#5b21b6', 'border' => '#c4b5fd']] as $statusKey => $sc)
                                <label style="cursor:pointer;">
                                    <input type="radio"
                                           name="attendances[{{ $student->id }}][status]"
                                           value="{{ $statusKey }}"
                                           class="att-radio-{{ $student->id }}"
                                           {{ $currentStatus === $statusKey ? 'checked' : '' }}
                                           {{ $isAttendanceReadOnly ? 'disabled' : '' }}
                                           style="display:none;"
                                           onchange="updateRowStyle({{ $student->id }})">
                                    <span class="status-btn status-{{ $statusKey }}-{{ $student->id }}"
                                          style="display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:0.5rem; border:2px solid {{ $currentStatus === $statusKey ? $sc['border'] : 'var(--color-outline-variant)' }}; background:{{ $currentStatus === $statusKey ? $sc['bg'] : 'var(--color-surface)' }}; color:{{ $currentStatus === $statusKey ? $sc['color'] : 'var(--color-on-surface-variant)' }}; font-size:0.75rem; font-weight:800; transition:all 0.15s; user-select:none;"
                                          data-status="{{ $statusKey }}"
                                          data-student="{{ $student->id }}"
                                          data-bg="{{ $sc['bg'] }}"
                                          data-color="{{ $sc['color'] }}"
                                          data-border="{{ $sc['border'] }}">
                                        {{ $sc['label'] }}
                                    </span>
                                </label>
                                @endforeach
                            </div>
                        </td>
                        <td style="padding:0.875rem 1.5rem;">
                            <input type="text"
                                   name="attendances[{{ $student->id }}][remarks]"
                                   value="{{ $existing?->remarks ?? '' }}"
                                   placeholder="Observation…"
                                   {{ $isAttendanceReadOnly ? 'disabled' : '' }}
                                   style="width:100%; height:36px; padding:0 0.75rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.8125rem; color:var(--color-on-surface);">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if(! $isAttendanceReadOnly)
    <div style="margin-top:1.5rem; display:flex; flex-wrap:wrap; justify-content:flex-end; gap:1rem;">
        <a href="{{ route('teacher.attendance.index') }}"
           style="padding:0.625rem 1.5rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); color:var(--color-on-surface); text-decoration:none; font-size:0.875rem; font-weight:600;">
            Annuler
        </a>
        <button type="submit" class="interactive-hover"
                style="padding:0.625rem 2rem; border-radius:0.5rem; border:none; background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.875rem; font-weight:700; cursor:pointer;">
            <span class="material-symbols-outlined" style="font-size:18px; vertical-align:middle;">save</span>
            Enregistrer les présences
        </button>
    </div>
    @endif
</form>

<script>
function updateRowStyle(studentId) {
    const radios = document.querySelectorAll(`.att-radio-${studentId}`);
    radios.forEach(radio => {
        const statusKey = radio.value;
        const btn = document.querySelector(`.status-${statusKey}-${studentId}`);
        if (!btn) return;
        if (radio.checked) {
            btn.style.border = `2px solid ${btn.dataset.border}`;
            btn.style.background = btn.dataset.bg;
            btn.style.color = btn.dataset.color;
        } else {
            btn.style.border = '2px solid var(--color-outline-variant)';
            btn.style.background = 'var(--color-surface)';
            btn.style.color = 'var(--color-on-surface-variant)';
        }
    });
}

function markAll(status) {
    document.querySelectorAll(`input[type="radio"][value="${status}"]`).forEach(radio => {
        radio.checked = true;
        const studentId = radio.name.match(/\[(\d+)\]/)[1];
        updateRowStyle(parseInt(studentId));
    });
}

// Init styles on load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
        const studentId = radio.name.match(/\[(\d+)\]/)[1];
        updateRowStyle(parseInt(studentId));
    });
});
</script>
@endif

@endsection
