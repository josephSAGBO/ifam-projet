<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Evaluation;
use App\Models\Fee;
use App\Models\Formation;
use App\Models\Grade;
use App\Models\Module;
use App\Models\Payment;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FinancialDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_uses_all_enrollment_balances_instead_of_legacy_fees(): void
    {
        [$student, $studentProfile] = $this->createUserWithStudentProfile(
            'etudiant-dashboard@ifam.test',
            'Étudiant',
            'Dashboard'
        );
        $activeEnrollment = $this->createEnrollment(
            $studentProfile,
            'Stylisme Année 1',
            'Session active',
            12000,
            10000
        );
        $completedEnrollment = $this->createEnrollment(
            $studentProfile,
            'Stylisme Année 2',
            'Session précédente',
            8000,
            6000,
            'completed'
        );
        $this->createEnrollmentPayment($activeEnrollment, 2500);
        $this->createEnrollmentPayment($completedEnrollment, 1000);

        $legacySession = AcademicSession::create([
            'name' => 'Ancienne session de frais',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);
        $legacyFee = Fee::create([
            'student_profile_id' => $studentProfile->id,
            'academic_session_id' => $legacySession->id,
            'title' => 'Ancien frais non utilisé',
            'amount' => 50000,
            'status' => 'pending',
        ]);
        Payment::create([
            'fee_id' => $legacyFee->id,
            'student_profile_id' => $studentProfile->id,
            'amount_paid' => 20000,
            'payment_date' => '2024-02-01',
            'method' => 'cash',
        ]);

        $response = $this->actingAs($student)->get(route('student.dashboard'));

        $response->assertOk();
        $response->assertSee('12 500 FCFA');
        $response->assertSee('3 500 FCFA');
        $response->assertSee('7 500 FCFA');
        $response->assertSee('Stylisme Année 1');
        $response->assertSee('Session active');
        $response->assertSee('Stylisme Année 2');
        $response->assertSee('Session précédente');
        $response->assertDontSee('50 000 FCFA');
    }

    public function test_parent_dashboard_shows_all_financial_enrollments_only_for_linked_children(): void
    {
        [$linkedStudent, $linkedStudentProfile] = $this->createUserWithStudentProfile(
            'enfant-dashboard@ifam.test',
            'Enfant',
            'Rattaché'
        );
        $this->createEnrollment($linkedStudentProfile, 'Formation en cours', 'Session en cours', 15000, 12000);
        $completedEnrollment = $this->createEnrollment(
            $linkedStudentProfile,
            'Formation terminée',
            'Session terminée',
            8000,
            8000,
            'completed'
        );
        $this->createEnrollmentPayment($completedEnrollment, 3000);

        [, $unlinkedStudentProfile] = $this->createUserWithStudentProfile(
            'autre-enfant-dashboard@ifam.test',
            'Autre',
            'Étudiant'
        );
        $this->createEnrollment($unlinkedStudentProfile, 'Dossier non lié', 'Session privée', 9000, 9000);
        $parent = $this->createUser('parent', 'parent-dashboard@ifam.test', 'Parent', 'Dashboard');
        $parent->children()->attach($linkedStudent->id, ['relationship' => 'Parent']);

        $response = $this->actingAs($parent)->get(route('parent.dashboard'));

        $response->assertOk();
        $response->assertSee('Enfant Rattaché');
        $response->assertSee('Formation en cours');
        $response->assertSee('Session en cours');
        $response->assertSee('Formation terminée');
        $response->assertSee('Session terminée');
        $response->assertSee('8 000 FCFA');
        $response->assertSee('3 000 FCFA');
        $response->assertSee('5 000 FCFA');
        $response->assertSee('Total payé :');
        $response->assertSee('Solde dû :');
        $response->assertSee('17 000 FCFA');
        $response->assertDontSee('Dossier non lié');
        $response->assertDontSee('Session privée');
    }

    public function test_parent_dashboard_shows_dated_grades_and_attendances_only_for_linked_children(): void
    {
        [$linkedStudent, $linkedStudentProfile] = $this->createUserWithStudentProfile(
            'enfant-academique@ifam.test',
            'Enfant',
            'Académique'
        );
        $linkedEnrollment = $this->createEnrollment(
            $linkedStudentProfile,
            'Formation liée',
            'Session liée',
            12000,
            12000
        );
        $linkedFormation = Formation::findOrFail($linkedEnrollment->formation_id);
        $linkedSession = AcademicSession::findOrFail($linkedEnrollment->academic_session_id);
        $linkedModule = Module::create(['name' => 'Module lié', 'code' => 'MOD-LIE']);
        $linkedCourse = Course::create([
            'formation_id' => $linkedFormation->id,
            'academic_session_id' => $linkedSession->id,
            'module_id' => $linkedModule->id,
            'name' => 'Atelier lié',
        ]);
        $linkedEvaluation = Evaluation::create([
            'cours_id' => $linkedCourse->id,
            'module_id' => $linkedModule->id,
            'formation_id' => $linkedFormation->id,
            'session_id' => $linkedSession->id,
            'titre' => 'Devoir daté visible',
            'type' => 'devoir',
            'date' => '2026-11-12',
            'statut' => 'cloturee',
        ]);
        Grade::create([
            'evaluation_id' => $linkedEvaluation->id,
            'student_profile_id' => $linkedStudentProfile->id,
            'score' => 16.75,
        ]);
        Attendance::create([
            'course_id' => $linkedCourse->id,
            'student_profile_id' => $linkedStudentProfile->id,
            'date' => '2026-11-14',
            'status' => 'present',
        ]);

        [$unlinkedStudent, $unlinkedStudentProfile] = $this->createUserWithStudentProfile(
            'enfant-non-lie-academique@ifam.test',
            'Autre',
            'Étudiant'
        );
        $unlinkedEnrollment = $this->createEnrollment(
            $unlinkedStudentProfile,
            'Formation privée',
            'Session privée',
            9000,
            9000
        );
        $unlinkedModule = Module::create(['name' => 'Module privé', 'code' => 'MOD-PRIVE']);
        $unlinkedCourse = Course::create([
            'formation_id' => $unlinkedEnrollment->formation_id,
            'academic_session_id' => $unlinkedEnrollment->academic_session_id,
            'module_id' => $unlinkedModule->id,
            'name' => 'Cours privé',
        ]);
        $unlinkedEvaluation = Evaluation::create([
            'cours_id' => $unlinkedCourse->id,
            'module_id' => $unlinkedModule->id,
            'formation_id' => $unlinkedEnrollment->formation_id,
            'session_id' => $unlinkedEnrollment->academic_session_id,
            'titre' => 'Note privée non liée',
            'type' => 'devoir',
            'date' => '2026-11-13',
            'statut' => 'cloturee',
        ]);
        Grade::create([
            'evaluation_id' => $unlinkedEvaluation->id,
            'student_profile_id' => $unlinkedStudentProfile->id,
            'score' => 18,
        ]);
        Attendance::create([
            'course_id' => $unlinkedCourse->id,
            'student_profile_id' => $unlinkedStudentProfile->id,
            'date' => '2026-11-15',
            'status' => 'absent',
        ]);

        $parent = $this->createUser('parent', 'parent-academique@ifam.test', 'Parent', 'Académique');
        $parent->children()->attach($linkedStudent->id, ['relationship' => 'Parent']);

        $response = $this->actingAs($parent)->get(route('parent.dashboard'));

        $response->assertSee('Devoir daté visible');
        $response->assertSee('12/11/2026');
        $response->assertSee('16,75 / 20');
        $response->assertSee('Atelier lié');
        $response->assertSee('14/11/2026');
        $response->assertSee('Présent');
        $response->assertDontSee('Note privée non liée');
        $response->assertDontSee('Cours privé');
        $response->assertDontSee('15/11/2026');
    }

    public function test_student_scolarite_uses_enrollment_amounts_and_enrollment_payments(): void
    {
        [$student, $studentProfile] = $this->createUserWithStudentProfile(
            'etudiant-scolarite@ifam.test',
            'Étudiant',
            'Scolarité'
        );
        $enrollment = $this->createEnrollment($studentProfile, 'Formation Scolarité', 'Session Scolarité', 12000, 10000);
        $this->createEnrollmentPayment($enrollment, 2500);

        $legacySession = AcademicSession::create([
            'name' => 'Session frais historiques',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);
        $legacyFee = Fee::create([
            'student_profile_id' => $studentProfile->id,
            'academic_session_id' => $legacySession->id,
            'title' => 'Ancienne ligne de frais',
            'amount' => 50000,
            'status' => 'pending',
        ]);
        Payment::create([
            'fee_id' => $legacyFee->id,
            'student_profile_id' => $studentProfile->id,
            'amount_paid' => 20000,
            'payment_date' => '2024-02-01',
            'method' => 'cash',
        ]);

        $response = $this->actingAs($student)->get(route('student.fees'));

        $response->assertOk();
        $response->assertSee('Formation Scolarité');
        $response->assertSee('Session Scolarité');
        $response->assertSee('10 000 FCFA');
        $response->assertSee('2 500 FCFA');
        $response->assertSee('7 500 FCFA');
        $response->assertDontSee('Ancienne ligne de frais');
        $response->assertDontSee('50 000 FCFA');
    }

    public function test_parent_fee_page_uses_linked_child_enrollments_and_payments(): void
    {
        [$linkedStudent, $linkedStudentProfile] = $this->createUserWithStudentProfile(
            'enfant-finance@ifam.test',
            'Enfant',
            'Finance'
        );
        $enrollment = $this->createEnrollment($linkedStudentProfile, 'Formation enfant', 'Session enfant', 12000, 10000);
        $this->createEnrollmentPayment($enrollment, 2500);

        [, $unlinkedStudentProfile] = $this->createUserWithStudentProfile(
            'autre-enfant-finance@ifam.test',
            'Autre',
            'Étudiant'
        );
        $this->createEnrollment($unlinkedStudentProfile, 'Formation privée', 'Session privée', 40000, 40000);

        $parent = $this->createUser('parent', 'parent-finance@ifam.test', 'Parent', 'Finance');
        $parent->children()->attach($linkedStudent->id, ['relationship' => 'Parent']);

        $response = $this->actingAs($parent)->get(route('parent.fees'));

        $response->assertOk();
        $response->assertSee('Formation enfant');
        $response->assertSee('2 500 FCFA');
        $response->assertSee('7 500 FCFA');
        $response->assertDontSee('Formation privée');
        $response->assertDontSee('40 000 FCFA');
    }

    public function test_parent_children_page_shows_enrollment_payment_and_balance_totals(): void
    {
        [$linkedStudent, $linkedStudentProfile] = $this->createUserWithStudentProfile(
            'enfant-child-finance@ifam.test',
            'Enfant',
            'Finance'
        );
        $enrollment = $this->createEnrollment($linkedStudentProfile, 'Formation enfant', 'Session enfant', 12000, 10000);
        $this->createEnrollmentPayment($enrollment, 2500);
        $parent = $this->createUser('parent', 'parent-child-finance@ifam.test', 'Parent', 'Finance');
        $parent->children()->attach($linkedStudent->id, ['relationship' => 'Parent']);

        $response = $this->actingAs($parent)->get(route('parent.children'));

        $response->assertOk();
        $response->assertSee('2 500');
        $response->assertSee('7 500');
    }

    /**
     * @return array{0: User, 1: StudentProfile}
     */
    private function createUserWithStudentProfile(string $email, string $firstName, string $lastName): array
    {
        $student = $this->createUser('etudiant', $email, $firstName, $lastName);

        return [$student, StudentProfile::create(['user_id' => $student->id])];
    }

    private function createUser(string $role, string $email, string $firstName, string $lastName): User
    {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        $user = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function createEnrollment(
        StudentProfile $studentProfile,
        string $formationName,
        string $sessionName,
        float $price,
        float $amountDue,
        string $status = 'active',
    ): Enrollment {
        $formation = Formation::create(['name' => $formationName, 'prix' => $price]);
        $session = AcademicSession::create([
            'name' => $sessionName,
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
        ]);

        return Enrollment::create([
            'student_profile_id' => $studentProfile->id,
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'prix_formation' => $price,
            'bourse_type' => 'montant',
            'bourse_valeur' => $price - $amountDue,
            'montant_du' => $amountDue,
            'status' => $status,
            'enrollment_date' => $session->start_date,
        ]);
    }

    private function createEnrollmentPayment(Enrollment $enrollment, float $amount): void
    {
        Payment::create([
            'inscription_id' => $enrollment->id,
            'montant' => $amount,
            'date_paiement' => '2026-10-01',
            'mode' => 'especes',
        ]);
    }
}
