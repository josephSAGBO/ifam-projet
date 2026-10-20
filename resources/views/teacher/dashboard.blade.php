@extends('layouts.role')
@section('title', 'Espace Enseignant')
@section('role_label', 'Enseignant')
@section('home_route', route('teacher.dashboard'))

@section('nav_links')
    @include('teacher.partials.nav')
@endsection


@section('content')

<div style="margin-bottom:2rem;">
    <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
        Bonjour, {{ auth()->user()->first_name }} 👋
    </h1>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
        {{ now()->isoFormat('dddd D MMMM YYYY') }}
        @if($activeSession)
        · Session : <strong>{{ $activeSession->name }}</strong>
        @endif
    </p>
</div>

{{-- KPIs --}}
<div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:1rem; margin-bottom:1.5rem;">
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <div style="font-size:0.7rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.07em; margin-bottom:0.75rem;">Mes cours</div>
        <div style="font-size:2rem; font-weight:800; color:var(--color-on-surface);">{{ $myCourses->count() }}</div>
    </div>
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <div style="font-size:0.7rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.07em; margin-bottom:0.75rem;">Évaluations</div>
        <div style="font-size:2rem; font-weight:800; color:var(--color-on-surface);">{{ $myEvaluations->count() }}</div>
    </div>
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <div style="font-size:0.7rem; font-weight:700; color:var(--color-on-surface-variant); text-transform:uppercase; letter-spacing:0.07em; margin-bottom:0.75rem;">Pointages aujourd'hui</div>
        <div style="font-size:2rem; font-weight:800; color:var(--color-on-surface);">{{ $todayAttendance }}</div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">

    {{-- Mes cours récents --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1.25rem; display:flex; align-items:center; gap:0.5rem;">
            <span class="material-symbols-outlined" style="font-size:20px; color:#6366f1;">class</span> Mes cours
        </h2>
        @if($myCourses->isEmpty())
        <p style="color:var(--color-on-surface-variant); font-size:0.875rem; text-align:center; margin:2rem 0;">Aucun cours assigné.</p>
        @else
        <div style="display:flex; flex-direction:column; gap:0.625rem;">
            @foreach($myCourses as $course)
            <div style="padding:0.75rem 1rem; border-radius:0.75rem; background:var(--color-surface-container-low); display:flex; align-items:center; gap:0.875rem;">
                <div style="width:36px; height:36px; border-radius:0.625rem; background:#6366f120; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <span class="material-symbols-outlined" style="font-size:18px; color:#6366f1;">class</span>
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="font-weight:600; font-size:0.875rem; color:var(--color-on-surface); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $course->display_name }}</div>
                    <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                        {{ $course->module->name ?? '—' }} · {{ $course->formation->name ?? '—' }}
                    </div>
                </div>
                @if($course->heure_debut)
                <div style="font-size:0.75rem; color:var(--color-on-surface-variant); white-space:nowrap;">
                    {{ $course->heure_debut }}
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Mes évaluations --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1.25rem; display:flex; align-items:center; gap:0.5rem;">
            <span class="material-symbols-outlined" style="font-size:20px; color:#f59e0b;">quiz</span> Évaluations récentes
        </h2>
        @if($myEvaluations->isEmpty())
        <p style="color:var(--color-on-surface-variant); font-size:0.875rem; text-align:center; margin:2rem 0;">Aucune évaluation.</p>
        @else
        <div style="display:flex; flex-direction:column; gap:0.625rem;">
            @foreach($myEvaluations as $eval)
            <div style="padding:0.75rem 1rem; border-radius:0.75rem; background:var(--color-surface-container-low); display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <div style="font-weight:600; font-size:0.875rem; color:var(--color-on-surface);">{{ $eval->titre }}</div>
                    <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                        {{ $eval->module->name ?? $eval->course?->module?->name ?? '—' }} · {{ $eval->date?->format('d/m/Y') }}
                    </div>
                </div>
                <span style="padding:0.25rem 0.5rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#fef3c7; color:#92400e;">
                    /20
                </span>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

@endsection
