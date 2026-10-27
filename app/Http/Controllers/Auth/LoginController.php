<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Contrôleur d'authentification IFAM Connect.
 *
 * Règles métier :
 * - Identifiant : email (admin/direction/enseignant/parent) ou matricule (étudiant)
 * - Mot de passe : 8 caractères min
 * - Changement obligatoire à la première connexion
 * - Expiration de session : 30 min d'inactivité (configuré dans session.php)
 * - Réinitialisation : uniquement par l'administrateur (pas de libre-service en v1)
 */
class LoginController extends Controller
{
    public function showLoginForm()
    {
        // dd($hash = Hash::make('password'));
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'email.required' => 'L\'identifiant est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        // Tentative de connexion via email ou matricule
        $field = filter_var($credentials['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'matricule';

        $user = User::where($field, $credentials['email'])->first();

        if (! $user || ! $user->is_active) {
            return back()->withErrors([
                'email' => 'Identifiant introuvable ou compte désactivé.',
            ])->onlyInput('email');
        }

        if (! Auth::attempt([$field => $credentials['email'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Identifiant ou mot de passe incorrect.',
            ])->onlyInput('email');
        }

        // Enregistrer la dernière connexion
        Auth::user()->update(['last_login_at' => now()]);

        $request->session()->regenerate();

        // Changement de mot de passe obligatoire
        if (Auth::user()->must_change_password) {
            return redirect()->route('password.change');
        }

        return $this->redirectByRole(Auth::user());
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Vous avez été déconnecté.');
    }

    private function redirectByRole(User $user)
    {
        $adminRoles = [
            'administrateur',
            'responsable_site',
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
            'direction_daa',
            'responsable_daa',
            'responsable_etudes',
            'responsable_insertion',
        ];

        if ($user->hasRole('responsable_commercial')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasAnyRole($adminRoles)) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->hasRole('direction')) {
            return redirect()->route('direction.dashboard');
        }
        if ($user->hasRole('alumni')) {
            return redirect()->route('alumni.dashboard');
        }
        if ($user->hasRole('enseignant')) {
            return redirect()->route('teacher.dashboard');
        }
        if ($user->hasRole('etudiant')) {
            return redirect()->route('student.dashboard');
        }
        if ($user->hasRole('parent')) {
            return redirect()->route('parent.dashboard');
        }

        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')->withErrors(['email' => 'Votre compte ne possède aucun rôle valide. Veuillez contacter l\'administration.']);
    }
}
