<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InternshipSite;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * CRUD — Lieux de Stage (répertoire).
 * Périmètre : responsable_site (son site), direction_daa (tous les sites), administrateur.
 */
class InternshipSiteController extends Controller
{
    // ── Liste ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        Gate::authorize('viewAny', InternshipSite::class);

        /** @var User $user */
        $user = auth()->user();
        $isGlobal = $user->hasAnyRole(['administrateur', 'direction_daa', 'direction_daf']);

        $query = InternshipSite::with('site')->orderBy('nom');

        // Scoping par site
        if (! $isGlobal) {
            $ownSiteIds = $user->sites()->pluck('sites.id')->all();
            $query->whereIn('site_id', $ownSiteIds);
        } elseif ($request->filled('site_id')) {
            $query->where('site_id', (int) $request->site_id);
        }

        // Filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nom', 'like', "%{$s}%")
                    ->orWhere('secteur_activite', 'like', "%{$s}%")
                    ->orWhere('commune', 'like', "%{$s}%")
                    ->orWhere('contact_nom', 'like', "%{$s}%");
            });
        }

        $internshipSites = $query->paginate(15)->withQueryString();
        $sites = $isGlobal ? Site::activeSites() : collect();

        return view('admin.internship-sites.index', compact('internshipSites', 'sites', 'isGlobal'));
    }

    // ── Créer ─────────────────────────────────────────────────────────────

    public function create()
    {
        Gate::authorize('create', InternshipSite::class);

        /** @var User $user */
        $user = auth()->user();
        $isGlobal = $user->hasAnyRole(['administrateur', 'direction_daa']);
        $sites = $isGlobal ? Site::activeSites() : $user->sites()->where('est_actif', true)->get();
        $defaultSiteId = $isGlobal ? Site::defaultActiveId() : $sites->first()?->id;

        return view('admin.internship-sites.create', compact('sites', 'isGlobal', 'defaultSiteId'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', InternshipSite::class);

        /** @var User $user */
        $user = auth()->user();
        $isGlobal = $user->hasAnyRole(['administrateur', 'direction_daa']);

        $validated = $this->validateInternshipSite($request, $isGlobal);

        // Forcer le site pour les acteurs à périmètre restreint
        if (! $isGlobal) {
            $ownSite = $user->sites()->where('est_actif', true)->first();
            if (! $ownSite) {
                abort(403, 'Aucun site actif associé à votre compte.');
            }
            $validated['site_id'] = $ownSite->id;
        }

        // Détection de doublon probable (même nom + commune + téléphone)
        $duplicateWarning = null;
        $duplicateExists = InternshipSite::where('nom', $validated['nom'])
            ->where('commune', $validated['commune'])
            ->where('telephone_principal', $validated['telephone_principal'])
            ->exists();
        if ($duplicateExists) {
            $duplicateWarning = 'Un lieu de stage portant le même nom, dans la même commune et avec le même numéro de téléphone existe déjà.';
        }

        $internshipSite = InternshipSite::create($validated);

        $message = "Lieu de stage « {$internshipSite->nom} » créé avec succès.";
        if ($duplicateWarning) {
            $message .= ' Attention : '.$duplicateWarning;
        }

        return redirect()->route('admin.internship-sites.index')
            ->with('success', $message);
    }

    // ── Détail ────────────────────────────────────────────────────────────

    public function show(InternshipSite $internshipSite)
    {
        Gate::authorize('view', $internshipSite);

        $internshipSite->load('site');

        return view('admin.internship-sites.show', compact('internshipSite'));
    }

    // ── Modifier ──────────────────────────────────────────────────────────

    public function edit(InternshipSite $internshipSite)
    {
        Gate::authorize('update', $internshipSite);

        /** @var User $user */
        $user = auth()->user();
        $isGlobal = $user->hasAnyRole(['administrateur', 'direction_daa']);
        $sites = $isGlobal ? Site::activeSites() : $user->sites()->where('est_actif', true)->get();

        $internshipSite->load('site');

        return view('admin.internship-sites.edit', compact('internshipSite', 'sites', 'isGlobal'));
    }

    public function update(Request $request, InternshipSite $internshipSite)
    {
        Gate::authorize('update', $internshipSite);

        /** @var User $user */
        $user = auth()->user();
        $isGlobal = $user->hasAnyRole(['administrateur', 'direction_daa']);

        $validated = $this->validateInternshipSite($request, $isGlobal);

        // Responsable_site ne peut pas changer le site
        if (! $isGlobal) {
            unset($validated['site_id']);
        }

        $internshipSite->update($validated);

        return redirect()->route('admin.internship-sites.show', $internshipSite)
            ->with('success', "Lieu de stage « {$internshipSite->nom} » mis à jour.");
    }

    // ── Supprimer ─────────────────────────────────────────────────────────

    public function destroy(InternshipSite $internshipSite)
    {
        Gate::authorize('delete', $internshipSite);

        $nom = $internshipSite->nom;
        $internshipSite->delete();

        return redirect()->route('admin.internship-sites.index')
            ->with('success', "Lieu de stage « {$nom} » supprimé.");
    }

    // ── Helper validation ─────────────────────────────────────────────────

    /**
     * @return array<string, mixed>
     */
    private function validateInternshipSite(Request $request, bool $isGlobal): array
    {
        $rules = [
            'nom' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'secteur_activite' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'pays' => ['nullable', 'string', 'max:100'],
            'departement' => ['required', 'string', 'max:100'],
            'commune' => ['required', 'string', 'max:100'],
            'quartier' => ['nullable', 'string', 'max:100'],
            'adresse' => ['required', 'string', 'max:255'],
            'repere' => ['nullable', 'string', 'max:255'],
            'contact_nom' => ['required', 'string', 'max:255'],
            'contact_fonction' => ['nullable', 'string', 'max:100'],
            'telephone_principal' => ['required', 'string', 'max:50'],
            'telephone_secondaire' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'site_web' => ['nullable', 'url', 'max:255'],
            'capacite_max' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'statut' => ['required', Rule::in(['actif', 'suspendu', 'archive'])],
        ];

        if ($isGlobal) {
            $rules['site_id'] = ['required', Rule::exists('sites', 'id')->where('est_actif', true)];
        }

        return $request->validate($rules, [
            'nom.required' => 'Le nom du lieu est obligatoire.',
            'type.required' => 'Le type d\'organisation est obligatoire.',
            'secteur_activite.required' => 'Le secteur d\'activité est obligatoire.',
            'departement.required' => 'Le département est obligatoire.',
            'commune.required' => 'La commune est obligatoire.',
            'adresse.required' => 'L\'adresse est obligatoire.',
            'contact_nom.required' => 'Le nom du contact est obligatoire.',
            'telephone_principal.required' => 'Le téléphone principal est obligatoire.',
            'statut.required' => 'Le statut est obligatoire.',
            'statut.in' => 'Le statut doit être : actif, suspendu ou archivé.',
            'site_id.required' => 'Le site IFAM de rattachement est obligatoire.',
            'site_id.exists' => 'Le site sélectionné est invalide ou inactif.',
        ]);
    }
}
