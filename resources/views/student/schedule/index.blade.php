@extends('layouts.role')
@section('title', 'Emploi du temps - Espace Étudiant')
@section('role_label', 'Étudiant')
@section('home_route', route('student.dashboard'))

@section('nav_links')
    @include('student.partials.nav')
@endsection

@section('content')
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Mon Emploi du Temps & Cours
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Retrouvez tous les cours et modules associés à votre cursus
        </p>
    </div>

    @if($enrollments->isNotEmpty())
    <div style="display:flex; align-items:center; gap:0.75rem;">
        @foreach($enrollments as $enrollment)
            <span style="font-size:0.8125rem; font-weight:600; padding:0.375rem 0.875rem; border-radius:9999px; background:var(--color-surface-container-high); color:var(--color-on-surface);">
                {{ $enrollment->formation->name ?? 'Formation' }}
            </span>
            <span style="font-size:0.8125rem; font-weight:700; padding:0.375rem 0.875rem; border-radius:9999px; background:var(--color-primary-container); color:var(--color-on-primary);">
                {{ $enrollment->academicSession->name ?? 'Session' }}
            </span>
        @endforeach
    </div>
    @endif
</div>

@if($enrollments->isEmpty())
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:3.5rem 1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); text-align:center;">
    <span class="material-symbols-outlined" style="font-size:52px; color:var(--color-on-surface-variant); opacity:0.35; margin-bottom:1rem; display:block;">school</span>
    <h3 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem;">Aucune inscription accessible</h3>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0 auto; max-width:400px;">
        Votre dossier étudiant n'est associé à aucune inscription active ou terminée. Veuillez contacter l'administration de l'IFAM.
    </p>
    <a href="{{ route('contact.index') }}"
       style="display:inline-flex; align-items:center; gap:0.5rem; margin-top:1.5rem; padding:0.625rem 1.25rem; border-radius:0.5rem;
              background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none; font-weight:700; font-size:0.875rem;">
        <span class="material-symbols-outlined" style="font-size:18px;">mail</span>
        Contacter l'administration
    </a>
</div>
@elseif($courses->isEmpty())
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:3.5rem 1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); text-align:center;">
    <span class="material-symbols-outlined" style="font-size:52px; color:var(--color-on-surface-variant); opacity:0.35; margin-bottom:1rem; display:block;">calendar_month</span>
    <h3 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem;">Aucun cours planifié pour le moment</h3>
    <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0 auto; max-width:450px;">
        @if($hasCoursesInEnrolledFormations)
            Des cours existent pour votre formation, mais ils sont rattachés à une autre session que celle de votre inscription. Contactez la scolarité pour vérifier ces associations.
        @else
            Les cours et créneaux de vos inscriptions sont en cours de finalisation par la scolarité. Revenez très bientôt.
        @endif
    </p>
</div>
@else
<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:1.25rem;">
    @foreach($courses as $course)
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden; border:1px solid var(--color-surface-container); display:flex; flex-direction:column; transition:transform 0.15s, box-shadow 0.15s;"
         onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(11,28,48,0.08)';"
         onmouseout="this.style.transform=''; this.style.boxShadow='0 1px 6px rgba(11,28,48,0.04)';">
        <div style="height:6px; background:{{ $course->color ?? '#e40066' }};"></div>
        <div style="padding:1.5rem; display:flex; flex-direction:column; justify-content:space-between; flex:1;">
            <div>
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                    <span style="font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; padding:0.25rem 0.5rem; border-radius:0.375rem; background:var(--color-surface-container-high); color:var(--color-on-surface-variant);">
                        {{ $course->module->code ?? 'MOD' }}
                    </span>
                    <span style="font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant);">
                        Coef. {{ $course->module->coefficient ?? 1 }}
                    </span>
                </div>

                <h3 style="font-size:1.0625rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem; line-height:1.3;">
                    {{ $course->display_name }}
                </h3>

                <div style="display:flex; flex-direction:column; gap:0.5rem; margin:0.75rem 0 1rem; font-size:0.8125rem; color:var(--color-on-surface-variant);">
                    <div style="display:flex; align-items:center; gap:0.5rem;">
                        <span class="material-symbols-outlined" style="font-size:18px;">calendar_today</span>
                        <span>{{ $course->date?->format('d/m/Y') ?? 'Date non définie' }}</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.5rem;">
                        <span class="material-symbols-outlined" style="font-size:18px;">schedule</span>
                        <span>{{ $course->heure_debut ? \Carbon\Carbon::parse($course->heure_debut)->format('H:i') : '--:--' }} - {{ $course->heure_fin ? \Carbon\Carbon::parse($course->heure_fin)->format('H:i') : '--:--' }}</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.5rem;">
                        <span class="material-symbols-outlined" style="font-size:18px;">meeting_room</span>
                        <span>{{ $course->salle->name ?? 'Salle non définie' }}</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.5rem;">
                        <span class="material-symbols-outlined" style="font-size:18px;">event</span>
                        <span>{{ $course->academicSession->name ?? 'Session non définie' }}</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.5rem;">
                        <span class="material-symbols-outlined" style="font-size:18px;">person</span>
                        <span>{{ $course->enseignant->user->full_name ?? $course->teacherProfile->user->full_name ?? 'Enseignant non assigné' }}</span>
                    </div>
                </div>

                @if($course->module && $course->module->description)
                <p style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin:0 0 1rem; line-height:1.4;">
                    {{ \Illuminate\Support\Str::limit($course->module->description, 90) }}
                </p>
                @endif
            </div>

            @if($course->documents->isNotEmpty())
            <div style="border-top:1px solid var(--color-surface-container); padding-top:1rem; margin-top:1rem;">
                <h4 style="display:flex; align-items:center; gap:0.375rem; font-size:0.8125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.625rem;">
                    <span class="material-symbols-outlined" style="font-size:18px; color:var(--color-primary-container);">folder_open</span>
                    Documents du cours
                </h4>
                <div style="display:flex; flex-direction:column; gap:0.5rem;">
                    @foreach($course->documents as $document)
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:0.75rem; padding:0.625rem 0.75rem; border-radius:0.5rem; background:var(--color-surface-container-low);">
                            <div style="min-width:0;">
                                <div style="font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); overflow-wrap:anywhere;">{{ $document->title }}</div>
                                <div style="font-size:0.6875rem; color:var(--color-on-surface-variant); overflow-wrap:anywhere;">{{ $document->original_name }} · {{ $document->created_at->format('d/m/Y') }}</div>
                            </div>
                            <a href="{{ route('student.course-documents.download', $document) }}" style="flex-shrink:0; display:inline-flex; align-items:center; gap:0.25rem; padding:0.375rem 0.625rem; border-radius:0.375rem; background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none; font-size:0.75rem; font-weight:700;">
                                <span class="material-symbols-outlined" style="font-size:16px;">download</span>
                                Télécharger
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            @else
            <div style="border-top:1px solid var(--color-surface-container); padding-top:0.75rem; margin-top:1rem; display:flex; align-items:center; gap:0.375rem; font-size:0.75rem; color:var(--color-on-surface-variant);">
                <span class="material-symbols-outlined" style="font-size:17px;">folder_open</span>
                Aucun document ajouté
            </div>
            @endif

            <div style="border-top:1px solid var(--color-surface-container); padding-top:1rem; margin-top:1rem; display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:0.5rem;">
                    <div style="width:30px; height:30px; border-radius:50%; background:var(--color-surface-container-high); display:flex; align-items:center; justify-content:center; color:var(--color-on-surface); font-size:0.6875rem; font-weight:700;">
                        {{ $course->teacherProfile->user->initials ?? 'PR' }}
                    </div>
                    <div>
                        <div style="font-size:0.8125rem; font-weight:600; color:var(--color-on-surface);">
                            {{ $course->teacherProfile->user->full_name ?? 'Enseignant non assigné' }}
                        </div>
                        <div style="font-size:0.6875rem; color:var(--color-on-surface-variant); text-transform:capitalize;">
                            {{ $course->teacherProfile->teacher_type ?? 'Professeur' }}
                        </div>
                    </div>
                </div>

                <span class="material-symbols-outlined" style="color:var(--color-primary-container); font-size:22px;">menu_book</span>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection
