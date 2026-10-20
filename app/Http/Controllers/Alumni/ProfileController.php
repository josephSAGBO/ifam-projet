<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        $alumni = $user->alumniProfile();

        if (! $alumni) {
            return redirect()->route('alumni.dashboard')
                ->with('error', 'Profil Alumni introuvable. Veuillez contacter l\'administration.');
        }

        $statutsPro = [
            'En emploi' => 'En emploi (salarié, cadre, styliste)',
            'Entrepreneur' => 'Entrepreneur / Fondateur d\'atelier ou de marque',
            'Études supérieures' => 'Poursuite d\'études supérieures',
            'En recherche' => 'En recherche d\'opportunité professionnelle',
            'Autre' => 'Autre situation',
        ];

        return view('alumni.profile.edit', compact('user', 'alumni', 'statutsPro'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $alumni = $user->alumniProfile();

        if (! $alumni) {
            abort(404, 'Profil Alumni introuvable.');
        }

        // L'alumni ne peut modifier que ses informations professionnelles et consentements autorisés
        $validated = $request->validate([
            'statut_actuel' => 'nullable|string|max:255',
            'entreprise_actuelle' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'poste_actuel' => 'nullable|string|max:255',
            'current_position' => 'nullable|string|max:255',
            'lieu_residence' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:50',
            'linkedin_url' => 'nullable|url|max:255',
            'portfolio_url' => 'nullable|url|max:255',
            'bio' => 'nullable|string|max:2000',
            'is_visible_directory' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
            'allow_public_email' => 'nullable|boolean',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        if ($request->has('current_position')) {
            $validated['current_position'] = $request->input('current_position');
            $validated['poste_actuel'] = $validated['poste_actuel'] ?? $request->input('current_position');
        } elseif ($request->has('poste_actuel')) {
            $validated['current_position'] = $request->input('poste_actuel');
        }

        if ($request->has('company')) {
            $validated['company'] = $request->input('company');
            $validated['entreprise_actuelle'] = $validated['entreprise_actuelle'] ?? $request->input('company');
        } elseif ($request->has('entreprise_actuelle')) {
            $validated['company'] = $request->input('entreprise_actuelle');
        }

        if ($request->has('city')) {
            $validated['city'] = $request->input('city');
            $validated['lieu_residence'] = $validated['lieu_residence'] ?? $request->input('city');
        } elseif ($request->has('lieu_residence')) {
            $validated['city'] = $request->input('lieu_residence');
        }

        if ($request->has('is_visible_directory')) {
            $validated['is_visible_directory'] = $request->boolean('is_visible_directory');
        }

        if ($request->has('is_public')) {
            $validated['is_public'] = $request->boolean('is_public');
        }

        if ($request->has('allow_public_email')) {
            $validated['allow_public_email'] = $request->boolean('allow_public_email');
        }

        if ($request->hasFile('photo')) {
            if ($alumni->photo_path && Storage::disk('public')->exists($alumni->photo_path)) {
                Storage::disk('public')->delete($alumni->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('alumnis', 'public');
        }

        $alumni->update($validated);

        return redirect()->route('alumni.profile.edit')
            ->with('success', 'Vos informations de profil Alumni ont été mises à jour avec succès.');
    }
}
