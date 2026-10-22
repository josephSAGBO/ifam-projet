<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function index(): View
    {
        $sites = Site::withCount(['academicSessions', 'enrollments', 'rooms'])
            ->orderByRaw("CASE WHEN nom = 'Cotonou' THEN 0 ELSE 1 END")
            ->orderBy('nom')
            ->paginate(20);

        return view('admin.sites.index', compact('sites'));
    }

    public function create(): View
    {
        return view('admin.sites.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:sites,nom',
            'ville' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:500',
            'telephone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'est_actif' => 'nullable|boolean',
        ]);

        Site::create([
            'nom' => $validated['nom'],
            'ville' => $validated['ville'],
            'adresse' => $validated['adresse'] ?? null,
            'telephone' => $validated['telephone'] ?? null,
            'email' => $validated['email'] ?? null,
            'est_actif' => $request->has('est_actif'),
        ]);

        return redirect()->route('admin.sites.index')
            ->with('success', 'Site créé avec succès.');
    }

    public function show(Site $site): View
    {
        $site->loadCount(['academicSessions', 'enrollments', 'rooms', 'expenses', 'alumnis', 'contactMessages']);

        return view('admin.sites.show', compact('site'));
    }

    public function edit(Site $site): View
    {
        return view('admin.sites.edit', compact('site'));
    }

    public function update(Request $request, Site $site): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255', Rule::unique('sites', 'nom')->ignore($site->id)],
            'ville' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:500',
            'telephone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'est_actif' => 'nullable|boolean',
        ]);

        $site->update([
            'nom' => $validated['nom'],
            'ville' => $validated['ville'],
            'adresse' => $validated['adresse'] ?? null,
            'telephone' => $validated['telephone'] ?? null,
            'email' => $validated['email'] ?? null,
            'est_actif' => $request->has('est_actif'),
        ]);

        return redirect()->route('admin.sites.index')
            ->with('success', 'Site mis à jour avec succès.');
    }

    public function destroy(Site $site): RedirectResponse
    {
        $site->loadCount(['academicSessions', 'enrollments', 'rooms', 'expenses', 'alumnis', 'contactMessages']);

        $hasData = $site->academic_sessions_count > 0
            || $site->enrollments_count > 0
            || $site->rooms_count > 0
            || $site->expenses_count > 0
            || $site->alumnis_count > 0
            || $site->contact_messages_count > 0;

        if ($hasData) {
            return redirect()->route('admin.sites.index')
                ->with('error', "Le site « {$site->nom} » contient des données et ne peut pas être supprimé. Vous pouvez le désactiver à la place.");
        }

        $site->delete();

        return redirect()->route('admin.sites.index')
            ->with('success', 'Site supprimé avec succès.');
    }

    public function toggleActive(Site $site): RedirectResponse
    {
        $site->update(['est_actif' => ! $site->est_actif]);

        $label = $site->est_actif ? 'activé' : 'désactivé';

        return redirect()->route('admin.sites.index')
            ->with('success', "Site « {$site->nom} » {$label} avec succès.");
    }
}
