<?php

namespace App\Services;

use App\Models\Enrollment;
use Barryvdh\DomPDF\Facade\Pdf;

class BulletinPdf
{
    /**
     * Génère l'instance PDF à partir d'une inscription et de données optionnelles.
     *
     * @param  array{show_grid?: bool, date_edition?: string, appreciation_generale?: string, niveau?: string}  $options
     */
    public function generate(Enrollment $enrollment, array $options = []): \Barryvdh\DomPDF\PDF
    {
        $studentUser = $enrollment->studentProfile?->user;
        $formation = $enrollment->formation;
        $session = $enrollment->academicSession;

        // Calcul de l'année académique
        $anneeAcademique = '';
        if ($session) {
            $startYear = $session->start_date ? $session->start_date->format('Y') : null;
            $endYear = $session->end_date ? $session->end_date->format('Y') : null;
            if ($startYear && $endYear) {
                $anneeAcademique = ($startYear === $endYear) ? $startYear : "{$startYear}-{$endYear}";
            } elseif ($startYear) {
                $anneeAcademique = $startYear;
            } elseif ($endYear) {
                $anneeAcademique = $endYear;
            }
        }

        /** @var BulletinService $bulletinService */
        $bulletinService = app(BulletinService::class);
        $bulletinData = $bulletinService->bulletin(
            $enrollment->studentProfile,
            $formation,
            $session->id
        );

        $modules = [];
        $totalCoefficients = null;
        $totalMoyennesPonderees = null;
        $coefCount = 0;
        $ponderationSum = 0;
        $hasAnyGrade = false;

        if (! empty($bulletinData['modules'])) {
            foreach ($bulletinData['modules'] as $modData) {
                $devoirs = $modData['devoirs'] ?? collect();
                $compNotes = $devoirs
                    ->map(fn ($grade) => $grade->is_absent ? 'ABS' : $grade->score)
                    ->filter(fn ($s) => $s !== null);

                $apprecs = ($modData['controles_continus'] ?? collect())
                    ->concat($devoirs)
                    ->pluck('comments')
                    ->filter()
                    ->unique();

                $coef = $modData['coefficient'] ?? null;
                $moyPond = $modData['moyenne_ponderee'] ?? null;

                if ($coef !== null) {
                    $coefCount += (float) $coef;
                }
                if ($moyPond !== null) {
                    $ponderationSum += (float) $moyPond;
                    $hasAnyGrade = true;
                }

                $modules[] = [
                    'name' => $modData['module']->name ?? '',
                    'coefficient' => $coef,
                    'moyenne_cc' => $modData['moyenne_cc'] ?? null,
                    'composition' => $compNotes->isNotEmpty() ? $compNotes->implode('/') : '',
                    'moyenne' => $modData['moyenne'] ?? null,
                    'moyenne_ponderee' => $moyPond,
                    'appreciation' => $apprecs->isNotEmpty() ? $apprecs->first() : '',
                ];
            }
        }

        if ($hasAnyGrade) {
            $totalCoefficients = $coefCount;
            $totalMoyennesPonderees = $ponderationSum;
        }

        $moyenneGenerale = $bulletinData['moyenne_generale'] ?? null;

        $mention = '';
        $decision = '';
        if ($moyenneGenerale !== null) {
            $mention = match (true) {
                $moyenneGenerale >= 16 => 'Très Bien',
                $moyenneGenerale >= 14 => 'Bien',
                $moyenneGenerale >= 12 => 'Assez Bien',
                $moyenneGenerale >= 10 => 'Passable',
                default => 'Ajourné',
            };
            $decision = ($moyenneGenerale >= 10) ? 'ADMIS(E)' : 'AJOURNÉ(E)';
        }

        $dateEdition = $options['date_edition'] ?? now()->format('d/m/Y');
        $appreciationGenerale = $options['appreciation_generale'] ?? null;
        $showGrid = $options['show_grid'] ?? config('bulletins.show_grid', false);
        $niveau = $options['niveau'] ?? null;

        return $this->renderPdf([
            'studentUser' => $studentUser,
            'formation' => $formation,
            'academicSession' => $session,
            'niveau' => $niveau,
            'anneeAcademique' => $anneeAcademique,
            'modules' => $modules,
            'totalCoefficients' => $totalCoefficients,
            'totalMoyennesPonderees' => $totalMoyennesPonderees,
            'moyenneGenerale' => $moyenneGenerale,
            'mention' => $mention,
            'decision' => $decision,
            'appreciationGenerale' => $appreciationGenerale,
            'dateEdition' => $dateEdition,
            'showGrid' => $showGrid,
        ]);
    }

    /**
     * Rendu HTML en PDF DomPDF depuis la vue officielle partagée.
     * La vue bulletin_wrapper réutilise exactement le même partial que l'admin et l'étudiant.
     */
    protected function renderPdf(array $viewData): \Barryvdh\DomPDF\PDF
    {
        // ── Images en base64 (nécessaire pour DomPDF qui ne peut pas charger des URLs locales) ──
        $viewData['logoRepubliqueBase64'] = $this->imageToBase64(
            file_exists(public_path('img/logo_republique_clean.png'))
                ? public_path('img/logo_republique_clean.png')
                : public_path('img/logoRepublique.jpg'),
            'image/png'
        );

        $viewData['logoIfamBase64'] = $this->imageToBase64(
            file_exists(public_path('img/logo_ifam_clean.png'))
                ? public_path('img/logo_ifam_clean.png')
                : public_path('img/logo.jpg'),
            'image/png'
        );

        $viewData['cachetBase64'] = $this->imageToBase64(
            public_path('img/cachet_dg_transparent.png'),
            'image/png'
        );

        $viewData['signatureBase64'] = $this->imageToBase64(
            public_path('img/signature_dg_transparent.png'),
            'image/png'
        );

        $viewData['watermarkBase64'] = $this->imageToBase64(
            public_path('img/watermark_ifam.png'),
            'image/png'
        );

        // ── On construit $bulletinData dans le format attendu par official.blade.php ──
        // official.blade.php attend $bulletin['modules'] avec les clés: module->name, coefficient,
        // moyenne_cc, devoirs, controles_continus, moyenne, moyenne_ponderee
        // On reconstruitmodules en objets compatibles
        $pdfModules = collect($viewData['modules'] ?? [])->map(function ($mod) {
            return [
                'module' => (object) ['name' => $mod['name'] ?? ''],
                'coefficient' => $mod['coefficient'] ?? null,
                'moyenne_cc' => $mod['moyenne_cc'] ?? null,
                'devoirs' => collect([]), // La composition est déjà préformatée
                'controles_continus' => collect([]),
                'moyenne' => $mod['moyenne'] ?? null,
                'moyenne_ponderee' => $mod['moyenne_ponderee'] ?? null,
                '_composition_str' => $mod['composition'] ?? '',
                '_appreciation_str' => $mod['appreciation'] ?? '',
            ];
        })->toArray();

        $viewData['bulletinData'] = [
            'modules' => $pdfModules,
            'moyenne_generale' => $viewData['moyenneGenerale'] ?? null,
        ];

        // ── Nombre de lignes dynamique (pour @once styles, pas utilisé dans le wrapper) ──
        $moduleCount = count($viewData['modules'] ?? []);
        $viewData['rowHeight'] = ($moduleCount <= 7) ? 8 : max(5, (int) floor(112 / max(1, $moduleCount)));
        $viewData['rowFontSize'] = ($moduleCount <= 7) ? 8 : (($moduleCount <= 10) ? 7 : 6.5);

        /** @var \Barryvdh\DomPDF\PDF $pdf */
        $pdf = Pdf::loadView('pdf.bulletin_wrapper', $viewData);
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
            'dpi' => 150,
            'enable_css_float' => true,
        ]);

        return $pdf;
    }

    /**
     * Convertit un fichier image en data URI base64.
     */
    protected function imageToBase64(string $path, string $mimeType): string
    {
        if (! file_exists($path)) {
            return '';
        }

        return 'data:'.$mimeType.';base64,'.base64_encode(file_get_contents($path));
    }
}
