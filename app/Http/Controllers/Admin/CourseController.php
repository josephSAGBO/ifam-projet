<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\DocumentCours;
use App\Models\Formation;
use App\Models\Module;
use App\Models\Room;
use App\Models\Site;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Retourne les IDs des sites gérés par l'utilisateur courant,
     * ou null si l'utilisateur a un accès global (administrateur / direction).
     *
     * @return int[]|null null = accès global ; tableau (éventuellement vide) = périmètre restreint
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
     * Contrôle strict d'accès à un cours par site.
     *
     * Le site d'un cours est déterminé par le site de sa session académique.
     * Un responsable de site ne peut accéder qu'aux cours dont la session
     * appartient à l'un de ses sites gérés.
     */
    protected function authorizeCourseAccess(Course $course): void
    {
        $managed = $this->managedSiteIds();

        if ($managed === null) {
            return; // accès global
        }

        if (empty($managed)) {
            abort(403, 'Accès refusé : vous n\'avez aucun site attribué.');
        }

        $course->loadMissing('academicSession');

        $sessionSiteId = $course->academicSession?->site_id;

        if ($sessionSiteId === null || ! in_array($sessionSiteId, $managed, true)) {
            abort(403, 'Accès refusé : ce cours n\'appartient pas à votre périmètre de site.');
        }
    }

    public function index(Request $request)
    {
        $managed = $this->managedSiteIds();
        $isResponsableEtudes = auth()->user()->hasRole('responsable_etudes');
        $availableSites = Site::query()
            ->where('est_actif', true)
            ->when($managed !== null, fn (Builder $query): Builder => $query->whereIn('id', $managed))
            ->orderBy('nom')
            ->get();

        $validated = $request->validate([
            'site_id' => ['nullable', 'integer'],
            'formation_id' => ['nullable', 'integer', 'exists:formations,id'],
            'module_id' => ['nullable', 'integer', 'exists:modules,id'],
        ]);
        $selectedSiteId = isset($validated['site_id']) ? (int) $validated['site_id'] : null;
        $selectedFormationId = isset($validated['formation_id']) ? (int) $validated['formation_id'] : null;
        $selectedModuleId = isset($validated['module_id']) ? (int) $validated['module_id'] : null;

        if ($selectedSiteId !== null && ! $availableSites->contains('id', $selectedSiteId)) {
            abort(403, 'Accès refusé : vous ne pouvez pas filtrer les cours de ce site.');
        }

        $availableFormations = Formation::query()
            ->whereHas('courses.academicSession', function (Builder $sessions) use ($managed, $selectedSiteId): void {
                if ($managed !== null) {
                    $sessions->whereIn('site_id', $managed);
                }
                if ($selectedSiteId !== null) {
                    $sessions->where('site_id', $selectedSiteId);
                }
            })
            ->orderBy('name')
            ->get();
        $availableModules = Module::query()
            ->whereHas('courses', function (Builder $courses) use ($managed, $selectedSiteId, $selectedFormationId): void {
                if ($selectedFormationId !== null) {
                    $courses->where('formation_id', $selectedFormationId);
                }
                $courses->whereHas('academicSession', function (Builder $sessions) use ($managed, $selectedSiteId): void {
                    if ($managed !== null) {
                        $sessions->whereIn('site_id', $managed);
                    }
                    if ($selectedSiteId !== null) {
                        $sessions->where('site_id', $selectedSiteId);
                    }
                });
            })
            ->orderBy('name')
            ->get();

        $query = Course::with([
            'formation',
            'academicSession',
            'module',
            'salle',
            'enseignant.user',
            'teacherProfile.user',
            'documents',
        ])->latest();

        if ($managed !== null) {
            if (empty($managed)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereHas('academicSession', function ($q) use ($managed) {
                    $q->whereIn('site_id', $managed);
                });
            }
        }

        if ($selectedSiteId !== null) {
            $query->whereHas('academicSession', fn (Builder $sessions): Builder => $sessions->where('site_id', $selectedSiteId));
        }
        if ($selectedFormationId !== null) {
            $query->where('formation_id', $selectedFormationId);
        }
        if ($selectedModuleId !== null) {
            $query->where('module_id', $selectedModuleId);
        }

        $courses = $query->paginate(15)->withQueryString();

        return view('admin.courses.index', compact(
            'courses',
            'availableSites',
            'selectedSiteId',
            'availableFormations',
            'availableModules',
            'selectedFormationId',
            'selectedModuleId',
            'isResponsableEtudes'
        ));
    }

    public function create()
    {
        $managed = $this->managedSiteIds();

        $academicSessionsQuery = AcademicSession::orderBy('start_date', 'desc');
        $roomsQuery = Room::where('is_active', true)->orderBy('name');

        if ($managed !== null) {
            $academicSessionsQuery->whereIn('site_id', $managed ?: [0]);
            $roomsQuery->whereIn('site_id', $managed ?: [0]);
        }

        $formations = Formation::with('modules')->orderBy('name')->get();
        $academicSessions = $academicSessionsQuery->get();
        $modules = Module::with(['formations', 'teachers.user'])->orderBy('name')->get();
        $rooms = $roomsQuery->get();

        $teachers = TeacherProfile::with(['user', 'modules'])->get()->sortBy(function ($profile) {
            return $profile->user->last_name ?? '';
        })->values();

        return view('admin.courses.create', compact('formations', 'academicSessions', 'modules', 'teachers', 'rooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'formation_id' => 'required|exists:formations,id',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'module_id' => 'required|exists:modules,id',
            'salle_id' => 'nullable|exists:rooms,id',
            'enseignant_id' => 'nullable|exists:teacher_profiles,id',
            'teacher_profile_id' => 'nullable|exists:teacher_profiles,id',
            'date' => 'nullable|date',
            'heure_debut' => 'nullable',
            'heure_fin' => 'nullable',
            'name' => 'nullable|string|max:255',
            'statut' => 'required|in:en_cours,termine',
            'color' => 'nullable|string|max:7',
        ]);

        // Harmonisation de la clé d'enseignant
        $teacherId = $validated['enseignant_id'] ?? $validated['teacher_profile_id'] ?? null;
        $validated['enseignant_id'] = $teacherId;
        $validated['teacher_profile_id'] = $teacherId;

        // Cloisonnement site : la session choisie doit appartenir au périmètre du responsable
        $managed = $this->managedSiteIds();
        if ($managed !== null) {
            $session = AcademicSession::findOrFail($validated['academic_session_id']);
            if (empty($managed) || ! in_array($session->site_id, $managed, true)) {
                abort(403, 'Accès refusé : cette session académique n\'appartient pas à votre périmètre de site.');
            }

            if (! empty($validated['salle_id'])) {
                $room = Room::findOrFail($validated['salle_id']);
                if (! in_array($room->site_id, $managed, true)) {
                    abort(403, 'Accès refusé : cette salle n\'appartient pas à votre périmètre de site.');
                }
            }
        }

        // Validation métier 1 : Le module doit appartenir à la formation choisie
        $formation = Formation::with('modules')->findOrFail($validated['formation_id']);
        if (! $formation->modules->contains($validated['module_id'])) {
            return back()->withErrors([
                'module_id' => 'Le module sélectionné n\'appartient pas à la formation choisie.',
            ])->withInput();
        }

        // Validation métier 2 : L'enseignant doit être rattaché au module choisi
        if ($teacherId) {
            $module = Module::with('teachers')->findOrFail($validated['module_id']);
            if (! $module->teachers->contains($teacherId)) {
                return back()->withErrors([
                    'enseignant_id' => 'L\'enseignant sélectionné n\'est pas rattaché au module choisi.',
                ])->withInput();
            }
        }

        $course = Course::create($validated);

        // Notifier l'enseignant et les étudiants concernés
        $course->load(['module', 'room', 'teacherProfile.user']);
        $this->notificationService->coursModifieOuCree($course, 'nouveau');

        return redirect()->route('admin.courses.index')
            ->with('success', 'Cours créé avec succès.');
    }

    public function show(Course $course)
    {
        $this->authorizeCourseAccess($course);

        $course->load([
            'formation',
            'academicSession',
            'module',
            'salle',
            'enseignant.user',
            'documents.uploader',
            'evaluations',
        ]);

        $students = $course->etudiantsConcernes(includeCompletedEnrollments: true);

        return view('admin.courses.show', compact('course', 'students'));
    }

    public function edit(Course $course)
    {
        $this->authorizeCourseAccess($course);

        $managed = $this->managedSiteIds();

        $academicSessionsQuery = AcademicSession::orderBy('start_date', 'desc');
        $roomsQuery = Room::where('is_active', true)->orderBy('name');

        if ($managed !== null) {
            $academicSessionsQuery->whereIn('site_id', $managed ?: [0]);
            $roomsQuery->whereIn('site_id', $managed ?: [0]);
        }

        $formations = Formation::with('modules')->orderBy('name')->get();
        $academicSessions = $academicSessionsQuery->get();
        $modules = Module::with(['formations', 'teachers.user'])->orderBy('name')->get();
        $rooms = $roomsQuery->get();

        $teachers = TeacherProfile::with(['user', 'modules'])->get()->sortBy(function ($profile) {
            return $profile->user->last_name ?? '';
        })->values();

        return view('admin.courses.edit', compact('course', 'formations', 'academicSessions', 'modules', 'teachers', 'rooms'));
    }

    public function update(Request $request, Course $course)
    {
        $this->authorizeCourseAccess($course);

        $validated = $request->validate([
            'formation_id' => 'required|exists:formations,id',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'module_id' => 'required|exists:modules,id',
            'salle_id' => 'nullable|exists:rooms,id',
            'enseignant_id' => 'nullable|exists:teacher_profiles,id',
            'teacher_profile_id' => 'nullable|exists:teacher_profiles,id',
            'date' => 'nullable|date',
            'heure_debut' => 'nullable',
            'heure_fin' => 'nullable',
            'name' => 'nullable|string|max:255',
            'statut' => 'required|in:en_cours,termine',
            'color' => 'nullable|string|max:7',
        ]);

        // Harmonisation de la clé d'enseignant
        $teacherId = $validated['enseignant_id'] ?? $validated['teacher_profile_id'] ?? null;
        $validated['enseignant_id'] = $teacherId;
        $validated['teacher_profile_id'] = $teacherId;

        // Cloisonnement site : la nouvelle session cible doit rester dans le périmètre
        $managed = $this->managedSiteIds();
        if ($managed !== null) {
            $session = AcademicSession::findOrFail($validated['academic_session_id']);
            if (empty($managed) || ! in_array($session->site_id, $managed, true)) {
                abort(403, 'Accès refusé : cette session académique n\'appartient pas à votre périmètre de site.');
            }

            if (! empty($validated['salle_id'])) {
                $room = Room::findOrFail($validated['salle_id']);
                if (! in_array($room->site_id, $managed, true)) {
                    abort(403, 'Accès refusé : cette salle n\'appartient pas à votre périmètre de site.');
                }
            }
        }

        // Validation métier 1 : Le module doit appartenir à la formation choisie
        $formation = Formation::with('modules')->findOrFail($validated['formation_id']);
        if (! $formation->modules->contains($validated['module_id'])) {
            return back()->withErrors([
                'module_id' => 'Le module sélectionné n\'appartient pas à la formation choisie.',
            ])->withInput();
        }

        // Validation métier 2 : L'enseignant doit être rattaché au module choisi
        if ($teacherId) {
            $module = Module::with('teachers')->findOrFail($validated['module_id']);
            if (! $module->teachers->contains($teacherId)) {
                return back()->withErrors([
                    'enseignant_id' => 'L\'enseignant sélectionné n\'est pas rattaché au module choisi.',
                ])->withInput();
            }
        }

        $course->update($validated);

        // Notifier l'enseignant et les étudiants concernés du changement
        $course->load(['module', 'room', 'teacherProfile.user']);
        $this->notificationService->coursModifieOuCree($course, 'modifie');

        return redirect()->route('admin.courses.index')
            ->with('success', 'Cours mis à jour avec succès.');
    }

    public function updateStatus(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeCourseAccess($course);

        $validated = $request->validate([
            'statut' => 'required|in:en_cours,termine',
        ]);

        $course->update(['statut' => $validated['statut']]);

        return back()->with('success', 'Statut du cours mis à jour.');
    }

    public function destroy(Course $course)
    {
        $this->authorizeCourseAccess($course);

        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('success', 'Cours supprimé avec succès.');
    }

    public function uploadDocument(Request $request, Course $course)
    {
        $this->authorizeCourseAccess($course);

        $request->validate([
            'title' => 'required|string|max:255',
            'document' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,png,zip|max:20480',
        ]);

        $file = $request->file('document');
        $path = $file->store('courses/documents', 'public');

        $course->documents()->create([
            'title' => $request->title,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Document de cours ajouté avec succès.');
    }

    public function destroyDocument(Course $course, DocumentCours $document)
    {
        $this->authorizeCourseAccess($course);

        if ($document->cours_id !== $course->id) {
            abort(403);
        }

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->back()->with('success', 'Document supprimé avec succès.');
    }
}
