<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Evaluation;
use App\Models\Formation;
use App\Models\Module;
use App\Models\TeacherProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    public function index(Request $request)
    {
        $query = Evaluation::with([
            'module.formations',
            'academicSession',
            'assignedTeacher.user',
            'course.module',
            'course.enseignant.user',
            'course.teacherProfile.user',
            'creator',
        ]);

        if ($request->filled('module_id')) {
            $query->where('module_id', $request->module_id);
        }

        $evaluations = $query->orderBy('date', 'desc')->paginate(15);
        $modules = Module::orderBy('name')->get();

        return view('admin.evaluations.index', compact('evaluations', 'modules'));
    }

    public function create()
    {
        $formations = Formation::with('modules')->orderBy('name')->get();
        $sessions = AcademicSession::orderBy('start_date', 'desc')->get();
        $modules = Module::with('formations')->orderBy('name')->get();
        $teachers = TeacherProfile::with(['user', 'modules'])->get()
            ->sortBy(fn (TeacherProfile $teacher) => $teacher->user->full_name ?? '')
            ->values();

        return view('admin.evaluations.create', compact('formations', 'sessions', 'modules', 'teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'formation_id' => 'required|exists:formations,id',
            'session_id' => 'required|exists:academic_sessions,id',
            'module_id' => 'required|exists:modules,id',
            'assigned_teacher_profile_id' => 'required|exists:teacher_profiles,id',
            'titre' => 'required|string|max:255',
            'date' => 'nullable|date',
            'statut' => 'required|in:brouillon,ouverte,cloturee',
        ]);

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
        if ($evaluation->type !== 'devoir') {
            return redirect()->route('admin.evaluations.index')->with('error', 'Les administrateurs ne peuvent modifier que les devoirs.');
        }

        $formations = Formation::with('modules')->orderBy('name')->get();
        $sessions = AcademicSession::orderBy('start_date', 'desc')->get();
        $modules = Module::with('formations')->orderBy('name')->get();
        $teachers = TeacherProfile::with(['user', 'modules'])->get()
            ->sortBy(fn (TeacherProfile $teacher) => $teacher->user->full_name ?? '')
            ->values();

        return view('admin.evaluations.edit', compact('evaluation', 'formations', 'sessions', 'modules', 'teachers'));
    }

    public function update(Request $request, Evaluation $evaluation)
    {
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
        if ($evaluation->type !== 'devoir') {
            return redirect()->route('admin.evaluations.index')->with('error', 'Les administrateurs ne peuvent supprimer que les devoirs.');
        }

        $evaluation->delete();

        return redirect()->route('admin.evaluations.index')
            ->with('success', 'Devoir supprimé avec succès.');
    }

    public function updateStatus(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $validated = $request->validate([
            'statut' => 'required|in:brouillon,ouverte,cloturee',
        ]);

        $evaluation->update(['statut' => $validated['statut']]);

        return redirect()->route('admin.evaluations.index')
            ->with('success', 'Statut de l’évaluation mis à jour.');
    }
}
