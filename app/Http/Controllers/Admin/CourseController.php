<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\DocumentCours;
use App\Models\Formation;
use App\Models\Module;
use App\Models\Room;
use App\Models\TeacherProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::with([
            'formation',
            'academicSession',
            'module',
            'salle',
            'enseignant.user',
            'teacherProfile.user',
            'documents',
        ])
            ->latest()
            ->paginate(15);

        return view('admin.courses.index', compact('courses'));
    }

    public function create()
    {
        $formations = Formation::with('modules')->orderBy('name')->get();
        $academicSessions = AcademicSession::orderBy('start_date', 'desc')->get();
        $modules = Module::with(['formations', 'teachers.user'])->orderBy('name')->get();
        $rooms = Room::where('is_active', true)->orderBy('name')->get();

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

        Course::create($validated);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Cours créé avec succès.');
    }

    public function show(Course $course)
    {
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
        $formations = Formation::with('modules')->orderBy('name')->get();
        $academicSessions = AcademicSession::orderBy('start_date', 'desc')->get();
        $modules = Module::with(['formations', 'teachers.user'])->orderBy('name')->get();
        $rooms = Room::where('is_active', true)->orderBy('name')->get();

        $teachers = TeacherProfile::with(['user', 'modules'])->get()->sortBy(function ($profile) {
            return $profile->user->last_name ?? '';
        })->values();

        return view('admin.courses.edit', compact('course', 'formations', 'academicSessions', 'modules', 'teachers', 'rooms'));
    }

    public function update(Request $request, Course $course)
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

        return redirect()->route('admin.courses.index')
            ->with('success', 'Cours mis à jour avec succès.');
    }

    public function updateStatus(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'statut' => 'required|in:en_cours,termine',
        ]);

        $course->update(['statut' => $validated['statut']]);

        return back()->with('success', 'Statut du cours mis à jour.');
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('success', 'Cours supprimé avec succès.');
    }

    public function uploadDocument(Request $request, Course $course)
    {
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
