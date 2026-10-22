<?php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\ServiceProfile;
use App\Models\ServiceReport;
use App\Models\Site;
use App\Models\UserAssignment;
use App\Services\NotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Liste des rapports reçus avec filtres.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ServiceReport::class);

        $query = ServiceReport::with(['serviceProfile.department', 'site', 'auteur', 'luParUser'])
            ->visiblesDirection(); // Jamais les brouillons

        // Filtres
        if ($request->filled('department_id')) {
            $query->whereHas('serviceProfile', fn ($q) => $q->where('department_id', $request->department_id));
        }

        if ($request->filled('service_profile_id')) {
            $query->where('service_profile_id', $request->service_profile_id);
        }

        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('periode_debut', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('periode_fin', '<=', $request->date_fin);
        }

        $reports = $query->orderByDesc('envoye_le')->paginate(15)->withQueryString();

        $departments = Department::active()->get();
        $services = ServiceProfile::active()->get();
        $sites = Site::active()->get();

        return view('direction.reports.index', compact('reports', 'departments', 'services', 'sites'));
    }

    /**
     * Consultation du rapport (marqué comme lu à l'ouverture).
     */
    public function show(ServiceReport $report): View
    {
        $this->authorize('view', $report);

        if ($report->statut === ServiceReport::STATUT_ENVOYE) {
            $report->update([
                'statut' => ServiceReport::STATUT_LU,
                'lu_le' => now(),
                'lu_par' => auth()->id(),
            ]);
        }

        $report->load(['serviceProfile.department', 'site', 'auteur', 'luParUser', 'documents.uploadedBy']);

        return view('direction.reports.show', compact('report'));
    }

    /**
     * Retourner pour correction avec commentaire.
     */
    public function returnForCorrection(Request $request, ServiceReport $report): RedirectResponse
    {
        $this->authorize('returnForCorrection', $report);

        $validated = $request->validate([
            'commentaire' => ['required', 'string', 'min:5', 'max:2000'],
        ], [
            'commentaire.required' => 'Le commentaire de correction est obligatoire pour retourner un rapport.',
            'commentaire.min' => 'Le commentaire doit contenir au moins 5 caractères.',
        ]);

        $report->update([
            'statut' => ServiceReport::STATUT_RETOURNE,
            'commentaire_direction' => $validated['commentaire'],
        ]);

        $this->notificationService->rapportRetourne($report);

        // TODO: Journal d'activité non présent - Enregistrer le retour du rapport pour correction.

        return redirect()->route('direction.reports.show', $report)
            ->with('success', 'Le rapport a été retourné pour correction au responsable du service.');
    }

    /**
     * Export PDF du rapport avec DomPDF.
     */
    public function exportPdf(ServiceReport $report): Response
    {
        $this->authorize('view', $report);

        $report->load(['serviceProfile.department', 'site', 'auteur', 'luParUser', 'documents']);

        $pdf = Pdf::loadView('direction.reports.pdf', compact('report'))
            ->setPaper('a4', 'portrait');

        $filename = 'rapport-'.($report->serviceProfile->code ?? 'service').'-'.$report->periode_debut->format('Y-m').'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Page Direction : Liste des rapports manquants par période.
     */
    public function missing(Request $request): View
    {
        $this->authorize('viewAny', ServiceReport::class);

        // Mois sélectionné (par défaut mois précédent)
        $month = $request->input('month', now()->subMonthNoOverflow()->format('Y-m'));
        $date = Carbon::parse($month.'-01');
        $periodeDebut = $date->copy()->startOfMonth()->toDateString();
        $periodeFin = $date->copy()->endOfMonth()->toDateString();

        $departments = Department::with('services')->active()->get();
        $sites = Site::active()->get();

        // Récupérer tous les rapports mensuels envoyés pour cette période
        $sentReports = ServiceReport::where('type', ServiceReport::TYPE_MENSUEL)
            ->where('periode_debut', $periodeDebut)
            ->whereIn('statut', [ServiceReport::STATUT_ENVOYE, ServiceReport::STATUT_LU])
            ->get();

        $missingServices = [];

        foreach ($departments as $dept) {
            foreach ($dept->services as $service) {
                // Trouver les sites assignés pour ce service
                $assignedSites = UserAssignment::where('service_profile_id', $service->id)
                    ->pluck('site_id')
                    ->unique();

                if ($assignedSites->isEmpty()) {
                    $assignedSites = collect([null]);
                }

                foreach ($assignedSites as $siteId) {
                    $hasSent = $sentReports->contains(function ($report) use ($service, $siteId) {
                        return $report->service_profile_id === $service->id &&
                               (is_null($siteId) ? is_null($report->site_id) : $report->site_id == $siteId);
                    });

                    if (! $hasSent) {
                        $site = $siteId ? $sites->firstWhere('id', $siteId) : null;

                        $responsables = UserAssignment::with('user')
                            ->where('service_profile_id', $service->id)
                            ->where('level', 'responsable')
                            ->when($siteId !== null, fn ($q) => $q->where(fn ($sq) => $sq->whereNull('site_id')->orWhere('site_id', $siteId)))
                            ->get()
                            ->pluck('user')
                            ->filter()
                            ->unique('id');

                        $missingServices[] = [
                            'department' => $dept,
                            'service' => $service,
                            'site' => $site,
                            'responsables' => $responsables,
                        ];
                    }
                }
            }
        }

        return view('direction.reports.missing', compact('missingServices', 'month', 'periodeDebut', 'periodeFin'));
    }
}
