<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Rôles vitaux du système ne pouvant être supprimés.
     */
    public const SYSTEM_ROLES = [
        'administrateur',
        'direction',
        'responsable_site',
        'enseignant',
        'etudiant',
        'parent',
        'alumni',
    ];

    public function index(Request $request): View
    {
        $query = Role::withCount(['users', 'permissions']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->query('search').'%');
        }

        $roles = $query->orderBy('name')->paginate(25)->withQueryString();

        return view('admin.roles.index', [
            'roles' => $roles,
            'systemRoles' => self::SYSTEM_ROLES,
            'search' => $request->query('search'),
        ]);
    }

    public function create(): View
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function ($perm) {
            // Regroupement thématique par préfixe ou mot-clé
            $parts = explode(' ', $perm->name);

            return count($parts) > 1 ? end($parts) : 'général';
        });

        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ], [
            'name.required' => 'Le nom du rôle est obligatoire.',
            'name.unique' => 'Ce rôle existe déjà.',
        ]);

        $normalizedName = Str::slug($validated['name'], '_');

        $role = Role::create([
            'name' => $normalizedName,
            'guard_name' => 'web',
        ]);

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', "Rôle « {$role->name} » créé avec succès.");
    }

    public function show(Role $role): View
    {
        $role->load('permissions');
        $users = User::role($role->name)->with('sites')->paginate(15);

        return view('admin.roles.show', [
            'role' => $role,
            'users' => $users,
            'isSystemRole' => in_array($role->name, self::SYSTEM_ROLES, true),
        ]);
    }

    public function edit(Role $role): View
    {
        $role->load('permissions');
        $allPermissions = Permission::orderBy('name')->get();
        $rolePermissionNames = $role->permissions->pluck('name')->all();

        return view('admin.roles.edit', [
            'role' => $role,
            'allPermissions' => $allPermissions,
            'rolePermissionNames' => $rolePermissionNames,
            'isSystemRole' => in_array($role->name, self::SYSTEM_ROLES, true),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $isSystem = in_array($role->name, self::SYSTEM_ROLES, true);

        $rules = [
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ];

        // Ne pas autoriser le renommage des rôles vitaux
        if (! $isSystem) {
            $rules['name'] = ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($role->id)];
        }

        $validated = $request->validate($rules, [
            'name.required' => 'Le nom du rôle est obligatoire.',
            'name.unique' => 'Ce nom de rôle est déjà utilisé.',
        ]);

        if (! $isSystem && ! empty($validated['name'])) {
            $role->name = Str::slug($validated['name'], '_');
            $role->save();
        }

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', "Rôle « {$role->name} » mis à jour avec succès.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->name, self::SYSTEM_ROLES, true)) {
            return redirect()->route('admin.roles.index')
                ->with('error', "Le rôle système « {$role->name} » est protégé et ne peut pas être supprimé.");
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', "Le rôle « {$role->name} » est actuellement attribué à {$role->users()->count()} utilisateur(s) et ne peut être supprimé.");
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', "Rôle « {$role->name} » supprimé avec succès.");
    }
}
