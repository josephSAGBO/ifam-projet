<?php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Formation;
use App\Models\StudentProfile;
use App\Services\PaiementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * Liste des étudiants (lecture seule pour la Direction).
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'formation_id' => ['nullable', 'integer', Rule::exists('formations', 'id')],
            'session_id' => ['nullable', 'integer', Rule::exists('academic_sessions', 'id')],
        ]);

        $query = StudentProfile::with(['user', 'enrollments.formation', 'enrollments.academicSession'])
            ->whereHas('user');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($studentQuery) use ($search): void {
                $studentQuery->whereHas('user', function ($userQuery) use ($search): void {
                    $userQuery->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('matricule', 'like', "%{$search}%");
                });
            });
        }

        if (! empty($filters['formation_id'])) {
            $query->whereHas('enrollments', fn ($enrollmentsQuery) => $enrollmentsQuery->where('formation_id', $filters['formation_id']));
        }

        if (! empty($filters['session_id'])) {
            $query->whereHas('enrollments', fn ($enrollmentsQuery) => $enrollmentsQuery->where('academic_session_id', $filters['session_id']));
        }

        $students = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $formations = Formation::orderBy('name')->get();
        $sessions = AcademicSession::orderByDesc('start_date')->get();

        return view('direction.students.index', compact('students', 'formations', 'sessions'));
    }

    public function show(StudentProfile $student, PaiementService $paiementService): View
    {
        $student->load([
            'user',
            'enrollments.formation',
            'enrollments.academicSession',
            'enrollments.payments',
        ]);

        $totalDue = (float) $student->enrollments->sum(fn ($enrollment) => (float) $enrollment->montant_du);
        $totalPaid = (float) $student->enrollments->sum(fn ($enrollment) => $paiementService->totalPaye($enrollment));
        $balance = (float) $student->enrollments->sum(fn ($enrollment) => $paiementService->solde($enrollment));

        return view('direction.students.show', compact('student', 'totalDue', 'totalPaid', 'balance'));
    }
}
