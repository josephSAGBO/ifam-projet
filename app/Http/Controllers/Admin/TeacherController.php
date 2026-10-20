<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Contrôleur métier — Gestion spécifique des Enseignants.
 */
class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = User::role('enseignant')->with('teacherProfile.modules')->orderByDesc('created_at');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%");
            });
        }

        $teachers = $query->paginate(20)->withQueryString();

        return view('admin.teachers.index', compact('teachers'));
    }

    public function show(User $teacher)
    {
        if (! $teacher->hasRole('enseignant')) {
            return redirect()->route('admin.teachers.index')->with('error', 'Cet utilisateur n\'est pas un enseignant.');
        }

        $profile = $teacher->teacherProfile()->firstOrCreate(['user_id' => $teacher->id]);
        $profile->load('modules');

        return view('admin.teachers.show', compact('teacher', 'profile'));
    }

    public function edit(User $teacher)
    {
        if (! $teacher->hasRole('enseignant')) {
            return redirect()->route('admin.teachers.index')->with('error', 'Cet utilisateur n\'est pas un enseignant.');
        }

        $profile = $teacher->teacherProfile()->firstOrCreate(['user_id' => $teacher->id]);
        $modules = Module::orderBy('name')->get();
        $selectedModuleIds = $profile->modules()->pluck('modules.id')->toArray();

        return view('admin.teachers.edit', compact('teacher', 'profile', 'modules', 'selectedModuleIds'));
    }

    public function update(Request $request, User $teacher)
    {
        if (! $teacher->hasRole('enseignant')) {
            return redirect()->route('admin.teachers.index')->with('error', 'Action non autorisée.');
        }

        $validated = $request->validate([
            'modules' => ['nullable', 'array'],
            'modules.*' => ['integer', 'exists:modules,id'],
            'teacher_type' => ['nullable', 'in:permanent,vacataire'],
            'qualification' => ['nullable', 'string', 'max:150'],
            'hired_at' => ['nullable', 'date'],
            'id_number' => ['nullable', 'string', 'max:50'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        $profile = $teacher->teacherProfile()->updateOrCreate(
            ['user_id' => $teacher->id],
            collect($validated)->except('modules')->toArray()
        );

        // Synchronise la table pivot enseignant_module
        $profile->modules()->sync($validated['modules'] ?? []);

        return redirect()->route('admin.teachers.show', $teacher)
            ->with('success', 'Profil enseignant mis à jour avec succès.');
    }
}
