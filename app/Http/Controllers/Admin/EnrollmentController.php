<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HasSiteFilter;
use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\Parcours;
use App\Models\StudentProfile;
use App\Services\NotificationService;
use App\Services\PaiementService;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    use HasSiteFilter;

    public function __construct(protected PaiementService $paiementService) {}

    public function index(Request $request)
    {
        ['sites' => $sites, 'selectedSiteId' => $selectedSiteId] = $this->siteFilter($request);
        $query = Enrollment::with(['studentProfile.user', 'formation', 'academicSession', 'payments'])
            ->when(auth()->check() && ! auth()->user()->hasAnyRole(['administrateur', 'direction', 'direction_daf', 'responsable_commercial']), function ($q) {
                $q->whereIn('site_id', auth()->user()->accessibleSiteIds());
            })
            ->latest('enrollment_date');
        if ($selectedSiteId !== null) {
            $query->where('site_id', $selectedSiteId);
        }

        if ($request->filled('formation_id')) {
            $query->where('formation_id', $request->formation_id);
        }

        if ($request->filled('academic_session_id')) {
            $query->where('academic_session_id', $request->academic_session_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('studentProfile.user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('matricule', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $enrollments = $query->paginate(15)->withQueryString();
        $formations = Formation::where('is_active', true)->orderBy('name')->get();
        $sessionsQuery = AcademicSession::orderBy('start_date', 'desc');
        if ($selectedSiteId !== null) {
            $sessionsQuery->where('site_id', $selectedSiteId);
        }
        $sessions = $sessionsQuery->get();

        return view('admin.enrollments.index', compact('enrollments', 'formations', 'sessions', 'sites', 'selectedSiteId'));
    }

    public function create()
    {
        $formations = Formation::where('is_active', true)->orderBy('name')->get();
        $parcoursList = Parcours::with(['formations' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('nom')
            ->get();
        $sessions = AcademicSession::orderBy('start_date', 'desc')->get();
        $students = StudentProfile::with('user')
            ->whereHas('user', function ($q) {
                $q->where('is_active', true);
            })
            ->get()
            ->sortBy('user.last_name');

        return view('admin.enrollments.create', compact('formations', 'parcoursList', 'sessions', 'students'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_profile_id' => 'required|exists:student_profiles,id',
            'parcours_id' => 'nullable|exists:parcours,id',
            'formation_id' => 'required|exists:formations,id',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'bourse_type' => 'nullable|in:aucune,pourcentage,montant',
            'bourse_valeur' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,suspended,completed,cancelled',
            'enrollment_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $session = AcademicSession::findOrFail($validated['academic_session_id']);
        if (! auth()->user()->hasAnyRole(['administrateur', 'responsable_commercial']) && ! auth()->user()->hasSiteAccess($session->site_id)) {
            abort(403, 'Accès interdit : vous ne pouvez créer une inscription que pour votre site.');
        }

        // Vérification doublon
        $exists = Enrollment::where('student_profile_id', $validated['student_profile_id'])
            ->where('formation_id', $validated['formation_id'])
            ->where('academic_session_id', $validated['academic_session_id'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'student_profile_id' => 'Cet étudiant est déjà inscrit à cette formation pour cette session académique.',
            ]);
        }

        $formation = Formation::findOrFail($validated['formation_id']);
        if ((int) ($validated['parcours_id'] ?? 0) !== (int) ($formation->parcours_id ?? 0)) {
            return back()->withInput()->withErrors([
                'formation_id' => 'La formation choisie ne correspond pas au parcours sélectionné.',
            ]);
        }

        if ($formation->parcours_id) {
            $formationsDuParcours = Formation::where('parcours_id', $formation->parcours_id)
                ->orderBy('ordre')
                ->orderBy('id')
                ->get();
            $formationPosition = $formationsDuParcours->search(fn (Formation $item) => $item->id === $formation->id);
            $previousFormations = $formationsDuParcours->take($formationPosition === false ? 0 : $formationPosition);

            foreach ($previousFormations as $previousFormation) {
                $latestPreviousAttempt = Enrollment::with('academicSession')
                    ->where('student_profile_id', $validated['student_profile_id'])
                    ->where('formation_id', $previousFormation->id)
                    ->get()
                    ->sortByDesc(fn (Enrollment $attempt) => $attempt->academicSession?->start_date?->timestamp ?? 0)
                    ->first();

                if ($latestPreviousAttempt?->status !== 'completed') {
                    return back()->withInput()->withErrors([
                        'formation_id' => "L'étudiant doit d'abord terminer l'étape « {$previousFormation->name} » du parcours.",
                    ]);
                }
            }
        }

        unset($validated['parcours_id']);
        $prixFormation = (float) ($formation->prix ?? 0.0);
        $bourseType = $validated['bourse_type'] ?? 'aucune';
        $bourseValeur = (float) ($validated['bourse_valeur'] ?? 0.0);

        $montantDu = $this->paiementService->calculerMontantDu($prixFormation, $bourseType, $bourseValeur);

        $validated['prix_formation'] = $prixFormation;
        $validated['bourse_type'] = $bourseType;
        $validated['bourse_valeur'] = $bourseValeur;
        $validated['montant_du'] = $montantDu;
        $validated['site_id'] = AcademicSession::findOrFail($validated['academic_session_id'])->site_id;

        $enrollment = Enrollment::create($validated);
        try {
            app(NotificationService::class)->inscriptionValidee($enrollment);
        } catch (\Throwable $e) {
        }

        return redirect()->route('admin.enrollments.index')
            ->with('success', 'Inscription créée avec succès.');
    }

    public function show(Enrollment $enrollment)
    {
        $this->authorizeEnrollmentAccess($enrollment);

        $enrollment->load(['studentProfile.user', 'formation.parcours', 'academicSession', 'payments.enregistrePar']);
        $totalPaye = $this->paiementService->totalPaye($enrollment);
        $solde = $this->paiementService->solde($enrollment);
        $soldeParcours = $this->paiementService->soldeParcours($enrollment);
        $statutPaiement = $this->paiementService->statut($enrollment);
        $parcoursEnrollments = Enrollment::with(['formation', 'academicSession', 'payments'])
            ->where('student_profile_id', $enrollment->student_profile_id)
            ->whereHas('formation', function ($query) use ($enrollment) {
                if ($enrollment->formation?->parcours_id) {
                    $query->where('parcours_id', $enrollment->formation->parcours_id);
                } else {
                    $query->whereKey($enrollment->formation_id);
                }
            })
            ->get();

        return view('admin.enrollments.show', compact(
            'enrollment',
            'totalPaye',
            'solde',
            'soldeParcours',
            'statutPaiement',
            'parcoursEnrollments',
        ));
    }

    public function edit(Enrollment $enrollment)
    {
        $this->authorizeEnrollmentAccess($enrollment);

        $enrollment->load(['studentProfile.user', 'formation', 'academicSession']);
        $formations = Formation::where('is_active', true)->orderBy('name')->get();
        $parcoursList = Parcours::with(['formations' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('nom')
            ->get();
        $sessions = AcademicSession::orderBy('start_date', 'desc')->get();

        return view('admin.enrollments.edit', compact('enrollment', 'formations', 'parcoursList', 'sessions'));
    }

    public function update(Request $request, Enrollment $enrollment)
    {
        $this->authorizeEnrollmentAccess($enrollment);

        $validated = $request->validate([
            'bourse_type' => 'nullable|in:aucune,pourcentage,montant',
            'bourse_valeur' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,suspended,completed,cancelled',
            'enrollment_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $prixFormation = (float) $enrollment->prix_formation;
        $bourseType = $validated['bourse_type'] ?? ($enrollment->bourse_type ?? 'aucune');
        $bourseValeur = (float) ($validated['bourse_valeur'] ?? ($enrollment->bourse_valeur ?? 0.0));

        $montantDu = $this->paiementService->calculerMontantDu($prixFormation, $bourseType, $bourseValeur);

        $validated['bourse_type'] = $bourseType;
        $validated['bourse_valeur'] = $bourseValeur;
        $validated['montant_du'] = $montantDu;

        $enrollment->update($validated);

        return redirect()->route('admin.enrollments.index')
            ->with('success', 'Inscription mise à jour avec succès.');
    }

    public function destroy(Enrollment $enrollment)
    {
        $this->authorizeEnrollmentAccess($enrollment);

        $enrollment->delete();

        return redirect()->route('admin.enrollments.index')
            ->with('success', 'Inscription supprimée avec succès.');
    }

    private function authorizeEnrollmentAccess(Enrollment $enrollment): void
    {
        if ($enrollment->site_id && ! auth()->user()->hasAnyRole(['administrateur', 'responsable_commercial']) && ! auth()->user()->hasSiteAccess($enrollment->site_id)) {
            abort(403, 'Accès interdit : cette inscription ne relève pas de votre site.');
        }
    }
}
