<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    /**
     * Contrôle strict d'accès à la salle par site.
     */
    protected function authorizeRoomAccess(Room $room): void
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        if ($currentUser->hasRole('administrateur')) {
            return;
        }

        if ($currentUser->hasRole('responsable_site')) {
            $managedSiteIds = $currentUser->sites()->pluck('sites.id')->all();
            if (! in_array($room->site_id, $managedSiteIds, true)) {
                abort(403, 'Accès refusé : cette salle n\'appartient pas à votre site.');
            }

            return;
        }

        abort(403, 'Action non autorisée.');
    }

    public function index(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser->hasRole('administrateur');

        $query = Room::with('site')->orderBy('name');

        if (! $isSuperAdmin) {
            $managedSiteIds = $currentUser->sites()->pluck('sites.id')->all();
            $query->whereIn('site_id', $managedSiteIds);
        } elseif ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        $rooms = $query->paginate(15)->withQueryString();
        $sites = $isSuperAdmin ? Site::activeSites() : $currentUser->sites()->where('est_actif', true)->get();

        return view('admin.rooms.index', compact('rooms', 'sites', 'isSuperAdmin'));
    }

    public function create()
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser->hasRole('administrateur');

        if ($isSuperAdmin) {
            $sites = Site::activeSites();
            $defaultSiteId = Site::defaultActiveId();
        } else {
            $sites = $currentUser->sites()->where('est_actif', true)->get();
            $defaultSiteId = $sites->first()?->id;
        }

        return view('admin.rooms.create', [
            'sites' => $sites,
            'defaultSiteId' => $defaultSiteId,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }

    public function store(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser->hasRole('administrateur');

        // Détermination et verrouillage strict du site_id
        if (! $isSuperAdmin) {
            $managedSite = $currentUser->sites()->where('est_actif', true)->first();
            if (! $managedSite) {
                abort(403, 'Aucun site actif n\'est associé à votre compte administrateur.');
            }
            $targetSiteId = $managedSite->id;
        } else {
            $validatedSite = $request->validate([
                'site_id' => ['required', Rule::exists('sites', 'id')->where('est_actif', true)],
            ], [
                'site_id.required' => 'Le site est obligatoire.',
                'site_id.exists' => 'Le site sélectionné n\'est pas valide ou est inactif.',
            ]);
            $targetSiteId = (int) $validatedSite['site_id'];
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('rooms', 'name')->where('site_id', $targetSiteId),
            ],
            'capacity' => 'required|integer|min:1',
            'type' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ], [
            'name.unique' => 'Une salle portant ce nom existe déjà sur ce site.',
        ]);

        Room::create([
            'name' => $validated['name'],
            'capacity' => $validated['capacity'],
            'type' => $validated['type'] ?? null,
            'is_active' => $request->has('is_active'),
            'site_id' => $targetSiteId,
        ]);

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Salle créée avec succès.');
    }

    public function edit(Room $room)
    {
        $this->authorizeRoomAccess($room);

        /** @var User $currentUser */
        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser->hasRole('administrateur');

        $sites = $isSuperAdmin ? Site::activeSites() : $currentUser->sites()->where('est_actif', true)->get();

        return view('admin.rooms.edit', compact('room', 'sites', 'isSuperAdmin'));
    }

    public function update(Request $request, Room $room)
    {
        $this->authorizeRoomAccess($room);

        /** @var User $currentUser */
        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser->hasRole('administrateur');

        $targetSiteId = $room->site_id;
        if ($isSuperAdmin && $request->filled('site_id')) {
            $request->validate([
                'site_id' => ['required', Rule::exists('sites', 'id')->where('est_actif', true)],
            ]);
            $targetSiteId = (int) $request->site_id;
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('rooms')->ignore($room->id)->where('site_id', $targetSiteId),
            ],
            'capacity' => 'required|integer|min:1',
            'type' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ], [
            'name.unique' => 'Une salle portant ce nom existe déjà sur ce site.',
        ]);

        $room->update([
            'name' => $validated['name'],
            'capacity' => $validated['capacity'],
            'type' => $validated['type'] ?? null,
            'is_active' => $request->has('is_active'),
            'site_id' => $targetSiteId,
        ]);

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Salle mise à jour avec succès.');
    }

    public function destroy(Room $room)
    {
        $this->authorizeRoomAccess($room);

        $room->delete();

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Salle supprimée avec succès.');
    }
}
