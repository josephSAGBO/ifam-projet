<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ActivationMail;
use App\Models\ParentProfile;
use App\Models\Site;
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
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

/**
 * Contrôleur CRUD — Gestion des utilisateurs (espace administrateur).
 */
class UserController extends Controller
{
    // ── Helper d'autorisation par site ──────────────────────────────────────

    /**
     * Vérifie si l'utilisateur connecté a le droit d'accéder au compte cible.
     */
    protected function authorizeUserAccess(User $targetUser): void
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        // 1. SuperAdmin a accès à tous les comptes
        if ($currentUser->hasRole('administrateur')) {
            return;
        }

        // 2. Administrateur de site (responsable_site)
        if ($currentUser->hasRole('responsable_site')) {
            $managedSiteIds = $currentUser->sites()->pluck('sites.id')->all();

            // Si le compte cible a un accès transversal ou direction, interdire l'édition par l'admin de site
            if ($targetUser->hasAnyRole(['administrateur', 'direction']) || $targetUser->isDepartmentHead()) {
                abort(403, 'Accès non autorisé à un compte transversal ou administrateur.');
            }

            // Vérifier si le compte cible partage au moins un des sites de l'admin
            $targetSiteIds = $targetUser->sites()->pluck('sites.id')->all();
            $commonSites = array_intersect($managedSiteIds, $targetSiteIds);

            if (empty($commonSites)) {
                abort(403, 'Accès refusé : cet utilisateur n\'appartient pas à votre site.');
            }

            return;
        }

        // 3. Responsable commercial : gestion transversale des étudiants uniquement
        if ($currentUser->hasRole('responsable_commercial')) {
            if (! $targetUser->hasRole('etudiant')) {
                abort(403, 'Action non autorisée : le responsable commercial ne peut accéder qu\'aux comptes étudiants.');
            }

            return;
        }

        abort(403, 'Action non autorisée.');
    }

    // ── Liste ──────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser->hasRole('administrateur');
        // Le responsable commercial a un périmètre transversal sur les inscriptions ;
        // il doit aussi pouvoir consulter les utilisateurs (étudiants) de tous les sites.
        $isRespCommercial = $currentUser->hasRole('responsable_commercial');

        $query = User::with(['roles', 'sites'])->orderByDesc('created_at');

        // Scoping par site : superadmin et responsable_commercial voient tous les sites
        if (! $isSuperAdmin && ! $isRespCommercial) {
            $managedSiteIds = $currentUser->sites()->pluck('sites.id')->all();
            $query->whereHas('sites', function ($q) use ($managedSiteIds) {
                $q->whereIn('sites.id', $managedSiteIds);
            });
        } elseif ($request->filled('site_id')) {
            $query->whereHas('sites', function ($q) use ($request) {
                $q->where('sites.id', $request->site_id);
            });
        }

        // Filtre par rôle : pour le commercial, restreint aux étudiants
        if ($isRespCommercial) {
            $query->role('etudiant');
        } elseif ($request->filled('role')) {
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
        $roles = $isRespCommercial ? Role::where('name', 'etudiant')->get() : Role::orderBy('name')->get();
        $canFilterSite = $isSuperAdmin || $isRespCommercial;
        $sites = $canFilterSite ? Site::activeSites() : $currentUser->sites()->where('est_actif', true)->get();

        return view('admin.users.index', compact('users', 'roles', 'sites', 'isSuperAdmin', 'canFilterSite'));
    }

    // ── Créer ─────────────────────────────────────────────────────────────

    public function create()
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser->hasRole('administrateur');
        // Le responsable commercial peut créer des étudiants sur n'importe quel site
        $isRespCommercial = ! $isSuperAdmin && $currentUser->hasRole('responsable_commercial');

        // Rôles autorisés à la création selon le rôle de l'acteur
        $allowedRoleNames = $this->allowedCreatableRoles($currentUser);
        $roles = Role::whereIn('name', $allowedRoleNames)->orderBy('name')->get();

        if ($isSuperAdmin || $isRespCommercial) {
            $sites = Site::activeSites();
            $defaultSiteId = Site::defaultActiveId();
        } else {
            $sites = $currentUser->sites()->where('est_actif', true)->get();
            $defaultSiteId = $sites->first()?->id;
        }

        // Pour le formulaire : afficher le sélecteur de site si superadmin ou responsable commercial
        $canChooseSite = $isSuperAdmin || $isRespCommercial;

        return view('admin.users.create', compact('roles', 'sites', 'canChooseSite', 'isSuperAdmin', 'defaultSiteId'));
    }

    public function store(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser->hasRole('administrateur');
        // Le responsable commercial est transversal : il choisit lui aussi le site de l'étudiant
        $isRespCommercial = ! $isSuperAdmin && $currentUser->hasRole('responsable_commercial');

        if ($isSuperAdmin) {
            $validatedSite = $request->validate([
                'site_id' => ['required', Rule::exists('sites', 'id')->where('est_actif', true)],
            ], [
                'site_id.required' => 'Le site de rattachement est obligatoire.',
                'site_id.exists' => 'Le site sélectionné n\'est pas valide ou est inactif.',
            ]);
            $targetSiteId = (int) $validatedSite['site_id'];
        } elseif ($isRespCommercial) {
            // Le responsable commercial peut choisir le site s'il le précise, sinon son site de rattachement
            if ($request->filled('site_id')) {
                $validatedSite = $request->validate([
                    'site_id' => [Rule::exists('sites', 'id')->where('est_actif', true)],
                ], [
                    'site_id.exists' => 'Le site sélectionné n\'est pas valide ou est inactif.',
                ]);
                $targetSiteId = (int) $validatedSite['site_id'];
            } else {
                $targetSiteId = (int) ($currentUser->sites()->where('est_actif', true)->first()?->id ?? Site::defaultActiveId());
            }
        } else {
            // Imposition stricte du site côté serveur pour les autres rôles locaux
            $managedSite = $currentUser->sites()->where('est_actif', true)->first();
            if (! $managedSite) {
                abort(403, 'Aucun site actif n\'est associé à votre compte.');
            }
            $targetSiteId = $managedSite->id;
        }

        // Rôles autorisés en création pour l'acteur courant (vérification serveur)
        $allowedRoles = $this->allowedCreatableRoles($currentUser);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['nullable', 'in:M,F,autre'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email:rfc', 'unique:users,email'],
            'role' => ['required', Rule::in($allowedRoles)],
            'photo' => ['nullable', 'image', 'max:2048'],
        ], [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom de famille est obligatoire.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse email valide (ex. nom@domaine.com).',
            'email.unique' => 'Cette adresse email est déjà associée à un compte IFAM.',
            'role.required' => 'Veuillez sélectionner un rôle.',
            'role.in' => 'Le rôle sélectionné n\'est pas autorisé pour votre niveau d\'accès.',
            'photo.image' => 'Le fichier doit être une image.',
            'photo.max' => 'L\'image ne doit pas dépasser 2Mo.',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos', 'public');
        }

        $token = Str::random(64);

        $user = DB::transaction(function () use ($validated, $token, $photoPath, $targetSiteId) {
            $userData = [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'gender' => $validated['gender'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => strtolower(trim($validated['email'])),
                'photo_path' => $photoPath,
                'password' => null,
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

            // Rattachement unique au site
            $newUser->sites()->sync([$targetSiteId]);

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
        $this->authorizeUserAccess($user);

        $user->load(['roles', 'creator', 'studentProfile', 'teacherProfile', 'sites']);

        return view('admin.users.show', compact('user'));
    }

    // ── Modifier ──────────────────────────────────────────────────────────

    public function edit(User $user)
    {
        $this->authorizeUserAccess($user);

        /** @var User $currentUser */
        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser->hasRole('administrateur');

        $roles = Role::whereIn('name', ['etudiant', 'enseignant', 'direction', 'parent'])->get();
        $user->load(['roles', 'sites']);

        $sites = $isSuperAdmin ? Site::activeSites() : $currentUser->sites()->where('est_actif', true)->get();

        return view('admin.users.edit', compact('user', 'roles', 'sites', 'isSuperAdmin'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeUserAccess($user);

        /** @var User $currentUser */
        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser->hasRole('administrateur');

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['nullable', 'in:M,F,autre'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'site_id' => [$isSuperAdmin ? 'required' : 'nullable', Rule::exists('sites', 'id')->where('est_actif', true)],
        ], [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom de famille est obligatoire.',
            'site_id.required' => 'Le site de rattachement est obligatoire.',
            'site_id.exists' => 'Le site sélectionné n\'est pas valide.',
        ]);

        if ($request->hasFile('photo')) {
            if ($user->photo_path && Storage::disk('public')->exists($user->photo_path)) {
                Storage::disk('public')->delete($user->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('photos', 'public');
        }

        // Modification du site autorisée UNIQUEMENT pour le SuperAdmin
        if ($isSuperAdmin && $request->filled('site_id')) {
            $user->sites()->sync([(int) $request->site_id]);
        }

        unset($validated['site_id']);
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
        $this->authorizeUserAccess($user);

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
        $this->authorizeUserAccess($user);

        $user->update(['is_active' => true]);

        return back()->with('success', "Le compte de {$user->full_name} a été réactivé.");
    }

    // ── Renvoyer l'email d'activation ─────────────────────────────────────

    public function resendActivation(User $user)
    {
        $this->authorizeUserAccess($user);

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
            'responsable_site' => 'Administrateur de site',
        ];
        $roleLabel = $roleLabels[$roleName] ?? ucfirst($roleName);

        try {
            Mail::to($user->email)->send(new ActivationMail($user, $token, $roleLabel));
        } catch (\Throwable $e) {
            Log::error('Erreur lors du renvoi de l\'email d\'activation : '.$e->getMessage());
        }

        return back()->with('success', "Un nouveau lien d'activation valable 48 heures a été envoyé à {$user->email}.");
    }

    // ── Helper : rôles créables selon l'acteur ────────────────────────────

    /**
     * Renvoie la liste des noms de rôles que l'acteur courant peut attribuer
     * lors de la création d'un compte.
     *
     * @return string[]
     */
    protected function allowedCreatableRoles(User $actor): array
    {
        if ($actor->hasRole('administrateur')) {
            // Le superadmin peut créer n'importe quel rôle sauf administrateur
            return Role::where('name', '!=', 'administrateur')->pluck('name')->all();
        }

        if ($actor->hasRole('responsable_commercial')) {
            // Le responsable commercial ne peut créer que des étudiants
            return ['etudiant'];
        }

        if ($actor->hasRole('responsable_site')) {
            // L'administrateur de site peut créer enseignants, parents et étudiants
            return ['enseignant', 'parent', 'etudiant'];
        }

        // Par défaut : rôles basiques uniquement
        return ['etudiant', 'enseignant', 'parent'];
    }
}
