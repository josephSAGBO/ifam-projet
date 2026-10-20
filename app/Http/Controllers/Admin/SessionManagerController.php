<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SessionManagerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::role('gestionnaire_session')->with('sessionManagerProfile')->orderByDesc('created_at');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%");
            });
        }

        $managers = $query->paginate(20)->withQueryString();

        return view('admin.session-managers.index', compact('managers'));
    }

    public function show(User $session_manager)
    {
        if (! $session_manager->hasRole('gestionnaire_session')) {
            return redirect()->route('admin.session-managers.index')->with('error', 'Cet utilisateur n\'est pas un gestionnaire.');
        }

        $profile = $session_manager->sessionManagerProfile()->firstOrCreate(['user_id' => $session_manager->id]);

        return view('admin.session-managers.show', compact('session_manager', 'profile'));
    }

    public function edit(User $session_manager)
    {
        if (! $session_manager->hasRole('gestionnaire_session')) {
            return redirect()->route('admin.session-managers.index')->with('error', 'Cet utilisateur n\'est pas un gestionnaire.');
        }

        $profile = $session_manager->sessionManagerProfile()->firstOrCreate(['user_id' => $session_manager->id]);

        return view('admin.session-managers.edit', compact('session_manager', 'profile'));
    }

    public function update(Request $request, User $session_manager)
    {
        if (! $session_manager->hasRole('gestionnaire_session')) {
            return redirect()->route('admin.session-managers.index')->with('error', 'Action non autorisée.');
        }

        $validated = $request->validate([
            'department' => ['nullable', 'string', 'max:150'],
            'internal_role' => ['nullable', 'string', 'max:150'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        $session_manager->sessionManagerProfile()->updateOrCreate(
            ['user_id' => $session_manager->id],
            $validated
        );

        return redirect()->route('admin.session-managers.show', $session_manager)
            ->with('success', 'Profil gestionnaire mis à jour avec succès.');
    }
}
