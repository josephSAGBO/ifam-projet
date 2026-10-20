<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    public function index(Request $request)
    {
        $query = User::role('parent')->with(['parentProfile', 'children'])->orderByDesc('created_at');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%");
            });
        }

        $parents = $query->paginate(20)->withQueryString();

        return view('admin.parents.index', compact('parents'));
    }

    public function show(User $parent)
    {
        if (! $parent->hasRole('parent')) {
            return redirect()->route('admin.parents.index')->with('error', 'Cet utilisateur n\'est pas un parent.');
        }

        $profile = $parent->parentProfile()->firstOrCreate(['user_id' => $parent->id]);
        $parent->load('children');

        return view('admin.parents.show', compact('parent', 'profile'));
    }

    public function edit(User $parent)
    {
        if (! $parent->hasRole('parent')) {
            return redirect()->route('admin.parents.index')->with('error', 'Cet utilisateur n\'est pas un parent.');
        }

        $profile = $parent->parentProfile()->firstOrCreate(['user_id' => $parent->id]);
        $parent->load('children');

        // On récupère tous les étudiants pour la sélection
        $allStudents = User::role('etudiant')->orderBy('last_name')->orderBy('first_name')->get();

        return view('admin.parents.edit', compact('parent', 'profile', 'allStudents'));
    }

    public function update(Request $request, User $parent)
    {
        if (! $parent->hasRole('parent')) {
            return redirect()->route('admin.parents.index')->with('error', 'Action non autorisée.');
        }

        $validated = $request->validate([
            'profession' => ['nullable', 'string', 'max:150'],
            'employer' => ['nullable', 'string', 'max:150'],
            'admin_notes' => ['nullable', 'string'],
            'children' => ['nullable', 'array'],
            'children.*' => ['exists:users,id'],
            'relationships' => ['nullable', 'array'], // child_id => relationship
        ]);

        // Mise à jour du profil parent
        $parent->parentProfile()->updateOrCreate(
            ['user_id' => $parent->id],
            [
                'profession' => $validated['profession'] ?? null,
                'employer' => $validated['employer'] ?? null,
                'admin_notes' => $validated['admin_notes'] ?? null,
            ]
        );

        // Mise à jour des enfants associés
        $syncData = [];
        if (! empty($validated['children'])) {
            foreach ($validated['children'] as $childId) {
                $syncData[$childId] = ['relationship' => $validated['relationships'][$childId] ?? null];
            }
        }
        $parent->children()->sync($syncData);

        return redirect()->route('admin.parents.show', $parent)
            ->with('success', 'Dossier parent mis à jour avec succès.');
    }
}
