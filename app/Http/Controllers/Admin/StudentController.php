<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HasSiteFilter;
use App\Http\Controllers\Controller;
use App\Models\Site;
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
    use HasSiteFilter;

    /**
     * Contrôle strict d'accès à l'étudiant par site.
     *
     * - SuperAdmin (administrateur) : accès global.
     * - Direction : accès transversal (lecture/consultation).
     * - Responsable de site : accès restreint aux étudiants rattachés à ses sites autorisés,
     *   soit via la table `site_user`, soit via au moins une inscription (`enrollments`)
     *   sur l'un de ses sites gérés.
     * - Tout étudiant sans site ni inscription est strictement inaccessible au responsable local.
     * - Tout accès non autorisé est rejeté avec une erreur HTTP 403.
     */
    protected function authorizeStudentAccess(User $student): void
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        // 1. Administrateur général conserve un accès global
        if ($currentUser->hasRole('administrateur')) {
            return;
        }

        // 2. Direction et Direction DAF conservent leurs droits transversaux
        if ($currentUser->hasAnyRole(['direction', 'direction_daf', 'direction_daa'])) {
            return;
        }

        // 3. Rôles locaux (Responsable de site, RAF, agents administratifs)
        $managedSiteIds = $currentUser->accessibleSiteIds();

        // Une absence de site ne doit jamais être interprétée comme un accès global
        if (empty($managedSiteIds)) {
            abort(403, 'Accès refusé : vous n\'avez aucun site attribué.');
        }

        // Vérifier si l'étudiant est rattaché directement via site_user
        $studentSiteIds = $student->sites()->pluck('sites.id')->all();
        if (! empty(array_intersect($managedSiteIds, $studentSiteIds))) {
            return;
        }

        // Vérifier si l'étudiant possède au moins une inscription active ou historique sur l'un des sites gérés
        $hasEnrollmentOnManagedSite = $student->studentProfile()
            ->whereHas('enrollments', function ($q) use ($managedSiteIds) {
                $q->whereIn('site_id', $managedSiteIds);
            })
            ->exists();

        if ($hasEnrollmentOnManagedSite) {
            return;
        }

        abort(403, 'Accès refusé : cet étudiant n\'appartient pas à votre périmètre de site.');
    }

    public function index(Request $request)
    {
        ['sites' => $sites, 'selectedSiteId' => $selectedSiteId] = $this->siteFilter($request);
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser->hasRole('administrateur');

        $query = User::role('etudiant')
            ->with(['studentProfile.enrollments.site', 'sites'])
            ->orderByDesc('created_at');

        // Cloisonnement strict par site
        if (! $isSuperAdmin && ! $currentUser->hasAnyRole(['direction', 'direction_daf', 'direction_daa'])) {
            $managedSiteIds = $currentUser->accessibleSiteIds();

            if (empty($managedSiteIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where(function ($q) use ($managedSiteIds) {
                    $q->whereHas('sites', function ($sq) use ($managedSiteIds) {
                        $sq->whereIn('sites.id', $managedSiteIds);
                    })->orWhereHas('studentProfile.enrollments', function ($eq) use ($managedSiteIds) {
                        $eq->whereIn('site_id', $managedSiteIds);
                    });
                });
            }
        }

        if ($selectedSiteId !== null) {
            $filteredSiteId = $selectedSiteId;
            $query->where(function ($q) use ($filteredSiteId) {
                $q->whereHas('sites', function ($sq) use ($filteredSiteId) {
                    $sq->where('sites.id', $filteredSiteId);
                })->orWhereHas('studentProfile.enrollments', function ($eq) use ($filteredSiteId) {
                    $eq->where('site_id', $filteredSiteId);
                });
            });
        }

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

        return view('admin.students.index', compact('students', 'sites', 'isSuperAdmin', 'selectedSiteId'));
    }

    public function show(User $student)
    {
        // Sécurité : s'assurer qu'il s'agit bien d'un étudiant
        if (! $student->hasRole('etudiant')) {
            return redirect()->route('admin.students.index')->with('error', 'Cet utilisateur n\'est pas un étudiant.');
        }

        $this->authorizeStudentAccess($student);

        $student->load(['sites', 'studentProfile.enrollments.site']);

        // Récupérer ou créer le profil s'il n'existe pas encore
        $profile = $student->studentProfile()->firstOrCreate(['user_id' => $student->id]);

        return view('admin.students.show', compact('student', 'profile'));
    }

    public function edit(User $student)
    {
        if (! $student->hasRole('etudiant')) {
            return redirect()->route('admin.students.index')->with('error', 'Cet utilisateur n\'est pas un étudiant.');
        }

        if (auth()->user()->hasAnyRole(['responsable_financier', 'comptable', 'caissier', 'agent_recouvrement', 'agent_financier'])
            && ! auth()->user()->can('edit student administrative')
            && ! auth()->user()->hasAnyRole(['administrateur', 'direction', 'direction_daf', 'responsable_administratif_financier'])) {
            abort(403, 'Accès refusé : les profils financiers ne sont pas autorisés à modifier les dossiers administratifs.');
        }

        $this->authorizeStudentAccess($student);

        $student->load(['sites', 'studentProfile.enrollments.site']);

        $profile = $student->studentProfile()->firstOrCreate(['user_id' => $student->id]);

        return view('admin.students.edit', compact('student', 'profile'));
    }

    public function update(Request $request, User $student)
    {
        if (! $student->hasRole('etudiant')) {
            return redirect()->route('admin.students.index')->with('error', 'Action non autorisée.');
        }

        if ($request->user()->hasAnyRole(['responsable_financier', 'comptable', 'caissier', 'agent_recouvrement', 'agent_financier'])
            && ! $request->user()->can('edit student administrative')
            && ! $request->user()->hasAnyRole(['administrateur', 'direction', 'direction_daf', 'responsable_administratif_financier'])) {
            abort(403, 'Accès refusé : les profils financiers ne sont pas autorisés à modifier les dossiers administratifs.');
        }

        $this->authorizeStudentAccess($student);

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
