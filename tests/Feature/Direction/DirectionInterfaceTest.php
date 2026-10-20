<?php

namespace Tests\Feature\Direction;

use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Fee;
use App\Models\Formation;
use App\Models\Payment;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DirectionInterfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_search_and_filters_are_applied_together(): void
    {
        $direction = $this->createUser('direction', 'direction-list@ifam.test');
        $formation = Formation::create(['name' => 'Stylisme', 'prix' => 12000]);
        $session = $this->createSession('Session A', '2026-09-01');

        $matchingStudent = $this->createStudent('Awa', 'Matching', 'ETU-MATCH-01');
        $otherStudent = $this->createStudent('Awa', 'Other', 'ETU-OTHER-01');
        $this->createEnrollment($matchingStudent, $formation, $session, 12000);
        $this->createEnrollment($otherStudent, $formation, $this->createSession('Session B', '2027-09-01'), 12000);

        $response = $this->actingAs($direction)->get(route('direction.students.index', [
            'search' => 'Awa',
            'session_id' => $session->id,
        ]));

        $response->assertOk();
        $response->assertSee('ETU-MATCH-01');
        $response->assertDontSee('ETU-OTHER-01');
    }

    public function test_finance_page_reports_enrollment_payments_and_filters_by_session(): void
    {
        $direction = $this->createUser('direction', 'direction-finance@ifam.test');
        $student = $this->createStudent('Awa', 'Finance', 'ETU-FIN-01');
        $formation = Formation::create(['name' => 'Formation finance', 'prix' => 12000]);
        $session = $this->createSession('Session financière', '2026-09-01');
        $enrollment = $this->createEnrollment($student, $formation, $session, 10000);
        Payment::create([
            'inscription_id' => $enrollment->id,
            'montant' => 2500,
            'date_paiement' => '2026-10-01',
            'mode' => 'especes',
        ]);

        $otherSession = $this->createSession('Autre session', '2027-09-01');
        $otherEnrollment = $this->createEnrollment($student, $formation, $otherSession, 20000);
        Payment::create([
            'inscription_id' => $otherEnrollment->id,
            'montant' => 9000,
            'date_paiement' => '2027-10-01',
            'mode' => 'especes',
        ]);

        $legacyFee = Fee::create([
            'student_profile_id' => $student->id,
            'academic_session_id' => $session->id,
            'title' => 'Ancien frais',
            'amount' => 50000,
            'status' => 'pending',
        ]);
        Payment::create([
            'fee_id' => $legacyFee->id,
            'student_profile_id' => $student->id,
            'amount_paid' => 20000,
            'payment_date' => '2026-10-02',
            'method' => 'cash',
        ]);

        $response = $this->actingAs($direction)->get(route('direction.finance.index', [
            'session_id' => $session->id,
        ]));

        $response->assertOk();
        $response->assertSee('2 500 FCFA');
        $response->assertSee('Formation finance');
        $response->assertSee('Session financière');
        $response->assertDontSee('9 000 FCFA');
        $response->assertDontSee('Ancien frais');
        $response->assertDontSee('20 000 FCFA');
    }

    public function test_finance_report_filters_payments_by_formation_and_inclusive_date_range(): void
    {
        $direction = $this->createUser('direction', 'direction-finance-range@ifam.test');
        $student = $this->createStudent('Awa', 'Range', 'ETU-RANGE-01');
        $session = $this->createSession('Session période', '2026-09-01');
        $formation = Formation::create(['name' => 'Formation sélectionnée', 'prix' => 10000]);
        $otherFormation = Formation::create(['name' => 'Autre formation', 'prix' => 20000]);
        $otherStudent = $this->createStudent('Moussa', 'Other', 'ETU-OTHER-01');
        $matchingEnrollment = $this->createEnrollment($student, $formation, $session, 10000);
        $outsideRangeEnrollment = $this->createEnrollment($otherStudent, $formation, $session, 10000);
        $otherFormationEnrollment = $this->createEnrollment($student, $otherFormation, $session, 20000);

        Payment::create([
            'inscription_id' => $matchingEnrollment->id,
            'montant' => 1000,
            'date_paiement' => '2026-10-01',
            'mode' => 'especes',
        ]);
        Payment::create([
            'inscription_id' => $matchingEnrollment->id,
            'montant' => 500,
            'date_paiement' => '2026-10-31',
            'mode' => 'especes',
        ]);
        Payment::create([
            'inscription_id' => $outsideRangeEnrollment->id,
            'montant' => 2000,
            'date_paiement' => '2026-09-30',
            'mode' => 'especes',
        ]);
        Payment::create([
            'inscription_id' => $otherFormationEnrollment->id,
            'montant' => 3000,
            'date_paiement' => '2026-10-31',
            'mode' => 'especes',
        ]);

        $response = $this->actingAs($direction)->get(route('direction.finance.index', [
            'formation_id' => $formation->id,
            'date_from' => '2026-10-01',
            'date_to' => '2026-10-31',
        ]));

        $response->assertSee('1 500 FCFA');
        $response->assertDontSee('+2 000 FCFA');
        $response->assertDontSee('+3 000 FCFA');
        $response->assertSee('Les dépenses sont filtrées par période', false);
    }

    public function test_finance_report_rejects_a_date_range_with_an_end_before_its_start(): void
    {
        $direction = $this->createUser('direction', 'direction-finance-invalid-range@ifam.test');

        $response = $this->actingAs($direction)->get(route('direction.finance.index', [
            'date_from' => '2026-11-01',
            'date_to' => '2026-10-01',
        ]));

        $response->assertSessionHasErrors(['date_from', 'date_to']);
    }

    private function createUser(string $role, string $email): User
    {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        $user = User::create([
            'first_name' => 'Direction',
            'last_name' => 'IFAM',
            'email' => $email,
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function createStudent(string $firstName, string $lastName, string $matricule): StudentProfile
    {
        $user = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => strtolower($matricule).'@ifam.test',
            'matricule' => $matricule,
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        Role::firstOrCreate(['name' => 'etudiant', 'guard_name' => 'web']);
        $user->assignRole('etudiant');

        return StudentProfile::create(['user_id' => $user->id]);
    }

    private function createSession(string $name, string $startDate): AcademicSession
    {
        return AcademicSession::create([
            'name' => $name,
            'start_date' => $startDate,
            'end_date' => date('Y-m-d', strtotime($startDate.' +1 year')),
        ]);
    }

    private function createEnrollment(
        StudentProfile $student,
        Formation $formation,
        AcademicSession $session,
        float $amountDue,
    ): Enrollment {
        return Enrollment::create([
            'student_profile_id' => $student->id,
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'prix_formation' => $formation->prix,
            'bourse_type' => 'aucune',
            'bourse_valeur' => 0,
            'montant_du' => $amountDue,
            'status' => 'active',
            'enrollment_date' => $session->start_date,
        ]);
    }
}
