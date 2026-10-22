<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partnership;
use App\Models\User;
use Illuminate\Http\Request;

class PartnershipController extends Controller
{
    /**
     * IDs des sites gérés par l'utilisateur courant.
     * null = accès global ; tableau = périmètre restreint.
     *
     * @return int[]|null
     */
    protected function managedSiteIds(): ?array
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->hasAnyRole(['administrateur', 'direction', 'direction_daa'])) {
            return null; // accès global
        }

        return $user->sites()->pluck('sites.id')->all();
    }

    /**
     * Contrôle strict d'accès à un partenariat par site.
     */
    protected function authorizePartnershipAccess(Partnership $partnership): void
    {
        $managed = $this->managedSiteIds();

        if ($managed === null) {
            return;
        }

        if (empty($managed)) {
            abort(403, 'Accès refusé : vous n\'avez aucun site attribué.');
        }

        if ($partnership->site_id !== null && ! in_array($partnership->site_id, $managed, true)) {
            abort(403, 'Accès refusé : ce partenariat appartient à un autre site.');
        }
    }

    /**
     * Site par défaut lors de la création (premier site géré, ou null pour accès global).
     */
    protected function resolveDefaultSiteId(): ?int
    {
        $managed = $this->managedSiteIds();

        return $managed === null ? null : ($managed[0] ?? null);
    }

    private function types(): array
    {
        return [
            'Stage' => 'Stage & Immersion',
            'Emploi' => 'Recrutement / Emploi',
            'Materiel' => 'Don / Prêt de Matériel',
            'Financier' => 'Sponsoring / Partenariat Financier',
            'Parrainage' => 'Parrainage de Promotion',
            'Formations' => 'Co-construction de Formations',
            'Autre' => 'Autre type',
        ];
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $type = $request->input('type');
        $statut = $request->input('statut');

        $query = Partnership::query();

        // Scoping multi-sites
        $managed = $this->managedSiteIds();
        if ($managed !== null) {
            $query->whereIn('site_id', $managed);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->orWhere('secteur_intervention', 'like', "%{$search}%")
                    ->orWhere('personne_ressource_interne', 'like', "%{$search}%")
                    ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        if ($type) {
            $query->where('type_partenariat', $type);
        }

        if ($statut) {
            $query->where('statut', $statut);
        }

        $partnerships = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        $types = $this->types();

        return view('admin.partnerships.index', compact('partnerships', 'search', 'type', 'statut', 'types'));
    }

    public function create()
    {
        $types = $this->types();

        return view('admin.partnerships.create', compact('types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'type_partenariat' => 'required|string|max:100',
            'duree' => 'nullable|string|max:255',
            'secteur_intervention' => 'nullable|string|max:255',
            'ce_qu_ils_offrent' => 'nullable|string',
            'ce_qu_on_offre' => 'nullable|string',
            'personne_ressource_interne' => 'nullable|string|max:255',
            'suivi' => 'nullable|string',
            'date_dernier_contact' => 'nullable|date',
            'statut' => 'required|string|in:Actif,En discussion,Inactif,Suspendu',
        ]);

        // Rattacher automatiquement au premier site géré pour les rôles à périmètre restreint
        $defaultSiteId = $this->resolveDefaultSiteId();
        if ($defaultSiteId !== null && empty($validated['site_id'])) {
            $validated['site_id'] = $defaultSiteId;
        }

        Partnership::create($validated);

        return redirect()->route('admin.partnerships.index')
            ->with('success', 'Partenariat enregistré avec succès.');
    }

    public function show(Partnership $partnership)
    {
        $this->authorizePartnershipAccess($partnership);

        return view('admin.partnerships.show', compact('partnership'));
    }

    public function edit(Partnership $partnership)
    {
        $this->authorizePartnershipAccess($partnership);
        $types = $this->types();

        return view('admin.partnerships.edit', compact('partnership', 'types'));
    }

    public function update(Request $request, Partnership $partnership)
    {
        $this->authorizePartnershipAccess($partnership);

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'type_partenariat' => 'required|string|max:100',
            'duree' => 'nullable|string|max:255',
            'secteur_intervention' => 'nullable|string|max:255',
            'ce_qu_ils_offrent' => 'nullable|string',
            'ce_qu_on_offre' => 'nullable|string',
            'personne_ressource_interne' => 'nullable|string|max:255',
            'suivi' => 'nullable|string',
            'date_dernier_contact' => 'nullable|date',
            'statut' => 'required|string|in:Actif,En discussion,Inactif,Suspendu',
        ]);

        $partnership->update($validated);

        return redirect()->route('admin.partnerships.index')
            ->with('success', 'Partenariat mis à jour avec succès.');
    }

    public function destroy(Partnership $partnership)
    {
        $this->authorizePartnershipAccess($partnership);
        $partnership->delete();

        return redirect()->route('admin.partnerships.index')
            ->with('success', 'Partenariat supprimé.');
    }
}
