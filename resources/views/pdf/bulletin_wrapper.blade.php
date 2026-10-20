<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin de note - {{ $studentUser->full_name ?? 'Etudiant' }}</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        html, body {
            margin: 0;
            padding: 0;
            width: 210mm;
            height: 297mm;
            background: #fff;
            font-family: DejaVu Sans, sans-serif;
            -webkit-print-color-adjust: exact;
        }
        * {
            box-sizing: border-box;
        }
        /* Mise en page stricte une seule feuille A4 pour DomPDF */
        html body .official-bulletin {
            position: relative !important;
            width: 210mm !important;
            max-width: 210mm !important;
            min-width: 210mm !important;
            height: 297mm !important;
            max-height: 297mm !important;
            min-height: 297mm !important;
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
            border: none !important;
            border-radius: 0 !important;
            overflow: hidden !important;
            page-break-after: avoid !important;
            page-break-inside: avoid !important;
        }
        html body .official-bulletin__content {
            position: relative !important;
            z-index: 1 !important;
            margin: 0 11mm !important;
            padding: 7mm 0 0 0 !important;
            width: 188mm !important;
            max-width: 188mm !important;
        }
        html body .official-bulletin__watermark-img {
            position: absolute !important;
            top: 65mm !important;
            left: 25mm !important;
            width: 160mm !important;
            height: auto !important;
            z-index: 0 !important;
        }
        html body .official-bulletin__footer-wrap {
            position: absolute !important;
            bottom: 6mm !important;
            left: 0 !important;
            width: 210mm !important;
            max-width: 210mm !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        /* Masquer les boutons et éléments non-print */
        .no-print { display: none !important; }
    </style>
</head>
<body style="margin: 0; padding: 0; overflow: hidden;">
@php
    // Construire $pdfStudent compatible avec official.blade.php ($student->user, $student->niveau)
    $pdfStudent = (object)[
        'user'   => $studentUser,
        'niveau' => $niveau ?? null,
    ];

    // Construire $bulletinData au format attendu par official.blade.php
    // official.blade.php lit $bulletin['modules'] avec: module->name, coefficient,
    // devoirs (Collection), controles_continus (Collection), moyenne_cc, moyenne, moyenne_ponderee
    // Ici les $modules du BulletinPdf ont déjà composition et appreciation pré-formatées,
    // donc on les injecte via _composition_str et _appreciation_str
    $pdfBulletin = [
        'modules' => collect($modules ?? [])->map(fn ($mod) => [
            'module'             => (object)['name' => $mod['name'] ?? ''],
            'coefficient'        => $mod['coefficient'] ?? null,
            'moyenne_cc'         => $mod['moyenne_cc'] ?? null,
            'devoirs'            => collect([]),
            'controles_continus' => collect([]),
            'moyenne'            => $mod['moyenne'] ?? null,
            'moyenne_ponderee'   => $mod['moyenne_ponderee'] ?? null,
            '_composition_str'   => $mod['composition'] ?? '',
            '_appreciation_str'  => $mod['appreciation'] ?? '',
        ])->toArray(),
        'moyenne_generale' => $moyenneGenerale ?? null,
    ];

    // La date d'édition est déjà formatée dans $dateEdition
    $pdfPublishedAt = $dateEdition ?? now()->format('d/m/Y');
@endphp
@include('bulletins.partials.official', [
    'student'               => $pdfStudent,
    'formation'             => $formation,
    'academicSession'       => $academicSession,
    'bulletin'              => $pdfBulletin,
    'publishedAt'           => $pdfPublishedAt,
    'watermarkBase64'       => $watermarkBase64 ?? null,
    'logoRepubliqueBase64'  => $logoRepubliqueBase64 ?? null,
    'logoIfamBase64'        => $logoIfamBase64 ?? null,
    'cachetBase64'          => $cachetBase64 ?? null,
    'signatureBase64'       => $signatureBase64 ?? null,
])</body>
</html>
