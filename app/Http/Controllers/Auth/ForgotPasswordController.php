<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Affiche le formulaire de saisie de l'email pour le mot de passe oublié.
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Traite la demande de réinitialisation de mot de passe.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email:rfc'],
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse email valide (ex. nom@domaine.com).',
        ]);

        $email = strtolower(trim($request->input('email')));
        $user = User::where('email', $email)->first();

        if ($user && $user->is_active) {
            $token = Str::random(64);
            $user->activation_token = $token;
            $user->activation_expires_at = now()->addHours(48);
            $user->save();

            try {
                Mail::to($user->email)->send(new PasswordResetMail($user, $token));
            } catch (\Throwable $e) {
                Log::error('Erreur envoi email reset: '.$e->getMessage());
            }
        }

        // Message neutre obligatoire (sécurité : pas d'énumération d'utilisateurs)
        return back()->with('status', 'Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.');
    }
}
