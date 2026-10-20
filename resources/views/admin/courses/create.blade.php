@extends('layouts.admin')

@section('title', 'Créer un Cours')

@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.courses.index') }}"
       style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
              display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
              transition:all 0.15s;"
       onmouseover="this.style.background='var(--color-surface-container-high)'"
       onmouseout="this.style.background='var(--color-surface-container)'">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Créer un cours
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Planifiez un nouveau cours (formation, session, module, enseignant, salle & horaires).
        </p>
    </div>
</div>

<form action="{{ route('admin.courses.store') }}" method="POST"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:2rem; max-width:850px;">
    @csrf

    <div style="display:grid; grid-template-columns:1fr; gap:1.5rem;">

        {{-- Section 1 : Formation & Session --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 18rem), 1fr)); gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Formation <span style="color:#dc2626;">*</span>
                </label>
                <select id="select_formation" name="formation_id" required
                        data-searchable-select="Rechercher une formation…"
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                               border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                               font-size:0.9375rem; color:var(--color-on-surface);">
                    <option value="">Sélectionner une formation</option>
                    @foreach($formations as $formation)
                        <option value="{{ $formation->id }}" {{ old('formation_id') == $formation->id ? 'selected' : '' }}>
                            {{ $formation->name }}
                        </option>
                    @endforeach
                </select>
                @error('formation_id') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Session académique <span style="color:#dc2626;">*</span>
                </label>
                <select name="academic_session_id" required
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                               border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                               font-size:0.9375rem; color:var(--color-on-surface);">
                    <option value="">Sélectionner une session</option>
                    @foreach($academicSessions as $session)
                        <option value="{{ $session->id }}" {{ old('academic_session_id') == $session->id ? 'selected' : '' }}>
                            {{ $session->name }}
                        </option>
                    @endforeach
                </select>
                @error('academic_session_id') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Section 2 : Module & Enseignant (filtrés dynamiquement) --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 18rem), 1fr)); gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Module / Matière <span style="color:#dc2626;">*</span>
                </label>
                <select id="select_module" name="module_id" required
                        data-searchable-select="Rechercher un module…"
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                               border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                               font-size:0.9375rem; color:var(--color-on-surface);">
                    <option value="">Sélectionner d'abord une formation...</option>
                    @foreach($modules as $module)
                        <option value="{{ $module->id }}"
                                data-formation-ids="{{ json_encode($module->formations->pluck('id')) }}"
                                {{ old('module_id') == $module->id ? 'selected' : '' }}>
                            {{ $module->code }} — {{ $module->name }}
                        </option>
                    @endforeach
                </select>
                @error('module_id') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Enseignant
                </label>
                <select id="select_teacher" name="enseignant_id"
                        data-searchable-select="Rechercher un enseignant…"
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                               border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                               font-size:0.9375rem; color:var(--color-on-surface);">
                    <option value="">Non assigné</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}"
                                data-module-ids="{{ json_encode($teacher->modules->pluck('id')) }}"
                                {{ old('enseignant_id', old('teacher_profile_id')) == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->user->first_name ?? '' }} {{ $teacher->user->last_name ?? '' }}
                        </option>
                    @endforeach
                </select>
                @error('enseignant_id') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Section 3 : Date, Horaires & Salle --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 16rem), 1fr)); gap:1.25rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Date du cours
                </label>
                <input type="date" name="date" value="{{ old('date') }}"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('date') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Heure de début
                </label>
                <input type="time" name="heure_debut" value="{{ old('heure_debut') }}"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('heure_debut') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Heure de fin
                </label>
                <input type="time" name="heure_fin" value="{{ old('heure_fin') }}"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);">
                @error('heure_fin') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 18rem), 1fr)); gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Salle de cours
                </label>
                <select name="salle_id"
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                               border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                               font-size:0.9375rem; color:var(--color-on-surface);">
                    <option value="">Sélectionner une salle (Optionnel)</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ old('salle_id') == $room->id ? 'selected' : '' }}>
                            {{ $room->name }} {{ $room->description ? '('.$room->description.')' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('salle_id') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="statut" style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Statut du cours <span style="color:#dc2626;">*</span>
                </label>
                <select id="statut" name="statut" required
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                               border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                               font-size:0.9375rem; color:var(--color-on-surface);">
                    <option value="en_cours" {{ old('statut', 'en_cours') === 'en_cours' ? 'selected' : '' }}>En cours</option>
                    <option value="termine" {{ old('statut') === 'termine' ? 'selected' : '' }}>Terminé</option>
                </select>
                @error('statut') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Couleur sur l'emploi du temps
                </label>
                <div style="display:flex; align-items:center; gap:0.75rem; height:44px;">
                    <input type="color" name="color" value="{{ old('color', '#e40066') }}"
                           style="width:44px; height:44px; padding:0; border:none; border-radius:0.5rem; cursor:pointer; background:transparent;">
                    <input type="text" name="name" value="{{ old('name') }}"
                           placeholder="Nom personnalisé (optionnel)"
                           style="flex:1; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                                  border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                                  font-size:0.875rem; color:var(--color-on-surface);">
                </div>
            </div>
        </div>

    </div>

    <div style="margin-top:2rem; display:flex; justify-content:flex-end; gap:1rem;">
        <a href="{{ route('admin.courses.index') }}"
           style="height:44px; display:inline-flex; align-items:center; padding:0 1.25rem; border-radius:0.75rem;
                  background:transparent; color:var(--color-on-surface-variant); font-size:0.9375rem; font-weight:600; text-decoration:none;">
            Annuler
        </a>
        <button type="submit" class="interactive-hover"
                style="height:44px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.5rem; border-radius:0.75rem; border:none; cursor:pointer;
                       background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.9375rem; font-weight:700;
                       box-shadow:0 3px 10px rgba(228,0,102,0.3);">
            <span class="material-symbols-outlined" style="font-size:20px;">save</span>
            Créer le cours
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const formationSelect = document.getElementById('select_formation');
    const moduleSelect = document.getElementById('select_module');
    const teacherSelect = document.getElementById('select_teacher');

    const allModuleOptions = Array.from(moduleSelect.options).slice(1);
    const allTeacherOptions = Array.from(teacherSelect.options).slice(1);

    function filterModules() {
        const formationId = parseInt(formationSelect.value);
        const currentModuleVal = moduleSelect.value;

        moduleSelect.innerHTML = '';
        const defaultModOpt = document.createElement('option');
        defaultModOpt.value = '';
        defaultModOpt.textContent = formationId ? 'Sélectionner un module' : 'Sélectionner d\'abord une formation...';
        moduleSelect.appendChild(defaultModOpt);

        allModuleOptions.forEach(opt => {
            const formationIds = JSON.parse(opt.getAttribute('data-formation-ids') || '[]');
            if (!formationId || formationIds.includes(formationId)) {
                const clone = opt.cloneNode(true);
                if (clone.value === currentModuleVal) clone.selected = true;
                moduleSelect.appendChild(clone);
            }
        });

        filterTeachers();
        window.refreshAdminSelectSearch?.(moduleSelect);
    }

    function filterTeachers() {
        const moduleId = parseInt(moduleSelect.value);
        const currentTeacherVal = teacherSelect.value;

        teacherSelect.innerHTML = '';
        const defaultTeacherOpt = document.createElement('option');
        defaultTeacherOpt.value = '';
        defaultTeacherOpt.textContent = 'Non assigné';
        teacherSelect.appendChild(defaultTeacherOpt);

        allTeacherOptions.forEach(opt => {
            const moduleIds = JSON.parse(opt.getAttribute('data-module-ids') || '[]');
            if (!moduleId || moduleIds.includes(moduleId)) {
                const clone = opt.cloneNode(true);
                if (clone.value === currentTeacherVal) clone.selected = true;
                teacherSelect.appendChild(clone);
            }
        });
        window.refreshAdminSelectSearch?.(teacherSelect);
    }

    formationSelect.addEventListener('change', filterModules);
    moduleSelect.addEventListener('change', filterTeachers);

    // Filtrage initial
    filterModules();
});
</script>

@endsection
