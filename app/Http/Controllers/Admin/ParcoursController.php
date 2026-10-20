<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parcours;
use Illuminate\Http\Request;

/**
 * CRUD des parcours de formation (Licence, Master, …).
 */
class ParcoursController extends Controller
{
    public function index()
    {
        $parcours = Parcours::withCount('formations')->orderBy('nom')->paginate(20);

        return view('admin.parcours.index', compact('parcours'));
    }

    public function create()
    {
        return view('admin.parcours.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255', 'unique:parcours,nom'],
            'description' => ['nullable', 'string'],
        ]);

        Parcours::create($validated);

        return redirect()->route('admin.parcours.index')
            ->with('success', 'Parcours créé avec succès.');
    }

    public function edit(Parcours $parcour)
    {
        $parcour->loadCount('formations');

        return view('admin.parcours.edit', compact('parcour'));
    }

    public function update(Request $request, Parcours $parcour)
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255', 'unique:parcours,nom,'.$parcour->id],
            'description' => ['nullable', 'string'],
        ]);

        $parcour->update($validated);

        return redirect()->route('admin.parcours.index')
            ->with('success', 'Parcours mis à jour avec succès.');
    }

    public function destroy(Parcours $parcour)
    {
        $parcour->delete();

        return back()->with('success', 'Parcours supprimé avec succès.');
    }
}
