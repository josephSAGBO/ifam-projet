@extends('layouts.admin')

@section('title', 'Présences')

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Suivi des Présences
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Sélectionnez une session, une formation puis un cours pour voir les étudiants concernés et leur statut.
        </p>
    </div>
</div>

<form method="GET" action="{{ route('admin.attendance.index') }}" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1rem; margin-bottom:1.5rem; padding:1.25rem; background:var(--color-surface-container-lowest); border:1px solid var(--color-outline-variant); border-radius:1rem;">
    @includeWhen($sites->isNotEmpty(), 'admin.partials.site-filter')
    <div>
        <label for="session_id" style="display:block; margin-bottom:0.375rem; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant);">Session</label>
        <select id="session_id" name="session_id" onchange="this.form.submit()" style="width:100%; height:42px; border:1px solid var(--color-outline-variant); border-radius:0.5rem; background:var(--color-surface); padding:0 0.75rem; color:var(--color-on-surface);">
            <option value="">Choisir une session</option>
            @foreach($sessions as $session)
                <option value="{{ $session->id }}" {{ (string) $selectedSessionId === (string) $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="formation_id" style="display:block; margin-bottom:0.375rem; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant);">Formation</label>
        <select id="formation_id" name="formation_id" onchange="this.form.submit()" {{ $selectedSessionId ? '' : 'disabled' }} style="width:100%; height:42px; border:1px solid var(--color-outline-variant); border-radius:0.5rem; background:var(--color-surface); padding:0 0.75rem; color:var(--color-on-surface);">
            <option value="">Choisir une formation</option>
            @foreach($formations as $formation)
                <option value="{{ $formation->id }}" {{ (string) $selectedFormationId === (string) $formation->id ? 'selected' : '' }}>{{ $formation->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="course_id" style="display:block; margin-bottom:0.375rem; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant);">Cours</label>
        <select id="course_id" name="course_id" onchange="this.form.submit()" {{ $selectedFormationId ? '' : 'disabled' }} style="width:100%; height:42px; border:1px solid var(--color-outline-variant); border-radius:0.5rem; background:var(--color-surface); padding:0 0.75rem; color:var(--color-on-surface);">
            <option value="">Choisir un cours</option>
            @foreach($courses as $courseItem)
                <option value="{{ $courseItem->id }}" {{ (string) $selectedCourseId === (string) $courseItem->id ? 'selected' : '' }}>{{ $courseItem->display_name }}</option>
            @endforeach
        </select>
    </div>
</form>

@if(! $selectedCourseId)
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:3rem 2rem; text-align:center; color:var(--color-on-surface-variant); border:1px solid var(--color-outline-variant);">
        Sélectionnez une session, puis une formation, puis un cours pour afficher les étudiants concernés et leur présence.
    </div>
@elseif(! $course)
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:3rem 2rem; text-align:center; color:var(--color-on-surface-variant); border:1px solid var(--color-outline-variant);">
        Aucun cours correspondant à ce filtre.
    </div>
@else
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden; border:1px solid var(--color-outline-variant);">
        <div style="padding:1.25rem 1.5rem; display:flex; flex-wrap:wrap; gap:1rem; align-items:center; justify-content:space-between; border-bottom:1px solid var(--color-outline-variant);">
            <div>
                <h2 style="margin:0; font-size:1.125rem; font-weight:800; color:var(--color-on-surface);">{{ $course->display_name }}</h2>
                <div style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin-top:0.25rem;">{{ $course->formation->name ?? 'Formation' }} · {{ $course->academicSession->name ?? 'Session' }}</div>
            </div>
            <div style="display:flex; flex-wrap:wrap; align-items:center; gap:0.75rem;">
                <form method="GET" action="{{ route('admin.attendance.index') }}" style="display:flex; flex-wrap:wrap; align-items:center; gap:0.5rem;">
                    <input type="hidden" name="session_id" value="{{ $selectedSessionId }}">
                    <input type="hidden" name="formation_id" value="{{ $selectedFormationId }}">
                    <input type="hidden" name="course_id" value="{{ $course->id }}">
                    @if($selectedSiteId)
                        <input type="hidden" name="site_id" value="{{ $selectedSiteId }}">
                    @endif
                    <label for="attendance-date" style="font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant);">Date</label>
                    <input id="attendance-date" name="date" type="date" value="{{ $date->format('Y-m-d') }}"
                           style="height:38px; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); padding:0 0.75rem; color:var(--color-on-surface);">
                    <button type="submit" class="interactive-hover"
                            style="height:38px; padding:0 0.875rem; border:0; border-radius:0.5rem; background:var(--color-surface-container-high); color:var(--color-on-surface); font-weight:700; cursor:pointer;">
                        Afficher
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.attendance.sheets.update', $course) }}" style="display:flex; flex-wrap:wrap; align-items:center; gap:0.5rem;">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
                    <select name="statut" aria-label="Statut de la feuille de présence"
                            style="height:38px; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); padding:0 0.75rem; color:var(--color-on-surface);">
                        <option value="ouverte" {{ ($attendanceSheet?->statut ?? 'ouverte') === 'ouverte' ? 'selected' : '' }}>Feuille ouverte</option>
                        <option value="cloturee" {{ $attendanceSheet?->statut === 'cloturee' ? 'selected' : '' }}>Feuille clôturée</option>
                    </select>
                    <button type="submit" class="interactive-hover"
                            style="height:38px; padding:0 0.875rem; border:0; border-radius:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); font-weight:700; cursor:pointer;">
                        Enregistrer le statut
                    </button>
                </form>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
                <thead>
                    <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                        <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Étudiant</th>
                        <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant); width:220px;">Statut</th>
                        <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Observation</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        @php $attendance = $attendances->get($student->id); $status = $attendance ? $attendance->status : 'non-renseigne'; @endphp
                        <tr style="border-bottom:1px solid var(--color-surface-container);">
                            <td style="padding:0.875rem 1rem; font-weight:600; color:var(--color-on-surface);">{{ $student->user->full_name ?? '—' }}</td>
                            <td style="padding:0.875rem 1rem;">
                                <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                                    @foreach(['present' => ['label' => 'Présent', 'color' => '#065f46', 'bg' => '#ecfdf5'], 'absent' => ['label' => 'Absent', 'color' => '#991b1b', 'bg' => '#fef2f2'], 'late' => ['label' => 'Retard', 'color' => '#92400e', 'bg' => '#fffbeb'], 'non-renseigne' => ['label' => 'Non renseigné', 'color' => '#374151', 'bg' => '#f3f4f6']] as $key => $config)
                                        <span style="font-size:0.75rem; padding:0.125rem 0.5rem; border-radius:9999px; background:{{ $status === $key ? $config['bg'] : '#f3f4f6' }}; color:{{ $status === $key ? $config['color'] : '#6b7280' }}; font-weight:700;">
                                            {{ $config['label'] }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">
                                {{ $attendance?->remarks ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="padding:2rem; text-align:center; color:var(--color-on-surface-variant);">Aucun étudiant actif pour ce cours.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection
