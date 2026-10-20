<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ActivationMail;
use App\Models\ParentProfile;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

/**
 * Contrôleur CRUD — Gestion des utilisateurs (espace administrateur).
 */
class UserController extends Controller
{
    // ── Liste ──────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = User::with('roles')->orderByDesc('created_at');

        // Filtre par rôle
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        // Filtre par statut
        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->whereNotNull('activation_token');
            } elseif ($request->status === 'active') {
                $query->whereNull('activation_token')->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Recherche par nom / email / matricule
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('matricule', 'like', "%{$s}%");
            });
        }

        $users = $query->paginate(20)->withQueryString();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    // ── Créer ─────────────────────────────────────────────────────────────

    public function create()
    {
        // Seuls ces 4 rôles peuvent être créés par l'administrateur (jamais Administrateur)
        $roles = Role::whereIn('name', ['etudiant', 'enseignant', 'direction', 'parent'])->get();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['nullable', 'in:M,F,autre'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email:rfc', 'unique:users,email'],
            'role' => ['required', 'in:etudiant,enseignant,direction,parent'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ], [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom de famille est obligatoire.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse email valide (ex. nom@domaine.com).',
            'email.unique' => 'Cette adresse email est déjà associée à un compte IFAM.',
            'role.required' => 'Veuillez sélectionner un rôle.',
            'role.in' => 'Le rôle sélectionné n\'est pas valide pour la création.',
            'photo.image' => 'Le fichier doit être une image.',
            'photo.max' => 'L\'image ne doit pas dépasser 2Mo.',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos', 'public');
        }

        $token = Str::random(64);

        $user = DB::transaction(function () use ($validated, $token, $photoPath) {
            $userData = [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'gender' => $validated['gender'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => strtolower(trim($validated['email'])),
                'photo_path' => $photoPath,
                'password' => null, // Aucun mot de passe défini à la création
                'activation_token' => $token,
                'activation_expires_at' => now()->addHours(48),
                'is_active' => true,
                'must_change_password' => false,
                'created_by' => auth()->id(),
            ];

            // Matricule automatique pour les étudiants
            if ($validated['role'] === 'etudiant') {
                $userData['matricule'] = User::generateMatricule();
            }

            $newUser = User::create($userData);
            $newUser->assignRole($validated['role']);

            // Création des profils associés
            if ($validated['role'] === 'etudiant') {
                StudentProfile::firstOrCreate(['user_id' => $newUser->id]);
            } elseif ($validated['role'] === 'enseignant') {
                TeacherProfile::firstOrCreate(['user_id' => $newUser->id]);
            } elseif ($validated['role'] === 'parent') {
                ParentProfile::firstOrCreate(['user_id' => $newUser->id]);
            }

            return $newUser;
        });

        // Envoi automatique de l'email d'activation
        $roleLabels = [
            'etudiant' => 'Étudiant',
            'enseignant' => 'Enseignant',
            'direction' => 'Membre de la Direction',
            'parent' => 'Parent d\'élève',
        ];
        $roleLabel = $roleLabels[$validated['role']] ?? ucfirst($validated['role']);

        try {
            Mail::to($user->email)->send(new ActivationMail($user, $token, $roleLabel));
        } catch (\Throwable $e) {
            Log::error('Erreur lors de l\'envoi de l\'email d\'activation : '.$e->getMessage());
        }

        return redirect()->route('admin.users.index')
            ->with('success', "Compte {$roleLabel} créé pour {$user->full_name}. L'email d'activation a été envoyé avec succès à {$user->email}.");
    }

    // ── Détail ────────────────────────────────────────────────────────────

    public function show(User $user)
    {
        $user->load(['roles', 'creator', 'studentProfile', 'teacherProfile']);

        return view('admin.users.show', compact('user'));
    }

    // ── Modifier ──────────────────────────────────────────────────────────

    public function edit(User $user)
    {
        $roles = Role::whereIn('name', ['etudiant', 'enseignant', 'direction', 'parent'])->get();
        $user->load('roles');

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['nullable', 'in:M,F,autre'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'phone' => ['nullable', 'string', 'max:20'],
            // 'email'      => ['required', 'email:rfc,dns', "unique:users,email,{$user->id}"],
            'is_active' => ['boolean'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ], [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom de famille est obligatoire.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
        ]);

        if ($request->hasFile('photo')) {
            // Supprimer l'ancienne photo si elle existe
            if ($user->photo_path && Storage::disk('public')->exists($user->photo_path)) {
                Storage::disk('public')->delete($user->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('photos', 'public');
        }

        $user->update($validated);

        // Réinitialisation du mot de passe directe par l'admin (optionnel)
        if ($request->filled('new_password')) {
            $request->validate([
                'new_password' => ['required', Password::min(8)],
            ]);
            $user->update([
                'password' => Hash::make($request->new_password),
                'must_change_password' => true,
            ]);
        }

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    // ── Désactiver (soft action — pas de suppression) ─────────────────────

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $user->update(['is_active' => false]);

        return redirect()->route('admin.users.index')
            ->with('success', "Le compte de {$user->full_name} a été désactivé.");
    }

    // ── Réactiver ─────────────────────────────────────────────────────────

    public function activate(User $user)
    {
        $user->update(['is_active' => true]);

        return back()->with('success', "Le compte de {$user->full_name} a été réactivé.");
    }

    // ── Renvoyer l'email d'activation ─────────────────────────────────────

    public function resendActivation(User $user)
    {
        if (! $user->email) {
            return back()->with('error', 'Cet utilisateur ne possède pas d\'adresse email.');
        }

        $token = Str::random(64);
        $user->activation_token = $token;
        $user->activation_expires_at = now()->addHours(48);
        $user->save();

        $roleName = $user->getRoleNames()->first() ?? 'utilisateur';
        $roleLabels = [
            'etudiant' => 'Étudiant',
            'enseignant' => 'Enseignant',
            'direction' => 'Membre de la Direction',
            'parent' => 'Parent d\'élève',
            'administrateur' => 'Administrateur',
        ];
        $roleLabel = $roleLabels[$roleName] ?? ucfirst($roleName);

        try {
            Mail::to($user->email)->send(new ActivationMail($user, $token, $roleLabel));
        } catch (\Throwable $e) {
            Log::error('Erreur lors du renvoi de l\'email d\'activation : '.$e->getMessage());
        }

        return back()->with('success', "Un nouveau lien d'activation valable 48 heures a été envoyé à {$user->email}.");
    }
}
