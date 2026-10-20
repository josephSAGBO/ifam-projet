<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::orderBy('name')->paginate(15);

        return view('admin.rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('admin.rooms.create', [
            'sites' => Site::activeSites(),
            'defaultSiteId' => Site::defaultActiveId(),
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'site_id' => $request->input('site_id', Site::defaultActiveId()),
        ]);

        $validated = $request->validate([
            'site_id' => ['required', Rule::exists('sites', 'id')->where('est_actif', true)],
            'name' => 'required|string|max:255|unique:rooms,name',
            'capacity' => 'required|integer|min:1',
            'type' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        Room::create([
            'name' => $validated['name'],
            'capacity' => $validated['capacity'],
            'type' => $validated['type'] ?? null,
            'is_active' => $request->has('is_active'),
            'site_id' => $validated['site_id'],
        ]);

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Salle créée avec succès.');
    }

    public function edit(Room $room)
    {
        return view('admin.rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('rooms')->ignore($room->id),
            ],
            'capacity' => 'required|integer|min:1',
            'type' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $room->update([
            'name' => $validated['name'],
            'capacity' => $validated['capacity'],
            'type' => $validated['type'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Salle mise à jour avec succès.');
    }

    public function destroy(Room $room)
    {
        $room->delete();

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Salle supprimée avec succès.');
    }
}
