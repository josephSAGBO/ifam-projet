<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\ServiceReport;
use App\Models\Site;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Liste des rapports du ou des services de l'utilisateur connecté.
     */
    public function index(): View
    {
        $user = auth()->user();
        $this->authorize('viewAny', ServiceReport::class);

        $accessibleServices = $user->accessibleServiceProfiles();
        $serviceIds = $accessibleServices->pluck('id');

        $reports = ServiceReport::with(['serviceProfile', 'site', 'auteur'])
            ->whereIn('service_profile_id', $serviceIds)
            ->where(function ($query) use ($user, $accessibleServices) {
                // Les brouillons créés par d'autres ne sont visibles que par les responsables du service
                $responsableServiceIds = $accessibleServices
                    ->filter(fn ($s) => $user->isServiceResponsable($s))
                    ->pluck('id');

                $query->where('statut', '!=', ServiceReport::STATUT_BROUILLON)
                    ->orWhere('auteur_id', $user->id)
                    ->orWhereIn('service_profile_id', $responsableServiceIds);
            })
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('service.reports.index', compact('reports'));
    }

    /**
     * Formulaire de création d'un rapport.
     */
    public function create(): View
    {
        $this->authorize('create', ServiceReport::class);

        $user = auth()->user();
        $assignments = $user->assignments()->with(['serviceProfile', 'site'])->get();
        $services = $user->accessibleServiceProfiles();
        $sites = $user->sites()->exists() ? $user->sites : Site::active()->get();

        return view('service.reports.create', compact('assignments', 'services', 'sites'));
    }

    /**
     * Enregistrement d'un nouveau rapport (brouillon ou envoyé).
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ServiceReport::class);

        $validated = $request->validate([
            'service_profile_id' => ['required', 'exists:service_profiles,id'],
            'site_id' => ['nullable', 'exists:sites,id'],
            'type' => ['required', 'in:hebdomadaire,mensuel,ponctuel'],
            'periode_debut' => ['required', 'date'],
            'periode_fin' => ['required', 'date', 'after_or_equal:periode_debut'],
            'titre' => ['required', 'string', 'max:255'],
            'resume' => ['required', 'string'],
            'activites' => ['required', 'string'],
            'difficultes' => ['nullable', 'string'],
            'besoins' => ['nullable', 'string'],
            'indicateurs' => ['nullable', 'array'],
            'indicateurs.*.libelle' => ['nullable', 'string', 'max:255'],
            'indicateurs.*.valeur' => ['nullable', 'string', 'max:255'],
            'attachments.*' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,webp', 'max:10240'],
        ]);

        // Unicité pour les rapports périodiques (hebdomadaire, mensuel)
        if (in_array($validated['type'], [ServiceReport::TYPE_HEBDOMADAIRE, ServiceReport::TYPE_MENSUEL])) {
            $conflict = ServiceReport::where('service_profile_id', $validated['service_profile_id'])
                ->when($validated['site_id'] !== null, fn ($q) => $q->where('site_id', $validated['site_id']), fn ($q) => $q->whereNull('site_id'))
                ->where('type', $validated['type'])
                ->whereDate('periode_debut', $validated['periode_debut'])
                ->exists();

            if ($conflict) {
                return back()->withErrors([
                    'periode_debut' => 'Un rapport périodique de ce type existe déjà pour ce service, ce site et cette date de début.',
                ])->withInput();
            }
        }

        // Nettoyer les indicateurs vides
        $indicateurs = collect($request->input('indicateurs', []))
            ->filter(fn ($item) => ! empty($item['libelle']) || ! empty($item['valeur']))
            ->values()
            ->all();

        $report = new ServiceReport([
            'service_profile_id' => $validated['service_profile_id'],
            'site_id' => $validated['site_id'] ?? null,
            'auteur_id' => auth()->id(),
            'type' => $validated['type'],
            'periode_debut' => $validated['periode_debut'],
            'periode_fin' => $validated['periode_fin'],
            'titre' => $validated['titre'],
            'resume' => $validated['resume'],
            'activites' => $validated['activites'],
            'difficultes' => $validated['difficultes'] ?? null,
            'besoins' => $validated['besoins'] ?? null,
            'indicateurs' => ! empty($indicateurs) ? $indicateurs : null,
            'statut' => ServiceReport::STATUT_BROUILLON,
        ]);

        $report->save();

        // Enregistrement des pièces jointes
        $this->saveAttachments($request, $report);

        // Si l'utilisateur a cliqué sur "Envoyer directement" et a les droits de responsable
        if ($request->input('action') === 'send') {
            if (auth()->user()->can('send', $report)) {
                $report->update([
                    'statut' => ServiceReport::STATUT_ENVOYE,
                    'envoye_le' => now(),
                ]);

                $this->notificationService->rapportEnvoye($report);

                // TODO: Journal d'activité non présent - Enregistrer l'envoi du rapport à la Direction.

                return redirect()->route('service.reports.index')
                    ->with('success', 'Rapport enregistré et transmis avec succès à la Direction.');
            }

            return redirect()->route('service.reports.show', $report)
                ->with('warning', 'Rapport enregistré en brouillon. Seul le responsable de service peut le transmettre à la Direction.');
        }

        return redirect()->route('service.reports.show', $report)
            ->with('success', 'Rapport enregistré avec succès en brouillon.');
    }

    /**
     * Consultation d'un rapport.
     */
    public function show(ServiceReport $report): View
    {
        $this->authorize('view', $report);

        $report->load(['serviceProfile', 'site', 'auteur', 'documents.uploadedBy']);

        return view('service.reports.show', compact('report'));
    }

    /**
     * Formulaire d'édition d'un rapport.
     */
    public function edit(ServiceReport $report): View
    {
        $this->authorize('update', $report);

        $user = auth()->user();
        $assignments = $user->assignments()->with(['serviceProfile', 'site'])->get();
        $services = $user->accessibleServiceProfiles();
        $sites = $user->sites()->exists() ? $user->sites : Site::active()->get();
        $report->load('documents');

        return view('service.reports.edit', compact('report', 'assignments', 'services', 'sites'));
    }

    /**
     * Mise à jour d'un rapport.
     */
    public function update(Request $request, ServiceReport $report): RedirectResponse
    {
        $this->authorize('update', $report);

        $validated = $request->validate([
            'site_id' => ['nullable', 'exists:sites,id'],
            'type' => ['required', 'in:hebdomadaire,mensuel,ponctuel'],
            'periode_debut' => ['required', 'date'],
            'periode_fin' => ['required', 'date', 'after_or_equal:periode_debut'],
            'titre' => ['required', 'string', 'max:255'],
            'resume' => ['required', 'string'],
            'activites' => ['required', 'string'],
            'difficultes' => ['nullable', 'string'],
            'besoins' => ['nullable', 'string'],
            'indicateurs' => ['nullable', 'array'],
            'indicateurs.*.libelle' => ['nullable', 'string', 'max:255'],
            'indicateurs.*.valeur' => ['nullable', 'string', 'max:255'],
            'attachments.*' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,webp', 'max:10240'],
        ]);

        // Unicité pour les rapports périodiques (si modification des dates/types)
        if (in_array($validated['type'], [ServiceReport::TYPE_HEBDOMADAIRE, ServiceReport::TYPE_MENSUEL])) {
            $conflict = ServiceReport::where('service_profile_id', $report->service_profile_id)
                ->when($validated['site_id'] !== null, fn ($q) => $q->where('site_id', $validated['site_id']), fn ($q) => $q->whereNull('site_id'))
                ->where('type', $validated['type'])
                ->whereDate('periode_debut', $validated['periode_debut'])
                ->where('id', '!=', $report->id)
                ->exists();

            if ($conflict) {
                return back()->withErrors([
                    'periode_debut' => 'Un rapport périodique de ce type existe déjà pour ce service, ce site et cette date de début.',
                ])->withInput();
            }
        }

        $indicateurs = collect($request->input('indicateurs', []))
            ->filter(fn ($item) => ! empty($item['libelle']) || ! empty($item['valeur']))
            ->values()
            ->all();

        $report->update([
            'site_id' => $validated['site_id'] ?? null,
            'type' => $validated['type'],
            'periode_debut' => $validated['periode_debut'],
            'periode_fin' => $validated['periode_fin'],
            'titre' => $validated['titre'],
            'resume' => $validated['resume'],
            'activites' => $validated['activites'],
            'difficultes' => $validated['difficultes'] ?? null,
            'besoins' => $validated['besoins'] ?? null,
            'indicateurs' => ! empty($indicateurs) ? $indicateurs : null,
        ]);

        $this->saveAttachments($request, $report);

        if ($request->input('action') === 'send') {
            if (auth()->user()->can('send', $report)) {
                $report->update([
                    'statut' => ServiceReport::STATUT_ENVOYE,
                    'envoye_le' => now(),
                ]);

                $this->notificationService->rapportEnvoye($report);

                // TODO: Journal d'activité non présent - Enregistrer l'envoi du rapport à la Direction.

                return redirect()->route('service.reports.index')
                    ->with('success', 'Rapport mis à jour et transmis à la Direction.');
            }

            return redirect()->route('service.reports.show', $report)
                ->with('warning', 'Rapport mis à jour. Seul un responsable de service peut le transmettre à la Direction.');
        }

        return redirect()->route('service.reports.show', $report)
            ->with('success', 'Rapport mis à jour avec succès.');
    }

    /**
     * Envoi d'un rapport à la Direction (réservé au niveau responsable).
     */
    public function send(ServiceReport $report): RedirectResponse
    {
        $this->authorize('send', $report);

        $report->update([
            'statut' => ServiceReport::STATUT_ENVOYE,
            'envoye_le' => now(),
        ]);

        $this->notificationService->rapportEnvoye($report);

        // TODO: Journal d'activité non présent - Enregistrer l'envoi du rapport à la Direction.

        return redirect()->route('service.reports.index')
            ->with('success', 'Le rapport a été transmis avec succès à la Direction Générale.');
    }

    /**
     * Suppression d'un brouillon par son auteur.
     */
    public function destroy(ServiceReport $report): RedirectResponse
    {
        $this->authorize('delete', $report);

        $report->delete();

        return redirect()->route('service.reports.index')
            ->with('success', 'Le brouillon a été supprimé.');
    }

    /**
     * Sauvegarde des pièces jointes associées.
     */
    protected function saveAttachments(Request $request, ServiceReport $report): void
    {
        if (! $request->hasFile('attachments')) {
            return;
        }

        foreach ($request->file('attachments') as $file) {
            $path = $file->store('service_reports/'.$report->id, 'public');

            $report->documents()->create([
                'title' => $file->getClientOriginalName(),
                'type' => 'other',
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'is_verified' => true,
                'uploaded_by' => auth()->id(),
            ]);
        }
    }
}
