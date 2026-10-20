<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partnership;
use Illuminate\Http\Request;

class PartnershipController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $type = $request->input('type');
        $statut = $request->input('statut');

        $query = Partnership::query();

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

        $types = [
            'Stage' => 'Stage & Immersion',
            'Emploi' => 'Recrutement / Emploi',
            'Materiel' => 'Don / Prêt de Matériel',
            'Financier' => 'Sponsoring / Partenariat Financier',
            'Parrainage' => 'Parrainage de Promotion',
            'Formations' => 'Co-construction de Formations',
            'Autre' => 'Autre type',
        ];

        return view('admin.partnerships.index', compact('partnerships', 'search', 'type', 'statut', 'types'));
    }

    public function create()
    {
        $types = [
            'Stage' => 'Stage & Immersion',
            'Emploi' => 'Recrutement / Emploi',
            'Materiel' => 'Don / Prêt de Matériel',
            'Financier' => 'Sponsoring / Partenariat Financier',
            'Parrainage' => 'Parrainage de Promotion',
            'Formations' => 'Co-construction de Formations',
            'Autre' => 'Autre type',
        ];

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

        Partnership::create($validated);

        return redirect()->route('admin.partnerships.index')
            ->with('success', 'Partenariat enregistré avec succès.');
    }

    public function show(Partnership $partnership)
    {
        return view('admin.partnerships.show', compact('partnership'));
    }

    public function edit(Partnership $partnership)
    {
        $types = [
            'Stage' => 'Stage & Immersion',
            'Emploi' => 'Recrutement / Emploi',
            'Materiel' => 'Don / Prêt de Matériel',
            'Financier' => 'Sponsoring / Partenariat Financier',
            'Parrainage' => 'Parrainage de Promotion',
            'Formations' => 'Co-construction de Formations',
            'Autre' => 'Autre type',
        ];

        return view('admin.partnerships.edit', compact('partnership', 'types'));
    }

    public function update(Request $request, Partnership $partnership)
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

        $partnership->update($validated);

        return redirect()->route('admin.partnerships.index')
            ->with('success', 'Partenariat mis à jour avec succès.');
    }

    public function destroy(Partnership $partnership)
    {
        $partnership->delete();

        return redirect()->route('admin.partnerships.index')
            ->with('success', 'Partenariat supprimé.');
    }
}
