<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $departments = Department::withCount('services')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.departments.index', compact('departments'));
    }

    public function create(): View
    {
        return view('admin.departments.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'code' => 'nullable|string|max:100|unique:departments,code',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'Le nom du département est obligatoire.',
            'name.unique' => 'Un département avec ce nom existe déjà.',
            'code.unique' => 'Ce code de département est déjà utilisé.',
        ]);

        $code = ! empty($validated['code'])
            ? Str::slug($validated['code'], '_')
            : Str::slug($validated['name'], '_');

        Department::create([
            'name' => $validated['name'],
            'code' => $code,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Département créé avec succès.');
    }

    public function show(Department $department): View
    {
        $department->load(['services' => function ($q) {
            $q->withCount('users');
        }]);

        return view('admin.departments.show', compact('department'));
    }

    public function edit(Department $department): View
    {
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments', 'name')->ignore($department->id)],
            'code' => ['nullable', 'string', 'max:100', Rule::unique('departments', 'code')->ignore($department->id)],
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'Le nom du département est obligatoire.',
            'name.unique' => 'Un département avec ce nom existe déjà.',
            'code.unique' => 'Ce code de département est déjà utilisé.',
        ]);

        $code = ! empty($validated['code'])
            ? Str::slug($validated['code'], '_')
            : $department->code;

        $department->update([
            'name' => $validated['name'],
            'code' => $code,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        // Keep department_name in sync on attached services
        $department->services()->update(['department_name' => $department->name]);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Département mis à jour avec succès.');
    }

    public function toggleActive(Department $department): RedirectResponse
    {
        $department->update(['is_active' => ! $department->is_active]);

        $label = $department->is_active ? 'activé' : 'désactivé';

        return redirect()->route('admin.departments.index')
            ->with('success', "Département « {$department->name} » {$label} avec succès.");
    }

    public function destroy(Department $department): RedirectResponse
    {
        if ($department->services()->count() > 0) {
            return redirect()->route('admin.departments.index')
                ->with('error', "Le département « {$department->name} » contient des services rattachés et ne peut pas être supprimé. Vous pouvez le désactiver à la place.");
        }

        $department->delete();

        return redirect()->route('admin.departments.index')
            ->with('success', 'Département supprimé avec succès.');
    }
}
