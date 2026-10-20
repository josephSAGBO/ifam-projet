@extends('layouts.role')
@section('title', 'Saisie du devoir - Espace Enseignant')
@section('role_label', 'Enseignant')
@section('home_route', route('teacher.dashboard'))

@section('nav_links')
    @include('teacher.partials.nav')
@endsection

@section('content')
<div style="margin-bottom:1.5rem;">
    <a href="{{ route('teacher.grades.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; margin-bottom:0.75rem; color:var(--color-primary-container); text-decoration:none; font-size:0.8125rem; font-weight:700;">
        <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
        Retour aux évaluations
    </a>
    <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem;">{{ $evaluation->titre }}</h1>
    <div style="display:flex; flex-wrap:wrap; gap:0.5rem;">
        @foreach([$evaluation->formation?->name, $evaluation->academicSession?->name, $evaluation->module?->name] as $label)
            @if($label)
                <span style="padding:0.3rem 0.7rem; border-radius:9999px; background:var(--color-surface-container-high); color:var(--color-on-surface-variant); font-size:0.75rem; font-weight:600;">{{ $label }}</span>
            @endif
        @endforeach
        @if($evaluation->date)
            <span style="padding:0.3rem 0.7rem; border-radius:9999px; background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.75rem; font-weight:700;">{{ $evaluation->date->format('d/m/Y') }}</span>
        @endif
    </div>
</div>

@if(session('success'))
    <div role="status" style="padding:0.875rem 1rem; margin-bottom:1rem; border-radius:0.625rem; background:#ecfdf5; color:#065f46; font-size:0.875rem; font-weight:600;">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div role="alert" style="padding:0.875rem 1rem; margin-bottom:1rem; border-radius:0.625rem; background:#fef2f2; color:#991b1b; font-size:0.875rem;">
        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif

@if($isReadOnly)
    <div role="status" style="padding:0.875rem 1rem; margin-bottom:1rem; border-radius:0.625rem; background:#f3f4f6; color:#4b5563; font-size:0.875rem; font-weight:700;">
        Cette évaluation est clôturée ou son cours est terminé. Les notes sont consultables en lecture seule.
    </div>
@endif

@if($students->isEmpty())
    <div style="padding:3rem 1.5rem; border-radius:0.75rem; background:var(--color-surface-container-lowest); text-align:center; color:var(--color-on-surface-variant);">
        Aucun étudiant actif n’est inscrit à cette formation pour cette session.
    </div>
@else
    <form method="POST" action="{{ route('teacher.grades.assigned.store', $evaluation) }}">
        @csrf
        <div style="background:var(--color-surface-container-lowest); border:1px solid var(--color-outline-variant); border-radius:0.75rem; overflow:hidden;">
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
                    <thead>
                        <tr style="background:var(--color-surface-container-low); text-align:left;">
                            <th style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">Étudiant</th>
                            <th style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">Note / 20</th>
                            <th style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">Absent</th>
                            <th style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">Commentaire</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            @php($grade = $grades->get($student->id))
                            <tr style="border-top:1px solid var(--color-surface-container);">
                                <td style="padding:0.75rem 1rem; font-weight:600; color:var(--color-on-surface);">{{ $student->user->full_name }}</td>
                                <td style="padding:0.75rem 1rem;">
                                    <input type="number" name="grades[{{ $student->id }}][score]" value="{{ old('grades.'.$student->id.'.score', $grade?->score) }}" min="0" max="20" step="0.25" {{ $isReadOnly ? 'disabled' : '' }} style="width:100px; height:38px; padding:0 0.5rem; border:1px solid var(--color-outline-variant); border-radius:0.5rem; background:var(--color-surface);">
                                </td>
                                <td style="padding:0.75rem 1rem; text-align:center;">
                                    <input type="checkbox" name="grades[{{ $student->id }}][is_absent]" value="1" {{ old('grades.'.$student->id.'.is_absent', $grade?->is_absent) ? 'checked' : '' }} {{ $isReadOnly ? 'disabled' : '' }} style="width:18px; height:18px;">
                                </td>
                                <td style="padding:0.75rem 1rem;">
                                    <input type="text" name="grades[{{ $student->id }}][comments]" value="{{ old('grades.'.$student->id.'.comments', $grade?->comments) }}" maxlength="255" {{ $isReadOnly ? 'disabled' : '' }} style="width:100%; height:38px; padding:0 0.75rem; border:1px solid var(--color-outline-variant); border-radius:0.5rem; background:var(--color-surface);">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if(! $isReadOnly)
        <div style="display:flex; justify-content:flex-end; margin-top:1rem;">
            <button type="submit" class="interactive-hover" style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem; border:0; border-radius:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.875rem; font-weight:700; cursor:pointer;">
                <span class="material-symbols-outlined" style="font-size:18px;">save</span>
                Enregistrer les notes
            </button>
        </div>
        @endif
    </form>
@endif
@endsection