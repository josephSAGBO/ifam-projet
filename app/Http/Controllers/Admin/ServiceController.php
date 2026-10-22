<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\ServiceProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $query = ServiceProfile::with('department')->withCount('users')->orderBy('name');

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $services = $query->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->get();

        return view('admin.services.index', compact('services', 'departments'));
    }

    public function create(): View
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('admin.services.create', compact('departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:service_profiles,name',
            'code' => 'nullable|string|max:100|unique:service_profiles,code',
            'department_id' => ['required', Rule::exists('departments', 'id')],
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'Le nom du service est obligatoire.',
            'name.unique' => 'Un service portant ce nom existe déjà.',
            'department_id.required' => 'Le rattachement à un département est obligatoire.',
            'department_id.exists' => 'Le département sélectionné n\'est pas valide.',
        ]);

        $code = ! empty($validated['code'])
            ? Str::slug($validated['code'], '_')
            : Str::slug($validated['name'], '_');

        $department = Department::findOrFail($validated['department_id']);

        ServiceProfile::create([
            'name' => $validated['name'],
            'code' => $code,
            'department_id' => $department->id,
            'department_name' => $department->name,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.services.index')
            ->with('success', 'Service créé et rattaché au département avec succès.');
    }

    public function show(ServiceProfile $service): View
    {
        $service->load(['department', 'users.sites']);

        return view('admin.services.show', compact('service'));
    }

    public function edit(ServiceProfile $service): View
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('admin.services.edit', compact('service', 'departments'));
    }

    public function update(Request $request, ServiceProfile $service): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('service_profiles', 'name')->ignore($service->id)],
            'code' => ['nullable', 'string', 'max:100', Rule::unique('service_profiles', 'code')->ignore($service->id)],
            'department_id' => ['required', Rule::exists('departments', 'id')],
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'Le nom du service est obligatoire.',
            'name.unique' => 'Un service portant ce nom existe déjà.',
            'department_id.required' => 'Le rattachement à un département est obligatoire.',
            'department_id.exists' => 'Le département sélectionné n\'est pas valide.',
        ]);

        $code = ! empty($validated['code'])
            ? Str::slug($validated['code'], '_')
            : $service->code;

        $department = Department::findOrFail($validated['department_id']);

        $service->update([
            'name' => $validated['name'],
            'code' => $code,
            'department_id' => $department->id,
            'department_name' => $department->name,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.services.index')
            ->with('success', 'Service mis à jour avec succès.');
    }

    public function toggleActive(ServiceProfile $service): RedirectResponse
    {
        $service->update(['is_active' => ! $service->is_active]);

        $label = $service->is_active ? 'activé' : 'désactivé';

        return redirect()->route('admin.services.index')
            ->with('success', "Service « {$service->name} » {$label} avec succès.");
    }

    public function destroy(ServiceProfile $service): RedirectResponse
    {
        if ($service->users()->count() > 0) {
            return redirect()->route('admin.services.index')
                ->with('error', "Le service « {$service->name} » a des utilisateurs affectés et ne peut pas être supprimé. Vous pouvez le désactiver à la place.");
        }

        $service->delete();

        return redirect()->route('admin.services.index')
            ->with('success', 'Service supprimé avec succès.');
    }
}
