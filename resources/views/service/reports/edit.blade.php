@extends('layouts.role')
@section('title', 'Modifier le Rapport — ' . $report->titre)
@section('role_label', 'Espace Service')
@section('home_route', route('service.reports.index'))

@section('nav_links')
<a href="{{ route('service.reports.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">assignment</span> Mes Rapports
</a>
<a href="{{ route('service.reports.create') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; color:var(--color-on-surface-variant); text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">add_circle</span> Nouveau Rapport
</a>
@if(auth()->user()->hasRole('responsable_commercial'))
<a href="{{ route('admin.enrollments.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; color:var(--color-on-surface-variant); text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span> Inscriptions
</a>
@elseif(auth()->user()->hasAnyRole(['administrateur', 'direction_daf', 'direction_daa', 'responsable_site', 'responsable_administratif_financier', 'responsable_daa']))
<a href="{{ route('admin.dashboard') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; color:var(--color-on-surface-variant); text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span> Administration
</a>
@endif
<a href="{{ route('contact.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; color:var(--color-on-surface-variant); text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">mail</span> Contact
</a>
@endsection

@section('content')
<div style="max-width:960px; margin:0 auto; padding:1.5rem;">
    <div style="margin-bottom:1.5rem;">
        <a href="{{ route('service.reports.show', $report) }}" style="display:inline-flex; align-items:center; gap:0.375rem; color:var(--color-primary-container); text-decoration:none; font-weight:700; font-size:0.875rem; margin-bottom:0.5rem;">
            <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
            Retour au rapport
        </a>
        <h1 style="font-size:1.5rem; font-weight:800; color:var(--color-on-surface); margin:0;">Modifier le rapport d'activité</h1>
    </div>

    {{-- Remarque de la Direction si retourné --}}
    @if($report->statut === 'retourne' && $report->commentaire_direction)
    <div style="padding:1.25rem; border-radius:0.75rem; background:#fff7ed; border:1px solid #fed7aa; margin-bottom:1.5rem;">
        <div style="display:flex; align-items:center; gap:0.5rem; color:#c2410c; font-weight:700; margin-bottom:0.5rem;">
            <span class="material-symbols-outlined">feedback</span>
            Instructions de correction de la Direction Générale
        </div>
        <p style="margin:0; font-size:0.875rem; color:#9a3412;">
            {{ $report->commentaire_direction }}
        </p>
    </div>
    @endif

    @if($errors->any())
    <div style="padding:1rem; border-radius:0.5rem; background:#fef2f2; border:1px solid #fecaca; margin-bottom:1.5rem;">
        <div style="font-weight:700; color:#b91c1c; margin-bottom:0.25rem;">Veuillez corriger les erreurs suivantes :</div>
        <ul style="margin:0; padding-left:1.25rem; color:#b91c1c; font-size:0.875rem;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('service.reports.update', $report) }}" enctype="multipart/form-data" style="background:var(--color-surface-container-lowest); border-radius:0.75rem; padding:1.75rem; box-shadow:0 1px 3px rgba(0,0,0,0.05); display:flex; flex-direction:column; gap:1.5rem;">
        @csrf
        @method('PUT')

        {{-- Section 1 : Périmètre & Type --}}
        <div>
            <h2 style="font-size:1rem; font-weight:800; color:var(--color-on-surface); margin:0 0 1rem; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.5rem;">
                1. Identification du rapport
            </h2>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:1rem;">
                <div>
                    <label style="display:block; font-size:0.8125rem; font-weight:700; margin-bottom:0.375rem;">Service concerné</label>
                    <input type="text" value="{{ $report->serviceProfile->name }}" disabled style="width:100%; padding:0.625rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface-variant);">
                </div>

                <div>
                    <label style="display:block; font-size:0.8125rem; font-weight:700; margin-bottom:0.375rem;">Site (Campus)</label>
                    <select name="site_id" style="width:100%; padding:0.625rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); font-size:0.875rem;">
                        <option value="">Tous les sites (Siège / Global)</option>
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}" {{ old('site_id', $report->site_id) == $site->id ? 'selected' : '' }}>
                                {{ $site->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:0.8125rem; font-weight:700; margin-bottom:0.375rem;">Type de rapport <span style="color:#ef4444;">*</span></label>
                    <select name="type" required style="width:100%; padding:0.625rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); font-size:0.875rem;">
                        <option value="mensuel" {{ old('type', $report->type) == 'mensuel' ? 'selected' : '' }}>Mensuel</option>
                        <option value="hebdomadaire" {{ old('type', $report->type) == 'hebdomadaire' ? 'selected' : '' }}>Hebdomadaire</option>
                        <option value="ponctuel" {{ old('type', $report->type) == 'ponctuel' ? 'selected' : '' }}>Ponctuel / Événement</option>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:1rem; margin-top:1rem;">
                <div>
                    <label style="display:block; font-size:0.8125rem; font-weight:700; margin-bottom:0.375rem;">Période début <span style="color:#ef4444;">*</span></label>
                    <input type="date" name="periode_debut" value="{{ old('periode_debut', $report->periode_debut->toDateString()) }}" required style="width:100%; padding:0.625rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); font-size:0.875rem;">
                </div>
                <div>
                    <label style="display:block; font-size:0.8125rem; font-weight:700; margin-bottom:0.375rem;">Période fin <span style="color:#ef4444;">*</span></label>
                    <input type="date" name="periode_fin" value="{{ old('periode_fin', $report->periode_fin->toDateString()) }}" required style="width:100%; padding:0.625rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); font-size:0.875rem;">
                </div>
                <div>
                    <label style="display:block; font-size:0.8125rem; font-weight:700; margin-bottom:0.375rem;">Titre / Objet synthétique <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="titre" value="{{ old('titre', $report->titre) }}" required style="width:100%; padding:0.625rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); font-size:0.875rem;">
                </div>
            </div>
        </div>

        {{-- Section 2 : Contenu détaillé --}}
        <div>
            <h2 style="font-size:1rem; font-weight:800; color:var(--color-on-surface); margin:0 0 1rem; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.5rem;">
                2. Contenu & Réalisations
            </h2>

            <div style="margin-bottom:1rem;">
                <label style="display:block; font-size:0.8125rem; font-weight:700; margin-bottom:0.375rem;">Résumé exécutif <span style="color:#ef4444;">*</span></label>
                <textarea name="resume" rows="3" required style="width:100%; padding:0.625rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); font-size:0.875rem;">{{ old('resume', $report->resume) }}</textarea>
            </div>

            <div style="margin-bottom:1rem;">
                <label style="display:block; font-size:0.8125rem; font-weight:700; margin-bottom:0.375rem;">Activités & Réalisations <span style="color:#ef4444;">*</span></label>
                <textarea name="activites" rows="5" required style="width:100%; padding:0.625rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); font-size:0.875rem;">{{ old('activites', $report->activites) }}</textarea>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1rem;">
                <div>
                    <label style="display:block; font-size:0.8125rem; font-weight:700; margin-bottom:0.375rem;">Difficultés rencontrées</label>
                    <textarea name="difficultes" rows="4" style="width:100%; padding:0.625rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); font-size:0.875rem;">{{ old('difficultes', $report->difficultes) }}</textarea>
                </div>
                <div>
                    <label style="display:block; font-size:0.8125rem; font-weight:700; margin-bottom:0.375rem;">Besoins & Appuis sollicités</label>
                    <textarea name="besoins" rows="4" style="width:100%; padding:0.625rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); font-size:0.875rem;">{{ old('besoins', $report->besoins) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Section 3 : Indicateurs chiffrés --}}
        <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.5rem;">
                <h2 style="font-size:1rem; font-weight:800; color:var(--color-on-surface); margin:0;">
                    3. Indicateurs clés de performance
                </h2>
                <button type="button" onclick="addIndicatorRow()" style="padding:0.375rem 0.75rem; border-radius:0.375rem; background:var(--color-surface-container); color:var(--color-primary-container); border:none; font-weight:700; font-size:0.75rem; cursor:pointer; display:inline-flex; align-items:center; gap:0.25rem;">
                    <span class="material-symbols-outlined" style="font-size:16px;">add</span>
                    Ajouter un indicateur
                </button>
            </div>

            <div id="indicators-container" style="display:flex; flex-direction:column; gap:0.75rem;">
                @php
                    $indicators = old('indicateurs', $report->indicateurs ?? [['libelle' => '', 'valeur' => '']]);
                @endphp
                @foreach($indicators as $idx => $ind)
                <div class="indicator-row" style="display:flex; gap:0.75rem; align-items:center;">
                    <input type="text" name="indicateurs[{{ $idx }}][libelle]" value="{{ $ind['libelle'] ?? '' }}" placeholder="Libellé" style="flex:2; padding:0.5rem; border-radius:0.375rem; border:1px solid var(--color-outline-variant); font-size:0.8125rem;">
                    <input type="text" name="indicateurs[{{ $idx }}][valeur]" value="{{ $ind['valeur'] ?? '' }}" placeholder="Valeur" style="flex:1; padding:0.5rem; border-radius:0.375rem; border:1px solid var(--color-outline-variant); font-size:0.8125rem;">
                    <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:#ef4444; cursor:pointer; padding:0.25rem;">
                        <span class="material-symbols-outlined" style="font-size:20px;">close</span>
                    </button>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Section 4 : Pièces jointes --}}
        <div>
            <h2 style="font-size:1rem; font-weight:800; color:var(--color-on-surface); margin:0 0 0.5rem; border-bottom:1px solid var(--color-surface-container); padding-bottom:0.5rem;">
                4. Pièces jointes & Documents justificatifs
            </h2>
            @if($report->documents->count() > 0)
            <div style="margin-bottom:0.75rem;">
                <div style="font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Documents actuels :</div>
                <ul style="margin:0; padding-left:1.25rem; font-size:0.8125rem;">
                    @foreach($report->documents as $doc)
                        <li>{{ $doc->original_name }} ({{ $doc->file_size_human }})</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <p style="font-size:0.75rem; color:var(--color-on-surface-variant); margin:0 0 0.75rem;">
                Ajouter de nouvelles pièces jointes (PDF, Word, Excel, images, 10 Mo max) :
            </p>
            <input type="file" name="attachments[]" multiple style="padding:0.5rem; border:1px dashed var(--color-outline-variant); border-radius:0.5rem; width:100%; background:var(--color-surface-container-low); font-size:0.8125rem;">
        </div>

        {{-- Boutons d'action --}}
        <div style="display:flex; justify-content:flex-end; gap:0.75rem; border-top:1px solid var(--color-surface-container); padding-top:1.25rem; flex-wrap:wrap;">
            <button type="submit" name="action" value="draft" style="padding:0.75rem 1.25rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-on-surface); border:none; font-weight:700; font-size:0.875rem; cursor:pointer;">
                Enregistrer les modifications
            </button>
            <button type="submit" name="action" value="send" onclick="return confirm('Confirmez-vous la transmission directe de ce rapport à la Direction Générale ?');" style="padding:0.75rem 1.5rem; border-radius:0.5rem; background:#16a34a; color:#ffffff; border:none; font-weight:700; font-size:0.875rem; cursor:pointer; display:inline-flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="font-size:18px;">send</span>
                Enregistrer et envoyer à la Direction
            </button>
        </div>
    </form>
</div>

<script>
let indicatorIndex = {{ count(old('indicateurs', $report->indicateurs ?? [])) + 1 }};
function addIndicatorRow() {
    const container = document.getElementById('indicators-container');
    const div = document.createElement('div');
    div.className = 'indicator-row';
    div.style = 'display:flex; gap:0.75rem; align-items:center;';
    div.innerHTML = `
        <input type="text" name="indicateurs[${indicatorIndex}][libelle]" placeholder="Libellé" style="flex:2; padding:0.5rem; border-radius:0.375rem; border:1px solid var(--color-outline-variant); font-size:0.8125rem;">
        <input type="text" name="indicateurs[${indicatorIndex}][valeur]" placeholder="Valeur" style="flex:1; padding:0.5rem; border-radius:0.375rem; border:1px solid var(--color-outline-variant); font-size:0.8125rem;">
        <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:#ef4444; cursor:pointer; padding:0.25rem;">
            <span class="material-symbols-outlined" style="font-size:20px;">close</span>
        </button>
    `;
    container.appendChild(div);
    indicatorIndex++;
}
</script>
@endsection
