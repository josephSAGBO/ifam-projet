<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use App\Models\Formation;
use Illuminate\Http\Request;

class AlumniDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $formationId = $request->input('formation_id');
        $statutActuel = $request->input('statut_actuel');

        $query = Alumni::query()
            ->publiclyVisible()
            ->with(['formation.parcours', 'posts' => fn ($q) => $q->published()]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->orWhere('entreprise_actuelle', 'like', "%{$search}%")
                    ->orWhere('poste_actuel', 'like', "%{$search}%")
                    ->orWhere('lieu_residence', 'like', "%{$search}%");
            });
        }

        if ($formationId) {
            $query->where('formation_id', $formationId);
        }

        if ($statutActuel) {
            $query->where('statut_actuel', $statutActuel);
        }

        if ($request->input('promotion')) {
            $query->where('promotion_year', $request->input('promotion'));
        }

        $alumnis = $query->orderByDesc('date_diplome')->orderBy('nom')->paginate(12)->withQueryString();
        $formations = Formation::orderBy('name')->get();
        $promotions = Alumni::publiclyVisible()->whereNotNull('promotion_year')->distinct()->pluck('promotion_year')->sortDesc();

        $statutsPro = [
            'En emploi' => 'En poste',
            'Entrepreneur' => 'Entrepreneurs & Fondateurs',
            'Études supérieures' => 'Études supérieures',
            'En recherche' => 'En recherche d\'opportunités',
        ];

        return view('public.alumnis.index', compact('alumnis', 'formations', 'promotions', 'search', 'formationId', 'statutActuel', 'statutsPro'));
    }

    public function show(Alumni $alumni)
    {
        // Un profil exige approbation, visibilité et consentement explicite.
        if (! $alumni->is_approved || ! $alumni->is_visible_directory || ! $alumni->is_public) {
            abort(404, 'Ce profil Alumni n\'est pas accessible.');
        }

        $alumni->load(['formation.parcours']);

        // Seules les publications validées et en ligne sont consultables publiquement
        $posts = $alumni->posts()
            ->published()
            ->with('media')
            ->latest('published_at')
            ->paginate(6);

        return view('public.alumnis.show', compact('alumni', 'posts'));
    }
}
