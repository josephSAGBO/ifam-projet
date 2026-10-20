<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Contrôleur métier — Gestion spécifique des Étudiants.
 *
 * S'occupe de la gestion fine du profil étudiant (StudentProfile).
 * La création de base se fait via le UserController, mais ici on gère
 * les spécificités (notes administratives, tuteur, historique académique).
 */
class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = User::role('etudiant')->with('studentProfile')->orderByDesc('created_at');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('matricule', 'like', "%{$s}%");
            });
        }

        $students = $query->paginate(20)->withQueryString();

        return view('admin.students.index', compact('students'));
    }

    public function show(User $student)
    {
        // Sécurité : s'assurer qu'il s'agit bien d'un étudiant
        if (! $student->hasRole('etudiant')) {
            return redirect()->route('admin.students.index')->with('error', 'Cet utilisateur n\'est pas un étudiant.');
        }

        // Récupérer ou créer le profil s'il n'existe pas encore
        $profile = $student->studentProfile()->firstOrCreate(['user_id' => $student->id]);

        return view('admin.students.show', compact('student', 'profile'));
    }

    public function edit(User $student)
    {
        if (! $student->hasRole('etudiant')) {
            return redirect()->route('admin.students.index')->with('error', 'Cet utilisateur n\'est pas un étudiant.');
        }

        $profile = $student->studentProfile()->firstOrCreate(['user_id' => $student->id]);

        return view('admin.students.edit', compact('student', 'profile'));
    }

    public function update(Request $request, User $student)
    {
        if (! $student->hasRole('etudiant')) {
            return redirect()->route('admin.students.index')->with('error', 'Action non autorisée.');
        }

        $validated = $request->validate([
            // Informations liées au profil étudiant
            'birth_place' => ['nullable', 'string', 'max:100'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'birth_certificate_number' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'guardian_relation' => ['nullable', 'string', 'max:100'],
            'previous_school' => ['nullable', 'string', 'max:150'],
            'previous_diploma' => ['nullable', 'string', 'max:150'],
            'entry_year' => ['nullable', 'integer', 'min:2000', 'max:'.(date('Y') + 1)],
            'admin_notes' => ['nullable', 'string'],
        ]);

        $student->studentProfile()->updateOrCreate(
            ['user_id' => $student->id],
            $validated
        );

        return redirect()->route('admin.students.show', $student)
            ->with('success', 'Profil étudiant mis à jour avec succès.');
    }
}
