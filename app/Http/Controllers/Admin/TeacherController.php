<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Contrôleur métier — Gestion spécifique des Enseignants.
 */
class TeacherController extends Controller
{
    /**
     * Contrôle strict d'accès à l'enseignant par site.
     *
     * - SuperAdmin (administrateur) : accès global.
     * - Direction : accès transversal.
     * - Responsable de site : accès restreint aux enseignants rattachés à ses sites autorisés,
     *   soit via la table `site_user`, soit via au moins un cours dispensé
     *   dans une session académique sur l'un de ses sites gérés.
     * - Tout enseignant sans site ni cours est strictement inaccessible au responsable local.
     * - Tout accès non autorisé est rejeté avec une erreur HTTP 403.
     */
    protected function authorizeTeacherAccess(User $teacher): void
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        // 1. Administrateur général conserve un accès global
        if ($currentUser->hasRole('administrateur')) {
            return;
        }

        // 2. Direction conserve ses droits transversaux
        if ($currentUser->hasRole('direction')) {
            return;
        }

        // 3. Responsable de site
        if ($currentUser->hasRole('responsable_site')) {
            $managedSiteIds = $currentUser->sites()->pluck('sites.id')->all();

            // Une absence de site ne doit jamais être interprétée comme un accès global
            if (empty($managedSiteIds)) {
                abort(403, 'Accès refusé : vous n\'avez aucun site attribué.');
            }

            // Vérifier si l'enseignant est rattaché directement via site_user
            $teacherSiteIds = $teacher->sites()->pluck('sites.id')->all();
            if (! empty(array_intersect($managedSiteIds, $teacherSiteIds))) {
                return;
            }

            // Vérifier si l'enseignant dispense au moins un cours dans une session sur l'un des sites gérés
            $hasCourseOnManagedSite = $teacher->teacherProfile()
                ->whereHas('courses.academicSession', function ($q) use ($managedSiteIds) {
                    $q->whereIn('site_id', $managedSiteIds);
                })
                ->exists();

            if ($hasCourseOnManagedSite) {
                return;
            }

            abort(403, 'Accès refusé : cet enseignant n\'appartient pas à votre périmètre de site.');
        }

        abort(403, 'Action non autorisée.');
    }

    public function index(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser->hasRole('administrateur');

        $query = User::role('enseignant')
            ->with([
                'teacherProfile.modules',
                'sites',
                'teacherProfile.courses.academicSession.site',
            ])
            ->orderByDesc('created_at');

        // Cloisonnement strict par site
        if (! $isSuperAdmin && ! $currentUser->hasRole('direction')) {
            $managedSiteIds = $currentUser->sites()->pluck('sites.id')->all();

            if (empty($managedSiteIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where(function ($q) use ($managedSiteIds) {
                    $q->whereHas('sites', function ($sq) use ($managedSiteIds) {
                        $sq->whereIn('sites.id', $managedSiteIds);
                    })->orWhereHas('teacherProfile.courses.academicSession', function ($cq) use ($managedSiteIds) {
                        $cq->whereIn('site_id', $managedSiteIds);
                    });
                });
            }
        } elseif ($isSuperAdmin && $request->filled('site_id')) {
            $filteredSiteId = (int) $request->site_id;
            $query->where(function ($q) use ($filteredSiteId) {
                $q->whereHas('sites', function ($sq) use ($filteredSiteId) {
                    $sq->where('sites.id', $filteredSiteId);
                })->orWhereHas('teacherProfile.courses.academicSession', function ($cq) use ($filteredSiteId) {
                    $cq->where('site_id', $filteredSiteId);
                });
            });
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%");
            });
        }

        $teachers = $query->paginate(20)->withQueryString();
        $sites = $isSuperAdmin ? Site::activeSites() : collect();

        return view('admin.teachers.index', compact('teachers', 'sites', 'isSuperAdmin'));
    }

    public function show(User $teacher)
    {
        if (! $teacher->hasRole('enseignant')) {
            return redirect()->route('admin.teachers.index')->with('error', 'Cet utilisateur n\'est pas un enseignant.');
        }

        $this->authorizeTeacherAccess($teacher);

        $teacher->load(['sites', 'teacherProfile.courses.academicSession.site', 'teacherProfile.modules']);

        $profile = $teacher->teacherProfile()->firstOrCreate(['user_id' => $teacher->id]);
        $profile->load('modules');

        return view('admin.teachers.show', compact('teacher', 'profile'));
    }

    public function edit(User $teacher)
    {
        if (! $teacher->hasRole('enseignant')) {
            return redirect()->route('admin.teachers.index')->with('error', 'Cet utilisateur n\'est pas un enseignant.');
        }

        $this->authorizeTeacherAccess($teacher);

        $teacher->load(['sites', 'teacherProfile.courses.academicSession.site']);

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

        $this->authorizeTeacherAccess($teacher);

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
