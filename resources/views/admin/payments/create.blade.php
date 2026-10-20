@extends('layouts.admin')
@section('title', 'Nouveau Paiement')
@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.payments.index') }}" style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container); display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Nouveau Paiement</h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">Enregistrer un versement pour une inscription</p>
    </div>
</div>

@if($errors->any())
<div style="padding:1rem; border-radius:0.75rem; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; margin-bottom:1.5rem; font-size:0.875rem; max-width:800px;">
    <div style="font-weight:700; margin-bottom:0.5rem; display:flex; align-items:center; gap:0.5rem;">
        <span class="material-symbols-outlined" style="font-size:20px;">error</span>
        Erreur lors de l'enregistrement :
    </div>
    <ul style="margin:0; padding-left:1.25rem;">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('admin.payments.store') }}" method="POST"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:2rem; max-width:800px; box-shadow:0 1px 6px rgba(11,28,48,0.04); border:1px solid var(--color-outline-variant);">
    @csrf

    <div style="display:grid; gap:1.5rem;">
        {{-- Sélection de l'inscription --}}
        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem; color:var(--color-on-surface);">
                Inscription de l'étudiant <span style="color:#dc2626;">*</span>
            </label>
            <select name="inscription_id" required id="inscriptionSelect" onchange="onInscriptionChange()"
                    data-searchable-select="Rechercher par étudiant, matricule, formation ou session…"
                    style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem; color:var(--color-on-surface);">
                <option value="">Sélectionner une inscription…</option>
                @foreach($enrollments as $enr)
                    @php
                        $solde = $enr->solde;
                        $soldeParcours = $enr->solde_parcours ?? $solde;
                    @endphp
                    <option value="{{ $enr->id }}"
                            data-solde="{{ $solde }}"
                            data-montant-du="{{ $enr->montant_du }}"
                            data-solde-parcours="{{ $soldeParcours }}"
                            {{ (old('inscription_id', $selectedInscriptionId) == $enr->id) ? 'selected' : '' }}>
                        {{ $enr->studentProfile->user->full_name ?? '—' }} ({{ $enr->studentProfile->user->matricule ?? '—' }}) —
                        {{ $enr->formation->parcours->nom ?? 'Formation indépendante' }}
                        · Étape {{ $enr->formation->ordre ?? 1 }} : {{ $enr->formation->name ?? '—' }}
                        · {{ $enr->academicSession->name ?? 'Session non définie' }}
                        (Solde annuel : {{ number_format($solde, 2, ',', ' ') }} FCFA
                        · Solde parcours : {{ number_format($soldeParcours, 2, ',', ' ') }} FCFA
                        · {{ ucfirst($enr->status) }})
                    </option>
                @endforeach
            </select>
            @error('inscription_id') <span style="color:#dc2626; font-size:0.75rem; display:block; margin-top:0.25rem;">{{ $message }}</span> @enderror
        </div>

        {{-- Panneau récapitulatif du solde --}}
        <div id="soldeBanner" style="display:none; background:var(--color-surface-container-low); border-radius:0.75rem; padding:1rem; border:1px dashed var(--color-outline-variant);">
            <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.875rem;">
                <div>
                    <span style="color:var(--color-on-surface-variant);">Montant net de l'année : </span>
                    <strong id="bannerMontantDu" style="color:var(--color-on-surface);">0 FCFA</strong>
                </div>
                <div>
                    <span style="color:var(--color-on-surface-variant);">Solde annuel maximal : </span>
                    <strong id="bannerSolde" style="color:#dc2626; font-size:1.0625rem;">0 FCFA</strong>
                </div>
                <div>
                    <span style="color:var(--color-on-surface-variant);">Solde total du parcours : </span>
                    <strong id="bannerSoldeParcours" style="color:var(--color-primary-container); font-size:1.0625rem;">0 FCFA</strong>
                </div>
            </div>
        </div>

        {{-- Montant, Date et Mode --}}
        <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem; color:var(--color-on-surface);">
                    Montant du versement <span style="color:#dc2626;">*</span>
                </label>
                <div style="position:relative;">
                    <input type="number" name="montant" id="montantInput" value="{{ old('montant') }}" required min="0.01" step="0.01"
                           placeholder="0.00"
                           style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem 0 2rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem; color:var(--color-on-surface);">
                    <span style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); font-weight:600; color:var(--color-on-surface-variant);">FCFA</span>
                </div>
                @error('montant') <span style="color:#dc2626; font-size:0.75rem; display:block; margin-top:0.25rem;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem; color:var(--color-on-surface);">
                    Date de paiement <span style="color:#dc2626;">*</span>
                </label>
                <input type="date" name="date_paiement" value="{{ old('date_paiement', date('Y-m-d')) }}" required
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem; color:var(--color-on-surface);">
                @error('date_paiement') <span style="color:#dc2626; font-size:0.75rem; display:block; margin-top:0.25rem;">{{ $message }}</span> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem; color:var(--color-on-surface);">
                    Mode de règlement <span style="color:#dc2626;">*</span>
                </label>
                <select name="mode" required
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem; color:var(--color-on-surface);">
                    <option value="especes" {{ old('mode') == 'especes' ? 'selected' : '' }}>Espèces</option>
                    <option value="virement" {{ old('mode') == 'virement' ? 'selected' : '' }}>Virement bancaire</option>
                    <option value="carte" {{ old('mode') == 'carte' ? 'selected' : '' }}>Carte bancaire</option>
                    <option value="cheque" {{ old('mode') == 'cheque' ? 'selected' : '' }}>Chèque</option>
                    <option value="autre" {{ old('mode') == 'autre' ? 'selected' : '' }}>Autre</option>
                </select>
                @error('mode') <span style="color:#dc2626; font-size:0.75rem; display:block; margin-top:0.25rem;">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Référence --}}
        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem; color:var(--color-on-surface);">
                Référence / N° de transaction
            </label>
            <input type="text" name="reference" value="{{ old('reference') }}" placeholder="Ex: VIR-2026-0042, CHQ-889021…"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem; color:var(--color-on-surface);">
            @error('reference') <span style="color:#dc2626; font-size:0.75rem; display:block; margin-top:0.25rem;">{{ $message }}</span> @enderror
        </div>

        {{-- Notes --}}
        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem; color:var(--color-on-surface);">
                Remarques / Observations
            </label>
            <textarea name="notes" rows="2" placeholder="Informations complémentaires sur le versement…"
                      style="width:100%; box-sizing:border-box; padding:0.75rem 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem; color:var(--color-on-surface);">{{ old('notes') }}</textarea>
        </div>

        {{-- Boutons --}}
        <div style="display:flex; justify-content:flex-end; gap:1rem; padding-top:1rem; border-top:1px solid var(--color-surface-container);">
            <a href="{{ route('admin.payments.index') }}"
               style="display:inline-flex; align-items:center; padding:0.625rem 1.25rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-on-surface); text-decoration:none; font-weight:600; font-size:0.875rem;">
                Annuler
            </a>
            <button type="submit"
                    style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.5rem; border-radius:0.5rem; border:none; background:var(--color-primary-container); color:var(--color-on-primary); font-weight:700; font-size:0.875rem; cursor:pointer; box-shadow:0 3px 10px rgba(228,0,102,0.3);">
                <span class="material-symbols-outlined" style="font-size:18px;">save</span>
                Valider le versement
            </button>
        </div>
    </div>
</form>

<script>
function onInscriptionChange() {
    const select = document.getElementById('inscriptionSelect');
    const selectedOption = select.options[select.selectedIndex];
    const banner = document.getElementById('soldeBanner');
    const montantInput = document.getElementById('montantInput');

    if (selectedOption && selectedOption.value) {
        const solde = parseFloat(selectedOption.getAttribute('data-solde') || 0);
        const montantDu = parseFloat(selectedOption.getAttribute('data-montant-du') || 0);
        const soldeParcours = parseFloat(selectedOption.getAttribute('data-solde-parcours') || solde);

        document.getElementById('bannerMontantDu').textContent = montantDu.toLocaleString('fr-FR', { maximumFractionDigits: 0 }) + ' FCFA';
        document.getElementById('bannerSolde').textContent = solde.toLocaleString('fr-FR', { maximumFractionDigits: 0 }) + ' FCFA';
        document.getElementById('bannerSoldeParcours').textContent = soldeParcours.toLocaleString('fr-FR', { maximumFractionDigits: 0 }) + ' FCFA';
        banner.style.display = 'block';

        // Définit le max de l'input montant au solde
        montantInput.max = solde.toFixed(2);
        if (!montantInput.value || parseFloat(montantInput.value) > solde) {
            montantInput.placeholder = 'Max: ' + solde.toLocaleString('fr-FR', { maximumFractionDigits: 0 }) + ' FCFA';
        }
    } else {
        banner.style.display = 'none';
        montantInput.removeAttribute('max');
        montantInput.placeholder = '0';
    }
}

document.addEventListener('DOMContentLoaded', onInscriptionChange);
</script>
@endsection
