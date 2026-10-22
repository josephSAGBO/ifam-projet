<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Site;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SessionController extends Controller
{
    /**
     * IDs des sites gérés par l'utilisateur courant.
     * null = accès global ; tableau = périmètre restreint.
     *
     * @return int[]|null
     */
    protected function managedSiteIds(): ?array
    {
        $user = auth()->user();

        if ($user && $user->hasAnyRole(['administrateur', 'direction', 'direction_daa', 'direction_daf'])) {
            return null; // accès global
        }

        return $user ? $user->sites()->pluck('sites.id')->all() : [];
    }

    protected function authorizeSessionAccess(AcademicSession $session): void
    {
        $managed = $this->managedSiteIds();
        if ($managed === null) {
            return;
        }

        if (empty($managed)) {
            abort(403, 'Accès refusé : vous n\'avez aucun site attribué.');
        }

        if ($session->site_id !== null && ! in_array($session->site_id, $managed, true)) {
            abort(403, 'Accès refusé : cette session appartient à un autre site.');
        }
    }

    public function index(Request $request)
    {
        $managed = $this->managedSiteIds();
        $sites = Site::query()
            ->where('est_actif', true)
            ->when($managed !== null, fn (Builder $query): Builder => $query->whereIn('id', $managed))
            ->orderBy('nom')
            ->get();
        $validated = $request->validate([
            'site_id' => ['nullable', 'integer'],
        ]);
        $selectedSiteId = isset($validated['site_id']) ? (int) $validated['site_id'] : null;

        if ($selectedSiteId !== null && ! $sites->contains('id', $selectedSiteId)) {
            abort(403, 'Accès refusé : vous ne pouvez pas filtrer les rentrées de ce site.');
        }

        $query = AcademicSession::with('site')->latest('start_date');
        if ($managed !== null) {
            $query->whereIn('site_id', $managed);
        }
        if ($selectedSiteId !== null) {
            $query->where('site_id', $selectedSiteId);
        }
        $sessions = $query->paginate(15)->withQueryString();

        return view('admin.sessions.index', compact('sessions', 'sites', 'selectedSiteId'));
    }

    public function create()
    {
        $managed = $this->managedSiteIds();
        $sites = $managed === null
            ? Site::activeSites()
            : auth()->user()->sites()->where('est_actif', true)->get();
        $defaultSiteId = $sites->first()?->id ?? Site::defaultActiveId();

        return view('admin.sessions.create', [
            'sites' => $sites,
            'defaultSiteId' => $defaultSiteId,
        ]);
    }

    public function store(Request $request)
    {
        $this->normalizeSessionName($request);
        $request->merge([
            'site_id' => $request->input('site_id', Site::defaultActiveId()),
        ]);

        $validated = $request->validate([
            'site_id' => ['required', Rule::exists('sites', 'id')->where('est_actif', true)],
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => ['required', Rule::in(AcademicSession::STATUSES)],
            'is_current' => 'nullable|boolean',
        ]);

        $managed = $this->managedSiteIds();
        if ($managed !== null && ! in_array((int) $validated['site_id'], $managed, true)) {
            abort(403, 'Accès refusé : vous ne pouvez créer une session que sur vos sites attribués.');
        }

        if ($this->sessionNameExists($validated['name'])) {
            return back()->withInput()->withErrors([
                'name' => 'Une session académique portant ce nom existe déjà.',
            ]);
        }

        AcademicSession::create([
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'],
            'is_current' => $request->boolean('is_current'),
            'site_id' => $validated['site_id'],
        ]);

        return redirect()->route('admin.sessions.index')
            ->with('success', 'Session créée avec succès.');
    }

    public function edit(AcademicSession $session)
    {
        $this->authorizeSessionAccess($session);

        return view('admin.sessions.edit', compact('session'));
    }

    public function update(Request $request, AcademicSession $session)
    {
        $this->authorizeSessionAccess($session);
        $this->normalizeSessionName($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => ['required', Rule::in(AcademicSession::STATUSES)],
            'is_current' => 'nullable|boolean',
        ]);

        if ($this->sessionNameExists($validated['name'], $session)) {
            return back()->withInput()->withErrors([
                'name' => 'Une session académique portant ce nom existe déjà.',
            ]);
        }

        $session->update([
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'],
            'is_current' => $request->boolean('is_current'),
        ]);

        return redirect()->route('admin.sessions.index')
            ->with('success', 'Session mise à jour avec succès.');
    }

    public function destroy(AcademicSession $session)
    {
        $this->authorizeSessionAccess($session);
        $session->delete();

        return redirect()->route('admin.sessions.index')
            ->with('success', 'Session supprimée avec succès.');
    }

    private function normalizeSessionName(Request $request): void
    {
        $name = $request->input('name');

        if (is_string($name)) {
            $request->merge(['name' => Str::squish($name)]);
        }
    }

    private function sessionNameExists(string $name, ?AcademicSession $except = null): bool
    {
        return AcademicSession::query()
            ->where('normalized_name', Str::lower(Str::squish($name)))
            ->when($except, fn ($query) => $query->where('id', '!=', $except->getKey()))
            ->exists();
    }
}
