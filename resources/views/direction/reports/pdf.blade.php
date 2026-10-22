<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $report->titre }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 11pt;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #002b49;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .institution-title {
            font-size: 14pt;
            font-weight: bold;
            color: #002b49;
            text-transform: uppercase;
        }
        .institution-sub {
            font-size: 9pt;
            color: #64748b;
        }
        .report-badge {
            display: inline-block;
            background: #002b49;
            color: #ffffff;
            font-size: 8pt;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .meta-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 9.5pt;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 3px 6px;
            vertical-align: top;
        }
        .meta-label {
            font-weight: bold;
            color: #475569;
            width: 25%;
        }
        h2 {
            font-size: 12pt;
            font-weight: bold;
            color: #002b49;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin-top: 18px;
            margin-bottom: 8px;
        }
        .content-block {
            font-size: 10pt;
            text-align: justify;
            white-space: pre-line;
            margin-bottom: 12px;
        }
        table.indicators {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 16px;
            font-size: 9.5pt;
        }
        table.indicators th {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
            font-weight: bold;
            color: #0f172a;
        }
        table.indicators td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
        }
        .signatures {
            margin-top: 40px;
            width: 100%;
            border-collapse: collapse;
        }
        .signatures td {
            width: 50%;
            vertical-align: top;
            font-size: 9.5pt;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width:100%;">
            <tr>
                <td>
                    <div class="institution-title">Institut IFAM</div>
                    <div class="institution-sub">Institut de Formation et d'Appui au Management — IFAM Connect</div>
                </td>
                <td style="text-align:right;">
                    <span class="report-badge">Rapport {{ $report->type }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="meta-box">
        <table class="meta-table">
            <tr>
                <td class="meta-label">Titre du rapport :</td>
                <td style="font-weight:bold; color:#0f172a;">{{ $report->titre }}</td>
                <td class="meta-label">Date transmission :</td>
                <td>{{ $report->envoye_le ? $report->envoye_le->format('d/m/Y H:i') : 'Non transmis' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Département :</td>
                <td>{{ $report->serviceProfile->department->name ?? 'Non défini' }}</td>
                <td class="meta-label">Période :</td>
                <td>Du {{ $report->periode_debut->format('d/m/Y') }} au {{ $report->periode_fin->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="meta-label">Service :</td>
                <td>{{ $report->serviceProfile->name }} ({{ $report->serviceProfile->code }})</td>
                <td class="meta-label">Site :</td>
                <td>{{ $report->site->nom ?? 'Tous sites (Siège)' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Rédacteur :</td>
                <td>{{ $report->auteur->name }} ({{ $report->auteur->email }})</td>
                <td class="meta-label">Statut :</td>
                <td style="text-transform:uppercase; font-weight:bold;">{{ $report->statut }}</td>
            </tr>
        </table>
    </div>

    <h2>1. Résumé synthétique</h2>
    <div class="content-block">{{ $report->resume }}</div>

    <h2>2. Activités & Réalisations de la période</h2>
    <div class="content-block">{{ $report->activites }}</div>

    @if(!empty($report->indicateurs) && count($report->indicateurs) > 0)
    <h2>3. Indicateurs clés de performance</h2>
    <table class="indicators">
        <thead>
            <tr>
                <th style="width:70%;">Indicateur</th>
                <th style="width:30%;">Valeur / Réalisation</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report->indicateurs as $indicator)
            <tr>
                <td>{{ $indicator['libelle'] ?? '—' }}</td>
                <td style="font-weight:bold;">{{ $indicator['valeur'] ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <h2>4. Difficultés rencontrées</h2>
    <div class="content-block">{{ $report->difficultes ?: 'Néant.' }}</div>

    <h2>5. Besoins & Appuis sollicités</h2>
    <div class="content-block">{{ $report->besoins ?: 'Néant.' }}</div>

    @if($report->documents->count() > 0)
    <h2>6. Pièces jointes annexées</h2>
    <ul>
        @foreach($report->documents as $doc)
        <li>{{ $doc->original_name ?? $doc->title }} ({{ $doc->file_size_human }})</li>
        @endforeach
    </ul>
    @endif

    <table class="signatures">
        <tr>
            <td>
                <strong>Le Responsable du Service</strong><br>
                <div style="font-size:8pt; color:#64748b; margin-top:2px;">Signature & Visa</div>
                <div style="height:50px;"></div>
                {{ $report->auteur->name }}
            </td>
            <td style="text-align:right;">
                <strong>Pour la Direction Générale</strong><br>
                <div style="font-size:8pt; color:#64748b; margin-top:2px;">Visa de validation</div>
                <div style="height:50px;"></div>
                {{ $report->luParUser ? $report->luParUser->name : 'Direction' }}
            </td>
        </tr>
    </table>
</body>
</html>
