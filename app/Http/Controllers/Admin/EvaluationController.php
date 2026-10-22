<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HasSiteFilter;
use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Evaluation;
use App\Models\Formation;
use App\Models\Module;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    use HasSiteFilter;

    /**
     * IDs des sites gérés par l'utilisateur courant.
     * null = accès global ; tableau = périmètre restreint.
     *
     * @return int[]|null
     */
    protected function managedSiteIds(): ?array
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->hasAnyRole(['administrateur', 'direction', 'direction_daa'])) {
            return null; // accès global
        }

        return $user->sites()->pluck('sites.id')->all();
    }

    /**
     * Contrôle strict d'accès à une évaluation par site.
     */
    protected function authorizeEvaluationAccess(Evaluation $evaluation): void
    {
        $managed = $this->managedSiteIds();

        if ($managed === null) {
            return;
        }

        if (empty($managed)) {
            abort(403, 'Accès refusé : vous n\'avez aucun site attribué.');
        }

        $evaluation->loadMissing(['academicSession', 'course.academicSession']);
        $siteId = $evaluation->academicSession?->site_id ?? $evaluation->course?->academicSession?->site_id;

        if ($siteId !== null && ! in_array($siteId, $managed, true)) {
            abort(403, 'Accès refusé : cette évaluation appartient à un autre site.');
        }
    }

    public function index(Request $request)
    {
        ['sites' => $sites, 'selectedSiteId' => $selectedSiteId] = $this->siteFilter($request);
        $validated = $request->validate([
            'formation_id' => ['nullable', 'integer', 'exists:formations,id'],
            'module_id' => ['nullable', 'integer', 'exists:modules,id'],
        ]);
        $isResponsableEtudes = $request->user()->hasRole('responsable_etudes');
        $selectedFormationId = isset($validated['formation_id']) ? (int) $validated['formation_id'] : null;
        $selectedModuleId = isset($validated['module_id']) ? (int) $validated['module_id'] : null;
        $query = Evaluation::with([
            'module.formations',
            'academicSession',
            'assignedTeacher.user',
            'course.module',
            'course.enseignant.user',
            'course.teacherProfile.user',
            'creator',
        ]);

        $managed = $this->managedSiteIds();
        if ($managed !== null) {
            $query->where(function ($q) use ($managed) {
                $q->whereHas('academicSession', fn ($sq) => $sq->whereIn('site_id', $managed))
                    ->orWhereHas('course.academicSession', fn ($csq) => $csq->whereIn('site_id', $managed));
            });
        }
        if ($selectedSiteId !== null) {
            $query->where(function ($q) use ($selectedSiteId) {
                $q->whereHas('academicSession', fn ($sq) => $sq->where('site_id', $selectedSiteId))
                    ->orWhereHas('course.academicSession', fn ($csq) => $csq->where('site_id', $selectedSiteId));
            });
        }

        $optionQuery = clone $query;
        $availableEvaluations = (clone $optionQuery)->get(['formation_id', 'module_id', 'cours_id']);
        $availableCourses = Course::query()
            ->whereIn('id', $availableEvaluations->pluck('cours_id')->filter()->unique())
            ->get(['id', 'formation_id', 'module_id'])
            ->keyBy('id');
        $formationIds = $availableEvaluations->pluck('formation_id')
            ->merge($availableEvaluations->pluck('cours_id')->map(fn ($courseId) => $availableCourses->get($courseId)?->formation_id))
            ->filter()
            ->unique()
            ->values();
        $formations = Formation::whereIn('id', $formationIds)->orderBy('name')->get();

        if ($selectedFormationId !== null) {
            $query->where(function ($evaluations) use ($selectedFormationId) {
                $evaluations->where('formation_id', $selectedFormationId)
                    ->orWhereHas('course', fn ($courses) => $courses->where('formation_id', $selectedFormationId));
            });
            $optionQuery->where(function ($evaluations) use ($selectedFormationId) {
                $evaluations->where('formation_id', $selectedFormationId)
                    ->orWhereHas('course', fn ($courses) => $courses->where('formation_id', $selectedFormationId));
            });
        }

        $availableEvaluations = $optionQuery->get(['formation_id', 'module_id', 'cours_id']);
        $availableCourses = Course::query()
            ->whereIn('id', $availableEvaluations->pluck('cours_id')->filter()->unique())
            ->get(['id', 'module_id'])
            ->keyBy('id');
        $moduleIds = $availableEvaluations->pluck('module_id')
            ->merge($availableEvaluations->pluck('cours_id')->map(fn ($courseId) => $availableCourses->get($courseId)?->module_id))
            ->filter()
            ->unique()
            ->values();
        $modules = Module::whereIn('id', $moduleIds)->orderBy('name')->get();

        if ($selectedModuleId !== null) {
            $query->where(function ($evaluations) use ($selectedModuleId) {
                $evaluations->where('module_id', $selectedModuleId)
                    ->orWhereHas('course', fn ($courses) => $courses->where('module_id', $selectedModuleId));
            });
        }

        $evaluations = $query->orderBy('date', 'desc')->paginate(15)->withQueryString();

        return view('admin.evaluations.index', compact(
            'evaluations',
            'modules',
            'formations',
            'sites',
            'selectedSiteId',
            'isResponsableEtudes',
            'selectedFormationId',
            'selectedModuleId',
        ));
    }

    public function create()
    {
        $this->assertUserCanManageEvaluations();

        $formations = Formation::with('modules')->orderBy('name')->get();
        $managed = $this->managedSiteIds();
        $sessionsQuery = AcademicSession::orderBy('start_date', 'desc');
        if ($managed !== null) {
            $sessionsQuery->whereIn('site_id', $managed);
        }
        $sessions = $sessionsQuery->get();
        $modules = Module::with('formations')->orderBy('name')->get();
        $teachers = TeacherProfile::with(['user', 'modules'])->get()
            ->sortBy(fn (TeacherProfile $teacher) => $teacher->user->full_name ?? '')
            ->values();

        return view('admin.evaluations.create', compact('formations', 'sessions', 'modules', 'teachers'));
    }

    public function store(Request $request)
    {
        $this->assertUserCanManageEvaluations();

        $validated = $request->validate([
            'formation_id' => 'required|exists:formations,id',
            'session_id' => 'required|exists:academic_sessions,id',
            'module_id' => 'required|exists:modules,id',
            'assigned_teacher_profile_id' => 'required|exists:teacher_profiles,id',
            'titre' => 'required|string|max:255',
            'date' => 'nullable|date',
            'statut' => 'required|in:brouillon,ouverte,cloturee',
        ]);

        // Vérification multi-sites à la création
        $managed = $this->managedSiteIds();
        if ($managed !== null) {
            $session = AcademicSession::findOrFail($validated['session_id']);
            if (! in_array($session->site_id, $managed, true)) {
                abort(403, 'Accès refusé : cette session n\'appartient pas à vos sites gérés.');
            }
        }

        // Vérification métier : Le module doit appartenir à la formation choisie
        $formation = Formation::with('modules')->findOrFail($validated['formation_id']);
        if (! $formation->modules->contains($validated['module_id'])) {
            return back()->withErrors([
                'module_id' => 'Le module sélectionné n\'appartient pas à la formation choisie.',
            ])->withInput();
        }

        $module = Module::with('teachers')->findOrFail($validated['module_id']);
        if (! $module->teachers->contains('id', (int) $validated['assigned_teacher_profile_id'])) {
            return back()->withErrors([
                'assigned_teacher_profile_id' => 'Cet enseignant n\'est pas affecté à ce module.',
            ])->withInput();
        }

        $course = Course::where('formation_id', $formation->id)
            ->where('academic_session_id', $validated['session_id'])
            ->where('module_id', $module->id)
            ->where(function ($query) use ($validated) {
                $query->where('enseignant_id', $validated['assigned_teacher_profile_id'])
                    ->orWhere('teacher_profile_id', $validated['assigned_teacher_profile_id']);
            })
            ->first();

        Evaluation::create([
            'type' => 'devoir',
            'created_by' => auth()->id(),
            'cours_id' => $course?->id,
            'formation_id' => $formation->id,
            'assigned_teacher_profile_id' => $validated['assigned_teacher_profile_id'],
            'module_id' => $module->id,
            'session_id' => $validated['session_id'],
            'titre' => $validated['titre'],
            'date' => $validated['date'] ?? null,
            'statut' => $validated['statut'],
        ]);

        return redirect()->route('admin.evaluations.index')
            ->with('success', 'Devoir créé avec succès.');
    }

    public function edit(Evaluation $evaluation)
    {
        $this->authorizeEvaluationAccess($evaluation);

        if ($evaluation->type !== 'devoir') {
            return redirect()->route('admin.evaluations.index')->with('error', 'Les administrateurs ne peuvent modifier que les devoirs.');
        }

        $formations = Formation::with('modules')->orderBy('name')->get();
        $managed = $this->managedSiteIds();
        $sessionsQuery = AcademicSession::orderBy('start_date', 'desc');
        if ($managed !== null) {
            $sessionsQuery->whereIn('site_id', $managed);
        }
        $sessions = $sessionsQuery->get();
        $modules = Module::with('formations')->orderBy('name')->get();
        $teachers = TeacherProfile::with(['user', 'modules'])->get()
            ->sortBy(fn (TeacherProfile $teacher) => $teacher->user->full_name ?? '')
            ->values();

        return view('admin.evaluations.edit', compact('evaluation', 'formations', 'sessions', 'modules', 'teachers'));
    }

    public function update(Request $request, Evaluation $evaluation)
    {
        $this->authorizeEvaluationAccess($evaluation);
        $this->assertUserCanManageEvaluations();

        if ($evaluation->type !== 'devoir') {
            return redirect()->route('admin.evaluations.index')->with('error', 'Les administrateurs ne peuvent modifier que les devoirs.');
        }

        $validated = $request->validate([
            'formation_id' => 'required|exists:formations,id',
            'session_id' => 'required|exists:academic_sessions,id',
            'module_id' => 'required|exists:modules,id',
            'assigned_teacher_profile_id' => 'required|exists:teacher_profiles,id',
            'titre' => 'required|string|max:255',
            'date' => 'nullable|date',
            'statut' => 'required|in:brouillon,ouverte,cloturee',
        ]);

        // Vérification multi-sites
        $managed = $this->managedSiteIds();
        if ($managed !== null) {
            $session = AcademicSession::findOrFail($validated['session_id']);
            if (! in_array($session->site_id, $managed, true)) {
                abort(403, 'Accès refusé : cette session n\'appartient pas à vos sites gérés.');
            }
        }

        // Vérification métier : Le module doit appartenir à la formation choisie
        $formation = Formation::with('modules')->findOrFail($validated['formation_id']);
        if (! $formation->modules->contains($validated['module_id'])) {
            return back()->withErrors([
                'module_id' => 'Le module sélectionné n\'appartient pas à la formation choisie.',
            ])->withInput();
        }

        $module = Module::with('teachers')->findOrFail($validated['module_id']);
        if (! $module->teachers->contains('id', (int) $validated['assigned_teacher_profile_id'])) {
            return back()->withErrors([
                'assigned_teacher_profile_id' => 'Cet enseignant n\'est pas affecté à ce module.',
            ])->withInput();
        }

        $course = Course::where('formation_id', $formation->id)
            ->where('academic_session_id', $validated['session_id'])
            ->where('module_id', $module->id)
            ->where(function ($query) use ($validated) {
                $query->where('enseignant_id', $validated['assigned_teacher_profile_id'])
                    ->orWhere('teacher_profile_id', $validated['assigned_teacher_profile_id']);
            })
            ->first();

        $evaluation->update([
            'cours_id' => $course?->id,
            'formation_id' => $formation->id,
            'assigned_teacher_profile_id' => $validated['assigned_teacher_profile_id'],
            'session_id' => $validated['session_id'],
            'module_id' => $module->id,
            'titre' => $validated['titre'],
            'date' => $validated['date'] ?? null,
            'statut' => $validated['statut'],
        ]);

        return redirect()->route('admin.evaluations.index')
            ->with('success', 'Devoir mis à jour avec succès.');
    }

    public function destroy(Evaluation $evaluation)
    {
        $this->authorizeEvaluationAccess($evaluation);
        $this->assertUserCanManageEvaluations();

        if ($evaluation->type !== 'devoir') {
            return redirect()->route('admin.evaluations.index')->with('error', 'Les administrateurs ne peuvent supprimer que les devoirs.');
        }

        $evaluation->delete();

        return redirect()->route('admin.evaluations.index')
            ->with('success', 'Devoir supprimé avec succès.');
    }

    public function updateStatus(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorizeEvaluationAccess($evaluation);
        $this->assertUserCanManageEvaluations();

        $validated = $request->validate([
            'statut' => 'required|in:brouillon,ouverte,cloturee',
        ]);

        $evaluation->update(['statut' => $validated['statut']]);

        return redirect()->route('admin.evaluations.index')
            ->with('success', 'Statut de l’évaluation mis à jour.');
    }

    private function assertUserCanManageEvaluations(): void
    {
        if (auth()->check() && auth()->user()->hasAnyRole([
            'direction_daf',
            'responsable_administratif_financier',
            'responsable_administratif',
            'secretaire_administratif',
            'charge_scolarite',
            'agent_administratif',
            'responsable_financier',
            'comptable',
            'caissier',
            'agent_recouvrement',
            'agent_financier',
        ]) && ! auth()->user()->hasAnyRole(['administrateur', 'direction'])) {
            abort(403, 'Accès interdit : les profils administratifs et financiers ne sont pas autorisés à créer ou modifier les évaluations pédagogiques.');
        }
    }
}
