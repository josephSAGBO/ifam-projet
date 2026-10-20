@php
    $studentUser = $student->user ?? null;
    // Support both $bulletin and $bulletinData (from PDF wrapper)
    $bulletinSource = $bulletinData ?? $bulletin ?? [];
    $bulletinModules = collect($bulletinSource['modules'] ?? []);

    // Année académique
    $anneeAcademique = '';
    if (!empty($academicSession)) {
        $startYear = $academicSession->start_date ? $academicSession->start_date->format('Y') : null;
        $endYear = $academicSession->end_date ? $academicSession->end_date->format('Y') : null;
        if ($startYear && $endYear) {
            $anneeAcademique = ($startYear === $endYear) ? $startYear : "{$startYear}-{$endYear}";
        } elseif ($startYear) {
            $anneeAcademique = $startYear;
        } elseif ($endYear) {
            $anneeAcademique = $endYear;
        } else {
            $anneeAcademique = $academicSession->name ?? '';
        }
    }

    // Calculs notes
    $modulesData = [];
    $totalCoefficients = 0;
    $totalMoyennesPonderees = 0;
    $hasGrades = false;

    foreach ($bulletinModules as $mod) {
        $devoirs = $mod['devoirs'] ?? collect();
        $compNotes = $devoirs
            ->map(fn ($grade) => $grade->is_absent ? 'ABS' : $grade->score)
            ->filter(fn ($s) => $s !== null);

        $apprecs = ($mod['controles_continus'] ?? collect())
            ->concat($devoirs)
            ->pluck('comments')
            ->filter()
            ->unique();

        $coef = $mod['coefficient'] ?? null;
        $moyPond = $mod['moyenne_ponderee'] ?? null;

        if ($coef !== null) {
            $totalCoefficients += (float) $coef;
        }
        if ($moyPond !== null) {
            $totalMoyennesPonderees += (float) $moyPond;
            $hasGrades = true;
        }

        $modulesData[] = [
            'name' => $mod['module']->name ?? '',
            'coefficient' => $coef,
            'moyenne_cc' => $mod['moyenne_cc'] ?? null,
            'composition' => $compNotes->isNotEmpty() ? $compNotes->implode('/') : ($mod['_composition_str'] ?? ''),
            'moyenne' => $mod['moyenne'] ?? null,
            'moyenne_ponderee' => $moyPond,
            'appreciation' => $apprecs->isNotEmpty() ? $apprecs->first() : ($mod['_appreciation_str'] ?? ''),
        ];
    }

    $moyenneGenerale = $bulletinSource['moyenne_generale'] ?? ($totalCoefficients > 0 && $hasGrades ? $totalMoyennesPonderees / $totalCoefficients : null);

    $mention = '—';
    $decision = '—';
    if ($moyenneGenerale !== null) {
        $mention = match(true) {
            $moyenneGenerale >= 16 => 'Très Bien',
            $moyenneGenerale >= 14 => 'Bien',
            $moyenneGenerale >= 12 => 'Assez Bien',
            $moyenneGenerale >= 10 => 'Passable',
            default => 'Ajourné',
        };
        $decision = ($moyenneGenerale >= 10) ? 'ADMIS(E)' : 'AJOURNÉ(E)';
    }

    $appreciationPedagogique = match(true) {
        $moyenneGenerale === null => 'En attente de délibération.',
        $moyenneGenerale >= 14 => 'Excellent travail d\'ensemble. Félicitations pour ces résultats très satisfaisants.',
        $moyenneGenerale >= 12 => 'Bon travail régulier. Continuez avec le même engagement.',
        $moyenneGenerale >= 10 => 'Résultats acceptables. Des efforts complémentaires sont recommandés pour progresser.',
        default => 'Résultats insuffisants. Travaillez avec assiduité et persévérance.',
    };

    $dateEdition = isset($publishedAt) && $publishedAt
        ? (is_string($publishedAt) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $publishedAt)
            ? $publishedAt
            : \Carbon\Carbon::parse($publishedAt)->locale('fr')->translatedFormat('d/m/Y'))
        : now()->locale('fr')->translatedFormat('d/m/Y');

    $moduleCount = count($modulesData);
    $emptyRowCount = max(0, 8 - $moduleCount);
@endphp

<article class="official-bulletin" aria-label="Bulletin de notes officiel IFAM">

    {{-- Filigrane officiel IFAM BENIN --}}
    <img class="official-bulletin__watermark-img"
         src="{{ $watermarkBase64 ?? asset('img/watermark_ifam.png') }}"
         alt="IFAM BENIN"
         aria-hidden="true">

    <div class="official-bulletin__content">

        {{-- En-tête Logos --}}
        <header class="official-bulletin__header">
            <table class="official-bulletin__header-table">
                <tr>
                    <td class="header-logo-left">
                        <img src="{{ $logoRepubliqueBase64 ?? asset('img/logo_republique_clean.png') }}" alt="République du Bénin - Ministère">
                    </td>
                    <td class="header-logo-right">
                        <img src="{{ $logoIfamBase64 ?? asset('img/logo_ifam_clean.png') }}" alt="Logo IFAM">
                    </td>
                </tr>
            </table>
        </header>

        {{-- Titre --}}
        <h1 class="official-bulletin__title">BULLETIN DE NOTE</h1>

        {{-- Identification de l'étudiant --}}
        <section class="official-bulletin__student-info" aria-label="Identification de l'étudiant">
            <div class="info-line">
                <span class="label">NOM &amp; PRENOMS&nbsp;:</span>
                <span class="value">{{ strtoupper($studentUser->full_name ?? '—') }}</span>
            </div>
            <div class="info-line">
                <span class="label">N&eacute; (e)&nbsp;:</span>
                <span class="value">
                    @if(!empty($studentUser->birth_date))
                        {{ $studentUser->birth_date instanceof \DateTimeInterface ? $studentUser->birth_date->format('d/m/Y') : $studentUser->birth_date }}
                    @else
                        —
                    @endif
                </span>
            </div>
            <table class="info-grid">
                <tr>
                    <td class="info-col info-col-left">
                        <span class="label">FILLIERE&nbsp;:</span>
                        <span class="value">{{ $formation->name ?? '—' }}</span>
                    </td>
                    <td class="info-col info-col-right">
                        <span class="label">ANNEE&nbsp;:</span>
                        <span class="value">{{ $student->niveau ?? '1ère Année' }}</span>
                    </td>
                </tr>
            </table>
            <table class="info-grid">
                <tr>
                    <td class="info-col info-col-left">
                        <span class="label">PROMOTION&nbsp;:</span>
                        <span class="value">{{ $academicSession->name ?? '—' }}</span>
                    </td>
                    <td class="info-col info-col-right">
                        <span class="label">ANNEE ACADEMIQUE&nbsp;:</span>
                        <span class="value">{{ $anneeAcademique }}</span>
                    </td>
                </tr>
            </table>
        </section>

        {{-- Tableau des notes --}}
        <section class="official-bulletin__grades-section" aria-label="Tableau des notes">
            <table class="official-bulletin__table">
                <thead>
                    <tr>
                        <th class="col-mod">MODULES</th>
                        <th class="col-coef">COEF</th>
                        <th class="col-moy-int">MOY.INT</th>
                        <th class="col-comp">COMP</th>
                        <th class="col-moy-gen">MOY.G&Eacute;N</th>
                        <th class="col-moy-coef"><span style="display:none;">MOY. COEF</span>MOY.<br>COEF</th>
                        <th class="col-apprec">APPRECIATION</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($modulesData as $mod)
                        <tr>
                            <td class="col-mod">{{ $mod['name'] }}</td>
                            <td class="col-coef">{{ $mod['coefficient'] !== null ? sprintf('%02d', (int)$mod['coefficient']) : '' }}</td>
                            <td class="col-moy-int">{{ $mod['moyenne_cc'] !== null ? number_format((float)$mod['moyenne_cc'], 2, ',', '') : '' }}</td>
                            <td class="col-comp">{{ $mod['composition'] ?? '' }}</td>
                            <td class="col-moy-gen">{{ $mod['moyenne'] !== null ? number_format((float)$mod['moyenne'], 2, ',', '') : '' }}</td>
                            <td class="col-moy-coef">{{ $mod['moyenne_ponderee'] !== null ? number_format((float)$mod['moyenne_ponderee'], 2, ',', '') : '' }}</td>
                            <td class="col-apprec">{{ $mod['appreciation'] ?? '' }}</td>
                        </tr>
                    @endforeach

                    @for($i = 0; $i < $emptyRowCount; $i++)
                        <tr class="row-empty">
                            <td class="col-mod">&nbsp;</td>
                            <td class="col-coef"></td>
                            <td class="col-moy-int"></td>
                            <td class="col-comp"></td>
                            <td class="col-moy-gen"></td>
                            <td class="col-moy-coef"></td>
                            <td class="col-apprec"></td>
                        </tr>
                    @endfor
                </tbody>
                <tfoot>
                    <tr class="row-total">
                        <td class="col-mod cell-total-label">TOTAL</td>
                        <td class="col-coef cell-total-coef">{{ $hasGrades ? sprintf('%02d', (int)$totalCoefficients) : '' }}</td>
                        <td class="col-moy-int"></td>
                        <td class="col-comp"></td>
                        <td class="col-moy-gen"></td>
                        <td class="col-moy-coef">{{ $hasGrades ? number_format((float)$totalMoyennesPonderees, 2, ',', '') : '' }}</td>
                        <td class="col-apprec"></td>
                    </tr>
                    <tr class="row-average">
                        <td colspan="4" class="cell-avg-label">MOYENNE GENERALE</td>
                        <td class="cell-avg-val">{{ $moyenneGenerale !== null ? number_format((float)$moyenneGenerale, 2, ',', '') : '' }}</td>
                        <td class="cell-avg-empty" colspan="2" style="border: none !important; border-top: none !important; border-bottom: none !important; border-right: none !important; background: transparent !important;"></td>
                    </tr>
                </tfoot>
            </table>
        </section>

        {{-- Cadres Récapitulatif & Décision (Deux panneaux hermétiquement séparés: 54.4% et 37.1%, espace vide de 8.5%) --}}
        <table class="official-bulletin__recap-decision-container">
            <tr>
                <td class="recap-pane">
                    <table class="recap-table">
                        <thead>
                            <tr>
                                <th colspan="2">R&Eacute;CAPITULATIF</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="label">Total des Coefficients</td>
                                <td class="val">{{ $hasGrades ? (int)$totalCoefficients : '' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Total des Moyennes Coeffici&eacute;es</td>
                                <td class="val">{{ $hasGrades ? number_format((float)$totalMoyennesPonderees, 2, ',', '') : '' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Mention</td>
                                <td class="val">{{ $mention }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td class="recap-decision-spacer"></td>
                <td class="decision-pane">
                    <table class="decision-table">
                        <thead>
                            <tr>
                                <th>DECISION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="decision-val-cell">
                                    <span class="decision-val">{{ $decision }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Cadre Appréciation Générale (Centré à 72.4% comme sur le canevas) --}}
        <section class="official-bulletin__pedago-card" aria-label="Appréciation générale">
            <div class="pedago-header">
                APPR&Eacute;CIATION G&Eacute;N&Eacute;RALE DU DEPARTEMENTS DES ETUDES<br>
                SERVICE P&Eacute;DAGOGIQUE
            </div>
            <div class="pedago-body">
                {{ $appreciationPedagogique }}
            </div>
        </section>

        {{-- Zone Signature --}}
        <table class="official-bulletin__signature-section" aria-label="Signatures officielles">
            <tr>
                <td class="sig-date-col">
                    <div class="sig-date">
                        Fait &agrave; Cotonou, le {{ $dateEdition }}
                    </div>
                </td>
                <td class="sig-authority-col">
                    <div class="sig-authority">
                        <div class="sig-title">Le Directeur G&eacute;n&eacute;ral</div>
                        <div class="sig-images">
                            <img class="sig-stamp" src="{{ $cachetBase64 ?? asset('img/cachet_dg_transparent.png') }}" alt="Cachet officiel IFAM">
                            <img class="sig-sign" src="{{ $signatureBase64 ?? asset('img/signature_dg_transparent.png') }}" alt="Signature du Directeur">
                        </div>
                        <div class="sig-name">M. Thomas Koffi TETEH</div>
                    </div>
                </td>
            </tr>
        </table>

    </div>

    {{-- Pied de page (bandeau rose + coordonnées) --}}
    <div class="official-bulletin__footer-wrap">
        <div class="official-bulletin__pink-band"></div>
        <footer class="official-bulletin__footer">
            <div>IFU&nbsp;: 0202234361105&nbsp;; RCCM&nbsp;: RB/COT/22 A 640653 T&eacute;l&nbsp;: 01 49 31 06 61 / 01 58 84 01 50</div>
            <div>Email&nbsp;: <a href="mailto:institutifamofficiel@gmail.com" class="footer-email">institutifamofficiel@gmail.com</a></div>
            <div>Num&eacute;ro d&apos;autorisation&nbsp;: N&deg; 035/MESTFP/DC/SGM/DPAF/SGSI/SA/030SGG24.</div>
        </footer>
    </div>

</article>

@once
<style>
    .official-bulletin,
    .student-content .official-bulletin {
        position: relative !important;
        width: 100% !important;
        max-width: 820px !important;
        margin: 0 auto !important;
        background: #ffffff !important;
        color: #000000 !important;
        font-family: DejaVu Sans, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif !important;
        box-shadow: 0 4px 24px rgba(15, 23, 42, 0.12) !important;
        border: none !important;
        border-radius: 4px !important;
        box-sizing: border-box !important;
        overflow: hidden !important;
        transform: none !important;
        transition: none !important;
    }

    .official-bulletin:hover,
    .student-content .official-bulletin:hover {
        border: none !important;
        box-shadow: 0 4px 24px rgba(15, 23, 42, 0.12) !important;
        transform: none !important;
    }

    .official-bulletin table,
    .student-content .official-bulletin table {
        min-width: 0 !important;
        max-width: 100% !important;
    }

    .official-bulletin th,
    .student-content .official-bulletin th {
        color: #000000 !important;
        letter-spacing: normal !important;
        text-transform: none !important;
    }

    .official-bulletin__watermark-img {
        position: absolute;
        top: 23.5%;
        left: 15.2%;
        width: 69.2%;
        height: auto;
        pointer-events: none;
        z-index: 0;
        user-select: none;
    }

    .official-bulletin__content {
        position: relative;
        z-index: 1;
        padding: 24px 34px 20px 34px;
        box-sizing: border-box;
    }

    /* ═══ En-tête Logos ═══ */
    .official-bulletin__header {
        margin-bottom: 8px;
        width: 100%;
    }
    .official-bulletin__header-table {
        width: 100%;
        border-collapse: collapse;
        border: none;
        margin: 0;
        padding: 0;
    }
    .official-bulletin__header-table td {
        border: none;
        padding: 0;
        vertical-align: top;
    }
    .header-logo-left {
        width: 60%;
        text-align: left;
    }
    .header-logo-left img {
        width: 230px;
        max-width: 100%;
        height: auto;
        display: block;
    }
    .header-logo-right {
        width: 40%;
        text-align: right;
    }
    .header-logo-right img {
        width: 105px;
        max-width: 100%;
        height: auto;
        display: inline-block;
    }

    /* ═══ Titre ═══ */
    .official-bulletin__title {
        text-align: center;
        font-size: 15px;
        font-weight: 800;
        letter-spacing: 0.05em;
        margin: 6px 0 12px 0;
        color: #000;
    }

    /* ═══ Informations Étudiant ═══ */
    .official-bulletin__student-info {
        font-size: 11px;
        line-height: 1.45;
        margin-bottom: 12px;
    }
    .info-line {
        margin-bottom: 4px;
    }
    .info-grid {
        width: 100%;
        border-collapse: collapse;
        border: none;
        margin-bottom: 4px;
    }
    .info-grid td {
        border: none;
        padding: 0;
        vertical-align: top;
    }
    .info-col-left, .info-col:first-child { width: 58%; }
    .info-col-right, .info-col:last-child  { width: 42%; }
    .official-bulletin__student-info .label {
        font-weight: 800;
    }
    .official-bulletin__student-info .value {
        font-weight: 700;
        margin-left: 6px;
    }

    /* ═══ Tableau Principal ═══ */
    .official-bulletin__grades-section {
        width: 100%;
        margin-bottom: 12px;
    }
    .official-bulletin__table {
        width: 100%;
        border-collapse: collapse;
        border: 1.5px solid #000;
        table-layout: fixed;
        font-size: 10.5px;
    }
    .official-bulletin__table th,
    .official-bulletin__table td {
        border: 1px solid #000;
        padding: 4px 5px;
        text-align: center;
        vertical-align: middle;
        background: transparent;
        color: #000;
    }
    .official-bulletin__table thead th {
        font-weight: 800;
        font-size: 10px;
        padding: 6px 4px;
    }
    /* Mesures exactes du canevas */
    .col-mod      { width: 31.84%; text-align: left !important; padding-left: 7px !important; }
    .col-coef     { width: 8.16%; }
    .col-moy-int  { width: 10.41%; }
    .col-comp     { width: 8.37%; }
    .col-moy-gen  { width: 11.63%; }
    .col-moy-coef { width: 8.37%; }
    .col-apprec   { width: 21.22%; text-align: left !important; padding-left: 6px !important; }

    .official-bulletin__table tbody tr {
        height: 25px;
    }
    .row-empty {
        height: 25px;
    }

    .row-total td, .row-average td {
        font-weight: 800;
        height: 26px;
    }
    .cell-total-label {
        font-weight: 800 !important;
        text-align: left !important;
        padding-left: 7px !important;
    }
    .cell-total-coef {
        font-weight: 800 !important;
    }
    .cell-avg-label {
        text-align: center !important;
        font-weight: 800 !important;
        letter-spacing: 0.05em;
    }
    .cell-avg-val {
        font-weight: 800 !important;
    }
    .official-bulletin__table td.cell-avg-empty {
        border: none !important;
        border-top: none !important;
        border-left: none !important;
        border-right: none !important;
        border-bottom: none !important;
        background: transparent !important;
        outline: none !important;
    }

    /* ═══ Récapitulatif & Décision (Deux blocs hermétiquement séparés) ═══ */
    .official-bulletin__recap-decision-container {
        width: 100% !important;
        border-collapse: collapse !important;
        border: none !important;
        margin: 12px 0 !important;
        box-sizing: border-box !important;
        table-layout: fixed !important;
    }
    .official-bulletin__recap-decision-container td {
        border: none !important;
        padding: 0 !important;
        vertical-align: top !important;
    }
    .recap-pane {
        width: 54.4% !important;
        box-sizing: border-box !important;
    }
    .recap-decision-spacer {
        width: 8.5% !important;
    }
    .decision-pane {
        width: 37.1% !important;
        box-sizing: border-box !important;
    }

    .recap-table {
        width: 100% !important;
        border-collapse: collapse !important;
        border: 1.5px solid #000 !important;
        font-size: 10.5px !important;
        table-layout: fixed !important;
        box-sizing: border-box !important;
    }
    .recap-table th, .recap-table td {
        border: 1px solid #000 !important;
        padding: 4px 7px !important;
        box-sizing: border-box !important;
    }
    .recap-table th {
        font-weight: 800 !important;
        font-size: 10px !important;
        text-align: center !important;
        background: transparent !important;
        height: 24px !important;
        padding: 4px 6px !important;
        letter-spacing: 0.04em !important;
    }
    .recap-table td.label {
        width: 68% !important;
        font-weight: 800 !important;
        height: 24px !important;
        text-align: left !important;
    }
    .recap-table td.val {
        width: 32% !important;
        text-align: center !important;
        font-weight: 800 !important;
        height: 24px !important;
    }

    .decision-table {
        width: 100% !important;
        border-collapse: collapse !important;
        border: 1.5px solid #000 !important;
        font-size: 10.5px !important;
        table-layout: fixed !important;
        box-sizing: border-box !important;
        height: 96px !important;
    }
    .decision-table th {
        border: none !important;
        border-bottom: 1px solid #000 !important;
        font-weight: 800 !important;
        font-size: 10px !important;
        text-align: center !important;
        background: transparent !important;
        height: 24px !important;
        padding: 4px 6px !important;
        letter-spacing: 0.04em !important;
        box-sizing: border-box !important;
    }
    .decision-val-cell {
        border: none !important;
        height: 72px !important;
        text-align: center !important;
        vertical-align: middle !important;
        padding: 8px 6px !important;
        box-sizing: border-box !important;
    }
    .decision-val {
        font-size: 15px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.06em !important;
        color: #000 !important;
        display: inline-block !important;
    }

    /* ═══ Appréciation Générale (Centré 72.4%) ═══ */
    .official-bulletin__pedago-card {
        width: 72.4%;
        margin: 0 auto 12px auto;
        border: 1.5px solid #000;
        box-sizing: border-box;
    }
    .pedago-header {
        border-bottom: 1px solid #000;
        font-weight: 800;
        font-size: 9.5px;
        text-align: center;
        padding: 4px 8px;
        line-height: 1.25;
    }
    .pedago-body {
        padding: 8px 12px;
        font-size: 10.5px;
        font-style: italic;
        text-align: center;
        line-height: 1.35;
        min-height: 38px;
        box-sizing: border-box;
    }

    /* ═══ Signatures ═══ */
    .official-bulletin__signature-section {
        width: 100% !important;
        border-collapse: collapse !important;
        border: none !important;
        margin-bottom: 12px !important;
    }
    .official-bulletin__signature-section td {
        border: none !important;
        padding: 0 !important;
        vertical-align: bottom !important;
    }
    .sig-date-col {
        width: 58% !important;
        text-align: left !important;
        vertical-align: bottom !important;
    }
    .sig-authority-col {
        width: 42% !important;
        text-align: right !important;
        vertical-align: bottom !important;
    }
    .sig-date {
        font-size: 11px;
        padding-left: 28px;
        padding-bottom: 12px;
    }
    .sig-authority {
        width: 100%;
        text-align: center;
    }
    .sig-title {
        font-weight: 800;
        font-size: 11px;
        margin-bottom: 2px;
    }
    .sig-images {
        position: relative;
        height: 85px;
        width: 170px;
        margin: 0 auto;
    }
    .sig-stamp {
        position: absolute;
        top: 0;
        left: 45px;
        width: 78px;
        height: auto;
        opacity: 0.95;
    }
    .sig-sign {
        position: absolute;
        top: 6px;
        left: 12px;
        width: 115px;
        height: auto;
        opacity: 0.90;
    }
    .sig-name {
        font-weight: 800;
        font-size: 11px;
        margin-top: 2px;
    }

    /* ═══ Bandeau Rose & Pied de page ═══ */
    .official-bulletin__footer-wrap {
        width: 100%;
        margin-top: 12px;
        padding: 0;
    }
    .official-bulletin__pink-band {
        width: 100% !important;
        height: 12px !important;
        background-color: #E6007E !important;
        margin-bottom: 8px !important;
    }
    .official-bulletin__footer {
        width: 100% !important;
        text-align: center !important;
        font-size: 8.5px !important;
        font-weight: 700 !important;
        line-height: 1.5 !important;
        color: #000 !important;
    }
    .footer-email {
        color: #0066cc;
        text-decoration: underline;
    }

    /* ═══ Impression Papier A4 ═══ */
    @media print {
        @page {
            size: A4 portrait;
            margin: 6mm 10mm 5mm 10mm;
        }
        html, body {
            background: #fff !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            height: auto !important;
        }
        .role-topbar,
        .role-navigation,
        header.role-topbar,
        .bulletin-actions,
        .no-print,
        #flash-success,
        #flash-error {
            display: none !important;
        }
        main,
        main.role-main-content,
        .student-content {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
            min-height: auto !important;
            background: transparent !important;
        }
        body * {
            visibility: hidden;
        }
        .official-bulletin,
        .official-bulletin * {
            visibility: visible;
        }
        .official-bulletin,
        .student-content .official-bulletin {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            height: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            overflow: hidden !important;
            page-break-after: avoid !important;
            page-break-inside: avoid !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .official-bulletin__content {
            padding: 0 !important;
        }
        .official-bulletin__footer-wrap {
            width: 100% !important;
            margin-top: 12px !important;
            padding: 0 !important;
        }
    }
</style>
@endonce
