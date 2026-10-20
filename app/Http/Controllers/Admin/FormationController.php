<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use App\Models\Parcours;
use Illuminate\Http\Request;

class FormationController extends Controller
{
    public function index(Request $request)
    {
        $query = Formation::with('parcours');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $formations = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.formations.index', compact('formations'));
    }

    public function create()
    {
        $parcoursList = Parcours::orderBy('nom')->get();

        return view('admin.formations.create', compact('parcoursList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:formations,name'],
            'description' => ['nullable', 'string'],
            'parcours_id' => ['nullable', 'exists:parcours,id'],
            'prix' => ['nullable', 'numeric', 'min:0'],
            'ordre' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['prix'] = $validated['prix'] ?? 0;
        $validated['ordre'] = $validated['ordre'] ?? 0;

        Formation::create($validated);

        return redirect()->route('admin.formations.index')->with('success', 'Formation créée avec succès.');
    }

    public function edit(Formation $formation)
    {
        $parcoursList = Parcours::orderBy('nom')->get();

        return view('admin.formations.edit', compact('formation', 'parcoursList'));
    }

    public function update(Request $request, Formation $formation)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:formations,name,'.$formation->id],
            'description' => ['nullable', 'string'],
            'parcours_id' => ['nullable', 'exists:parcours,id'],
            'prix' => ['nullable', 'numeric', 'min:0'],
            'ordre' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        $formation->update($validated);

        return redirect()->route('admin.formations.index')->with('success', 'Formation mise à jour avec succès.');
    }

    public function destroy(Formation $formation)
    {
        $formation->delete();

        return back()->with('success', 'Formation supprimée avec succès.');
    }
}
