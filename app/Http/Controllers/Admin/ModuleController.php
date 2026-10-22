<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModuleController extends Controller
{
    public function index()
    {
        $modules = Module::with('formations')->latest()->paginate(15);

        return view('admin.modules.index', compact('modules'));
    }

    protected function authorizeManageModules(): void
    {
        if (auth()->check() && ! auth()->user()->can('manage modules') && ! auth()->user()->hasAnyRole(['administrateur', 'direction', 'direction_daa'])) {
            abort(403, 'Accès interdit : la gestion du catalogue partagé des modules est réservée à la Direction Générale et aux administrateurs.');
        }
    }

    public function create()
    {
        $this->authorizeManageModules();
        $formations = Formation::where('is_active', true)->orderBy('name')->get();

        return view('admin.modules.create', compact('formations'));
    }

    public function store(Request $request)
    {
        $this->authorizeManageModules();
        $validated = $request->validate([
            'formation_id' => ['required', 'exists:formations,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:modules,code'],
            'description' => ['nullable', 'string'],
            'coefficient' => ['nullable', 'numeric', 'min:0'],
            'duree_mois' => ['nullable', 'integer', 'min:0'],
            'duree_semaines' => ['nullable', 'integer', 'min:0'],
            'duree_heures' => ['nullable', 'integer', 'min:0'],
            'duree_minutes' => ['nullable', 'integer', 'min:0', 'max:59'],
        ]);

        $formationId = $validated['formation_id'];
        unset($validated['formation_id']);

        $module = Module::create($validated);
        $module->formations()->attach($formationId);

        return redirect()->route('admin.modules.index')
            ->with('success', 'Module créé avec succès.');
    }

    public function edit(Module $module)
    {
        $this->authorizeManageModules();
        $formations = Formation::where('is_active', true)->orderBy('name')->get();

        return view('admin.modules.edit', compact('module', 'formations'));
    }

    public function update(Request $request, Module $module)
    {
        $this->authorizeManageModules();
        $validated = $request->validate([
            'formation_id' => ['required', 'exists:formations,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('modules')->ignore($module->id),
            ],
            'description' => ['nullable', 'string'],
            'coefficient' => ['nullable', 'numeric', 'min:0'],
            'duree_mois' => ['nullable', 'integer', 'min:0'],
            'duree_semaines' => ['nullable', 'integer', 'min:0'],
            'duree_heures' => ['nullable', 'integer', 'min:0'],
            'duree_minutes' => ['nullable', 'integer', 'min:0', 'max:59'],
        ]);

        $formationId = $validated['formation_id'];
        unset($validated['formation_id']);

        $module->update($validated);
        $module->formations()->sync([$formationId]);

        return redirect()->route('admin.modules.index')
            ->with('success', 'Module mis à jour avec succès.');
    }

    public function destroy(Module $module)
    {
        $this->authorizeManageModules();
        $module->delete();

        return redirect()->route('admin.modules.index')
            ->with('success', 'Module supprimé avec succès.');
    }
}
