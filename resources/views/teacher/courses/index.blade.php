@extends('layouts.role')
@section('title', 'Mes Cours - Espace Enseignant')
@section('role_label', 'Enseignant')
@section('home_route', route('teacher.dashboard'))

@section('nav_links')
    @include('teacher.partials.nav')
@endsection

@section('content')
<div style="margin-bottom:2rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Mes Cours
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Sessions de mes cours :
            @forelse($courseSessions as $courseSession)
                {{ $courseSession->name }}@if(! $loop->last), @endif
            @empty
                aucune
            @endforelse
        </p>
    </div>
</div>

@if($courses->isEmpty())
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:3rem; text-align:center; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <span class="material-symbols-outlined" style="font-size:48px; color:var(--color-on-surface-variant); opacity:0.5; margin-bottom:1rem;">library_books</span>
        <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem;">Aucun cours assigné</h2>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Vous n'avez pas de cours assignés pour le moment.
        </p>
    </div>
@else
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:1.5rem;">
        @foreach($courses as $course)
            <div style="background:var(--color-surface-container-lowest); border-radius:1rem; overflow:hidden; box-shadow:0 1px 6px rgba(11,28,48,0.04); display:flex; flex-direction:column; transition:transform 0.2s, box-shadow 0.2s;" 
                 onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 24px rgba(11,28,48,0.08)';"
                 onmouseout="this.style.transform='none'; this.style.boxShadow='0 1px 6px rgba(11,28,48,0.04)';">
                
                {{-- Entête de la carte du cours --}}
                <div style="height:80px; background:{{ $course->color ?? 'var(--color-primary)' }}; position:relative; padding:1.25rem;">
                    <div style="position:absolute; inset:0; background:linear-gradient(to right, rgba(0,0,0,0.2), transparent);"></div>
                    <div style="position:relative; z-index:1;">
                        <h3 style="margin:0; font-family:var(--font-display); font-size:1.25rem; font-weight:700; color:#ffffff; line-height:1.2; text-shadow:0 1px 2px rgba(0,0,0,0.2);">
                            {{ $course->display_name }}
                        </h3>
                    </div>
                </div>

                {{-- Corps de la carte --}}
                <div style="padding:1.25rem; flex:1; display:flex; flex-direction:column;">
                    
                    {{-- Informations --}}
                    <div style="display:flex; flex-direction:column; gap:0.75rem; margin-bottom:1.5rem;">
                        <div style="display:flex; align-items:center; gap:0.5rem; font-size:0.8125rem; color:var(--color-on-surface-variant);">
                            <span class="material-symbols-outlined" style="font-size:18px;">calendar_today</span>
                            <span>{{ $course->date?->format('d/m/Y') ?? 'Date non définie' }}</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:0.5rem; font-size:0.8125rem; color:var(--color-on-surface-variant);">
                            <span class="material-symbols-outlined" style="font-size:18px;">schedule</span>
                            <span>{{ $course->heure_debut ? \Carbon\Carbon::parse($course->heure_debut)->format('H:i') : '--:--' }} - {{ $course->heure_fin ? \Carbon\Carbon::parse($course->heure_fin)->format('H:i') : '--:--' }}</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:0.5rem; font-size:0.8125rem; color:var(--color-on-surface-variant);">
                            <span class="material-symbols-outlined" style="font-size:18px;">meeting_room</span>
                            <span>{{ $course->salle->name ?? 'Salle non définie' }}</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:0.5rem; font-size:0.8125rem; color:var(--color-on-surface-variant);">
                            <span class="material-symbols-outlined" style="font-size:18px;">school</span>
                            <span>{{ $course->formation->name ?? 'Formation non définie' }}</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:0.5rem; font-size:0.8125rem; color:var(--color-on-surface-variant);">
                            <span class="material-symbols-outlined" style="font-size:18px;">event</span>
                            <span>{{ $course->academicSession->name ?? 'Session non définie' }}</span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div style="margin-top:auto; display:flex; gap:0.75rem;">
                        <a href="{{ route('teacher.courses.show', $course) }}" style="flex:1; display:inline-flex; align-items:center; justify-content:center; gap:0.375rem; padding:0.625rem; background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none; border-radius:0.5rem; font-size:0.8125rem; font-weight:700;">
                            <span class="material-symbols-outlined" style="font-size:18px;">visibility</span> Détails
                        </a>
                        <a href="{{ route('teacher.attendance.index', ['course_id' => $course->id, 'history' => $course->statut === 'termine' ? 1 : null]) }}" style="flex:1; display:inline-flex; align-items:center; justify-content:center; gap:0.375rem; padding:0.625rem; background:var(--color-surface-container-high); color:var(--color-on-surface); text-decoration:none; border-radius:0.5rem; font-size:0.8125rem; font-weight:600;">
                            <span class="material-symbols-outlined" style="font-size:18px;">{{ $course->statut === 'termine' ? 'history' : 'fact_check' }}</span>
                            {{ $course->statut === 'termine' ? 'Historique' : 'Appel' }}
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
