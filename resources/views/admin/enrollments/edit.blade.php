@extends('layouts.admin')

@section('title', 'Modifier l\'inscription')

@section('content')

<div style="max-width:48rem; margin:0 auto; padding-bottom:3rem;">
    <div style="margin-bottom:1.5rem;">
        <a href="{{ route('admin.enrollments.index') }}"
           style="display:inline-flex; align-items:center; gap:0.375rem; font-size:0.875rem; color:var(--color-on-surface-variant); text-decoration:none; margin-bottom:0.75rem;">
            <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
            Retour aux inscriptions
        </a>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Modifier l'Inscription
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Étudiant : <strong>{{ $enrollment->studentProfile->user->full_name ?? '—' }}</strong> ({{ $enrollment->studentProfile->user->matricule ?? '—' }})
        </p>
    </div>

    @if($errors->any())
    <div style="padding:1rem; border-radius:0.75rem; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; margin-bottom:1.5rem; font-size:0.875rem;">
        <div style="font-weight:700; margin-bottom:0.5rem; display:flex; align-items:center; gap:0.5rem;">
            <span class="material-symbols-outlined" style="font-size:20px;">error</span>
            Veuillez corriger les erreurs suivantes :
        </div>
        <ul style="margin:0; padding-left:1.25rem;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:2rem; border:1px solid var(--color-outline-variant);">
        <form method="POST" action="{{ route('admin.enrollments.update', $enrollment) }}">
            @csrf
            @method('PUT')

            <div style="display:flex; flex-direction:column; gap:1.5rem;">
                {{-- Infos fixes --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; padding:1rem; background:var(--color-surface-container-low); border-radius:0.5rem;">
                    <div>
                        <span style="font-size:0.75rem; color:var(--color-on-surface-variant); display:block;">Formation</span>
                        <strong style="font-size:0.9375rem; color:var(--color-on-surface);">{{ $enrollment->formation->name ?? '—' }}</strong>
                    </div>
                    <div>
                        <span style="font-size:0.75rem; color:var(--color-on-surface-variant); display:block;">Session académique</span>
                        <strong style="font-size:0.9375rem; color:var(--color-on-surface);">{{ $enrollment->academicSession->name ?? '—' }}</strong>
                    </div>
                </div>

                {{-- Bourse & Tarification --}}
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
                                <option value="aucune" {{ old('bourse_type', $enrollment->bourse_type ?? 'aucune') == 'aucune' ? 'selected' : '' }}>Aucune bourse</option>
                                <option value="pourcentage" {{ old('bourse_type', $enrollment->bourse_type) == 'pourcentage' ? 'selected' : '' }}>Pourcentage (%)</option>
                                <option value="montant" {{ old('bourse_type', $enrollment->bourse_type) == 'montant' ? 'selected' : '' }}>Montant fixe (FCFA)</option>
                            </select>
                        </div>

                        <div>
                            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                                Valeur de la bourse <span id="bourse_unit_label"></span>
                            </label>
                            <input type="number" step="0.01" min="0" name="bourse_valeur" id="bourse_valeur"
                                   value="{{ old('bourse_valeur', $enrollment->bourse_valeur ?? 0) }}" oninput="updateCalculations()"
                                   style="width:100%; height:44px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">
                        </div>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; padding:0.75rem 1rem; border-radius:0.5rem; background:var(--color-surface); border:1px dashed var(--color-outline-variant);">
                        <div>
                            <span style="font-size:0.8125rem; color:var(--color-on-surface-variant);">Prix formation : </span>
                            <strong style="font-size:0.875rem; color:var(--color-on-surface);">{{ number_format($enrollment->prix_formation, 2, ',', ' ') }} FCFA</strong>
                            <span style="margin:0 0.5rem; color:var(--color-outline-variant);">|</span>
                            <span style="font-size:0.8125rem; color:var(--color-on-surface-variant);">Déduction : </span>
                            <strong id="preview_deduction" style="font-size:0.875rem; color:#16a34a;">0.00 FCFA</strong>
                        </div>
                        <div>
                            <span style="font-size:0.8125rem; color:var(--color-on-surface-variant);">Montant dû : </span>
                            <strong id="preview_montant_du" style="font-size:1.125rem; color:var(--color-primary-container);">{{ number_format($enrollment->montant_du, 2, ',', ' ') }} FCFA</strong>
                        </div>
                    </div>
                </div>

                {{-- Date & Statut --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div>
                        <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                            Date d'inscription <span style="color:#e40066;">*</span>
                        </label>
                        <input type="date" name="enrollment_date" value="{{ old('enrollment_date', $enrollment->enrollment_date ? $enrollment->enrollment_date->format('Y-m-d') : '') }}" required
                               style="width:100%; height:44px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">
                    </div>

                    <div>
                        <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                            Statut <span style="color:#e40066;">*</span>
                        </label>
                        <select name="status" required
                                style="width:100%; height:44px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">
                            <option value="active" {{ old('status', $enrollment->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ old('status', $enrollment->status) == 'suspended' ? 'selected' : '' }}>Suspendue</option>
                            <option value="completed" {{ old('status', $enrollment->status) == 'completed' ? 'selected' : '' }}>Terminée</option>
                            <option value="cancelled" {{ old('status', $enrollment->status) == 'cancelled' ? 'selected' : '' }}>Annulée</option>
                        </select>
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                        Notes administratives / Observations
                    </label>
                    <textarea name="notes" rows="3"
                              style="width:100%; padding:0.75rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">{{ old('notes', $enrollment->notes) }}</textarea>
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
                        Mettre à jour
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
const prix = {{ (float) ($enrollment->prix_formation ?? 0) }};

function updateCalculations() {
    const bourseType = document.getElementById('bourse_type').value;
    const bourseValeur = parseFloat(document.getElementById('bourse_valeur').value || 0);
    const unitLabel = document.getElementById('bourse_unit_label');

    let deduction = 0;
    if (bourseType === 'pourcentage') {
        unitLabel.textContent = '(%)';
        deduction = prix * (bourseValeur / 100);
    } else if (bourseType === 'montant') {
        unitLabel.textContent = '(FCFA)';
        deduction = bourseValeur;
    } else {
        unitLabel.textContent = '';
        deduction = 0;
    }

    const montantDu = Math.max(0, prix - deduction);

    document.getElementById('preview_deduction').textContent = '-' + deduction.toFixed(2) + ' FCFA';
    document.getElementById('preview_montant_du').textContent = montantDu.toFixed(2) + ' FCFA';
}

document.addEventListener('DOMContentLoaded', updateCalculations);
</script>
@endsection
