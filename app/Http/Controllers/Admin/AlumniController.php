<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use App\Models\AlumniPost;
use App\Models\Formation;
use App\Models\StudentProfile;
use App\Services\AlumniEligibilityService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AlumniController extends Controller
{
    public function __construct(
        protected AlumniEligibilityService $eligibilityService,
        protected NotificationService $notificationService
    ) {}

    public function index(Request $request)
    {
        $tab = $request->input('tab', 'registered'); // 'registered' ou 'candidates'
        $search = $request->input('search');
        $formationId = $request->input('formation_id');
        $statutActuel = $request->input('statut_actuel');
        $approval = $request->input('approval');

        $query = Alumni::with(['formation', 'studentProfile.user', 'posts']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('entreprise_actuelle', 'like', "%{$search}%")
                    ->orWhere('poste_actuel', 'like', "%{$search}%")
                    ->orWhere('lieu_residence', 'like', "%{$search}%")
                    ->orWhereHas('studentProfile.user', function ($uq) use ($search) {
                        $uq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('matricule', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($formationId) {
            $query->where('formation_id', $formationId);
        }

        if ($statutActuel) {
            $query->where('statut_actuel', $statutActuel);
        }

        if ($approval !== null && $approval !== '') {
            $query->where('is_approved', (bool) $approval);
        }

        $alumnis = $query->orderByDesc('date_diplome')->orderBy('nom')->paginate(12)->withQueryString();
        $formations = Formation::orderBy('name')->get();

        // Étudiants éligibles à la diplomation
        $eligibleCandidates = $this->eligibilityService->getEligibleCandidates();

        $pendingPostsCount = AlumniPost::where('status', 'pending')->count();

        $statutsPro = [
            'En emploi' => 'En emploi',
            'Entrepreneur' => 'Entrepreneur / Fondateur',
            'Études supérieures' => 'Poursuite d\'études',
            'En recherche' => 'En recherche d\'opportunité',
            'Autre' => 'Autre',
        ];

        return view('admin.alumnis.index', compact(
            'alumnis',
            'formations',
            'search',
            'formationId',
            'statutActuel',
            'approval',
            'statutsPro',
            'eligibleCandidates',
            'pendingPostsCount',
            'tab'
        ));
    }

    public function create()
    {
        $formations = Formation::orderBy('name')->get();
        $eligibleStudents = $this->eligibilityService->getEligibleCandidates();

        $statutsPro = [
            'En emploi' => 'En emploi',
            'Entrepreneur' => 'Entrepreneur / Fondateur',
            'Études supérieures' => 'Poursuite d\'études',
            'En recherche' => 'En recherche d\'opportunité',
            'Autre' => 'Autre',
        ];

        return view('admin.alumnis.create', compact('formations', 'eligibleStudents', 'statutsPro'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:50',
            'date_diplome' => 'nullable|date',
            'formation_id' => 'nullable|exists:formations,id',
            'student_profile_id' => 'nullable|exists:student_profiles,id|unique:alumnis,student_profile_id',
            'lieu_residence' => 'nullable|string|max:255',
            'statut_actuel' => 'nullable|string|max:255',
            'entreprise_actuelle' => 'nullable|string|max:255',
            'poste_actuel' => 'nullable|string|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'portfolio_url' => 'nullable|url|max:255',
            'bio' => 'nullable|string',
            'is_approved' => 'nullable|boolean',
            'is_visible_directory' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
        ]);

        $validated['is_approved'] = $request->boolean('is_approved', true);
        $validated['is_visible_directory'] = $request->boolean('is_visible_directory', true);
        $validated['is_public'] = $request->boolean('is_public');

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('alumnis', 'public');
        }

        DB::transaction(function () use ($validated) {
            $alumni = Alumni::create($validated);

            // Si rattaché à un profil étudiant, attribuer le rôle alumni au User
            if ($alumni->student_profile_id) {
                $user = $alumni->studentProfile?->user;
                if ($user && ! $user->hasRole('alumni')) {
                    $user->assignRole('alumni');
                }
                $this->notificationService->alumniStatutAccorde($alumni);
            }
        });

        return redirect()->route('admin.alumnis.index')
            ->with('success', 'Fiche Alumni créée avec succès.');
    }

    /**
     * Action dédiée : Convertir un étudiant éligible en Alumni officiel.
     */
    public function convertStudent(Request $request, StudentProfile $studentProfile)
    {
        if (! $this->eligibilityService->isEligible($studentProfile)) {
            return back()->with('error', 'Cet étudiant n\'est pas encore éligible au statut Alumni (inscriptions en cours ou étapes non validées).');
        }

        $terminalFormations = $this->eligibilityService->getCompletedTerminalFormations($studentProfile);
        $primaryFormation = $terminalFormations->first();

        $user = $studentProfile->user;

        DB::transaction(function () use ($studentProfile, $user, $primaryFormation) {
            $alumni = Alumni::create([
                'student_profile_id' => $studentProfile->id,
                'formation_id' => $primaryFormation['formation']->id ?? null,
                'site_id' => $primaryFormation['site_id'],
                'date_diplome' => $primaryFormation['date_completion'] ?? now(),
                'nom' => $user->last_name,
                'prenom' => $user->first_name,
                'email' => $user->email,
                'telephone' => $user->phone,
                'photo_path' => $user->photo_path,
                'is_approved' => true,
                'is_visible_directory' => true,
            ]);

            // Attribution du rôle Spatie alumni
            if (! $user->hasRole('alumni')) {
                $user->assignRole('alumni');
            }

            // Notification envoyée à l'étudiant diplômé
            $this->notificationService->alumniStatutAccorde($alumni);
        });

        return redirect()->route('admin.alumnis.index')
            ->with('success', "L'étudiant {$user->full_name} a été officiellement diplômé et converti en Alumni.");
    }

    public function show(Alumni $alumni)
    {
        $alumni->load(['formation', 'studentProfile.user', 'posts.media', 'posts.validator']);

        return view('admin.alumnis.show', compact('alumni'));
    }

    public function edit(Alumni $alumni)
    {
        $formations = Formation::orderBy('name')->get();
        $eligibleStudents = $this->eligibilityService->getEligibleCandidates();

        $statutsPro = [
            'En emploi' => 'En emploi',
            'Entrepreneur' => 'Entrepreneur / Fondateur',
            'Études supérieures' => 'Poursuite d\'études',
            'En recherche' => 'En recherche d\'opportunité',
            'Autre' => 'Autre',
        ];

        return view('admin.alumnis.edit', compact('alumni', 'formations', 'eligibleStudents', 'statutsPro'));
    }

    public function update(Request $request, Alumni $alumni)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:50',
            'date_diplome' => 'nullable|date',
            'formation_id' => 'nullable|exists:formations,id',
            'lieu_residence' => 'nullable|string|max:255',
            'statut_actuel' => 'nullable|string|max:255',
            'entreprise_actuelle' => 'nullable|string|max:255',
            'poste_actuel' => 'nullable|string|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'portfolio_url' => 'nullable|url|max:255',
            'bio' => 'nullable|string',
            'is_approved' => 'nullable|boolean',
            'is_visible_directory' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
            'rapports_donnees' => 'nullable|string',
        ]);

        $validated['is_approved'] = $request->boolean('is_approved', $alumni->is_approved);
        $validated['is_visible_directory'] = $request->boolean('is_visible_directory', $alumni->is_visible_directory);
        $validated['is_public'] = $request->boolean('is_public', $alumni->is_public);

        if ($request->hasFile('photo')) {
            if ($alumni->photo_path && Storage::disk('public')->exists($alumni->photo_path)) {
                Storage::disk('public')->delete($alumni->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('alumnis', 'public');
        }

        $alumni->update($validated);

        return redirect()->route('admin.alumnis.index')
            ->with('success', 'Fiche Alumni mise à jour.');
    }

    public function toggleApproval(Alumni $alumni)
    {
        $alumni->update(['is_approved' => ! $alumni->is_approved]);

        $message = $alumni->is_approved ? 'Profil Alumni approuvé.' : 'Approbation retirée.';

        return back()->with('success', $message);
    }

    public function toggleDirectory(Alumni $alumni)
    {
        $alumni->update(['is_visible_directory' => ! $alumni->is_visible_directory]);

        $message = $alumni->is_visible_directory ? 'Profil rendu visible dans l\'annuaire public.' : 'Profil masqué de l\'annuaire public.';

        return back()->with('success', $message);
    }

    public function destroy(Alumni $alumni)
    {
        if ($alumni->photo_path && Storage::disk('public')->exists($alumni->photo_path)) {
            Storage::disk('public')->delete($alumni->photo_path);
        }

        $alumni->delete();

        return redirect()->route('admin.alumnis.index')
            ->with('success', 'Fiche Alumni supprimée avec succès.');
    }
}
