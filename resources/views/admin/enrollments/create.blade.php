@extends('layouts.admin')

@section('title', 'Nouvelle Inscription')

@section('content')
<div style="max-width:900px; margin:0 auto; padding-bottom:3rem;">
    {{-- Header --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem;">
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <a href="{{ route('admin.enrollments.index') }}"
               style="display:inline-flex; align-items:center; justify-content:center; width:40px; height:40px; border-radius:0.5rem;
                      background:var(--color-surface-container); color:var(--color-on-surface); text-decoration:none;">
                <span class="material-symbols-outlined" style="font-size:20px;">arrow_back</span>
            </a>
            <div>
                <h1 style="font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0;">
                    Nouvelle inscription
                </h1>
                <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0.25rem 0 0;">
                    Inscrire un étudiant à une formation et calculer les frais de scolarité
                </p>
            </div>
        </div>
    </div>

    {{-- Formulaire --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; border:1px solid var(--color-outline-variant); padding:2rem;">
        <form action="{{ route('admin.enrollments.store') }}" method="POST">
            @csrf

            <div style="display:grid; grid-template-columns:1fr; gap:1.5rem;">
                {{-- Étudiant --}}
                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                        Étudiant <span style="color:#e40066;">*</span>
                    </label>
                    <select name="student_profile_id" required
                            data-searchable-select="Rechercher un étudiant par nom ou matricule…"
                            style="width:100%; height:44px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">
                        <option value="">Sélectionner un étudiant…</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ old('student_profile_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->user->last_name }} {{ $student->user->first_name }} (Matricule: {{ $student->user->matricule ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                    @error('student_profile_id') <span style="color:#dc2626; font-size:0.75rem; display:block; margin-top:0.25rem;">{{ $message }}</span> @enderror
                </div>

                {{-- Parcours, formation et session --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div>
                        <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                            Parcours
                        </label>
                        <select name="parcours_id" id="parcours_select" onchange="filterFormations(); updateCalculations()"
                                style="width:100%; height:44px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">
                            <option value="" {{ old('parcours_id') === null ? 'selected' : '' }}>Formation indépendante / hors parcours</option>
                            @foreach($parcoursList as $parcours)
                                <option value="{{ $parcours->id }}" {{ old('parcours_id') == $parcours->id ? 'selected' : '' }}>
                                    {{ $parcours->nom }} ({{ $parcours->formations->count() }} étape(s))
                                </option>
                            @endforeach
                        </select>
                        @error('parcours_id') <span style="color:#dc2626; font-size:0.75rem; display:block; margin-top:0.25rem;">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                            Formation / étape <span style="color:#e40066;">*</span>
                        </label>
                        <select name="formation_id" id="formation_select" required onchange="updateCalculations()"
                                style="width:100%; height:44px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">
                            <option value="" data-prix="0">Sélectionner une formation…</option>
                            @foreach($formations as $formation)
                                <option value="{{ $formation->id }}"
                                        data-prix="{{ $formation->prix ?? 0 }}"
                                        data-parcours-id="{{ $formation->parcours_id ?? '' }}"
                                        data-ordre="{{ $formation->ordre ?? '' }}"
                                        {{ old('formation_id') == $formation->id ? 'selected' : '' }}>
                                    {{ $formation->ordre ? 'Étape '.$formation->ordre.' · ' : '' }}{{ $formation->name }} ({{ number_format($formation->prix ?? 0, 0, ',', ' ') }} FCFA)
                                </option>
                            @endforeach
                        </select>
                        @error('formation_id') <span style="color:#dc2626; font-size:0.75rem; display:block; margin-top:0.25rem;">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div>
                        <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                            Session académique <span style="color:#e40066;">*</span>
                        </label>
                        <select name="academic_session_id" required
                                style="width:100%; height:44px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">
                            <option value="">Sélectionner une session…</option>
                            @foreach($sessions as $session)
                                <option value="{{ $session->id }}" {{ (old('academic_session_id') == $session->id || ($session->is_current && !old('academic_session_id'))) ? 'selected' : '' }}>
                                    {{ $session->name }} — {{ $session->status === 'terminee' ? 'Terminée' : 'En cours' }}{{ $session->is_current ? ' · session actuelle' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('academic_session_id') <span style="color:#dc2626; font-size:0.75rem; display:block; margin-top:0.25rem;">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Section Bourse & Tarification --}}
                <div style="background:var(--color-surface-container-low); border-radius:0.75rem; padding:1.25rem; border:1px solid var(--color-outline-variant);">
                    <h3 style="font-size:0.9375rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
                        <span class="material-symbols-outlined" style="font-size:20px; color:var(--color-primary);">payments</span>
                        Tarification & Bourse d'études
                    </h3>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                        <div>
                            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                                Type de bourse
                            </label>
                            <select name="bourse_type" id="bourse_type" onchange="updateCalculations()"
                                    style="width:100%; height:44px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">
                                <option value="aucune" {{ old('bourse_type', 'aucune') == 'aucune' ? 'selected' : '' }}>Aucune bourse</option>
                                <option value="pourcentage" {{ old('bourse_type') == 'pourcentage' ? 'selected' : '' }}>Pourcentage (%)</option>
                                <option value="montant" {{ old('bourse_type') == 'montant' ? 'selected' : '' }}>Montant fixe (FCFA)</option>
                            </select>
                            @error('bourse_type') <span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                                Valeur de la bourse <span id="bourse_unit_label"></span>
                            </label>
                            <input type="number" step="0.01" min="0" name="bourse_valeur" id="bourse_valeur" value="{{ old('bourse_valeur', 0) }}" oninput="updateCalculations()"
                                   style="width:100%; height:44px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">
                            @error('bourse_valeur') <span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Résumé automatique --}}
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:0.75rem 1rem; border-radius:0.5rem; background:var(--color-surface); border:1px dashed var(--color-outline-variant);">
                        <div>
                            <span style="font-size:0.8125rem; color:var(--color-on-surface-variant);">Prix initial : </span>
                            <strong id="preview_prix" style="font-size:0.875rem; color:var(--color-on-surface);">0.00 €</strong>
                            <span style="margin:0 0.5rem; color:var(--color-outline-variant);">|</span>
                            <span style="font-size:0.8125rem; color:var(--color-on-surface-variant);">Déduction bourse : </span>
                            <strong id="preview_deduction" style="font-size:0.875rem; color:#16a34a;">0.00 €</strong>
                        </div>
                        <div>
                            <span style="font-size:0.8125rem; color:var(--color-on-surface-variant);">Montant dû : </span>
                            <strong id="preview_montant_du" style="font-size:1.125rem; color:var(--color-primary-container);">0.00 €</strong>
                        </div>
                    </div>
                </div>

                {{-- Date & Statut --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div>
                        <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                            Date d'inscription <span style="color:#e40066;">*</span>
                        </label>
                        <input type="date" name="enrollment_date" value="{{ old('enrollment_date', date('Y-m-d')) }}" required
                               style="width:100%; height:44px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">
                        @error('enrollment_date') <span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                            Statut <span style="color:#e40066;">*</span>
                        </label>
                        <select name="status" required
                                style="width:100%; height:44px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspendue</option>
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Terminée</option>
                            <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Annulée</option>
                        </select>
                        @error('status') <span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                        Notes administratives / Observations
                    </label>
                    <textarea name="notes" rows="3" placeholder="Informations complémentaires, aménagement d'études…"
                              style="width:100%; padding:0.75rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">{{ old('notes') }}</textarea>
                    @error('notes') <span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span> @enderror
                </div>

                {{-- Boutons --}}
                <div style="display:flex; justify-content:flex-end; gap:1rem; margin-top:1rem; border-top:1px solid var(--color-surface-container); padding-top:1.5rem;">
                    <a href="{{ route('admin.enrollments.index') }}"
                       style="display:inline-flex; align-items:center; padding:0.625rem 1.25rem; border-radius:0.5rem;
                              background:var(--color-surface-container); color:var(--color-on-surface); text-decoration:none; font-weight:600; font-size:0.875rem;">
                        Annuler
                    </a>
                    <button type="submit"
                            style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.5rem; border-radius:0.5rem; border:none;
                                   background:var(--color-primary-container); color:var(--color-on-primary); font-weight:700; font-size:0.875rem; cursor:pointer;
                                   box-shadow:0 3px 10px rgba(228,0,102,0.3);">
                        <span class="material-symbols-outlined" style="font-size:18px;">save</span>
                        Enregistrer l'inscription
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
let allFormationOptions = [];

function updateCalculations() {
    const formationSelect = document.getElementById('formation_select');
    const selectedOption = formationSelect.options[formationSelect.selectedIndex];
    const prix = parseFloat(selectedOption ? selectedOption.getAttribute('data-prix') || 0 : 0);

    const bourseType = document.getElementById('bourse_type').value;
    const bourseValeur = parseFloat(document.getElementById('bourse_valeur').value || 0);
    const unitLabel = document.getElementById('bourse_unit_label');

    let deduction = 0;
    if (bourseType === 'pourcentage') {
        unitLabel.textContent = '(%)';
        deduction = prix * (bourseValeur / 100);
    } else if (bourseType === 'montant') {
        unitLabel.textContent = '(€)';
        deduction = bourseValeur;
    } else {
        unitLabel.textContent = '';
        deduction = 0;
    }

    const montantDu = Math.max(0, prix - deduction);

    document.getElementById('preview_prix').textContent = prix.toLocaleString('fr-FR', { maximumFractionDigits: 0 }) + ' FCFA';
    document.getElementById('preview_deduction').textContent = '-' + deduction.toLocaleString('fr-FR', { maximumFractionDigits: 0 }) + ' FCFA';
    document.getElementById('preview_montant_du').textContent = montantDu.toLocaleString('fr-FR', { maximumFractionDigits: 0 }) + ' FCFA';
}

function filterFormations() {
    const parcoursId = document.getElementById('parcours_select').value;
    const formationSelect = document.getElementById('formation_select');
    const previousValue = formationSelect.value;

    formationSelect.innerHTML = '';
    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.dataset.prix = '0';
    placeholder.textContent = 'Sélectionner une formation…';
    formationSelect.appendChild(placeholder);

    allFormationOptions.forEach(option => {
        const matchesParcours = parcoursId
            ? option.dataset.parcoursId === parcoursId
            : !option.dataset.parcoursId;

        if (matchesParcours) {
            const clone = option.cloneNode(true);
            if (clone.value === previousValue) clone.selected = true;
            formationSelect.appendChild(clone);
        }
    });

    if (previousValue && !Array.from(formationSelect.options).some(option => option.value === previousValue)) {
        formationSelect.value = '';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const formationSelect = document.getElementById('formation_select');
    allFormationOptions = Array.from(formationSelect.options).slice(1);
    filterFormations();
    updateCalculations();
});
</script>
@endsection
