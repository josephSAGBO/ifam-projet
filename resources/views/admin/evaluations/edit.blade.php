@extends('layouts.admin')

@section('title', 'Modifier un Devoir')

@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.evaluations.index') }}"
       style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
              display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
              transition:all 0.15s;"
       onmouseover="this.style.background='var(--color-surface-container-high)'"
       onmouseout="this.style.background='var(--color-surface-container)'">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Modifier {{ $evaluation->titre }}
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Mettre à jour ce devoir
        </p>
    </div>
</div>

<form action="{{ route('admin.evaluations.update', $evaluation->id) }}" method="POST"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:2rem; max-width:800px;">
    @csrf
    @method('PUT')

    <div style="display:grid; grid-template-columns:1fr; gap:1.5rem;">
        
        {{-- Formation & Session --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 18rem), 1fr)); gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Formation <span style="color:#dc2626;">*</span>
                </label>
                <select id="select_formation" name="formation_id" required
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                               border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                               font-size:0.9375rem; color:var(--color-on-surface);"
                        onfocus="this.style.borderColor='var(--color-primary-container)'"
                        onblur="this.style.borderColor='var(--color-outline-variant)'">
                    <option value="">Sélectionner une formation</option>
                    @foreach($formations as $formation)
                        <option value="{{ $formation->id }}"
                            {{ old('formation_id', $evaluation->module?->formations->first()?->id) == $formation->id ? 'selected' : '' }}>
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
                <select name="session_id" required
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                               border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                               font-size:0.9375rem; color:var(--color-on-surface);"
                        onfocus="this.style.borderColor='var(--color-primary-container)'"
                        onblur="this.style.borderColor='var(--color-outline-variant)'">
                    <option value="">Sélectionner une session</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}" {{ old('session_id', $evaluation->session_id) == $session->id ? 'selected' : '' }}>
                            {{ $session->name }}
                        </option>
                    @endforeach
                </select>
                @error('session_id') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Module (filtré par Formation) --}}
        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Module <span style="color:#dc2626;">*</span>
            </label>
            <select id="select_module" name="module_id" required
                    style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                           border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                           font-size:0.9375rem; color:var(--color-on-surface);"
                    onfocus="this.style.borderColor='var(--color-primary-container)'"
                    onblur="this.style.borderColor='var(--color-outline-variant)'">
                <option value="">Sélectionner d'abord une formation...</option>
                @foreach($modules as $module)
                    <option value="{{ $module->id }}"
                            data-formation-ids="{{ json_encode($module->formations->pluck('id')) }}"
                            {{ old('module_id', $evaluation->module_id) == $module->id ? 'selected' : '' }}>
                        {{ $module->code }} — {{ $module->name }}
                    </option>
                @endforeach
            </select>
            @error('module_id') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
        </div>

        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Enseignant responsable <span style="color:#dc2626;">*</span>
            </label>
            <select id="select_teacher" name="assigned_teacher_profile_id" required
                    style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem; color:var(--color-on-surface);">
                <option value="">Sélectionner d'abord un module…</option>
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}"
                            data-module-ids="{{ json_encode($teacher->modules->pluck('id')->map(fn ($id) => (int) $id)) }}"
                            {{ old('assigned_teacher_profile_id', $evaluation->assigned_teacher_profile_id) == $teacher->id ? 'selected' : '' }}>
                        {{ $teacher->user->full_name ?? 'Enseignant sans nom' }}
                    </option>
                @endforeach
            </select>
            @error('assigned_teacher_profile_id') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
        </div>

        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Titre du devoir <span style="color:#dc2626;">*</span>
            </label>
            <input type="text" name="titre" value="{{ old('titre', $evaluation->titre) }}" required
                   placeholder="ex: Devoir 1"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                          border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                          font-size:0.9375rem; color:var(--color-on-surface);"
                   onfocus="this.style.borderColor='var(--color-primary-container)'"
                   onblur="this.style.borderColor='var(--color-outline-variant)'">
            @error('titre') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
        </div>
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Date
                </label>
                <input type="date" name="date" value="{{ old('date', $evaluation->date ? $evaluation->date->format('Y-m-d') : '') }}"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                              border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                              font-size:0.9375rem; color:var(--color-on-surface);"
                       onfocus="this.style.borderColor='var(--color-primary-container)'"
                       onblur="this.style.borderColor='var(--color-outline-variant)'">
                @error('date') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                    Statut <span style="color:#dc2626;">*</span>
                </label>
                <select name="statut" required
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem;
                               border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low);
                               font-size:0.9375rem; color:var(--color-on-surface);"
                        onfocus="this.style.borderColor='var(--color-primary-container)'"
                        onblur="this.style.borderColor='var(--color-outline-variant)'">
                    <option value="brouillon" {{ old('statut', $evaluation->statut) == 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                    <option value="ouverte" {{ old('statut', $evaluation->statut) == 'ouverte' ? 'selected' : '' }}>Ouverte</option>
                    <option value="cloturee" {{ old('statut', $evaluation->statut) == 'cloturee' ? 'selected' : '' }}>Clôturée</option>
                </select>
                @error('statut') <span style="color:#dc2626; font-size:0.75rem; margin-top:0.25rem; display:block;">{{ $message }}</span> @enderror
            </div>
        </div>

    </div>

    <div style="margin-top:2rem; display:flex; justify-content:flex-end; gap:1rem;">
        <a href="{{ route('admin.evaluations.index') }}"
           style="height:44px; display:inline-flex; align-items:center; padding:0 1.25rem; border-radius:0.75rem;
                  background:transparent; color:var(--color-on-surface-variant); font-size:0.9375rem; font-weight:600; text-decoration:none;">
            Annuler
        </a>
        <button type="submit" class="interactive-hover"
                style="height:44px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.5rem; border-radius:0.75rem; border:none; cursor:pointer;
                       background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.9375rem; font-weight:700;
                       box-shadow:0 3px 10px rgba(228,0,102,0.3);">
            <span class="material-symbols-outlined" style="font-size:20px;">save</span>
            Enregistrer
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const formationSelect = document.getElementById('select_formation');
    const moduleSelect = document.getElementById('select_module');
    const teacherSelect = document.getElementById('select_teacher');

    const initialModuleVal = "{{ old('module_id', $evaluation->module_id) }}";
    const allModuleOptions = Array.from(moduleSelect.options).slice(1);
    const allTeacherOptions = Array.from(teacherSelect.options).slice(1);

    function filterTeachers() {
        const moduleId = parseInt(moduleSelect.value);
        const currentTeacherId = teacherSelect.value || "{{ old('assigned_teacher_profile_id', $evaluation->assigned_teacher_profile_id) }}";

        teacherSelect.innerHTML = '';
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = moduleId ? 'Sélectionner un enseignant…' : 'Sélectionner d\'abord un module…';
        teacherSelect.appendChild(placeholder);

        allTeacherOptions.forEach(option => {
            const moduleIds = JSON.parse(option.getAttribute('data-module-ids') || '[]');
            if (moduleId && moduleIds.includes(moduleId)) {
                const clone = option.cloneNode(true);
                if (clone.value === currentTeacherId) clone.selected = true;
                teacherSelect.appendChild(clone);
            }
        });

        if (currentTeacherId && !Array.from(teacherSelect.options).some(option => option.value === currentTeacherId)) {
            teacherSelect.value = '';
        }
    }

    function filterModules() {
        const formationId = parseInt(formationSelect.value);
        const currentModuleVal = moduleSelect.value || initialModuleVal;

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
    }

    formationSelect.addEventListener('change', filterModules);
    moduleSelect.addEventListener('change', filterTeachers);
    filterModules();
});
</script>

@endsection
