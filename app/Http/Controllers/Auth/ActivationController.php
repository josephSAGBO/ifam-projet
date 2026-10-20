<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class ActivationController extends Controller
{
    /**
     * Affiche la page « Définir mon mot de passe » ou « Réinitialiser mon mot de passe ».
     */
    public function show(Request $request, $token)
    {
        $isReset = $request->routeIs('password.reset') || $request->has('reset');
        $user = User::where('activation_token', $token)->first();

        // 1. Token inexistant ou déjà utilisé
        if (! $user) {
            return response()->view('auth.activation_error', [
                'title' => 'Lien déjà utilisé ou invalide',
                'message' => 'Ce lien n\'est plus valide ou a déjà été utilisé pour configurer votre mot de passe.',
                'isReset' => $isReset,
            ], 404);
        }

        // 2. Token expiré (valable 48h)
        $expiresAt = $user->activation_expires_at ? Carbon::parse($user->activation_expires_at) : null;
        if ($expiresAt && $expiresAt->isPast()) {
            return response()->view('auth.activation_error', [
                'title' => 'Lien expiré',
                'message' => 'Ce lien a expiré car il n\'est valable que 48 heures.',
                'isReset' => $isReset,
            ], 410);
        }

        // 3. Valide : affichage du formulaire
        return view('auth.activation', [
            'token' => $token,
            'user' => $user,
            'isReset' => $isReset,
            'submitRoute' => $isReset ? route('password.update', $token) : route('activation.activate', $token),
        ]);
    }

    /**
     * Traite la définition / réinitialisation du mot de passe.
     */
    public function activate(Request $request, $token)
    {
        $isReset = $request->routeIs('password.update') || $request->has('reset');

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.required' => 'Le nouveau mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit comporter au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $user = User::where('activation_token', $token)->first();

        $expiresAt = $user?->activation_expires_at ? Carbon::parse($user->activation_expires_at) : null;
        if (! $user || ($expiresAt && $expiresAt->isPast())) {
            return response()->view('auth.activation_error', [
                'title' => 'Lien invalide ou expiré',
                'message' => 'Ce lien n\'est plus valide ou a expiré.',
                'isReset' => $isReset,
            ], 400);
        }

        // Enregistrement du nouveau mot de passe et invalidation immédiate du token
        $user->password = Hash::make($request->password);
        $user->activation_token = null;
        $user->activation_expires_at = null;
        $user->must_change_password = false;
        $user->is_active = true;
        $user->save();

        // Afficher l'écran de confirmation avec bouton vers la page de connexion
        return view('auth.activation_success', [
            'user' => $user,
            'isReset' => $isReset,
        ]);
    }
}
